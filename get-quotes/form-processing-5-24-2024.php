<?php
require_once __DIR__ . '/../inc/env.php';

ini_set('display_errors', is_local() ? 1 : 0);
error_reporting(is_local() ? E_ALL : E_ALL & ~E_NOTICE); 
/**********************************************************
 * 
 * Checking Email Isn't Blacklisted 
 * We add all emails submitted to a database with a decaying cooldown period.
 * The cooldown is reset each time the email address is resubmitted.
 * 
 **********************************************************/

// Database configuration from environment
$dbHost = env('DB_HOST', 'localhost');
$dbUser = env('DB_USER', 'healthca_leads');
$dbPass = env('DB_PASS', '');
$dbName = env('DB_NAME', 'healthca_leads');

// Create connection
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Create connection
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// Check connection
if ($mysqli->connect_error) {
    die(json_encode(['error' => "Connection failed: " . $mysqli->connect_error]));
}

// Ensure email is provided
if (empty($_POST['Email'])) {
    echo json_encode(['error' => 'Email address is required.']);
    exit;
}

$email = $_POST['Email'];
$phone = $_POST['Primary_Phone'];

// Prepare the statement to check the blacklist status
$stmt = $mysqli->prepare("SELECT block_until, submission_count, is_permanent, phone FROM email_blacklist WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Check if the email is still within its blacklist period or is permanently blacklisted
    if (new DateTime($row['block_until']) > new DateTime() || $row['is_permanent']) {
        // Increment the submission count
        $submission_count = $row['submission_count'] + 1;
        $is_permanent = $row['is_permanent'];

        if ($submission_count >= 5) {
            $is_permanent = true; // Make the blacklist permanent
        }

        // Update the blacklist entry to include phone number
        $updateStmt = $mysqli->prepare("UPDATE email_blacklist SET block_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE), submission_count = ?, is_permanent = ?, phone = ? WHERE email = ?");
        $updateStmt->bind_param("iiss", $submission_count, $is_permanent, $phone, $email);
        $updateStmt->execute();
        $updateStmt->close();
        
        if ($submission_count >= 6 && $is_permanent == true) {
            sendPermanentBlacklistEmail($email, $phone, $submission_count);
        }
        echo json_encode(['error' => 'This email address is temporarily blocked.']);
        exit;
    }
}

// For new submissions or if the blacklist has expired and is not permanent
// Insert or update the blacklist entry for this email including the phone number
$stmt = $mysqli->prepare("INSERT INTO email_blacklist (email, phone, block_until, submission_count, is_permanent) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE), 1, FALSE) ON DUPLICATE KEY UPDATE block_until = DATE_ADD(NOW(), INTERVAL 5 MINUTE), submission_count = IF(is_permanent, submission_count, submission_count + 1), is_permanent = IF(submission_count >= 5, TRUE, is_permanent), phone = ?");
$stmt->bind_param("ssss", $email, $phone, $phone);
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
$postdata = http_build_query( $data);

$cURLConnection = curl_init('https://infinixmedia.leadportal.com/genericPostlead.php?');
curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $postdata);
curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

$apiResponse = curl_exec($cURLConnection);
curl_close($cURLConnection);


//echo $postdata;
echo $apiResponse;
print_r($apiResponse);
//die;
// if(!$apiResponse) {
//     die("Connection Failure");
// } else {
//   header("Location: ". $_POST['Redirect_URL']);
// }


/*********************************************************
 * 
 * Add Submission to Blacklist
 * // Insert or update the blacklist entry for this email if it's a new submission
 * 
 *********************************************************/

// Email Blacklisted Addresses
function sendPermanentBlacklistEmail($email, $phone, $submission_count) {
    // Mailtrap API endpoint
    $url = 'https://send.api.mailtrap.io/api/send';
    $formattedPhone = preg_replace('/\D/', '', $phone);
    
    // Prepare the payload
    $payload = json_encode([
        "from" => [
            "email" => "no-reply@healthcare-insurance.com",
            "name" => "Healthcare Insurance"
        ],
        "to" => [
            ["email" => "mgeorge@infinixmedia.com"] // Add a recipient
        ],
        "subject" => "Permanent Blacklist Notification",
        "text" => "Notification: The email address $email and phone number $formattedPhone have been permanently blacklisted after $submission_count submissions.",
        "category" => "Blacklisted Emails"
    ]);
    
    // Initialize cURL
    $ch = curl_init($url);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . env('MAILTRAP_TOKEN'),
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    
    // Execute the request
    $result = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    
    // Check for errors and print the results
    if ($err) {
        echo "cURL Error: " . $err;
    } else {
        echo "Message has been sent. Response: " . $result;
    }
}

 echo json_encode(['success' => 'Form processed successfully.']);

 $mysqli->close();

?>