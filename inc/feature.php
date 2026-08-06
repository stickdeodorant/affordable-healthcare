<?php

if (!function_exists('ah_feature_normalize_caption')) {
	function ah_feature_normalize_caption($featureCaption)
	{
		$caption = is_array($featureCaption) ? $featureCaption : [];
		$keys = ['default', 'mobile', 'filled', 'mfilled'];
		foreach ($keys as $key) {
			if (!isset($caption[$key])) {
				$caption[$key] = '';
			}
		}

		if (($caption['mobile'] === '' || $caption['mobile'] === null) && $caption['default'] !== '' && $caption['default'] !== null) {
			$caption['mobile'] = $caption['default'];
		}
		if (($caption['filled'] === '' || $caption['filled'] === null) && $caption['default'] !== '' && $caption['default'] !== null) {
			$caption['filled'] = $caption['default'];
		}
		if (($caption['mfilled'] === '' || $caption['mfilled'] === null) && $caption['mobile'] !== '' && $caption['mobile'] !== null) {
			$caption['mfilled'] = $caption['mobile'];
		}

		foreach ($keys as $key) {
			if ($caption[$key] === 0 || $caption[$key] === '0') {
				$caption[$key] = '';
			}
		}

		$hasContent = false;
		foreach ($keys as $key) {
			if ($caption[$key] !== '' && $caption[$key] !== null) {
				$hasContent = true;
				break;
			}
		}

		if (!$hasContent) {
			return null;
		}

		$isUniform = ($caption['default'] === $caption['mobile'])
			&& ($caption['default'] === $caption['filled'])
			&& ($caption['default'] === $caption['mfilled']);

		if ($isUniform) {
			return '<h6 id="zip-caption" class="zip-caption mt-2 mt-sm-4 font-weight-normal">' . $caption['default'] . '</h6>';
		}

		$desktop = ($caption['default'] === $caption['filled'])
			? $caption['default']
			: '<span class="zip-caption-unfilled">' . $caption['default'] . '</span><span class="zip-caption-filled">' . $caption['filled'] . '</span>';

		$mobile = ($caption['mobile'] === $caption['mfilled'])
			? $caption['mobile']
			: '<span class="zip-caption-unfilled">' . $caption['mobile'] . '</span><span class="zip-caption-filled">' . $caption['mfilled'] . '</span>';

		return '<h6 id="zip-caption" class="zip-caption mt-2 mt-sm-4 font-weight-normal">'
			. '<span class="d-none d-md-inline">' . $desktop . '</span>'
			. '<span class="d-inline d-md-none">' . $mobile . '</span>'
			. '</h6>';
	}
}

$isCall = isset($_GET['call']);
$isSingleForm = isset($_GET['form']) && $_GET['form'] === 'single';
$formAction = $isSingleForm ? '/get-quotes' : 'multi-quote';
$zipValue = isset($_GET['Zip']) ? $_GET['Zip'] : '';

// Experiment overrides (C main headline, I hero CTA line) for the ?debug=1 preview tool.
if (function_exists('ah_experiment')) {
	$ahHeadlineVariants = [
		'ppo' => 'Find a <strong>PPO</strong> Plan',
		'hmo' => 'Find an <strong>HMO</strong> Plan',
		'fast_quotes' => 'Get Healthcare Quotes in <strong>Under 30 Seconds</strong>',
		'free_quotes' => 'Find <strong>Free</strong> Healthcare Quotes',
	];
	$ahHeadline = ah_experiment('main_headline');
	if (isset($ahHeadlineVariants[$ahHeadline])) {
		$featureTitle = $ahHeadlineVariants[$ahHeadline];
	}
	$ahCtaVariants = [
		'get_30' => 'Get healthcare quotes in under 30 seconds',
		'get_quotes_30' => 'Get quotes in under 30 seconds',
		'affordable_30' => 'Affordable healthcare quotes in under 30 seconds',
		'save_hundreds' => '20 seconds could save you hundreds on your monthly premium',
	];
	$ahCta = ah_experiment('hero_cta');
	if (isset($ahCtaVariants[$ahCta])) {
		$featureSubtitle = $ahCtaVariants[$ahCta];
	}
}

$captionHtml = ah_feature_normalize_caption(isset($featureCaption) ? $featureCaption : null);
?>

