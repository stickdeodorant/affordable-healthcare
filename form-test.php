<?php
require_once __DIR__ . '/inc/feature-flags.php';
if (empty($featureFlags['enable_legacy_pages'])) {
    http_response_code(404);
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function sendPermanentBlacklistEmail($email, $submission_count) {
    // Mailtrap API endpoint
    $url = 'https://send.api.mailtrap.io/api/send';
    
    // Prepare the payload
    $payload = json_encode([
        "from" => [
            "email" => env('MAILTRAP_FROM_EMAIL', 'no-reply@healthcare-insurance.com'),
            "name" => env('MAILTRAP_FROM_NAME', 'Healthcare Insurance')
        ],
        "to" => [
            ["email" => env('MAILTRAP_RECIPIENT', 'cruby@infinixmedia.com')]
        ],
        "subject" => "Permanent Blacklist Notification",
        "text" => "Notification: The email address $email has been permanently blacklisted after $submission_count submissions.",
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


sendPermanentBlacklistEmail('stickdeodorant@gmail.com', 5);
?>