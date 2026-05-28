<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['number'])) {
    $access_key = '5e26fd2eda12db72664cb0e450e54c15';
    $phone_number = $_POST['number'];
    $url = 'https://apilayer.net/api/validate?access_key=' . $access_key . '&number=' . $phone_number . '&country_code=US';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    
    // Debugging statement
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }

    curl_close($ch);

    echo $response;
} else {
    echo json_encode(['error' => 'No phone number provided']);
}
?>
