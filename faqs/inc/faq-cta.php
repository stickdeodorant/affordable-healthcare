<?php
/**
 * FAQ Bottom CTA Section Template
 * 
 * Optional variables before including:
 * - $ctaTitle: CTA heading (default: "Ready to Find Your Plan?")
 * - $ctaDescription: CTA description text
 * - $ctaBenefits: Array of benefit strings for the list
 * - $ctaButtonText: Button text (default: "Get Started Now")
 */

$ctaTitle = $ctaTitle ?? "Ready to Find Your Plan?";
$ctaDescription = $ctaDescription ?? "Have your ZIP code, household size, and estimated income ready. Our licensed agents can help you understand your options and find the right coverage.";
$ctaBenefits = $ctaBenefits ?? [
	"Compare multiple plans side-by-side",
	"See your potential subsidy savings",
	"Get help from licensed professionals"
];
$ctaButtonText = $ctaButtonText ?? "Get Started Now";
?>
<section class="faq-cta">
	<svg x="0px" y="0px" viewBox="0 0 1364.8 100" preserveAspectRatio="none"><path d="M1364.8,99.8V0H0v100C273,0.1,682.5,0,1364.8,99.8z"/></svg>
	<div class="container pt-5">
		<div class="row align-items-center">
			<div class="col-lg-8">
				<h2 class="mb-3"><?= $ctaTitle ?></h2>
				<p class="mb-4"><?= $ctaDescription ?></p>
				<ul class="mb-0">
					<?php foreach ($ctaBenefits as $benefit): ?>
						<li><?= $benefit ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="col-lg-4 mt-4 mt-lg-0 text-lg-right text-center">
				<a class="button bg-white text-accent scroll-to-zip" href="#zip-form"><?= $ctaButtonText ?></a>
			</div>
		</div>
	</div>
</section>
