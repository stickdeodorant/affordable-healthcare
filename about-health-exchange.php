<?php
include 'inc/header.php';
$provider = 'About Health Exchange';
if($state) {
	$featureTitle = 'Compare Affordable <strong>' . $state . '</strong> Health Plans!';
	$subtitle = 'Affordable Healthcare offers a broad selection of ' . $state . ' healthcare plan options that includes converage for individuals, families and short term from most of the leading ' . $state . ' health insurance companies.';
} else {
	$featureTitle = 'Find Affordable Healthcare!';
	$subtitle = 'Compare the latest Health Exchange Options Available';
}
$featureSubtitle = $subtitle;
// Use 0 or '0' for a caption value to hide the value, defaults to main otherwise
$featureCaption = [
	'Find affordable health insurance <br class="d-inline-block d-sm-none">with ' . $featureTitle,
	'state' => '',
	'filled' => '',
	'mfilled' => '',
];
include 'inc/feature.php';
?>
<main>
	<?php include 'inc/sections/steps.php'; ?>
	<?php include 'inc/sections/cta-banner.php'; ?>
	<?php include 'inc/sections/faq-section.php'; ?>
</main>
<?php include 'inc/footer.php'; ?>
