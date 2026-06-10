<?php
require_once __DIR__ . '/classes/SessionManager.php';
require_once __DIR__ . '/classes/FormFieldGenerator.php';
require_once __DIR__ . '/classes/TrackingConfig.php';
require_once __DIR__ . '/classes/SecurityHelper.php';
require_once __DIR__ . '/config/app.php';

// Initialize session and security
SessionManager::init();
$config = AppConfig::getInstance();

// Auto-detect campaign based on referrer
if (!isset($_SESSION['campaign']) && isset($_SERVER['HTTP_REFERER'])) {
    $referrer = parse_url($_SERVER['HTTP_REFERER']);
    $referrerPath = $referrer['path'] ?? '';
    
    if (strpos($referrerPath, '/usha.php') !== false) {
        $_SESSION['campaign'] = 'usha';  // ← Set to campaign NAME, not SRC value
    }
}

// Also handle GET parameter if you're passing it
if (isset($_GET['usha']) && $_GET['usha'] === 'true') {
    $_SESSION['campaign'] = 'usha';
}

// ============= FIXED ZIP PROCESSING BLOCK =============
// Handle incoming ZIP code from landing page
if (isset($_GET['zip']) && !empty($_GET['zip'])) {
    $incomingZip = $_GET['zip'];
    // error_log("DEBUG: Processing ZIP: $incomingZip");

    // Check if we need to process this ZIP
    $currentZip = SessionManager::getZip();
    $currentCity = SessionManager::getCity();
    $currentState = SessionManager::getState();

    // error_log("DEBUG: Session data - ZIP: '$currentZip', City: '$currentCity', State: '$currentState'");

    // Process ZIP if:
    // 1. We don't have this ZIP in session, OR
    // 2. We have the ZIP but missing city/state data
    if (empty($currentZip) || $currentZip !== $incomingZip || empty($currentCity) || empty($currentState)) {
        // error_log("DEBUG: Need to process ZIP - missing or incomplete data");

        // Fetch location data from ZIP API
        $apiUrl = "https://zip.getziptastic.com/v2/US/$incomingZip";
        // error_log("DEBUG: API URL: $apiUrl");

        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'Mozilla/5.0 (compatible; Healthcare Form/1.0)'
            ]
        ]);

        $response = @file_get_contents($apiUrl, false, $context);

        if ($response !== false) {
            // error_log("DEBUG: API response received: " . substr($response, 0, 100));

            $locationData = json_decode($response, true);

            if ($locationData && !isset($locationData['error'])) {
                // error_log("DEBUG: Location data is valid");

                // Format city name properly
                $city = ucwords(strtolower($locationData['city'] ?? ''));
                $state = $locationData['state_short'];
                $stateName = $locationData['state'];

                error_log("DEBUG: Formatted data - City: '$city', State: '$state', StateName: '$stateName'");

                // Store location data in session
                try {
                    SessionManager::setLocation($city, $state, $incomingZip);
                    // error_log("DEBUG: SessionManager::setLocation called");

                    // Store location data in session
                    SessionManager::setLocation($city, $state, $incomingZip);

                    // Also store the full state name in the location array directly
                    $_SESSION['location']['state_name'] = $stateName;

                    // Verify it was stored
                    $verifyCity = SessionManager::getCity();
                    $verifyState = SessionManager::getState();
                    $verifyZip = SessionManager::getZip();
                    error_log("DEBUG: Verification - City: '$verifyCity', State: '$verifyState', ZIP: '$verifyZip'");

                    // Also store in form data
                    SessionManager::setFormData('Zip', $incomingZip);
                    SessionManager::setFormData('City', $city);
                    SessionManager::setFormData('State', $state);
                    SessionManager::setFormData('state_name', $stateName);

                    error_log("DEBUG: Form data stored successfully");
                } catch (Exception $e) {
                    error_log("ERROR: Exception in SessionManager: " . $e->getMessage());
                }
            } else {
                error_log("ERROR: Invalid location data: " . json_encode($locationData));
            }
        } else {
            error_log("ERROR: API call failed for ZIP: $incomingZip");
        }
    } else {
        error_log("DEBUG: Complete ZIP data already in session, skipping");
    }
} else {
    error_log("DEBUG: No ZIP parameter in request");
}
// ============= END FIXED ZIP PROCESSING BLOCK =============

