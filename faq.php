<?php
$pageName = 'home';
include 'inc/header.php';
if (isset($state)) {
	$featureTitle = 'Compare Affordable <strong>' . $state . '</strong> Health Plans!';
	$subtitle = 'You may qualify for a plan with no monthly cost';
} else {
	$featureTitle = '<span class="text-secondary">Find Affordable</span> Healthcare!';
	$subtitle = 'You may qualify for a plan with no monthly cost';
}
$featureSubtitle = 'Don\'t overpay for health coverage';
// Use 0 or '0' for a caption value to hide the value, defaults to main otherwise
$featureCaption = [
	'default' => 'Healthcare quotes are moments away.',
	'mobile' => '',
	'filled' => '',
	'mfilled' => '',
];
?>
<main>
	<section id="faq" class="pt-4 pb-5">
		<div class="container">
			<div class="row mt-5 d-flex pb-5 text-center text-lg-left">
				<div class="col-12 text-center">
					<h3 class="h1">Frequently Asked Questions</h3>
					<p style="font-style: italic;">Before enrolling in any health plan, here are some important things to keep in mind.</p>
				</div>
			</div>
			<div class="row mt-0 d-flex pb-5 text-center text-lg-left">
				<div class="col-12">
					<div id="accordion">
						<div class="card faq-item mb-2">
							<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
								<div class="card-header" id="headingOne">
									<h5 class="card-header-title my-0">
										What is healthcare-quotes.com?
									</h5>
								</div>
							</a>
							<div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
								<div class="card-body">
									healthcare-quotes.com is a private health insurance comparison service that helps connect you with licensed agents to review plan options available in your area.
								</div>
							</div>
						</div>

						<div class="card faq-item mb-2">
							<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
								<div class="card-header" id="headingTwo">
									<h5 class="card-header-title my-0">
										Is there a cost to use this service?
									</h5>
								</div>
							</a>
							<div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
								<div class="card-body">
									No. Comparing health plan options and speaking with a licensed agent through this site does not require a fee.
								</div>
							</div>
						</div>

						<div class="card faq-item mb-2">
							<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
								<div class="card-header" id="headingThree">
									<h5 class="card-header-title my-0">
										How does the health exchange work?
									</h5>
								</div>
							</a>
							<div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
								<div class="card-body">
									The exchange lets you review plan tiers, network options, and estimated costs so you can select coverage that fits your medical and budget needs.
								</div>
							</div>
						</div>

						<div class="card faq-item mb-2">
							<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
								<div class="card-header" id="headingFour">
									<h5 class="card-header-title my-0">
										When is Open Enrollment for 2027?
									</h5>
								</div>
							</a>
							<div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#accordion">
								<div class="card-body">
									Open Enrollment dates are set federally each year, and timelines can vary by state. A licensed agent can confirm your exact enrollment window.
								</div>
							</div>
						</div>

						<div class="card faq-item mb-2">
							<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
								<div class="card-header" id="headingFive">
									<h5 class="card-header-title my-0">
										What is a Qualifying Life Event (QLE)?
									</h5>
								</div>
							</a>
							<div id="collapseFive" class="collapse" aria-labelledby="headingFive" data-parent="#accordion">
								<div class="card-body">
									A QLE is a major life change, such as losing other coverage, marriage, birth of a child, or moving, that can make you eligible for a Special Enrollment Period.
								</div>
							</div>
						</div>

						<div class="card faq-item mb-2">
							<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
								<div class="card-header" id="headingSix">
									<h5 class="card-header-title my-0">
										What should I ask about plan benefits?
									</h5>
								</div>
							</a>
							<div id="collapseSix" class="collapse" aria-labelledby="headingSix" data-parent="#accordion">
								<div class="card-body">
									Ask about deductible, copays, coinsurance, network size, specialist access, and total yearly out-of-pocket exposure.
								</div>
							</div>
						</div>

						<div class="card faq-item mb-2">
							<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
								<div class="card-header" id="headingSeven">
									<h5 class="card-header-title my-0">
										What is maximum out-of-pocket exposure?
									</h5>
								</div>
							</a>
							<div id="collapseSeven" class="collapse" aria-labelledby="headingSeven" data-parent="#accordion">
								<div class="card-body">
									It is the most you pay in a plan year for covered in-network care before the plan starts paying 100% of covered services.
								</div>
							</div>
						</div>

						<div class="card faq-item mb-2">
							<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseEight" aria-expanded="false" aria-controls="collapseEight">
								<div class="card-header" id="headingEight">
									<h5 class="card-header-title my-0">
										Will my prescriptions be covered?
									</h5>
								</div>
							</a>
							<div id="collapseEight" class="collapse" aria-labelledby="headingEight" data-parent="#accordion">
								<div class="card-body">
									Most plans include prescription coverage, but formularies vary. Check your medications against each plan before enrolling.
								</div>
							</div>
						</div>

						<div class="card faq-item mb-2">
							<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseNine" aria-expanded="false" aria-controls="collapseNine">
								<div class="card-header" id="headingNine">
									<h5 class="card-header-title my-0">
										Can I keep my doctor?
									</h5>
								</div>
							</a>
							<div id="collapseNine" class="collapse" aria-labelledby="headingNine" data-parent="#accordion">
								<div class="card-body">
									You may be able to if your doctor is in-network. Always verify provider participation before selecting a plan.
								</div>
							</div>
						</div>

						<div class="card faq-item mb-2">
							<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTen" aria-expanded="false" aria-controls="collapseTen">
								<div class="card-header" id="headingTen">
									<h5 class="card-header-title my-0">
										What does "comprehensive coverage" mean?
									</h5>
								</div>
							</a>
							<div id="collapseTen" class="collapse" aria-labelledby="headingTen" data-parent="#accordion">
								<div class="card-body">
									Comprehensive plans generally include preventive care, doctor visits, hospital services, emergency care, and prescription benefits.
								</div>
							</div>
						</div>

						<div class="card faq-item mb-2">
							<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseEleven" aria-expanded="false" aria-controls="collapseEleven">
								<div class="card-header" id="headingEleven">
									<h5 class="card-header-title my-0">
										What if I feel pressured to enroll?
									</h5>
								</div>
							</a>
							<div id="collapseEleven" class="collapse" aria-labelledby="headingEleven" data-parent="#accordion">
								<div class="card-body">
									You should never feel pressured. Ask for written details, compare options, and enroll only when you fully understand plan costs and coverage.
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</main>
<?php include 'inc/footer.php'; ?>