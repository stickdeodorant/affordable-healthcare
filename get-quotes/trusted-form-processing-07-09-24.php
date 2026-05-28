<?php 
require_once __DIR__ . '/../inc/env.php';

$data = $_POST;
$certID = $data['certID'];
$url = $certID;

$postdata = http_build_query($userData);

$cURLConnection = curl_init($url);
curl_setopt($cURLConnection, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($cURLConnection, CURLOPT_USERPWD, "API:" . env('TRUSTEDFORM_API_KEY'));
curl_setopt($cURLConnection, CURLOPT_HEADER, 'Content-type: application/json');
curl_setopt($cURLConnection, CURLOPT_POST, true);
curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $postdata);
curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

$apiResponse = curl_exec($cURLConnection);
curl_close($cURLConnection);

echo json_encode($apiResponse);

?>