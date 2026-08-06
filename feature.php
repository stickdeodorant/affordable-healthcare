<header class="container-fluid text-center d-flex flex-wrap">
	<div class="container justify-content-center align-self-center">
		<div class="row row1">
			<div class="col-12">
				<h1 class="headline my-0"><?php echo $featureTitle; ?></h1>
				<h5 class="subheadline font-weight-normal mb-3 mt-2"><?php echo $featureSubtitle; ?></h5>
				<form action="/get-quotes" method="GET" autocomplete="off">
					<label for="zip" class="d-block w-100 mt-3 mb-1">My zip code is...</label>
					<div class="input-container d-inline-block position-relative mb-3">
						<input type="hidden" name="page" value="<?php echo $url; ?>">
						<input type="hidden" name="engine" value="">
						<input type="hidden" name="keyword" value="">
						<input type="hidden" name="gclid_" value="<?php echo $_GET['gclid']; ?>">
						<!-- <input name="zip" type="tel" placeholder="My zip code is..." class="form-control" maxlength="5" style="width: 90%; float: left;"> -->
						<input id="zip" name="zip" type="tel" inputmode="numeric" autocomplete="postal-code" pattern="\d{5}" maxlength="5" required="" placeholder="ZIP code" aria-label="ZIP code" class="mx-auto h2 text-center mb-0 py-2 w-100">
						<i class="ti ti-lock"></i>
					</div>
					<div class="bottom-nav">
						<input class="button bg-accent text-white" type="submit" value="Find Plans">
					</div>
				</form>
				<?php if (!empty($featureCaption['default']) || !empty($featureCaption['mobile']) || !empty($featureCaption['filled']) || !empty($featureCaption['mfilled'])) {
					if ((empty($featureCaption['mobile']) && $featureCaption['mobile'] !== 0 && $featureCaption['mobile'] !== '0') && !empty($featureCaption['default'])) { $featureCaption['mobile'] = $featureCaption['default']; }
					if ((empty($featureCaption['mfilled']) && $featureCaption['mfilled'] !== 0 && $featureCaption['mfilled'] !== '0') && !empty($featureCaption['filled'])) { $featureCaption['mfilled'] = $featureCaption['filled']; }
					if ((empty($featureCaption['filled']) && $featureCaption['filled'] !== 0 && $featureCaption['filled'] !== '0') && !empty($featureCaption['default'])) { $featureCaption['filled'] = $featureCaption['default']; }
					if ((empty($featureCaption['mfilled']) && $featureCaption['mfilled'] !== 0 && $featureCaption['mfilled'] !== '0') && !empty($featureCaption['mobile'])) { $featureCaption['mfilled'] = $featureCaption['mobile']; }
					if ($featureCaption['filled'] === 0 || $featureCaption['filled'] === '0') {
						if ($featureCaption['default'] === 0 || $featureCaption['default'] === '0') { $featureCaption['filled'] = ''; $featureCaption['default'] = ''; }
						else { $featureCaption['filled'] = $featureCaption['default']; }
					} else if ($featureCaption['default'] === 0 || $featureCaption['default'] === '0') { $featureCaption['default'] = ''; }
					if ($featureCaption['mfilled'] === 0 || $featureCaption['mfilled'] === '0') {
						if ($featureCaption['mobile'] === 0 || $featureCaption['mobile'] === '0') { $featureCaption['mfilled'] = ''; $featureCaption['mobile'] = ''; }
						else { $featureCaption['mfilled'] = $featureCaption['mobile']; }
					} else if ($featureCaption['mobile'] === 0 || $featureCaption['mobile'] === '0') { $featureCaption['mobile'] = ''; }
					echo '<h6 id="zip-caption" class="zip-caption mt-4 font-weight-normal">';
					if ($featureCaption === array_fill(0,count($featureCaption),$featureCaption[0])) {
						echo $featureCaption['default'];
					} else if ((!empty($featureCaption['default']) || !empty($featureCaption['filled'])) && (!empty($featureCaption['mobile']) || !empty($featureCaption['mfilled']))) {
						if ($featureCaption['default'] == $featureCaption['filled']) { echo '<span class="d-none d-md-inline">' . $featureCaption['default'] . '</span>'; }
						else { echo '<span class="d-none d-md-inline"><span class="zip-caption-unfilled">' . $featureCaption['default'] . '</span><span class="zip-caption-filled">' . $featureCaption['filled'] . '</span></span>'; }
						if ($featureCaption['mobile'] == $featureCaption['mfilled']) { echo '<span class="d-inline d-md-none">' . $featureCaption['mobile'] . '</span>'; }
						else { echo '<span class="d-inline d-md-none"><span class="zip-caption-unfilled">' . $featureCaption['mobile'] . '</span><span class="zip-caption-filled">' . $featureCaption['mfilled'] . '</span></span>'; }
					} else if ((!empty($featureCaption['default']) || !empty($featureCaption['filled']))) {
						if ($featureCaption['default'] == $featureCaption['filled']) { echo '<span class="d-none d-md-inline">' . $featureCaption['default'] . '</span>'; }
						else { echo '<span class="d-none d-md-inline"><span class="zip-caption-unfilled">' . $featureCaption['default'] . '</span><span class="zip-caption-filled">' . $featureCaption['filled'] . '</span></span>'; }
					} else if ((!empty($featureCaption['mobile']) || !empty($featureCaption['mfilled']))) {
						if ($featureCaption['mobile'] == $featureCaption['mfilled']) { echo '<span class="d-inline d-md-none">' . $featureCaption['mobile'] . '</span>'; }
						else { echo '<span class="d-none d-md-inline"><span class="zip-caption-unfilled">' . $featureCaption['mobile'] . '</span><span class="zip-caption-filled">' . $featureCaption['mfilled'] . '</span></span>'; }
					}
					echo '</h6>';
				} ?>
			</div>
		</div>
	</div>
	<div id="aff" class="container-fluid no-gutters">
		<div class="col-12">
				<img src="/img/affiliate-all.png" alt="aetna, humana, cigna, blue cross blue shield, multiplan" class="d-md-none">
				<img src="/img/affiliate-aetna.png" alt="aetna logo" class="d-none d-md-inline-block">
				<img src="/img/affiliate-humana.png" alt="humana logo" class="d-none d-md-inline-block">
				<img src="/img/affiliate-cigna.png" alt="cigna logo" class="d-none d-md-inline-block">
				<img src="/img/affiliate-bluecross.png" alt="blue cross blue shield logo" class="d-none d-md-inline-block">
				<img src="/img/affiliate-multiplan.png" alt="multiplan logo" class="d-none d-md-inline-block">
		</div>
	</div>
</header>
