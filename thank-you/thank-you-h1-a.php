<?php 

    session_start();

    $pageName = 'typ';
    include 'inc/header.php';
    $firstname = $_SESSION['name_first'];
    $lastname  = $_SESSION['name_last'];
    $fullname  = $_SESSION['name_full'];
    $nophone = false;
    if ($_GET['nophone'] == true)
        // (strtoupper($_GET['state']) == 'FL' && $_GET['type'] != 'medicare')
        { $nophone = true; }

    $state = $_GET['state'];
?>

<!-- 07/06/2021 Event snippet for Website lead conversion page -->
<?php if ($_GET['type'] != 'medicare') { ?>
    <script>
      gtag('event', 'conversion', {'send_to': 'AW-340114397'});
    </script>
<?php } ?>

<section id="hero" class="first-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 id="headline">Information <span class="green">received</span>.</h1>
                <h2 id="subheadline">Your information has been submitted. You may qualify with several different <span class="green">a-rated insurance carriers</span>.</h2>
                <img src="img/envelope.jpg" alt="Flex Card" class="img-fluid img-main">
                <h2 id="subheadline">Several different groups may be contacting you with your available benefit.<br>Your information has been matched with agencies that will be reviewing your information.</h2>
                
                <h4 class="mb-0 warning large">Ready to enroll now?</h4>
                <p>Call now to see what benefits you may be&nbsp;eligible&nbsp;for.</p>
                <a class="lg-call-button" href="tel:<?php echo ($_GET['type'] == 'medicare') ? $phonemin['typ'] : $phonemin['typ']; ?>" onclick="ga('send', 'event', 'Call Buttons', 'Click', '<?php echo ($_GET['type'] == 'medicare') ? 'medicare' : 'healthcare'; ?>', 'main');">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon-phone d-inline-block" fill="#FFF" width="40px" height="40px" style="vertical-align:middle; margin-right: 5px;">
                        <path d="M4.292 2.353S-2.582 7.708 1.07 17.338s7.854 16.674 15.8 20.953 12.988-.835 12.988-.835L21.26 25.9s-3.653 6.422-9.236-.213c-4.083-5.14-4.514-8.56.214-11.77C12.47 9.317 6.212 2.9 4.29 2.354zm13.2 6.14l.285 3.8a5.96 5.96 0 015.081 2.251c.914 1.3 1.342 2.42.564 5.908a31.58 31.58 0 013.668 1.685s1.837-6.6-1.4-10.405a9.03 9.03 0 00-8.188-3.239zm.7-3.794l.064-4.7s11.236.57 15.317 8.915a22.485 22.485 0 01.8 16.98l-4.08-2.136s2.342-8.6-1.72-13.913A13.47 13.47 0 0018.192 4.7z"></path>
                    </svg><?php echo ($_GET['type'] == 'medicare') ? $phone['typ'] : $phone['typ']; ?>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content text-center">
            <div class="modal-body" style="background-color: #ffffff; width: 760px">
                <a href="tel:<?php echo ($_GET['type'] == 'medicare') ? $phonemin['typ'] : $phonemin['typ']; ?>">
                    <p class="phone-number" style="border: thick;"></p>
                    <img src="../img/call-modal-bg.jpg" alt="" width="726px;">
                </a>
            </div>
        </div>
    </div>
</div>
<?php include "inc/footer.php"; ?>
<script src="../js/jquery-3.2.1.min.js"></script>
<script src="../js/bootstrap.min.js"></script>

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

</body>
</html>
