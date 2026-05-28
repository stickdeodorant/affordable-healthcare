<?php 
// Initialize session FIRST, before any includes
require_once 'inc/classes/SessionManager.php';
require_once 'inc/classes/SecurityHelper.php';
SessionManager::init();

// Now include other files
include 'inc/header.php';

if(isset($_SESSION['state'])) {
	$featureTitle = 'Compare Affordable <span style="font-weight:bold;">'.$_SESSION['state'].'</span> Health plans';
} else {
	$featureTitle = $sitename.'.';
}
$featureSubtitle = 'Learn More About Your <br class="d-inline-block d-sm-none">'. ((isset($_SESSION['state'])) ? $_SESSION['state'] : '') .' Healthcare Options!';
// Use 0 or '0' for a caption value to hide the value, defaults to main otherwise
$featureCaption = [
	'default' => 'Find affordable health insurance <br class="d-inline-block d-sm-none">with ' . $featureTitle,
	'mobile' => '',
	'filled' => '',
	'mfilled' => '',
];

include 'inc/multipart-form.php';

include 'inc/footer.php'; ?>