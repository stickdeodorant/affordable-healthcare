<?php 

    if(session_status() === PHP_SESSION_NONE) session_start();

    $pageName = 'typ';
    include 'inc/header.php';
    $firstname = $_SESSION['first_name'] ? $_SESSION['first_name'] : $_GET['first_name'];
    $lastname  = $_SESSION['last_name'] ? $_SESSION['last_name'] : $_GET['last_name'];
    $fullname  = $_SESSION['name_full'] ? $_SESSION['name_full'] : $_GET['name_full'];
    $age = $_SESSION['age'] ? $_SESSION['age'] : $_GET['age'];
    $city = $_SESSION['city'] ? $_SESSION['city'] : $_GET['city'];
    $state = $_SESSION['state'] ? $_SESSION['state'] : $_GET['state'];
    $zip = $_SESSION['zip'] ? $_SESSION['zip'] : $_GET['zip'];
    $did = $_SESSION['did'] ? $_SESSION['did'] : $_GET['did'];

?>

<!-- 07/06/2021 Event snippet for Website lead conversion page -->
<?php if ($_GET['type'] != 'medicare') { ?>
    <script>
      gtag('event', 'conversion', {'send_to': 'AW-340114397'});
    </script>
<?php } ?>

<section id="success">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 success text-center d-xl-none">
                <h2>
                    <svg class="checkmark text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/><path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/></svg>
                    <span>Information Received!</span>
                </h2>
                <p class="text-left ml-5 pl-2">
                    <span class="fade1">Name: <strong><?=$firstname?> <?=$lastname?></strong><span class="check-fade1 green bold"> &#x2713;</span></span><br>
                    <span class="fade2">Age: <strong><?=$age?></strong><span class="check-fade2 green bold"> &#x2713;</span></span><br>
                    <span class="fade3">City: <strong><?=$city?></strong><span class="check-fade3 green bold"> &#x2713;</span></span><br>
                    <span class="fade4">State: <strong><?=$state?></strong><span class="check-fade4 green bold"> &#x2713;</span></span><br>
                    <span class="fade5">Application: <strong style="color:#7AC142;">Submitted</strong><span class="check-fade5 green bold"> &#x2713;</span></span>
                </p>
            </div>
        </div>
    </div>
</section>
<section id="typ">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-6 d-flex flex-column align-items-center justify-content-center">
                    <div class="success text-center d-none d-xl-block">
                        <h2>
                            <svg class="checkmark text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/><path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/></svg>
                            <span>Information Received!</span>
                        </h2>
                        <p class="text-left ml-5 pl-2">
                            <span class="fade1">Name: <strong><?=$firstname?> <?=$lastname?></strong><span class="check-fade1 green bold"> &#x2713;</span></span><br>
                            <span class="fade2">Age: <strong><?=$age?></strong><span class="check-fade2 green bold"> &#x2713;</span></span><br>
                            <span class="fade3">City: <strong><?=$city?></strong><span class="check-fade3 green bold"> &#x2713;</span></span><br>
                            <span class="fade4">State: <strong><?=$state?></strong><span class="check-fade4 green bold"> &#x2713;</span></span><br>
                            <span class="fade5">Application: <strong style="color:#7AC142;">Submitted</strong><span class="check-fade5 green bold"> &#x2713;</span></span>
                        </p>
                    </div>
                    <p class="my-3 text-center"><?=$firstname?>, your information has been shared with one or more agents who may be reaching out to discuss options.</p>
                    <h1 class="bolder text-center">Ready to enroll?</h1>
                    <p class="sub-head mb-4 text-center"><strong style="text-transform: uppercase;" class="green">Call Now</strong> and see what benefits you may be eligible for.</p>
                    <a class="call-btn text-center" href="tel:<?php echo ($_GET['type'] == 'medicare') ? $phonemin['typ'] : $phonemin[$did]; ?>" onclick="ga('send', 'event', 'Call Buttons', 'Click', '<?php echo ($_GET['type'] == 'medicare') ? 'medicare' : 'healthcare'; ?>', 'main');">
                        <span style="font-weight:500">Call Now</span><br>
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon-phone d-inline-block" fill="#FFF" width="40px" height="40px" style="vertical-align:middle; margin-right: 10px; margin-left: -10px;">
                            <path d="M4.292 2.353S-2.582 7.708 1.07 17.338s7.854 16.674 15.8 20.953 12.988-.835 12.988-.835L21.26 25.9s-3.653 6.422-9.236-.213c-4.083-5.14-4.514-8.56.214-11.77C12.47 9.317 6.212 2.9 4.29 2.354zm13.2 6.14l.285 3.8a5.96 5.96 0 015.081 2.251c.914 1.3 1.342 2.42.564 5.908a31.58 31.58 0 013.668 1.685s1.837-6.6-1.4-10.405a9.03 9.03 0 00-8.188-3.239zm.7-3.794l.064-4.7s11.236.57 15.317 8.915a22.485 22.485 0 01.8 16.98l-4.08-2.136s2.342-8.6-1.72-13.913A13.47 13.47 0 0018.192 4.7z"></path>
                        </svg><?php echo ($_GET['type'] == 'medicare') ? $phone['typ'] : $phone[$did]; ?>
                    </a>

                </div>
                <?php /*<div class="col-xl-6 mt-5 mt-xl-0 p-0" style="moz-radial-gradient(center,ellipse cover,#2141a8 0,#2141a8 100%);background:-webkit-radial-gradient(center,ellipse cover,#1459c6 0,#6298ee 100%);background:radial-gradient(ellipse at center,#1459c6 0,#6298ee 100%);">*/ ?>
                <div class="col-xl-6 mt-5 mt-xl-0 p-0" style="background: #143F84;">

                    <img class="img-fluid full-image d-none d-xl-block" src="img/csr1.avif" alt="Call Now">

                </div>
            </div>
        </div>
    </section>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content text-center">
            <div class="modal-body" style="background-color: #ffffff; width: 760px">
                <a href="tel:<?php echo ($_GET['type'] == 'medicare') ? $phonemin['typ'] : $phonemin[$did]; ?>">
                    <p class="phone-number" style="border: thick;"></p>
                    <img src="img/call-modal-bg.jpg" alt="" width="726px;">
                </a>
            </div>
        </div>
    </div>
