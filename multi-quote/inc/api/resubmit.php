<?php
/**
 * Resubmit API Endpoint
 * Handles lead resubmission to Boberdoo
 * Location: /multi-quote/inc/api/resubmit.php
 */

session_start();
header('Content-Type: application/json');

// Include required files
require_once __DIR__ . '/../config/db-config.php';
require_once __DIR__ . '/../../boberdoo-response-parser.php';

// Get database connection
$mysqli = getDBConnection();

if (!$mysqli) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed']));
}

// Get action and parameters
$action = $_POST['action'] ?? 'resubmit_single';
$response = ['success' => false, 'message' => 'Invalid action'];

switch ($action) {
    case 'resubmit_single':
        $leadId = $_POST['lead_id'] ?? null;
        if ($leadId) {
            $response = resubmitSingleLead($mysqli, $leadId);
        } else {
            $response = ['success' => false, 'message' => 'Lead ID required'];
        }
        break;
        
    case 'process_all_pending':
        $response = processAllPendingLeads($mysqli);
        break;
        
    case 'process_queue':
        $response = processQueue($mysqli);
        break;
        
    case 'bulk_resubmit':
        $leadIds = $_POST['lead_ids'] ?? [];
        if (!empty($leadIds)) {
            $response = bulkResubmitLeads($mysqli, $leadIds);
        } else {
            $response = ['success' => false, 'message' => 'No leads selected'];
        }
        break;
        
    case 'bulk_delete':
        $leadIds = $_POST['lead_ids'] ?? [];
        if (!empty($leadIds)) {
            $response = bulkDeleteFromQueue($mysqli, $leadIds);
        } else {
            $response = ['success' => false, 'message' => 'No leads selected'];
        }
        break;
        
    case 'clear_completed':
        $response = clearCompletedItems($mysqli);
        break;
        
    default:
        $response = ['success' => false, 'message' => 'Unknown action: ' . $action];
}

echo json_encode($response);
$mysqli->close();

/**
 * Resubmit a single lead to Boberdoo
 */
