<?php
/**
 * Boberdoo Response Parser
 * Properly parses both XML and JSON responses from Boberdoo API
 * 
 * Include this file in your form processing script and use the 
 * parseBoberdooResponse() function to correctly parse responses
 */

/**
 * Parse Boberdoo API response (XML or JSON)
 * 
 * @param string $response The raw response from Boberdoo
 * @return array Parsed response with status, lead_id, and other fields
 */
function parseBoberdooResponse($response) {
    $result = [
        'status' => 'error',
        'lead_id' => null,
        'price' => null,
        'redirect_url' => null,
        'error_message' => null,
        'raw_response' => $response,
        'response_type' => 'unknown'
    ];
    
    if (empty($response)) {
        $result['error_message'] = 'Empty response';
        return $result;
    }
    
    // Detect and parse XML response
    if (strpos($response, '<?xml') !== false || strpos($response, '<response>') !== false) {
        $result['response_type'] = 'xml';
        
        // Parse XML
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($response);
        
        if ($xml === false) {
            $errors = libxml_get_errors();
            $result['error_message'] = 'XML parsing failed: ' . ($errors[0]->message ?? 'Unknown error');
            libxml_clear_errors();
            return $result;
        }
        
        // Extract status (case-insensitive)
        if (isset($xml->status)) {
            $status = (string)$xml->status;
            if (strtolower($status) === 'success') {
                $result['status'] = 'success';
            } elseif (strtolower($status) === 'sold') {
                $result['status'] = 'success';
            } elseif (strtolower($status) === 'rejected') {
                $result['status'] = 'rejected';
            } else {
                $result['status'] = 'error';
                $result['error_message'] = $status;
            }
        }
        
        // Extract lead_id
        if (isset($xml->lead_id)) {
            $result['lead_id'] = (string)$xml->lead_id;
        } elseif (isset($xml->leadid)) { // Alternative naming
            $result['lead_id'] = (string)$xml->leadid;
        }
        
        // Extract price if available
        if (isset($xml->price)) {
            $result['price'] = (float)$xml->price;
        }
        
        // Extract redirect URL if available
        if (isset($xml->redirect_url)) {
            $result['redirect_url'] = (string)$xml->redirect_url;
        } elseif (isset($xml->redirect)) {
            $result['redirect_url'] = (string)$xml->redirect;
        }
        
        // Extract error message if available
        if (isset($xml->message)) {
            $result['error_message'] = (string)$xml->message;
        } elseif (isset($xml->error)) {
            $result['error_message'] = (string)$xml->error;
        } elseif (isset($xml->reason)) {
            $result['error_message'] = (string)$xml->reason;
        }
    }
    // Detect and parse JSON response
    elseif (strpos(trim($response), '{') === 0) {
        $result['response_type'] = 'json';
        
        $json = json_decode($response, true);
        
        if ($json === null) {
            $result['error_message'] = 'JSON parsing failed: ' . json_last_error_msg();
            return $result;
        }
        
        // Extract status
        if (isset($json['status'])) {
            $status = $json['status'];
            if (strtolower($status) === 'success' || strtolower($status) === 'sold') {
                $result['status'] = 'success';
            } elseif (strtolower($status) === 'rejected') {
                $result['status'] = 'rejected';
            } else {
                $result['status'] = 'error';
                $result['error_message'] = $status;
            }
        }
        
        // Extract lead_id
        if (isset($json['lead_id'])) {
            $result['lead_id'] = $json['lead_id'];
        } elseif (isset($json['leadid'])) {
            $result['lead_id'] = $json['leadid'];
        }
        
        // Extract other fields
        if (isset($json['price'])) {
            $result['price'] = (float)$json['price'];
        }
        
        if (isset($json['redirect_url'])) {
            $result['redirect_url'] = $json['redirect_url'];
        } elseif (isset($json['redirect'])) {
            $result['redirect_url'] = $json['redirect'];
        }
        
        if (isset($json['message'])) {
            $result['error_message'] = $json['message'];
        } elseif (isset($json['error'])) {
            $result['error_message'] = $json['error'];
        } elseif (isset($json['reason'])) {
            $result['error_message'] = $json['reason'];
        }
    }
    // Try to detect success patterns in plain text
    else {
        $result['response_type'] = 'text';
        
        // Check for success indicators
        if (stripos($response, 'success') !== false || stripos($response, 'sold') !== false) {
            $result['status'] = 'success';
            
            // Try to extract lead ID from text
            if (preg_match('/lead[_\s]?id[:\s]+(\d+)/i', $response, $matches)) {
                $result['lead_id'] = $matches[1];
            }
        } else {
            $result['status'] = 'error';
            $result['error_message'] = substr($response, 0, 500); // Limit error message length
        }
    }
    
    return $result;
}

