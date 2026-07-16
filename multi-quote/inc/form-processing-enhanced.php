<?php
/**
 * Enhanced Form Processing Script with Lead Buffer & Historical Storage
 * Handles form submission, blacklist checking, API posting, buffer storage, and redirection
 */

// Include required classes
require_once __DIR__ . '/classes/SessionManager.php';
require_once __DIR__ . '/classes/SecurityHelper.php';
require_once __DIR__ . '/config/app.php';

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

// Development override no longer needed - use different .env files

/**
 * Generate unique lead ID
 */
function generateLeadId() {
    return 'LEAD-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));
}

/**
 * Validate required fields
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

$errors = [];
$sanitizedData = [];

foreach ($requiredFields as $field => $type) {
    if (empty($_POST[$field])) {
        $errors[] = "$field is required";
    } else {
        $sanitizedData[$field] = SecurityHelper::sanitize($_POST[$field], $type);
        
        // Additional validation
        if ($field === 'Email' && !SecurityHelper::validate($sanitizedData[$field], 'email')) {
            $errors[] = 'Invalid email format';
        }
        if ($field === 'Primary_Phone' && !SecurityHelper::validate($sanitizedData[$field], 'phone')) {
            $errors[] = 'Invalid phone number format';
        }
    }
}

if (!empty($errors)) {
    echo json_encode(['error' => 'Validation failed', 'details' => $errors]);
    exit;
}

/**
 * Process phone number (remove formatting)
 */
$sanitizedData['Primary_Phone'] = preg_replace('/\D/', '', $sanitizedData['Primary_Phone']);

$ageValue = null;
if (!empty($_POST['Age']) && is_numeric($_POST['Age'])) {
    $ageValue = (int)$_POST['Age'];
} elseif (!empty($_POST['DOB'])) {
    $dobTs = strtotime((string)$_POST['DOB']);
    if ($dobTs !== false) {
        $ageValue = (int)floor((time() - $dobTs) / 31556926);
    }
}

$isSeniorLead = ($ageValue !== null && $ageValue >= 65);
$normalizedType = $isSeniorLead ? '23' : '24';
$normalizedSrc = $isSeniorLead ? 'WebPostM' : 'Infinix-M-Out';

$_POST['TYPE'] = $normalizedType;
$_POST['SRC'] = $normalizedSrc;

/**
 * Capture additional data
 */
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$leadId = generateLeadId();
$submissionStartTime = microtime(true);

/**
 * Database connection
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
    
    // Set charset to prevent injection attacks
    $mysqli->set_charset("utf8mb4");
    
} catch (Exception $e) {
    SecurityHelper::logSecurityEvent('Database connection failed', ['error' => $e->getMessage()]);
    echo json_encode(['error' => 'System error. Please try again later.']);
    exit;
}

/**
 * Check blacklist status
 */
$email = $sanitizedData['Email'];
$phone = $sanitizedData['Primary_Phone'];
$isBlacklisted = false;
$blacklistReason = '';

