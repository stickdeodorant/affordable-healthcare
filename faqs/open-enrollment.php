<?php
/**
 * FAQ Page: Open Enrollment
 */

$faqTitle = "Open Enrollment Dates & Deadlines";
$faqDescription = "Know when to enroll, key deadlines, and what happens if you miss the enrollment window.";
$currentFaqPage = 'open-enrollment';

// Custom CTA content for this page
$ctaTitle = "Don't Wait Until the Last Minute";
$ctaDescription = "Enrolling early gives you time to compare plans, ask questions, and make an informed decision. Our licensed agents are ready to help you navigate your options.";
$ctaBenefits = [
	"Compare plans before deadlines",
	"Get personalized recommendations",
	"Understand your subsidy savings"
];

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
					<h2>When Is Open Enrollment?</h2>
					<p>Open Enrollment is the annual period when you can sign up for health insurance through the ACA marketplace without needing a qualifying life event. For most states, the Open Enrollment period runs from <strong>November 1 through January 15</strong> for coverage starting the following year.</p>
					
					<p>Some states run their own marketplaces and may have extended deadlines. Check your state's specific dates to ensure you don't miss your window.</p>

					<h3 class="mt-4 mb-3">Key Deadlines to Remember</h3>
					<ul>
						<li><strong>November 1:</strong> Open Enrollment begins for most states</li>
						<li><strong>December 15:</strong> Deadline for coverage starting January 1 in most states</li>
						<li><strong>January 15:</strong> Final deadline for enrollment in federal marketplace states</li>
					</ul>
					<p>If you enroll after December 15 but before the final deadline, your coverage typically starts February 1.</p>

					<h3 class="mt-4 mb-3">What If You Miss Open Enrollment?</h3>
					<p>If you miss the Open Enrollment window, you may still qualify for a Special Enrollment Period (SEP) if you experience certain life events:</p>
					<ul>
						<li>Losing health coverage (job loss, aging off parent's plan, COBRA ending)</li>
						<li>Moving to a new ZIP code or county</li>
						<li>Getting married or divorced</li>
						<li>Having a baby or adopting a child</li>
						<li>Changes in household income affecting subsidy eligibility</li>
						<li>Gaining citizenship or lawful presence</li>
					</ul>

					<div class="mt-4">
						<a class="button bg-accent text-white mr-2 scroll-to-zip" href="#zip-form">Check My Options</a>
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
									Can I change my plan during Open Enrollment?
								</button>
							</div>
							<div id="answer1" class="collapse show" aria-labelledby="faq1" data-parent="#faqAccordion">
								<div class="card-body">
									Yes! Open Enrollment isn't just for new enrollees. If you already have marketplace coverage, this is your chance to switch plans, update your household information, or shop for better rates. Your current plan may have changed, so it's wise to compare options each year.
								</div>
							</div>
						</div>

						<div class="card">
							<div class="card-header" id="faq2">
								<button type="button" class="collapsed" data-toggle="collapse" data-target="#answer2" aria-expanded="false" aria-controls="answer2">
									What documents do I need to enroll?
								</button>
							</div>
							<div id="answer2" class="collapse" aria-labelledby="faq2" data-parent="#faqAccordion">
								<div class="card-body">
									Have ready: Social Security numbers for household members, estimated annual income, current employer and income information, policy numbers for any current health coverage, and immigration documents if applicable. This information helps determine your subsidy eligibility.
								</div>
							</div>
						</div>

						<div class="card">
							<div class="card-header" id="faq3">
								<button type="button" class="collapsed" data-toggle="collapse" data-target="#answer3" aria-expanded="false" aria-controls="answer3">
									How long is a Special Enrollment Period?
								</button>
							</div>
							<div id="answer3" class="collapse" aria-labelledby="faq3" data-parent="#faqAccordion">
								<div class="card-body">
									Most Special Enrollment Periods last 60 days from the qualifying life event. For example, if you lose coverage on March 15, you typically have until May 14 to enroll in a new plan. Coverage start dates depend on when you complete enrollment.
								</div>
							</div>
						</div>

						<div class="card">
							<div class="card-header" id="faq4">
								<button type="button" class="collapsed" data-toggle="collapse" data-target="#answer4" aria-expanded="false" aria-controls="answer4">
									Do I have to re-enroll every year?
								</button>
							</div>
							<div id="answer4" class="collapse" aria-labelledby="faq4" data-parent="#faqAccordion">
								<div class="card-body">
									If you don't take action, you'll typically be auto-renewed into your current plan or a similar one. However, premiums, benefits, and networks can change annually. We recommend reviewing your options each Open Enrollment to ensure you have the best plan for your needs and budget.
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
