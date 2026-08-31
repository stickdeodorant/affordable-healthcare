<div class="disclaimer">
	<div class="container">
		<div class="row badges justify-content-center mt-md-3">
			<div class="col-auto">
				<img id="trusted-form" src="../img/trustedform-badge.webp" alt="Trusted Form" />
			</div>
			<div class="col-auto">
				<img id="trust-pilot" src="../img/trustpilot-badge.webp" alt="Trust Pilot" />
			</div>
			<div class="col-auto">
				<img id="ssl-secured" src="../img/ssl-badge.webp" alt="SSL Secured" />
			</div>
		</div>
		<div class="row">
			<div class="col text-centern pt-3">
				<?php
				// Prefer the state stored from the zip lookup; fall back to legacy session/GET if needed.
				$stateAbbr = '';
				if (class_exists('SessionManager')) {
					$stateAbbr = SessionManager::getState();
				}
				if (!$stateAbbr) {
					$stateAbbr = $_SESSION['state_abbr'] ?? ($_SESSION['location']['state'] ?? ($_GET['state'] ?? ''));
				}
				$stateAbbr = strtoupper($stateAbbr);
				?>
				<p style="color: #728c98; font-size: 70%; line-height: 1.3; font-weight: 400">
					<?php if ($stateAbbr && in_array($stateAbbr, ['NY', 'MA', 'CA', 'OH'])) { ?>

						<?= $sitename ?> is owned and operated by Michael Whitney, a licensed insurance agent. Invitations for applications for insurance on Health-Insurance.com are made only where licensed and appointed. License information for all states can be found <a data-toggle="modal" data-target="#licensesModal" style="font-weight: bold; color: inherit !important; cursor: pointer;">here</a>. The appearance of logos related to various insurance providers is not an endorsement nor guarantee of product availability from those providers. Availablity is dependent on various details such as geography, individual needs, agency appointments, and carrier relationships.

					<?php } else { ?>

						<!-- <?= $sitename ?> is privately owned and operated by RM7 Services LLC. Invitations for applications for insurance on Health-Insurance.com are made through RM7 Services LLC, a subsidiary of Affordable Healthcare, only where licensed and appointed. RM7 Services LLC licensing information can be found <a data-toggle="modal" data-target="#licensesModal" style="font-weight: bold; color: inherit !important; cursor: pointer;">here</a>. Submission of your information constitutes permission for an agent to contact you with additional information about the cost and coverage details of health plans. The appearance of logos related to various insurance providers is not an endorsement nor guarantee of product availability from those providers. Availablity is dependent on various details such as geography, individual needs, agency appointments, and carrier relationships. -->
						This website is for marketing and informational purposes only. We are not an insurance company, broker, or government agency, and we make no claims or guarantees regarding eligibility, enrollment, savings, or coverage. While we strive to connect consumers with reputable and properly licensed professionals, we cannot and do not promise specific outcomes or results.<br><br>
						We have taken steps to ensure that every agency and partner we work with maintains the appropriate licenses, appointments, and products we believe consumers may wish to consider. However, not all sales representatives or agents will have access to every carrier or plan. Consumers are encouraged to take an active role in their healthcare decisions and to verify directly with a licensed agent whether they qualify for the plans or programs being discussed.</br><br>
						By submitting your information through this website, you consent to be contacted by a licensed insurance agent or partner agency regarding health insurance and related products. Contact may occur via phone calls, text messages (SMS), or emails concerning plan options, pricing, and enrollment assistance. All communications comply with applicable laws, including the Telephone Consumer Protection Act (TCPA) and the CAN-SPAM Act, and are supported by valid consumer consent.<br><br>
						Not all products, carriers, or programs mentioned or displayed are available in every state. Availability, pricing, and eligibility vary based on location, carrier, and individual circumstances. During Open Enrollment and Special Enrollment periods, consumers should verify eligibility directly with a licensed agent, the Health Insurance Marketplace, or their applicable state exchange.<br><br>
						The appearance of logos, trademarks, or brand names of insurance providers on this site does not constitute an endorsement or guarantee of product availability from those providers. Product availability depends on factors such as geography, individual needs, agency appointments, and carrier relationships.<br><br>
						<?= $sitename ?> is privately owned and operated by RM7 Services LLC, a licensed insurance agency. Invitations for applications for insurance on affordable-healthcare.com are made through RM7 Services LLC, a subsidiary of <?= $sitename ?>, only where licensed and appointed. RM7 Services LLC licensing information can be found <a data-toggle="modal" data-target="#licensesModal" style="font-weight: bold; color: inherit !important; cursor: pointer;">here</a>. Submission of your information constitutes permission for a licensed agent to contact you with additional information about the cost and coverage details of health plans.<br><br>
						Possible insurance options include, but are not limited to, Major Medical Plans, Short-Term Plans, Fixed Indemnity Plans, and other supplemental coverage. All plan descriptions are for informational purposes only and subject to change. Insurance products may not be available in all states. <?= $sitename ?> is not affiliated with or endorsed by the United States government or the federal Medicare program.
						By using this site, you acknowledge that you have read and agree to our Terms of Service and Privacy Policy.
					<?php } ?>

				</p>
				<p style="color: #728c98; font-size: 14px; line-height: 1.3; font-weight: 400">
					<strong>Medicare recipients:</strong> We do not offer every plan available in your area. Any information we provide is limited to those plans we do offer in your specific geography. Please contact <a href="https://www.healthcare.gov/" target="_blank" style="font-weight: bold; color: inherit !important; text-decoration: underline!important;">HealthCare.gov</a> or 1-800-MEDICARE to get information on all of your options.<br><br>
					<strong>IMPORTANT:</strong> Some affiliates may offer a fixed indemnity policy, this is not comprehensive health insurance. It does not meet the "minimum essential coverage" requirements of the ACA.
				</p>
			</div>
		</div>
	</div>
