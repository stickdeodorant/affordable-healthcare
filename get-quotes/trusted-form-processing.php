<?php
require_once __DIR__ . '/../inc/env.php';

// Function to get data from either $_POST or $_GET
function get_request_data() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        return $_POST;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        return $_GET;
    }
    return [];
}

$data = get_request_data();
$certID = $data['certID'] ?? null;
$phone = $data['phone'] ?? null;
$src = $data['src'] ?? null;
$clickID = $data['reference'] ?? null;
$ipAddress = $data['ip_address'] ?? null;

if ($certID === null) {
    die(json_encode(['error' => 'Certificate ID is required']));
}

$url = $certID;

$postdata = json_encode([
    "match_lead" => [
        "phone" => $phone ?? ""
    ],
    "retain" => [
        "reference" => $clickID ?? "",
        "vendor" => $src ?? "Infinix-K"
    ]
]);

$cURLConnection = curl_init($url);
curl_setopt($cURLConnection, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($cURLConnection, CURLOPT_USERPWD, "API:" . env('TRUSTEDFORM_API_KEY'));
curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, [
    'Api-Version: 4.0',
    'Content-Type: application/json'
]);
curl_setopt($cURLConnection, CURLOPT_POST, true);
curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $postdata);
curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

$apiResponse = curl_exec($cURLConnection);

if (curl_errno($cURLConnection)) {
    echo json_encode(['error' => curl_error($cURLConnection)]);
} else {
    echo json_encode($apiResponse);
}

curl_close($cURLConnection);

?>
