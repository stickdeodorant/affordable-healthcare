<?php
include 'inc/header.php';
$featureTitle = 'Emblem Health';
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
	<section class="container-fluid">
		<div class="container">
			<div class="row mt-5 d-flex pb-5 text-center text-lg-left">
				<div class="col-lg-7 offset-lg-1 order-md-2 scale scale-right">
					<h3 class="h1">Searching For Medical Insurance Made Easy!</h3>
					<p>When you browse for affordable <?php echo strtolower($featureTitle); ?> online, you should be able to compare insurance quotes with ease. That is why <?php echo $sitename; ?> provides FREE insurance quotes through our customized search engine. Whether you are looking to expand your current coverage, or need to save money on insurance, we make looking for affordable insurance in <?php echo $state; ?> fast and easy. Learn more about your options TODAY!</p>
				</div>
				<div class="d-md-none d-lg-block col-lg-4 order-md-1 mt-4 mt-md-0">
					<?php include 'inc/testimonials.php'; ?>
				</div>
			</div>
		</div>
	</section>
	<section id="grassy" class="bg-accent" style="background-image: linear-gradient(rgba(139, 195, 74, 0.9),rgba(139, 195, 74, 0.9)), url(/img/grass.svg); background-size: 575px auto;">
		<svg x="0px" y="0px" viewBox="0 0 1364.8 100" style="enable-background:new 0 0 1364.8 100; z-index: 1;" xml:space="preserve"><path d="M1364.8,99.8V0H0v100C273,0.1,682.5,0,1364.8,99.8z"/></svg>
		<div class="container-fluid">
			<div class="container">
				<div class="row">
					<div class="col-12 col-sm-10 offset-sm-1 col-md-8 offset-md-2 text-center">
						<h3 class="h1">Professional</h3>
						<p>Looking to get more information on your results? At <?php echo $sitename; ?>, we have a team of dedicated professionals to help you to choose a <?php echo $state_abbr; ?> insurance plan that best fits your needs. Call for a FREE consultation regarding your options and compare your quotes with the help of our expert staff.</p>
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
					<p>Finding <?php echo strtolower($featureTitle); ?> for yourself doesn't have to be difficult. Review your options with <?php echo $sitename; ?> for FREE!</p>
					<img class="withwithout mb-4" src="/img/before.svg" alt="cartoon man with his arms up, disappointed">
				</div>
				<div class="col-md-6 px-sm-2 py-2 px-md-5">
					<h3 class="mb-3 h2">Family<span class="d-inline-block d-md-none">&nbsp;Plans</span></h3>
					<p>Does your current healthcare plan not provide enough coverage for your spouse or dependents? Compare <?php echo $state_abbr; ?> healthcare plans and start now!</p>
					<img class="withwithout mb-4" src="/img/after.svg" alt="cartoon man, celebrating, happy">
				</div>
			</div>
		</div>
		<div>
			<?php if(isset($_GET['call'])) { ?><a href="tel:<?=$phonemin['fb-call']?>" class="button bg-accent text-white"><?=$phone['fb-call']?></a><?php } else { ?><a class="button bg-accent text-white mt-4 mb-5 scale-4x scroll-top" href="/get-quotes">Find Plans</a><?php } ?>
		</div>
			</section>
			<?php include 'inc/sections/faq-section.php'; ?>
	<div id="plane-banner" class="container-fluid my-3 my-sm-5" style="transform: translateX(100%);">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<svg x="0px" y="0px" viewBox="0 0 383.1 56.6" style="enable-background:new 0 0 383.1 56.6;" xml:space="preserve" class="slide-left">
					  <g>
							<path d="M45.5,28.1c0,0,0.4,0,1,0.1" style="fill:none;stroke:#d0d9dd;stroke-width:calc(1px - .025vw);stroke-linecap:round;stroke-miterlimit:10;"/>
							<path d="M48.4,28.4c2,0.4,4.9,1.2,8,2.8c5.6,3,13.8,6.7,15.7,6.9c2.8,0.3,5.4-1.3,6.9-3" style="fill:none;stroke:#d0d9dd;stroke-width:calc(1px - .025vw);stroke-linecap:round;stroke-miterlimit:10;stroke-dasharray:1.9107,1.9107;"/>
							<path d="M79.7,34.4c0.2-0.3,0.4-0.6,0.5-0.8" style="fill:none;stroke:#d0d9dd;stroke-width:calc(1px - .025vw);stroke-linecap:round;stroke-miterlimit:10;"/>
						</g>
					  <path d="M72.2,39.3c0,0-0.3,3.3,5.3,6.1" style="fill:none;stroke:#d0d9dd;stroke-width:calc(1px - .025vw);stroke-linecap:round;stroke-miterlimit:10;stroke-dasharray:1.7;"/>
					  <g>
							<path d="M22.8,6c-1.2-4.1-5.3-6.7-8.1-5.8c-1.8,0.6-2.3,1.7-1.8,3.8l3.8,14.4l10.4,4.3L22.8,6z" style="fill:#cbd4d9;"/>
							<path d="M49.1,22.6c0.6-1.3,0.6-1.9,0.5-2.9c-0.1-1.3-1.2-2.3-1.3-2.4c-2.5-2.2-5.9,2-7.8,2.6
								c-1.6,0.5-3.3,0.2-4.9-0.5c-8.3-4-20.6-8.3-21.9-8.7C8.6,9.3,5,12.3,4.3,16c-0.7,3.2,0.8,8.3,6.5,9.4c10.3,1.9,29.5,4.9,29.5,4.9
								s3.4,0.4,4.6-1.3L49.1,22.6z" style="fill:#1abae7;"/>
							<g>
								<defs>
									<path id="SVGID_plane_1_" d="M49.1,22.6c0.6-1.3,0.6-1.9,0.5-2.9c-0.1-1.3-1.2-2.3-1.3-2.4c-2.5-2.2-5.9,2-7.8,2.6
										c-1.6,0.5-3.3,0.2-4.9-0.5c-8.3-4-20.6-8.3-21.9-8.7C8.6,9.3,5,12.3,4.3,16c-0.7,3.2,0.8,8.3,6.5,9.4
										c10.3,1.9,29.5,4.9,29.5,4.9s3.4,0.4,4.6-1.3L49.1,22.6z"/>
								</defs>
								<clipPath id="SVGID_plane_2_">
									<use xlink:href="#SVGID_plane_1_" style="overflow:visible;"/>
								</clipPath>
								<path d="M6.4,19.7l-10.5-3.4L-1,6.7L9.5,10c2.7,0.9,4.2,3.7,3.3,6.4l0,0C11.9,19.1,9.1,20.6,6.4,19.7z" style="opacity:0.38;clip-path:url(#SVGID_plane_2_);fill:#fff;"/>
							</g>
							<path d="M26.5,22.4c-2.9-1.2-5.7-2.5-8.6-3.6c-0.5-0.2-1,0.3-0.9,0.8c1.5,5.8,3,11.9,4.9,17.3
									c0.9,2.6,4.8,6.6,8.4,5.6c2.3-0.9,2.3-1.9,1.8-3.8c-1.2-4.8-3.3-10.2-4.8-15.4C27.1,22.9,26.9,22.6,26.5,22.4z" style="fill:#cbd4d9;"/>
					  </g>
					  <g>
						<path d="M4.7,6.6C4.1,6.5,3.3,7.2,2.9,9c-0.6,2.7-0.3,5,0,6.6c0.9-1.3,2-3.4,2.6-6C5.8,7.7,5.3,6.8,4.7,6.6z" style="fill:#cbd4d9;"/>
						<path d="M2.8,15.6c-0.9,1.3-2,3.4-2.6,6c-0.4,1.9,0.1,2.8,0.7,3l0,0c0.6,0.1,1.4-0.5,1.8-2.4
							C3.3,19.5,3.1,17.2,2.8,15.6z" style="fill:#cbd4d9;"/>
					  </g>
					  <g>
						<g>
							<path d="M380.2,56.4c0,0-10.4-3.2-27.2-3.4c-9.8-0.1-21.3,1.6-34.8,1.4c-13.2-0.2-27.2-2-42.5-2.2
								c-14.6-0.2-29.5,2.7-44.7,2.5c-11.5-0.1-24.2-3.4-35.5-3.5C183.8,51,164.9,54.1,154,54c-11-0.2-35.2-4.1-42-4.2
								c-19.7-0.3-35.8,3-35.8,2.9c1.7-8.9,1.5-17.4,2.8-26.4c0-0.1,19.3-3.6,35.9-3.6c7.3,0,27.7,5.1,37.9,5.1c11.1,0,28.7-2.5,40.8-2.5
								c11.8,0,26.6,2.9,38.8,2.9c13.9,0,25-3,38.5-2.9c15.9,0,33.4,2.9,47.2,2.9c10.9,0,21.5-0.7,30-1.1c21.1-1.1,35,2.7,35,2.7
								l-9.3,12.5L380.2,56.4z"/>
						</g>
						<path id="SVGID_plane_bannersize" d="M380.2,72.4c0,0-10.4-3.1-27.2-3.3c-9.8-0.1-21.3,1.5-34.8,1.4
							c-13.2-0.2-27.2-1.9-42.5-2.1c-14.6-0.2-29.5,2.6-44.7,2.4c-11.5-0.1-24.2-3.3-35.5-3.4c-11.7-0.1-30.6,2.8-41.5,2.7
							c-11-0.1-35.2-4-42-4.2c-19.7-0.3-35.8,3-35.8,2.8c1.7-8.7,1.5-17,2.8-25.8c0-0.1,19.3-3.5,35.9-3.5c7.3,0,27.7,5,37.9,5
							c11.1,0,28.7-2.5,40.8-2.4c11.8,0,26.6,2.9,38.8,2.9c13.9,0,25-2.9,38.5-2.9c15.9,0,33.4,2.9,47.2,2.9c10.9,0,21.5-0.7,30-1.1
							c21.1-1.1,35,2.6,35,2.6l-9.3,12.2L380.2,72.4z" style="fill:none;"/>
              <text><textPath xlink:href="#SVGID_plane_bannersize" startOffset="51%"><tspan style="font-size:11px;"><?php echo $sitename; ?> — Healthcare Made Simple</tspan></textPath></text>
            </g>
					</svg>
				</div>
			</div>
		</div>
	</div>
</main>
<?php include 'inc/footer.php'; ?>
