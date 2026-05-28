<?php
/**
 * FAQ Page: ACA Information
 */

$faqTitle = "Understanding ACA Health Insurance";
$faqDescription = "Learn how Affordable Care Act plans work, what they cover, and how subsidies can lower your monthly cost.";
$currentFaqPage = 'aca-information';

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
					<h2>What Is the Affordable Care Act?</h2>
					<p>The Affordable Care Act (ACA), commonly known as Obamacare, established a health insurance marketplace where Americans can shop for coverage that meets federal quality standards. Plans are organized by metal tiers—Bronze, Silver, Gold, and Platinum—each offering different balances of premiums and out-of-pocket costs.</p>
					
					<p>All ACA plans must cover essential health benefits including preventive care, emergency services, hospitalization, prescription drugs, maternity care, mental health services, and more. This ensures you get comprehensive coverage regardless of which plan you choose.</p>

					<h3 class="mt-4 mb-3">How Subsidies Work</h3>
					<p>Many Americans qualify for premium tax credits (subsidies) that significantly reduce monthly costs. Eligibility depends on:</p>
					<ul>
						<li>Your household income relative to the federal poverty level</li>
						<li>Household size and composition</li>
						<li>Your geographic location</li>
						<li>Whether you have access to other affordable coverage</li>
					</ul>
					<p>Cost-sharing reductions (CSRs) are also available for eligible households choosing Silver plans, lowering deductibles, copays, and out-of-pocket maximums.</p>

					<h3 class="mt-4 mb-3">Enrollment Periods</h3>
					<ul>
						<li><strong>Open Enrollment:</strong> Typically runs November 1 through December 15 each year for coverage starting January 1.</li>
						<li><strong>Special Enrollment:</strong> Available after qualifying life events like losing coverage, moving, getting married, or having a baby.</li>
					</ul>

					<div class="mt-4">
						<a class="button bg-accent text-white mr-2 scroll-to-zip" href="#zip-form">Start My Free Quote</a>
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
									What's the difference between Bronze, Silver, Gold, and Platinum plans?
								</button>
							</div>
							<div id="answer1" class="collapse show" aria-labelledby="faq1" data-parent="#faqAccordion">
								<div class="card-body">
									Metal tiers indicate how you and your plan share costs. Bronze plans have lower premiums but higher out-of-pocket costs when you need care. Platinum plans have higher premiums but lower costs when you use services. Silver plans are popular because they're eligible for cost-sharing reductions if you qualify.
								</div>
							</div>
						</div>

						<div class="card">
							<div class="card-header" id="faq2">
								<button type="button" class="collapsed" data-toggle="collapse" data-target="#answer2" aria-expanded="false" aria-controls="answer2">
									How do I know if I qualify for subsidies?
								</button>
							</div>
							<div id="answer2" class="collapse" aria-labelledby="faq2" data-parent="#faqAccordion">
								<div class="card-body">
									Subsidy eligibility is based on your expected household income for the year and household size. Generally, if your income is between 100% and 400% of the federal poverty level, you may qualify. Our quote process can help estimate your potential savings.
								</div>
							</div>
						</div>

						<div class="card">
							<div class="card-header" id="faq3">
								<button type="button" class="collapsed" data-toggle="collapse" data-target="#answer3" aria-expanded="false" aria-controls="answer3">
									Can I keep my current doctor with an ACA plan?
								</button>
							</div>
							<div id="answer3" class="collapse" aria-labelledby="faq3" data-parent="#faqAccordion">
								<div class="card-body">
									It depends on the plan's network. Before enrolling, you should verify that your preferred doctors, specialists, and hospitals are in-network. Each insurance carrier has different networks, so comparing options is important.
								</div>
							</div>
						</div>

						<div class="card">
							<div class="card-header" id="faq4">
								<button type="button" class="collapsed" data-toggle="collapse" data-target="#answer4" aria-expanded="false" aria-controls="answer4">
									What if I miss Open Enrollment?
								</button>
							</div>
							<div id="answer4" class="collapse" aria-labelledby="faq4" data-parent="#faqAccordion">
								<div class="card-body">
									If you miss Open Enrollment, you can still enroll during a Special Enrollment Period if you experience a qualifying life event such as losing other coverage, moving to a new area, getting married, having a baby, or changes in household income.
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
