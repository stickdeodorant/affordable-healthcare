<?php

if (session_status() === PHP_SESSION_NONE) session_start();

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
<style>
    .fade1,
    .fade2,
    .fade3,
    .fade4,
    .fade5 {
        opacity: 0 !important;
        /* Start invisible, not display:none */
        visibility: hidden !important;
        /* Hidden but still takes space */
        color: #e4f5d7ff;
        text-shadow: 1px 1px 1px rgba(122, 193, 66, .5) !important;
        font-weight: 500 !important;
        transition: opacity 0.6s ease-in-out, visibility 0s linear 0.6s !important;
    }
</style>
<!-- 07/06/2021 Event snippet for Website lead conversion page -->
<?php if ($_GET['type'] != 'medicare') { ?>
    <script>
        gtag('event', 'conversion', {
            'send_to': 'AW-340114397'
        });
    </script>
<?php } ?>

<section id="success">
    <div class="success-content-wrapper">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    <div class="success text-center">
                        <h2>
                            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                                <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" />
                                <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
                            </svg>
                            <span>Information Received!</span>
                        </h2>
                        <div class="info-list">
                            <p><span class="fade1">Name: <strong><?= $firstname ?> <?= $lastname ?></strong></span><span class="check-fade1"> ✓</span></p>
                            <p><span class="fade2">Age: <strong><?= $age ?></strong></span><span class="check-fade2"> ✓</span></p>
                            <p><span class="fade3">City: <strong><?= $city ?></strong></span><span class="check-fade3"> ✓</span></p>
                            <p><span class="fade4">State: <strong><?= $state ?></strong></span><span class="check-fade4"> ✓</span></p>
                            <p><span class="fade5">Application: <strong style="color:#7AC142;">Submitted</strong></span><span class="check-fade5"> ✓</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="health-plans" class="plans-section">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">
                <div class="plans-intro text-center">
                    <?php /* Affiliation logos */ ?>
                    <h2 class="plans-title">Call Now To Select Your Plan!</h2>
                    <a class="d-none d-md-block text-white mb-3" style="margin-top: -40px;" href="tel:<?php echo ($_GET['type'] == 'medicare') ? $phonemin['typ'] : $phonemin[$did]; ?>" onclick="ga('send', 'event', 'Call Buttons', 'Click', '<?php echo ($_GET['type'] == 'medicare') ? 'medicare' : 'healthcare'; ?>', 'main');">
                        <h2 class="plans-title"><?php echo $phone[$did]; ?></h2>
                    </a>
                    <?php include '../inc/aff-logos.php'; ?>
                    <?php /*
                    <p class="plans-subtitle">We offer multiple health insurance options to fit your situation and budget</p>
                */ ?>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="plan-card comprehensive-plan">
                            <div class="plan-icon">
                                <!-- <i class="fas fa-hospital"></i> -->
                                <img src="../img/icon-hospital.png" alt="Health Insurance" class="plan-icon-img">
                                <h3 class="plan-title">Major Medical Insurance</h3>
                            </div>
                            <p class="plan-description">
                                If your agency offers more comprehensive benefits, you may qualify for traditional health care plans that include hospital care, doctor visits, prescriptions and more. Inquire below to see if you qualify today.
                            </p>
                            <a href="tel:<?php echo ($_GET['type'] == 'medicare') ? $phonemin['typ'] : $phonemin[$did]; ?>" onclick="ga('send', 'event', 'Call Buttons', 'Click', '<?php echo ($_GET['type'] == 'medicare') ? 'medicare' : 'healthcare'; ?>', 'main');">
                                <div class="plan-highlight">Full Coverage</div>
                            </a>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="plan-card aca-plan">
                            <div class="plan-icon">
                                <!-- <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" style="width: 48px; height: 48px;fill: #dc3545 !important"><path d="M288 96V64c0-17.7 14.3-32 32-32s32 14.3 32 32v32h64c53 0 96 43 96 96s-43 96-96 96h-16v-64h16c17.7 0 32-14.3 32-32s-14.3-32-32-32h-64v192h32c53 0 96 43 96 96 0 47.6-34.6 87.1-80 94.7v-67c9.6-5.5 16-15.9 16-27.7 0-17.7-14.3-32-32-32h-32v160c0 17.7-14.3 32-32 32s-32-14.3-32-32v-32h-32c-17.7 0-32-14.3-32-32s14.3-32 32-32h32v-64h-32c-53 0-96-43-96-96 0-47.6 34.6-87.1 80-94.7v67a31.9 31.9 0 0 0-16 27.7c0 17.7 14.3 32 32 32h32V160h-72.6a64 64 0 0 1-55.4 32h-16a48.01 48.01 0 0 1 0-96h144z"/></svg> -->
                                <img src="../img/icon-aca.png" alt="Affordable Care Act" class="plan-icon-img">
                                <h3 class="plan-title">Affordable Care Act</h3>
                            </div>
                            <p class="plan-description">
                                If you live in an ACA state and have experienced a life-changing event, you may qualify for Affordable Care Act coverage. Subsidies based on income could reduce your premiums, sometimes as low as $0 per month. Call today to check your eligibility.
                            </p>
                            <a href="tel:<?php echo ($_GET['type'] == 'medicare') ? $phonemin['typ'] : $phonemin[$did]; ?>" onclick="ga('send', 'event', 'Call Buttons', 'Click', '<?php echo ($_GET['type'] == 'medicare') ? 'medicare' : 'healthcare'; ?>', 'main');">
                                <div class="plan-highlight">$0/Month Possible</div>
                            </a>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="plan-card indemnity-plan">
                            <div class="plan-icon">
                                <!-- <i class="fas fa-shield-alt"></i> -->
                                <img src="../img/icon-doctor.png" alt="Fixed Indemnity Plans" class="plan-icon-img">
                                <h3 class="plan-title">Fixed Indemnity Plans</h3>
                            </div>
                            <p class="plan-description">
                                These are not major medical insurance and are not ACA compliant. They may include limited benefits or discount style coverage and can leave important gaps in care. Ask specifically about prescriptions, doctor visits, hospitalizations, and out of pocket exposure before enrolling.
                            </p>
                            <a href="tel:<?php echo ($_GET['type'] == 'medicare') ? $phonemin['typ'] : $phonemin[$did]; ?>" onclick="ga('send', 'event', 'Call Buttons', 'Click', '<?php echo ($_GET['type'] == 'medicare') ? 'medicare' : 'healthcare'; ?>', 'main');">
                                <div class="plan-highlight">Lower Costs</div>
                            </a>
                        </div>
                    </div>
                </div>

                <?php /* 
                    <div class="text-center mt-4">
                        <p class="call-prompt">Ready to find your perfect plan? <strong>Call now to speak with a licensed agent.</strong></p>
                    </div>
                */ ?>
            </div>
        </div>
    </div>
