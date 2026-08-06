$(document).ready(function () {
    // Initialize variables
    var current_fs, next_fs, previous_fs;
    var animating = false;
    var isInitializing = false;
    var needsZip = $('#msform').data('needs-zip') === true || $('#msform').data('needs-zip') === 'true';
    var debugEnabled = window.location.search.indexOf('debug=1') !== -1;

    function debugLog() {
        if (debugEnabled && window.console && typeof console.log === 'function') {
            console.log.apply(console, arguments);
        }
    }

    // Function to get CSRF token dynamically
    function getCSRFToken() {
        var token = $('input[name="csrf_token"]').val();
        if (!token) {
            console.error('CSRF token not found!');
            return '';
        }
        return token;
    }

    // Initialize Parsley for form validation
    var $form = $("#msform");
    var parsleyForm = $form.parsley();

    // Get all fieldsets
    var $sections = $(".form-step");

    // Assign data-parsley-group attributes to each input in each fieldset
    $sections.each(function (index, section) {
        var stepNumber = $(section).data('step') || (index + 1);
        $(section).find(":input").attr("data-parsley-group", "block-" + stepNumber);
    });

    // Initialize form on load
    initializeForm();

    function isMobileViewport() {
        return window.matchMedia && window.matchMedia('(max-width: 767px)').matches;
    }

    function keepFieldVisible($field) {
        if (!isMobileViewport() || !$field || !$field.length) {
            return;
        }

        setTimeout(function () {
            var field = $field.get(0);
            if (field && typeof field.scrollIntoView === 'function') {
                field.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
            }
        }, 120);
    }

    function updateMobileKeyboardState() {
        if (!window.visualViewport || !isMobileViewport()) {
            document.body.classList.remove('mobile-keyboard-open');
            return;
        }

        var keyboardOpen = window.visualViewport.height < (window.innerHeight - 120);
        document.body.classList.toggle('mobile-keyboard-open', keyboardOpen);
    }

    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', updateMobileKeyboardState);
        window.visualViewport.addEventListener('scroll', updateMobileKeyboardState);
    }

    $(window).on('orientationchange resize', function () {
        setTimeout(updateMobileKeyboardState, 150);
    });

    $(document).on('focus', '#msform input, #msform select, #msform textarea', function () {
        keepFieldVisible($(this));
        updateMobileKeyboardState();
    });

    $(document).on('blur', '#msform input, #msform select, #msform textarea', function () {
        setTimeout(updateMobileKeyboardState, 80);
    });

    updateMobileKeyboardState();

    /**
     * Initialize form state from session
     */
    function initializeForm() {
        isInitializing = true;
        // Check session for existing data
        $.ajax({
            url: '../multi-quote/inc/get-session-step.php',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                debugLog('Session response received');

                if (response.success) {
                    var targetStep = 1;

                    // Determine which step to show
                    if (!response.hasZip && needsZip) {
                        targetStep = 0;
                    } else if (response.currentStep) {
                        targetStep = response.currentStep;
                    } else {
                        targetStep = 1;
                    }

                    debugLog('Restoring to step:', targetStep);

                    // Hide all steps first
                    $('.form-step').hide().attr('data-active', 'false');

                    // Show the target step
                    $('.form-step[data-step="' + targetStep + '"]').show().attr('data-active', 'true');

                    // Update progress bar
                    updateProgressBar(targetStep);

                    // Update location displays if available
                    if (response.locationData) {
                        updateLocationDisplay(response.locationData);
                        // Also update hidden fields
                        if (response.locationData.city) $('#City').val(response.locationData.city);
                        if (response.locationData.state) $('#State').val(response.locationData.state);
                        if (response.locationData.zip) $('#Zip').val(response.locationData.zip);
                    }

                    // Populate form with existing data
                    if (response.formData) {
                        populateFormData(response.formData);
                    }

                    // Load from localStorage as backup
                    loadLocalStorageData();

                    setTimeout(function () {
                        isInitializing = false;
                    }, 500);
                }
            },
            error: function (xhr, status, error) {
                console.error('Failed to get session data:', error);
                loadLocalStorageData();
                isInitializing = false;
            }
        });
    }

    /**
     * Handle ZIP code submission
     */
    $('#zip-submit').on('click', function (e) {
        e.preventDefault();
        handleZipSubmission();
    });

    // Also handle Enter key on zip input
    $('#zip-input').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            handleZipSubmission();
        }
    });

    function handleZipSubmission() {
        var $btn = $('#zip-submit');
        var zipCode = $('#zip-input').val();

        // Validate with Parsley
        if (!$('#zip-input').parsley().validate()) {
            return;
        }

        // Show loader
        $('#zip-loader').show();
        $('#zip-error').hide();
        $btn.prop('disabled', true).text('Verifying...');

        // Submit to server
        $.ajax({
            url: '../multi-quote/inc/save-zip-data.php',
            type: 'POST',
            data: {
                zip: zipCode,
                csrf_token: getCSRFToken()
            },
            dataType: 'json',
            success: function (response) {
                $('#zip-loader').hide();

                if (response.success) {
                    // Store in localStorage
                    localStorage.setItem('zip', response.data.zip);
                    localStorage.setItem('city', response.data.city);
                    localStorage.setItem('state', response.data.state);
                    localStorage.setItem('state_name', response.data.state_name);

                    // Store in sessionStorage for tracking
                    sessionStorage.setItem('state', response.data.state);

                    // Update location displays
                    updateLocationDisplay(response.data);

                    // Update ALL hidden fields - this is critical!
                    $('#Zip').val(response.data.zip);
                    $('#City').val(response.data.city);
                    $('#State').val(response.data.state);
                    $('input[name="Zip"]').val(response.data.zip);
                    $('input[name="City"]').val(response.data.city);
                    $('input[name="State"]').val(response.data.state);

                    // Check for valid state
                    checkForValidState(response.data.state_name, response.data.state);

                    // Transition to next step
                    transitionStep(0, 1);

                } else if (response.error === 'restricted_state') {
                    showRestrictedStateMessage(response.message);
                } else {
                    $('#zip-error').text(response.error || 'Invalid ZIP code. Please try again.').show();
                    $btn.prop('disabled', false).text('Continue');
                }
            },
            error: function (xhr) {
                $('#zip-loader').hide();
                console.error('ZIP submission error:', xhr);
                $('#zip-error').text('An error occurred. Please try again.').show();
                $btn.prop('disabled', false).text('Continue');
            }
        });
    }

    /**
     * Handle radio button selections
     */
    // Update the existing radio button handler to save household to session
    $('.radio-option input[type="radio"]').on('change', function (event) {
        var targetField = $(this).data('target');
        if (targetField) {
            $('#' + targetField).val($(this).val());

            // If this is household, save to session immediately
            if (targetField === 'household') {
                var householdValue = $(this).val();

                // Save to session via AJAX
                $.ajax({
                    url: '../multi-quote/inc/save-field.php',
                    type: 'POST',
                    data: {
                        field: 'Household',
                        value: householdValue,
                        csrf_token: getCSRFToken()
                    },
                    success: function (response) {
                        debugLog('Household value saved to session');

                        // Update income options dynamically
                        updateIncomeOptions(householdValue);
                    }
                });
            }
        }

        // Save to localStorage
        saveFieldToLocalStorage($(this).attr('name'), $(this).val());

        // Only auto-advance on real user interactions, not scripted .trigger('change') events
        if (event && event.originalEvent) {
            var stepNumber = parseInt($(this).closest('.form-step').data('step'), 10);
            if (shouldAutoAdvanceStep(stepNumber)) {
                setTimeout(function () {
                    autoAdvanceStep(stepNumber);
                }, 120);
            }
        }
    });

    // Function to update income options dynamically
    // Function to update income options dynamically
    function updateIncomeOptions(householdSize) {
        // Income matrix using arrays to preserve order
        var incomeMatrix = {
            '1': [
                { value: '24999', label: 'Under $30,000' },
                { value: '39999', label: '$30,000 - $60,000' },
                { value: '69999', label: '$60,000 - $100,000' },
                { value: '100000', label: 'Over $100,000' },
                { value: '54999', label: 'Prefer not to say' }
            ],
            '2': [
                { value: '24999', label: 'Under $30,000' },
                { value: '54999', label: '$30,000 - $60,000' },
                { value: '99999', label: '$60,000 - $100,000' },
                { value: '100000', label: 'Over $100,000' },
                { value: '69999', label: 'Prefer not to say' }
            ],
            '3': [
                { value: '24999', label: 'Under $30,000' },
                { value: '54999', label: '$30,000 - $60,000' },
                { value: '99999', label: '$60,000 - $100,000' },
                { value: '100000', label: 'Over $100,000' },
                { value: '69999', label: 'Prefer not to say' }
            ]
        };

        var $incomeSelect = $('#household_income');
        if ($incomeSelect.length > 0 && incomeMatrix[householdSize]) {
            var currentValue = $incomeSelect.val();

            // Clear existing options except the first (placeholder)
            $incomeSelect.find('option:not(:first)').remove();

            // Add new options - now using array iteration to preserve order
            var incomeOptions = incomeMatrix[householdSize];
            for (var i = 0; i < incomeOptions.length; i++) {
                var option = incomeOptions[i];
                var $option = $('<option></option>')
                    .attr('value', option.value)
                    .text(option.label);

                if (option.value === currentValue) {
                    $option.attr('selected', 'selected');
                }

                $incomeSelect.append($option);
            }

            // Update data attribute
            $incomeSelect.attr('data-household', householdSize);
        }
    }

    function shouldAutoAdvanceStep(stepNumber) {
        return [1, 2, 3].indexOf(stepNumber) !== -1;
    }

    function autoAdvanceStep(stepNumber) {
        if (isInitializing || animating || !shouldAutoAdvanceStep(stepNumber)) {
            return;
        }

        var $step = $('.form-step[data-step="' + stepNumber + '"]');
        if (!$step.length || !$step.is(':visible')) {
            return;
        }

        var $nextButton = $step.find('.next').first();
        if (!$nextButton.length || $nextButton.hasClass('processing')) {
            return;
        }

        var nextStep = parseInt($nextButton.data('next-step'), 10);
        if (!nextStep) {
            return;
        }

        if (!validateStep(stepNumber)) {
            return;
        }

        $nextButton.addClass('processing');
        saveStepData(stepNumber);
        transitionStep(stepNumber, nextStep);

        setTimeout(function () {
            $nextButton.removeClass('processing');
        }, 800);
    }

    // When navigating to Step 6, check if we need to update income options
    $(document).on('click', '.next[data-next-step="6"]', function () {
        setTimeout(function () {
            var currentHousehold = $('#household').val() || localStorage.getItem('Household') || '1';
            updateIncomeOptions(currentHousehold);
        }, 500);
    });

    $(document).on('change', '.form-step[data-step="3"] #urgency', function (event) {
        // Ignore programmatic changes during populate/restore
        if (!(event && event.originalEvent)) {
            return;
        }
        autoAdvanceStep(3);
    });

    /**
     * Handle next button clicks
     */
    $(document).off('click.nextstep').on('click.nextstep', '.next', function (e) {
        e.preventDefault();
        e.stopPropagation();

        if ($(this).attr('id') === 'zip-submit') {
            return;
        }

        var $button = $(this);

        if ($button.hasClass('processing') || animating) {
            return false;
        }

        $button.addClass('processing');

        var currentStep = parseInt($button.closest('.form-step').data('step'));
        var nextStep = parseInt($button.data('next-step'));

        // Validate current step
        if (!validateStep(currentStep)) {
            $button.removeClass('processing');
            return false;
        }

        // Save current step data
        saveStepData(currentStep);

        // Transition to next step
        transitionStep(currentStep, nextStep);

        setTimeout(function () {
            $button.removeClass('processing');
        }, 800);
    });

    /**
     * Handle previous button clicks
     */
    $(document).off('click.prevstep').on('click.prevstep', '.prev', function (e) {
        e.preventDefault();
        e.stopPropagation();

        if (animating) {
            return false;
        }

        var currentStep = parseInt($(this).closest('.form-step').data('step'));
        var prevStep = parseInt($(this).data('prev-step'));

        transitionStep(currentStep, prevStep);
    });

    /**
     * Handle form submission
     */
    $('#submit-form, .submit').on('click', function (e) {
        e.preventDefault();

        // Validate final step
        if (!validateStep(7)) {
            return false;
        }

        // Save final step data
        saveStepData(7);

        // Check for valid phone number
        var phoneNumber = $('input[name="Primary_Phone"]').val();
        if (!validatePhoneNumber(phoneNumber)) {
            showPhoneError();
            return false;
        }

        submitForm();
    });

    /**
     * Validate a form step
     */
    function validateStep(stepNumber) {
        var groupName = "block-" + stepNumber;
        var isValid = parsleyForm.validate({ group: groupName });

        if (!isValid) {
            parsleyForm.validate({ group: groupName });
        }

        return isValid;
    }

    /**
     * Save step data to localStorage and session
     */
    function saveStepData(stepNumber) {
        var $step = $('.form-step[data-step="' + stepNumber + '"]');

        $step.find('input, select, textarea').each(function () {
            var $field = $(this);
            var name = $field.attr('name');
            var id = $field.attr('id');
            var value = $field.val();

            if (name && value) {
                // Save to localStorage with both name and id as keys
                localStorage.setItem(name, value);
                if (id) {
                    localStorage.setItem(id, value);
                }

                // Update hidden fields if they exist
                $('#' + id).val(value);
                $('input[name="' + name + '"]').val(value);

                // For specific fields, update display elements
                if (name === 'First_Name') {
                    $('.name').text(value);
                }
            }
        });

        // Handle special cases
        if (stepNumber === 4) {
            // CRITICAL FIX: Save first name properly
            var firstName = $('#first_name').val();
            var lastName = $('#last_name').val();
            if (firstName) {
                $('.name').text(firstName);
                localStorage.setItem('First_Name', firstName);
                localStorage.setItem('first_name', firstName);
                $('input[name="First_Name"]').val(firstName);
                $('#First_Name').val(firstName);
                debugLog('First name captured');
            }
            if (lastName) {
                localStorage.setItem('Last_Name', lastName);
                localStorage.setItem('last_name', lastName);
                $('input[name="Last_Name"]').val(lastName);
                $('#Last_Name').val(lastName);
            }
        }

        // Handle date of birth
        if (stepNumber === 5) {
            updateDateOfBirth();
        }

        // Handle income and reason
        if (stepNumber === 6) {
            var income = $('#household_income').val();
            var reason = $('#reason').val();
            if (income) {
                localStorage.setItem('Household_Income', income);
                $('input[name="Household_Income"]').val(income);
            }
            if (reason) {
                localStorage.setItem('Reason', reason);
                $('input[name="Reason"]').val(reason);
            }
        }

        // Handle contact info
        if (stepNumber === 7) {
            var email = $('#email').val();
            var phone = $('#phone').val();
            if (email) {
                localStorage.setItem('Email', email);
                $('input[name="Email"]').val(email);
            }
            if (phone) {
                localStorage.setItem('Primary_Phone', phone);
                $('input[name="Primary_Phone"]').val(phone);
            }
        }

        // Run settings after critical steps
        if (stepNumber >= 5) {
            runSettings();
        }
    }

    /**
     * Update date of birth from separate fields - CRITICAL FIX
     */
    function updateDateOfBirth() {
        var month = $('#birthmonth').val();
        var day = $('#birthday').val();
        var year = $('#birthyear').val();

        if (month && day && year) {
            // Format DOB as MM/DD/YYYY
            var formattedMonth = ('0' + month).slice(-2);
            var formattedDay = ('0' + day).slice(-2);
            var dob = formattedMonth + '/' + formattedDay + '/' + year;

            // Update all DOB fields
            $('#dob').val(dob);
            $('input[name="DOB"]').val(dob);
            localStorage.setItem('DOB', dob);

            debugLog('DOB formatted and saved');

            // Calculate age
            var age = calculateAge(year, month, day);
            $('#age').val(age);
            $('input[name="Age"]').val(age);
            localStorage.setItem('Age', age);

            debugLog('Age calculated');
        }
    }

    /**
     * Calculate age from date parts
     */
    function calculateAge(year, month, day) {
        var today = new Date();
        var birthDate = new Date(year, month - 1, day);
        var age = today.getFullYear() - birthDate.getFullYear();
        var monthDiff = today.getMonth() - birthDate.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        return age;
    }

    /**
     * Run settings for form data processing
     */
    function runSettings() {
        debugLog('Running settings');
        getAge();
        setSRC();
        redirectUrlUpdate();
    }

    /**
     * Get Age From DOB - Ensure proper formatting
     */
    function getAge() {
        var BM = $('#birthmonth').val() || $('select[name="birthmonth"]').val();
        var BD = $('#birthday').val() || $('select[name="birthday"]').val();
        var BY = $('#birthyear').val() || $('select[name="birthyear"]').val();

        if (BM && BD && BY) {
            // Format DOB as MM/DD/YYYY
            var formattedMonth = ('0' + BM).slice(-2);
            var formattedDay = ('0' + BD).slice(-2);
            var DOB = formattedMonth + '/' + formattedDay + '/' + BY;

            $('input[name="DOB"]').val(DOB);
            $('#dob').val(DOB);
            localStorage.setItem('DOB', DOB);

            var today = new Date();
            var birthDate = new Date(DOB);
            var age = today.getFullYear() - birthDate.getFullYear();
            var m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age = age - 1;
            }

            $('input[name="Age"]').val(Math.floor(age));
            $('#age').val(Math.floor(age));
            localStorage.setItem('Age', Math.floor(age));

            debugLog('Age and DOB fields updated');
        }
    }

    /**
     * Set SRC and TYPE based on age and campaign
     */
    function setSRC() {
        var age = $('#age').val() || $('input[name="Age"]').val() || localStorage.getItem('Age');

        debugLog('Setting SRC based on age threshold');

        if (parseInt(age) >= 65) {
            $('input[name="TYPE"]').val('23');
            $('#type').val('23');
            $('input[name="SRC"]').val('WebPostM');
            $('#src').val('WebPostM');
        } else {
            $('input[name="TYPE"]').val('24');
            $('#type').val('24');
            $('#src').val('Infinix-M-Out');
            $('input[name="SRC"]').val('Infinix-M-Out');
        }
    }

    /**
     * Update redirect URL based on user data
     */
    function redirectUrlUpdate() {
        debugLog('Updating redirect URL');

        var redirectUrl = $('input[name="Redirect_URL"]');

        // Get all the data - FIXED first_name capture
        var fname = $('#first_name').val() ||
            $('input[name="First_Name"]').val() ||
            localStorage.getItem('First_Name') ||
            localStorage.getItem('first_name') || '';

        var age = $('#age').val() || $('input[name="Age"]').val() || localStorage.getItem('Age') || '18';
        var city = $('#City').val() || $('input[name="City"]').val() || localStorage.getItem('city') || '';
        var state = $('#State').val() || $('input[name="State"]').val() || localStorage.getItem('state') || '';
        var household = $('#household').val() || $('input[name="Household"]').val() || localStorage.getItem('Household') || '1';
        var income = $('#household_income').val() || $('input[name="Household_Income"]').val() || localStorage.getItem('Household_Income') || '';
        var email = $('#email').val() || $('input[name="Email"]').val() || localStorage.getItem('Email') || '';
        var phone = $('#phone').val() || $('input[name="Primary_Phone"]').val() || localStorage.getItem('Primary_Phone') || '';

        var healthTwoStates = ["AL", "FL", "GA", "KS", "MS", "MO", "NC", "OH", "OK", "SC", "TN", "TX"];
        var lander, type, did;

        // Determine landing page based on age
        if (parseInt(age) >= 65) {
            lander = '../65typ.php';
            did = 'medicare';
            type = 'medicare';
        } else {
            type = 'healthcare';

            // Determine based on state and income
            if ($.inArray(state, healthTwoStates) !== -1) {
                // Map income values to numeric for comparison
                var incomeNumeric = 0;
                if (income === '60K+') incomeNumeric = 60000;
                else if (income === '30-60K') incomeNumeric = 45000;
                else if (income === '<30K') incomeNumeric = 20000;
                else if (income === '55K') incomeNumeric = 55000;
                else incomeNumeric = parseInt(income) || 0;

                if (incomeNumeric > 39999) {
                    lander = '../thank-you/thank-you-h3-b.php';
                    did = 'premium';
                } else {
                    lander = '../thank-you/thank-you-h3-b.php';
                    did = 'h2';
                }
            } else {
                var incomeNumeric = 0;
                if (income === '60K+') incomeNumeric = 60000;
                else if (income === '30-60K') incomeNumeric = 45000;
                else if (income === '<30K') incomeNumeric = 20000;
                else if (income === '55K') incomeNumeric = 55000;
                else incomeNumeric = parseInt(income) || 0;

                if (incomeNumeric > 54999) {
                    lander = '../thank-you/thank-you-h3-b.php';
                    did = 'premium';
                } else {
                    lander = '../thank-you/thank-you-h3-b.php';
                    did = 'standard';
                }
            }
        }

        // Store for later use
        sessionStorage.setItem('did', did);
        sessionStorage.setItem('redirectType', type);

        // Get SRC value
        var src = $('#src').val() || $('input[name="SRC"]').val() || 'Infinix-M-Out';

        // Build redirect URL
        var urlParams = '?type=' + type +
            '&city=' + encodeURIComponent(city) +
            '&state=' + encodeURIComponent(state) +
            '&did=' + did +
            '&Household=' + household +
            '&first_name=' + encodeURIComponent(fname) +
            '&age=' + age +
            '&src=' + btoa(src) +
            '&email=' + encodeURIComponent(email) +
            '&phone=' + encodeURIComponent(phone);

        var fullUrl = lander + urlParams;
        redirectUrl.val(fullUrl);

        debugLog('Redirect URL prepared');
    }

    /**
     * Check for valid state (MA/NY restriction)
     */
    function checkForValidState(state, state_abbr) {
        if (['MA', 'NY'].includes(state_abbr)) {
            $('#msform').before(
                '<div class="alert alert-warning text-center" style="padding-top: 15vh; padding-bottom: 15vh">' +
                '<h2>We\'re Sorry</h2>' +
                '<p>Unfortunately, at this time we do not offer any coverage in the state of ' + state + '.</p>' +
                '</div>'
            );
            $('#msform').slideUp();
            $('#grad1').hide();
        }
    }

    /**
     * Transition between form steps
     */
    function transitionStep(fromStep, toStep) {
        if (animating) {
            debugLog('Animation in progress, skipping transition');
            return false;
        }

        animating = true;

        var $fromStep = $('.form-step[data-step="' + fromStep + '"]');
        var $toStep = $('.form-step[data-step="' + toStep + '"]');

        $fromStep.fadeOut(400, function () {
            $(this).attr('data-active', 'false');
            $toStep.fadeIn(400, function () {
                $(this).attr('data-active', 'true');
                saveCurrentStep(toStep);
                animating = false;
            });
        });

        updateProgressBar(toStep);
        $('html, body').animate({ scrollTop: 0 }, 300);
    }

    /**
     * Update progress bar
     */
    function updateProgressBar(step) {
        $('#progressbar li').removeClass('active');
        for (var i = 0; i <= step; i++) {
            $('#progressbar #step' + i).addClass('active');
        }
    }

    var saveStepTimeout;
    var lastSavedStep = null;

    /**
     * Save current step to session
     */
    function saveCurrentStep(step) {
        if (lastSavedStep === step) {
            debugLog('Step ' + step + ' already saved, skipping');
            return;
        }

        localStorage.setItem('currentStep', step);
        clearTimeout(saveStepTimeout);

        var csrfToken = getCSRFToken();
        if (!csrfToken) {
            console.warn('Cannot save step - no CSRF token available');
            return;
        }

        saveStepTimeout = setTimeout(function () {
            if (lastSavedStep === step) {
                return;
            }

            lastSavedStep = step;

            $.ajax({
                url: '../multi-quote/inc/save-step.php',
                type: 'POST',
                data: {
                    step: step,
                    csrf_token: csrfToken
                },
                success: function (response) {
                    debugLog('Step saved');
                },
                error: function (xhr, status, error) {
                    console.error('Error saving step:', xhr.responseJSON || error);
                    lastSavedStep = null;
                }
            });
        }, 100);
    }

    /**
     * Update location display elements
     */
    function updateLocationDisplay(locationData) {
        if (locationData.city) {
            $('.city-name').text(locationData.city);
        }
        if (locationData.state || locationData.state_name) {
            $('.state-name').text(locationData.state_name || locationData.state);
        }
    }

    /**
     * Populate form data from session/localStorage
     */
    function populateFormData(data) {
        for (var key in data) {
            if (data.hasOwnProperty(key)) {
                var $field = $('[name="' + key + '"]');
                var $fieldById = $('#' + key.toLowerCase());

                if ($field.length) {
                    if ($field.is(':radio')) {
                        $field.filter('[value="' + data[key] + '"]').prop('checked', true).trigger('change');
                    } else if ($field.is('select')) {
                        $field.val(data[key]);
                    } else {
                        $field.val(data[key]);
                    }
                }

                if ($fieldById.length && !$fieldById.is(':radio')) {
                    $fieldById.val(data[key]);
                }

                // Store in localStorage too
                localStorage.setItem(key, data[key]);

                // Update display elements
                if (key === 'First_Name') {
                    $('.name').text(data[key]);
                }
            }
        }
    }

    /**
     * Load data from localStorage
     */
    function loadLocalStorageData() {
        var formFields = [
            'First_Name', 'Last_Name', 'Email', 'Primary_Phone',
            'DOB', 'Gender', 'Household', 'Household_Income',
            'Currently_Insured', 'Urgency', 'Reason',
            'City', 'State', 'Zip', 'Age'
        ];

        formFields.forEach(function (field) {
            var value = localStorage.getItem(field);
            if (value) {
                // Try both name and ID selectors
                var $field = $('[name="' + field + '"]');
                var $fieldById = $('#' + field.toLowerCase());

                if ($field.length) {
                    $field.val(value);
                }
                if ($fieldById.length) {
                    $fieldById.val(value);
                }
            }
        });

        // Update name display
        var firstName = localStorage.getItem('First_Name') || localStorage.getItem('first_name');
        if (firstName) {
            $('.name').text(firstName);
        }
    }

    /**
     * Save field to localStorage
     */
    function saveFieldToLocalStorage(name, value) {
        if (name && value) {
            localStorage.setItem(name, value);
        }
    }

    /**
     * Validate phone number
     */
    function validatePhoneNumber(phone) {
        var cleaned = phone.replace(/\D/g, '');

        var tollFree = ["800", "822", "833", "844", "855", "866", "877", "880", "887", "888", "889"];
        if (tollFree.indexOf(cleaned.substr(0, 3)) >= 0) {
            return false;
        }

        var fullyRepeated = /(\d)\1{9}/;
        if (fullyRepeated.test(cleaned)) {
            return false;
        }

        var prefixRepeated = /(\d)\1{2}/;
        if (prefixRepeated.test(cleaned.substr(3, 3))) {
            return false;
        }

        return cleaned.length === 10;
    }

    /**
     * Show phone error message
     */
    function showPhoneError() {
        var $phoneField = $('input[name="Primary_Phone"]');
        $phoneField.removeClass('parsley-success').addClass('parsley-error');

        if (!$('#phone-error-message').length) {
            $phoneField.after(
                '<ul class="parsley-errors-list filled" id="phone-error-message">' +
                '<li class="parsley-custom-error-message">' +
                'The phone number you entered appears to be invalid.<br>' +
                'Please enter a valid 10-digit phone number.' +
                '</li></ul>'
            );
        }

        $phoneField.val('').focus();
    }

    /**
     * Show restricted state message
     */
    function showRestrictedStateMessage(message) {
        $('#msform').before(
            '<div class="alert alert-warning text-center" role="alert">' +
            '<h3>Service Not Available</h3>' +
            '<p>' + (message || 'Unfortunately, we do not offer coverage in your state at this time.') + '</p>' +
            '<p>If you believe this is an error, please try again with a different ZIP code.</p>' +
            '</div>'
        );
        $('#msform').slideUp();
    }

    /**
     * Submit form
     */
    function submitForm() {
        debugLog('Starting form submission');

        // CRITICAL: Ensure all data is captured before submission
        gatherAllFormData();

        // Update all derived fields
        runSettings();

        // Show loading modal
        if (window.OHPLoadingModal && typeof window.OHPLoadingModal.show === 'function') {
            window.OHPLoadingModal.show();
            if (typeof window.OHPLoadingModal.setText === 'function') {
                window.OHPLoadingModal.setText('Verifying Your Information');
            }
        } else {
            $('#loading').modal('show');
        }

        // Get phone number
        var phoneNumber = $('input[name="Primary_Phone"]').val() || $('#phone').val();
        phoneNumber = phoneNumber.replace(/\D/g, '');
        sessionStorage.setItem('userPhone', phoneNumber);

        // Check for Flex Media parameters
        if (localStorage.getItem('Sub_ID2')) {
            sessionStorage.setItem('hid', localStorage.getItem('Sub_ID2'));
            sessionStorage.setItem('flex_lead', 'true');
        }

        // Insert TrustedForm token if available
        var trustedFormCert = $('input[name="xxTrustedFormToken"]').val();
        if (trustedFormCert) {
            $('#LeadiD_URL').val(trustedFormCert);
            $('input[name="LeadiD_URL"]').val(trustedFormCert);
        }

        // Prepare form data
        var formData = $form.serialize();

        // Store response for later use
        var formResponse = null;
        var responseStatus = null;

        // Submit form
        $.ajax({
            url: '../multi-quote/inc/form-processing.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                formResponse = response;
                responseStatus = 'success';

                // Try to parse Boberdoo response
                try {
                    var boberdooResponse = response.boberdoo_response || response;
                    if (boberdooResponse && boberdooResponse.responseText) {
                        handleBoberdooResponse(boberdooResponse.responseText);
                    }
                } catch (e) {
                    debugLog('Could not parse Boberdoo response');
                }
            },
            error: function (xhr, status, error) {
                formResponse = xhr.responseJSON || {};
                responseStatus = 'error';

                // Try to handle error response
                if (xhr.responseText) {
                    handleBoberdooResponse(xhr.responseText);
                }
            }
        });

        function onProgressDone() {
            // Progress complete, now handle the response
            if (formResponse !== null) {
                handleFormResponse(formResponse, responseStatus);
                return;
            }

            // If response hasn't come back yet, wait for it
            var checkResponse = setInterval(function () {
                if (formResponse !== null) {
                    clearInterval(checkResponse);
                    handleFormResponse(formResponse, responseStatus);
                }
            }, 100);
        }

        // Start progress animation with completion callback
        if (window.OHPLoadingModal && typeof window.OHPLoadingModal.startProgress === 'function') {
            window.OHPLoadingModal.startProgress({ onDone: onProgressDone });
        } else {
            showProgressAnimation(onProgressDone);
        }
    }

    /**
     * Gather all form data before submission - ENHANCED VERSION
     */
    function gatherAllFormData() {
        debugLog('Gathering form data before submit');

        // CRITICAL: Ensure DOB is built from components
        var BM = $('#birthmonth').val() || $('select[name="birthmonth"]').val() || localStorage.getItem('birthmonth');
        var BD = $('#birthday').val() || $('select[name="birthday"]').val() || localStorage.getItem('birthday');
        var BY = $('#birthyear').val() || $('select[name="birthyear"]').val() || localStorage.getItem('birthyear');

        if (BM && BD && BY) {
            // Format DOB as MM/DD/YYYY
            var formattedMonth = ('0' + BM).slice(-2);
            var formattedDay = ('0' + BD).slice(-2);
            var DOB = formattedMonth + '/' + formattedDay + '/' + BY;

            // Update ALL DOB fields
            $('input[name="DOB"]').val(DOB);
            $('#dob').val(DOB);
            localStorage.setItem('DOB', DOB);

            debugLog('DOB rebuilt from components');
        }

        // Get all visible inputs from the form
        $('#msform').find('input:visible, select:visible, textarea:visible').each(function () {
            var $el = $(this);
            var name = $el.attr('name');
            var id = $el.attr('id');
            var value = $el.val();

            if (value && name) {
                // Find matching hidden field and update it
                $('input[type="hidden"][name="' + name + '"]').val(value);

                // Also store in localStorage
                localStorage.setItem(name, value);
            }

            if (value && id) {
                // Update by ID as well
                $('#' + id).val(value);
                localStorage.setItem(id, value);
            }
        });

        // CRITICAL FIX: Explicitly handle First_Name
        var firstName = $('#first_name').val() ||
            localStorage.getItem('First_Name') ||
            localStorage.getItem('first_name');

        if (firstName) {
            $('input[name="First_Name"]').val(firstName);
            $('#First_Name').val(firstName);
            debugLog('First name synchronized to hidden fields');
        }

        // Explicitly set critical fields from localStorage if they're missing
        var criticalFields = {
            'First_Name': firstName || $('#first_name').val(),
            'Last_Name': localStorage.getItem('Last_Name') || $('#last_name').val(),
            'Email': localStorage.getItem('Email') || $('#email').val(),
            'Primary_Phone': localStorage.getItem('Primary_Phone') || $('#phone').val(),
            'Age': localStorage.getItem('Age') || $('#age').val(),
            'DOB': localStorage.getItem('DOB') || $('#dob').val(),
            'Gender': localStorage.getItem('Gender') || $('#gender').val(),
            'City': localStorage.getItem('city') || $('#City').val(),
            'State': localStorage.getItem('state') || $('#State').val(),
            'Zip': localStorage.getItem('zip') || $('#Zip').val(),
            'Household': localStorage.getItem('Household') || $('#household').val() || '1',
            'Household_Income': localStorage.getItem('Household_Income') || $('#household_income').val(),
            'Currently_Insured': localStorage.getItem('Currently_Insured') || $('#insured').val(),
            'Urgency': localStorage.getItem('Urgency') || $('#urgency').val(),
            'Reason': localStorage.getItem('Reason') || $('#reason').val()
        };

        // Update all hidden fields with gathered data
        for (var fieldName in criticalFields) {
            if (criticalFields[fieldName]) {
                $('input[name="' + fieldName + '"]').val(criticalFields[fieldName]);
                $('#' + fieldName.toLowerCase()).val(criticalFields[fieldName]);
            }
        }
    }

    /**
     * Handle Boberdoo response
     */
    function handleBoberdooResponse(responseText) {
        try {
            var $response = $(responseText);
            var status = $response.find('status').text();
            var error = $response.find('error').text();
            var leadID = $response.find('lead_id').text();

            var statusLength = status.length;
            var errorLength = error.length;
            var leadIDLength = leadID.length;

            var statusFinal = status.slice(0, statusLength / 2);
            var errorFinal = error.slice(0, errorLength / 2);
            var leadIDFinal = leadID.slice(0, leadIDLength / 2);

            var message = '';
            if (errorLength > 1) {
                message = ' Message: ' + errorFinal;
            }
            if (leadIDLength > 1) {
                message = ' Lead ID: ' + leadIDFinal;
            }

            var responseMessage = 'Status: ' + statusFinal + '   ' + message;
            ajaxInsert(responseMessage);
        } catch (e) {
            debugLog('Error parsing Boberdoo response');
            ajaxInsert('Processing complete');
        }
    }

    /**
     * Insert confirmation to database
     */
    function ajaxInsert(response) {
        var firstName = $('input[name="First_Name"]').val() ||
            localStorage.getItem('First_Name') ||
            localStorage.getItem('first_name');

        $.ajax({
            type: 'POST',
            url: 'https://affordable-healthcare.com/get-quotes/inc/insert_confirmation.php',
            data: 'first_name=' + firstName +
                '&email=' + ($('input[name="Email"]').val() || localStorage.getItem('Email')) +
                '&ipaddress=' + $('input[name="IP_Address"]').val() +
                '&error=' + response,
            success: function (data) {
                sessionStorage.setItem('entryStatus', 'success');
                debugLog('Entry status:', sessionStorage.getItem('entryStatus'));

                if (sessionStorage.getItem('flex_lead') == 'true') {
                    flexPostback();
                } else {
                    completeRedirect();
                }
            },
            error: function (data, jqXHR, textStatus, errorThrown) {
                sessionStorage.setItem('entryStatus', 'error');
                debugLog('Entry status:', sessionStorage.getItem('entryStatus'));

                if (sessionStorage.getItem('flex_lead') == 'true') {
                    flexPostback();
                } else {
                    completeRedirect();
                }
            }
        });
        return false;
    }

    /**
     * Flex Media postback
     */
    function flexPostback() {
        $.ajax({
            url: 'https://affordable-healthcare.com/fc/inc/postback-flex.php',
            type: "POST",
            data: {
                hid: sessionStorage.getItem('hid'),
                sid: '31917',
                transid: sessionStorage.getItem('userPhone')
            },
            dataType: "json",
            success: function (data) {
                sessionStorage.setItem('flexStatus', 'success');
                debugLog('Flex postback status:', sessionStorage.getItem('flexStatus'));
                completeRedirect();
            },
            error: function (data, jqXHR, textStatus, errorThrown) {
                sessionStorage.setItem('flexStatus', 'error');
                debugLog('Flex postback status:', sessionStorage.getItem('flexStatus'));
                completeRedirect();
            }
        });
        return false;
    }

    /**
     * Complete redirect to thank you page
     */
    function completeRedirect() {
        // Ensure redirect URL is updated with latest data
        gatherAllFormData();
        redirectUrlUpdate();

        var redirectUrl = $('input[name="Redirect_URL"]').val();

        // If still no redirect URL, build one with all available data
        if (!redirectUrl || redirectUrl.indexOf('first_name=&') > -1) {
            var firstName = localStorage.getItem('First_Name') ||
                localStorage.getItem('first_name') ||
                $('#first_name').val() ||
                $('#First_Name').val() || '';

            var params = {
                type: sessionStorage.getItem('redirectType') || 'healthcare',
                city: localStorage.getItem('city') || $('#City').val() || '',
                state: localStorage.getItem('state') || $('#State').val() || '',
                age: localStorage.getItem('Age') || $('#age').val() || '18',
                first_name: firstName,
                did: sessionStorage.getItem('did') || 'standard',
                Household: localStorage.getItem('Household') || $('#household').val() || '1',
                src: btoa($('#src').val() || 'Infinix-M-Out')
            };

            debugLog('Building fallback redirect URL');

            var queryString = $.param(params);
            redirectUrl = '../thank-you/thank-you-h3-b.php?' + queryString;
        }

        window.location = redirectUrl;
    }

    /**
     * Show progress animation
     */
    function showProgressAnimation(onComplete) {
        const steps = [
            {
                title: 'Verifying Your Information',
                substeps: ['Validating personal details', 'Checking data integrity', 'Confirming information'],
                lightColor: '#005D75',
                darkColor: '#00B9E9'
            },
            {
                title: 'Submitting Your Application',
                substeps: ['Preparing submission', 'Uploading data', 'Confirming receipt'],
                lightColor: '#B2282E',
                darkColor: '#FF525A'
            },
            {
                title: 'Finding Best Plans',
                substeps: ['Analyzing requirements', 'Searching database', 'Comparing options', 'Selecting matches'],
                lightColor: '#FF8300',
                darkColor: '#FCB945'
            },
            {
                title: 'Finalizing Application',
                substeps: ['Processing application', 'Final validation', 'Generating results'],
                lightColor: '#1B8335',
                darkColor: '#8BC34A'
            },
            {
                title: 'Done! Your information has been received!',
                substeps: ['Application complete'],
                lightColor: '#2E7D32',
                darkColor: '#8BC34A'
            }
        ];

        // Rebuild SVG with pre-created arc segments
        const container = document.querySelector('.circle-container');
        if (!container) {
            console.error('Circle container not found');
            if (typeof onComplete === 'function') {
                setTimeout(onComplete, 1000);
            }
            return;
        }

        container.innerHTML = `
                <svg class="circle-svg" viewBox="0 0 200 200">
                    <!-- Background circle -->
                    <circle cx="100" cy="100" r="90" fill="none" stroke="#e0e0e0" stroke-width="20"/>
                    
                    <!-- Pre-create all segment paths -->
                    <g id="progress-segments" transform="rotate(-90 100 100)"></g>
                </svg>
                <div class="ticks"></div>
            `;

        let currentStep = 0;
        let currentSubstep = 0;
        const radius = 90;
        const ticksContainer = document.querySelector('.ticks');
        const progressSegments = document.getElementById('progress-segments');

        // Create arc path string
        function createArcPathString(startAngle, endAngle, radius) {
            const startRad = (startAngle * Math.PI) / 180;
            const endRad = (endAngle * Math.PI) / 180;

            const x1 = 100 + radius * Math.cos(startRad);
            const y1 = 100 + radius * Math.sin(startRad);
            const x2 = 100 + radius * Math.cos(endRad);
            const y2 = 100 + radius * Math.sin(endRad);

            const largeArc = endAngle - startAngle > 180 ? 1 : 0;

            return `M ${x1} ${y1} A ${radius} ${radius} 0 ${largeArc} 1 ${x2} ${y2}`;
        }

        // Pre-create all segment paths (both light and dark)
        const segmentPaths = [];
        for (let i = 0; i < 5; i++) {
            const startAngle = i * 72;
            const endAngle = (i + 1) * 72;
            const pathString = createArcPathString(startAngle, endAngle, radius);

            // Calculate path length for this segment
            const arcLength = (72 / 360) * 2 * Math.PI * radius;

            // Light path for substeps
            const lightPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            lightPath.setAttribute('d', pathString);
            lightPath.setAttribute('class', 'segment-path');
            lightPath.style.stroke = steps[i].lightColor;
            lightPath.style.strokeDasharray = arcLength;
            lightPath.style.strokeDashoffset = arcLength; // Start hidden
            progressSegments.appendChild(lightPath);

            // Dark path for completed step
            const darkPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            darkPath.setAttribute('d', pathString);
            darkPath.setAttribute('class', 'segment-path');
            darkPath.style.stroke = steps[i].darkColor;
            darkPath.style.strokeDasharray = arcLength;
            darkPath.style.strokeDashoffset = arcLength; // Start hidden
            progressSegments.appendChild(darkPath);

            segmentPaths.push({
                light: lightPath,
                dark: darkPath,
                length: arcLength
            });
        }

        // Add tick marks
        function addAllTicks() {
            steps.forEach((step, stepIndex) => {
                const stepStartPercent = (stepIndex / 5) * 100;
                const stepSizePercent = 20;

                step.substeps.forEach((substep, subIndex) => {
                    const substepFraction = (subIndex + 1) / step.substeps.length;
                    const tickPercent = stepStartPercent + (substepFraction * stepSizePercent);
                    addTick(tickPercent, 'substep');
                });

                const mainTickPercent = ((stepIndex + 1) / 5) * 100;
                addTick(mainTickPercent, 'main');
            });
        }

        function addTick(percent, type) {
            const angle = (percent / 100) * 360;
            const radian = (angle * Math.PI) / 180;

            const x = 100 + radius * Math.cos(radian - Math.PI / 2);
            const y = 100 + radius * Math.sin(radian - Math.PI / 2);

            const tick = document.createElement('div');
            tick.className = `tick ${type}-tick`;

            const tickHeight = type === 'main' ? 12 : 8;
            const tickWidth = type === 'main' ? 3 : 2;

            tick.style.left = `${x - tickWidth / 2}px`;
            tick.style.top = `${y - tickHeight / 2}px`;
            tick.style.transform = `rotate(${angle}deg)`;

            ticksContainer.appendChild(tick);
        }

        addAllTicks();

        function animate() {
            if (currentStep >= 5) {
                return;
            }

            const step = steps[currentStep];
            const substepsInCurrentStep = step.substeps.length;
            const segment = segmentPaths[currentStep];

            const titleEl = document.querySelector('.step-title');
            const substepEl = document.querySelector('.substep-title');

            if (titleEl) titleEl.textContent = step.title;
            if (substepEl && currentSubstep < substepsInCurrentStep) {
                substepEl.textContent = step.substeps[currentSubstep];
            }

            // Calculate how much of the segment to reveal
            const substepFraction = (currentSubstep + 1) / substepsInCurrentStep;
            const revealAmount = segment.length * (1 - substepFraction);

            // Smoothly animate the light path
            segment.light.style.strokeDashoffset = revealAmount;

            currentSubstep++;

            if (currentSubstep >= substepsInCurrentStep) {
                setTimeout(() => {
                    // Reveal the dark path to overlay
                    segment.dark.style.strokeDashoffset = 0;

                    currentStep++;
                    currentSubstep = 0;

                    if (currentStep < 5) {
                        setTimeout(animate, 400);
                    } else {
                        if (substepEl) substepEl.textContent = 'Complete!';
                        setTimeout(function () {
                            if (typeof onComplete === 'function') {
                                onComplete();
                            }
                        }, 300);
                    }
                }, 200);
            } else {
                setTimeout(animate, 400);
            }
        }

        setTimeout(animate, 0);
    }

    /**
     * Handle form response
     */
    function handleFormResponse(response, status) {
        sessionStorage.setItem('entryStatus', status);

        // One more attempt to ensure data is captured
        gatherAllFormData();

        // Update redirect URL with all necessary data
        redirectUrlUpdate();

        // Get redirect URL
        var redirectUrl = $('input[name="Redirect_URL"]').val();

        // If still missing data, try one more time
        if (!redirectUrl || redirectUrl.indexOf('first_name=&') > -1 || redirectUrl.indexOf('city=&') > -1) {
            var firstName = localStorage.getItem('First_Name') ||
                localStorage.getItem('first_name') ||
                $('#first_name').val() ||
                $('#First_Name').val() || '';

            var params = {
                type: sessionStorage.getItem('redirectType') || 'healthcare',
                city: localStorage.getItem('city') || $('#City').val() || '',
                state: localStorage.getItem('state') || $('#State').val() || '',
                age: localStorage.getItem('Age') || $('#age').val() || '18',
                first_name: firstName,
                did: sessionStorage.getItem('did') || 'standard',
                Household: localStorage.getItem('Household') || '1',
                src: btoa($('#src').val() || 'Infinix-M-Out'),
                email: localStorage.getItem('Email') || $('#email').val() || '',
                phone: localStorage.getItem('Primary_Phone') || $('#phone').val() || ''
            };

            var queryString = $.param(params);
            redirectUrl = '../thank-you/thank-you-h3-b.php?' + queryString;
        }

        // Clear localStorage after successful submission
        if (status === 'success') {
            localStorage.clear();
        }

        // Check if we need to do Flex postback
        if (sessionStorage.getItem('flex_lead') === 'true') {
            setTimeout(function () {
                flexPostback();
            }, 1000);
        } else if (response && response.success === false && response.error) {
            ajaxInsert(response.error);
        } else {
            // Direct redirect after delay
            setTimeout(function () {
                window.location.href = redirectUrl;
            }, 2000);
        }
    }

    /**
     * Initialize input masks
     */
    $('.phone').inputmask("(999) 999-9999");

    /**
     * Handle input changes for real-time saving
     */
    $form.find('input, select, textarea').on('change blur', function () {
        var $field = $(this);
        var name = $field.attr('name');
        var value = $field.val();

        if (name && value) {
            saveFieldToLocalStorage(name, value);

            // Also update any matching hidden fields immediately
            $('input[type="hidden"][name="' + name + '"]').val(value);
        }
    });

    /**
     * Monitor First Name field specifically - ENHANCED
     */
    $('#first_name').on('change keyup blur input', function () {
        var value = $(this).val();
        if (value) {
            $('.name').text(value);
            $('input[name="First_Name"]').val(value);
            $('#First_Name').val(value);
            localStorage.setItem('First_Name', value);
            localStorage.setItem('first_name', value);
            debugLog('First name field updated');
        }
    });

    /**
     * Monitor date fields for proper DOB formatting
     */
    $('#birthmonth, #birthday, #birthyear').on('change', function () {
        var month = $('#birthmonth').val();
        var day = $('#birthday').val();
        var year = $('#birthyear').val();

        // Store individual components
        if (month) localStorage.setItem('birthmonth', month);
        if (day) localStorage.setItem('birthday', day);
        if (year) localStorage.setItem('birthyear', year);

        // Build DOB if all components are present
        if (month && day && year) {
            var formattedMonth = ('0' + month).slice(-2);
            var formattedDay = ('0' + day).slice(-2);
            var dob = formattedMonth + '/' + formattedDay + '/' + year;

            $('#dob').val(dob);
            $('input[name="DOB"]').val(dob);
            localStorage.setItem('DOB', dob);
            debugLog('DOB field updated');
        }
    });

    /**
     * Prevent form submission on Enter key except for submit button
     */
    $form.on('keypress', function (e) {
        if (e.which === 13 && !$(e.target).is('.submit')) {
            e.preventDefault();
            return false;
        }
    });

    /**
     * String prototype for capitalization
     */
    String.prototype.capitalize = function () {
        return this.toLowerCase().replace(/\b\w/g, function (m) {
            return m.toUpperCase();
        });
    };
});