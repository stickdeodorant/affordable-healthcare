<?php
$slider = 'active';
$slideInitial = $_GET['initial'];
$sliderAuto = $_GET['auto'];


include 'inc/header.php';
$featureTitle = 'Your Quote is Ready Now!';
$featureSubtitle = 'Call To Speak With A Licensed Agent and Sign Up In Minutes.';
// Use 0 or '0' for a caption value to hide the value, defaults to main otherwise
$featureCaption = [
	'default' => 'Find affordable health insurance <br class="d-inline-block d-sm-none">with ' . $featureTitle,
	'mobile' => '',
	'filled' => '',
	'mfilled' => '',
];
?>
<header class="container-fluid text-center d-flex flex-wrap">
	<div class="container justify-content-center align-self-center">
		<div class="row row1">
			<div class="col-12">
				<h1 class="headline my-0"><?=$featureTitle?></h1>
				<h5 class="subheadline font-weight-normal mb-3 mt-2"><?=$featureSubtitle?></h5>
			</div>
		</div>
    <div class="row pt-3 pt-md-5">
      <div class="col-md-8 offset-md-2 body-call-content">
        <div class="row">
          <div class="col text-center testimonials">
            <p style="font-style: italic;">"9/10 people qualify for cheaper healthcare then they already&nbsp;have."</p>
            <p style="font-style: italic;">"8/10 people qualify for cheaper healthcare then they already&nbsp;have."</p>
            <p style="font-style: italic;">"7/10 people qualify for cheaper healthcare then they already&nbsp;have."</p>
            <p style="font-style: italic;">"6/10 people qualify for cheaper healthcare then they already&nbsp;have."</p>
          </div>
        </div>
        <div class="row space-around text-center">
          <div class="call-btn call-content col-auto order-1 order-lg-2">
            <a class="ringpool click-to-call-only" href="tel:<?=$phone[$phoneVer]?>">
              <div class="phone-text modal-phone-number d-flex align-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="35.675" height="40" class="icon-phone" fill="#FFF">
                  <path d="M4.292 2.353S-2.582 7.708 1.07 17.338s7.854 16.674 15.8 20.953 12.988-.835 12.988-.835L21.26 25.9s-3.653 6.422-9.236-.213c-4.083-5.14-4.514-8.56.214-11.77C12.47 9.317 6.212 2.9 4.29 2.354zm13.2 6.14l.285 3.8a5.96 5.96 0 015.081 2.251c.914 1.3 1.342 2.42.564 5.908a31.58 31.58 0 013.668 1.685s1.837-6.6-1.4-10.405a9.03 9.03 0 00-8.188-3.239zm.7-3.794l.064-4.7s11.236.57 15.317 8.915a22.485 22.485 0 01.8 16.98l-4.08-2.136s2.342-8.6-1.72-13.913A13.47 13.47 0 0018.192 4.7z"></path>
                </svg>
                <span class="phone-number"><?=$phone[$phoneVer]?></span>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
    <?php /*
    <div class="row pt-3 pt-md-5">
      <div class="col text-center">
        <img src="/img/affiliate-all-updated.png" class="img-fluid">
      </div>
    </div>
    */ ?>
	</div>
</header>
<input class="d-none" type="hidden" id="initial-slide" value="<?php if ($slideInitial != '' || $slideInitial != null) { echo $slideInitial; } else { echo '0'; }?>">
<input class="d-none" type="hidden" id="slider-auto" value="<?php if ($sliderAuto === 'true') { echo 'true'; } else { echo 'false'; } ?>">
<?php include 'inc/footer.php'; ?>