</section>

<section id="typ">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div id="typ-card" class="col-12 col-xl-8">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="insurance-card-content">
                                <div class="card-image-section">
                                    <img src="../img/agent-photo4.png" alt="Customer Service Representative" class="agent-image" style="filter: brightness(1.1)  saturate(0.9) drop-shadow(2px 4px 6px white)">
                                </div>

                                <div class="card-text-section pl-xl-5 pr-lx-3">
                                    <h1 class="card-title-custom">A 10 minute call could save you thousands every&nbsp;year.</h1>
                                    <h2 class="card-title-custom"><span style="font-weight: 900;">Call now</span> for your free&nbsp;quote.</h2>

                                    <a href="tel:<?php echo ($_GET['type'] == 'medicare') ? $phonemin['typ'] : $phonemin[$did]; ?>" onclick="ga('send', 'event', 'Call Buttons', 'Click', '<?php echo ($_GET['type'] == 'medicare') ? 'medicare' : 'healthcare'; ?>', 'main');" class="phone-button">
                                        <?php echo ($_GET['type'] == 'medicare') ? $phone['typ'] : $phone[$did]; ?>
                                    </a>

                                    <p class="card-description">
                                        Agents are standing by.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php /*
                <div class="row badges justify-content-center mt-4">
                    <div class="col-auto">
                        <img src="../img/trustedform-badge.webp" alt="Trusted Form" />
                    </div>
                    <div class="col-auto">
                        <img src="../img/trustpilot-badge.webp" alt="Trust Pilot" />
                    </div>
                    <div class="col-auto">
                        <img src="../img/ssl-badge.webp" alt="SSL Secured" />
                    </div>
                </div>
                */ ?>
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
                    <img src="../img/call-modal-bg.jpg" alt="" width="726px;">
                </a>
            </div>
        </div>
    </div>
</div>

