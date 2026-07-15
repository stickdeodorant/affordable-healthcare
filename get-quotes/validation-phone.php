<?php
require_once __DIR__ . '/../inc/env.php';

$appEnv = env('APP_ENV', 'production');
$appDebug = env_bool('APP_DEBUG', $appEnv !== 'production');
ini_set('display_errors', $appDebug ? '1' : '0');
error_reporting($appDebug ? E_ALL : 0);

if (isset($_POST['number'])) {
    $access_key = env('PHONE_VALIDATION_ACCESS_KEY', '');
    if ($access_key === '') {
        http_response_code(500);
        echo json_encode(['error' => 'Phone validation not configured']);
        exit;
    }

    $phone_number = $_POST['number'];
    $url = 'https://apilayer.net/api/validate?access_key=' . $access_key . '&number=' . $phone_number . '&country_code=US';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        error_log('validation-phone.php cURL error: ' . curl_error($ch));
        http_response_code(502);
        echo json_encode(['error' => 'Phone validation service unavailable']);
        curl_close($ch);
        exit;
    }

    curl_close($ch);

    echo $response;
} else {
    echo json_encode(['error' => 'No phone number provided']);
}
?>
