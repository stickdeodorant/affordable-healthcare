<?php
/**
 * FAQ Page: Subsidies & Tax Credits
 */

$faqTitle = "Health Insurance Subsidies & Tax Credits";
$faqDescription = "Learn how premium tax credits work and whether you qualify for savings on your monthly health insurance.";
$currentFaqPage = 'subsidies-tax-credits';

// Custom CTA content for this page
$ctaTitle = "Find Out What You Could Save";
$ctaDescription = "Millions of Americans qualify for subsidies but don't realize it. Get a free, personalized quote to see your potential monthly savings.";
$ctaBenefits = [
	"See your estimated subsidy amount",
	"Compare plans at your subsidized price",
	"No commitment required"
];
$ctaButtonText = "Check My Savings";

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
					<h2>What Are Premium Tax Credits?</h2>
					<p>Premium tax credits (often called subsidies) are financial assistance from the federal government that reduces your monthly health insurance premium. These credits are available to people who purchase coverage through the ACA marketplace and meet income requirements.</p>
					
					<p>The amount of your subsidy depends on your household income, family size, age, and the cost of plans in your area. Many people are surprised to find they qualify for significant savings.</p>

					<h3 class="mt-4 mb-3">Who Qualifies for Subsidies?</h3>
					<p>You may qualify for premium tax credits if:</p>
					<ul>
						<li>Your household income is between 100% and 400% of the federal poverty level (FPL)</li>
						<li>You're not eligible for affordable employer coverage or government programs like Medicaid or Medicare</li>
						<li>You file taxes (or plan to file for the coverage year)</li>
						<li>You're not claimed as a dependent on someone else's tax return</li>
					</ul>

					<h3 class="mt-4 mb-3">Cost-Sharing Reductions (CSRs)</h3>
					<p>In addition to premium tax credits, you may qualify for cost-sharing reductions if your income is below 250% FPL and you choose a Silver plan. CSRs lower your:</p>
					<ul>
						<li>Deductibles</li>
						<li>Copayments</li>
						<li>Out-of-pocket maximums</li>
					</ul>
					<p>These savings only apply to Silver-tier plans, making them especially valuable for eligible households.</p>

					<h3 class="mt-4 mb-3">How to Claim Your Subsidy</h3>
					<p>You have two options for receiving your premium tax credit:</p>
					<ul>
						<li><strong>Advance Payment:</strong> The credit is paid directly to your insurance company each month, lowering your premium bill</li>
						<li><strong>Tax Refund:</strong> Pay full price monthly and claim the credit when you file your tax return</li>
					</ul>
					<p>Most people choose advance payments to reduce their out-of-pocket costs throughout the year.</p>

					<div class="mt-4">
						<a class="button bg-accent text-white mr-2 scroll-to-zip" href="#zip-form">See My Potential Savings</a>
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
									What counts as household income for subsidies?
								</button>
							</div>
							<div id="answer1" class="collapse show" aria-labelledby="faq1" data-parent="#faqAccordion">
								<div class="card-body">
									Household income includes your Modified Adjusted Gross Income (MAGI), which is your adjusted gross income plus certain items like tax-exempt interest, non-taxable Social Security benefits, and foreign income. It includes income from all tax household members who are required to file taxes.
								</div>
							</div>
						</div>

						<div class="card">
							<div class="card-header" id="faq2">
								<button type="button" class="collapsed" data-toggle="collapse" data-target="#answer2" aria-expanded="false" aria-controls="answer2">
									What if my income changes during the year?
								</button>
							</div>
							<div id="answer2" class="collapse" aria-labelledby="faq2" data-parent="#faqAccordion">
								<div class="card-body">
									You should report income changes to the marketplace as soon as possible. If your income increases, you may need to repay some of your advance credit at tax time. If it decreases, you may qualify for more assistance. Keeping your information updated helps avoid surprises.
								</div>
							</div>
						</div>

						<div class="card">
							<div class="card-header" id="faq3">
								<button type="button" class="collapsed" data-toggle="collapse" data-target="#answer3" aria-expanded="false" aria-controls="answer3">
									Can I get subsidies if my employer offers insurance?
								</button>
							</div>
							<div id="answer3" class="collapse" aria-labelledby="faq3" data-parent="#faqAccordion">
								<div class="card-body">
									Generally, you can't get subsidies if you have access to affordable employer coverage that meets minimum value standards. However, if your employer's plan costs more than about 8.5% of your household income for employee-only coverage, it may be considered unaffordable, and you could qualify for marketplace subsidies.
								</div>
							</div>
						</div>

						<div class="card">
							<div class="card-header" id="faq4">
								<button type="button" class="collapsed" data-toggle="collapse" data-target="#answer4" aria-expanded="false" aria-controls="answer4">
									How much can I save with subsidies?
								</button>
							</div>
							<div id="answer4" class="collapse" aria-labelledby="faq4" data-parent="#faqAccordion">
								<div class="card-body">
									Savings vary widely based on income, location, age, and household size. Some people save hundreds of dollars per month. The only way to know your specific savings is to get a personalized quote. Enter your ZIP code above to see what plans and subsidies are available in your area.
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
