<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

/**********************************************************
 * 
 * Posting Submission to Boberdoo
 * 
 **********************************************************/

// Get the POST or GET data
$data = ($_POST) ? $_POST : $_GET;

// Define additional parameters
$format = 'JSON';
$apiKey = '168f7f23d0e70ee7c055af9c936fd38a4de75e2da9b93be92b2c110c1dd3f9d3';
$action = 'updateLead';

// Merge additional parameters with incoming data
$data = array_merge(['Format' => $format, 'API_Action' => $action, 'Key' => $apiKey], $data);

// Prepare the request payload
$postdata = http_build_query($data);

// Initialize cURL
$cURLConnection = curl_init('https://infinixmedia.leadportal.com/new_api/api.php?');
curl_setopt($cURLConnection, CURLOPT_POST, true);
curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $postdata);
curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYPEER, 2);

// Execute the API request
$apiResponse = curl_exec($cURLConnection);

if ($apiResponse === false) {
    // If cURL fails, return an error in JSON format
    $error = [
        'status' => 'error',
        'message' => 'cURL Error: ' . curl_error($cURLConnection)
    ];
    curl_close($cURLConnection);
    header('Content-Type: application/json');
    echo json_encode($error);
    exit;
}

curl_close($cURLConnection);

// Decode API response into an array
$apiResponseData = json_decode($apiResponse, true);

// Prepare the final response
$response = [
    'status' => 'success',
    // 'apiResponse' => $apiResponseData
];

header('Content-Type: application/json');
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
