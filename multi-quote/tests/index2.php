<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<?php include 'inc/globalvars.php'; ?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1,user-scalable=no">
    <title><?php if (isset($title) && !empty($title)) {
                echo $title . ' | ';
            }
            echo $sitename; ?></title>
    <!-- Google Tag Manager and Analytics scripts here... -->
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css"
        integrity="sha512-yHknP1/AwR+yx26cB1y0cjvQUMvEa2PFzt1c9LlS4pRQ5NOTZFWbhBig+X9G9eYW/8m0/4OXNx8pxJ6z57x0dw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css"
        integrity="sha512-17EgCFERpgZKcm0j0fEq1YCJuyAWdz9KUtv1EjVuaOz8pDnh/0nZxmU6BBXwaaxqoi9PQXnRWqlcDB027hgv9A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="../fa5/web-fonts-with-css/css/fontawesome-all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;600;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="apple-touch-icon" href="./img/favicon.png">
    <link rel="icon" href="./img/favicon.png" type="image/x-icon">
    <script defer src="/fa5/svg-with-js/js/fontawesome-all.min.js"></script>
    <!-- Additional tracking scripts (Bing, TrustedForm, etc.) -->
</head>

<body>
    <!-- Google Tag Manager (noscript) and Navigation -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MJMNPM5" height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>
    <nav class="d-flex align-items-center">
        <div class="container h-100" style="color: #333;">
            <div class="row align-items-center h-100">
                <div class="col-lg-4 col-xl-5 text-center text-sm-left">
                    <img class="logo" src="./img/logo.svg" alt="<?php echo $sitename; ?> logo">
                </div>
                <!-- Optional call-now block commented out -->
            </div>
        </div>
    </nav>
    <div id="banner" style="display: block; position: relative; margin: 0 0 -1px; padding: 10px 15px; background: #124085; font-family: Montserrat,Helvetica,Arial,sans-serif; color: #fff; text-align: center; font-size: 18px;">
        <span>Healthplans Available - <span style="font-weight: 600; color: #fff;">Shop&nbsp;Now!</span></span>
    </div>

    <?php // Optionally include shared non-form elements (header, navigation, etc.)
    include 'inc/form/non-questions.php';
    ?>

    <!-- Multi-Step Form (Single Page) -->
    <form id="msform" novalidate>
        <!-- Progress Bar -->
        <ul id="progressbar">
            <li class="active"></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
        </ul>

        <!-- STEP 1: Zip Code -->
        <fieldset aria-headline="What is your Zip Code?">
            <div class="form-card text-center">
                <h3 class="fs-title">What is your Zip Code?</h3>
                <input id="zip" name="zip" type="tel" class="form-control" placeholder="12345" autocomplete="postal-code" required
                    value="<?php echo isset($_GET['zip']) ? htmlspecialchars($_GET['zip']) : ''; ?>">
                <button type="button" class="next btn action-button">Continue</button>
            </div>
        </fieldset>

        <!-- STEP 2: Household Size -->
        <fieldset aria-headline="How many people are you insuring?">
            <div class="form-card text-center">
                <h3 class="fs-title">How many people are you insuring?</h3>
                <div class="radio-group">
                    <input type="hidden" name="Household" id="household" class="form-control" />
                    <div class="radio btn" data-value="1" tabindex="1">1 person</div>
                    <div class="radio btn" data-value="2" tabindex="2">2 people</div>
                    <div class="radio btn" data-value="3" tabindex="3">Family (3 or more)</div>
                    <?php /* <button type="button" class="next btn action-button">Continue</button> */ ?>
                </div>
            </div>
        </fieldset>

        <!-- STEP 3: Date of Birth -->
        <fieldset aria-headline="What is your date of birth?">
            <div class="form-card">
                <h3 class="fs-title">What is your date of birth?</h3>
                <input id="dob" name="DOB" type="hidden" autocomplete="bday" maxlength="10" minlength="10">
                <div class="row">
                    <div class="form-group col-4">
                        <select name="birthmonth" id="birthmonth" required class="form-control">
                            <option disabled selected value="">MONTH</option>
                            <option value="01">01</option>
                            <option value="02">02</option>
                            <option value="03">03</option>
                            <option value="04">04</option>
                            <option value="05">05</option>
                            <option value="06">06</option>
                            <option value="07">07</option>
                            <option value="08">08</option>
                            <option value="09">09</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                        </select>
                    </div>
                    <div class="form-group col-4">
                        <select name="birthday" id="birthday" required class="form-control">
                            <option disabled selected value="">DAY</option>
                            <?php
                            for ($i = 1; $i <= 31; $i++) {
                                $day = str_pad($i, 2, '0', STR_PAD_LEFT);
                                echo "<option value=\"$day\">$day</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-4">
                        <select name="birthyear" id="birthyear" required class="form-control">
                            <option disabled selected value="">YEAR</option>
                            <?php
                            $range = 64;
                            $limit = 18;
                            $current = date('Y');
                            $eldest = $current - $range;
                            $recent = $current - $limit;
                            foreach (range($recent, $eldest) as $year) {
                                echo "<option value=\"$year\">$year</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <?php /* <button type="button" class="next btn action-button">Continue</button> */ ?>
            </div>
        </fieldset>

        <!-- STEP 4: Household Income -->
        <fieldset aria-headline="Which of the following matches your household income?">
            <div class="form-card text-center">
                <h3 class="fs-title">Which of the following matches your household income?</h3>
                <div class="radio-group">
                    <input type="hidden" name="Household_Income" id="household-income" class="form-control" />
                    <div class="radio btn" data-value="24999" tabindex="1">Below $25,000</div>
                    <div class="radio btn" data-value="39999" tabindex="2">$25,000 - $39,999</div>
                    <div class="radio btn" data-value="54999" tabindex="3">$40,000 - $54,999</div>
                    <div class="radio btn" data-value="69999" tabindex="4">$55,000 - $69,999</div>
                    <div class="radio btn" data-value="99999" tabindex="5">$70,000 - $99,999</div>
                    <div class="radio btn" data-value="100000" tabindex="6">$100,000+</div>
                    <p class="small"><sup>*</sup>Household income helps determine how much financial help you qualify for.</p>
                    <?php /* <button type="button" class="next btn action-button">Continue</button> */ ?>
                </div>
            </div>
        </fieldset>

        <!-- STEP 5: Name -->
        <fieldset aria-headline="What is your name?">
            <div class="form-card text-center">
                <h3 class="fs-title">What is your name?</h3>
                <input name="First_Name" id="first_name" type="text" class="form-control" placeholder="First Name" autocomplete="given-name" required minlength="2" maxlength="100"
                    value="<?php echo isset($_GET['First_Name']) ? htmlspecialchars($_GET['First_Name']) : ''; ?>">
                <input name="Last_Name" id="last_name" type="text" class="form-control" placeholder="Last Name" autocomplete="family-name" required minlength="2" maxlength="100"
                    value="<?php echo isset($_GET['Last_Name']) ? htmlspecialchars($_GET['Last_Name']) : ''; ?>">
                <?php /* <button type="button" class="next btn action-button">Continue</button> */ ?>
            </div>
        </fieldset>

        <!-- STEP 6: Email -->
        <fieldset aria-headline="What is your email?">
            <div class="form-card text-center">
                <h3 class="fs-title">What is your email?</h3>
                <input id="email" name="Email" type="email" class="form-control" placeholder="name@email.com" autocomplete="email" required
                    value="<?php echo isset($_GET['Email']) ? htmlspecialchars($_GET['Email']) : ''; ?>">
                <?php /* <button type="button" class="next btn action-button">Continue</button> */ ?>
            </div>
        </fieldset>

        <!-- STEP 7: Phone Number (Final Step) -->
        <fieldset aria-headline="Last Step!">
            <div class="form-card text-center">
                <h3 class="fs-title">
                    <span class="name"><?php echo isset($_SESSION['First_Name']) ? htmlspecialchars($_SESSION['First_Name']) : ''; ?></span>, your results are ready.
                    What is your phone number?
                </h3>
                <input name="Primary_Phone" type="tel" class="phone form-control" placeholder="(555) 555-5555" autocomplete="tel" required>
                <?php /* <button type="button" class="submit next btn action-button">Submit</button> */ ?>
                <p class="small">
                    By submitting this form I verify that I have read and accept the
                    <a data-toggle="modal" data-target="#policyModal" style="cursor: pointer;">Privacy Policy</a> and
                    <a data-toggle="modal" data-target="#termsModal" style="cursor: pointer;">Terms of Use</a> and provide consent to receive communications.
                </p>
            </div>
        </fieldset>

        <!-- Hidden Fields: Add your existing hidden tracking fields below -->
        <?php
        // Example hidden field (adjust as needed)
        if (isset($_GET['gclid'])) {
            echo '<input type="hidden" name="gclid" id="gclid_field" value="' . htmlspecialchars($_GET['gclid']) . '">';
        }
        // ... (other hidden fields)
        ?>
        <!-- Hidden inputs (retaining your tracking and session values) -->
        <?php if (isset($_SESSION['fb']) && $_SESSION['fb'] === 'true') { ?>
            <input type="hidden" name="TYPE" id="type" value="19">
            <input type="hidden" name="SRC" id="src" value="<?= base64_encode("Infinix-KFB") ?>">
        <?php } elseif (isset($_SESSION['campaign'])) { ?>
            <input type="hidden" name="TYPE" id="type" value="19">
            <input type="hidden" name="SRC" id="src" value="<?= base64_encode($_SESSION['campaign']) ?>">
        <?php } elseif (isset($_SESSION['search_partners']) && $_SESSION['search_partners'] == 'Search_partners') { ?>
            <input type="hidden" name="TYPE" id="type" value="29">
            <input type="hidden" name="SRC" id="src" value="<?= base64_encode('InfinixMedia-Ksp') ?>">
        <?php } else { ?>
            <input type="hidden" name="TYPE" id="type" value="24">
            <input type="hidden" name="SRC" id="src" value="<?= base64_encode("Infinix-K-Ping") ?>">
        <?php } ?>
        <?php if (isset($_GET['gclid'])) { ?>
            <input type="hidden" name="gclid" id="gclid_field" value="<?= $_GET['gclid'] ?>">
        <?php } ?>
        <?php if (isset($_GET['msclkid'])) { ?>
            <input type="hidden" name="msclkid" id="msclkid_field" value="<?= $_GET['msclkid'] ?>">
        <?php } ?>
        <input type="hidden" name="IP_Address" value="<?php echo $ip; ?>">
        <input type="hidden" name="Pub_ID" value="<?= isset($_SESSION['Pub_ID']) ? $_SESSION['Pub_ID'] : ((isset($_GET['Pub_ID'])) ? $_GET['Pub_ID'] : '') ?>">
        <input type="hidden" name="Sub_ID" value="<?= isset($_SESSION['Sub_ID']) ? $_SESSION['Sub_ID'] : getPeakStatus() ?>">
        <?php if (isset($_SESSION['HIT_ID'])) { ?>
            <input type="hidden" name="ad_id" value="<?= $_SESSION['HIT_ID'] ?>">
        <?php } elseif (isset($_SESSION['ad_id'])) { ?>
            <input type="hidden" name="ad_id" value="<?= $_SESSION['ad_id'] ?>">
        <?php } ?>
        <?php if (isset($_SESSION['adset_id'])) { ?>
            <input type="hidden" name="adset_id" value="<?= $_SESSION['adset_id'] ?>">
        <?php } elseif (isset($_SESSION['utm_agid'])) { ?>
            <input type="hidden" name="adset_id" value="<?= $_SESSION['utm_agid'] ?>">
        <?php } ?>
        <?php if (isset($_SESSION['Sub_ID2'])) { ?>
            <input type="hidden" name="hid" value="<?= $_SESSION['Sub_ID2'] ?>">
        <?php } ?>
        <input type="hidden" name="Preexisting_List" value="<?= (isset($_GET['page'])) ? $_GET['page'] : $_GET['Preexisting_List'] ?>">
        <input type="hidden" name="notes" <?= (isset($_GET['notes'])) ? 'value="' . $_GET['notes'] . '"' : '' ?>>
        <input type="hidden" name="Search_Engine" value="<?= (isset($_GET['engine'])) ? substr(htmlentities($_GET['engine']), 0, 100) : '' ?>">
        <input type="hidden" name="Landing_Page" value="<?= (isset($_GET['page'])) ? $_GET['page'] : 'https://' . $domain . '/' ?>">
        <input type="hidden" class="LandingPageId" name="LandingPageId" value="<?= (isset($pivot_lpid)) ? $pivot_lpid : '' ?>">
        <input type="hidden" id="Redirect_URL" name="Redirect_URL" value="/thank-you/thank-you-h1-b.php" />
        <input type="hidden" name="LeadiD_URL" id="LeadiD_URL" value="" />
        <input type="hidden" name="Age" id="age" value="<?= (isset($_GET['Age'])) ? $_GET['Age'] : '' ?>" />
        <input name="Address" type="hidden" maxlength="100" <?= (isset($_GET['Address'])) ? 'value="' . $_GET['Address'] . '"' : 'value="-"' ?>>
        <input name="city" id="city" type="hidden" maxlength="100" <?= (isset($_GET['city'])) ? 'value="' . $_GET['city'] . '"' : '' ?>>
        <input name="City" id="City" type="hidden" maxlength="100" <?= (isset($_GET['city'])) ? 'value="' . $_GET['city'] . '"' : '' ?>>
        <input name="state" id="state" type="hidden" maxlength="100" <?= (isset($_GET['state'])) ? 'value="' . $_GET['state'] . '"' : '' ?>>
        <input name="State" id="State" type="hidden" maxlength="100" <?= (isset($_GET['state'])) ? 'value="' . $_GET['state'] . '"' : '' ?>>
        <?php
        if (isset($_GET['zip'])) {
            $_SESSION['zip'] = $_GET['zip'];
        }
        $zip_value = isset($_SESSION['zip']) ? $_SESSION['zip'] : '';
        ?>
        <input name="zip" id="zip" type="hidden" value="<?= htmlspecialchars($zip_value); ?>">
        <input name="Zip" id="Zip" type="hidden" value="<?= htmlspecialchars($zip_value); ?>">

    </form>

    <!-- Disclaimer, Footer, and Modals (Privacy Policy, Terms, Licenses, etc.) go here -->
    <div class="disclaimer">
        <div class="container">
            <div class="row">
                <div class="col text-centern pt-3">
                    <p style="color: #728c98; font-size: 70%; line-height: 1.3;">
                        <?php if (in_array($_SESSION['state_abbr'], ['NY', 'MA', 'CA', 'OH'])) { ?>
                            <?= $sitename ?> is owned and operated by Michael Whitney, a licensed insurance agent. Invitations for applications for insurance on Health-Insurance.com are made only where licensed and appointed. License information for all states can be found <a data-toggle="modal" data-target="#licensesModal" style="font-weight: bold; color: inherit !important; cursor: pointer;">here</a>.
                        <?php } else { ?>
                            <?= $sitename ?> is privately owned and operated by HCI Compare LLC. Invitations for applications for insurance on Health-Insurance.com are made through HCI Compare LLC, a subsidiary of Healthcare-Insurance, only where licensed and appointed. HCI Compare LLC licensing information can be found <a data-toggle="modal" data-target="#licensesModal" style="font-weight: bold; color: inherit !important; cursor: pointer;">here</a>. Submission of your information constitutes permission for an agent to contact you with additional information about the cost and coverage details of health plans.
                        <?php } ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <footer class="container-fluid py-3">
        <div class="container">
            <div class="row wrap">
                <div class="col-sm-5 col-md-6 col-lg-5 text-center text-sm-left"></div>
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
                <div class="col"></div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src='./js/jquery.min.js'></script>
    <script src='./js/bootstrap.bundle.min.js'></script>
    <script src='./js/parsley.min.js'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.min.js"></script>
    <script src='./js/jquery.inputmask.min.js'></script>
    <script src="https://cdn.jsdelivr.net/gh/tigrr/circle-progress@v0.2.4/dist/circle-progress.min.js" type="module"></script>
    <script src='./js/custom.js'></script>
    <!-- Modals for Policy, Terms, Licenses, Affiliates, etc. -->
    <!-- Additional tracking scripts (Mouseflow, Facebook Pixel, Callrail, etc.) -->
</body>

</html>