<?php
/**
 * FAQ Page: Medicare Basics
 */

$faqTitle = "Medicare Basics: What You Need to Know";
$faqDescription = "Understand Medicare eligibility, parts, enrollment periods, and how to choose the right coverage.";
$currentFaqPage = 'medicare-basics';

// Custom CTA content for this page
$ctaTitle = "Navigate Medicare With Confidence";
$ctaDescription = "Medicare can be confusing with its multiple parts and enrollment periods. Our licensed agents can help you understand your options and find the right coverage.";
$ctaBenefits = [
	"Compare Medicare Advantage plans",
	"Understand Medigap options",
	"Get help with enrollment"
];
$ctaButtonText = "Explore Options";

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
					<h2>What Is Medicare?</h2>
					<p>Medicare is a federal health insurance program primarily for people 65 and older, though younger individuals with certain disabilities or conditions may also qualify. Understanding Medicare's different parts helps you choose the right coverage.</p>

					<h3 class="mt-4 mb-3">The Four Parts of Medicare</h3>
					<ul>
						<li><strong>Part A (Hospital Insurance):</strong> Covers inpatient hospital stays, skilled nursing facility care, hospice, and some home health care. Most people don't pay a premium for Part A.</li>
						<li><strong>Part B (Medical Insurance):</strong> Covers doctor visits, outpatient care, preventive services, and medical equipment. There's a monthly premium for Part B.</li>
						<li><strong>Part C (Medicare Advantage):</strong> Private insurance plans that combine Parts A and B, often including Part D and extra benefits like dental and vision.</li>
						<li><strong>Part D (Prescription Drug Coverage):</strong> Covers prescription medications. Available as a standalone plan or included in Medicare Advantage.</li>
					</ul>

					<h3 class="mt-4 mb-3">Who Is Eligible?</h3>
					<p>You're eligible for Medicare if you:</p>
					<ul>
						<li>Are 65 or older and a U.S. citizen or permanent resident</li>
						<li>Are under 65 with certain disabilities and have received Social Security disability benefits for 24 months</li>
						<li>Have End-Stage Renal Disease (ESRD) or ALS at any age</li>
					</ul>

					<h3 class="mt-4 mb-3">Medicare Enrollment Periods</h3>
					<ul>
						<li><strong>Initial Enrollment Period:</strong> 7 months around your 65th birthday (3 months before, your birthday month, and 3 months after)</li>
						<li><strong>General Enrollment:</strong> January 1 – March 31 each year (coverage starts July 1)</li>
						<li><strong>Open Enrollment (Medicare Advantage):</strong> October 15 – December 7 each year</li>
					</ul>

					<div class="mt-4">
						<a class="button bg-accent text-white mr-2 scroll-to-zip" href="#zip-form">See Medicare Options</a>
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
									What's the difference between Original Medicare and Medicare Advantage?
								</button>
							</div>
							<div id="answer1" class="collapse show" aria-labelledby="faq1" data-parent="#faqAccordion">
								<div class="card-body">
									Original Medicare (Parts A & B) is provided directly by the federal government and lets you see any doctor that accepts Medicare. Medicare Advantage (Part C) is offered by private insurers, often includes drug coverage and extra benefits, but typically requires you to use network providers. Many Advantage plans have $0 or low premiums.
								</div>
							</div>
						</div>

						<div class="card">
							<div class="card-header" id="faq2">
								<button type="button" class="collapsed" data-toggle="collapse" data-target="#answer2" aria-expanded="false" aria-controls="answer2">
									Do I need a Medigap (Supplement) plan?
								</button>
							</div>
							<div id="answer2" class="collapse" aria-labelledby="faq2" data-parent="#faqAccordion">
								<div class="card-body">
									Medigap plans help cover costs that Original Medicare doesn't pay, like copayments, coinsurance, and deductibles. If you choose Medicare Advantage, you can't use a Medigap plan. Consider your healthcare usage and budget when deciding if a supplement makes sense for you.
								</div>
							</div>
						</div>

						<div class="card">
							<div class="card-header" id="faq3">
								<button type="button" class="collapsed" data-toggle="collapse" data-target="#answer3" aria-expanded="false" aria-controls="answer3">
									What if I'm still working at 65?
								</button>
							</div>
							<div id="answer3" class="collapse" aria-labelledby="faq3" data-parent="#faqAccordion">
								<div class="card-body">
									If you have employer coverage through your or your spouse's current job, you may be able to delay Medicare without penalty. The rules depend on employer size. When your employment or coverage ends, you'll have a Special Enrollment Period to sign up. Consult with a licensed agent to understand your options.
								</div>
							</div>
						</div>

						<div class="card">
							<div class="card-header" id="faq4">
								<button type="button" class="collapsed" data-toggle="collapse" data-target="#answer4" aria-expanded="false" aria-controls="answer4">
									Are there penalties for late enrollment?
								</button>
							</div>
							<div id="answer4" class="collapse" aria-labelledby="faq4" data-parent="#faqAccordion">
								<div class="card-body">
									Yes. If you don't enroll in Part B when first eligible and don't have qualifying coverage, you may pay a late enrollment penalty—10% higher premiums for each 12-month period you could have had Part B but didn't. Part D also has late enrollment penalties. It's important to understand your enrollment windows.
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
