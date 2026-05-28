<?php
include 'inc/header.php';
if($state) {
	$featureTitle = 'Compare Affordable <strong>' . $state . '</strong> Health Plans!';
	$subtitle = 'You may qualify for a plan with no monthly cost';
} else {
	$featureTitle = 'Compare Affordable Health Plans!';
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
if ( $_GET['call'] == 'true') {
	include 'inc/call.php';
} else {
	include 'inc/feature.php';
}
?>
<main>
	<?php include 'inc/sections/steps.php'; ?>
	<?php include 'inc/sections/cta-banner.php'; ?>
	<section class="container-fluid">
		<div class="container">
			<div class="row mt-5 d-flex pb-5 text-center text-lg-left">
				<div class="col-lg-7 offset-lg-1 order-md-2 scale scale-right">
					<h3 class="h1">Searching For Affordable Healthcare Plans Made Easy!</h3>
					<p>Looking for a healthcare plan in your budget doesn’t have to be difficult. When you browse for affordable healthcare online, you should be able to sort through plans with ease. That is why <?=$sitename?> provide FREE healthcare quotes through our customized search engine. We make looking for affordable insurance simple. Find out more about <?php echo $state_abbr; ?> health insurance TODAY!</p>
				</div>
				<div class="d-md-none d-lg-block col-lg-4 order-md-1 mt-4 mt-md-0">
					<?php include 'inc/testimonials.php'; ?>
				</div>
			</div>
		</div>
	</section>
	<section id="grassy" class="bg-accent" style="background-image: linear-gradient(rgba(139, 195, 74, 0.9),rgba(139, 195, 74, 0.9)), url(/img/grass.svg); background-size: 575px auto;">
		<svg x="0px" y="0px" viewBox="0 0 1364.8 100" style="enable-background:new 0 0 1364.8 100; z-index: 1;" xml:space="preserve"><path d="M1364.8,99.8V0H0v100C273,0.1,682.5,0,1364.8,99.8z"/></svg>
		<div class="container-fluid scale">
			<div class="container">
				<?php /*
					<div class="row">
						<div class="col-sm-10 offset-sm-1 text-center">
							<h3 class="h1">Special Enrollment Period</h3>
							<p>There is currently a SPECIAL ENROLLMENT PERIOD that has been enacted by the government due the COVI-19 emergency. A special enrollment period has been set in place to help individuals and families enroll through the Health Insurance Marketplace for an Obamacare plan outside of the Open Enrollment Period.</p>
							<p>This Special Enrollment Period issued by the federal government started February 15, 2021, and will end August 15, 2021.</p>
							<?php if(isset($_GET['call'])) { ?><a href="tel:<?=$phonemin['fb-call']?>" class="button bg-primary text-white"><?=$phone['fb-call']?></a><?php } else { ?><a class="button bg-primary text-white mr-2 scale-4x scroll-top" href="/get-quotes">Find Plans</a><?php } ?>
						</div>
					</div>
				*/ ?>
				<div class="row">
					<div class="col-sm-10 offset-sm-1 text-center">
						<h3 class="h1">Open Enrollment Period</h3>
						<p>The timeframe when individuals or families can enroll in a health insurance plan, apply changes to their healthcare options, or cancel a plan.<p>
						<p>Enrollment through the Health Insurance Marketplace normally takes place from Nov. 1 through Jan 15. All plans acquired during this time are effective January 1st of the following year.</p>
						<?php if(isset($_GET['call'])) { ?><a href="tel:<?=$phonemin['fb-call']?>" class="button bg-primary text-white"><?=$phone['fb-call']?></a><?php } else { ?><a class="button bg-primary text-white mr-2 scale-4x scroll-top" href="/get-quotes">Find Plans</a><?php } ?>
					</div>
				</div>
			</div>
		</div>
		<img src="/img/picking-fruit.svg" style="position: absolute; right: calc(48px + 5vw); top: calc(-32px - 4vw);
width: calc(32px + 12vw); max-width: 50%; transform: scaleX(-1); z-index: 2;" alt="cartoon girl picking fruit from tree">
		<img src="/img/running-girl.svg" alt="cartoon girl running, fitness" style="position: absolute; bottom: calc(-16px + 5vw); left: 30px; width: calc(64px + 6vw); max-width: 50%; z-index: 2;">
		<svg x="0px" y="0px" viewBox="0 0 1366 100" style="enable-background:new 0 0 1366 100; z-index: 3;" xml:space="preserve"><g><path d="M0,100h836.7C614.7,100,341.5,66.7,0,0V100z"/><path d="M836.7,100H1366V0C1229.4,66.7,1058.7,100,836.7,100z"/></g></svg>
	</section>
	<section id="beforeafter" class="container-fluid">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<h3 class="h1">Advantages of <br class="d-inline-block d-md-none"><?php echo $sitename; ?></h3>
				</div>
			</div>
			<div class="row mt-5">
				<div class="col-md-6 px-sm-2 py-2 px-md-5">
					<h3 class="mb-3 h2">Individual<span class="d-inline-block d-md-none">&nbsp;Plans</span></h3>
					<p>Looking for an individual healthcare plan? Affordable health insurance is just a few clicks away! Get started with <?=$sitename?> TODAY!</p>
					<img class="withwithout mb-4" src="/img/before.svg" alt="cartoon man with his arms up, disappointed">
				</div>
				<div class="col-md-6 px-sm-2 py-2 px-md-5">
					<h3 class="mb-3 h2">Family<span class="d-inline-block d-md-none">&nbsp;Plans</span></h3>
					<p>Looking to increase the healthcare coverage for your spouse or dependents? Learn more about your options today, try today for FREE!</p>
					<img class="withwithout mb-4" src="/img/after.svg" alt="cartoon man, celebrating, happy">
				</div>
			</div>
		</div>
		<div>
			<?php if(isset($_GET['call'])) { ?><a href="tel:<?=$phonemin['fb-call']?>" class="button bg-accent text-white"><?=$phone['fb-call']?></a><?php } else { ?><a class="button bg-accent text-white mt-4 mb-5 scale-4x scroll-top" href="/get-quotes">Find Plans</a><?php } ?>
		</div>
			</section>
			<?php include 'inc/sections/faq-section.php'; ?>
	<?php /* include 'inc/sections/plane-banner.php'; */ ?>
</main>
<?php include 'inc/footer.php'; ?>
