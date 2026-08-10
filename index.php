<?php
$pageName = 'home';
include 'inc/header.php';
if (isset($state)) {
	$featureTitle = 'Compare Affordable <strong>' . $state . '</strong> Health Plans!';
	$subtitle = 'You may qualify for a plan with no monthly cost';
} else {
	$featureTitle = '<span class="text-secondary">Find Affordable</span> Healthcare!';
	$subtitle = 'You may qualify for a plan with no monthly cost';
}
$featureSubtitle = 'Don\'t overpay for health coverage';
// Use 0 or '0' for a caption value to hide the value, defaults to main otherwise
$featureCaption = [
	'default' => 'Healthcare quotes are moments away.',
	'mobile' => '',
	'filled' => '',
	'mfilled' => '',
];
include 'inc/hero.php';
?>
<main>
	<?php include 'inc/sections/cta-banner.php'; ?>
	<?php include 'inc/sections/steps2.php'; ?>
	<?php include 'inc/sections/plane-banner.php'; ?>
	<?php include 'inc/sections/searching-intro.php'; ?>
	<?php include 'inc/sections/open-enrollment.php'; ?>
	<?php include 'inc/sections/consumer-caution.php'; ?>
	<?php include 'inc/sections/faq-section.php'; ?>
</main>
<?php include 'inc/footer.php'; ?>