</div>
<footer class="container-fluid py-3">
	<div class="container">
		<div class="row wrap">
			<div class="col-sm-5 col-md-6 col-lg-5 text-center text-sm-left"><? //=$address; 
																				?></div>
			<div class="col-sm-7 col-md-6 col-lg-7 text-center text-sm-right">
				<a data-toggle="modal" data-target="#policyModal" style="cursor: pointer;">Privacy<span class="d-none d-lg-inline"> Policy</span></a>
				<a data-toggle="modal" data-target="#termsModal" style="cursor: pointer;">Terms<span class="d-none d-lg-inline"> and Conditions</span></a>
			</div>
		</div>
	</div>
</footer>

<div class="fixed-bottom bg-white d-md-none">
	<div class="container">
		<div class="row">
			<?php /* if($call_now == 'true') { ?>
            <div class="col-lg-8 col-xl-7 offset-lg-4 offset-xl-5 py-2 py-md-0 text-center text-lg-right d-md-flex justify-content-end align-items-center">
              <div style="transform:scale(.8)">
                <div class="d-inline-block">
                  <p class="d-flex mb-0">Need&nbsp;a&nbsp;quote?&ensp;<b>Call&nbsp;toll&nbsp;free:&ensp;</b></p>
                </div>
                <div class="d-inline-block">
                  <div class="d-flex">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon-phone d-inline-block" fill="#67cdcb" width="40px" height="40px">
                      <path d="M4.292 2.353S-2.582 7.708 1.07 17.338s7.854 16.674 15.8 20.953 12.988-.835 12.988-.835L21.26 25.9s-3.653 6.422-9.236-.213c-4.083-5.14-4.514-8.56.214-11.77C12.47 9.317 6.212 2.9 4.29 2.354zm13.2 6.14l.285 3.8a5.96 5.96 0 015.081 2.251c.914 1.3 1.342 2.42.564 5.908a31.58 31.58 0 013.668 1.685s1.837-6.6-1.4-10.405a9.03 9.03 0 00-8.188-3.239zm.7-3.794l.064-4.7s11.236.57 15.317 8.915a22.485 22.485 0 01.8 16.98l-4.08-2.136s2.342-8.6-1.72-13.913A13.47 13.47 0 0018.192 4.7z"></path>
                    </svg>
                    <div class="d-inline-block text-center ml-2">
                      <a class="d-block text-primary" style="font-size: 1.8rem;line-height: 1;" href="tel:<?=$phonemin['agent']?>"><?=$phone['agent']?></a>
                      <span class="d-none">Mon - Sat 9 AM - 6 PM EST</span>
                    </div>
                  </div>
                </div>
                <?php } */ ?>
		</div>
	</div>
</div>
</div>
</div>

<script src='./js/jquery.min.js'></script>
<script src='./js/bootstrap.bundle.min.js'></script>
<script src='./js/parsley.min.js'></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.min.js"></script>
<script src='./js/jquery.inputmask.min.js'></script>
<script src="https://cdn.jsdelivr.net/gh/tigrr/circle-progress@v0.2.4/dist/circle-progress.min.js"></script>
<script src='./inc/shared/loading-modal/loading-modal.js'></script>
<script src='./js/custom.js'></script>

