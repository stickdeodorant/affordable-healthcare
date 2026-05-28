<?php
/**
 * Form Processing Script - FULLY CORRECTED VERSION
 * FIXES: Properly uses boberdoo-response-parser.php functions
 * This eliminates the pending status issue completely
 */

// Include required classes
require_once __DIR__ . '/classes/SessionManager.php';
require_once __DIR__ . '/classes/SecurityHelper.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/boberdoo-response-parser.php';  // Parser is included

// Initialize session and config
SessionManager::init();
$config = AppConfig::getInstance();

// Set error reporting based on environment
$environment = $config->get('environment', 'production');
if ($environment === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Set response header
header('Content-Type: application/json');

/**
 * Validate request method
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

/**
 * Validate CSRF token
 */
if (!SecurityHelper::validateCSRFToken($_POST['csrf_token'] ?? '')) {
    SecurityHelper::logSecurityEvent('Invalid CSRF token in form submission');
    echo json_encode(['error' => 'Invalid security token']);
    exit;
}

/**
 * Database Configuration - loaded from environment
 */
require_once __DIR__ . '/../../inc/env.php';
$dbConfig = [
    'host' => env('DB_HOST', 'localhost'),
    'user' => env('DB_USER', 'healthca_leads'),
    'pass' => env('DB_PASS', ''),
    'name' => env('DB_NAME', 'healthca_leads')
];

// Development override (if needed)
if ($environment === 'development') {
    // Override with development database if needed
}

/**
 * Validation Rules
 */
$requiredFields = [
    'Email' => 'email',
    'Primary_Phone' => 'phone',
    'First_Name' => 'name',
    'Last_Name' => 'name',
    'DOB' => 'string',
    'Gender' => 'alpha',
    'Zip' => 'zip',
    'City' => 'name',
    'State' => 'alpha'
];

/**
 * Validate and sanitize input
 */
$errors = [];
$sanitizedData = [];

foreach ($requiredFields as $field => $type) {
    if (empty($_POST[$field])) {
        $errors[] = "$field is required";
        continue;
    }
    
    $sanitizedData[$field] = SecurityHelper::sanitize($_POST[$field], $type);
    
    // Additional validation for email and phone
    if ($field === 'Email' && !SecurityHelper::validate($sanitizedData[$field], 'email')) {
        $errors[] = 'Invalid email format';
    }
    
    if ($field === 'Primary_Phone' && !SecurityHelper::validate($sanitizedData[$field], 'phone')) {
        $errors[] = 'Invalid phone number format';
    }
}

if (!empty($errors)) {
    SecurityHelper::logSecurityEvent('Validation failed', ['errors' => $errors]);
    echo json_encode(['error' => 'Validation failed', 'details' => $errors]);
    exit;
}

// Process phone number
$sanitizedData['Primary_Phone'] = preg_replace('/\D/', '', $sanitizedData['Primary_Phone']);

// Extract email and phone for easier access
$email = $sanitizedData['Email'];
$phone = $sanitizedData['Primary_Phone'];

// Generate unique lead ID
$leadId = 'LEAD-' . date('YmdHis') . '-' . mt_rand(100000, 999999) . '-' . substr(md5(uniqid(mt_rand(), true)), 0, 4);

/**
 * Connect to database
 */
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
    SecurityHelper::logSecurityEvent('Database connection failed', ['error' => $e->getMessage()]);
    echo json_encode(['error' => 'System error. Please try again later.']);
    exit;
}

/**
 * Check blacklist
 */
$isBlacklisted = false;