try {
    // Check if email or phone is blacklisted
    $stmt = $mysqli->prepare("
        SELECT block_until, submission_count, is_permanent, phone,
               blacklist_reason, ip_addresses, whitelist_override
        FROM email_blacklist 
        WHERE (email = ? OR phone = ?) AND whitelist_override = FALSE
    ");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $mysqli->error);
    }
    
    $stmt->bind_param("ss", $email, $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $blockUntil = new DateTime($row['block_until']);
        $now = new DateTime();
        
        // Check if still blacklisted
        if ($blockUntil > $now || $row['is_permanent']) {
            $isBlacklisted = true;
            $blacklistReason = $row['blacklist_reason'] ?? 'Multiple submission attempts';
            $submissionCount = $row['submission_count'] + 1;
            $isPermanent = $row['is_permanent'] || ($submissionCount >= 5);
            
            // Update IP addresses array
            $ipAddresses = json_decode($row['ip_addresses'] ?? '[]', true);
            if (!in_array($ipAddress, $ipAddresses)) {
                $ipAddresses[] = $ipAddress;
            }
            
            // Update blacklist entry
            $updateStmt = $mysqli->prepare("
                UPDATE email_blacklist 
                SET block_until = DATE_ADD(NOW(), INTERVAL 8 HOUR),
                    submission_count = ?,
                    is_permanent = ?,
                    phone = ?,
                    last_attempt_date = NOW(),
                    total_lifetime_attempts = total_lifetime_attempts + 1,
                    ip_addresses = ?,
                    first_blacklisted_date = COALESCE(first_blacklisted_date, NOW())
                WHERE email = ? OR phone = ?
            ");
            
            $ipAddressesJson = json_encode($ipAddresses);
            $updateStmt->bind_param("iissss", 
                $submissionCount, 
                $isPermanent, 
                $phone, 
                $ipAddressesJson,
                $email, 
                $phone
            );
            $updateStmt->execute();
            $updateStmt->close();
            
            // Send notification if permanently blacklisted
            if ($submissionCount >= 6 && $isPermanent) {
                sendPermanentBlacklistNotification($email, $phone, $submissionCount);
            }
            
            SecurityHelper::logSecurityEvent('Blacklisted submission attempt', [
                'email' => $email,
                'phone' => $phone,
                'count' => $submissionCount
            ]);
        }
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    SecurityHelper::logSecurityEvent('Blacklist check failed', ['error' => $e->getMessage()]);
    // Continue processing even if blacklist check fails
}

/**
 * Save to Lead Buffer (7-day storage) - ALWAYS save, even if blacklisted
 */
try {
    // Prepare complete form data as JSON
    $formDataJson = json_encode($_POST);
    
    $bufferStmt = $mysqli->prepare("
        INSERT INTO lead_buffer (
            lead_id, first_name, last_name, email, primary_phone,
            dob, age, gender, zip, city, state, address,
            household, household_income, currently_insured, urgency, reason,
            src, type, campaign, sub_id1, sub_id2,
            ip_address, user_agent, form_data_json,
            boberdoo_status, marked_for_resubmit
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?
        )
    ");
    
    // Set initial status based on blacklist
    $boberdooStatus = $isBlacklisted ? 'error' : 'pending';
    $markedForResubmit = false;
    
    $bufferStmt->bind_param("ssssssissssssssssssssssssi",
        $leadId,
        $sanitizedData['First_Name'],
        $sanitizedData['Last_Name'],
        $sanitizedData['Email'],
        $sanitizedData['Primary_Phone'],
        $_POST['DOB'] ?? null,
        $_POST['Age'] ?? null,
        $sanitizedData['Gender'],
        $sanitizedData['Zip'],
        $sanitizedData['City'],
        $sanitizedData['State'],
        $_POST['Address'] ?? null,
        $_POST['Household'] ?? null,
        $_POST['Household_Income'] ?? null,
        $_POST['Currently_Insured'] ?? null,
        $_POST['Urgency'] ?? null,
        $_POST['Reason'] ?? null,
        $normalizedSrc,
        $normalizedType,
        $_POST['Campaign'] ?? null,
        $_POST['Sub_ID1'] ?? null,
        $_POST['Sub_ID2'] ?? null,
        $ipAddress,
        $userAgent,
        $formDataJson,
        $boberdooStatus,
        $markedForResubmit
    );
    
    $bufferStmt->execute();
    $bufferId = $mysqli->insert_id;
    $bufferStmt->close();
    
} catch (Exception $e) {
    SecurityHelper::logSecurityEvent('Lead buffer save failed', ['error' => $e->getMessage()]);
}

/**
 * TrustedForm Certificate Claiming (if enabled and not blacklisted)
 */
if (!$isBlacklisted) {
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
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                SecurityHelper::logSecurityEvent('TrustedForm claim failed', [
                    'url' => $trustedFormCertUrl,
                    'http_code' => $httpCode
                ]);
            }
        } catch (Exception $e) {
            SecurityHelper::logSecurityEvent('TrustedForm error', ['error' => $e->getMessage()]);
        }
    }
}

/**
 * Submit to Boberdoo API (only if not blacklisted)
 */
$boberdooSuccess = false;
$boberdooResponse = null;
$boberdooLeadId = null;
$boberdooError = null;
$boberdooPrice = null;
$boberdooBuyer = null;
$responseTimeMs = null;

