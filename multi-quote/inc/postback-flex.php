<?php 

$data = $_POST;
$postdata = http_build_query($data);

$cURLConnection = curl_init('https://www.bellcoat.com/rd/px.php?'.$postdata);
curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

$apiResponse = curl_exec($cURLConnection);
curl_close($cURLConnection);


echo json_encode($apiResponse);


?>