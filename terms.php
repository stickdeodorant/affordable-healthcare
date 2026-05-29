<?php
$pageName = 'home';
include 'inc/header.php';
if (isset($state)) {
	$featureTitle = 'Compare Affordable <strong>' . $state . '</strong> Health Plans!';
	$subtitle = 'You may qualify for a plan with no monthly cost';
} else {
	$featureTitle = 'Find Affordable Healthcare!';
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
?>
<main>
	<section class="container-fluid">
		<div class="container">
			<div class="row mt-5 d-flex pb-5 text-center text-lg-left">
				<div class="col-12">
					<?=file_get_contents(__DIR__ . '/inc/modals/terms.php')?>
				</div>
			</div>
		</div>
	</section>
</main>
<?php include 'inc/footer.php'; ?>