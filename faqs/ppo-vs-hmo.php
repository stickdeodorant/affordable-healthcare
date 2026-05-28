<?php
/**
 * FAQ Page: PPO vs HMO
 */

$faqTitle = "PPO vs HMO: Understanding Plan Types";
$faqDescription = "Compare health plan types to find the right balance of flexibility, cost, and coverage for your needs.";
$currentFaqPage = 'ppo-vs-hmo';

// Custom CTA content for this page
$ctaTitle = "Find the Right Plan Type for You";
$ctaDescription = "Our licensed agents can help you understand which plan type fits your healthcare needs and budget. Compare PPO, HMO, and other options side by side.";
$ctaBenefits = [
	"See all available plan types in your area",
	"Compare costs and benefits",
	"Check if your doctors are in-network"
];
$ctaButtonText = "Compare Plans Now";

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
					<h2>What's the Difference Between PPO and HMO?</h2>
					<p>PPO (Preferred Provider Organization) and HMO (Health Maintenance Organization) are two common types of health insurance plans. The main differences involve network flexibility, costs, and how you access care.</p>

					<h3 class="mt-4 mb-3">HMO Plans</h3>
					<p>HMO plans typically offer lower premiums and out-of-pocket costs in exchange for less flexibility:</p>
					<ul>
						<li><strong>Primary Care Physician (PCP):</strong> You choose a PCP who coordinates all your care</li>
						<li><strong>Referrals Required:</strong> You need a referral from your PCP to see specialists</li>
						<li><strong>In-Network Only:</strong> Generally, only in-network care is covered (except emergencies)</li>
						<li><strong>Lower Costs:</strong> Usually lower premiums and copays than PPOs</li>
					</ul>

					<h3 class="mt-4 mb-3">PPO Plans</h3>
					<p>PPO plans offer more flexibility but typically cost more:</p>
					<ul>
						<li><strong>No PCP Required:</strong> You don't need to choose a primary care physician</li>
						<li><strong>No Referrals:</strong> You can see specialists directly without a referral</li>
						<li><strong>Out-of-Network Coverage:</strong> You can see out-of-network providers at a higher cost</li>
						<li><strong>Higher Premiums:</strong> Monthly costs are usually higher than HMOs</li>
					</ul>

					<h3 class="mt-4 mb-3">Other Plan Types</h3>
					<p>Beyond PPO and HMO, you may also encounter:</p>
					<ul>
						<li><strong>EPO (Exclusive Provider Organization):</strong> Like a PPO but without out-of-network coverage</li>
						<li><strong>POS (Point of Service):</strong> Combines HMO and PPO features; requires a PCP but allows out-of-network care</li>
						<li><strong>HDHP (High Deductible Health Plan):</strong> Lower premiums with higher deductibles; often paired with HSA</li>
					</ul>

					<div class="mt-4">
						<a class="button bg-accent text-white mr-2 scroll-to-zip" href="#zip-form">See Plans In My Area</a>
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
									Which is better: PPO or HMO?
								</button>
							</div>
							<div id="answer1" class="collapse show" aria-labelledby="faq1" data-parent="#faqAccordion">
								<div class="card-body">
									Neither is universally better—it depends on your needs. Choose an HMO if you want lower costs and don't mind using a primary care doctor for referrals. Choose a PPO if you want flexibility to see specialists directly or use out-of-network providers. Consider your health needs, budget, and preferred doctors.
								</div>
							</div>
						</div>

						<div class="card">
							<div class="card-header" id="faq2">
								<button type="button" class="collapsed" data-toggle="collapse" data-target="#answer2" aria-expanded="false" aria-controls="answer2">
									Can I see my current doctor with any plan type?
								</button>
							</div>
							<div id="answer2" class="collapse" aria-labelledby="faq2" data-parent="#faqAccordion">
								<div class="card-body">
									It depends on the plan's network. Before enrolling, check whether your preferred doctors and hospitals are in-network. With a PPO, you can still see out-of-network doctors but will pay more. With an HMO, out-of-network care typically isn't covered except in emergencies.
								</div>
							</div>
						</div>

						<div class="card">
							<div class="card-header" id="faq3">
								<button type="button" class="collapsed" data-toggle="collapse" data-target="#answer3" aria-expanded="false" aria-controls="answer3">
									What happens in an emergency with an HMO?
								</button>
							</div>
							<div id="answer3" class="collapse" aria-labelledby="faq3" data-parent="#faqAccordion">
								<div class="card-body">
									Emergency care is covered regardless of whether the facility is in-network. All ACA-compliant plans must cover emergency services without prior authorization. After you're stabilized, your plan may require transfer to an in-network facility for continued care.
								</div>
							</div>
						</div>

						<div class="card">
							<div class="card-header" id="faq4">
								<button type="button" class="collapsed" data-toggle="collapse" data-target="#answer4" aria-expanded="false" aria-controls="answer4">
									What is an HSA-eligible plan?
								</button>
							</div>
							<div id="answer4" class="collapse" aria-labelledby="faq4" data-parent="#faqAccordion">
								<div class="card-body">
									An HSA-eligible plan is a High Deductible Health Plan (HDHP) that qualifies you to open a Health Savings Account. HSAs let you save pre-tax money for medical expenses. The funds roll over year to year and can even be invested. These plans have lower premiums but higher deductibles.
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
