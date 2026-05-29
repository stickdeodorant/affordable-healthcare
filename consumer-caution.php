<?php
$pageName = 'home';
include 'inc/header.php';
if (isset($state)) {
	$featureTitle = 'Compare Affordable <strong>' . $state . '</strong> Health Plans!';
	$subtitle = 'You may qualify for a plan with no monthly cost';
} else {
	$featureTitle = 'Find Affordable Healthcare!';
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
					<h3 class="h1">Consumer Caution / Buyer Beware</h3>
					<p style="font-style: italic;">Before enrolling in any health plan, here are some important things to keep in mind.</p>
				</div>
			</div>
			<div class="row mt-0 d-flex pb-5 text-center text-lg-left">
				<div class="col-12">
					<p><?= $sitename ?> is a third party lead generation website. We are not an insurance company. We do not issue insurance policies. We do not enroll you directly into coverage. We do not control the final plan, price, benefits, availability, or coverage you may be offered by an affiliate agency.</p>
					<p>We do our best to work with reputable affiliate agencies. However, your information may be shared, sold, or transferred to affiliate agencies, marketing companies, or service providers. These agencies or companies may contact you in an effort to discuss or sell you a health insurance plan.</p>
					<p>We want to be clear about that because consumers should understand how this process works. This website helps generate consumer interest and connect that interest with affiliate agencies that may be able to discuss coverage. We may not be positioned to offer you a quote directly. Our role is to connect consumers with affiliate agencies that may be able to discuss coverage options.</p>
					<p>We do our best to ensure you are in good hands, but we are a lead generation company and cannot make any claims about coverage. We cannot make claims or guarantees about the plan you may be offered, what price you may be quoted, what benefits will be available, whether you will qualify, or whether the plan will be comprehensive health insurance coverage.</p>
					<p>Healthcare is volatile and things change rapidly. Plans change. Rules change. Enrollment periods change. Agency availability changes. State availability can change. Because of that, we cannot make assurances that an affiliate agency contacting you will be able to offer you comprehensive health insurance coverage. That does not mean you do not have options. It means you should ask clear questions before enrolling.</p>
					<p>Before you buy any plan, ask what the benefits are. Ask what the plan covers. Ask what it does not cover. Ask whether your prescriptions are covered. Ask whether your doctor accepts the plan. Ask what happens if you have an accident. Ask what happens if you need surgery. Ask what happens if you are hospitalized. Ask what your maximum out of pocket exposure could be.</p>
					<p>Also ask whether the plan is comprehensive coverage. Some products in the market are barebones products. Some may be limited benefit plans, short term plans, fixed indemnity products, supplemental products, discount products, or other non comprehensive options. These plans may not work the way you expect if you have a serious medical need.</p>
					<p>Avoid high pressure sales tactics. If an agent makes you feel rushed, uneasy, confused, or pressured, slow down. If the agent does not explain the benefits, slow down. If the agent will not provide plan documents, slow down. If the plan sounds too good to be true, ask more questions.</p>
					<p>If you have questions, feel uneasy, or believe you were contacted in a way that was unclear or inappropriate, please email us at <a href="mailto:support@<?= $domain ?>">support@<?= $domain ?></a>. We may not be able to resolve every issue, but we want consumers to have a place to send concerns.</p>
					<p>We wish you the best of luck and encourage you to shop carefully, ask questions, and make sure the benefits are explained before enrolling.</p>
				</div>
			</div>
		</div>
	</section>
</main>
<?php include 'inc/footer.php'; ?>