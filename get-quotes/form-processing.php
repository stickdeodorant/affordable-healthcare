<?php
require_once __DIR__ . '/../inc/env.php';

$appEnv = env('APP_ENV', 'production');
$appDebug = env_bool('APP_DEBUG', $appEnv !== 'production');

ini_set('display_errors', $appDebug ? '1' : '0');
error_reporting($appDebug ? E_ALL : E_ALL & ~E_NOTICE);
header('Content-Type: application/json');
/**********************************************************
 * 
 * Checking Email Isn't Blacklisted 
 * We add all emails submitted to a database with a decaying cooldown period.
 * The cooldown is reset each time the email address is resubmitted.
 * 
 **********************************************************/

// Database configuration (env overrides, legacy defaults for production safety)
$dbHost = env('DB_HOST', 'localhost');
$dbUser = env('DB_USER', 'afford_leads');
$dbPass = env('DB_PASS', '');
$dbName = env('DB_NAME', 'afford_leads');

$boberdooUrl = env('BOBERDOO_URL', 'https://infinixmedia.leadportal.com/genericPostlead.php?');
$enableLeadPost = env_bool('ENABLE_BOBERDOO_POST', true);

// Create connection
$mysqli = null;
mysqli_report(MYSQLI_REPORT_OFF);
try {
    $mysqli = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
} catch (Throwable $e) {
    $message = $appDebug ? ('Database connection failed: ' . $e->getMessage()) : 'Database connection failed';
    echo json_encode(['error' => $message]);
    exit;
}

// Check connection
if (!$mysqli || $mysqli->connect_errno) {
    $message = $appDebug ? ('Connection failed: ' . ($mysqli ? $mysqli->connect_error : 'unknown')) : 'Database connection failed';
    echo json_encode(['error' => $message]);
    exit;
}

// Ensure email and phone number are provided
if (empty($_POST['Email']) || empty($_POST['Primary_Phone'])) {
    echo json_encode(['error' => 'Email address and phone number are required.']);
    exit;
}

$email = $_POST['Email'];
$phone = $_POST['Primary_Phone'];

// Prepare the statement to check the blacklist status for both email and phone
$stmt = $mysqli->prepare("SELECT block_until, submission_count, is_permanent, phone FROM email_blacklist WHERE email = ? OR phone = ?");
$stmt->bind_param("ss", $email, $phone);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Check if the email or phone is still within its blacklist period or is permanently blacklisted
    if (new DateTime($row['block_until']) > new DateTime() || $row['is_permanent']) {
        // Increment the submission count
        $submission_count = $row['submission_count'] + 1;
        $is_permanent = $row['is_permanent'];

        if ($submission_count >= 5) {
            $is_permanent = true; // Make the blacklist permanent
        }

        // Update the blacklist entry to include phone number
        $updateStmt = $mysqli->prepare("UPDATE email_blacklist SET block_until = DATE_ADD(NOW(), INTERVAL 8 HOUR), submission_count = ?, is_permanent = ?, phone = ? WHERE email = ? OR phone = ?");
        $updateStmt->bind_param("iisss", $submission_count, $is_permanent, $phone, $email, $phone);
        $updateStmt->execute();
        $updateStmt->close();
        
        if ($submission_count >= 6 && $is_permanent == true) {
            sendPermanentBlacklistEmail($email, $phone, $submission_count);
        }
        echo json_encode(['error' => 'This email address or phone number is temporarily blocked.']);
        exit;
    }
}

// For new submissions or if the blacklist has expired and is not permanent
// Insert or update the blacklist entry for this email and phone number
$stmt = $mysqli->prepare("INSERT INTO email_blacklist (email, phone, block_until, submission_count, is_permanent) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 8 HOUR), 1, FALSE) ON DUPLICATE KEY UPDATE block_until = DATE_ADD(NOW(), INTERVAL 8 HOUR), submission_count = IF(is_permanent, submission_count, submission_count + 1), is_permanent = IF(submission_count >= 5, TRUE, is_permanent), phone = ?");
$stmt->bind_param("sss", $email, $phone, $phone);
$stmt->execute();
$stmt->close();



