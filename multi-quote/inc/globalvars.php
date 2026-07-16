<?php
require_once __DIR__ . '/../../inc/globalvars.php';
require_once __DIR__ . '/classes/TrackingConfig.php';

// Optional override: when enabled, use multi-quote TrackingConfig Taboola detection.
$useMultiQuoteTaboolaDetection = env_bool('MQ_USE_TRACKING_CONFIG_TABOOLA', false);
if ($useMultiQuoteTaboolaDetection) {
	$isTaboola = TrackingConfig::isTaboolaRequest($_SESSION, $_GET);
	$_SESSION['tracking'] = $_SESSION['tracking'] ?? [];
	$_SESSION['tracking']['utm_source'] = $_GET['utm_source'] ?? '';
	$_SESSION['tracking']['utm_content'] = $_GET['utm_content'] ?? '';
	$_SESSION['tracking']['t_clickid'] = $_GET['t_clickid'] ?? ($_GET['t_click'] ?? '');
	$_SESSION['tracking']['is_taboola'] = $isTaboola;

	$enableAnalytics = env_bool('ENABLE_ANALYTICS', $appEnv !== 'local');
	if ($isTaboola) {
		$enableAnalytics = false;
	}
}

// Optional override: apply multi-quote default phone set when enabled.
$useMultiQuotePhoneDefaults = env_bool('MQ_USE_PHONE_DEFAULTS', false);
if ($useMultiQuotePhoneDefaults) {
	$phone = array_merge($phone, env_phone_defaults());
}

$phoneOverrides = json_decode(env('PHONE_OVERRIDES_JSON', ''), true);
if (is_array($phoneOverrides)) {
	$phone = array_merge($phone, $phoneOverrides);
}

$phonemin = array_map(function($val) {
	$val = str_replace(' ', '-', $val);
	$val = preg_replace('/[^A-Za-z0-9\-]/', '', $val);
	return preg_replace('/-+/', '-', $val);
}, $phone);

function getUserIpAddr() {
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'] ?? '';
	}
	return $ip;
}
$ip = getUserIpAddr();

// Multi-quote campaign/session overrides.
if (isset($_GET['usha']) && ($_GET['usha'] === 'true' || $_GET['usha'] === '1')) {
	$_SESSION['campaign'] = 'usha';
	$_SESSION['usha'] = 'true';
}

if (isset($_GET['campaign']) && strtolower($_GET['campaign']) === 'usha') {
	$_SESSION['campaign'] = 'usha';
}

if (isset($_GET['gclid']) && !empty($_GET['gclid'])) {
	$_SESSION['gclid'] = $_GET['gclid'];
}

if (isset($_GET['affiliate_ID']) && !empty($_GET['affiliate_ID'])) {
	$_SESSION['affiliate_ID'] = $_GET['affiliate_ID'];
}

if (isset($_GET['HIT_ID']) && !empty($_GET['HIT_ID'])) {
	$_SESSION['HIT_ID'] = $_GET['HIT_ID'];
}

if (isset($_GET['Sub_ID']) && !empty($_GET['Sub_ID'])) {
	$_SESSION['Sub_ID'] = $_GET['Sub_ID'];
}

if (isset($_GET['Sub_ID2']) && !empty($_GET['Sub_ID2'])) {
	$_SESSION['Sub_ID2'] = $_GET['Sub_ID2'];
}

if (isset($_GET['utm_medium']) && !empty($_GET['utm_medium'])) {
	$_SESSION['search_partners'] = $_GET['utm_medium'];
}

function getPeakStatus() {
	$dayOfWeek = date('N');
	$hourOfDay = date('G');

	if ($dayOfWeek >= 1 && $dayOfWeek <= 5 && $hourOfDay >= 9 && $hourOfDay < 19) {
		return 'peak';
	}

	return 'off-peak';
}