<section class="container-fluid text-center d-flex flex-wrap ah-hero">
	<div class="container justify-content-center align-self-center">
		<div class="row row1 ah-hero-row">
			<div class="col-12 col-lg-6 ah-hero-copy">
				<h1 class="headline my-0"><?php echo $featureTitle; ?></h1>
				<?php if (!empty($featureSubtitle)) { ?>
					<h5 class="subheadline font-weight-normal mb-3 mt-2"><?php echo $featureSubtitle; ?>.</h5>
				<?php } ?>

				<?php if ($isCall) { ?>
					<a href="tel:<?= $phonemin['fb-call'] ?>" class="button bg-accent text-white"><?= $phone['fb-call'] ?></a>
				<?php } else { ?>
					<form class="mt-4 mb-2 mb-sm-5 my-md-0" action="<?= $formAction ?>" method="GET" autocomplete="off">
						<?php if (!$isSingleForm) { ?>
							<input type="hidden" name="step" value="0">
						<?php } ?>

						<div class="input-container d-inline-block position-relative mb-3">
							<input type="hidden" name="page" value="<?php echo $url; ?>">
							<input type="hidden" name="engine" value="">
							<input type="hidden" name="keyword" value="">
							<input type="hidden" name="gclid" value="<?= htmlspecialchars(isset($_GET['gclid']) ? $_GET['gclid'] : '', ENT_QUOTES, 'UTF-8') ?>">
							<input type="hidden" name="notes" value="">

							<?php
							$passThroughFields = [
								'First_Name',
								'Last_Name',
								'Primary_Phone',
								'Email',
								'Household_Income',
								'DOB',
								'Address',
								'city',
								'state',
								'utm_medium',
								'usha',
								'Sub_ID',
							];
							foreach ($passThroughFields as $field) {
								if (isset($_GET[$field])) {
									echo '<input name="' . htmlspecialchars($field, ENT_QUOTES, 'UTF-8') . '" type="hidden" value="' . htmlspecialchars($_GET[$field], ENT_QUOTES, 'UTF-8') . '">';
								}
							}

							if (isset($_GET['utm_campaign'])) {
								echo '<input name="adset_id" type="hidden" value="' . htmlspecialchars($_GET['utm_campaign'], ENT_QUOTES, 'UTF-8') . '">';
							}

							if (isset($_GET['ad_id'])) {
								echo '<input name="ad_id" type="hidden" value="' . htmlspecialchars($_GET['ad_id'], ENT_QUOTES, 'UTF-8') . '">';
							} elseif (isset($_GET['utm_agid'])) {
								echo '<input name="ad_id" type="hidden" value="' . htmlspecialchars($_GET['utm_agid'], ENT_QUOTES, 'UTF-8') . '">';
							} elseif (isset($_SESSION['ad_id']) && !empty($_SESSION['ad_id'])) {
								echo '<input name="ad_id" type="hidden" value="' . htmlspecialchars($_SESSION['ad_id'], ENT_QUOTES, 'UTF-8') . '">';
							}

							$pubId = isset($_GET['Pub_ID']) ? $_GET['Pub_ID'] : 'K-1';
							echo '<input name="Pub_ID" type="hidden" value="' . htmlspecialchars($pubId, ENT_QUOTES, 'UTF-8') . '">';
							?>

							<input id="zip" name="zip" type="tel" inputmode="numeric" autocomplete="postal-code" pattern="\d{5}" maxlength="5" required="" placeholder="ZIP code" aria-label="ZIP code" class="mx-auto h2 text-center mb-0 py-2 w-100" value="<?= $zipValue ?>">
							<svg xmlns="http://www.w3.org/2000/svg" id="secure-form" class="ti-lock" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
								<path fill="currentColor" d="M17 8h-1V6a4 4 0 0 0-8 0v2H7a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V10a2 2 0 0 0-2-2Zm-6 8.73V18a1 1 0 0 0 2 0v-1.27a2 2 0 1 0-2 0ZM10 8V6a2 2 0 1 1 4 0v2h-4Z"/>
							</svg>
						</div>
						<div class="bottom-nav">
							<input class="button bg-accent text-white" type="submit" value="Find Plans">
						</div>
					</form>
				<?php } ?>

				<?php if (!empty($captionHtml)) {
					echo $captionHtml;
				} ?>

				<p class="disclaimer mt-0 mb-0 small">By clicking "Find My Quote," you understand that affordable-healthcare.com is a third party lead generation website. We are not an insurance company and do not issue insurance policies. Your information may be shared with affiliate agencies, marketing companies, or service providers that may contact you about health insurance options.</p>
			</div>

			<div class="col-12 col-lg-6 ah-hero-art" aria-hidden="true">
				<div class="ah-hero-scene">
					<img src="/img/svgs/hero-bg-shape.svg" class="ah-layer ah-layer-bg-shape" alt="">
					<img src="/img/svgs/mountain.svg" class="ah-layer ah-layer-mountain" alt="">
					<img src="/img/svgs/tree1.svg" class="ah-layer ah-layer-tree-1" alt="">
					<img src="/img/svgs/tree2.svg" class="ah-layer ah-layer-tree-2" alt="">
					<img src="/img/svgs/dad-son.svg" class="ah-layer ah-layer-dad-son" alt="">
					<img src="/img/svgs/mom-daughter.svg" class="ah-layer ah-layer-mom-daughter" alt="">
					<img src="/img/svgs/bush2.svg" class="ah-layer ah-layer-bush" alt="">
					<img src="/img/svgs/cloud1.svg" class="ah-layer ah-layer-cloud ah-layer-cloud-1" alt="">
					<img src="/img/svgs/cloud2.svg" class="ah-layer ah-layer-cloud ah-layer-cloud-2" alt="">
					<img src="/img/svgs/cloud3.svg" class="ah-layer ah-layer-cloud ah-layer-cloud-3" alt="">
					<img src="/img/svgs/cloud4.svg" class="ah-layer ah-layer-cloud ah-layer-cloud-4" alt="">
				</div>
			</div>
		</div>
	</div>
</section>