<div class="disclaimer">
    <div class="container">
        <div class="row">
            <div class="col text-centern pt-3">
                <p style="color: #728c98; font-size: 70%; line-height: 1.3;">
                    <?php if (in_array($_SESSION['state_abbr'], ['NY', 'MA', 'CA', 'OH'])) { ?>

                        <?= $sitename ?> is owned and operated by Michael Whitney, a licensed insurance agent. Invitations for applications for insurance on Health-Insurance.com are made only where licensed and appointed. License information for all states can be found <a data-toggle="modal" data-target="#licensesModal" style="font-weight: bold; color: inherit !important; cursor: pointer;">here</a>. The appearance of logos related to various insurance providers is not an endorsement nor guarantee of product availability from those providers. Availability is dependent on various details such as geography, individual needs, agency appointments, and carrier relationships.

                    <?php } else { ?>

                        <?= $sitename ?> is privately owned and operated by HCI Compare LLC. Invitations for applications for insurance on Health-Insurance.com are made through HCI Compare LLC, a subsidiary of Affordable Healthcare, only where licensed and appointed. HCI Compare LLC licensing information can be found <a data-toggle="modal" data-target="#licensesModal" style="font-weight: bold; color: inherit !important; cursor: pointer;">here</a>. Submission of your information constitutes permission for an agent to contact you with additional information about the cost and coverage details of health plans. The appearance of logos related to various insurance providers is not an endorsement nor guarantee of product availability from those providers. Availability is dependent on various details such as geography, individual needs, agency appointments, and carrier relationships.

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

<?php include "inc/footer.php"; ?>
<script src="../js/jquery-3.2.1.min.js"></script>
<script src="../js/bootstrap.min.js"></script>

<style>
    /* Complete CSS Fix - All Styles */

    /* Reset and Base Styles */
    * {
        box-sizing: border-box;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        line-height: 1.6;
        color: #333;
        margin: 0;
        padding: 0;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
    }

    /* Navigation Styles */
    nav {
        background-color: #fafafa;
        padding: 15px 0;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        position: relative;
        z-index: 1000;
    }

    body nav .logo {
        max-height: 50px;
        width: auto;
    }

    .logo {
        max-width: 240px;
    }

    /* Success Section */
    #success {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 40px 0;
        margin-bottom: 40px;
    }

    .success {
        background: rgba(255, 255, 255, 0.15) !important;
        backdrop-filter: blur(10px) !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
        border-radius: 0 !important;
        padding: 0px 0px 30px !important;
        color: white !important;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2) !important;
        margin: 0 auto !important;
        max-width: 600px !important;
        overflow: visible !important;
    }

    .success h2 {
        color: white !important;
        font-size: 2rem !important;
        font-weight: 600 !important;
        margin-bottom: 0px !important;
        text-align: center !important;
        text-transform: capitalize !important;
        line-height: 1.2 !important;
        padding: 20px 0 15px;
    }

    /* Info List in Success Section */
    .info-list {
        text-align: left;
        max-width: 450px;
        margin: 0 auto;
    }

    .info-list p {
        margin: 12px 0 !important;
        font-size: 1.1rem !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        padding: 8px 15px !important;
        background: rgba(255, 255, 255, 0.1) !important;
        border-radius: 0 !important;
        color: white !important;
    }

    .info-list p strong {
        color: #fff !important;
        font-weight: 600 !important;
    }

    /* Fade Animation Elements - Use opacity instead of display:none */

    .fade1.fade-in,
    .fade2.fade-in,
    .fade3.fade-in,
    .fade4.fade-in,
    .fade5.fade-in {
        opacity: 1 !important;
        visibility: visible !important;
        transition: opacity 0.6s ease-in-out, visibility 0s linear 0s !important;
    }

    .check-fade1,
    .check-fade2,
    .check-fade3,
    .check-fade4,
    .check-fade5 {
        opacity: 0 !important;
        /* Start invisible, not display:none */
        visibility: hidden !important;
        /* Hidden but still takes space */
        color: #e4f5d7ff;
        text-shadow: 1px 1px 1px rgba(122, 193, 66, .5) !important;
        font-weight: bold !important;
        font-size: 1.3rem !important;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4) !important;
        transition: opacity 0.4s ease-in-out, visibility 0s linear 0.4s !important;
    }

    .check-fade1.fade-in,
    .check-fade2.fade-in,
    .check-fade3.fade-in,
    .check-fade4.fade-in,
    .check-fade5.fade-in {
        opacity: 1 !important;
        visibility: visible !important;
        transition: opacity 0.4s ease-in-out, visibility 0s linear 0s !important;
    }

    /* Checkmark Animation */
    .checkmark {
        animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
        border-radius: 50%;
        box-shadow: inset 0px 0px 0px #FFF;
        display: inline-block;
        height: 60px;
        margin-top: -13px;
        stroke: #FFF;
        stroke-miterlimit: 10;
        stroke-width: 4;
        width: 60px;
    }

    .checkmark__circle {
        animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        fill: #2fbdaa;
        stroke-dasharray: 166;
        stroke-dashoffset: 166;
        stroke-width: 4;
        stroke-miterlimit: 10;
        stroke: #2fbdaa;
    }

    .checkmark__check {
        animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
        stroke-dasharray: 48;
        stroke-dashoffset: 48;
        transform-origin: 50% 50%;
    }

    /* Main Section */
    #typ {
        padding: 0 !important;
        display: block !important;
        height: auto !important;
        align-items: stretch !important;
        flex-direction: column !important;
        justify-content: flex-start !important;
    }

    /* Card Styling */
    .card {
        background: white !important;
        border-radius: 0 !important;
        padding: 0 !important;
        margin: 20px auto 40px !important;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15) !important;
        border: none !important;
        max-width: 900px !important;
    }

    .card-body {
        padding: 0;
        overflow: hidden;
        position: relative;
    }

    .card-title {
        font-size: 1.8rem !important;
        color: #143F84 !important;
        font-weight: 700 !important;
        margin-bottom: 30px !important;
        text-align: center !important;
    }

    /* Insurance Card Content */
    .insurance-card-content {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        margin-bottom: 0px !important;
        padding: 0px !important;
        position: relative;
    }

    .insurance-card-content:before {
        background-image: url(../img/mark.webp);
        background-size: contain;
        background-position-x: 0;
        background-position-y: 0;
        background-repeat: no-repeat;
        content: '';
        width: 100%;
        height: 100%;
        position: absolute;
        opacity: 0.3;
        transform: rotate(15deg);
        margin-left: -60px;
        margin-top: -30px;
        z-index: 0;
    }

    .card-image-section {
        flex: 0 0 auto !important;
        margin-right: 30px !important;
        position: relative;
    }

    .agent-image {
        object-fit: cover !important;
        border-bottom-left-radius: 0px;
    }

    .card-text-section {
        flex: 1 !important;
        position: relative;
        text-align: left !important;
    }

    .card-title-custom {
        color: #1a4d7d !important;
        font-size: 2rem !important;
        font-weight: bold !important;
        margin-bottom: 25px !important;
        line-height: 1.2 !important;
    }

    h2.card-title-custom {
        font-size: 1.5rem !important;
        opacity: .8;
    }

    /* Phone Button in Card */
    .phone-button {
        background: linear-gradient(135deg, #FF6600 0%, #e55a00 100%) !important;
        color: white !important;
        padding: 18px 30px !important;
        border-radius: 0 !important;
        text-decoration: none !important;
        font-size: 1.4rem !important;
        font-weight: 700 !important;
        display: inline-block !important;
        margin: 20px 0 !important;
        box-shadow: 0 8px 20px rgba(255, 102, 0, 0.3) !important;
        transition: all 0.3s ease !important;
        border: none !important;
    }

    .phone-button:hover {
        background: linear-gradient(135deg, #e55a00 0%, #cc4f00 100%) !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 12px 25px rgba(255, 102, 0, 0.4) !important;
        color: white !important;
        text-decoration: none !important;
    }

    .phone-button i {
        margin-right: 8px !important;
        font-size: 1.2rem !important;
    }

    .card-description {
        font-size: 1.1rem !important;
        color: #6c757d !important;
        margin: 15px 0 0 0 !important;
        line-height: 1.4 !important;
    }

    /* Bottom Section */
    .bottom-section {
        padding: 30px 0 !important;
        border-top: 1px solid #e9ecef !important;
    }

    .user-info {
        font-size: 1.1rem !important;
        color: #6c757d !important;
        margin-bottom: 25px !important;
    }

    .main-headline {
        color: #143F84 !important;
        font-weight: 800 !important;
        font-size: 2.2rem !important;
        line-height: 1.3 !important;
        text-transform: uppercase !important;
        margin: 25px 0 !important;
    }

    h1 {
        color: #143F84 !important;
        font-weight: 800 !important;
        font-size: 2.2rem !important;
        line-height: 1.3 !important;
    }

    .sub-head {
        font-size: 1.4rem !important;
        margin-bottom: 30px !important;
        color: #495057 !important;
    }

    p.sub-head {
        font-size: 1.4rem !important;
    }

    .green {
        color: #2fbdaa !important;
    }

    /* Primary CTA Button */
    .call-btn,
    .call-btn.primary-cta {
        background: linear-gradient(135deg, #FF6600 0%, #e55a00 100%) !important;
        border: 3px solid #FF6600 !important;
        border-radius: 0 !important;
        color: white !important;
        display: inline-block !important;
        font-size: 1.4rem !important;
        font-weight: 700 !important;
        text-decoration: none !important;
        padding: 20px 45px !important;
        margin: 20px 0 !important;
        transition: all 0.3s ease !important;
        box-shadow: 0 10px 25px rgba(255, 102, 0, 0.3) !important;
        min-width: 320px !important;
        text-align: center !important;
        line-height: 1.2 !important;
        position: relative !important;
        width: auto !important;
        max-width: 100% !important;
    }

    .call-btn:hover,
    .call-btn.primary-cta:hover {
        background: linear-gradient(135deg, #e55a00 0%, #cc4f00 100%) !important;
        border-color: #e55a00 !important;
        transform: translateY(-3px) !important;
        box-shadow: 0 15px 30px rgba(255, 102, 0, 0.4) !important;
        color: white !important;
        text-decoration: none !important;
    }

    .call-btn span,
    .call-btn.primary-cta span {
        font-size: 1rem !important;
        text-transform: uppercase !important;
        letter-spacing: 1px !important;
        display: block !important;
        margin-bottom: 5px !important;
        font-weight: 600 !important;
    }

    .call-btn .icon-phone {
        vertical-align: middle !important;
        margin-left: 10px !important;
    }

    #aff {
        padding: 0em 1em 0em;
    }

    #aff svg {
        width: 100%;
        height: 30px;
        filter: grayscale(1) brightness(10) opacity(0.7) drop-shadow(2px 4px 6px rgba(0, 0, 0, -0.3));
    }

    #aff img {
        position: relative;
        width: 100%;
        height: auto;
        padding: 0 10px;
    }

    @media (max-width: 1025px) {
        #aff .col {
            min-width: 26%;
            padding: 5px 20px;
        }

        #aff svg#carrierI {
            padding: 7px 0 !important;
        }
    }

    @media (min-width: 1026px) {
        #aff {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            /* padding: 1.5em 0; */
            -webkit-box-pack: justify;
            -ms-flex-pack: justify;
            justify-content: space-between;
        }

        #aff img {
            max-height: calc(25px + 2vw);
            width: auto;
        }
    }

    @media (min-width: 1026px) and (min-width: 1200px) {
        #aff img {
            max-height: 44px;
        }
    }

    /* Badges */
    .badges {
        margin-top: 20px !important;
        text-align: center !important;
    }

    .badges img {
        height: 40px !important;
        margin: 0px 15px 40px !important;
        opacity: 0.7 !important;
        filter: grayscale(.9) opacity(0.6) !important;
        transition: all 0.3s ease !important;
    }

    .badges img:hover {
        opacity: 1 !important;
        filter: grayscale(0) !important;
        transform: translateY(-2px) !important;
    }

    .wiggle {
        animation: wiggle 5s infinite !important;
    }

    .full-image {
        height: 100%;
        object-fit: cover;
        object-position: 21% 100%;
    }

    /* Updated Responsive Design - Changed from 768px/992px to 1025px */
    @media (max-width: 1025px) {
        .insurance-card-content {
            flex-direction: row !important;
            /* Keep horizontal layout */
            text-align: left !important;
            padding: 0px !important;
            align-items: flex-end !important;
            /* Align items to top */
        }

        .card-image-section {
            margin-right: 20px !important;
            /* Space between image and text */
            margin-bottom: 0 !important;
            flex-shrink: 0 !important;
            /* Prevent image from shrinking */
        }

        .card-title-custom {
            color: #0abcea !important;
            font-size: 1.1rem !important;
            margin-bottom: 10px !important;
            line-height: 1.2 !important;
            -webkit-text-stroke: 2px #FFF;
            paint-order: stroke fill;
            text-transform: capitalize;
        }

        h2.card-title-custom {
            color: #1a4d7d !important;
            font-size: 0.95rem !important;
            margin-bottom: 15px !important;
            opacity: 0.8 !important;
        }

        .card-text-section {
            text-align: left !important;
            flex: 1 !important;
            padding-right: 20px;
            padding-bottom: 20px;
        }

        /* Make phone button full width of text section */
        .phone-button {
            width: 100% !important;
            text-align: center !important;
            font-size: 1.2rem !important;
            padding: 15px 20px !important;
        }

        /* Adjust description text */
        .card-description {
            font-size: 1rem !important;
            text-align: center !important;
        }
    }

    /* Even smaller screens */
    @media (max-width: 576px) {
        .insurance-card-content {
            padding: 0px !important;
        }

        .agent-image {
            width: 190px !important;
            height: 100% !important;
            position: relative;
            bottom: 0;
        }

        .card-image-section {
            margin-right: -15px !important;
            margin-left: -15%;
            bottom: 0;
            position: absolute;
            height: 100%;
        }

        .card-text-section {
            text-align: left !important;
            flex: 1 !important;
            padding-right: 0px;
            padding-bottom: 20px;
        }

        .phone-button {
            font-size: 2rem !important;
            padding: 0px 5px 4px !important;
        }

        .card-title-custom {
            text-align: center;
            font-size: 1rem !important;
            text-shadow: 1px 1px 5px rgba(255, 255, 255, 1), -1px -1px 5px rgba(255, 255, 255, 1);
            margin-left: 109px;
            padding-right: 20px;
        }

        h2.card-title-custom {
            font-size: 0.95rem !important;
            margin-bottom: 15px !important;
            opacity: 0.8 !important;
            text-shadow: 1px 1px 5px rgba(255, 255, 255, 1), -1px -1px 5px rgba(255, 255, 255, 1);
        }

        .card-description {
            color: #24b86e !important;
            font-size: 0.95rem !important;
            font-weight: 600;
            margin: -12px 0 0 0 !important;
            text-shadow: 1px 1px 4px rgba(255, 255, 255, 1), -1px -1px 4px rgba(255, 255, 255, 1);
            margin-left: 125px !important;
        }

        .col-auto {
            padding: 0 !important;
        }

        .badges {
            justify-content: space-evenly !important;
        }

        .badges img {
            height: 25px !important;
            margin: 0px 4px 40px !important;
        }
    }

    /* Animation Keyframes */
    @keyframes stroke {
        100% {
            stroke-dashoffset: 0;
        }
    }

    @keyframes scale {

        0%,
        100% {
            transform: none;
        }

        50% {
            transform: scale3d(1.1, 1.1, 1);
        }
    }

    @keyframes fill {
        100% {
            box-shadow: inset 0 0 0 30px #7ac142;
        }
    }

    @keyframes wiggle {
        0% {
            transform: rotate(0deg);
        }

        75% {
            transform: rotate(0deg);
        }

        80% {
            transform: rotate(5deg);
        }

        85% {
            transform: rotate(-5deg);
        }

        90% {
            transform: rotate(5deg);
        }

        95% {
            transform: rotate(-5deg);
        }

        100% {
            transform: rotate(0deg);
        }
    }

    @keyframes shrink {
        15% {
            transform: scale(1.2);
        }

        100% {
            transform: scale(0);
        }
    }

    /* Utility Classes */
    .mt-3 {
        margin-top: 1rem !important;
    }

    .mt-4 {
        margin-top: 1.5rem !important;
    }

    .mb-4 {
        margin-bottom: 1.5rem !important;
    }

    .my-3 {
        margin-top: 1rem !important;
        margin-bottom: 1rem !important;
    }

    .bolder {
        font-weight: 800 !important;
    }

    .d-flex {
        display: flex !important;
    }

    .align-items-center {
        align-items: center !important;
    }

    /* Success section container preparation */
    #success {
        overflow: hidden !important;
        transition: all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) !important;
    }

    /* Success box preparation */
    .success {
        transform: translateY(0) !important;
        transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94),
            opacity 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) !important;
    }

    /* Smooth slide-up animation - single transform only */
    .success.slide-up-disappear {
        transform: translateY(-100%) !important;
        opacity: 0 !important;
    }

    /* Hide entire success section after animation completes */
    #success.section-hidden {
        max-height: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        overflow: hidden !important;
        transition: max-height 0.3s ease-out 0s,
            padding 0.3s ease-out 0s,
            margin 0.3s ease-out 0s !important;
    }

    /* Smooth transition for content below */
    #typ {
        transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) !important;
    }

    /* Optional: Subtle upward movement of main content */
    #success.section-hidden~#typ {
        transform: translateY(40px) !important;
    }

    .disclaimer {
        margin-top: 113px;
    }

    body footer {
        background-color: #67cdcb;
        color: #fff;
    }

    body footer a:not(:first-child) {
        border-left: 1px solid rgba(255, 255, 255, 0.25);
        padding-left: 10px;
        margin-left: 10px;
    }

    /* NEW: Hide health-plans section initially */
    #health-plans {
        display: none;
        /* Hidden on page load */
    }

    /* NEW: Desktop-only content replacement setup */
    @media (min-width: 1026px) {
        #success {
            /* min-height: 400px; */
            min-height: 290px;
            /* Maintain height during transition */
            position: relative;

        }

        .success-content-wrapper {
            position: relative;
            opacity: 1;
            transition: opacity 0.6s ease;
        }

        .success-content-wrapper.fade-out {
            opacity: 0;
        }

        .plans-content-wrapper {
            opacity: 0;
            transition: opacity 0.6s ease;
        }

        .plans-content-wrapper.fade-in {
            opacity: 1;
        }
    }

    /* Mobile: Keep original behavior */
    @media (max-width: 1025px) {
        #health-plans {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 50px 0 30px;
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            overflow: hidden;
            position: absolute;
        }

        #health-plans.slide-in {
            display: block !important;
            opacity: 1;
            position: relative;
            transform: translateY(0);
        }
    }

    /* Health Plans Section */
    .plans-intro {
        margin-bottom: 40px;
    }

    .plans-title {
        color: white !important;
        font-size: 2.2rem !important;
        font-weight: 700 !important;
        margin-bottom: 15px !important;
        text-align: center !important;
        text-shadow: 2px 2px 3px rgba(0, 0, 0, 0.4);
    }

    .plans-subtitle {
        color: rgba(255, 255, 255, 0.9) !important;
        font-size: 1.2rem !important;
        margin-bottom: 0 !important;
        text-align: center !important;
    }

    /* Plan Cards */
    .plan-card {
        background: rgba(255, 255, 255, 0.95) !important;
        border-radius: 0px !important;
        padding: 30px 25px !important;
        text-align: center !important;
        height: 100% !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2) !important;
        transition: all 0.3s ease !important;
        position: relative !important;
        overflow: hidden !important;
        backdrop-filter: blur(10px) !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        gap: 18px;
    }

    .plan-card:hover {
        transform: translateY(-5px) !important;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25) !important;
    }

    .plan-icon {
        font-size: 3rem !important;
        margin-bottom: 0 !important;
        color: #1a4d7d !important;

        display: flex;
        align-items: flex-start;
        gap: 18px;
    }

    .plan-icon-img {
        width: 56px;
        max-width: 56px;
        flex: 0 0 56px;
        margin-top: 2px;
    }
    @media (min-width: 1600) {
        .plan-icon-img {
            position: absolute;
        }
    }

    .plan-title {
        color: #1a4d7d !important;
        font-size: 1.25rem !important;
        font-weight: 700 !important;
        line-height: 1.3 !important;
        margin: 0 !important;

        flex: 1;
    }

    .plan-description {
        color: #495057 !important;
        font-size: 1rem !important;
        line-height: 1.6 !important;
        margin: 0 !important;
        text-align: left !important;
    }

    .plan-card a {
        margin-top: auto;
        align-self: center;
    }

    .plan-highlight {
        background: linear-gradient(135deg, #FF6600 0%, #e55a00 100%) !important;
        color: white !important;
        padding: 8px 20px !important;
        border-radius: 20px !important;
        font-size: 0.9rem !important;
        font-weight: 600 !important;
        display: inline-block !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
    }

    /* Special styling for different plan types */
    .comprehensive-plan .plan-icon {
        color: #28a745 !important;
    }

    .indemnity-plan .plan-icon {
        color: #17a2b8 !important;
    }

    .aca-plan .plan-icon {
        color: #dc3545 !important;
    }

    .comprehensive-plan .plan-highlight {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
    }

    .indemnity-plan .plan-highlight {
        background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%) !important;
    }

    .aca-plan .plan-highlight {
        background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%) !important;
    }

    /* Call prompt at bottom */
    .call-prompt {
        color: white !important;
        font-size: 1.3rem !important;
        margin: 30px 0 0 0 !important;
        text-align: center !important;
    }

    .call-prompt strong {
        color: #FFD700 !important;
        font-weight: 700 !important;
    }

    /* Updated Health Plans Responsive Design - Changed from 768px to 1025px */
    @media (max-width: 1025px) {
        #health-plans {
            padding: 0 0 50px;
        }

        .plans-title {
            font-size: 1.8rem !important;
            line-height: 1.4;
        }

        .plans-subtitle {
            font-size: 1.1rem !important;
        }

        .plan-card {
            padding: 25px 20px !important;
            gap: 14px;
        }

        .plan-icon {
            font-size: 2.5rem !important;
            gap: 14px;
        }

        .plan-title {
            font-size: 1.2rem !important;
            margin-bottom: 0px !important;
        }

        .plan-description {
            font-size: 0.95rem !important;
            text-align: center !important;
        }

        .call-prompt {
            font-size: 1.1rem !important;
            margin-top: 20px !important;
        }
    }

    @media (max-width: 576px) {
        .plans-title {
            font-size: 1.2rem !important;
        }

        .plan-card {
            padding: 20px 15px !important;
            gap: 12px;
        }

        .plan-icon {
            font-size: 2rem !important;
            gap: 12px;
        }

        .plan-icon-img {
            width: 44px;
            max-width: 44px;
            flex-basis: 44px;
        }
    }

    /* Updated Mobile Section Reordering - Changed from 768px to 1025px */
    @media (max-width: 1025px) {

        /* Only reorder the main sections, not footer/disclaimer */
        #success {
            order: 1;
        }

        #typ {
            order: 2 !important;
        }

        #health-plans {
            order: 3 !important;
            position: relative !important;
        }

        /* Disclaimer and footer should stay at bottom */
        .disclaimer {
            order: 99 !important;
            margin-top: 40px;
        }

        footer {
            order: 100 !important;
        }

        /* Create flex container ONLY for main content sections */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Ensure nav stays at top */
        nav {
            order: 0 !important;
            position: fixed !important;
            top: 0 !important;
            width: 100% !important;
            z-index: 1000 !important;
        }

        /* Adjust body padding to account for fixed nav */
        body {
            padding-top: 45px !important;
        }

        /* Margins for better mobile spacing */
        #typ {
            margin-bottom: 40px;
        }

        #health-plans {
            margin-top: 0;
        }
    }

    /* Updated reset for desktop - Changed from 769px to 1026px */
    @media (min-width: 1026px) {
        body {
            display: block !important;
        }

        /* Reset all orders on desktop */
        #success,
        #health-plans,
        #typ,
        .disclaimer,
        footer {
            order: initial !important;
        }
    }

    /* Additional mobile reordering support */
    body.mobile-reorder {
        display: flex;
        flex-direction: column;
    }

    body.mobile-reorder #typ {
        order: 3 !important;
    }

    body.mobile-reorder #health-plans {
        order: 4 !important;
    }

    /* Updated animation check sizes - Changed from 768px to 1025px */
    @media (max-width: 1025px) {

        .check-fade1.fade-in,
        .check-fade2.fade-in,
        .check-fade3.fade-in,
        .check-fade4.fade-in,
        .check-fade5.fade-in {
            font-size: 1.1rem !important;
        }
    }
