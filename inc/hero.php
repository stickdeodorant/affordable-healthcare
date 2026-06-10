<?php
/**
 * Hero section – new design
 *
 * Expects the following variables set by the including page:
 *   $featureTitle    (string)  Main headline
 *   $featureSubtitle (string)  Sub-headline, optional
 *   $featureCaption  (array)   zip-field caption, optional
 *                              keys: default | mobile | filled | mfilled
 *   $url             (string)  Hidden page-tracking value
 *   $phonemin        (array)   Minimal phone string, used in call-mode
 *   $phone           (array)   Display phone string, used in call-mode
 */

if (!function_exists('ah_caption_html')) {
	function ah_caption_html($cap)
	{
		$c = is_array($cap) ? $cap : [];
		foreach (['default', 'mobile', 'filled', 'mfilled'] as $k) {
			if (!array_key_exists($k, $c)) {
				$c[$k] = '';
			}
			if ($c[$k] === 0 || $c[$k] === '0') {
				$c[$k] = '';
			}
		}

		if ($c['mobile']  === '') { $c['mobile']  = $c['default']; }
		if ($c['filled']  === '') { $c['filled']  = $c['default']; }
		if ($c['mfilled'] === '') { $c['mfilled'] = $c['mobile'];  }

		$has = array_filter($c, fn($v) => $v !== '' && $v !== null);
		if (!$has) { return ''; }

		$isUniform = ($c['default'] === $c['mobile'] && $c['default'] === $c['filled'] && $c['default'] === $c['mfilled']);
		if ($isUniform) {
			return '<h6 id="zip-caption" class="zip-caption mt-2 mt-sm-4 font-weight-normal">' . $c['default'] . '</h6>';
		}

		$desk = ($c['default'] === $c['filled'])
			? $c['default']
			: '<span class="zip-caption-unfilled">' . $c['default'] . '</span><span class="zip-caption-filled">' . $c['filled'] . '</span>';

		$mob = ($c['mobile'] === $c['mfilled'])
			? $c['mobile']
			: '<span class="zip-caption-unfilled">' . $c['mobile'] . '</span><span class="zip-caption-filled">' . $c['mfilled'] . '</span>';

		return '<h6 id="zip-caption" class="zip-caption mt-2 mt-sm-4 font-weight-normal">'
			. '<span class="d-none d-md-inline">' . $desk . '</span>'
			. '<span class="d-inline d-md-none">' . $mob  . '</span>'
			. '</h6>';
	}
}

$_isCall       = isset($_GET['call']);
$_isSingle     = isset($_GET['form']) && $_GET['form'] === 'single';
$_formAction   = $_isSingle ? '/get-quotes' : 'multi-quote';
$_zipValue     = isset($_GET['Zip']) ? htmlspecialchars($_GET['Zip'], ENT_QUOTES, 'UTF-8') : '';
$_captionHtml  = ah_caption_html(isset($featureCaption) ? $featureCaption : []);
$_title        = isset($featureTitle)    ? $featureTitle    : '';
$_subtitle     = isset($featureSubtitle) ? $featureSubtitle : '';
$_url          = isset($url)             ? htmlspecialchars($url, ENT_QUOTES, 'UTF-8') : '';
$_gclid        = isset($_GET['gclid'])   ? htmlspecialchars($_GET['gclid'], ENT_QUOTES, 'UTF-8') : '';
$_pubId        = isset($_GET['Pub_ID'])  ? htmlspecialchars($_GET['Pub_ID'], ENT_QUOTES, 'UTF-8') : 'K-1';
?>