</div>

<?php include "inc/footer.php"; ?>
<script src="../js/jquery-3.2.1.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<style>
        #typ                { align-items: center; display: flex; flex-direction: column; height: 100%; justify-content: center; }
        #typ .row           { min-height: 100vh; }
        .logo               { max-width: 240px; }
        .success            { overflow: hidden; }
        h1                  { color: #143F84; font-weight:800; font-size: 36px; line-height: 1.3; text-transform: uppercase; }
        h2                  { color: #2fbdaa; font-weight:600; font-size: 30px; line-height:1.2; text-transform:capitalize; }
        p.sub-head          { font-size: 1.5rem; }
        .green              { color: #2fbdaa; }
        .checkmark          { animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both; border-radius: 50%; box-shadow: inset 0px 0px 0px #FFF; display: inline-block; height: 40px; margin-top: -13px; stroke: #FFF; stroke-miterlimit: 10; stroke-width: 4; width: 40px; }
        .checkmark__circle  { animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards; fill: #2fbdaa; stroke-dasharray: 166; stroke-dashoffset: 166; stroke-width: 4; stroke-miterlimit: 10; stroke: #2fbdaa; }
        .checkmark__check   { animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards; stroke-dasharray: 48; stroke-dashoffset: 48; transform-origin: 50% 50%; }
        .call-btn,
        .call-btn:hover     { background-color: #FF6600; border: 1px solid #fff; border-radius: 100px; color: white; display: inline-block; font-size: 26px; font-weight: 600; line-height: 0.6; margin-top: 10px; max-width: 100%; padding: 15px 40px 20px; position: relative; text-decoration: none; width: 340px; }
        .call-btn span      { font-size: 16px; text-transform: uppercase; }

        .wiggle             { animation: wiggle 5s infinite; }
        .full-image         { height: 100%; object-fit: cover; object-position: 21% 100%; }
        span.fade1, span.check-fade1 { display: none; }
        span.fade2, span.check-fade2 { display: none; }
        span.fade3, span.check-fade3 { display: none; }
        span.fade4, span.check-fade4 { display: none; }
        span.fade5, span.check-fade5 { display: none; }
        
        @keyframes stroke {
          100% { stroke-dashoffset: 0; }
        }
        @keyframes scale {
          0%, 100% { transform: none; }
          50% { transform: scale3d(1.1, 1.1, 1); }
        }
        @keyframes fill {
          100% { box-shadow: inset 0px 0px 0px 30px #7ac142; }
        }
        @keyframes wiggle {
            0% { transform: rotate(0deg); }
           75% { transform: rotate(0deg); }
           80% { transform: rotate(5deg); }
           85% { transform: rotate(-5deg); }
           90% { transform: rotate(5deg); }
           95% { transform: rotate(-5deg); }
          100% { transform: rotate(0deg); }
        }
        @keyframes shrink {
            15%  { transform: scale(1.2); }
            100% { transform: scale(0); }
        }
		@media (min-width: 992px) {
            h2.span         { font-weight:600; font-size:calc(24px + 2.5vw); letter-spacing:-0.25px; }
        }
        @media (min-width: 1200px) {
            
        }
        @media (min-width: 1440px) {
            
        }
        @media (min-width: 1921px) {
            
        }
	</style>

    <script>
        $( document ).ready(function() {
            console.log('ready');
            document.addEventListener('click',(e)=>{
                if(e.target.closest('a[href*="tel:"]')){
                    gtag('event','phone_number_click')
                }
            })

            if (window.screen.width < 1200) {
                $('.d-xl-block').remove();
            } else {
                $('.d-xl-none').remove();
            }
            
            $('.fade1').fadeIn( 500, function() {

                $('.check-fade1').fadeIn( 800 );
                $('.fade2').fadeIn( 500, function() {

                    $('.check-fade2').fadeIn( 800 );
                    $('.fade3').fadeIn( 500, function() {

                        $('.check-fade3').fadeIn( 800 );
                        $('.fade4').fadeIn( 500, function() {

                            $('.check-fade4').fadeIn( 800, function() {
                            $('.fade5').fadeIn( 500, function() {

                                    $('.check-fade5').fadeIn( 800, function() {

                                        var success = $('.success');
                                        var ogHeight = success.outerHeight();
                                        success.css({'height': ogHeight+'px'});
                                        setTimeout(function(){ 

                                                success.animate({'height' : (ogHeight*1.3)+'px'}, 300);
                                                success.animate({'height' : (ogHeight*0)+'px', 'opacity': 0}, 1000);

                                        }, 1000);
                                    });
                                });
                            });
                        });
                    });
                });
            });
        });
    </script>

<?php
    $statesAccepted = array('AK', 'AL', 'AZ', 'AR', 'CA', 'DE', 'GA', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'MI', 'MS', 'MO', 'NV', 'NM', 'OH', 'PA', 'SC', 'SD', 'TN', 'TX', 'VA', 'WV', 'WI', 'WY');
    
    if ($_GET['src'] === 'Magenta') {
        $sid = '7090';
    } else if ($_GET['src'] === 'Magenta2') {
        $sid = '7088';
    }
    if (in_array($state, $statesAccepted)) {
        $trackingString = 'https://www.pirolane.com/rd/ipx.php?hid='.$_SESSION['HIT_ID'].'&sid='.$sid.'&transid='; ?>
        <input id="tracking-string" type="hidden" value="<?=$trackingString?>">
        <script>
            var trackingString = $('#tracking-string').val();
            var trackingStringJS = trackingString + sessionStorage.getItem('userPhone');
            if(sessionStorage.getItem('entryStatus') == 'success') {
                document.write('<iframe width="1" height="1" frameborder="0" src="'+trackingStringJS+'"></iframe>');
            }
        </script>
<?php } ?>

<?php /* DMS Tracking Pixel */
    // $dmsStatesAccepted = array('AZ','FL','GA','TX','KS','MS','MO','NC','OK','SC','TN','IL','MI','OH','LA','VA','AL','AR','CA','DE','GA','ID','IL','IN','IA','KY','NV','NM','PA','SC','WV','WI','WY','CO','CT','MA','MN','MT','NE','NH','NJ','ND','UT');
    // $dmsStatesAccepted = array('AL','AR','AZ','CA','CO','CT','DE','FL','GA','IA','ID','IL','IN','KS','KY','LA','MA','MI','MN','MO','MS','MT','NC','ND','NE','NH','NJ','NM','NV','OH','OK','PA','SC','TN','TX','UT','VA','WI','WV','WY');
    $dmsStatesAccepted = array('AL','AR','AZ','CO','DE','FL','GA','IA','ID','IL','IN','KS','KY','LA','MA','MI','MN','MO','MS','MT','NC','ND','NE','NH','NJ','NM','OH','OK','SC','TN','TX','UT','VA','WI','WV','WY');
    if (in_array($state, $dmsStatesAccepted)) { ?>
        <script>
            if(sessionStorage.getItem('dms_lead') == 'true') {
                var dms_trackingString = 'https://eng.trkcnv.com/pixel?cid='+sessionStorage.getItem('cid')+'&refid='+sessionStorage.getItem('userPhone')+'&clickid='+sessionStorage.getItem('gclid');
                document.write('<iframe width="1" height="1" frameborder="0" src="'+dms_trackingString+'"></iframe>');
            }
        </script>
<?php } ?>

<script type="text/javascript">var hitComplete = true;</script>

<script type="text/javascript">
$('a[data-target="#myModal"]').click(function() {
    var phone = $(this).data('phone');
    $('#myModal .phone-number').text(phone);
    $('#myModal a').prop('href', 'tel:' + phone);
});
</script>

<!-- Facebook Pixel Code -->
<script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window,document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '1459473901156680'); 
    fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" src="https://www.facebook.com/tr?id=1459473901156680&ev=PageView &noscript=1"/></noscript>
<!-- End Facebook Pixel Code -->

<script>fbq('track', 'Contact');</script>

<?php if($call_now === 'true') { ?>
	<script type="text/javascript" src="//cdn.callrail.com/companies/447996446/375307dddfb93a0d4e5c/12/swap.js"></script>
<?php } ?>

<script>
  dataLayer.push({
    'event':'EC_FormSubmit',
    'enhanced_conversion_data': {
      "email": '<?=$user_email?>',   
    }
  })
</script>

</body>
</html>