</style>

<!-- UPDATED JAVASCRIPT SECTION -->
<script>
    $(document).ready(function() {
        console.log('Animation script loaded - jQuery version:', $.fn.jquery);

        // Track phone number clicks
        document.addEventListener('click', (e) => {
            if (e.target.closest('a[href*="tel:"]')) {
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'phone_number_click');
                }
            }
        });

        // Handle responsive display (if you have d-xl elements)
        if (window.screen.width < 1200) {
            $('.d-xl-block').remove();
            console.log('Removed d-xl-block elements for mobile');
        } else {
            $('.d-xl-none').remove();
            console.log('Removed d-xl-none elements for desktop');
        }

        // Debug: Check if elements exist
        console.log('Fade elements found:');
        console.log('fade1:', $('.fade1').length);
        console.log('fade2:', $('.fade2').length);
        console.log('fade3:', $('.fade3').length);
        console.log('fade4:', $('.fade4').length);
        console.log('fade5:', $('.fade5').length);

        console.log('Check elements found:');
        console.log('check-fade1:', $('.check-fade1').length);
        console.log('check-fade2:', $('.check-fade2').length);
        console.log('check-fade3:', $('.check-fade3').length);
        console.log('check-fade4:', $('.check-fade4').length);
        console.log('check-fade5:', $('.check-fade5').length);

        // Elements are already hidden by CSS, no need to hide them again
        console.log('Elements are hidden by CSS opacity');

        // Wait for checkmark animation to complete (1.2 seconds)
        setTimeout(function() {
            console.log('Starting fade sequence...');

            // Use CSS class-based animations instead of jQuery fadeIn
            function animateElement(selector, callback) {
                $(selector).addClass('fade-in');
                setTimeout(callback, 600); // Wait for animation to complete
            }

            function animateCheck(selector, callback) {
                $(selector).addClass('fade-in');
                setTimeout(callback, 400); // Wait for animation to complete
            }

            // Sequential animation with CSS classes
            animateElement('.fade1', function() {
                console.log('✓ Name shown');
                setTimeout(function() {
                    animateCheck('.check-fade1', function() {
                        console.log('✓ Name check shown');

                        animateElement('.fade2', function() {
                            console.log('✓ Age shown');
                            setTimeout(function() {
                                animateCheck('.check-fade2', function() {
                                    console.log('✓ Age check shown');

                                    animateElement('.fade3', function() {
                                        console.log('✓ City shown');
                                        setTimeout(function() {
                                            animateCheck('.check-fade3', function() {
                                                console.log('✓ City check shown');

                                                animateElement('.fade4', function() {
                                                    console.log('✓ State shown');
                                                    setTimeout(function() {
                                                        animateCheck('.check-fade4', function() {
                                                            console.log('✓ State check shown');

                                                            animateElement('.fade5', function() {
                                                                console.log('✓ Application shown');
                                                                setTimeout(function() {
                                                                    animateCheck('.check-fade5', function() {
                                                                        console.log('✓ Application check shown');
                                                                        console.log('🎉 All animations complete!');

                                                                        // NEW: Updated completion handler for desktop vs mobile
                                                                        setTimeout(function() {
                                                                            var isDesktop = window.innerWidth > 1025;

                                                                            if (isDesktop) {
                                                                                // DESKTOP: Replace content instead of sliding
                                                                                console.log('Desktop view - replacing content');

                                                                                // Fade out success content
                                                                                $('.success-content-wrapper').addClass('fade-out');

                                                                                setTimeout(function() {
                                                                                    // Clone health-plans content
                                                                                    var healthPlansContent = $('#health-plans .container-fluid').clone();

                                                                                    // Clear success section content and add health plans content
                                                                                    $('#success').html(
                                                                                        '<div class="plans-content-wrapper">' +
                                                                                        '<div class="container-fluid">' +
                                                                                        healthPlansContent.html() +
                                                                                        '</div></div>'
                                                                                    );

                                                                                    // Keep the gradient background
                                                                                    $('#success').css({
                                                                                        'background': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                                                                                        'padding': '50px 0 30px'
                                                                                    });

                                                                                    // Fade in new content
                                                                                    setTimeout(function() {
                                                                                        $('.plans-content-wrapper').addClass('fade-in');

                                                                                        // Add wiggle to plan buttons
                                                                                        setTimeout(function() {
                                                                                            $('.plan-highlight').parent().addClass('wiggle-attention');
                                                                                            $('#typ .phone-button').addClass('wiggle-attention');
                                                                                            console.log('Added wiggle to buttons');
                                                                                        }, 600);
                                                                                    }, 100);

                                                                                    // Remove the original health-plans section
                                                                                    $('#health-plans').remove();

                                                                                }, 600); // Wait for fade out

                                                                            } else {
                                                                                // MOBILE/TABLET: Keep original sliding behavior
                                                                                console.log('Mobile/Tablet view - using slide animation');

                                                                                // Original mobile behavior
                                                                                $('.success').addClass('slide-up-disappear');

                                                                                setTimeout(function() {
                                                                                    $('#success').addClass('section-hidden');
                                                                                    console.log('Success section collapsed');

                                                                                    // Show health plans with slide-in
                                                                                    setTimeout(function() {
                                                                                        $('#health-plans').css('display', 'block').addClass('slide-in');
                                                                                        console.log('Health plans section revealed');

                                                                                        // Add attention animations
                                                                                        setTimeout(function() {
                                                                                            $('#typ .phone-button').addClass('wiggle-attention');
                                                                                            setTimeout(function() {
                                                                                                $('#health-plans .plan-highlight').parent().addClass('wiggle-attention');
                                                                                            }, 1000);
                                                                                        }, 600);
                                                                                    }, 300);
                                                                                }, 600);
                                                                            }
                                                                        }, 1000); // Wait 1 second after last fade item
                                                                    });
                                                                }, 100);
                                                            });
                                                        });
                                                    }, 100);
                                                });
                                            });
                                        }, 100);
                                    });
                                });
                            }, 100);
                        });
                    });
                }, 100);
            });
        }, 1200); // Wait for checkmark animation

        // Add enhanced animation styles
        $('<style>').prop('type', 'text/css').html(`
        /* Animation completion effects */
        .animation-complete {
            animation: successGlow 1.2s ease-in-out;
        }
        
        @keyframes successGlow {
            0%, 100% { 
                transform: scale(1);
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            }
            50% { 
                transform: scale(1.015);
                box-shadow: 0 20px 45px rgba(124, 193, 66, 0.3);
            }
        }
        
        /* Attention-grabbing wiggle for call buttons */
        .wiggle-attention {
            animation: callAttention 4s ease-in-out infinite;
        }
        
        @keyframes callAttention {
            0%, 85%, 100% { 
                transform: rotate(0deg) scale(1); 
            }
            87% { 
                transform: rotate(-1deg) scale(1.01); 
            }
            89% { 
                transform: rotate(1deg) scale(1.01); 
            }
            91% { 
                transform: rotate(-1deg) scale(1.01); 
            }
            93% { 
                transform: rotate(1deg) scale(1.01); 
            }
            95% { 
                transform: rotate(0deg) scale(1); 
            }
        }

        /* Pulse effect for phone buttons */
        .phone-button:hover, .call-btn.primary-cta:hover {
            animation: none !important; /* Stop wiggle on hover */
        }
    `).appendTo('head');

        console.log('Animation styles added');

        // Optional: Add click tracking for debugging
        $('.call-btn, .phone-button').on('click', function() {
            console.log('Call button clicked:', $(this).attr('href'));
        });

        // Add loading complete indicator
        setTimeout(function() {
            $('body').addClass('animations-loaded');
            console.log('Page animations fully loaded');
        }, 100);
    });

    // Handle section order on resize
    $(window).on('resize', function() {
        var isMobile = window.innerWidth <= 1025;

        if (isMobile) {
            // Ensure proper order on tablet/mobile
            $('body').addClass('mobile-reorder');
        } else {
            // Remove mobile reordering class
            $('body').removeClass('mobile-reorder');
        }
    }).trigger('resize'); // Trigger on load

    $(document).ready(function() {
        // Get the phone number from the page (already rendered by PHP)
        const phoneNumber = $('.phone-button').first().text().trim() ||
            $('a[href^="tel:"]').first().text().trim();
        const phoneLink = $('a[href^="tel:"]').first().attr('href');

        // Store original logo HTML
        const originalLogo = $('.logo').parent().html();

        // Create phone number element
        const phoneHTML = `
        <a href="${phoneLink}" class="nav-phone-number">
            <span>${phoneNumber}</span>
        </a>
    `;

        let logoReplaced = false;
        let lastScrollTop = 0;
        const scrollThreshold = 100; // Pixels to scroll before triggering

        // Check if tablet/mobile (changed from 768px to 1025px)
        function isMobile() {
            return window.innerWidth <= 1025;
        }

        // Handle scroll event
        function handleScroll() {
            if (!isMobile()) {
                // If not tablet/mobile and logo was replaced, restore it
                if (logoReplaced) {
                    $('.logo').parent().html(originalLogo);
                    logoReplaced = false;
                }
                return;
            }

            const currentScroll = $(window).scrollTop();

            // Replace logo when scrolling down past threshold
            if (currentScroll > scrollThreshold && !logoReplaced) {
                $('.logo').parent().fadeOut(200, function() {
                    $(this).html(phoneHTML).fadeIn(200);
                    logoReplaced = true;
                });
            }
            // Restore logo when scrolling back to top
            else if (currentScroll <= 20 && logoReplaced) {
                $('.nav-phone-number').parent().fadeOut(200, function() {
                    $(this).html(originalLogo).fadeIn(200);
                    logoReplaced = false;
                });
            }

            lastScrollTop = currentScroll;
        }

        // Throttle scroll event for performance
        let scrollTimer;
        $(window).on('scroll', function() {
            if (scrollTimer) {
                clearTimeout(scrollTimer);
            }
            scrollTimer = setTimeout(handleScroll, 10);
        });

        // Handle window resize
        $(window).on('resize', function() {
            handleScroll();
        });

        // Add CSS for the phone number in nav
        $('<style>').prop('type', 'text/css').html(`
        .nav-phone-number {
            display: inline-flex;
            align-items: center;
            color: #143F84 !important;
            font-size: 1.1rem !important;
            font-weight: 700 !important;
            text-decoration: none !important;
            background: linear-gradient(135deg, #FF6600 0%, #e55a00 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            transition: all 0.3s ease;
        }
        
        .nav-phone-number:hover {
            transform: scale(1.05);
            text-decoration: none !important;
        }
        
        .nav-phone-number i {
            font-size: 2rem;
            -webkit-text-fill-color: #FF6600;
        }
        
        /* Pulse animation for attention */
        .nav-phone-number {
            animation: phonePulse 2s ease-in-out infinite;
        }
        
        @keyframes phonePulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.02);
            }
        }
        
        /* Ensure proper alignment in nav */
        @media (max-width: 1025px) {
            nav .container .row {
                min-height: 50px;
            }
            
            .nav-phone-number {
                font-size: 2rem !important;
                white-space: nowrap;
            }
        }
        
        @media (max-width: 480px) {
            .nav-phone-number {
                font-size: 1.7rem !important;
            }
            
            .nav-phone-number i {
                margin-right: 5px;
            }
        }
    `).appendTo('head');

        // Initial check
        handleScroll();

        // Optional: Add click tracking
        $(document).on('click', '.nav-phone-number', function() {
            if (typeof gtag !== 'undefined') {
                gtag('event', 'nav_phone_click', {
                    'event_category': 'Phone Clicks',
                    'event_label': 'Navigation Bar Mobile'
                });
            }
            console.log('Navigation phone number clicked');
        });
    });
