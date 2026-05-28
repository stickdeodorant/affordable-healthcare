<?php 
require_once __DIR__ . '/../../inc/env.php';

$data = $_POST;
$test = $_GET;
$postdata = http_build_query($data);

$convoso_token = env('CONVOSO_AUTH_TOKEN');
$convoso_list_id = env('CONVOSO_LIST_ID', '11817');
$cURLConnection = curl_init("https://api.convoso.com/v1/leads/insert?auth_token={$convoso_token}&list_id={$convoso_list_id}");
curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $postdata);
curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

$apiResponse = curl_exec($cURLConnection);
curl_close($cURLConnection);

// print_r($_POST);
// echo $postdata;
echo json_encode($apiResponse);
// print_r($apiResponse);

?>