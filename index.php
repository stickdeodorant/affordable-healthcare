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
include 'inc/feature.php';
?>
<main>
	<?php include 'inc/sections/cta-banner.php'; ?>
	<?php include 'inc/sections/steps.php'; ?>
	<?php /*
	<section class="container-fluid">
		<div class="container">
			<div class="row mt-5 d-flex pb-5 text-center text-lg-left">
				<div class="col-lg-7 offset-lg-1 order-md-2 scale scale-right">
					<h3 class="h1">Searching For Affordable Healthcare Plans Made Easy!</h3>
					<p>Looking for a healthcare plan in your budget doesn't have to be difficult. When you browse for affordable healthcare online, you should be able to sort through plans with ease. That is why <?=$sitename?> provide FREE healthcare quotes through our customized search engine. We make looking for affordable insurance simple. Find out more about <?php echo $state_abbr; ?> health insurance TODAY!</p>
				</div>
				<div class="d-md-none d-lg-block col-lg-4 order-md-1 mt-4 mt-md-0">
					<?php include 'inc/testimonials.php'; ?>
				</div>
			</div>
		</div>
	</section>
	*/ ?>
	<section id="grassy" class="bg-accent" style="background-image: linear-gradient(rgba(139, 195, 74, 0.9),rgba(139, 195, 74, 0.9)), url(/img/grass.svg); background-size: 575px auto;">
		<svg x="0px" y="0px" viewBox="0 0 1364.8 100" style="enable-background:new 0 0 1364.8 100; z-index: 1;" xml:space="preserve">
			<path d="M1364.8,99.8V0H0v100C273,0.1,682.5,0,1364.8,99.8z" />
		</svg>
		<div class="container-fluid scale">
			<div class="container">
				<?php /*
					<div class="row">
						<div class="col-sm-10 offset-sm-1 text-center">
							<h3 class="h1">Special Enrollment Period</h3>
							<p>There is currently a SPECIAL ENROLLMENT PERIOD that has been enacted by the government due the COVI-19 emergency. A special enrollment period has been set in place to help individuals and families enroll through the Health Insurance Marketplace for an Obamacare plan outside of the Open Enrollment Period.</p>
							<p>This Special Enrollment Period issued by the federal government started February 15, 2021, and will end August 15, 2021.</p>
							<?php if(isset($_GET['call'])) { ?><a href="tel:<?=$phonemin['fb-call']?>" class="button bg-primary text-white"><?=$phone['fb-call']?></a><?php } else { ?><a class="button bg-primary text-white mr-2 scale-4x" href="/get-quotes">Find Plans</a><?php } ?>
						</div>
					</div>
				*/ ?>
				<div class="row">
					<div class="col-sm-12 text-center">
						<h3 class="h1">Open Enrollment And Qualifying Life Events</h3>
						<p>Open Enrollment is the yearly period when many consumers can enroll in comprehensive Marketplace health insurance. For 2027 coverage, Open Enrollment is currently scheduled to begin on November 1, 2026 in most states and end on December 15, 2026 in most states. Some state based Marketplaces may have later deadlines, including December 23 or December 31, 2026, and some deadlines may change.
						<p>
						<p>Outside Open Enrollment, you may need a qualifying life event, also called a QLE, to enroll in certain types of comprehensive coverage. A QLE may include losing qualifying coverage, getting married, having a baby, adopting a child, moving, or another eligible household change.</p>
						<p class="bold" style="font-style: italic;">Ask the agent to confirm the enrollment rules and deadlines that apply in your state.</p>
						<?php if (isset($_GET['call'])) { ?><a href="tel:<?= $phonemin['fb-call'] ?>" class="button bg-primary text-white"><?= $phone['fb-call'] ?></a><?php } else { ?><a class="button bg-primary text-white mr-2 scale-4x scroll-top" href="/get-quotes">Find Plans</a><?php } ?>
					</div>
				</div>
			</div>
		</div>
		<img src="/img/picking-fruit.svg" style="position: absolute; right: calc(48px + 5vw); top: calc(-32px - 4vw);
width: calc(32px + 12vw); max-width: 50%; transform: scaleX(-1); z-index: 2;" alt="cartoon girl picking fruit from tree">
		<img src="/img/running-girl.svg" alt="cartoon girl running, fitness" style="position: absolute; bottom: calc(-16px + 5vw); left: 30px; width: calc(64px + 6vw); max-width: 50%; z-index: 2;">
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
	<?php /* include 'inc/sections/faq-section.php'; */ ?>
	<?php /* include 'inc/sections/plane-banner.php'; */ ?>
</main>
<?php include 'inc/footer.php'; ?>