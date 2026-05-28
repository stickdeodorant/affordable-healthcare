<?php
/**
 * GET-LEAD-DETAILS.PHP - Get detailed information about a specific lead
 * Location: /multi-quote/inc/api/get-lead-details.php
 */

session_start();

// Check authentication (optional for testing)
if (!isset($_SESSION['admin_authenticated'])) {
    // Commented out for testing - uncomment in production
    // http_response_code(401);
    // echo json_encode(['error' => 'Unauthorized']);
    // exit;
}

header('Content-Type: application/json');

// Include database configuration
require_once __DIR__ . '/../config/db-config.php';

// Get database connection
$mysqli = getDBConnection();

if (!$mysqli) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Get the identifier - could be numeric ID or lead_id string
$identifier = $_GET['id'] ?? $_GET['lead_id'] ?? '';

if (!$identifier) {
    echo json_encode(['error' => 'No lead identifier provided']);
    exit;
}

$data = null;
$source = '';

// Determine if it's a numeric ID or lead_id string
if (is_numeric($identifier)) {
    // Numeric ID - search by id column
    $id = intval($identifier);
    
    // Try buffer first
    $query = "SELECT * FROM lead_buffer WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $source = 'buffer';
    } else {
        // Try history
        $query = "SELECT * FROM lead_history WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            $source = 'history';
        }
    }
    $stmt->close();
    
} else {
    // String lead_id - search by lead_id column
    $lead_id = $mysqli->real_escape_string($identifier);
    
    // Try buffer first
    $query = "SELECT * FROM lead_buffer WHERE lead_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $lead_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $source = 'buffer';
    } else {
        // Try history
        $query = "SELECT * FROM lead_history WHERE lead_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $lead_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            $source = 'history';
        }
    }
    $stmt->close();
}

// If lead found, format the response
if ($data) {
    // Parse JSON fields if they exist
    if (isset($data['resubmit_history']) && $data['resubmit_history']) {
        $data['resubmit_history'] = json_decode($data['resubmit_history'], true) ?? [];
    } else {
        $data['resubmit_history'] = [];
    }
    
    if (isset($data['form_data_json']) && $data['form_data_json']) {
        $data['form_data'] = json_decode($data['form_data_json'], true) ?? [];
    }
    
    if (isset($data['boberdoo_response']) && $data['boberdoo_response']) {
        $data['boberdoo_response_parsed'] = json_decode($data['boberdoo_response'], true);
    }
    
    // Add computed fields
    $data['full_name'] = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
    $data['location'] = trim(($data['city'] ?? '') . ', ' . ($data['state'] ?? '') . ' ' . ($data['zip'] ?? ''));
    $data['source_table'] = $source;
    
    // Check blacklist status
    $email = $data['email'] ?? '';
    $phone = $data['phone'] ?? $data['primary_phone'] ?? '';
    
    if ($email || $phone) {
        $blacklistQuery = "SELECT * FROM email_blacklist WHERE ";
        $conditions = [];
        $params = [];
        $types = '';
        
        if ($email) {
            $conditions[] = "email = ?";
            $params[] = $email;
            $types .= 's';
        }
        
        if ($phone) {
            $conditions[] = "phone = ?";
            $params[] = $phone;
            $types .= 's';
        }
        
        $blacklistQuery .= implode(' OR ', $conditions);
        
        if ($stmt = $mysqli->prepare($blacklistQuery)) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $blacklistResult = $stmt->get_result();
            
            if ($blacklistResult->num_rows > 0) {
                $blacklistData = $blacklistResult->fetch_assoc();
                $data['blacklist_status'] = [
                    'is_blacklisted' => true,
                    'is_permanent' => $blacklistData['is_permanent'] ? true : false,
                    'block_until' => $blacklistData['block_until'],
                    'submission_count' => $blacklistData['submission_count'],
                    'blacklist_reason' => $blacklistData['blacklist_reason']
                ];
            } else {
                $data['blacklist_status'] = ['is_blacklisted' => false];
            }
            $stmt->close();
        }
    }
    
    // Get related API responses
    if (isset($data['lead_id'])) {
        $apiQuery = "SELECT * FROM api_response_log WHERE lead_id = ? ORDER BY created_at DESC LIMIT 10";
        if ($stmt = $mysqli->prepare($apiQuery)) {
            $stmt->bind_param("s", $data['lead_id']);
            $stmt->execute();
            $apiResult = $stmt->get_result();
            
            $apiResponses = [];
            while ($apiRow = $apiResult->fetch_assoc()) {
                $apiResponses[] = [
                    'api_name' => $apiRow['api_name'],
                    'response_code' => $apiRow['response_code'],
                    'response_body' => $apiRow['response_body'],
                    'created_at' => $apiRow['created_at']
                ];
            }
            
            $data['api_responses'] = $apiResponses;
            $stmt->close();
        }
    }
    
    // Get resubmission queue status
    if (isset($data['lead_id'])) {
        $queueQuery = "SELECT * FROM resubmission_queue WHERE lead_id = ? AND status IN ('pending', 'scheduled') ORDER BY created_at DESC LIMIT 1";
        if ($stmt = $mysqli->prepare($queueQuery)) {
            $stmt->bind_param("s", $data['lead_id']);
            $stmt->execute();
            $queueResult = $stmt->get_result();
            
            if ($queueResult->num_rows > 0) {
                $data['queue_status'] = $queueResult->fetch_assoc();
            } else {
                $data['queue_status'] = null;
            }
            $stmt->close();
        }
    }
    
    // Success response
    echo json_encode([
        'success' => true,
        'data' => $data,
        'source' => $source,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} else {
    // Not found
    echo json_encode([
        'success' => false,
        'error' => 'Lead not found',
        'searched_for' => $identifier,
        'searched_in' => ['lead_buffer', 'lead_history']
    ]);
}

$mysqli->close();
?>