// Process step navigation
$currentStep = 1;
if (isset($_GET['step']) && is_numeric($_GET['step'])) {
    $requestedStep = intval($_GET['step']);

    // Convert step=0 from landing page to step=1
    if ($requestedStep === 0) {
        $currentStep = 1;
    } else {
        $currentStep = min(7, max(1, $requestedStep));
    }

    $_SESSION['form-step'] = $currentStep;
} elseif (isset($_SESSION['form-step'])) {
    $currentStep = $_SESSION['form-step'];
}

// Store other form data in session securely
foreach ($_GET as $key => $value) {
    if (!in_array($key, ['step', 'csrf_token', 'zip'])) { // Exclude zip since we processed it above
        $_SESSION['form_data'][$key] = SecurityHelper::sanitize($value);
    }
}

// Reset form if campaign parameter is present
if (isset($_GET['campaign'])) {
    $_SESSION['form-step'] = 1;
    $_SESSION['form_data'] = [];
    // Don't clear location data when resetting form
}

// Generate CSRF token
$csrfToken = SecurityHelper::generateCSRFToken();

// Debug output (remove this in production)
if (isset($_GET['debug'])) {
    echo "<pre>";
    echo "Current ZIP: " . SessionManager::getZip() . "\n";
    echo "Current City: " . SessionManager::getCity() . "\n";
    echo "Current State: " . SessionManager::getState() . "\n";
    echo "Current Step: $currentStep\n";
    echo "GET params: " . json_encode($_GET, JSON_PRETTY_PRINT) . "\n";
    echo "Session location: " . json_encode($_SESSION['location'] ?? 'not set', JSON_PRETTY_PRINT) . "\n";
    echo "</pre>";
}
if (isset($_GET['debug'])) {
    echo "<pre>";
    echo "Campaign in session: " . ($_SESSION['campaign'] ?? 'not set') . "\n";
    echo "USHA in session: " . ($_SESSION['usha'] ?? 'not set') . "\n";
    echo "Campaign config: ";
    print_r(TrackingConfig::getCampaignConfig($_SESSION));
    echo "</pre>";
}

// Define income matrix based on household size
$incomeMatrix = [
    '1' => [
        '24999' => 'Under $30k',
        '39999' => '$30k - $60k',
        '69999' => '$60k - $100k',
        '100000' => 'Over $100k',
        '54999' => 'Prefer not to say'
    ],
    '2' => [
        '24999' => 'Under $30k',
        '54999' => '$30k - $60k',
        '99999' => '$60k - $100k',
        '100000' => 'Over $100k',
        '69999' => 'Prefer not to say'
    ],
    '3' => [ // 3+ people
        '24999' => 'Under $30k',
        '54999' => '$30k - $60k',
        '99999' => '$60k - $100k',
        '100000' => 'Over $100k',
        '69999' => 'Prefer not to say'
    ]
];

// Get current household selection from session (form_data or direct)
$currentHousehold = SessionManager::getFormData('Household') ??
    $_SESSION['form_data']['Household'] ??
    $_GET['Household'] ??
    '1';

// Ensure we have a valid household value
if (!in_array($currentHousehold, ['1', '2', '3'])) {
    $currentHousehold = '1';
}

$currentIncomeOptions = $incomeMatrix[$currentHousehold];
?>

<!-- MultiStep Form -->
<div class="container-fluid">
    <div class="row justify-content-center mt-0">
        <div class="col-11 col-sm-9 col-md-7 col-lg-6 text-center p-0 mt-4 mb-0">
            <!-- Progress Bar -->
            <ul id="progressbar" role="progressbar" aria-label="Form progress">
                <?php for ($i = 1; $i <= 7; $i++): ?>
                    <li id="step<?= $i ?>" class="<?= $i <= $currentStep ? 'active' : '' ?>"
                        aria-current="<?= $i === $currentStep ? 'step' : 'false' ?>">
                        <span class="sr-only">Step <?= $i ?> of 7</span>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
    </div>
</div>

