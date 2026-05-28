<?php
/**
 * Lead Resubmission Handler
 * Handles individual and bulk resubmission of leads from the buffer to Boberdoo
 */

require_once __DIR__ . '/classes/SecurityHelper.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/db-config.php';

session_start();

// Check for admin authentication
if (!isset($_SESSION['admin_authenticated']) || $_SESSION['admin_authenticated'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Set response header
header('Content-Type: application/json');

// Get database connection
$mysqli = getDBConnection();

if (!$mysqli) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Get request parameters
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$leadIds = $_POST['lead_ids'] ?? [];
$priority = $_POST['priority'] ?? 5;
$scheduledTime = $_POST['scheduled_time'] ?? null;

// Validate action
if (!in_array($action, ['resubmit_single', 'resubmit_bulk', 'queue_bulk', 'process_queue', 'get_status'])) {
    echo json_encode(['error' => 'Invalid action']);
    exit;
}

// Database connection
try {
    $mysqli = new mysqli(
        $dbConfig['host'],
        $dbConfig['user'],
        $dbConfig['pass'],
        $dbConfig['name']
    );
    
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }
    
    $mysqli->set_charset("utf8mb4");
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

/**
 * Main action handler
 */
switch ($action) {
    case 'resubmit_single':
        resubmitSingleLead($mysqli, $leadIds[0] ?? null);
        break;
        
    case 'resubmit_bulk':
        resubmitBulkLeads($mysqli, $leadIds);
        break;
        
    case 'queue_bulk':
        queueBulkResubmission($mysqli, $leadIds, $priority, $scheduledTime);
        break;
        
    case 'process_queue':
        processResubmissionQueue($mysqli);
        break;
        
    case 'get_status':
        getResubmissionStatus($mysqli, $leadIds[0] ?? null);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}

/**
 * Resubmit a single lead to Boberdoo
 */
function resubmitSingleLead($mysqli, $leadId) {
    if (!$leadId) {
        echo json_encode(['error' => 'Lead ID required']);
        return;
    }
    
    // Get lead data from buffer
    $stmt = $mysqli->prepare("
        SELECT * FROM lead_buffer 
        WHERE id = ? AND expires_at > NOW()
    ");
    
    $stmt->bind_param("i", $leadId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'Lead not found or expired']);
        return;
    }
    
    $lead = $result->fetch_assoc();
    $stmt->close();
    
    // Check if already successfully submitted
    if ($lead['boberdoo_status'] === 'success') {
        echo json_encode([
            'success' => false,
            'message' => 'Lead already successfully submitted'
        ]);
        return;
    }
    
    // Parse the stored form data
    $formData = json_decode($lead['form_data_json'], true);
    if (!$formData) {
        echo json_encode(['error' => 'Invalid form data']);
        return;
    }
    
    // Submit to Boberdoo
    $boberdooResult = submitToBoberdoo($formData);
    
    // Update resubmission history
    $resubmitHistory = json_decode($lead['resubmit_history'] ?? '[]', true);
    $resubmitHistory[] = [
        'timestamp' => date('Y-m-d H:i:s'),
        'status' => $boberdooResult['success'] ? 'success' : 'failed',
        'response' => $boberdooResult['response'],
        'admin_user' => $_SESSION['admin_user'] ?? 'system'
    ];
    
    // Update buffer record
    $updateStmt = $mysqli->prepare("
        UPDATE lead_buffer 
        SET boberdoo_status = ?,
            boberdoo_response = ?,
            boberdoo_lead_id = ?,
            boberdoo_error = ?,
            resubmit_count = resubmit_count + 1,
            last_resubmit_time = NOW(),
            resubmit_history = ?
        WHERE id = ?
    ");
    
    $status = $boberdooResult['success'] ? 'resubmitted' : 'error';
    $resubmitHistoryJson = json_encode($resubmitHistory);
    
    $updateStmt->bind_param("sssssi",
        $status,
        $boberdooResult['response'],
        $boberdooResult['lead_id'],
        $boberdooResult['error'],
        $resubmitHistoryJson,
        $leadId
    );
    $updateStmt->execute();
    $updateStmt->close();
    
    // Log admin activity
    logAdminActivity($mysqli, 'resubmit_single', 'lead', $leadId, [
        'success' => $boberdooResult['success'],
        'lead_email' => $lead['email']
    ]);
    
    // Return response
    echo json_encode([
        'success' => $boberdooResult['success'],
        'message' => $boberdooResult['success'] ? 
            'Lead successfully resubmitted' : 
            'Resubmission failed: ' . $boberdooResult['error'],
        'lead_id' => $boberdooResult['lead_id'],
        'attempts' => $lead['resubmit_count'] + 1
    ]);
}

/**
 * Resubmit multiple leads
 */
function resubmitBulkLeads($mysqli, $leadIds) {
    if (empty($leadIds) || !is_array($leadIds)) {
        echo json_encode(['error' => 'No leads selected']);
        return;
    }
    
    $results = [
        'total' => count($leadIds),
        'successful' => 0,
        'failed' => 0,
        'details' => []
    ];
    
    foreach ($leadIds as $leadId) {
        // Get lead data
        $stmt = $mysqli->prepare("
            SELECT * FROM lead_buffer 
            WHERE id = ? AND expires_at > NOW()
        ");
        
        $stmt->bind_param("i", $leadId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $results['failed']++;
            $results['details'][] = [
                'id' => $leadId,
                'success' => false,
                'message' => 'Lead not found or expired'
            ];
            continue;
        }
        
        $lead = $result->fetch_assoc();
        $stmt->close();
        
        // Skip if already successful
        if ($lead['boberdoo_status'] === 'success') {
            $results['details'][] = [
                'id' => $leadId,
                'success' => false,
                'message' => 'Already successfully submitted'
            ];
            continue;
        }
        
        // Parse and submit
        $formData = json_decode($lead['form_data_json'], true);
        if (!$formData) {
            $results['failed']++;
            $results['details'][] = [
                'id' => $leadId,
                'success' => false,
                'message' => 'Invalid form data'
            ];
            continue;
        }
        
        // Submit to Boberdoo
        $boberdooResult = submitToBoberdoo($formData);
        
        // Update resubmission history
        $resubmitHistory = json_decode($lead['resubmit_history'] ?? '[]', true);
        $resubmitHistory[] = [
            'timestamp' => date('Y-m-d H:i:s'),
            'status' => $boberdooResult['success'] ? 'success' : 'failed',
            'response' => $boberdooResult['response'],
            'admin_user' => $_SESSION['admin_user'] ?? 'system',
            'bulk_submission' => true
        ];
        
        // Update buffer record
        $updateStmt = $mysqli->prepare("
            UPDATE lead_buffer 
            SET boberdoo_status = ?,
                boberdoo_response = ?,
                boberdoo_lead_id = ?,
                boberdoo_error = ?,
                resubmit_count = resubmit_count + 1,
                last_resubmit_time = NOW(),
                resubmit_history = ?
            WHERE id = ?
        ");
        
        $status = $boberdooResult['success'] ? 'resubmitted' : 'error';
        $resubmitHistoryJson = json_encode($resubmitHistory);
        
        $updateStmt->bind_param("sssssi",
            $status,
            $boberdooResult['response'],
            $boberdooResult['lead_id'],
            $boberdooResult['error'],
            $resubmitHistoryJson,
            $leadId
        );
        $updateStmt->execute();
        $updateStmt->close();
        
        // Track results
        if ($boberdooResult['success']) {
            $results['successful']++;
        } else {
            $results['failed']++;
        }
        
        $results['details'][] = [
            'id' => $leadId,
            'email' => $lead['email'],
            'success' => $boberdooResult['success'],
            'message' => $boberdooResult['success'] ? 
                'Successfully resubmitted' : 
                'Failed: ' . $boberdooResult['error'],
            'lead_id' => $boberdooResult['lead_id']
        ];
        
        // Add small delay to prevent overwhelming the API
        usleep(250000); // 250ms delay
    }
    
    // Log admin activity
    logAdminActivity($mysqli, 'resubmit_bulk', 'leads', implode(',', $leadIds), [
        'total' => $results['total'],
        'successful' => $results['successful'],
        'failed' => $results['failed']
    ]);
    
    echo json_encode($results);
}

/**
 * Queue leads for scheduled resubmission
 */
function queueBulkResubmission($mysqli, $leadIds, $priority, $scheduledTime) {
    if (empty($leadIds) || !is_array($leadIds)) {
        echo json_encode(['error' => 'No leads selected']);
        return;
    }
    
    $queued = 0;
    $failed = 0;
    
    foreach ($leadIds as $leadId) {
        // Check if lead exists and is eligible
        $checkStmt = $mysqli->prepare("
            SELECT id FROM lead_buffer 
            WHERE id = ? 
            AND expires_at > NOW() 
            AND boberdoo_status != 'success'
        ");
        
        $checkStmt->bind_param("i", $leadId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows === 0) {
            $failed++;
            continue;
        }
        $checkStmt->close();
        
        // Add to queue
        $queueStmt = $mysqli->prepare("
            INSERT INTO resubmission_queue 
            (buffer_lead_id, priority, scheduled_time, status)
            VALUES (?, ?, ?, 'queued')
            ON DUPLICATE KEY UPDATE
                priority = VALUES(priority),
                scheduled_time = VALUES(scheduled_time),
                status = 'queued'
        ");
        
        $queueStmt->bind_param("iis", $leadId, $priority, $scheduledTime);
        if ($queueStmt->execute()) {
            $queued++;
            
            // Mark lead for resubmit in buffer
            $markStmt = $mysqli->prepare("
                UPDATE lead_buffer 
                SET marked_for_resubmit = TRUE 
                WHERE id = ?
            ");
            $markStmt->bind_param("i", $leadId);
            $markStmt->execute();
            $markStmt->close();
        } else {
            $failed++;
        }
        $queueStmt->close();
    }
    
    // Log admin activity
    logAdminActivity($mysqli, 'queue_resubmission', 'leads', implode(',', $leadIds), [
        'queued' => $queued,
        'failed' => $failed,
        'priority' => $priority,
        'scheduled_time' => $scheduledTime
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => "Successfully queued $queued leads for resubmission",
        'queued' => $queued,
        'failed' => $failed
    ]);
}

/**
 * Process the resubmission queue (called by cron)
 */
function processResubmissionQueue($mysqli) {
    // Get queued items ready for processing
    $stmt = $mysqli->prepare("
        SELECT q.*, lb.form_data_json, lb.email
        FROM resubmission_queue q
        JOIN lead_buffer lb ON q.buffer_lead_id = lb.id
        WHERE q.status = 'queued'
        AND (q.scheduled_time IS NULL OR q.scheduled_time <= NOW())
        AND lb.expires_at > NOW()
        ORDER BY q.priority DESC, q.created_at ASC
        LIMIT 50
    ");
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $processed = 0;
    $successful = 0;
    $failed = 0;
    
    while ($row = $result->fetch_assoc()) {
        // Update status to processing
        $updateStmt = $mysqli->prepare("
            UPDATE resubmission_queue 
            SET status = 'processing', 
                attempts = attempts + 1,
                last_attempt = NOW()
            WHERE id = ?
        ");
        $updateStmt->bind_param("i", $row['id']);
        $updateStmt->execute();
        $updateStmt->close();
        
        // Parse and submit
        $formData = json_decode($row['form_data_json'], true);
        if (!$formData) {
            $failed++;
            updateQueueStatus($mysqli, $row['id'], 'failed', 'Invalid form data');
            continue;
        }
        
        // Submit to Boberdoo
        $boberdooResult = submitToBoberdoo($formData);
        
        if ($boberdooResult['success']) {
            $successful++;
            updateQueueStatus($mysqli, $row['id'], 'completed', null);
            
            // Update buffer
            $bufferStmt = $mysqli->prepare("
                UPDATE lead_buffer 
                SET boberdoo_status = 'resubmitted',
                    boberdoo_response = ?,
                    boberdoo_lead_id = ?,
                    resubmit_count = resubmit_count + 1,
                    last_resubmit_time = NOW(),
                    marked_for_resubmit = FALSE
                WHERE id = ?
            ");
            
            $bufferStmt->bind_param("ssi",
                $boberdooResult['response'],
                $boberdooResult['lead_id'],
                $row['buffer_lead_id']
            );
            $bufferStmt->execute();
            $bufferStmt->close();
        } else {
            $failed++;
            
            // Check if should retry
            if ($row['attempts'] < 3) {
                updateQueueStatus($mysqli, $row['id'], 'queued', $boberdooResult['error']);
            } else {
                updateQueueStatus($mysqli, $row['id'], 'failed', $boberdooResult['error']);
            }
        }
        
        $processed++;
        
        // Small delay between submissions
        usleep(500000); // 500ms
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'processed' => $processed,
        'successful' => $successful,
        'failed' => $failed,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Submit data to Boberdoo API
 */
function submitToBoberdoo($formData) {
    // Remove internal fields
    $fieldsToRemove = ['csrf_token', 'Redirect_URL'];
    foreach ($fieldsToRemove as $field) {
        unset($formData[$field]);
    }
    
    // Add required fields
    $formData['Format'] = 'JSON';
    
    // Clean phone number
    if (isset($formData['Primary_Phone'])) {
        $formData['Primary_Phone'] = preg_replace('/\D/', '', $formData['Primary_Phone']);
    }
    
    try {
        $ch = curl_init('https://infinixmedia.leadportal.com/genericPostlead.php');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($formData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            return [
                'success' => false,
                'error' => "cURL Error: $curlError",
                'response' => null,
                'lead_id' => null
            ];
        }
        
        if ($httpCode !== 200) {
            return [
                'success' => false,
                'error' => "HTTP Error: $httpCode",
                'response' => $response,
                'lead_id' => null
            ];
        }
        
        // Parse response
        $responseData = json_decode($response, true);
        if ($responseData) {
            return [
                'success' => isset($responseData['status']) && $responseData['status'] === 'success',
                'error' => $responseData['error'] ?? null,
                'response' => $response,
                'lead_id' => $responseData['lead_id'] ?? null
            ];
        } else {
            // Try XML parsing
            $xml = simplexml_load_string($response);
            if ($xml) {
                return [
                    'success' => (string)$xml->status === 'success',
                    'error' => (string)$xml->error,
                    'response' => $response,
                    'lead_id' => (string)$xml->lead_id
                ];
            }
        }
        
        return [
            'success' => false,
            'error' => 'Invalid response format',
            'response' => $response,
            'lead_id' => null
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'response' => null,
            'lead_id' => null
        ];
    }
}

/**
 * Update queue status
 */
function updateQueueStatus($mysqli, $queueId, $status, $errorMessage) {
    $stmt = $mysqli->prepare("
        UPDATE resubmission_queue 
        SET status = ?,
            error_message = ?,
            completed_at = CASE WHEN ? IN ('completed', 'failed') THEN NOW() ELSE NULL END
        WHERE id = ?
    ");
    
    $stmt->bind_param("sssi", $status, $errorMessage, $status, $queueId);
    $stmt->execute();
    $stmt->close();
}

/**
 * Get resubmission status for a lead
 */
function getResubmissionStatus($mysqli, $leadId) {
    if (!$leadId) {
        echo json_encode(['error' => 'Lead ID required']);
        return;
    }
    
    // Get lead and queue status
    $stmt = $mysqli->prepare("
        SELECT 
            lb.id,
            lb.email,
            lb.boberdoo_status,
            lb.resubmit_count,
            lb.last_resubmit_time,
            lb.resubmit_history,
            q.status as queue_status,
            q.priority,
            q.scheduled_time,
            q.attempts as queue_attempts
        FROM lead_buffer lb
        LEFT JOIN resubmission_queue q ON lb.id = q.buffer_lead_id
        WHERE lb.id = ?
    ");
    
    $stmt->bind_param("i", $leadId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'Lead not found']);
        return;
    }
    
    $data = $result->fetch_assoc();
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
}

/**
 * Log admin activity
 */
function logAdminActivity($mysqli, $actionType, $targetType, $targetId, $details) {
    try {
        $stmt = $mysqli->prepare("
            INSERT INTO admin_activity_log 
            (admin_user, action_type, target_type, target_id, action_details, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $adminUser = $_SESSION['admin_user'] ?? 'system';
        $detailsJson = json_encode($details);
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $stmt->bind_param("sssssss",
            $adminUser,
            $actionType,
            $targetType,
            $targetId,
            $detailsJson,
            $ipAddress,
            $userAgent
        );
        
        $stmt->execute();
        $stmt->close();
        
    } catch (Exception $e) {
        error_log("Admin activity log failed: " . $e->getMessage());
    }
}

$mysqli->close();
