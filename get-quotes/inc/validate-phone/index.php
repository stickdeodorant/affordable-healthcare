<?php
require_once __DIR__ . '/../../../inc/env.php';

// Install the library via PEAR or download the .zip file to your project folder.
// This line loads the library

if(isset($_GET['phone']) && is_numeric($_GET['phone']) && strlen($_GET['phone']) == 10)
{
		$phone_number = $_GET['phone'];
		$account_sid = env('TWILIO_ACCOUNT_SID');
		$auth_token = env('TWILIO_AUTH_TOKEN');
	  	$base_url = "https://lookups.twilio.com/v1/PhoneNumbers/+1$phone_number";
    	$ch = curl_init($base_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, "$account_sid:$auth_token");	
		$response = json_decode(curl_exec($ch));
		
		echo ($response->status != 404) ? json_encode('true') : json_encode('false');
}