<div class="container-fluid" id="grad1">
    <div class="row justify-content-center mt-0">
        <div class="col-11 col-sm-9 col-md-7 col-lg-7 text-center p-0 mt-3 mb-2">
            <div class="row">
                <div class="col-md-12 mx-0">

                    <form id="msform"
                        action="/multi-quote/inc/form-processing.php"
                        method="POST"
                        data-parsley-validate
                        novalidate
                        data-current-step="<?= $currentStep ?>">

                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?= SecurityHelper::escape($csrfToken) ?>">

                        <!-- Hidden Fields Generated by FormFieldGenerator -->
                        <div id="hidden-fields">
                            <?php
                            $generator = new FormFieldGenerator(
                                $_SESSION,
                                $_GET,
                                $_SERVER,
                                [
                                    'domain' => $config->get('domain', $_SERVER['HTTP_HOST']),
                                    'pivot_lpid' => $config->get('pivot_lpid', '1003')
                                ]
                            );
                            echo $generator->generateAllFields();
                            ?>
                        </div>

                        <!-- Step 1: Insurance Status -->
                        <fieldset class="form-step" data-step="1" <?= $currentStep === 1 ? 'data-active="true"' : 'style="display:none"' ?>>
                            <div class="form-card text-center">
                                <h2 class="form-headline">
                                    <strong>You're minutes away from saving on healthcare coverage!</strong>
                                </h2>
                                <h3 class="fs-title">Are you currently insured?</h3>

                                <div class="radio-group mb-5" role="radiogroup" aria-labelledby="insurance-status-label">
                                    <input type="hidden" name="Currently_Insured" id="insured" required>

                                    <div class="radio-option">
                                        <input type="radio"
                                            id="insuredYes"
                                            name="insuranceStatus"
                                            class="custom-control-input"
                                            value="Yes"
                                            data-target="insured"
                                            <?= isset($_SESSION['form_data']['insuranceStatus']) && $_SESSION['form_data']['insuranceStatus'] == 'Yes' ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="insuredYes">Yes</label>
                                    </div>

                                    <div class="radio-option">
                                        <input type="radio"
                                            id="insuredNo"
                                            name="insuranceStatus"
                                            class="custom-control-input"
                                            value="No"
                                            data-target="insured"
                                            <?= isset($_SESSION['form_data']['insuranceStatus']) && $_SESSION['form_data']['insuranceStatus'] == 'No' ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="insuredNo">No</label>
                                    </div>
                                </div>

                                <button type="button"
                                    class="next btn btn-primary action-button"
                                    data-next-step="2"
                                    aria-label="Continue to next step">
                                    Next
                                </button>
                            </div>
                        </fieldset>

                        <!-- Step 2: Household Size -->
                        <fieldset class="form-step" data-step="2" <?= $currentStep === 2 ? 'data-active="true"' : 'style="display:none"' ?>>
                            <div class="form-card text-center">
                                <h2 class="form-headline">
                                    <strong>How many people are you covering?</strong>
                                </h2>

                                <div class="radio-group mb-5" role="radiogroup" aria-labelledby="household-size-label">
                                    <input type="hidden" name="Household" id="household" required>

                                    <?php
                                    $householdOptions = [
                                        '1' => '1',
                                        '2' => '2',
                                        '3' => '3+'
                                    ];
                                    foreach ($householdOptions as $value => $label):
                                    ?>
                                        <div class="radio-option">
                                            <input type="radio"
                                                id="household_<?= $value ?>"
                                                name="household_size"
                                                class="custom-control-input"
                                                value="<?= $value ?>"
                                                data-target="household"
                                                <?= isset($_SESSION['form_data']['Household']) && $_SESSION['form_data']['Household'] == $value ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="household_<?= $value ?>">
                                                <?= SecurityHelper::escape($label) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <button type="button"
                                    class="prev btn btn-secondary action-button mr-2 hidden"
                                    data-prev-step="1"
                                    aria-label="Go to previous step">
                                    Back
                                </button>
                                <button type="button"
                                    class="next btn btn-primary action-button"
                                    data-next-step="3"
                                    aria-label="Continue to next step">
                                    Next
                                </button>
                            </div>
                        </fieldset>

                        <!-- Step 3: Coverage Start Date -->
                        <fieldset class="form-step" data-step="3" <?= $currentStep === 3 ? 'data-active="true"' : 'style="display:none"' ?>>
                            <div class="form-card">
                                <h2 class="form-headline">
                                    <strong>When do you want your coverage to start?</strong>
                                </h2>

                                <div class="row justify-content-center">
                                    <div class="form-group col-md-6 mb-5 margin-auto">
                                        <label for="urgency" class="sr-only">Coverage Start Date</label>
                                        <select name="Urgency"
                                            id="urgency"
                                            required
                                            class="form-control"
                                            aria-label="Select when you want coverage to start">
                                            <option value="" selected disabled hidden>Select start date</option>
                                            <option value="Today" <?= isset($_SESSION['form_data']['urgency']) && $_SESSION['form_data']['urgency'] == 'Today' ? 'selected' : '' ?>>
                                                Today
                                            </option>
                                            <option value="Month" <?= isset($_SESSION['form_data']['urgency']) && $_SESSION['form_data']['urgency'] == 'Month' ? 'selected' : '' ?>>
                                                Within a Month
                                            </option>
                                            <option value="Shopping" <?= isset($_SESSION['form_data']['urgency']) && $_SESSION['form_data']['urgency'] == 'Shopping' ? 'selected' : '' ?>>
                                                Just Shopping
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <button type="button"
                                    class="prev btn btn-secondary action-button mr-2 hidden"
                                    data-prev-step="2">
                                    Back
                                </button>
                                <button type="button"
                                    class="next btn btn-primary action-button"
                                    data-next-step="4">
                                    Next
                                </button>
                            </div>
                        </fieldset>

                        <!-- Step 4: Name -->
                        <fieldset class="form-step" data-step="4" <?= $currentStep === 4 ? 'data-active="true"' : 'style="display:none"' ?>>
                            <div class="form-card">
                                <h2 class="form-headline">
                                    <strong>Who is this plan&nbsp;for?</strong>
                                </h2>

                                <div class="form-group text-left">
                                    <label for="first_name">First Name</label>
                                    <input type="text"
                                        name="First_Name"
                                        id="first_name"
                                        class="form-control"
                                        autocomplete="given-name"
                                        required
                                        minlength="2"
                                        maxlength="100"
                                        pattern="[a-zA-Z\s\-']+"
                                        value="<?= SecurityHelper::escape($_SESSION['form_data']['First_Name'] ?? '') ?>"
                                        data-parsley-error-message="Please enter a valid first name">
                                </div>

                                <div class="form-group text-left mb-5">
                                    <label for="last_name">Last Name</label>
                                    <input type="text"
                                        name="Last_Name"
                                        id="last_name"
                                        class="form-control"
                                        autocomplete="family-name"
                                        required
                                        minlength="2"
                                        maxlength="100"
                                        pattern="[a-zA-Z\s\-']+"
                                        value="<?= SecurityHelper::escape($_SESSION['form_data']['Last_Name'] ?? '') ?>"
                                        data-parsley-error-message="Please enter a valid last name">
                                </div>

                                <button type="button"
                                    class="prev btn btn-secondary action-button mr-2 hidden"
                                    data-prev-step="3">
                                    Back
                                </button>
                                <button type="button"
                                    class="next btn btn-primary action-button"
                                    data-next-step="5">
                                    Next
                                </button>
                            </div>
                        </fieldset>

                        <!-- Step 5: Date of Birth & Gender -->
                        <fieldset class="form-step" data-step="5" <?= $currentStep === 5 ? 'data-active="true"' : 'style="display:none"' ?>>
                            <div class="form-card">
                                <h2 class="form-headline">
                                    <strong>Hello, <span class="name text-primary text-capitalize"></span><span class="text-primary">!</span> <br class="d-md-none"><span class="long-headline">Finish your profile before viewing your options.</span></strong>
                                </h2>
                                <h3 class="fs-title">Let us help you find personalized options.</h3>
                                <?php /* <h3 class="fs-title">We'll use your details to match you with options that&nbsp;fit best.</h3> */ ?>

                                <input type="hidden" name="DOB" id="dob">

                                <div class="form-group">
                                    <label>Date of Birth</label>
                                    <div class="row justify-content-center dob">
                                        <div class="col-auto pl-0">
                                            <label for="birthmonth" class="sr-only">Birth Month</label>
                                            <select name="birthmonth"
                                                id="birthmonth"
                                                required
                                                class="form-control"
                                                aria-label="Birth month">
                                                <option value="">Month</option>
                                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                                    <option value="<?= sprintf('%02d', $i) ?>"
                                                        <?= isset($_SESSION['form_data']['birthmonth']) && $_SESSION['form_data']['birthmonth'] == sprintf('%02d', $i) ? 'selected' : '' ?>>
                                                        <?= date('M', mktime(0, 0, 0, $i, 1)) ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>

                                        <div class="col-auto px-0">
                                            <label for="birthday" class="sr-only">Birth Day</label>
                                            <select name="birthday"
                                                id="birthday"
                                                required
                                                class="form-control"
                                                aria-label="Birth day">
                                                <option value="">Day</option>
                                                <?php for ($i = 1; $i <= 31; $i++): ?>
                                                    <option value="<?= sprintf('%02d', $i) ?>"
                                                        <?= isset($_SESSION['form_data']['birthday']) && $_SESSION['form_data']['birthday'] == sprintf('%02d', $i) ? 'selected' : '' ?>>
                                                        <?= sprintf('%02d', $i) ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>

                                        <div class="col-auto pr-0">
                                            <label for="birthyear" class="sr-only">Birth Year</label>
                                            <select name="birthyear"
                                                id="birthyear"
                                                required
                                                class="form-control"
                                                aria-label="Birth year">
                                                <option value="">Year</option>
                                                <?php
                                                $currentYear = date('Y');
                                                $minAge = 18;
                                                $maxAge = 90;
                                                for ($year = $currentYear - $minAge; $year >= $currentYear - $maxAge; $year--):
                                                ?>
                                                    <option value="<?= $year ?>"
                                                        <?= isset($_SESSION['form_data']['birthyear']) && $_SESSION['form_data']['birthyear'] == $year ? 'selected' : '' ?>>
                                                        <?= $year ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-4">
                                    <label>Gender</label>
                                    <div class="radio-group mt-0">
                                        <input type="hidden" name="Gender" id="gender" required>

                                        <div class="radio-option">
                                            <input type="radio"
                                                id="male"
                                                name="gender_selection"
                                                class="custom-control-input"
                                                value="M"
                                                data-target="gender"
                                                <?= isset($_SESSION['form_data']['Gender']) && $_SESSION['form_data']['Gender'] == 'M' ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="male">Male</label>
                                        </div>

                                        <div class="radio-option">
                                            <input type="radio"
                                                id="female"
                                                name="gender_selection"
                                                class="custom-control-input"
                                                value="F"
                                                data-target="gender"
                                                <?= isset($_SESSION['form_data']['Gender']) && $_SESSION['form_data']['Gender'] == 'F' ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="female">Female</label>
                                        </div>
                                    </div>
                                </div>

                                <button type="button"
                                    class="prev btn btn-secondary action-button mr-2 hidden"
                                    data-prev-step="4">
                                    Back
                                </button>
                                <button type="button"
                                    class="next btn btn-primary action-button"
                                    data-next-step="6">
                                    Next
                                </button>
                            </div>
                        </fieldset>

                        <!-- Step 6: Income & Reason -->
                        <fieldset class="form-step" data-step="6" <?= $currentStep === 6 ? 'data-active="true"' : 'style="display:none"' ?>>
                            <div class="form-card">
                                <h2 class="form-headline step-6">
                                    <span class="long-headline">
                                        <strong>You're just moments away from discovering affordable options available in <br class="d-md-none">
                                            <span class="text-primary city-name"><?= SecurityHelper::escape(SessionManager::getCity() ?: 'your area') ?></span>,
                                            <span class="text-primary state-name"><?= SecurityHelper::escape(SessionManager::getState() ?: 'your state') ?></span>!</strong>
                                    </span>
                                </h2>

                                <div class="form-group text-left">
                                    <label for="household_income">Household Income</label>
                                    <select name="Household_Income"
                                        id="household_income"
                                        required
                                        class="form-control"
                                        data-household="<?= SecurityHelper::escape($currentHousehold) ?>">
                                        <option value="" disabled selected hidden>Select income range</option>
                                        <?php
                                        // Income options are populated server-side based on household selection from session
                                        foreach ($currentIncomeOptions as $value => $label):
                                            $selected = (isset($_SESSION['form_data']['Household_Income']) &&
                                                $_SESSION['form_data']['Household_Income'] == $value) ? 'selected' : '';
                                        ?>
                                            <option value="<?= SecurityHelper::escape($value) ?>" <?= $selected ?>>
                                                <?= SecurityHelper::escape($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group text-left mt-4 mb-5">
                                    <label for="reason">Reason for Shopping</label>
                                    <select name="Reason"
                                        id="reason"
                                        required
                                        class="form-control">
                                        <option disable hidden value="">Select reason</option>
                                        <option value="New plan" <?= isset($_SESSION['form_data']['Reason']) && $_SESSION['form_data']['Reason'] == 'New plan' ? 'selected' : '' ?>>
                                            Find a new plan
                                        </option>
                                        <option value="Lower Cost" <?= isset($_SESSION['form_data']['Reason']) && $_SESSION['form_data']['Reason'] == 'Lower Cost' ? 'selected' : '' ?>>
                                            Save money
                                        </option>
                                        <option value="Improve benefits" <?= isset($_SESSION['form_data']['Reason']) && $_SESSION['form_data']['Reason'] == 'Improve benefits' ? 'selected' : '' ?>>
                                            Improve benefits
                                        </option>
                                        <option value="Just Shopping" <?= isset($_SESSION['form_data']['Reason']) && $_SESSION['form_data']['Reason'] == 'Just Shopping' ? 'selected' : '' ?>>
                                            Just shopping
                                        </option>
                                    </select>
                                </div>

                                <button type="button"
                                    class="prev btn btn-secondary action-button mr-2 hidden"
                                    data-prev-step="5">
                                    Back
                                </button>
                                <button type="button"
                                    class="next btn btn-primary action-button"
                                    data-next-step="7">
                                    Next
                                </button>
                            </div>
                        </fieldset>

                        <!-- Step 7: Contact Information -->
                        <fieldset class="form-step" data-step="7" <?= $currentStep === 7 ? 'data-active="true"' : 'style="display:none"' ?>>
                            <div class="form-card">
                                <h2 class="form-headline">
                                    <strong>Congratulations <span class="name text-primary text-capitalize"></span><span class="text-primary">!</span>
                                        <? /* your <span class="text-primary state-name"><?= SecurityHelper::escape(SessionManager::getStateName() ?: 'state') ?></span> */ ?>
                                        Your Health Quote is Ready!</strong>
                                </h2>
                                <h3 class="fs-title text-primary"><b style="font-weight: 600;">Enter your information below to explore your options now.</b></h3>

                                <div class="form-group text-left">
                                    <label for="email">Email Address</label>
                                    <input type="email"
                                        name="Email"
                                        id="email"
                                        class="form-control"
                                        placeholder="name@email.com"
                                        autocomplete="email"
                                        required
                                        pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                                        value="<?= SecurityHelper::escape($_SESSION['form_data']['Email'] ?? '') ?>"
                                        data-parsley-type="email"
                                        data-parsley-error-message="Please enter a valid email address">
                                </div>

                                <div class="form-group text-left mt-4 mb-5">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel"
                                        name="Primary_Phone"
                                        id="phone"
                                        class="phone form-control"
                                        placeholder="(555) 555-5555"
                                        autocomplete="tel"
                                        required
                                        data-parsley-pattern="^\D*([2-9]\d{2})(\D*)([2-9]\d{2})(\D*)(\d{4})\D*$"
                                        data-parsley-error-message="Please enter a valid 10-digit phone number"
                                        value="<?= SecurityHelper::escape($_SESSION['form_data']['Primary_Phone'] ?? '') ?>">
                                </div>

                                <div class="disclaimer">
                                    <p class="small text-muted">
                                        <?php /*
                                        By clicking "Get My Quote," you agree to be contacted by licensed insurance agents, 
                                        insurance companies, or their representatives via telephone call, text message, or 
                                        email using automated dialing technology at the phone number and email address provided, 
                                        even if the number is listed on a state or federal Do Not Call registry. */ ?> 
                                        By clicking "Get My Quote," you give consent to <?php echo $sitename; ?> and it's 
                                        <a data-toggle="modal" data-target="#partnerModal" style="cursor: pointer;" href="#">affiliates</a> 
                                        to contact you via telephone call, text message, or email using automated dialing 
                                        technology at the phone number and email address provided, even if the number is 
                                        listed on a state or federal Do Not Call registry. 
                                        Consent is not required to purchase insurance. Message and data rates may apply. 
                                        <a data-toggle="modal" data-target="#policyModal" style="cursor: pointer;" href="#">Privacy Policy</a> |
                                        <a data-toggle="modal" data-target="#termsModal" style="cursor: pointer;" href="#">Terms of Service</a>
                                    </p>
                                </div>

                                <button type="button"
                                    class="prev btn btn-secondary action-button mr-2 hidden"
                                    data-prev-step="6">
                                    Back
                                </button>
                                <button type="submit"
                                    class="btn btn-success action-button submit"
                                    id="submit-form">
                                    Get My Quote
                                </button>
                            </div>
                        </fieldset>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php /* Affiliation logos */ ?>
<?php //include '../inc/aff-logos.php'; ?>

<!-- Loading Modal -->
<?php include __DIR__ . '/shared/loading-modal/loading-modal.html'; ?>