try {
    $stmt = $mysqli->prepare("
        SELECT block_until, submission_count, is_permanent, phone 
        FROM email_blacklist 
        WHERE email = ? OR phone = ?
    ");
    
    if ($stmt) {
        $stmt->bind_param("ss", $email, $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $blockUntil = new DateTime($row['block_until']);
            $now = new DateTime();
            
            if ($blockUntil > $now || $row['is_permanent']) {
                $isBlacklisted = true;
                $submissionCount = $row['submission_count'] + 1;
                $isPermanent = $row['is_permanent'] || ($submissionCount >= 5);
                
                // Update blacklist entry
                $updateStmt = $mysqli->prepare("
                    UPDATE email_blacklist 
                    SET block_until = DATE_ADD(NOW(), INTERVAL 8 HOUR),
                        submission_count = ?,
                        is_permanent = ?,
                        phone = ?
                    WHERE email = ? OR phone = ?
                ");
                
                $updateStmt->bind_param("iisss", $submissionCount, $isPermanent, $phone, $email, $phone);
                $updateStmt->execute();
                $updateStmt->close();
                
                // Send notification if permanently blacklisted
                if ($submissionCount >= 6 && $isPermanent) {
                    sendPermanentBlacklistNotification($email, $phone, $submissionCount);
                }
                
                SecurityHelper::logSecurityEvent('Blacklisted submission attempt', [
                    'email' => $email,
                    'phone' => $phone,
                    'submission_count' => $submissionCount,
                    'is_permanent' => $isPermanent
                ]);
                
                echo json_encode(['error' => 'This email address or phone number is temporarily blocked.']);
                $stmt->close();
                $mysqli->close();
                exit;
            }
        }
        
        $stmt->close();
    }
    
} catch (Exception $e) {
    SecurityHelper::logSecurityEvent('Blacklist check failed', ['error' => $e->getMessage()]);
    // Continue processing even if blacklist check fails
}

// Initialize buffer ID variable
$bufferId = null;

// Save to lead_buffer BEFORE Boberdoo submission
try {
    // Prepare data
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $formDataJson = json_encode($_POST);
    $expires = date('Y-m-d H:i:s', strtotime('+7 days'));
    
    // Extract additional fields
    $dob = isset($_POST['DOB']) ? $_POST['DOB'] : null;
    $age = $_POST['Age'] ?? null;
    $src = $_POST['SRC'] ?? $_POST['source'] ?? null;
    $campaign = $_POST['campaign'] ?? null;
    $subId1 = $_POST['Sub_ID1'] ?? $_POST['sub_id1'] ?? null;
    $subId2 = $_POST['Sub_ID2'] ?? $_POST['sub_id2'] ?? null;
    
    // Insert into buffer with status 'pending'
    $bufferStmt = $mysqli->prepare("
        INSERT INTO lead_buffer (
            lead_id,
            first_name,
            last_name,
            email,
            primary_phone,
            dob,
            age,
            gender,
            zip,
            city,
            state,
            src,
            campaign,
            sub_id1,
            sub_id2,
            ip_address,
            user_agent,
            form_data_json,
            boberdoo_status,
            expires_at,
            submission_time
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())
    ");
    
    if ($bufferStmt) {
        $bufferStmt->bind_param("ssssssissssssssssss",  // 19 characters for 19 parameters
            $leadId,
            $sanitizedData['First_Name'],
            $sanitizedData['Last_Name'],
            $sanitizedData['Email'],
            $sanitizedData['Primary_Phone'],
            $dob,
            $age,
            $sanitizedData['Gender'],
            $sanitizedData['Zip'],
            $sanitizedData['City'],
            $sanitizedData['State'],
            $src,
            $campaign,
            $subId1,
            $subId2,
            $ipAddress,
            $userAgent,
            $formDataJson,
            $expires
        );
        
        if ($bufferStmt->execute()) {
            $bufferId = $mysqli->insert_id;
            error_log("Lead saved to buffer: ID=$bufferId, LeadID=$leadId, Email=$email");
        } else {
            error_log("Buffer insert failed: " . $bufferStmt->error);
        }
        
        $bufferStmt->close();
    } else {
        error_log("Buffer prepare failed: " . $mysqli->error);
    }
    
} catch (Exception $e) {
    // Log error but don't stop processing
    error_log("Buffer save error: " . $e->getMessage());
    SecurityHelper::logSecurityEvent('Buffer save failed', ['error' => $e->getMessage()]);
}

/**
 * Claim TrustedForm certificate if present
 */
$trustedFormCertUrl = $_POST['LeadiD_URL'] ?? '';

if (!empty($trustedFormCertUrl) && filter_var($trustedFormCertUrl, FILTER_VALIDATE_URL)) {
    try {
        $ch = curl_init($trustedFormCertUrl);
        curl_setopt_array($ch, [
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => "API:" . env('TRUSTEDFORM_API_KEY'),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $trustedFormResponse = curl_exec($ch);
        $httpCodeTF = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCodeTF === 200 || $httpCodeTF === 201) {
            SecurityHelper::logSecurityEvent('TrustedForm certificate claimed', ['url' => $trustedFormCertUrl]);
        } else {
            SecurityHelper::logSecurityEvent('TrustedForm claim failed', [
                'url' => $trustedFormCertUrl,
                'http_code' => $httpCodeTF
            ]);
        }
        
    } catch (Exception $e) {
        error_log("TrustedForm error: " . $e->getMessage());
    }
}

/**
 * Prepare data for Boberdoo API
 */
$boberdooData = $_POST;
unset($boberdooData['csrf_token']);
unset($boberdooData['Redirect_URL']);

// Format data
$boberdooData['Format'] = 'JSON';
if (isset($boberdooData['Primary_Phone'])) {
    $boberdooData['Primary_Phone'] = preg_replace('/\D/', '', $boberdooData['Primary_Phone']);
}

/**
 * Submit to Boberdoo API
 */
$apiResponse = '';
$httpCode = null;

try {
    $ch = curl_init('https://infinixmedia.leadportal.com/genericPostlead.php');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($boberdooData),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_FOLLOWLOCATION => true
    ]);
    
    $apiResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        throw new Exception("cURL Error: " . $curlError);
    }
    
    error_log("Boberdoo API Response: HTTP $httpCode, Response: " . substr($apiResponse, 0, 200));
    
} catch (Exception $e) {
    SecurityHelper::logSecurityEvent('Boberdoo submission failed', [
        'error' => $e->getMessage(),
        'email' => $email
    ]);
    
    // Set empty response if curl failed
    $apiResponse = '';
    error_log("Boberdoo API Exception: " . $e->getMessage());
}

/**
 * CRITICAL FIX: Use the parser to process the response and update the database
 */
$parsedResponse = null;
$updateSuccess = false;

// Parse the Boberdoo response using the included parser
if (!empty($apiResponse)) {
    $parsedResponse = parseBoberdooResponse($apiResponse);
    error_log("Parsed Response: " . json_encode($parsedResponse));
} else {
    // Create a default error response if no API response
    $parsedResponse = [
        'status' => 'error',
        'lead_id' => null,
        'price' => null,
        'redirect_url' => null,
        'error_message' => 'No response from API',
        'raw_response' => '',
        'response_type' => 'none'
    ];
}

// Update the lead_buffer with the parsed response
if ($bufferId && $parsedResponse) {
    $updateSuccess = updateLeadBufferWithResponse($mysqli, $bufferId, $parsedResponse);
    
    if ($updateSuccess) {
        error_log("Successfully updated buffer ID $bufferId with status: " . $parsedResponse['status']);
    } else {
        error_log("Failed to update buffer ID $bufferId");
        
        // Fallback: Try manual update if the function failed
        try {
            $manualUpdate = $mysqli->prepare("
                UPDATE lead_buffer 
                SET boberdoo_status = ?,
                    boberdoo_response = ?,
                    boberdoo_lead_id = ?,
                    boberdoo_error = ?
                WHERE id = ?
            ");
            
            if ($manualUpdate) {
                $status = $parsedResponse['status'];
                $response = $parsedResponse['raw_response'];
                $leadIdFromAPI = $parsedResponse['lead_id'];
                $errorMsg = $parsedResponse['error_message'];
                
                $manualUpdate->bind_param("ssssi", $status, $response, $leadIdFromAPI, $errorMsg, $bufferId);
                $manualUpdate->execute();
                $manualUpdate->close();
                
                error_log("Manual update fallback executed for buffer ID $bufferId");
            }
        } catch (Exception $e) {
            error_log("Manual update fallback failed: " . $e->getMessage());
        }
    }
} else {
    error_log("Cannot update buffer: bufferId=$bufferId, parsedResponse=" . json_encode($parsedResponse));
}

/**
 * Log to API response log
 */
try {
    $logStmt = $mysqli->prepare("
        INSERT INTO api_response_log (lead_id, api_name, response_code, response_body, created_at)
        VALUES (?, 'boberdoo', ?, ?, NOW())
    ");
    
    if ($logStmt) {
        $logStmt->bind_param("sis", $leadId, $httpCode, $apiResponse);
        $logStmt->execute();
        $logStmt->close();
    }
} catch (Exception $e) {
    error_log("API log error: " . $e->getMessage());
}

/**
 * Save to lead_history (Permanent Historical Storage)
 */
try {
    $historyStmt = $mysqli->prepare("
        INSERT INTO lead_history (
            lead_id, email, phone, first_name, last_name,
            age, gender, city, state, zip,
            source, campaign, type,
            ip_address, user_agent,
            boberdoo_status, boberdoo_response_code, boberdoo_lead_id,
            boberdoo_error_message, boberdoo_price, boberdoo_buyer,
            is_blacklisted, response_time_ms
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?,
            ?, ?, ?,
            ?, ?, ?,
            ?, ?
        )
    ");
    
    if ($historyStmt) {
        // Use the parsed response for accurate status
        $historyStatus = $parsedResponse ? $parsedResponse['status'] : 'error';
        $boberdooLeadId = $parsedResponse ? $parsedResponse['lead_id'] : null;
        $boberdooError = $parsedResponse ? $parsedResponse['error_message'] : null;
        $boberdooPrice = $parsedResponse ? $parsedResponse['price'] : null;
        $responseTimeMs = 0; // Can add timing if needed
        $boberdooBuyer = null; // Not captured in original
        
        $historyStmt->bind_param("sssssisssssssssssssdsi",
            $leadId,
            $email,
            $phone,
            $sanitizedData['First_Name'],
            $sanitizedData['Last_Name'],
            $age,
            $sanitizedData['Gender'],
            $sanitizedData['City'],
            $sanitizedData['State'],
            $sanitizedData['Zip'],
            $src,
            $campaign,
            $_POST['TYPE'] ?? null,
            $ipAddress,
            $userAgent,
            $historyStatus,
            $httpCode,
            $boberdooLeadId,
            $boberdooError,
            $boberdooPrice,
            $boberdooBuyer,
            $isBlacklisted,
            $responseTimeMs
        );
        
        if ($historyStmt->execute()) {
            error_log("Lead saved to history: LeadID=$leadId, Status=$historyStatus");
        } else {
            error_log("History insert failed: " . $historyStmt->error);
        }
        
        $historyStmt->close();
    }
} catch (Exception $e) {
    error_log("Lead history save error: " . $e->getMessage());
    // Continue processing - don't stop user flow
}

/**
 * Update blacklist entry
 */
try {
    $stmt = $mysqli->prepare("
        INSERT INTO email_blacklist (email, phone, block_until, submission_count, is_permanent) 
        VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 8 HOUR), 1, FALSE) 
        ON DUPLICATE KEY UPDATE 
            block_until = DATE_ADD(NOW(), INTERVAL 8 HOUR),
            submission_count = IF(is_permanent, submission_count, submission_count + 1),
            is_permanent = IF(submission_count >= 5, TRUE, is_permanent),
            phone = VALUES(phone)
    ");
    
    $stmt->bind_param("ss", $email, $phone);
    $stmt->execute();
    $stmt->close();
    
} catch (Exception $e) {
    SecurityHelper::logSecurityEvent('Blacklist update failed', ['error' => $e->getMessage()]);
}

// Close database connection
$mysqli->close();

/**
 * Prepare redirect URL with parameters
 */
$redirectUrl = $_POST['Redirect_URL'] ?? '/thank-you/thank-you-h3-b.php';

// Build query parameters for thank you page
$redirectParams = [
    'type' => 'healthcare',
    'city' => $sanitizedData['City'] ?? SessionManager::getCity(),
    'state' => $sanitizedData['State'] ?? SessionManager::getState(),
    'age' => $_POST['Age'] ?? '',
    'first_name' => $sanitizedData['First_Name'] ?? '',
    'lead_id' => $leadId // Include lead ID for tracking
];

// Check success based on parsed response
$isSuccess = ($parsedResponse && $parsedResponse['status'] === 'success');

// Add submission status
if ($isSuccess) {
    $redirectParams['status'] = 'success';
    // Clear form data from session after successful submission
    SessionManager::clearFormData();
} else {
    $redirectParams['status'] = 'pending';
}

// Build full redirect URL
$separator = (strpos($redirectUrl, '?') === false) ? '?' : '&';
$fullRedirectUrl = $redirectUrl . $separator . http_build_query($redirectParams);

/**
 * Return response
 */
if ($isSuccess) {
    echo json_encode([
        'success' => true,
        'message' => 'Form processed successfully',
        'redirect' => $fullRedirectUrl,
        'lead_id' => $leadId,
        'boberdoo_id' => $parsedResponse['lead_id']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Form saved but submission pending',
        'redirect' => $fullRedirectUrl,
        'lead_id' => $leadId,
        'error' => $parsedResponse ? $parsedResponse['error_message'] : 'Unknown error'
    ]);
}

/**
 * Helper function to send permanent blacklist notification
 */
function sendPermanentBlacklistNotification($email, $phone, $submissionCount) {
    $apiKey = 'Bearer ' . env('MAILTRAP_TOKEN', '');
    $notificationEmail = env('MAILTRAP_RECIPIENT', 'kelliott@infinixmedia.com');
    
    $payload = [
        "from" => ["email" => "no-reply@healthcare-insurance.com", "name" => "Healthcare Insurance"],
        "to" => [["email" => $notificationEmail]],
        "subject" => "Permanent Blacklist Notification",
        "text" => sprintf(
            "The email address %s and phone number %s have been permanently blacklisted after %d submissions.",
            $email, $phone, $submissionCount
        ),
        "category" => "Blacklisted Emails"
    ];
    
    try {
        $ch = curl_init('https://send.api.mailtrap.io/api/send');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Authorization: ' . $apiKey, 'Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        curl_exec($ch);
        curl_close($ch);
    } catch (Exception $e) {
        // Silent fail for notifications
    }
}

// Script ends here - no emergency fix needed!
exit;