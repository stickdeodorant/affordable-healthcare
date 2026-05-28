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
$action = 'pingPostConsent';
$mode = 'ping';
$src = 'InfinixConsentK';
$type = 31;

// Merge additional parameters with incoming data
$data = array_merge(['Format' => $format, 'API_Action' => $action, 'Key' => $apiKey, 'Mode' => $mode, 'SRC' => $src, 'TYPE' => $type], $data);

// Prepare the request payload
$postdata = http_build_query($data);

// Initialize cURL
$cURLConnection = curl_init('https://infinixmedia.leadportal.com/new_api/api.php?');
curl_setopt($cURLConnection, CURLOPT_POST, true);
curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $postdata);
curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYPEER, 0);

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

// Define allowed tags and attributes
$allowedTags = '<p><a><b><i><strong><em><ul><ol><li><br>';
$allowedAttributes = ['href', 'title', 'target'];

// Function to sanitize HTML
function sanitizeHtml($html, $allowedTags, $allowedAttributes) {
    // Strip tags except allowed ones
    $cleanHtml = strip_tags($html, $allowedTags);

    // Use DOMDocument to process attributes
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Suppress warnings from invalid HTML
    $dom->loadHTML('<div>' . $cleanHtml . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    foreach ($dom->getElementsByTagName('*') as $element) {
        foreach ($element->attributes as $attr) {
            // Remove attributes not in the allowed list
            if (!in_array($attr->name, $allowedAttributes)) {
                $element->removeAttribute($attr->name);
            }
        }
    }

    // Return sanitized HTML
    return $dom->saveHTML($dom->documentElement);
}

// Sanitize seller_html if it exists in the response
if (isset($apiResponseData['response']['bids']['bid'][0]['seller_html'])) {
    $apiResponseData['response']['bids']['bid'][0]['seller_html'] = sanitizeHtml(
        $apiResponseData['response']['bids']['bid'][0]['seller_html'],
        $allowedTags,
        $allowedAttributes
    );
}

// Prepare the final response
$response = [
    'status' => 'success',
    'requestData' => $data, // Optional: include the request data for debugging
    'apiResponse' => $apiResponseData
];

header('Content-Type: application/json');
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
