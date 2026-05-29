<?php if (isset($_GET['layout']) && $_GET['layout'] == '1') { ?>
	<header class="container-fluid text-center d-flex flex-wrap bg-couples">
		<div class="container justify-content-center align-self-center">
			<div class="row justify-content-end">
				<div class="col-lg-6 pb-lg-5 mb-lg-5">
					<h1 class="headline my-0" style="text-transform:uppercase;"><?php echo $featureTitle; ?></h1>
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
						echo '<h6 id="zip-caption" class="zip-caption mt-2 mb-3 font-weight-normal" style="text-transform:capitalize;">';
						$featureCaptionIsUniform = is_array($featureCaption)
							&& count($featureCaption) > 0
							&& isset($featureCaption[0])
							&& ($featureCaption === array_fill(0, count($featureCaption), $featureCaption[0]));
						if ($featureCaptionIsUniform) {
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
					<?php if (isset($_GET['call'])) { ?>
						<a href="tel:<?= $phonemin['fb-call'] ?>" class="button bg-accent text-white"><?= $phone['fb-call'] ?></a>
					<?php } else { ?>
						<form class="mt-4 mb-2 mb-sm-5 my-md-0" action="multi-quote" method="GET" autocomplete="off">
							<input type="hidden" name="step" value="0">
						<?php } ?>
						<?php /*<label for="zip" class="d-block w-100 mt-3 mb-1">My zip code is...</label>*/ ?>
						<div class="input-container d-inline-block position-relative mb-3">
							<input type="hidden" name="page" value="<?php echo $url; ?>">
							<input type="hidden" name="engine" value="">
							<input type="hidden" name="keyword" value="">
							<input type="hidden" name="gclid" value="<?= isset($_GET['gclid']) ? $_GET['gclid'] : '' ?>">
							<input type="hidden" name="notes" value="">

							<?php if (isset($_GET['First_Name'])) { ?>
								<input name="First_Name" type="hidden" value="<?= $_GET['First_Name'] ?>">
							<?php } ?>
							<?php if (isset($_GET['Last_Name'])) { ?>
								<input name="Last_Name" type="hidden" value="<?= $_GET['Last_Name'] ?>">
							<?php } ?>
							<?php if (isset($_GET['Primary_Phone'])) { ?>
								<input name="Primary_Phone" type="hidden" value="<?= $_GET['Primary_Phone'] ?>">
							<?php } ?>
							<?php if (isset($_GET['Email'])) { ?>
								<input name="Email" type="hidden" value="<?= $_GET['Email'] ?>">
							<?php } ?>
							<?php if (isset($_GET['Household_Income'])) { ?>
								<input name="Household_Income" type="hidden" value="<?= $_GET['Household_Income'] ?>">
							<?php } ?>
							<?php if (isset($_GET['DOB'])) { ?>
								<input name="DOB" type="hidden" value="<?= $_GET['DOB'] ?>">
							<?php } ?>
							<?php if (isset($_GET['Address'])) { ?>
								<input name="Address" type="hidden" value="<?= $_GET['Address'] ?>">
							<?php } ?>
							<?php if (isset($_GET['city'])) { ?>
								<input name="city" type="hidden" value="<?= $_GET['city'] ?>">
							<?php } ?>
							<?php if (isset($_GET['state'])) { ?>
								<input name="state" type="hidden" value="<?= $_GET['state'] ?>">
							<?php } ?>
							<?php if (isset($_GET['ad_id'])) { ?>
								<input name="ad_id" type="hidden" value="<?= $_GET['ad_id'] ?>">
							<?php } elseif (isset($_GET['utm_agid'])) { ?>
								<input name="ad_id" type="hidden" value="<?= $_GET['utm_agid'] ?>">
							<?php } elseif (isset($_GET['utm_agid'])) { ?>
								<input name="ad_id" type="hidden" value="<?= $_GET['utm_agid'] ?>">
							<?php } elseif (isset($_SESSION['ad_id']) && !empty($_SESSION['ad_id'])) { ?>
								<input name="ad_id" type="hidden" value="<?= $_SESSION['ad_id'] ?>">
							<?php } ?>
							<?php if (isset($_GET['utm_campaign'])) { ?>
								<input name="adset_id" type="hidden" value="<?= $_GET['utm_campaign'] ?>">
							<?php } ?>
							<?php if (isset($_GET['usha'])) { ?>
								<input name="usha" type="hidden" value="<?= $_GET['usha'] ?>">
							<?php } ?>
							<?php if (isset($_GET['Pub_ID'])) { ?>
								<input name="Pub_ID" type="hidden" value="<?= $_GET['Pub_ID'] ?>">
							<?php } ?>
							<?php if (isset($_GET['Sub_ID'])) { ?>
								<input name="Sub_ID" type="hidden" value="<?= $_GET['Sub_ID'] ?>">
							<?php } ?>
							<?php if (isset($_GET['utm_medium'])) { ?>
								<input name="utm_medium" type="hidden" value="<?= $_GET['utm_medium'] ?>">
							<?php } ?>

							<!-- <input name="zip" type="tel" placeholder="My zip code is..." class="form-control" maxlength="5" style="width: 90%; float: left;"> -->
							<input id="zip" name="zip" type="tel" pattern="\d{5}" maxlength="5" required="" placeholder="Zip code..." class="mx-auto h2 text-center mb-0 py-2 w-100" <?php if (isset($_GET['Zip'])) {
																																															echo 'value="' . $_GET['Zip'] . '"';
																																														} ?>>
							<svg xmlns="http://www.w3.org/2000/svg" id="secure-form" class="ti-lock" version="1.1" viewBox="0 0 60 60">
								<style>
									.secure-form0 {
										display: inline
									}

									.secure-form1 {
										fill: #203a72
									}
								</style>
								<path d="M5.2 58.29c.71 0 1.23-.12 1.55-.36.32-.24.49-.58.49-1.02 0-.26-.06-.49-.17-.68a1.73 1.73 0 0 0-.47-.51c-.2-.15-.45-.29-.75-.42-.29-.13-.63-.26-1-.38-.38-.14-.74-.28-1.09-.45A2.93 2.93 0 0 1 2.19 53a2.8 2.8 0 0 1-.24-1.21c0-.98.34-1.75 1.02-2.31a4.25 4.25 0 0 1 2.78-.84 6.26 6.26 0 0 1 3.06.73l-.61 1.6a5.17 5.17 0 0 0-2.48-.61c-.53 0-.95.11-1.25.33-.3.22-.45.53-.45.93 0 .24.05.45.15.62.1.17.24.33.42.46.18.14.4.26.64.38l.81.33c.51.19.97.38 1.37.57.4.19.74.42 1.02.69.28.27.49.58.64.94.15.36.22.8.22 1.31 0 .98-.35 1.74-1.04 2.28-.7.53-1.71.8-3.05.8a7.72 7.72 0 0 1-3.47-.8l.58-1.62c.28.16.66.31 1.15.47.48.16 1.06.24 1.74.24zM11.04 59.76V48.89h6.99v1.68h-5.01v2.68h4.46v1.65h-4.46v3.19h5.38v1.68h-7.36zM24.68 60c-.82 0-1.55-.12-2.2-.38a4.3 4.3 0 0 1-1.65-1.11 4.98 4.98 0 0 1-1.04-1.78 7.47 7.47 0 0 1-.36-2.42c0-.91.14-1.72.42-2.42.28-.7.66-1.3 1.14-1.78a4.73 4.73 0 0 1 1.7-1.11 6.47 6.47 0 0 1 4.27-.05c.27.09.5.17.67.27l.38.2-.58 1.62a5.27 5.27 0 0 0-2.6-.66c-.47 0-.91.08-1.32.24-.41.16-.76.41-1.06.73-.3.33-.53.73-.7 1.22a5.22 5.22 0 0 0-.25 1.71c0 .58.06 1.1.2 1.59.13.48.33.9.6 1.25.27.35.62.62 1.04.82.42.19.92.29 1.51.29a5.77 5.77 0 0 0 2.73-.61l.53 1.62a4.9 4.9 0 0 1-1.27.49 7.6 7.6 0 0 1-.99.19c-.36.06-.75.08-1.17.08zM34.05 60c-.74 0-1.38-.11-1.92-.32a3.48 3.48 0 0 1-2.11-2.26 5.56 5.56 0 0 1-.25-1.73v-6.8h1.99v6.61c0 .49.05.91.16 1.26s.27.64.47.86c.2.22.44.38.72.49.28.11.59.16.93.16.34 0 .66-.05.94-.16.28-.1.53-.27.73-.49.2-.22.36-.5.47-.86.11-.35.16-.77.16-1.26v-6.61h1.99v6.8c0 .63-.09 1.2-.26 1.73a3.56 3.56 0 0 1-2.13 2.26c-.5.21-1.15.32-1.89.32zM43.89 48.78c1.57 0 2.77.29 3.6.86.83.58 1.25 1.45 1.25 2.64 0 1.48-.73 2.47-2.18 3a30.64 30.64 0 0 1 2.19 3.24c.24.42.46.84.64 1.25h-2.21a21.78 21.78 0 0 0-1.34-2.25l-.7-1.02-.64-.86-.38.02h-1.26v4.11h-1.98V49.04c.48-.1.99-.17 1.54-.21.56-.03 1.04-.05 1.47-.05zm.14 1.71c-.42 0-.81.02-1.16.05v3.52h.86c.48 0 .91-.03 1.27-.08.37-.05.67-.15.92-.28.25-.13.43-.32.56-.55.13-.23.19-.52.19-.88 0-.34-.06-.62-.19-.85a1.5 1.5 0 0 0-.54-.55 2.6 2.6 0 0 0-.84-.29 5.85 5.85 0 0 0-1.07-.09zM50.94 59.76V48.89h6.99v1.68h-5.01v2.68h4.46v1.65h-4.46v3.19h5.38v1.68h-7.36zM45.82 19.66a37.1 37.1 0 0 0-3.58-1.4v-5.67C42.24 5.84 36.75.35 30 .35S17.76 5.84 17.76 12.59v5.67c-1.29.43-2.47.89-3.58 1.4a2.91 2.91 0 0 0-1.71 2.65v19.26c0 1.6 1.3 2.91 2.91 2.91h29.24c1.6 0 2.91-1.3 2.91-2.91V22.3a2.9 2.9 0 0 0-1.71-2.64zM33.26 36.15a.94.94 0 0 1-.91 1.18h-4.71a.93.93 0 0 1-.75-.37.9.9 0 0 1-.16-.81l1.52-5.73a3.86 3.86 0 0 1 1.75-7.3 3.86 3.86 0 0 1 1.75 7.3l1.51 5.73zm2.23-19.46a38.4 38.4 0 0 0-10.97 0V12.6a5.5 5.5 0 0 1 10.98 0v4.09z" class="secure-form1" />
							</svg>
						</div>
						<div class="bottom-nav">
							<input class="button bg-accent text-white" type="submit" value="Find My Quote">
						</div>
						</form>
						<div class="row badges justify-content-center mt-3">
							<div class="col-auto">
								<img src="/img/trustedform-badge.webp" alt="Trusted Form" />
							</div>
							<div class="col-auto">
								<img src="/img/ssl-badge.webp" alt="SSL Secured" />
							</div>
						</div>
				</div>
			</div>
		</div>
	<?php } else { ?>
		<header class="container-fluid text-center d-flex flex-wrap bg-couples">
			<div class="container justify-content-center align-self-center">
				<div class="row row1">
					<div class="col-12">
						<h1 class="headline my-0" style="text-transform:uppercase;"><?php echo $featureTitle; ?></h1>
						<h5 class="subheadline font-weight-normal mb-3 mt-2" style="text-transform:capitalize;"><?php echo $featureSubtitle; ?>.</h5>
						<?php if (isset($_GET['call'])) { ?>
							<a href="tel:<?= $phonemin['fb-call'] ?>" class="button bg-accent text-white"><?= $phone['fb-call'] ?></a>
						<?php } else { ?>
							<?php if (isset($_GET['form']) && $_GET['form'] == 'single') { ?>
								<form class="mt-4 mb-2 mb-sm-5 my-md-0" action="/get-quotes" method="GET" autocomplete="off">
								<?php } else { ?>
									<form class="mt-4 mb-2 mb-sm-5 my-md-0" action="multi-quote" method="GET" autocomplete="off">
										<input type="hidden" name="step" value="0">
									<?php } ?>
									<?php /*<label for="zip" class="d-block w-100 mt-3 mb-1">My zip code is...</label>*/ ?>
									<div class="input-container d-inline-block position-relative mb-3">
										<input type="hidden" name="page" value="<?php echo $url; ?>">
										<input type="hidden" name="engine" value="">
										<input type="hidden" name="keyword" value="">
										<input type="hidden" name="gclid" value="<?= isset($_GET['gclid']) ? $_GET['gclid'] : '' ?>">
										<input type="hidden" name="notes" value="">

										<?php if (isset($_GET['First_Name'])) { ?>
											<input name="First_Name" type="hidden" value="<?= $_GET['First_Name'] ?>">
										<?php } ?>
										<?php if (isset($_GET['Last_Name'])) { ?>
											<input name="Last_Name" type="hidden" value="<?= $_GET['Last_Name'] ?>">
										<?php } ?>
										<?php if (isset($_GET['Primary_Phone'])) { ?>
											<input name="Primary_Phone" type="hidden" value="<?= $_GET['Primary_Phone'] ?>">
										<?php } ?>
										<?php if (isset($_GET['Email'])) { ?>
											<input name="Email" type="hidden" value="<?= $_GET['Email'] ?>">
										<?php } ?>
										<?php if (isset($_GET['Household_Income'])) { ?>
											<input name="Household_Income" type="hidden" value="<?= $_GET['Household_Income'] ?>">
										<?php } ?>
										<?php if (isset($_GET['DOB'])) { ?>
											<input name="DOB" type="hidden" value="<?= $_GET['DOB'] ?>">
										<?php } ?>
										<?php if (isset($_GET['Address'])) { ?>
											<input name="Address" type="hidden" value="<?= $_GET['Address'] ?>">
										<?php } ?>
										<?php if (isset($_GET['city'])) { ?>
											<input name="city" type="hidden" value="<?= $_GET['city'] ?>">
										<?php } ?>
										<?php if (isset($_GET['state'])) { ?>
											<input name="state" type="hidden" value="<?= $_GET['state'] ?>">
										<?php } ?>
										<?php if (isset($_GET['ad_id'])) { ?>
											<input name="ad_id" type="hidden" value="<?= $_GET['ad_id'] ?>">
										<?php } elseif (isset($_GET['utm_agid'])) { ?>
											<input name="ad_id" type="hidden" value="<?= $_GET['utm_agid'] ?>">
										<?php } elseif (isset($_GET['utm_agid'])) { ?>
											<input name="ad_id" type="hidden" value="<?= $_GET['utm_agid'] ?>">
										<?php } elseif (isset($_SESSION['ad_id']) && !empty($_SESSION['ad_id'])) { ?>
											<input name="ad_id" type="hidden" value="<?= $_SESSION['ad_id'] ?>">
										<?php } ?>
										<?php if (isset($_GET['utm_campaign'])) { ?>
											<input name="adset_id" type="hidden" value="<?= $_GET['utm_campaign'] ?>">
										<?php } ?>
										<?php if (isset($_GET['usha'])) { ?>
											<input name="usha" type="hidden" value="<?= $_GET['usha'] ?>">
										<?php } ?>
										<input name="Pub_ID" type="hidden" value="K-1">
										<?php if (isset($_GET['Sub_ID'])) { ?>
											<input name="Sub_ID" type="hidden" value="<?= $_GET['Sub_ID'] ?>">
										<?php } ?>
										<?php if (isset($_GET['utm_medium'])) { ?>
											<input name="utm_medium" type="hidden" value="<?= $_GET['utm_medium'] ?>">
										<?php } ?>

										<!-- <input name="zip" type="tel" placeholder="My zip code is..." class="form-control" maxlength="5" style="width: 90%; float: left;"> -->
										<input id="zip" name="zip" type="tel" pattern="\d{5}" maxlength="5" required="" placeholder="Zip code..." class="mx-auto h2 text-center mb-0 py-2 w-100" <?php if (isset($_GET['Zip'])) {
																																																		echo 'value="' . $_GET['Zip'] . '"';
																																																	} ?>>
										<svg xmlns="http://www.w3.org/2000/svg" id="secure-form" class="ti-lock" version="1.1" viewBox="0 0 60 60">
											<style>
												.secure-form0 {
													display: inline
												}

												.secure-form1 {
													fill: #203a72
												}
											</style>
											<path d="M5.2 58.29c.71 0 1.23-.12 1.55-.36.32-.24.49-.58.49-1.02 0-.26-.06-.49-.17-.68a1.73 1.73 0 0 0-.47-.51c-.2-.15-.45-.29-.75-.42-.29-.13-.63-.26-1-.38-.38-.14-.74-.28-1.09-.45A2.93 2.93 0 0 1 2.19 53a2.8 2.8 0 0 1-.24-1.21c0-.98.34-1.75 1.02-2.31a4.25 4.25 0 0 1 2.78-.84 6.26 6.26 0 0 1 3.06.73l-.61 1.6a5.17 5.17 0 0 0-2.48-.61c-.53 0-.95.11-1.25.33-.3.22-.45.53-.45.93 0 .24.05.45.15.62.1.17.24.33.42.46.18.14.4.26.64.38l.81.33c.51.19.97.38 1.37.57.4.19.74.42 1.02.69.28.27.49.58.64.94.15.36.22.8.22 1.31 0 .98-.35 1.74-1.04 2.28-.7.53-1.71.8-3.05.8a7.72 7.72 0 0 1-3.47-.8l.58-1.62c.28.16.66.31 1.15.47.48.16 1.06.24 1.74.24zM11.04 59.76V48.89h6.99v1.68h-5.01v2.68h4.46v1.65h-4.46v3.19h5.38v1.68h-7.36zM24.68 60c-.82 0-1.55-.12-2.2-.38a4.3 4.3 0 0 1-1.65-1.11 4.98 4.98 0 0 1-1.04-1.78 7.47 7.47 0 0 1-.36-2.42c0-.91.14-1.72.42-2.42.28-.7.66-1.3 1.14-1.78a4.73 4.73 0 0 1 1.7-1.11 6.47 6.47 0 0 1 4.27-.05c.27.09.5.17.67.27l.38.2-.58 1.62a5.27 5.27 0 0 0-2.6-.66c-.47 0-.91.08-1.32.24-.41.16-.76.41-1.06.73-.3.33-.53.73-.7 1.22a5.22 5.22 0 0 0-.25 1.71c0 .58.06 1.1.2 1.59.13.48.33.9.6 1.25.27.35.62.62 1.04.82.42.19.92.29 1.51.29a5.77 5.77 0 0 0 2.73-.61l.53 1.62a4.9 4.9 0 0 1-1.27.49 7.6 7.6 0 0 1-.99.19c-.36.06-.75.08-1.17.08zM34.05 60c-.74 0-1.38-.11-1.92-.32a3.48 3.48 0 0 1-2.11-2.26 5.56 5.56 0 0 1-.25-1.73v-6.8h1.99v6.61c0 .49.05.91.16 1.26s.27.64.47.86c.2.22.44.38.72.49.28.11.59.16.93.16.34 0 .66-.05.94-.16.28-.1.53-.27.73-.49.2-.22.36-.5.47-.86.11-.35.16-.77.16-1.26v-6.61h1.99v6.8c0 .63-.09 1.2-.26 1.73a3.56 3.56 0 0 1-2.13 2.26c-.5.21-1.15.32-1.89.32zM43.89 48.78c1.57 0 2.77.29 3.6.86.83.58 1.25 1.45 1.25 2.64 0 1.48-.73 2.47-2.18 3a30.64 30.64 0 0 1 2.19 3.24c.24.42.46.84.64 1.25h-2.21a21.78 21.78 0 0 0-1.34-2.25l-.7-1.02-.64-.86-.38.02h-1.26v4.11h-1.98V49.04c.48-.1.99-.17 1.54-.21.56-.03 1.04-.05 1.47-.05zm.14 1.71c-.42 0-.81.02-1.16.05v3.52h.86c.48 0 .91-.03 1.27-.08.37-.05.67-.15.92-.28.25-.13.43-.32.56-.55.13-.23.19-.52.19-.88 0-.34-.06-.62-.19-.85a1.5 1.5 0 0 0-.54-.55 2.6 2.6 0 0 0-.84-.29 5.85 5.85 0 0 0-1.07-.09zM50.94 59.76V48.89h6.99v1.68h-5.01v2.68h4.46v1.65h-4.46v3.19h5.38v1.68h-7.36zM45.82 19.66a37.1 37.1 0 0 0-3.58-1.4v-5.67C42.24 5.84 36.75.35 30 .35S17.76 5.84 17.76 12.59v5.67c-1.29.43-2.47.89-3.58 1.4a2.91 2.91 0 0 0-1.71 2.65v19.26c0 1.6 1.3 2.91 2.91 2.91h29.24c1.6 0 2.91-1.3 2.91-2.91V22.3a2.9 2.9 0 0 0-1.71-2.64zM33.26 36.15a.94.94 0 0 1-.91 1.18h-4.71a.93.93 0 0 1-.75-.37.9.9 0 0 1-.16-.81l1.52-5.73a3.86 3.86 0 0 1 1.75-7.3 3.86 3.86 0 0 1 1.75 7.3l1.51 5.73zm2.23-19.46a38.4 38.4 0 0 0-10.97 0V12.6a5.5 5.5 0 0 1 10.98 0v4.09z" class="secure-form1" />
										</svg>
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
									echo '<h6 id="zip-caption" class="zip-caption mt-2 mt-sm-4 font-weight-normal" style="text-transform:capitalize;">';
									$featureCaptionIsUniform = is_array($featureCaption)
										&& count($featureCaption) > 0
										&& isset($featureCaption[0])
										&& ($featureCaption === array_fill(0, count($featureCaption), $featureCaption[0]));
									if ($featureCaptionIsUniform) {
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
					<p class="disclaimer mt-0 mb-0 small text-white mx-auto" style="text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.4); max-width: 600px;">By clicking "Find My Quote," you understand that healthcare-quotes.com is a third party lead generation website. We are not an insurance company and do not issue insurance policies. Your information may be shared with affiliate agencies, marketing companies, or service providers that may contact you about health insurance options.</p>
				</div>
			</div>
		<?php } ?>
		<?php /* Affiliation logos */ ?>
		<?php //include __DIR__ . '/aff-logos.php'; 
		?>

		</header>