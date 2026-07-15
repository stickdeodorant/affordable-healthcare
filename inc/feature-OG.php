<?php $safeGet = array_map(function($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }, $_GET); ?>
<header class="container-fluid text-center d-flex flex-wrap">
	<div class="container justify-content-center align-self-center">
		<div class="row row1">
			<div class="col-12">
				<h1 class="headline my-0" style="text-transform:uppercase;"><?php echo $featureTitle; ?></h1>
				<h5 class="subheadline font-weight-normal mb-3 mt-2" style="text-transform:capitalize;"><?php echo $featureSubtitle; ?>.</h5>
				<?php if (isset($_GET['call'])) { ?>
					<a href="tel:<?= $phonemin['fb-call'] ?>" class="button bg-accent text-white"><?= $phone['fb-call'] ?></a>
				<?php } else { ?>
					<?php if (isset($_GET['form']) && $_GET['form'] == 'single') { ?>
						<form class="zipcode-form mt-4 mb-5 my-md-0" action="/get-quotes" method="GET" autocomplete="off">
						<?php } else { ?>
							<form class="zipcode-form mt-4 mb-5 my-md-0" action="/multi-quote" method="GET" autocomplete="off">
								<input type="hidden" name="step" value="0">
							<?php } ?>
							<?php /*<label for="zip" class="d-block w-100 mt-3 mb-1">My zip code is...</label>*/ ?>
							<div class="input-container d-inline-block position-relative mb-3">
								<input type="hidden" name="page" value="<?php echo $url; ?>">
								<input type="hidden" name="engine" value="">
								<input type="hidden" name="keyword" value="">
								<input type="hidden" name="gclid" value="<?= $safeGet['gclid'] ?? '' ?>">
								<input type="hidden" name="notes" value="">

								<?php if (isset($_GET['First_Name'])) { ?>
									<input name="First_Name" type="hidden" value="<?= $safeGet['First_Name'] ?? '' ?>">
								<?php } ?>
								<?php if (isset($_GET['Last_Name'])) { ?>
									<input name="Last_Name" type="hidden" value="<?= $safeGet['Last_Name'] ?? '' ?>">
								<?php } ?>
								<?php if (isset($_GET['Primary_Phone'])) { ?>
									<input name="Primary_Phone" type="hidden" value="<?= $safeGet['Primary_Phone'] ?? '' ?>">
								<?php } ?>
								<?php if (isset($_GET['Email'])) { ?>
									<input name="Email" type="hidden" value="<?= $safeGet['Email'] ?? '' ?>">
								<?php } ?>
								<?php if (isset($_GET['Household_Income'])) { ?>
									<input name="Household_Income" type="hidden" value="<?= $safeGet['Household_Income'] ?? '' ?>">
								<?php } ?>
								<?php if (isset($_GET['DOB'])) { ?>
									<input name="DOB" type="hidden" value="<?= $safeGet['DOB'] ?? '' ?>">
								<?php } ?>
								<?php if (isset($_GET['Address'])) { ?>
									<input name="Address" type="hidden" value="<?= $safeGet['Address'] ?? '' ?>">
								<?php } ?>
								<?php if (isset($_GET['city'])) { ?>
									<input name="city" type="hidden" value="<?= $safeGet['city'] ?? '' ?>">
								<?php } ?>
								<?php if (isset($_GET['state'])) { ?>
									<input name="state" type="hidden" value="<?= $safeGet['state'] ?? '' ?>">
								<?php } ?>
								<?php if (isset($_GET['ad_id'])) { ?>
									<input name="ad_id" type="hidden" value="<?= $safeGet['ad_id'] ?? '' ?>">
								<?php } elseif (isset($_GET['utm_agid'])) { ?>
									<input name="ad_id" type="hidden" value="<?= $safeGet['utm_agid'] ?? '' ?>">
								<?php } elseif (isset($_GET['utm_agid'])) { ?>
									<input name="ad_id" type="hidden" value="<?= $safeGet['utm_agid'] ?? '' ?>">
								<?php } elseif (isset($_SESSION['ad_id']) && !empty($_SESSION['ad_id'])) { ?>
									<input name="ad_id" type="hidden" value="<?= htmlspecialchars((string)$_SESSION['ad_id'], ENT_QUOTES, 'UTF-8') ?>">
								<?php } ?>
								<?php if (isset($_GET['utm_campaign'])) { ?>
									<input name="adset_id" type="hidden" value="<?= $safeGet['utm_campaign'] ?? '' ?>">
								<?php } ?>
								<?php if (isset($_GET['Pub_ID'])) { ?>
									<input name="Pub_ID" type="hidden" value="<?= $safeGet['Pub_ID'] ?? '' ?>">
								<?php } ?>
								<?php if (isset($_GET['Sub_ID'])) { ?>
									<input name="Sub_ID" type="hidden" value="<?= $safeGet['Sub_ID'] ?? '' ?>">
								<?php } ?>
								<?php if (isset($_GET['utm_medium'])) { ?>
									<input name="utm_medium" type="hidden" value="<?= $safeGet['utm_medium'] ?? '' ?>">
								<?php } ?>

								<!-- <input name="zip" type="tel" placeholder="My zip code is..." class="form-control" maxlength="5" style="width: 90%; float: left;"> -->
								<input id="zip" name="zip" type="tel" pattern="\d{5}" maxlength="5" required="" placeholder="Zip code..." class="mx-auto h2 text-center mb-0 py-2 w-100" <?php if (isset($_GET['Zip'])) {
																								echo 'value="' . ($safeGet['Zip'] ?? '') . '"';
																							} ?>>
								<i class="ti ti-lock"></i>
							</div>
							<div class="bottom-nav">
								<input class="button bg-accent text-white" type="submit" value="Find Plans">
							</div>
							</form>
						<?php } ?>
						<?php if (!empty($featureCaption['default']) || !empty($featureCaption['mobile']) || !empty($featureCaption['filled']) || !empty($featureCaption['mfilled'])) {
							if ((empty($featureCaption['mobile']) && $featureCaption['mobile'] !== 0 && $featureCaption['mobile'] !== '0') && !empty($featureCaption['default'])) {
								$featureCaption['mobile'] = $featureCaption['default'];
							}
							if ((empty($featureCaption['mfilled']) && $featureCaption['mfilled'] !== 0 && $featureCaption['mfilled'] !== '0') && !empty($featureCaption['filled'])) {
								$featureCaption['mfilled'] = $featureCaption['filled'];
							}
							if ((empty($featureCaption['filled']) && $featureCaption['filled'] !== 0 && $featureCaption['filled'] !== '0') && !empty($featureCaption['default'])) {
								$featureCaption['filled'] = $featureCaption['default'];
							}
							if ((empty($featureCaption['mfilled']) && $featureCaption['mfilled'] !== 0 && $featureCaption['mfilled'] !== '0') && !empty($featureCaption['mobile'])) {
								$featureCaption['mfilled'] = $featureCaption['mobile'];
							}
							if ($featureCaption['filled'] === 0 || $featureCaption['filled'] === '0') {
								if ($featureCaption['default'] === 0 || $featureCaption['default'] === '0') {
									$featureCaption['filled'] = '';
									$featureCaption['default'] = '';
								} else {
									$featureCaption['filled'] = $featureCaption['default'];
								}
							} else if ($featureCaption['default'] === 0 || $featureCaption['default'] === '0') {
								$featureCaption['default'] = '';
							}
							if ($featureCaption['mfilled'] === 0 || $featureCaption['mfilled'] === '0') {
								if ($featureCaption['mobile'] === 0 || $featureCaption['mobile'] === '0') {
									$featureCaption['mfilled'] = '';
									$featureCaption['mobile'] = '';
								} else {
									$featureCaption['mfilled'] = $featureCaption['mobile'];
								}
							} else if ($featureCaption['mobile'] === 0 || $featureCaption['mobile'] === '0') {
								$featureCaption['mobile'] = '';
							}
							echo '<h6 id="zip-caption" class="zip-caption mt-4 font-weight-normal" style="text-transform:capitalize;">';
							if ($featureCaption === array_fill(0, count($featureCaption), $featureCaption[0])) {
								echo $featureCaption['default'];
							} else if ((!empty($featureCaption['default']) || !empty($featureCaption['filled'])) && (!empty($featureCaption['mobile']) || !empty($featureCaption['mfilled']))) {
								if ($featureCaption['default'] == $featureCaption['filled']) {
									echo '<span class="d-none d-md-inline">' . $featureCaption['default'] . '</span>';
								} else {
									echo '<span class="d-none d-md-inline"><span class="zip-caption-unfilled">' . $featureCaption['default'] . '</span><span class="zip-caption-filled">' . $featureCaption['filled'] . '</span></span>';
								}
								if ($featureCaption['mobile'] == $featureCaption['mfilled']) {
									echo '<span class="d-inline d-md-none">' . $featureCaption['mobile'] . '</span>';
								} else {
									echo '<span class="d-inline d-md-none"><span class="zip-caption-unfilled">' . $featureCaption['mobile'] . '</span><span class="zip-caption-filled">' . $featureCaption['mfilled'] . '</span></span>';
								}
							} else if ((!empty($featureCaption['default']) || !empty($featureCaption['filled']))) {
								if ($featureCaption['default'] == $featureCaption['filled']) {
									echo '<span class="d-none d-md-inline">' . $featureCaption['default'] . '</span>';
								} else {
									echo '<span class="d-none d-md-inline"><span class="zip-caption-unfilled">' . $featureCaption['default'] . '</span><span class="zip-caption-filled">' . $featureCaption['filled'] . '</span></span>';
								}
							} else if ((!empty($featureCaption['mobile']) || !empty($featureCaption['mfilled']))) {
								if ($featureCaption['mobile'] == $featureCaption['mfilled']) {
									echo '<span class="d-inline d-md-none">' . $featureCaption['mobile'] . '</span>';
								} else {
									echo '<span class="d-none d-md-inline"><span class="zip-caption-unfilled">' . $featureCaption['mobile'] . '</span><span class="zip-caption-filled">' . $featureCaption['mfilled'] . '</span></span>';
								}
							}
							echo '</h6>';
						} ?>
			</div>
		</div>
	</div>

	<?php if (isset($brands) && $brands == 'show') { ?>
		<div id="aff" class="container-fluid no-gutters">
			<div class="col-12">
				<img src="/img/affiliate-all.png" alt="blue cross blue shield, aetna, humana, cigna, firsthealth" class="d-md-none">
				<img src="/img/affiliate-bluecross.png" alt="blue cross blue shield logo" class="d-none d-md-inline-block">
				<img src="/img/affiliate-aetna.png" alt="aetna logo" class="d-none d-md-inline-block">
				<img src="/img/affiliate-cigna.png" alt="cigna logo" class="d-none d-md-inline-block">
				<img src="/img/affiliate-firsthealth.png" alt="firsthealth logo" class="d-none d-md-inline-block">
			</div>
		</div>
	<?php } ?>

	<?php if (isset($_GET['campaign'])) { ?>
		<div id="aff" class="container-fluid no-gutters">
			<div class="col-12">
				<img src="/img/brands/uhc.svg" alt="United Healthcare" class="d-none d-md-inline-block">
				<img src="/img/brands/aetna.svg" alt="aetna logo" class="d-none d-md-inline-block">
				<img src="/img/affiliate-bluecross.png" alt="blue cross blue shield logo" class="d-none d-md-inline-block">
			</div>
		</div>
	<?php } ?>

</header>