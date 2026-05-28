<?php
/**
 * FAQ Page: Short-Term Health Insurance
 */

$faqTitle = "Short-Term Health Insurance Plans";
$faqDescription = "Temporary coverage for gaps between jobs, waiting periods, or when you need quick, affordable protection.";
$currentFaqPage = 'short-term-health';

// Custom CTA content for this page
$ctaTitle = "Need Coverage Now?";
$ctaDescription = "Don't go uninsured during a gap in coverage. Compare short-term and ACA options to find the right fit for your situation and budget.";
$ctaBenefits = [
	"Coverage can start tomorrow",
	"Compare short-term and ACA plans",
	"Licensed agents to guide you"
];
$ctaButtonText = "Get Covered Fast";

include __DIR__ . '/../inc/header.php';
?>
<main>
	<?php include __DIR__ . '/inc/faq-hero.php'; ?>
	<?php include __DIR__ . '/inc/faq-tabs.php'; ?>

	<!-- Main Content Section -->
	<section id="learn-more" class="faq-content">
		<div class="container">
			<div class="row">
				<!-- Main Content Column -->
				<div class="col-lg-8">
					<h2>What Is Short-Term Health Insurance?</h2>
					<p>Short-term health insurance provides temporary medical coverage for people who need protection during gaps in their regular insurance. These plans can start quickly—sometimes the next day—and typically last from 30 days to 12 months, with some states allowing renewals up to 36 months.</p>
					
					<p>Short-term plans are designed for temporary situations, not as a replacement for comprehensive ACA coverage.</p>

					<h3 class="mt-4 mb-3">When Short-Term Plans Make Sense</h3>
					<ul>
						<li><strong>Between Jobs:</strong> Coverage while waiting for new employer benefits to start</li>
						<li><strong>Missed Open Enrollment:</strong> Protection until the next enrollment period</li>
						<li><strong>Aging Off Parent's Plan:</strong> Bridge coverage after turning 26</li>
						<li><strong>Waiting for Medicare:</strong> Coverage before Medicare eligibility at 65</li>
						<li><strong>Recent Graduates:</strong> Temporary coverage while job searching</li>
						<li><strong>Early Retirees:</strong> Coverage before Medicare kicks in</li>
					</ul>

					<h3 class="mt-4 mb-3">What Short-Term Plans Cover</h3>
					<p>Coverage varies by plan and insurer, but typically includes:</p>
					<ul>
						<li>Doctor visits and urgent care</li>
						<li>Emergency room visits</li>
						<li>Hospital stays</li>
						<li>Surgery and anesthesia</li>
						<li>Diagnostic tests and lab work</li>
					</ul>

					<h3 class="mt-4 mb-3">Important Limitations</h3>
					<p>Short-term plans are not ACA-compliant and have significant differences:</p>
					<ul>
						<li><strong>Pre-existing Conditions:</strong> Usually not covered</li>
						<li><strong>No Essential Health Benefits:</strong> May not cover maternity, mental health, or prescriptions</li>
						<li><strong>No Subsidies:</strong> Premium tax credits don't apply</li>
						<li><strong>Medical Underwriting:</strong> You may be denied based on health history</li>
					</ul>

					<div class="mt-4">
						<a class="button bg-accent text-white mr-2 scroll-to-zip" href="#zip-form">See Short-Term Options</a>
					</div>
				</div>

				<?php include __DIR__ . '/inc/faq-sidebar.php'; ?>
			</div>

			<!-- FAQ Accordion -->
			<div class="row mt-5">
				<div class="col-lg-8">
					<h2 class="mb-4">Frequently Asked Questions</h2>
					<div class="faq-accordion" id="faqAccordion">
						<div class="card">
							<div class="card-header" id="faq1">
								<button type="button" data-toggle="collapse" data-target="#answer1" aria-expanded="true" aria-controls="answer1">
									How quickly can short-term coverage start?
								</button>
							</div>
							<div id="answer1" class="collapse show" aria-labelledby="faq1" data-parent="#faqAccordion">
								<div class="card-body">
									One of the biggest advantages of short-term plans is fast enrollment. Many plans can start as soon as the next day after you apply and are approved. This makes them ideal for urgent coverage needs when you can't wait for the next ACA Open Enrollment period.
								</div>
							</div>
						</div>

						<div class="card">
							<div class="card-header" id="faq2">
								<button type="button" class="collapsed" data-toggle="collapse" data-target="#answer2" aria-expanded="false" aria-controls="answer2">
									Are short-term plans cheaper than ACA plans?
								</button>
							</div>
							<div id="answer2" class="collapse" aria-labelledby="faq2" data-parent="#faqAccordion">
								<div class="card-body">
									Short-term plans often have lower premiums than ACA plans because they provide less comprehensive coverage and can deny applicants with pre-existing conditions. However, if you qualify for ACA subsidies, a marketplace plan may actually be more affordable and provide better coverage.
								</div>
							</div>
						</div>

						<div class="card">
							<div class="card-header" id="faq3">
								<button type="button" class="collapsed" data-toggle="collapse" data-target="#answer3" aria-expanded="false" aria-controls="answer3">
									Can I renew my short-term plan?
								</button>
							</div>
							<div id="answer3" class="collapse" aria-labelledby="faq3" data-parent="#faqAccordion">
								<div class="card-body">
									It depends on your state and the specific plan. Some states limit short-term plans to 3 months with no renewals, while others allow plans up to 12 months with renewals for up to 36 months total. Be aware that renewing may require new medical underwriting.
								</div>
							</div>
						</div>

						<div class="card">
							<div class="card-header" id="faq4">
								<button type="button" class="collapsed" data-toggle="collapse" data-target="#answer4" aria-expanded="false" aria-controls="answer4">
									Should I choose short-term or ACA coverage?
								</button>
							</div>
							<div id="answer4" class="collapse" aria-labelledby="faq4" data-parent="#faqAccordion">
								<div class="card-body">
									If you're generally healthy and need temporary coverage for a specific gap, short-term may work well. If you have ongoing health needs, pre-existing conditions, or qualify for subsidies, an ACA plan is usually better. A licensed agent can help you compare your specific options and costs.
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<?php include __DIR__ . '/inc/faq-cta.php'; ?>
</main>

<?php include __DIR__ . '/inc/faq-scripts.php'; ?>
<?php include __DIR__ . '/../inc/footer.php'; ?>