function resubmitSingleLead($mysqli, $leadId) {
    // Get lead data from buffer
    $stmt = $mysqli->prepare("
        SELECT * FROM lead_buffer 
        WHERE lead_id = ? 
        LIMIT 1
    ");
    
    $stmt->bind_param("s", $leadId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return ['success' => false, 'message' => 'Lead not found'];
    }
    
    $lead = $result->fetch_assoc();
    $stmt->close();
    
    // Parse form data JSON
    $formData = json_decode($lead['form_data_json'], true);
    if (!$formData) {
        return ['success' => false, 'message' => 'Invalid form data'];
    }
    
    // Submit to Boberdoo
    $boberdooResult = submitToBoberdoo($formData);
    
    // Update lead status based on result
    if ($boberdooResult['success']) {
        // Update buffer status
        $updateStmt = $mysqli->prepare("
            UPDATE lead_buffer 
            SET boberdoo_status = 'success',
                boberdoo_lead_id = ?,
                boberdoo_price = ?,
                boberdoo_buyer = ?,
                updated_at = NOW()
            WHERE lead_id = ?
        ");
        
        $updateStmt->bind_param("sdss", 
            $boberdooResult['lead_id'],
            $boberdooResult['price'],
            $boberdooResult['buyer'],
            $leadId
        );
        
        $updateStmt->execute();
        $updateStmt->close();
        
        // Log to history
        logToHistory($mysqli, $lead, 'success', $boberdooResult);
        
        return [
            'success' => true,
            'message' => 'Lead resubmitted successfully',
            'boberdoo_lead_id' => $boberdooResult['lead_id'],
            'price' => $boberdooResult['price']
        ];
    } else {
        // Update buffer with error
        $updateStmt = $mysqli->prepare("
            UPDATE lead_buffer 
            SET boberdoo_status = 'error',
                error_message = ?,
                updated_at = NOW()
            WHERE lead_id = ?
        ");
        
        $errorMsg = $boberdooResult['error'] ?? 'Unknown error';
        $updateStmt->bind_param("ss", $errorMsg, $leadId);
        $updateStmt->execute();
        $updateStmt->close();
        
        // Log to history
        logToHistory($mysqli, $lead, 'error', $boberdooResult);
        
        return [
            'success' => false,
            'message' => 'Resubmission failed: ' . $errorMsg
        ];
    }
}

/**
 * Process all pending leads
 */
function processAllPendingLeads($mysqli) {
    // Get all pending leads
    $result = $mysqli->query("
        SELECT * FROM lead_buffer 
        WHERE boberdoo_status IN ('pending', 'error', 'failed')
        ORDER BY created_at ASC
        LIMIT 50
    ");
    
    $processed = 0;
    $successful = 0;
    $failed = 0;
    
    while ($lead = $result->fetch_assoc()) {
        $resubmitResult = resubmitSingleLead($mysqli, $lead['lead_id']);
        $processed++;
        
        if ($resubmitResult['success']) {
            $successful++;
        } else {
            $failed++;
        }
        
        // Add small delay to avoid overwhelming the API
        usleep(500000); // 0.5 second delay
    }
    
    return [
        'success' => true,
        'message' => "Processed $processed leads",
        'processed' => $processed,
        'successful' => $successful,
        'failed' => $failed
    ];
}

/**
 * Process resubmission queue
 */
function processQueue($mysqli) {
    // Get queued items
    $result = $mysqli->query("
        SELECT * FROM resubmission_queue 
        WHERE status IN ('queued', 'scheduled')
        AND (scheduled_at IS NULL OR scheduled_at <= NOW())
        ORDER BY priority DESC, created_at ASC
        LIMIT 25
    ");
    
    $processed = 0;
    $successful = 0;
    $failed = 0;
    
    while ($item = $result->fetch_assoc()) {
        // Update status to processing
        $updateStmt = $mysqli->prepare("
            UPDATE resubmission_queue 
            SET status = 'processing', 
                attempts = attempts + 1,
                last_attempt = NOW()
            WHERE queue_id = ?
        ");
        $updateStmt->bind_param("i", $item['queue_id']);
        $updateStmt->execute();
        $updateStmt->close();
        
        // Resubmit the lead
        $resubmitResult = resubmitSingleLead($mysqli, $item['lead_id']);
        $processed++;
        
        // Update queue status based on result
        if ($resubmitResult['success']) {
            $successful++;
            $status = 'completed';
            $errorMsg = null;
        } else {
            $failed++;
            $status = ($item['attempts'] >= 3) ? 'failed' : 'queued';
            $errorMsg = $resubmitResult['message'];
        }
        
        $updateStmt = $mysqli->prepare("
            UPDATE resubmission_queue 
            SET status = ?,
                error_message = ?,
                completed_at = IF(? = 'completed', NOW(), NULL)
            WHERE queue_id = ?
        ");
        $updateStmt->bind_param("sssi", $status, $errorMsg, $status, $item['queue_id']);
        $updateStmt->execute();
        $updateStmt->close();
        
        // Add delay
        usleep(500000);
    }
    
    return [
        'success' => true,
        'message' => "Processed $processed items",
        'processed' => $processed,
        'successful' => $successful,
        'failed' => $failed
    ];
}

/**
 * Bulk resubmit leads
 */
function bulkResubmitLeads($mysqli, $leadIds) {
    $count = 0;
    $successful = 0;
    
    foreach ($leadIds as $leadId) {
        $result = resubmitSingleLead($mysqli, $leadId);
        $count++;
        if ($result['success']) {
            $successful++;
        }
        usleep(500000); // Delay between submissions
    }
    
    return [
        'success' => true,
        'message' => "Resubmitted $count leads",
        'count' => $count,
        'successful' => $successful
    ];
}

/**
 * Bulk delete from queue
 */
function bulkDeleteFromQueue($mysqli, $leadIds) {
    $placeholders = str_repeat('?,', count($leadIds) - 1) . '?';
    $types = str_repeat('s', count($leadIds));
    
    $stmt = $mysqli->prepare("
        DELETE FROM resubmission_queue 
        WHERE lead_id IN ($placeholders)
    ");
    
    $stmt->bind_param($types, ...$leadIds);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    
    return [
        'success' => true,
        'message' => "Deleted $affected items",
        'count' => $affected
    ];
}

/**
 * Clear completed items from queue
 */
function clearCompletedItems($mysqli) {
    $result = $mysqli->query("
        DELETE FROM resubmission_queue 
        WHERE status = 'completed'
    ");
    
    $affected = $mysqli->affected_rows;
    
    return [
        'success' => true,
        'message' => "Cleared $affected completed items",
        'count' => $affected
    ];
}

/**
 * Submit to Boberdoo API
 */
function submitToBoberdoo($formData) {
    // Your Boberdoo API credentials
    $api_url = 'https://leads.boberdoo.com/leadimport.aspx';
    $api_key = 'YOUR_API_KEY'; // Update with actual API key
    
    // Build Boberdoo request
    $boberdooData = [
        'TYPE' => '85',
        'SRC' => 'InfinixMedia',
        'Landing_Page' => 'affordable-healthcare.com',
        'IP_Address' => $formData['ip_address'] ?? $_SERVER['REMOTE_ADDR'],
        'First_Name' => $formData['First_Name'] ?? '',
        'Last_Name' => $formData['Last_Name'] ?? '',
        'Email' => $formData['Email'] ?? '',
        'Primary_Phone' => $formData['Primary_Phone'] ?? '',
        'Address' => $formData['Address'] ?? '',
        'City' => $formData['City'] ?? '',
        'State' => $formData['State'] ?? '',
        'Zip' => $formData['Zip'] ?? '',
        'Gender' => $formData['Gender'] ?? '',
        'DOB' => $formData['DOB'] ?? '',
        'Tobacco' => $formData['Tobacco'] ?? '',
        'Major_Health_Conditions' => $formData['Major_Health_Conditions'] ?? '',
        'Life_Event' => $formData['Life_Event'] ?? '',
        'Household_Income' => $formData['Household_Income'] ?? '',
        'Household_Size' => $formData['Household_Size'] ?? ''
    ];
    
    // Make API call
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($boberdooData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'error' => 'CURL Error: ' . $error
        ];
    }
    
    // Parse Boberdoo response
    $parser = new BoberdooResponseParser($response);
    $parsedResponse = $parser->parse();
    
    if ($parsedResponse['success']) {
        return [
            'success' => true,
            'lead_id' => $parsedResponse['lead_id'],
            'price' => $parsedResponse['price'],
            'buyer' => $parsedResponse['buyer']
        ];
    } else {
        return [
            'success' => false,
            'error' => $parsedResponse['message'] ?? 'Unknown error'
        ];
    }
}

/**
 * Log to history table
 */
function logToHistory($mysqli, $lead, $status, $result) {
    $stmt = $mysqli->prepare("
        INSERT INTO lead_history (
            lead_id, email, phone, first_name, last_name,
            city, state, zip, ip_address, status,
            boberdoo_lead_id, boberdoo_error, boberdoo_price,
            boberdoo_buyer, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $boberdooLeadId = $result['lead_id'] ?? null;
    $boberdooError = $result['error'] ?? null;
    $boberdooPrice = $result['price'] ?? null;
    $boberdooBuyer = $result['buyer'] ?? null;
    
    $stmt->bind_param("ssssssssssssds",
        $lead['lead_id'],
        $lead['email'],
        $lead['primary_phone'],
        $lead['first_name'],
        $lead['last_name'],
        $lead['city'],
        $lead['state'],
        $lead['zip'],
        $lead['ip_address'],
        $status,
        $boberdooLeadId,
        $boberdooError,
        $boberdooPrice,
        $boberdooBuyer
    );
    
    $stmt->execute();
    $stmt->close();
}

?>