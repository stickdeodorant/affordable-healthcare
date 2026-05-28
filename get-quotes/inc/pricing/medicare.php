<div class="container affiliate-table" style="padding-top: 20px; padding-right: 0; padding-left: 0;">
	<div class="list-group">
		<div class="list-group-item">
			<div class="row">
				<div class="<?php echo (!$nophone ? 'col-xs-12 col-sm-3 col-sm-offset-2 col-md-offset-0 text-center' : 'col-xs-12 col-sm-4 col-md-3 col-sm-offset-2 col-md-offset-0 text-center'); ?>">
					<div class="vam-parent">
						<div class="vam-child logo-container">
							<?php if (!$nophone) { ?><a data-toggle="modal" data-target="#myModal" data-phone="<?php echo $phone['m-cigna']; ?>" class="hidden-xs" style="cursor: pointer !important;"><?php } ?><img src="/img/brands/cigna.svg" class="aff-logo hidden-xs"><?php if (!$nophone) { ?></a><?php } ?>
							<?php if (!$nophone) { ?><a href="tel:<?php echo $phonemin['m-cigna']; ?>" class="visible-xs" style="cursor: pointer !important;"><?php } ?><img src="/img/brands/cigna.svg" class="aff-logo visible-xs center-block"><?php if (!$nophone) { ?></a><?php } ?>
						</div>
					</div>
				</div>
				<div class="<?php echo (!$nophone ? 'col-sm-3 margin-xs-center text-xs-center' : 'col-sm-4 margin-xs-center text-xs-center'); ?>">
					<div class="vam-parent">
						<div class="vam-child">
							<h1 class="plan-bullets-price">$281.48</h1>
							<p>Part F &amp; G Plans</p>
							<ul class="plan-bullets">
								<li><small>100% Coverage</small></li>
								<li><small>No Deductibles</small></li>
								<li><small>No Coinsurance</small></li>
							</ul>
						</div>
					</div>
				</div>
				<?php if (!$nophone) { ?><div class="visible-xs col-xs-12 text-xs-center">
					<a href="tel:<?php echo $phonemin['m-cigna']; ?>" class="btn btn-primary mobile-plan-call-button" onclick="ga('send', 'event', 'Call Buttons', 'click', '<?php echo ($_GET['type'] == 'medicare') ? "medicare" : "healthcare"; ?>', 'cigna');"><i class="ti ti-phone" style="transform: scaleX(-1);"></i>Call Now</a>
				</div><?php } ?>
				<div class="clearfix visible-xs"></div>
				<div class="col-md-4 hidden-xs hidden-sm">
					<div class="vam-parent">
						<div class="vam-child">
							<?php if (!$nophone) { ?><a data-toggle="modal" data-target="#myModal" data-phone="<?php echo $phone['m-cigna']; ?>" class="hidden-xs"><?php } ?><h4 class="orange-text">Comprehensive Cigna Coverage</h4><?php if (!$nophone) { ?></a><?php } ?><h4 class="orange-text visible-xs"><?php if (!$nophone) { ?><a href="tel:<?php echo $phonemin['m-cigna']; ?>" class="hidden-xs"><?php } ?>Comprehensive Cigna Coverage<?php if (!$nophone) { ?></a><?php } ?></h4>
							<p>We have your one-stop-shop for Part F and G Cigna plans with the options to meet your health needs. Access the industry's highest rated quotes for FREE now!</p>
						</div>
					</div>
				</div>
				<?php if (!$nophone) { ?><div class="col-sm-2 hidden-xs text-center" style="padding: 0;">
					<div class="vam-parent pull-right">
						<div class="vam-child">
							<a data-toggle="modal" data-target="#myModal" data-phone="<?php echo $phone['m-cigna']; ?>" class="call-now-button pull-right" onclick="ga('send', 'event', 'Call Buttons', 'click', '<?php echo ($_GET['type'] == 'medicare') ? "medicare" : "healthcare"; ?>', 'cigna');"><i class="ti ti-phone small" style="clear: both; transform: scaleX(-1);"></i></a>
							<a data-toggle="modal" data-target="#myModal" data-phone="<?php echo $phone['m-cigna']; ?>" class="call-now-button details pull-right">More Details</a>
						</div>
					</div>
				</div><?php } ?>
			</div>
		</div>
		<div class="list-group-item">
			<div class="row">
				<div class="<?php echo (!$nophone ? 'col-xs-12 col-sm-3 col-sm-offset-2 col-md-offset-0 text-center' : 'col-xs-12 col-sm-4 col-md-3 col-sm-offset-2 col-md-offset-0 text-center'); ?>">
					<div class="vam-parent">
						<div class="vam-child logo-container">
							<?php if (!$nophone) { ?><a data-toggle="modal" data-target="#myModal" data-phone="<?php echo $phone['m-aetna']; ?>" class="hidden-xs" style="cursor: pointer !important;"><?php } ?><img src="/img/brands/aetna.svg" class="aff-logo hidden-xs" style="width: 120px;"><?php if (!$nophone) { ?></a><?php } ?>
							<?php if (!$nophone) { ?><a href="tel:<?php echo $phonemin['m-aetna']; ?>" class="visible-xs" style="cursor: pointer !important;"><?php } ?><img src="/img/brands/aetna.svg" class="aff-logo visible-xs center-block" style="width: 80%; max-width: 175px;"><?php if (!$nophone) { ?></a><?php } ?>
						</div>
					</div>
				</div>
				<div class="<?php echo (!$nophone ? 'col-sm-3 margin-xs-center text-xs-center' : 'col-sm-4 margin-xs-center text-xs-center'); ?>">
					<div class="vam-parent">
						<div class="vam-child">
							<h1 class="plan-bullets-price">$219.94</h1>
							<p>Supplemental PPO</p>
							<ul class="plan-bullets">
								<li><small>100% Coverage</small></li>
								<li><small>No Coinsurance</small></li>
							</ul>
						</div>
					</div>
				</div>
				<?php if (!$nophone) { ?><div class="visible-xs col-xs-12 text-xs-center">
					<a href="tel:<?php echo $phonemin['m-aetna']; ?>" class="btn btn-primary mobile-plan-call-button" onclick="ga('send', 'event', 'Call Buttons', 'click', '<?php echo ($_GET['type'] == 'medicare') ? "medicare" : "healthcare"; ?>', 'aetna');"><i class="ti ti-phone" style="transform: scaleX(-1);"></i>Call Now</a>
				</div><?php } ?>
				<div class="clearfix visible-xs"></div>
				<div class="col-md-4 hidden-xs hidden-sm">
					<div class="vam-parent">
						<div class="vam-child">
							<?php if (!$nophone) { ?><a data-toggle="modal" data-target="#myModal" data-phone="<?php echo $phone['m-aetna']; ?>" class="hidden-xs"><?php } ?><h4 class="orange-text">Affordable Aetna Plans</h4><?php if (!$nophone) { ?></a><?php } ?><h4 class="orange-text visible-xs"><?php if (!$nophone) { ?><a href="tel:<?php echo $phonemin['m-aetna']; ?>" class="hidden-xs"><?php } ?>Affordable Aetna Plans<?php if (!$nophone) { ?></a><?php } ?></h4>
							<p>Your search for the best Supplemental PPO coverage is over. We have thousands of quotes you can compare for FREE. Sign-up and get started in minutes!</p>
						</div>
					</div>
				</div>
				<?php if (!$nophone) { ?><div class="col-sm-2 hidden-xs text-center" style="padding: 0;">
					<div class="vam-parent pull-right">
						<div class="vam-child">
							<a data-toggle="modal" data-target="#myModal" data-phone="<?php echo $phone['m-aetna']; ?>" class="call-now-button pull-right" onclick="ga('send', 'event', 'Call Buttons', 'click', '<?php echo ($_GET['type'] == 'medicare') ? "medicare" : "healthcare"; ?>', 'aetna');"><i class="ti ti-phone small" style="clear: both; transform: scaleX(-1);"></i></a>
							<a data-toggle="modal" data-target="#myModal" data-phone="<?php echo $phone['m-aetna']; ?>" class="call-now-button details pull-right">More Details</a>
						</div>
					</div>
				</div><?php } ?>
			</div>
		</div>
		<div class="list-group-item">
			<div class="row">
				<div class="<?php echo (!$nophone ? 'col-xs-12 col-sm-3 col-sm-offset-2 col-md-offset-0 text-center' : 'col-xs-12 col-sm-4 col-md-3 col-sm-offset-2 col-md-offset-0 text-center'); ?>">
					<div class="vam-parent">
						<div class="vam-child logo-container">
							<?php if (!$nophone) { ?><a data-toggle="modal" data-target="#myModal" data-phone="<?php echo $phone['m-united']; ?>" class="hidden-xs" style="cursor: pointer !important;"><?php } ?><img src="/img/brands/uhc.svg" class="aff-logo hidden-xs"><?php if (!$nophone) { ?></a><?php } ?>
							<?php if (!$nophone) { ?><a href="tel:<?php echo $phonemin['m-united']; ?>" class="visible-xs" style="cursor: pointer !important;"><?php } ?><img src="/img/brands/uhc.svg" class="aff-logo visible-xs center-block"><?php if (!$nophone) { ?></a><?php } ?>
						</div>
					</div>
				</div>
				<div class="<?php echo (!$nophone ? 'col-sm-3 margin-xs-center text-xs-center' : 'col-sm-4 margin-xs-center text-xs-center'); ?>">
					<div class="vam-parent">
						<div class="vam-child">
							<h1 class="plan-bullets-price">$253.49</h1>
							<p>Supplemental PPO</p>
							<ul class="plan-bullets">
								<li><small>100% Coverage</small></li>
								<li><small>No Coinsurance</small></li>
							</ul>
						</div>
					</div>
				</div>
				<?php if (!$nophone) { ?><div class="visible-xs col-xs-12 text-xs-center">
					<a href="tel:<?php echo $phonemin['m-united']; ?>" class="btn btn-primary mobile-plan-call-button" onclick="ga('send', 'event', 'Call Buttons', 'click', '<?php echo ($_GET['type'] == 'medicare') ? "medicare" : "healthcare"; ?>', 'united');"><i class="ti ti-phone" style="transform: scaleX(-1);"></i>Call Now</a>
				</div><?php } ?>
				<div class="clearfix visible-xs"></div>
				<div class="col-md-4 hidden-xs hidden-sm">
					<div class="vam-parent">
						<div class="vam-child">
							<?php if (!$nophone) { ?><a data-toggle="modal" data-target="#myModal" data-phone="<?php echo $phone['m-united']; ?>" class="hidden-xs"><?php } ?><h4 class="orange-text">Affordable Aetna Plans</h4><?php if (!$nophone) { ?></a><?php } ?><h4 class="orange-text visible-xs"><?php if (!$nophone) { ?><a href="tel:<?php echo $phonemin['m-united']; ?>" class="hidden-xs"><?php } ?>Perfect United Healthcare Options<?php if (!$nophone) { ?></a><?php } ?></h4>
							<p>Who says getting a Supplemental PPO plan needs to be frustrating? Our BCBS plans offer the widest range of options at the lowest price. Start comparing FREE quotes today!</p>
						</div>
					</div>
				</div>
				<?php if (!$nophone) { ?><div class="col-sm-2 hidden-xs text-center" style="padding: 0;">
					<div class="vam-parent pull-right">
						<div class="vam-child">
							<a data-toggle="modal" data-target="#myModal" data-phone="<?php echo $phone['m-united']; ?>" class="call-now-button pull-right" onclick="ga('send', 'event', 'Call Buttons', 'click', '<?php echo ($_GET['type'] == 'medicare') ? "medicare" : "healthcare"; ?>', 'united');"><i class="ti ti-phone small" style="clear: both; transform: scaleX(-1);"></i></a>
							<a data-toggle="modal" data-target="#myModal" data-phone="<?php echo $phone['m-united']; ?>" class="call-now-button details pull-right">More Details</a>
						</div>
					</div>
				</div><?php } ?>
			</div>
		</div>
		<div class="list-group-item">
			<div class="row">
				<div class="<?php echo (!$nophone ? 'col-xs-12 col-sm-3 col-sm-offset-2 col-md-offset-0 text-center' : 'col-xs-12 col-sm-4 col-md-3 col-sm-offset-2 col-md-offset-0 text-center'); ?>">
					<div class="vam-parent">
						<div class="vam-child logo-container">
							<?php if (!$nophone) { ?><a data-toggle="modal" data-target="#myModal" data-phone="<?php echo $phone['m-omaha']; ?>" class="hidden-xs" style="cursor: pointer !important;"><?php } ?><img src="/img/brands/mutualofomaha.svg" class="aff-logo hidden-xs"><?php if (!$nophone) { ?></a><?php } ?>
							<?php if (!$nophone) { ?><a href="tel:<?php echo $phonemin['m-omaha']; ?>" class="visible-xs" style="cursor: pointer !important;"><?php } ?><img src="/img/brands/mutualofomaha.svg" class="aff-logo visible-xs center-block"><?php if (!$nophone) { ?></a><?php } ?>
						</div>
					</div>
				</div>
				<div class="<?php echo (!$nophone ? 'col-sm-3 margin-xs-center text-xs-center' : 'col-sm-4 margin-xs-center text-xs-center'); ?>">
					<div class="vam-parent">
						<div class="vam-child">
							<h1 class="plan-bullets-price">$231.34</h1>
							<p>Supplemental PPO</p>
							<ul class="plan-bullets">
								<li><small>100% Coverage</small></li>
								<li><small>No Coinsurance</small></li>
							</ul>
						</div>
					</div>
				</div>
				<?php if (!$nophone) { ?><div class="visible-xs col-xs-12 text-xs-center">
					<a href="tel:<?php echo $phonemin['m-omaha']; ?>" class="btn btn-primary mobile-plan-call-button" onclick="ga('send', 'event', 'Call Buttons', 'click', '<?php echo ($_GET['type'] == 'medicare') ? "medicare" : "healthcare"; ?>', 'omaha');"><i class="ti ti-phone" style="transform: scaleX(-1);"></i>Call Now</a>
				</div><?php } ?>
				<div class="clearfix visible-xs"></div>
				<div class="col-md-4 hidden-xs hidden-sm">
					<div class="vam-parent">
						<div class="vam-child">
							<?php if (!$nophone) { ?><a data-toggle="modal" data-target="#myModal" data-phone="<?php echo $phone['m-omaha']; ?>" class="hidden-xs"><?php } ?><h4 class="orange-text">Exclusive Mutual of Omaha Plans</h4><?php if (!$nophone) { ?></a><?php } ?><h4 class="orange-text visible-xs"><?php if (!$nophone) { ?><a href="tel:<?php echo $phonemin['m-omaha']; ?>" class="hidden-xs"><?php } ?>Exclusive Mutual of Omaha Plans<?php if (!$nophone) { ?></a><?php } ?></h4>
							<p>Discover limitless Supplemental PPO options for the most accurate coverage to support you. Now is the time to find Aetna coverage, compare quotes here!</p>
						</div>
					</div>
				</div>
				<?php if (!$nophone) { ?><div class="col-sm-2 hidden-xs text-center" style="padding: 0;">
					<div class="vam-parent pull-right">
						<div class="vam-child">
							<a data-toggle="modal" data-target="#myModal" data-phone="<?php echo $phone['m-omaha']; ?>" class="call-now-button pull-right" onclick="ga('send', 'event', 'Call Buttons', 'click', '<?php echo ($_GET['type'] == 'medicare') ? "medicare" : "healthcare"; ?>', 'omaha');"><i class="ti ti-phone small" style="clear: both; transform: scaleX(-1);"></i></a>
							<a data-toggle="modal" data-target="#myModal" data-phone="<?php echo $phone['m-omaha']; ?>" class="call-now-button details pull-right">More Details</a>
						</div>
					</div>
				</div><?php } ?>
			</div>
		</div>
	</div>
</div>