/**
 * Update lead buffer with parsed Boberdoo response
 * 
 * @param mysqli $mysqli Database connection
 * @param int $bufferId The lead_buffer table ID
 * @param array $parsedResponse Response from parseBoberdooResponse()
 * @return bool Success status
 */
function updateLeadBufferWithResponse($mysqli, $bufferId, $parsedResponse) {
    // Updated query to match actual lead_buffer table schema
    $query = "
        UPDATE lead_buffer 
        SET 
            boberdoo_status = ?,
            boberdoo_lead_id = ?,
            boberdoo_response = ?,
            boberdoo_error = ?
        WHERE id = ?
    ";
    
    $stmt = $mysqli->prepare($query);
    
    if (!$stmt) {
        error_log("updateLeadBufferWithResponse - Prepare failed: " . $mysqli->error);
        return false;
    }
    
    $stmt->bind_param(
        "ssssi",
        $parsedResponse['status'],
        $parsedResponse['lead_id'],
        $parsedResponse['raw_response'],
        $parsedResponse['error_message'],
        $bufferId
    );
    
    $success = $stmt->execute();
    
    if (!$success) {
        error_log("updateLeadBufferWithResponse - Execute failed: " . $stmt->error);
    }
    
    $stmt->close();
    
    // If successful, also log to api_response_log if table exists
    if ($success && $parsedResponse['status'] === 'success') {
        $logQuery = "
            INSERT INTO api_response_log 
            (lead_id, response_code, response_body, created_at) 
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
            response_code = VALUES(response_code),
            response_body = VALUES(response_body)
        ";
        
        if ($logStmt = $mysqli->prepare($logQuery)) {
            $logStmt->bind_param(
                "sis",
                $parsedResponse['lead_id'],
                $responseCode,
                $parsedResponse['raw_response']
            );
            $logStmt->execute();
            $logStmt->close();
        }
    }
    
    return $success;
}

// Example usage in your form processing script:
/*
// After sending to Boberdoo and getting $response:
$parsedResponse = parseBoberdooResponse($response);

// Update the database
if (updateLeadBufferWithResponse($mysqli, $leadBufferId, $parsedResponse)) {
    if ($parsedResponse['status'] === 'success') {
        // Handle successful submission
        echo "Lead successfully submitted! Boberdoo ID: " . $parsedResponse['lead_id'];
    } else {
        // Handle error
        echo "Submission failed: " . $parsedResponse['error_message'];
    }
}
*/

// Test function to verify parsing
function testResponseParsing() {
    $testCases = [
        // XML Success
        '<?xml version="1.0" encoding="UTF-8"?><response><status>Success</status><lead_id>3261815</lead_id></response>',
        
        // XML Error
        '<?xml version="1.0"?><response><status>Error</status><message>Invalid phone number</message></response>',
        
        // JSON Success
        '{"status":"Success","lead_id":"3261816","price":25.00}',
        
        // JSON Error
        '{"status":"Rejected","reason":"Duplicate lead"}',
    ];
    
    echo "<h3>Response Parser Test Results:</h3>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Response</th><th>Parsed Status</th><th>Lead ID</th><th>Error</th></tr>";
    
    foreach ($testCases as $response) {
        $parsed = parseBoberdooResponse($response);
        echo "<tr>";
        echo "<td><pre>" . htmlspecialchars(substr($response, 0, 100)) . "</pre></td>";
        echo "<td style='color: " . ($parsed['status'] === 'success' ? 'green' : 'red') . "'>";
        echo $parsed['status'] . "</td>";
        echo "<td>" . ($parsed['lead_id'] ?? '-') . "</td>";
        echo "<td>" . ($parsed['error_message'] ?? '-') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

// Uncomment to test the parser:
// testResponseParsing();
?>