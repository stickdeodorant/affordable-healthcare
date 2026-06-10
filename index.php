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
	<section class="container-fluid">
		<div class="container">
			<div class="row mt-5 d-flex pb-5 text-center text-lg-left">
				<div class="col-lg-7 offset-lg-1 order-md-2 scale scale-right">
					<h3 class="h1">Searching For Affordable Healthcare Plans Made Easy!</h3>
					<p>Looking for a healthcare plan in your budget does not have to be difficult. <?= $sitename ?> is a private health insurance comparison service that helps connect you with licensed agents to review plan options available in your area. Comparing options through this site does not require a fee, and plan availability, pricing, and eligibility vary by state and individual circumstances.</p>
				</div>
				<div class="d-md-none d-lg-block col-lg-4 order-md-1 mt-4 mt-md-0">
					<?php include 'inc/testimonials.php'; ?>
				</div>
			</div>
		</div>
	</section>
	<section id="grassy" class="bg-tertiary" style="background: var(--color-ink)">
		<svg x="0px" y="0px" viewBox="0 0 1364.8 100" style="enable-background:new 0 0 1364.8 100; z-index: 1;" xml:space="preserve">
			<path d="M1364.8,99.8V0H0v100C273,0.1,682.5,0,1364.8,99.8z" />
		</svg>
		<div class="container-fluid scale">
			<div class="container">
				<div class="row">
					<div class="col-sm-12 text-center">
						<h3 class="h1 text-white">Open Enrollment And Qualifying Life Events</h3>
						<p>Open Enrollment is the yearly period when many consumers can enroll in comprehensive Marketplace health insurance. For 2027 coverage, Open Enrollment is currently scheduled to begin on November 1, 2026 in most states and end on December 15, 2026 in most states. Some state based Marketplaces may have later deadlines, including December 23 or December 31, 2026, and some deadlines may change.
						<p>
						<p>Outside Open Enrollment, you may need a qualifying life event, also called a QLE, to enroll in certain types of comprehensive coverage. A QLE may include losing qualifying coverage, getting married, having a baby, adopting a child, moving, or another eligible household change.</p>
						<p class="bold" style="font-style: italic;">Ask the agent to confirm the enrollment rules and deadlines that apply in your state.</p>
						<?php if (isset($_GET['call'])) { ?><a href="tel:<?= $phonemin['fb-call'] ?>" class="button bg-primary text-white"><?= $phone['fb-call'] ?></a><?php } else { ?><a class="button bg-primary text-white mr-2 scale-4x scroll-top" href="/get-quotes">Find Plans</a><?php } ?>
					</div>
				</div>
			</div>
		</div>
		<img src="/img/hired.svg" alt="New Job" style="position: absolute; bottom: calc(-16px + 1vw); left: 20px; width: calc(64px + 23vw); max-width: 50%; z-index: 5;">
		<img src="/img/pregnant.svg" alt="Pregnancy" style="position: absolute; right: calc(0px + 3vw); top: calc(-32px - 7vw); width: calc(32px + 26vw); max-width: 50%; transform: scaleX(-1); z-index: 2;">
		<svg x="0px" y="0px" viewBox="0 0 1366 100" style="enable-background:new 0 0 1366 100; z-index: 3;" xml:space="preserve">
			<g>
				<path d="M0,100h836.7C614.7,100,341.5,66.7,0,0V100z" />
				<path d="M836.7,100H1366V0C1229.4,66.7,1058.7,100,836.7,100z" />
			</g>
		</svg>
	</section>
	<section id="consumer-caution" class="container-fluid mb-5">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="card border-rounded-lg border-2 border-muted p-4">
						<h3 class="h1">Consumer Caution</h3>
						<p class="mb-1">Before enrolling in any plan, ask whether it is comprehensive health insurance coverage. Ask whether your doctors and prescriptions are covered, what your benefits are, and what your maximum out of pocket exposure may be. Avoid high pressure sales tactics and ask for written plan details before&nbsp;enrolling.</p>
						<a class="text-right" href="/consumer-caution">Read Consumer Caution ></a>
					</div>
				</div>
			</div>
		</div>
	</section>
	<?php include 'inc/sections/faq-section.php'; ?>
</main>
<?php include 'inc/footer.php'; ?>