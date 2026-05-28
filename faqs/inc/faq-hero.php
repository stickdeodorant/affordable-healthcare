<?php
/**
 * FAQ Hero Section Template
 * 
 * Required variables before including:
 * - $faqTitle: Main H1 title
 * - $faqDescription: Subtitle/description under the title
 */
?>
<section class="faq-hero">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-lg-7">
				<h1 class="mb-3"><?= $faqTitle ?></h1>
				<p class="lead mb-4"><?= $faqDescription ?></p>
				<div class="d-flex flex-wrap gap-2">
					<a class="button bg-accent text-white mr-2 mb-2 scroll-to-zip" href="#zip-form">Get My Free Quote</a>
					<a class="button bg-white text-primary mb-2" href="#learn-more">Learn More</a>
				</div>
			</div>
			<div class="col-lg-5 mt-4 mt-lg-0">
				<div class="card" id="zip-form">
					<div class="card-body text-center">
						<h2 class="text-muted">See Plans In Your Area</h2>
						<form action="/multi-quote/" method="get">
							<div class="form-group mb-3">
								<label class="sr-only" for="hero-zip">ZIP Code</label>
								<input id="hero-zip" name="zip" class="form-control form-control-lg text-center zip-input" placeholder="Enter ZIP Code" pattern="\d{5}" maxlength="5" required>
							</div>
							<button type="submit" class="button bg-accent text-white w-100">View Plans</button>
						</form>
						<p class="small text-muted mt-3 mb-0"><i class="fa fa-lock mr-1"></i> Free, secure, no obligation</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<svg x="0px" y="0px" viewBox="0 0 1366 100" preserveAspectRatio="none"><g><path d="M0,100h836.7C614.7,100,341.5,66.7,0,0V100z"/><path d="M836.7,100H1366V0C1229.4,66.7,1058.7,100,836.7,100z"/></g></svg>
</section>