if (!$isBlacklisted) {
    // Prepare data for Boberdoo submission
    $boberdooData = $_POST;
    
    // Remove internal fields
    $fieldsToRemove = ['csrf_token', 'Redirect_URL'];
    foreach ($fieldsToRemove as $field) {
        unset($boberdooData[$field]);
    }
    
    // Add required Boberdoo fields
    $boberdooData['Format'] = 'JSON';
    $boberdooData['TYPE'] = $normalizedType;
    $boberdooData['SRC'] = $normalizedSrc;
    
    // Clean phone number for Boberdoo
    if (isset($boberdooData['Primary_Phone'])) {
        $boberdooData['Primary_Phone'] = preg_replace('/\D/', '', $boberdooData['Primary_Phone']);
    }
    
    try {
        $apiStartTime = microtime(true);
        
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
        
        $apiEndTime = microtime(true);
        $responseTimeMs = round(($apiEndTime - $apiStartTime) * 1000);
        
        if ($curlError) {
            throw new Exception("cURL Error: " . $curlError);
        }
        
        if ($httpCode === 200 && $apiResponse) {
            $boberdooResponse = $apiResponse;
            
            // Try to parse response
            $responseData = json_decode($apiResponse, true);
            if ($responseData) {
                $boberdooSuccess = isset($responseData['status']) && $responseData['status'] === 'success';
                $boberdooLeadId = $responseData['lead_id'] ?? null;
                $boberdooError = $responseData['error'] ?? null;
                $boberdooPrice = $responseData['price'] ?? null;
                $boberdooBuyer = $responseData['buyer'] ?? null;
            } else {
                // Try XML parsing for legacy responses
                $xml = simplexml_load_string($apiResponse);
                if ($xml) {
                    $boberdooSuccess = (string)$xml->status === 'success';
                    $boberdooLeadId = (string)$xml->lead_id;
                    $boberdooError = (string)$xml->error;
                }
            }
            
            // Log successful submission
            SecurityHelper::logSecurityEvent('Boberdoo submission', [
                'email' => $email,
                'success' => $boberdooSuccess,
                'lead_id' => $boberdooLeadId
            ]);
        } else {
            throw new Exception("API returned HTTP $httpCode");
        }
        
    } catch (Exception $e) {
        SecurityHelper::logSecurityEvent('Boberdoo submission failed', [
            'error' => $e->getMessage(),
            'email' => $email
        ]);
        
        $boberdooError = $e->getMessage();
        $boberdooSuccess = false;
    }
    
    // Update buffer with Boberdoo response
    if (isset($bufferId)) {
        $updateBufferStmt = $mysqli->prepare("
            UPDATE lead_buffer 
            SET boberdoo_status = ?,
                boberdoo_response = ?,
                boberdoo_lead_id = ?,
                boberdoo_error = ?
            WHERE id = ?
        ");
        
        $status = $boberdooSuccess ? 'success' : 'error';
        $updateBufferStmt->bind_param("ssssi",
            $status,
            $boberdooResponse,
            $boberdooLeadId,
            $boberdooError,
            $bufferId
        );
        $updateBufferStmt->execute();
        $updateBufferStmt->close();
    }
}

/**
 * Save to Historical Lead Data
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
    
    $historyStatus = $isBlacklisted ? 'rejected' : ($boberdooSuccess ? 'success' : 'error');
    
    $historyStmt->bind_param("sssssisssssssssssssdsi",
        $leadId,
        $email,
        $phone,
        $sanitizedData['First_Name'],
        $sanitizedData['Last_Name'],
        $_POST['Age'] ?? null,
        $sanitizedData['Gender'],
        $sanitizedData['City'],
        $sanitizedData['State'],
        $sanitizedData['Zip'],
        $normalizedSrc,
        $_POST['Campaign'] ?? null,
        $normalizedType,
        $ipAddress,
        $userAgent,
        $historyStatus,
        $httpCode ?? null,
        $boberdooLeadId,
        $boberdooError,
        $boberdooPrice,
        $boberdooBuyer,
        $isBlacklisted,
        $responseTimeMs
    );
    
    $historyStmt->execute();
    $historyStmt->close();
    
} catch (Exception $e) {
    SecurityHelper::logSecurityEvent('Historical data save failed', ['error' => $e->getMessage()]);
}

/**
 * Update or create blacklist entry (for all submissions)
 */
if (!$isBlacklisted) {
    try {
        $stmt = $mysqli->prepare("
            INSERT INTO email_blacklist (
                email, phone, block_until, submission_count, 
                is_permanent, first_blacklisted_date, last_attempt_date,
                total_lifetime_attempts, ip_addresses
            ) 
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 8 HOUR), 1, FALSE, NOW(), NOW(), 1, ?) 
            ON DUPLICATE KEY UPDATE 
                block_until = DATE_ADD(NOW(), INTERVAL 8 HOUR),
                submission_count = IF(is_permanent, submission_count, submission_count + 1),
                is_permanent = IF(submission_count >= 5, TRUE, is_permanent),
                phone = VALUES(phone),
                last_attempt_date = NOW(),
                total_lifetime_attempts = total_lifetime_attempts + 1,
                ip_addresses = JSON_MERGE_PRESERVE(COALESCE(ip_addresses, '[]'), VALUES(ip_addresses))
        ");
        
        $ipAddressJson = json_encode([$ipAddress]);
        $stmt->bind_param("sss", $email, $phone, $ipAddressJson);
        $stmt->execute();
        $stmt->close();
        
    } catch (Exception $e) {
        SecurityHelper::logSecurityEvent('Blacklist update failed', ['error' => $e->getMessage()]);
    }
}

