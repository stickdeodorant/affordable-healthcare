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
	<section class="container-fluid">
		<div class="container">
			<div class="row mt-5 d-flex pb-5 text-center text-lg-left">
				<div class="col-12 text-center">
					<h3 class="h1">Smart Shopping Before You Enroll</h3>
					<p style="font-size: 1rem; font-style: italic;">Affordable Healthcare helps consumers request a healthcare quote and connect with affiliate agencies that may be able to discuss available health insurance options. Although we trust you are in good hands, there are a lot of barebones products out there. It is important that you ask your agent clear questions before enrolling in any plan.</p>
				</div>
			</div>
			<div class="row mt-0 d-flex pb-5 text-center text-lg-left">
				<div class="col-12">
					<p>There are many types of health insurance products in the market. Some plans may be comprehensive coverage. Some plans may only cover limited benefits. Some may not cover your doctors, prescriptions, hospital visits, accidents, surgeries, or major medical events the way you expect. That does not mean every limited product is bad, but it does mean you should understand exactly what you are buying before you agree to enroll.</p>
					<p>Before enrolling, ask the agent what the benefits are. Do not just ask what the monthly payment is. Ask what the plan actually covers. Ask whether doctor visits are covered. Ask whether hospital visits are covered. Ask whether emergency care is covered. Ask whether surgery, imaging, specialists, lab work, and follow up care are covered.</p>
					<p>You should also ask about your maximum out of pocket exposure. If you have an accident or a serious illness, what could you owe? Are there deductibles, copays, coinsurance, balance bills, coverage caps, or services that are not covered? God forbid something happens, you want to know whether the plan is actually protecting you.</p>
					<p>Prescription coverage is also important. Ask whether your medications are covered. Ask whether the plan covers the prescriptions you currently take. Ask whether there are restrictions, prior authorizations, generic requirements, or higher costs for certain medications.</p>
					<p>You should ask whether your doctor accepts the plan. You should also ask whether your hospital, pharmacy, and specialists are in network. A plan can sound good on the phone but still not work for you if your doctor does not accept it.</p>
					<p>Most importantly, ask whether the plan is comprehensive health insurance coverage. Ask directly. If the plan is not comprehensive coverage, ask what type of product it is. Is it a limited benefit plan? Is it short term coverage? Is it fixed indemnity? Is it supplemental? Is it a discount product? Make sure the answer is clear.</p>
					<p>Avoid high pressure sales tactics. If an agent pushes you to enroll before answering your questions, that is a warning sign. If the benefits are not explained, that is a warning sign. If you cannot get plan documents, that is a warning sign.</p>
					<p>We wish you the best of luck in finding coverage that works for you. Be cautious, ask questions, and make sure you understand the plan before enrolling.</p>
				</div>
			</div>
		</div>
	</section>
</main>
<?php include 'inc/footer.php'; ?>