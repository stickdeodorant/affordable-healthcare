<?php
require_once __DIR__ . '/env.php';

// Environment
$appEnv = env('APP_ENV', 'production');
$appDebug = env_bool('APP_DEBUG', $appEnv !== 'production');

// Site Details
$sitename = env('SITE_NAME', 'Healthcare Insurance');
$address = env('SITE_ADDRESS', '8881 Ave, White Hall, OK 71612');
$domain = env('APP_DOMAIN', $_SERVER['HTTP_HOST'] ?? 'localhost');
$siteurl = env('APP_URL', (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($domain ?? 'localhost'));
$url = isset($_SERVER['REQUEST_URI']) ? $siteurl . $_SERVER['REQUEST_URI'] : $siteurl;

// Tracking / Marketing
$gtm_containers = env_array('GTM_CONTAINERS', ['GTM-5DHQH9H', 'GTM-NGLCRXJH', 'GTM-MJMNPM5']);
$google_ads_ids = env_array('GOOGLE_ADS_IDS', ['AW-340114397']);
$ga_measurement_ids = env_array('GA_MEASUREMENT_IDS', ['UA-203937944-1', 'UA-203921006-1']);
$enableAnalytics = env_bool('ENABLE_ANALYTICS', $appEnv !== 'local');
$conversion_id = env('CONVERSION_ID', '809932216');
$conversion_label = env('CONVERSION_LABEL', '2jwBCJKU5X8QuKuaggM');

// Taboola campaign tracking
$utm_source = $_GET['utm_source'] ?? '';
$utm_content = $_GET['utm_content'] ?? '';
$taboola_click_id = $_GET['t_clickid'] ?? ($_GET['t_click'] ?? '');
$isTaboola = ($taboola_click_id !== '') || (strtolower($utm_source) === 'taboola');

// Persist attribution for downstream use
$_SESSION['tracking'] = $_SESSION['tracking'] ?? [];
$_SESSION['tracking']['utm_source'] = $utm_source;
$_SESSION['tracking']['utm_content'] = $utm_content;
$_SESSION['tracking']['t_clickid'] = $taboola_click_id;
$_SESSION['tracking']['is_taboola'] = $isTaboola;

// Disable Google/Bing analytics when Taboola click is present
if ($isTaboola) {
	$enableAnalytics = false;
}

// Pivot Integration
$pivot_lpid = env('PIVOT_LPID', '1003');
// Dyanmic Date & Time
$year = date('Y');
$month = date('n');
$phone = [
	// 'main' => '1 (888) 686-5921',
	// 'typ' 		=> '(866) 218-2038',
	// 'typ' 		=> '(866) 670-5961',
	'typ' 		=> '(866) 296-4954',
	// 'popup' 	=> '(866) 218-2178',
	// 'popup' 	=> '(866) 670-4681',
	// 'kobe1' 	=> '(855) 329-5850',
	'kobe1' 	=> '(866) 670-5962',
	// 'kobe2' 	=> '(855) 296-0322',
	'kobe2' 	=> '(866) 670-6136',
	'kobe3' 	=> '(855) 328-3572',
	'fb-call' 	=> '(866) 209-5909',
	'fb-typ' 	=> '(866) 670-4518',
	'email' 	=> '(866) 418-6455',
	'agent'		=> '(866) 670-4157',
	'saints-typ'	=> '(866) 274-3950',
	// 'standard'	=> '(877) 206-5525',
	// 'premium'	=> '(877) 518-2037',
	// 'h2'		=> '(855) 296-0322',
	'medicare'	=> '(888) 670-1899',
	'popup' 	=> '(866) 302-9552',
	'standard'	=> '(866) 307-0165',
	'premium'	=> '(866) 303-4071',
	'h2'		=> '(866) 231-7963',
];

$phoneOverrides = json_decode(env('PHONE_OVERRIDES_JSON', ''), true);
if (is_array($phoneOverrides)) {
	$phone = array_merge($phone, $phoneOverrides);
}
$phonemin = array_map(function($val) {
	$val = str_replace(' ', '-', $val);
	$val = preg_replace('/[^A-Za-z0-9\-]/', '', $val);
	return preg_replace('/-+/', '-', $val);
}, $phone);
$email = [
	'main' => 'info@' . $domain,
	'opt' => 'info@' . $domain,
];
// $optphonelink = '<a href="tel:' . $phonemin['opt'] . '" class="phone-link optphone-link">' . $phone['opt'] . '</a>';
// $optemaillink = '<a href="mailto:' . $email['opt'] . '" class="email-link optemail-link">' . $email['opt'] . '</a>';

$states = [
	'AK' => 'Alaska',
	'AL' => 'Alabama',
	'AR' => 'Arkansas',
	'AZ' => 'Arizona',
	'CA' => 'California',
	'CO' => 'Colorado',
	'CT' => 'Connecticut',
	'DC' => 'District of Columbia',
	'DE' => 'Delaware',
	'FL' => 'Florida',
	'GA' => 'Georgia',
	'HI' => 'Hawaii',
	'IA' => 'Iowa',
	'ID' => 'Idaho',
	'IL' => 'Illinois',
	'IN' => 'Indiana',
	'KS' => 'Kansas',
	'KY' => 'Kentucky',
	'LA' => 'Louisiana',
	'MA' => 'Massachusetts',
	'MD' => 'Maryland',
	'ME' => 'Maine',
	'MI' => 'Michigan',
	'MN' => 'Minnesota',
	'MO' => 'Missouri',
	'MS' => 'Mississippi',
	'MT' => 'Montana',
	'NC' => 'North Carolina',
	'ND' => 'North Dakota',
	'NE' => 'Nebraska',
	'NH' => 'New Hampshire',
	'NJ' => 'New Jersey',
	'NM' => 'New Mexico',
	'NV' => 'Nevada',
	'NY' => 'New York',
	'OH' => 'Ohio',
	'OK' => 'Oklahoma',
	'OR' => 'Oregon',
	'PA' => 'Pennsylvania',
	'RI' => 'Rhode Island',
	'SC' => 'South Carolina',
	'SD' => 'South Dakota',
	'TN' => 'Tennessee',
	'TX' => 'Texas',
	'UT' => 'Utah',
	'VA' => 'Virginia',
	'VT' => 'Vermont',
	'WA' => 'Washington',
	'WI' => 'Wisconsin',
	'WV' => 'West Virginia',
	'WY' => 'Wyoming',
];
if (isset($_GET['st'])) {
	$state = $states[strtoupper($_GET['st'])];
	$_SESSION['state_abbr'] = strtoupper($_GET['st']);
	$_SESSION['state'] = $state;
}

$today = date('m/d');
$holidays = array(
	'01/01', // New Year Day
	'05/04', // Independence Day
	'09/05', // Memorial Day    *Update Yearly
	'11/24', // Thanksgiving    *Update Yearly
	'12/24', // Christmas Eve
	'12/25', // Christmas Day
	'12/31', // New Year Eve
	'09/02' //Today
);
if (in_array($today, $holidays)) {

	$call_now = 'false';

} else {

	date_default_timezone_set('America/New_York');
	$current_time = date('H:i');
	$current_date = date('N');
	$startTime = "09:00";
	$endTime = "18:00";
	$time1 = DateTime::createFromFormat('H:i', $current_time);
	$time2 = DateTime::createFromFormat('H:i', $startTime);
	$time3 = DateTime::createFromFormat('H:i', $endTime);

	if ($current_date <= 5 ) { 
		if ($time1 >= $time2 && $time1 <= $time3) {
			$call_now = 'true';
		} else {
			$call_now = 'false';
		}
	} else {
		$call_now = 'false';
	}

}

?>