<!-- Modal -->
<div class="modal fade" id="policyModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content" style="overflow: scroll;">
			<div class="modal-body" style="color: #5b727d;">
				<?= file_get_contents(__DIR__ . '/modals/policy.php') ?>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="termsModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content" style="overflow: scroll;">
			<div class="modal-body" style="color: #5b727d;">
				<?= file_get_contents(__DIR__ . '/modals/terms.php') ?>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="licensesModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content" style="overflow: scroll;">
			<div class="modal-body" style="color: #5b727d;">
				<?= file_get_contents(__DIR__ . '/modals/licenses.php') ?>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="partnerModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content" style="overflow: scroll;">
			<div class="modal-body" style="color: #5b727d;">
				<?= file_get_contents(__DIR__ . '/modals/affiliates.php') ?>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="lastcall" tabindex="-1" role="dialog" aria-labelledby="lastcall">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content modal-data">
			<div class="modal-header header-close">
				<div class="col-md-12 pt-3">
					<button type="button" class="close btn-close-modal" data-dismiss="modal" aria-label="Close">
						<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-x icon-close" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
							<path fill-rule="evenodd" d="M4.646 4.646a.5.5 0 01.708 0L8 7.293l2.646-2.647a.5.5 0 01.708.708L8.707 8l2.647 2.646a.5.5 0 01-.708.708L8 8.707l-2.646 2.647a.5.5 0 01-.708-.708L7.293 8 4.646 5.354a.5.5 0 010-.708z"></path>
						</svg>
					</button>
				</div>
			</div>
			<div class="modal-body modal-body-container container p-0">
				<div class="row">
					<div class="body-content text-center">
						<h3 class="modal-title">
							Before you go... We can connect you to a licensed health insurance agent ready to help.
						</h3>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 body-call-content">
						<div class="row space-around text-center justify-content-center">
							<div class="call-agent col-md-4">
								<div class="position-relative">
									<img src="./img/agent-photo4.png" loading="lazy" class="agent-photo" height="292px" width="292px" alt="Female support agent smiling">
									<div class="white-dot-agent" id="blink">
										<div class="blue-dot-agent"></div>
									</div>
								</div>
								<span class="licensed-agent-text">Licensed Agents Available</span>
							</div>
							<div class="call-btn call-content col-auto">
								<a class="ringpool click-to-call-only" href="tel:<?= $phone['popup'] ?>">
									<div class="phone-text modal-phone-number d-flex align-items-center">
										<svg xmlns="http://www.w3.org/2000/svg" width="35.675" height="40" class="icon-phone" fill="#FFF">
											<path d="M4.292 2.353S-2.582 7.708 1.07 17.338s7.854 16.674 15.8 20.953 12.988-.835 12.988-.835L21.26 25.9s-3.653 6.422-9.236-.213c-4.083-5.14-4.514-8.56.214-11.77C12.47 9.317 6.212 2.9 4.29 2.354zm13.2 6.14l.285 3.8a5.96 5.96 0 015.081 2.251c.914 1.3 1.342 2.42.564 5.908a31.58 31.58 0 013.668 1.685s1.837-6.6-1.4-10.405a9.03 9.03 0 00-8.188-3.239zm.7-3.794l.064-4.7s11.236.57 15.317 8.915a22.485 22.485 0 01.8 16.98l-4.08-2.136s2.342-8.6-1.72-13.913A13.47 13.47 0 0018.192 4.7z"></path>
										</svg>
										<span class="phone-number"><?= $phone['popup'] ?></span>
									</div>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="divide-line">
				<hr class="my-0">
			</div>
			<div class="modal-footer modal-footer-container">
				<div class="col-md-12 body-button text-center">
					<button type="button" class="quote-btn modal-continue-btn" data-dismiss="modal" aria-label="Close" onclick="openJustForm();">
						NO, THANKS. I'LL CONTINUE ONLINE.
					</button>
				</div>
			</div>
		</div>
	</div>

</div>
<script type="text/javascript">
	window._mfq = window._mfq || [];
	(function() {
		var mf = document.createElement("script");
		mf.type = "text/javascript";
		mf.defer = true;
		mf.src = "//cdn.mouseflow.com/projects/8ca073b2-3928-4c00-b64c-bd49cda80ae8.js";
		document.getElementsByTagName("head")[0].appendChild(mf);
	})();
</script>
<!-- Facebook Pixel Code -->
<script>
	! function(f, b, e, v, n, t, s) {
		if (f.fbq) return;
		n = f.fbq = function() {
			n.callMethod ?
				n.callMethod.apply(n, arguments) : n.queue.push(arguments)
		};
		if (!f._fbq) f._fbq = n;
		n.push = n;
		n.loaded = !0;
		n.version = '2.0';
		n.queue = [];
		t = b.createElement(e);
		t.async = !0;
		t.src = v;
		s = b.getElementsByTagName(e)[0];
		s.parentNode.insertBefore(t, s)
	}(window, document, 'script',
		'https://connect.facebook.net/en_US/fbevents.js');
	fbq('init', '1459473901156680');
	fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" src="https://www.facebook.com/tr?id=1459473901156680&ev=PageView &noscript=1" /></noscript>
<!-- End Facebook Pixel Code -->
<script type="text/javascript" src="//cdn.callrail.com/companies/631399289/2dc49fb8bb1148ee6b02/12/swap.js"></script>
</body>

</html>