<section class="ah-hero">
	<div class="container h-100">
		<div class="row ah-hero-row align-items-end">

			<!-- ── Left: copy + form ───────────────────────────────────────── -->
			<div class="col-12 col-lg-6 ah-hero-copy d-flex flex-column justify-content-center pt-4 pt-lg-0 pb-3 pb-lg-5">

				<h1 class="ah-hero-title mb-2"><?php echo $_title; ?></h1>

				<?php if ($_subtitle !== '') { ?>
					<p class="ah-hero-sub mb-4"><?php echo $_subtitle; ?></p>
				<?php } ?>

				<?php if ($_isCall) { ?>

					<a href="tel:<?= htmlspecialchars(isset($phonemin['fb-call']) ? $phonemin['fb-call'] : '', ENT_QUOTES, 'UTF-8') ?>"
					   class="ah-btn button bg-accent text-white align-self-start">
						<?= htmlspecialchars(isset($phone['fb-call']) ? $phone['fb-call'] : 'Call Now', ENT_QUOTES, 'UTF-8') ?>
					</a>

				<?php } else { ?>

					<form class="ah-hero-form mt-1"
					      action="<?= htmlspecialchars($_formAction, ENT_QUOTES, 'UTF-8') ?>"
					      method="GET" autocomplete="off">
        
                        <?php if (!$_isSingle) { ?>
                            <input type="hidden" name="step"    value="0">
                        <?php } ?>
                        <input type="hidden" name="page"    value="<?= $_url ?>">
                        <input type="hidden" name="engine"  value="">
                        <input type="hidden" name="keyword" value="">
                        <input type="hidden" name="gclid"   value="<?= $_gclid ?>">
                        <input type="hidden" name="notes"   value="">
                        <input type="hidden" name="Pub_ID"  value="<?= $_pubId ?>">
        
                        <?php
                        $_pass = [
                            'First_Name', 'Last_Name', 'Primary_Phone', 'Email',
                            'Household_Income', 'DOB', 'Address', 'city', 'state',
                            'utm_medium', 'usha', 'Sub_ID',
                        ];
                        foreach ($_pass as $_f) {
                            if (isset($_GET[$_f])) {
                                echo '<input type="hidden" name="' . htmlspecialchars($_f, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($_GET[$_f], ENT_QUOTES, 'UTF-8') . '">';
                            }
                        }
                        if (isset($_GET['utm_campaign'])) {
                            echo '<input type="hidden" name="adset_id" value="' . htmlspecialchars($_GET['utm_campaign'], ENT_QUOTES, 'UTF-8') . '">';
                        }
                        if (isset($_GET['ad_id'])) {
                            echo '<input type="hidden" name="ad_id" value="' . htmlspecialchars($_GET['ad_id'], ENT_QUOTES, 'UTF-8') . '">';
                        } elseif (isset($_GET['utm_agid'])) {
                            echo '<input type="hidden" name="ad_id" value="' . htmlspecialchars($_GET['utm_agid'], ENT_QUOTES, 'UTF-8') . '">';
                        } elseif (!empty($_SESSION['ad_id'])) {
                            echo '<input type="hidden" name="ad_id" value="' . htmlspecialchars($_SESSION['ad_id'], ENT_QUOTES, 'UTF-8') . '">';
                        }
                        ?>
        
                        <div class="input-container position-relative mb-3 d-flex align-items-center">
							<input id="zip" name="zip" type="tel" inputmode="numeric"
							       pattern="\d{5}" maxlength="5" required
							       placeholder="Zip code..."
							       class="ah-zip form-control text-center h2 mb-0"
							       value="<?= $_zipValue ?>">
							<svg xmlns="http://www.w3.org/2000/svg" id="secure-icon" class="pl-3" viewBox="0 0 92.27 97.43"><defs><style>.cls-1{fill:#3a676b}</style></defs><g id="secure-icon" data-name="secure-icon"><path d="M5.86 94.66q1.74 0 2.52-.59.8-.58.79-1.66 0-.64-.27-1.1-.27-.45-.76-.83a6 6 0 0 0-1.21-.69q-.72-.32-1.63-.63-.91-.33-1.77-.72a6 6 0 0 1-1.5-.98 5 5 0 0 1-1.04-1.4 4.5 4.5 0 0 1-.4-1.96q0-2.4 1.66-3.76t4.51-1.36a10.5 10.5 0 0 1 4.97 1.17l-.99 2.6a8 8 0 0 0-1.9-.74 9 9 0 0 0-2.13-.26q-1.3 0-2.03.54t-.73 1.5q0 .58.24 1.01.24.42.69.75t1.05.61q.6.29 1.31.53 1.25.47 2.23.93t1.66 1.12 1.03 1.53c.36.88.36 1.3.36 2.13a4.4 4.4 0 0 1-1.7 3.71q-1.7 1.3-4.96 1.31a14 14 0 0 1-3.61-.49q-.7-.21-1.21-.42a7 7 0 0 1-.82-.4l.94-2.62a11 11 0 0 0 1.86.76q1.17.39 2.83.38ZM15.34 97.05V79.38h11.34v2.73h-8.13v4.36h7.24v2.68h-7.24v5.18h8.74v2.73H15.34ZM37.49 97.43q-2 0-3.57-.61a7 7 0 0 1-2.68-1.8 8 8 0 0 1-1.68-2.89q-.59-1.71-.59-3.92c0-2.21.22-2.79.67-3.92a9 9 0 0 1 1.85-2.89 8 8 0 0 1 2.75-1.8 9 9 0 0 1 3.39-.61q1.1 0 1.99.17.89.16 1.56.37a7 7 0 0 1 1.71.76l-.94 2.62q-.61-.38-1.75-.73a9 9 0 0 0-2.46-.34q-1.14 0-2.14.4t-1.72 1.18a6 6 0 0 0-1.13 1.99 10.02 10.02 0 0 0-.09 5.36q.32 1.17.98 2.02t1.68 1.33q1.02.47 2.45.47a9.3 9.3 0 0 0 4.44-.99l.87 2.62q-.3.2-.83.41a12 12 0 0 1-2.84.71q-.89.1-1.91.11ZM52.71 97.43a8 8 0 0 1-3.11-.52 5.7 5.7 0 0 1-3.41-3.69 9 9 0 0 1-.41-2.8V79.38h3.24v10.73q0 1.2.27 2.05c.27.85.43 1.03.76 1.39a3 3 0 0 0 1.17.79q.68.25 1.52.25c.84 0 1.07-.08 1.53-.25q.69-.25 1.19-.79t.76-1.39q.27-.85.27-2.05V79.38h3.24v11.04a9 9 0 0 1-.42 2.8 5.75 5.75 0 0 1-3.46 3.69 9 9 0 0 1-3.14.52M68.69 79.21q3.82 0 5.85 1.4t2.03 4.28q0 3.6-3.54 4.87a33 33 0 0 1 2.35 3.25q.63.98 1.22 2.01.58 1.03 1.05 2.03h-3.59q-.48-.92-1.05-1.85a51 51 0 0 0-3.31-4.86q-.36.02-.61.02h-2.04v6.68h-3.21V79.63q1.17-.25 2.5-.34 1.32-.09 2.37-.09Zm.23 2.78a22 22 0 0 0-1.89.08v5.71h1.4q1.17 0 2.06-.13a4 4 0 0 0 1.49-.46q.6-.33.91-.89a3 3 0 0 0 .31-1.43q0-.81-.31-1.38-.3-.56-.88-.89a4 4 0 0 0-1.36-.47q-.8-.14-1.73-.14M80.14 97.05V79.38h11.34v2.73h-8.13v4.36h7.24v2.68h-7.24v5.18h8.74v2.73H80.14ZM6.88 33.66a26.74 26.74 0 0 1 49.98-13.3 20 20 0 0 1 5.43-4.33A33.62 33.62 0 0 0 0 33.66a33.65 33.65 0 0 0 45.42 31.49v-7.49a26 26 0 0 1-11.76 2.75c-14.78 0-26.78-12-26.78-26.75" class="cls-1"/><path d="M42.92 23.41 29.73 36.6l-5.38-5.38a3.44 3.44 0 0 0-4.86 4.86l7.81 7.81a3.43 3.43 0 0 0 4.86 0l15.62-15.62a3.44 3.44 0 0 0-4.86-4.86" class="cls-1"/><path fill="#f2d046" d="M90.32 42.35a43 43 0 0 0-4.08-1.59v-6.47c0-7.7-6.26-13.96-13.96-13.96s-13.96 6.26-13.96 13.96v6.47a43 43 0 0 0-4.08 1.59 3.3 3.3 0 0 0-1.95 3.02v21.96a3.3 3.3 0 0 0 3.32 3.32h33.35a3.3 3.3 0 0 0 3.32-3.32V45.37c0-1.3-.76-2.48-1.95-3.02ZM76 61.15c.09.32.02.66-.19.93s-.52.42-.85.42h-5.37c-.33 0-.64-.15-.85-.42s-.27-.61-.19-.93l1.73-6.54a4.4 4.4 0 0 1 1.99-8.32 4.4 4.4 0 0 1 1.99 8.32zm2.54-22.19a44 44 0 0 0-12.51 0v-4.67a6.27 6.27 0 0 1 12.52 0v4.67Z"/></g></svg>
						</div>

						<button type="submit" class="ah-btn button bg-accent text-white">Find Plans</button>

					</form>

				<?php } ?>

				<?php if ($_captionHtml !== '') { echo $_captionHtml; } ?>

			</div>

			<!-- ── Right: layered SVG illustration ─────────────────────────── -->
			<div class="col-12 col-lg-6 ah-hero-art position-relative" aria-hidden="true">
				<div class="ah-hero-scene">
					<img src="/img/svgs/hero-bg-shape.svg" class="ah-layer ah-layer-bg-shape"      alt="">
					<img src="/img/svgs/mountain.svg" class="ah-layer ah-layer-mountain" alt="">
					<img src="/img/svgs/tree1.svg" class="ah-layer ah-layer-tree-1" alt="">
					<img src="/img/svgs/tree2.svg" class="ah-layer ah-layer-tree-2" alt="">
					<img src="/img/svgs/tree1.svg" class="ah-layer ah-layer-tree-3" alt="">
					<img src="/img/svgs/tree2.svg" class="ah-layer ah-layer-tree-4" alt="">
					<img src="/img/svgs/tree2.svg" class="ah-layer ah-layer-tree-5" alt="">
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
    <div class="container">
        <div class="ah-hero-disclaimer-wrap">
            <p class="ah-disclaimer small mb-0">By clicking "Find Plans," you understand that healthcare-quotes.com is a third party lead generation website. We are not an insurance company and do not issue insurance policies. Your information may be shared with affiliate agencies, marketing companies, or service providers that may contact you about health insurance options.</p>
        </div>
    </div>
</section>