</script>

<!-- Rest of the tracking code remains the same -->
<?php
$statesAccepted = array('AK', 'AL', 'AZ', 'AR', 'CA', 'DE', 'GA', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'MI', 'MS', 'MO', 'NV', 'NM', 'OH', 'PA', 'SC', 'SD', 'TN', 'TX', 'VA', 'WV', 'WI', 'WY');

if ($_GET['src'] === 'Magenta') {
    $sid = '7090';
} else if ($_GET['src'] === 'Magenta2') {
    $sid = '7088';
}
if (in_array($state, $statesAccepted)) {
    $trackingString = 'https://www.pirolane.com/rd/ipx.php?hid=' . $_SESSION['HIT_ID'] . '&sid=' . $sid . '&transid='; ?>
    <input id="tracking-string" type="hidden" value="<?= $trackingString ?>">
    <script>
        var trackingString = $('#tracking-string').val();
        var trackingStringJS = trackingString + sessionStorage.getItem('userPhone');
        if (sessionStorage.getItem('entryStatus') == 'success') {
            document.write('<iframe width="1" height="1" frameborder="0" src="' + trackingStringJS + '"></iframe>');
        }
    </script>
<?php } ?>

<?php /* DMS Tracking Pixel */
$dmsStatesAccepted = array('AL', 'AR', 'AZ', 'CO', 'DE', 'FL', 'GA', 'IA', 'ID', 'IL', 'IN', 'KS', 'KY', 'LA', 'MA', 'MI', 'MN', 'MO', 'MS', 'MT', 'NC', 'ND', 'NE', 'NH', 'NJ', 'NM', 'OH', 'OK', 'SC', 'TN', 'TX', 'UT', 'VA', 'WI', 'WV', 'WY');
if (in_array($state, $dmsStatesAccepted)) { ?>
    <script>
        if (sessionStorage.getItem('dms_lead') == 'true') {
            var dms_trackingString = 'https://eng.trkcnv.com/pixel?cid=' + sessionStorage.getItem('cid') + '&refid=' + sessionStorage.getItem('userPhone') + '&clickid=' + sessionStorage.getItem('gclid');
            document.write('<iframe width="1" height="1" frameborder="0" src="' + dms_trackingString + '"></iframe>');
        }
    </script>
<?php } ?>

<script type="text/javascript">
    var hitComplete = true;
</script>

<script type="text/javascript">
    $('a[data-target="#myModal"]').click(function() {
        var phone = $(this).data('phone');
        $('#myModal .phone-number').text(phone);
        $('#myModal a').prop('href', 'tel:' + phone);
    });
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

<script>
    fbq('track', 'Contact');
</script>

    <script type="text/javascript" src="//cdn.callrail.com/companies/631399289/2dc49fb8bb1148ee6b02/12/swap.js"></script>

<script>
    dataLayer.push({
        'event': 'EC_FormSubmit',
        'enhanced_conversion_data': {
            "email": '<?= $user_email ?>',
        }
    })
</script>

</body>

</html>