// Close database connection
$mysqli->close();

/**
 * Log admin activity if applicable
 */
if (isset($_SESSION['admin_user'])) {
    logAdminActivity($_SESSION['admin_user'], 'form_submission', 'lead', $leadId, [
        'email' => $email,
        'success' => $boberdooSuccess,
        'blacklisted' => $isBlacklisted
    ]);
}

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
    'did' => $_POST['did'] ?? 'standard',
    'src' => base64_encode($normalizedSrc)
];

// Add submission status
if ($isBlacklisted) {
    $redirectParams['status'] = 'blacklisted';
} elseif ($boberdooSuccess) {
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
$responseData = [
    'success' => !$isBlacklisted && $boberdooSuccess,
    'message' => $isBlacklisted ? 'This email/phone is temporarily blocked.' : 
                ($boberdooSuccess ? 'Form processed successfully' : 'Form saved but submission pending'),
    'redirect' => $fullRedirectUrl,
    'lead_id' => $leadId,
    'boberdoo_response' => $boberdooResponse
];

if ($isBlacklisted) {
    $responseData['blacklisted'] = true;
    $responseData['blacklist_reason'] = $blacklistReason;
}

echo json_encode($responseData);

/**
 * Helper Functions
 */
function sendPermanentBlacklistNotification($email, $phone, $submissionCount) {
    global $config;
    
    $apiKey = 'Bearer ' . env('MAILTRAP_TOKEN', '');
    $notificationEmail = env('MAILTRAP_RECIPIENT', 'kelliott@infinixmedia.com');
    
    $payload = [
        "from" => [
            "email" => "no-reply@affordable-healthcare.com",
            "name" => "Affordable Healthcare"
        ],
        "to" => [
            ["email" => $notificationEmail]
        ],
        "subject" => "Permanent Blacklist Notification",
        "text" => sprintf(
            "The email address %s and phone number %s have been permanently blacklisted after %d submissions.\n\n" .
            "Time: %s\n" .
            "IP Address: %s\n\n" .
            "Please review in the dashboard.",
            $email,
            $phone,
            $submissionCount,
            date('Y-m-d H:i:s'),
            $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        ),
        "category" => "Blacklisted Emails"
    ];
    
    try {
        $ch = curl_init('https://send.api.mailtrap.io/api/send');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $apiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            SecurityHelper::logSecurityEvent('Blacklist notification failed', [
                'http_code' => $httpCode,
                'email' => $email
            ]);
        }
        
    } catch (Exception $e) {
        SecurityHelper::logSecurityEvent('Blacklist notification error', [
            'error' => $e->getMessage()
        ]);
    }
}

function logAdminActivity($adminUser, $actionType, $targetType, $targetId, $details) {
    global $mysqli;
    
    try {
        $stmt = $mysqli->prepare("
            INSERT INTO admin_activity_log 
            (admin_user, action_type, target_type, target_id, action_details, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
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
        // Log but don't fail the main process
        error_log("Admin activity log failed: " . $e->getMessage());
    }
}

// Exit to prevent any additional output
exit;
