<?php
include 'inc/header.php';
$provider = 'United Healthcare';
if($state) {
	$featureTitle = 'Compare Affordable <strong>' . $state . '</strong> Health Plans!';
} else {
	$featureTitle = '<span class="text-secondary">Find Affordable</span> Healthcare!';
}
$featureSubtitle = 'Start Comparing '. $state .' '.$provider.' Options';
// Use 0 or '0' for a caption value to hide the value, defaults to main otherwise
$featureCaption = [
	'default' => 'Enter your zip and see what savings are available!',
	'mobile' => '',
	'filled' => '',
	'mfilled' => '',
];
include 'inc/hero.php';
?>
<main>
	<?php include 'inc/sections/steps.php'; ?>
	<?php include 'inc/sections/cta-banner.php'; ?>
	<?php include 'inc/sections/faq-section.php'; ?>
	<?php /* include 'inc/sections/plane-banner.php'; */ ?>
</main>
<?php include 'inc/footer.php'; ?>