/**********************************************************
 * 
 * Claiming Trusted Form Certificate
 * 
 **********************************************************/

// $url = $_POST['LeadiD_URL'];;
// $postfields = [];

// $cURLConnection = curl_init($url);
// curl_setopt($cURLConnection, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
// curl_setopt($cURLConnection, CURLOPT_USERPWD, "API:" . env('TRUSTEDFORM_API_KEY'));
// curl_setopt($cURLConnection, CURLOPT_HEADER, 'Content-type: application/json');
// curl_setopt($cURLConnection, CURLOPT_POST, true);
// curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $postfields);
// curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

// $trustedFormResponse = curl_exec($cURLConnection);
// curl_close($cURLConnection);


/**********************************************************
 * 
 * Posting Submission to Boberdoo
 * 
 **********************************************************/

$data = $_POST;
unset($data['Redirect_URL']);

$leadType = null;
if (isset($data['TYPE']) && $data['TYPE'] !== '') {
    $leadType = $data['TYPE'];
} elseif (isset($data['Type']) && $data['Type'] !== '') {
    $leadType = $data['Type'];
} elseif (isset($data['lead_type']) && $data['lead_type'] !== '') {
    $leadType = $data['lead_type'];
} else {
    $leadType = env('BOBERDOO_DEFAULT_TYPE', '24');
}

$data['TYPE'] = (string)$leadType;
unset($data['Type'], $data['lead_type']);

$data = array_merge([
    'Format' => 'JSON',
], $data);
$postdata = http_build_query($data);

if ($enableLeadPost) {
    if (empty($boberdooUrl)) {
        error_log('form-processing.php error: BOBERDOO_URL is empty');
        echo json_encode(['error' => 'Lead service URL is not configured']);
        $mysqli->close();
        exit;
    }

    $cURLConnection = curl_init($boberdooUrl);
    curl_setopt($cURLConnection, CURLOPT_POST, true);
    curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $postdata);
    curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($cURLConnection, CURLOPT_TIMEOUT, 30);
    curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYPEER, 1);

    $apiResponse = curl_exec($cURLConnection);
    if ($apiResponse === false) {
        $curlError = curl_error($cURLConnection);
        error_log('form-processing.php cURL error: ' . $curlError);
        curl_close($cURLConnection);
        echo json_encode(['error' => 'Unable to reach lead API service']);
        $mysqli->close();
        exit;
    }

    curl_close($cURLConnection);
} else {
    $apiResponse = json_encode([
        'status' => 'skipped',
        'reason' => 'Lead post disabled for this environment',
        'environment' => $appEnv,
    ]);
}

echo $apiResponse;


/*********************************************************
 * 
 * Add Submission to Blacklist
 * // Insert or update the blacklist entry for this email if it's a new submission
 * 
 *********************************************************/

// Email Blacklisted Addresses
function sendPermanentBlacklistEmail($email, $phone, $submission_count) {
    if (!env_bool('ENABLE_MAIL', true)) {
        return;
    }

    $token = env('MAILTRAP_TOKEN');
    if (!$token) {
        return;
    }

    $url = 'https://send.api.mailtrap.io/api/send';
    $formattedPhone = preg_replace('/\D/', '', $phone);

    $payload = json_encode([
        'from' => [
            'email' => env('MAILTRAP_FROM_EMAIL', 'no-reply@affordable-healthcare.com'),
            'name' => env('MAILTRAP_FROM_NAME', 'Affordable Healthcare'),
        ],
        'to' => [
            ['email' => env('MAILTRAP_RECIPIENT', 'kelliott@infinixmedia.com')],
        ],
        'subject' => 'Permanent Blacklist Notification',
        'text' => "Notification: The email address $email and phone number $formattedPhone have been permanently blacklisted after $submission_count submissions.",
        'category' => 'Blacklisted Emails',
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $result = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        error_log('sendPermanentBlacklistEmail cURL Error: ' . $err);
    } else {
        error_log('sendPermanentBlacklistEmail success response: ' . $result);
    }
}

$mysqli->close();

?>