<?php
require_once __DIR__ . '/../inc/feature-flags.php';
if (empty($featureFlags['enable_legacy_pages'])) {
	http_response_code(404);
	exit;
}

$data = $_POST;
unset($data['Redirect_URL']);
$url = 'https://infinixmedia.leadportal.com/genericPostlead.php?';
$postdata = http_build_query( $data);

$cURLConnection = curl_init('https://infinixmedia.leadportal.com/genericPostlead.php?');
curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $postdata);
curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

$apiResponse = curl_exec($cURLConnection);
curl_close($cURLConnection);

//echo $postdata;
// echo $apiResponse;
//die;
// if(!$apiResponse) {
//     die("Connection Failure");
// } else {
//   header("Location: ". $_POST['Redirect_URL']);
// }
?>