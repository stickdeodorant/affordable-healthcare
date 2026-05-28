<?php
include 'inc/header.php';
$provider = 'Amerigroup';
if($state) {
	$featureTitle = 'Compare Affordable <strong>' . $state . '</strong> Health Plans!';
	$subtitle = 'You may qualify for a plan with no monthly cost';
} else {
	$featureTitle = 'Find Affordable Affordable Healthcare!';
	$subtitle = 'You may qualify for a plan with no monthly cost';
}
$featureSubtitle = 'You may qualify for a plan with no monthly cost';
// Use 0 or '0' for a caption value to hide the value, defaults to main otherwise
$featureCaption = [
	'default' => 'Enter your zip and see what savings are available!',
	'mobile' => '',
	'filled' => '',
	'mfilled' => '',
];
include 'inc/feature.php';
?>
<main>
	<?php include 'inc/sections/steps.php'; ?>
	<?php include 'inc/sections/cta-banner.php'; ?>
  <?php include 'inc/sections/faq-section.php'; ?>
	<?php /* include 'inc/sections/plane-banner.php'; */ ?>
</main>
<?php include 'inc/footer.php'; ?>
