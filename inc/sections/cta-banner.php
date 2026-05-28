<section class="bg-primary">
	<svg x="0px" y="0px" viewBox="0 0 1364.8 100" style="enable-background:new 0 0 1364.8 100; z-index: 1;" xml:space="preserve"><path d="M1364.8,99.8V0H0v100C273,0.1,682.5,0,1364.8,99.8z"/></svg>
	<div class="container-fluid scale">
		<div class="container">
			<div class="row">
				<div class="col-12 col-sm-10 offset-sm-1 col-md-10 offset-md-1 text-center">
					<h3 class="h1">Find Affordable Healthcare Options</h3>
					<p style="font-style:italic;">Don't overpay for health coverage.</p>
					<p><?= $sitename ?> helps consumers request a healthcare quote and connect with affiliate agencies that may be able to discuss health insurance options. Our goal is to make the shopping process easier by helping you request information about plans that may fit your needs, budget, location, and eligibility.</p>
					<p>We do our best to work with reputable affiliate agencies. However, healthcare plans can vary, and some products may offer more limited benefits than others. Before enrolling, ask clear questions about benefits, prescriptions, doctors, maximum out of pocket exposure, and whether the plan is comprehensive health insurance coverage.</p>
					<?php if(isset($_GET['call'])) { ?><a href="tel:<?=$phonemin['fb-call']?>" class="button bg-accent text-white"><?=$phone['fb-call']?></a><?php } else { ?><div><a class="button bg-accent text-white scale-4x scroll-top" href="/#">Find Plans</a></div><?php } ?>
				</div>
			</div>
		</div>
	</div>
	<img src="/img/doctors3.svg" alt="insurance doctors" class="doctors">
	<svg x="0px" y="0px" viewBox="0 0 1366 100" style="enable-background:new 0 0 1366 100;" xml:space="preserve"><g><path d="M0,100h836.7C614.7,100,341.5,66.7,0,0V100z"/><path d="M836.7,100H1366V0C1229.4,66.7,1058.7,100,836.7,100z"/></g></svg>
</section>
