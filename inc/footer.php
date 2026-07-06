			<footer class="container-fluid py-3">
				<div class="container pt-3">
					<div class="row">
						<div class="col-lg-4 pr-lg-4">
							<h5>Consumer Caution</h5>
							<p>We feel confident in our verified affiliate agencies and do our best to make sure consumers are in good hands. However, affordable-healthcare.com is a third party lead generation website. We are not an insurance company and cannot make claims or guarantees about coverage, pricing, benefits, eligibility, or plan availability.</p>
							<a href="/consumer-caution.php" class="footer-link-emphasis">Consumer Caution</a>
						</div>
						<div class="col-lg-4 px-lg-4">
							<h5>Health Exchange Information</h5>
							<p>The health exchange is a marketplace where eligible consumers may be able to compare and enroll in qualified health plans. For 2027 coverage, Open Enrollment is currently scheduled to begin on November 1, 2026 in most states and end on December 15, 2026 in most states. Some state based Marketplaces may have later deadlines. Outside Open Enrollment, you may need a qualifying life event, also called a QLE, to enroll in certain types of comprehensive coverage.</p>
						</div>
						<div class="col-lg-4 pl-lg-4">
							<h5>Quick Links</h5>
							<ul>
								<li><a href="/smart-shopping.php">Smart Shopping</a></li>
								<li><a href="/consumer-caution.php">Consumer Caution</a></li>
								<li><a href="/faq.php">FAQ</a></li>
								<li><a href="/terms.php">Terms & Conditions</a></li>
								<li><a href="mailto:support@<?= $domain ?>"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail w-4 h-4" aria-hidden="true" code-path="src/components/Layout.tsx:119:17">
											<path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"></path>
											<rect x="2" y="4" width="20" height="16" rx="2"></rect>
										</svg> support@<?= $domain ?></a></li>
								<li><a href="tel:1-888-HEALTH-Q"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone w-4 h-4" aria-hidden="true" code-path="src/components/Layout.tsx:123:17">
											<path d="M13.832 16.568a1 1 0 0 0 1.213-.303l.355-.465A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.468.351a1 1 0 0 0-.292 1.233 14 14 0 0 0 6.392 6.384"></path>
										</svg> 1-888-HEALTH-Q</a></li>
						</div>
					</div>
					<div class="row border-top border-muted mt-3 pt-3">
						<div class="col">
							<p><?= $sitename ?> is a third party lead generation website. We are not an insurance company, government agency, health exchange, or insurance carrier. We do not issue, underwrite, administer, or make final decisions about insurance policies. Your information may be shared, sold, or transferred to affiliate agencies, marketing companies, or service providers that may contact you about health insurance options. We are a lead generation company and cannot make any claims about coverage. Before enrolling, ask clear questions, avoid high pressure sales tactics, and ask for written plan documents.</p>
						</div>
					</div>
				</div>
			</footer>
			<?php //if($_SESSION['agent'])  { 
			?>
			<?php /*
				<div class="fixed-bottom bg-white d-md-none">
					<div class="container">
						<div class="row">
							<?php if($call_now == 'true') { ?>
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
									<?php } ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php */ ?>
			<?php //} 
			?>
			<?php $mainJsVersion = @filemtime(__DIR__ . '/../js/index.js') ?: time(); ?>
			<script src='/js/jquery-3.2.1.min.js'></script>
			<script src='/js/popper.min.js'></script>
			<script src='/js/bootstrap.min.js'></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js" integrity="sha512-XtmMtDEcNz2j7ekrtHvOVR4iwwaD6o/FUJe6+Zq+HgcCsk3kj4uSQQR8weQ2QVj1o0Pk6PwYLohm206ZzNfubg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
			<script src='/js/index.js?v=<?= $mainJsVersion ?>'></script>
			<div class="container">
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

				<?php if (!isset($_GET['campaign']) && $call_now == 'true') { ?>
					<div class="modal fade" id="lastcall" tabindex="-1" role="dialog" aria-labelledby="lastcall">
						<div class="modal-dialog modal-dialog-centered modal-lg">
							<div class="modal-content">
								<div class="modal-body">
									<button type="button" class="close close-custom" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>

									<div class="insurance-modal-content">
										<div class="modal-image-section">
											<!-- Using a placeholder image - replace with your actual image -->
											<img src="/img/agent-photo4.png" alt="Customer Service Representative">
										</div>

										<div class="modal-text-section">
											<h2 class="modal-title-custom">Looking For Affordable<br>Health Plans?</h2>

											<a href="tel:<?= $phone[$phoneVer] ?>" class="phone-button">
												<i class="ti ti-phone"></i>&nbsp;
												<?= $phone[$phoneVer] ?>
											</a>

											<p class="modal-description">
												Speak Directly with a Licensed<br>
												Insurance Agent.
											</p>
										</div>
									</div>
								</div>
							</div>
							<!-- <div class="modal-dialog modal-md" role="document">
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
									<div class="col-md-6 body-call-content">
									<div class="body-content text-center">
											<div class="position-relative">
											<img src="/img/agent-photo.png" loading="lazy" class="agent-photo" height="292px" width="292px" alt="Female support agent smiling">
												<div class="white-dot-agent" id="blink">
													<div class="blue-dot-agent"></div>
												</div>
											</div>
										</div>
										<div class="col-md-6 body-call-content">
											<div class="row space-around text-center">
												<h3 class="modal-title">
													Looking For Affordable Health Plans?
												</h3>
												<div class="call-btn call-content col-auto">
												<a class="ringpool click-to-call-only" href="tel:<?= $phone[$phoneVer] ?>">
													<div class="phone-text modal-phone-number d-flex align-items-center">
														<svg xmlns="http://www.w3.org/2000/svg" width="35.675" height="40" class="icon-phone" fill="#FFF">
															<path d="M4.292 2.353S-2.582 7.708 1.07 17.338s7.854 16.674 15.8 20.953 12.988-.835 12.988-.835L21.26 25.9s-3.653 6.422-9.236-.213c-4.083-5.14-4.514-8.56.214-11.77C12.47 9.317 6.212 2.9 4.29 2.354zm13.2 6.14l.285 3.8a5.96 5.96 0 015.081 2.251c.914 1.3 1.342 2.42.564 5.908a31.58 31.58 0 013.668 1.685s1.837-6.6-1.4-10.405a9.03 9.03 0 00-8.188-3.239zm.7-3.794l.064-4.7s11.236.57 15.317 8.915a22.485 22.485 0 01.8 16.98l-4.08-2.136s2.342-8.6-1.72-13.913A13.47 13.47 0 0018.192 4.7z"></path>
														</svg>
														<span class="phone-number"><?= $phone[$phoneVer] ?></span>
													</div>
												</a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div> -->
						</div>
					<?php } ?>
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
					<script>
						var initSlide = $('#initial-slide').val();
						var sliderAuto = $('#slider-auto').val();
						if (sliderAuto === 'true') {
							$('.testimonials').slick({
								infinite: true,
								slidesToShow: 1,
								arrows: true,
								autoplay: true,
								autoplaySpeed: 2000,
								initialSlide: initSlide,
							});
						} else {
							$('.testimonials').slick({
								infinite: true,
								slidesToShow: 1,
								arrows: true,
								autoplay: false,
								initialSlide: initSlide,
							});
						}
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
					<?php if ($call_now === 'true') { ?>
						<script type="text/javascript" src="//cdn.callrail.com/companies/447996446/375307dddfb93a0d4e5c/12/swap.js"></script>
					<?php } ?>
					<input type="hidden" value="<?= $current_date ?>" />
					<input type="hidden" value="changes working" />
					</body>

					</html>