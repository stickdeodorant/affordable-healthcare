<?php session_start();
include 'inc/header.php';
$_SESSION['test'] = "";
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
	$ip = $_SERVER['HTTP_CLIENT_IP'];
} else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
	$ip = $_SERVER['REMOTE_ADDR'];
}
?>
	<style type="text/css">
	.has-error .form-control { border-color: #ed4b82; }
	.has-error .help-block, .has-error .control-label, .has-error .radio, .has-error .checkbox, .has-error .radio-inline, .has-error .checkbox-inline, .has-error.radio label, .has-error.checkbox label, .has-error.radio-inline label, .has-error.checkbox-inline label { color: #ed4b82; }
	label { line-height: 36px; }
	.help-block { margin-bottom: -24px; white-space: nowrap; }
	.form-group + .form-group .help-block, label:not(:first-child) + .form-group .help-block { right: 0; left: auto; }
	label:first-child { text-align: right; left: -15px; }
	label { /*color: #728c98;*/ color: #000; }
	.form-control { height: 40px !important; box-shadow: none; }
	.form-control:focus::-webkit-input-placeholder { color: transparent; }
	.form-control:focus::-moz-placeholder { color: transparent; }
	.form-control:focus:-ms-input-placeholder { color: transparent; }
	.form-control:focus:-moz-placeholder { color: transparent; }
	@media only screen and (max-width: 767px) {
		.alert-warning { display: none !important; }
		label[for="full_name"] + .form-group.has-error + .form-group .help-block { display: none !important; }
		/*label,
		.form-group,
		.form-control {
			display: block !important;
			width: 100% !important;
		}*/
		.help-block {
		    position: absolute;
		    font-size: 11px;
		    left: 1px;
		    bottom: 5px;
		    font-weight: 700;
		}
		.page-title h4 {
			font-size: 14px;
			margin: 0;
		}
		.page-title {
			padding: 20px 0 !important;
			background: #efefef !important;
			font-size: 14px !important;
			margin-bottom: 23px !important;
		}
		.row {
			margin-bottom: 24px !important;
		}
		.last-field {
			padding-right: 0;
			margin: 0;
			padding: 0;
			display: block;
			clear: both;
			padding-top: 20px;
		}
			}
	@media only screen and (max-width: 399px) {
		select { font-size: 90%; }
	}
	</style>
	<!-- Main jumbotron for a primary marketing message or call to action -->
	<div class="jumbotron <?php echo 'state-' . strtolower($_GET['state']); ?> blue-gradient hidden-xs">
		<div class="container text-center">
			<h2>Affordable plans <span class="location-text hidden-xs hidden-sm"></span><br class="visible-xs-inline">that work for you! </h2>
			<p style="font-size:18px;">We make it easy <br class="visible-xs-inline">with free quotes.</p>
			<div class="stepprogress step-2">
				<div class="stepprogress-bar"><span class="hidden-xs">Step 1 — </span>Enter Zip</div><div class="stepprogress-bar"><span class="hidden-xs">Step 2 — </span>Quote</div><div class="stepprogress-bar"><span class="hidden-xs">Step 3 — </span>Compare</div>
			</div>
		</div>
	</div>
	<div class="get-info container style-1">
		<div class="page-title text-center">
			<h4><i class="far fa-pencil" style="margin-right: 10px;"></i>Please fill in all fields</h4>
			<div class="alert alert-warning" role="alert" style="display: none;">There are some errors below</div>
		</div>
		<div id="form">
			<form id="forms" enctype="application/json" method="POST">
				<?php
				require_once __DIR__ . '/../inc/feature-flags.php';
				if (empty($featureFlags['enable_legacy_pages'])) {
					http_response_code(404);
					exit;
				}
				$_SESSION['name_first'] = $_POST['First_Name'];
				$_SESSION['name_last'] = $_POST['Last_Name'];
				$_SESSION['name_full'] = $_POST['First_Name'] . " " . $_POST['Last_Name'];
				?>
				<!-- <input type="hidden" name="Test_Lead" value="1"> -->
				<input type="hidden" name="TYPE" id="type" value="24">
				<?php if(isset($_GET['gclid_'])): ?>
				<input type="hidden" name="gclid" id="gclid_field" value="<?php echo $_GET['gclid_']; ?>">
				<?php endif; ?>
				<input type="hidden" name="IP_Address" value="<?php echo $ip; ?>">
				<input type="hidden" name="SRC" id="src" value="Infinix-K-Ping">
				<input type="hidden" name="Pub_ID" value="">
				<input type="hidden" name="Sub_ID" value="<?php echo substr(htmlentities($_GET['keyword']),0,100); ?>">
				<input type="hidden" name="Search_Engine" value="<?php echo substr(htmlentities($_GET['engine']),0,100); ?>">
				<input type="hidden" name="Landing_Page" value="<?php echo ($_GET['page']) ? $_GET['page'] : 'https://' . $domain . '/'?>">
				<input type="hidden" class="LandingPageId" name="LandingPageId" value="<?php echo $pivot_lpid; ?>">
				<input type="hidden" name="Redirect_URL" value="/get-quotes/plans.php" /></script>
				<input type="hidden" name="LeadiD_URL" id="LeadiD_URL" value="" /></script>
					<div class="row">
						<div class="col-sm-6 padding-left">
							<div class="row">
								<label for="pre-existing" id="#pre-ex" class="nopadding">Pre‑existing Conditions</label>
								<div class="form-group inline-list">
									<input type="radio" name="Preexisting_Condition" value="no" required checked> No
									<input type="radio" name="Preexisting_Condition" value="yes" class="ml-2" required> Yes
								</div>
							</div>
							<div class="row">
								<label class="col-xs-3 nopadding">Insuring</label>
								<div class="form-group col-xs-9 nopadding">
									<select id="household" name="Household" class="household form-control select-filled" required="" aria-required="true">
										<option value="1" selected>1 person</option>
										<option value="2">2 people</option>
										<option value="3">Family (3 or more)</option>
									</select>
								</div>
							</div>
							<div class="row">
								<label for="full_name" class="col-xs-3 nopadding">First Name</label>
								<div class="form-group col-xs-9 nopadding">
									<input name="First_Name" type="text" class="form-control" placeholder="First Name" autocomplete="given-name" required minlength="2" maxlength="100">
								</div>
							</div>
							<div class="row">
								<label for="full_name" class="col-xs-3 nopadding">Last Name</label>
								<div class="form-group col-xs-9 nopadding">
									<input name="Last_Name" type="text" class="form-control" placeholder="Last Name" autocomplete="family-name" required minlength="2" maxlength="100">
								</div>
							</div>
							<div class="row">
								<label class="col-xs-3 nopadding">Phone</label>
								<div class="form-group col-xs-9 nopadding">
									<input name="Primary_Phone" type="tel" class="phone form-control" placeholder="5555555555" autocomplete="tel" required>
								</div>
							</div>
							<div class="row">
								<label class="col-xs-3 nopadding">Email</label>
								<div class="form-group col-xs-9 nopadding">
									<input id="email" name="Email" v-model="email" type="email" class="form-control" placeholder="name@email.com" autocomplete="email" required>
								</div>
							</div>
						</div>
						<div class="col-sm-6 padding-right">
							<div class="row">
								<label class="col-xs-3 nopadding">Household</label>
								<div class="form-group col-xs-9 nopadding">
									<select id="household-income" name="Household_Income" class="household-income form-control" required="" aria-required="true">
										<option value="">Income</option>
										<option value="11999">Below $12,000</option>
										<option value="28999">$12,000 - $28,999</option>
										<option value="46999">$29,000 - $46,999</option>
										<option value="99999">$47,000 and Over</option>
									</select>
								</div>
							</div>
							<div class="row">
								<label class="col-xs-3 nopadding">Age</label>
								<div class="form-group col-xs-9 nopadding">
									<select id="age" name="Age" class="age form-control" required>
										<option value="">Select</option>
										<?php for($age = 18; $age <= 100; $age++): ?><option value="<?php echo $age;?>"><?php echo $age; ?></option><?php endfor; ?>
									</select>
								</div>
							</div>
							<div id="dob" class="row hidden dob">
								<label for="full_name" class="col-xs-3 nopadding">Birth</label>
								<div id="mob-container" class="form-group col-xs-5 nopadding">
									<select id="mob" name="birthmonth" required class="form-control abbr-select-xs">
										<option value="" disabled="disabled" selected hidden style="display: none !important;">Month</option>
										<option value="01">January</option>
										<option value="02">February</option>
										<option value="03">March</option>
										<option value="04">April</option>
										<option value="05">May</option>
										<option value="06">June</option>
										<option value="07">July</option>
										<option value="08">August</option>
										<option value="09">September</option>
										<option value="10">October</option>
										<option value="11">November</option>
										<option value="12">December</option>
									</select>
								</div>
								<div class="form-group col-xs-3 col-xs-push-0 col-sm-push-1 nopadding">
									<select id="yob" name="birthyear" required class="form-control">
										<option value="" disabled="disabled" selected hidden style="display: none !important;">Year</option>
										<option value="1957">1957</option>
										<option value="1956">1956</option>
										<option value="1955">1955</option>
										<option value="1954">1954</option>
										<option value="1953">1953</option>
										<option value="1952">1952</option>
										<option value="1951">1951</option>
										<option value="1950">1950</option>
										<option value="1949">1949</option>
									</select>
								</div>
								<div class="form-group nopadding hidden">
									<input type="hidden" value="12/23/1800" name="DOB" v-model="birthDate" maxlength="10" v-on="keyup: formatDate" class="date form-control" placeholder="mm/dd/yyyy" required>
								</div>
							</div>
							<div class="row">
								<label class="col-xs-3 nopadding">Address</label>
								<div class="form-group col-xs-9 nopadding">
									<input name="Address" type="text" placeholder="Address" autocomplete="address-line1" class="form-control" required maxlength="100">
								</div>
							</div>
							<div class="row">
								<label class="col-xs-3 nopadding">City</label>
								<div class="form-group col-xs-9 nopadding">
									<input name="city" id="city" type="text" class="form-control" placeholder="City" autocomplete="address-level2" required maxlength="100" style="text-transform: capitalize;">
								</div>
							</div>
							<div class="row">
								<label class="col-xs-3 nopadding">State</label>
								<div class="form-group col-xs-4 nopadding">
									<select name="state" id="state" required class="form-control" autocomplete="address-level1">
										<option value="">Select</option>
										<option value="AL">Alabama</option>
										<option value="AK">Alaska</option>
										<option value="AZ">Arizona</option>
										<option value="AR">Arkansas</option>
										<option value="CA">California</option>
										<option value="CO">Colorado</option>
										<option value="CT">Connecticut</option>
										<option value="DE">Delaware</option>
										<option value="DC">District Of Columbia</option>
										<option value="FL">Florida</option>
										<option value="GA">Georgia</option>
										<option value="HI">Hawaii</option>
										<option value="ID">Idaho</option>
										<option value="IL">Illinois</option>
										<option value="IN">Indiana</option>
										<option value="IA">Iowa</option>
										<option value="KS">Kansas</option>
										<option value="KY">Kentucky</option>
										<option value="LA">Louisiana</option>
										<option value="ME">Maine</option>
										<option value="MD">Maryland</option>
										<option value="MA">Massachusetts</option>
										<option value="MI">Michigan</option>
										<option value="MN">Minnesota</option>
										<option value="MS">Mississippi</option>
										<option value="MO">Missouri</option>
										<option value="MT">Montana</option>
										<option value="NE">Nebraska</option>
										<option value="NV">Nevada</option>
										<option value="NH">New Hampshire</option>
										<option value="NJ">New Jersey</option>
										<option value="NM">New Mexico</option>
										<option value="NY">New York</option>
										<option value="NC">North Carolina</option>
										<option value="ND">North Dakota</option>
										<option value="OH">Ohio</option>
										<option value="OK">Oklahoma</option>
										<option value="OR">Oregon</option>
										<option value="PA">Pennsylvania</option>
										<option value="RI">Rhode Island</option>
										<option value="SC">South Carolina</option>
										<option value="SD">South Dakota</option>
										<option value="TN">Tennessee</option>
										<option value="TX">Texas</option>
										<option value="UT">Utah</option>
										<option value="VT">Vermont</option>
										<option value="VA">Virginia</option>
										<option value="WA">Washington</option>
										<option value="WV">West Virginia</option>
										<option value="WI">Wisconsin</option>
										<option value="WY">Wyoming</option>
									</select>
								</div>
								<label class="col-xs-2 nopadding text-center">Zip</label>
								<div class="form-group col-xs-3 nopadding">
									<input name="Zip" type="tel" value="<?php echo htmlentities($_GET['zip']); ?>" class="form-control" placeholder="Zip" autocomplete="postal-code" required minlength="5" maxlength="5">
								</div>
							</div>
						</div>
					</div>
					<center>
						<p style="width: 100%;">
							<button class="btn btn-green" name="submit" id="submit"><i class="ti ti-lock" style="margin-right: 10px;"></i>Find Plans</button>
						</p>
					</center>
					<div class="modal fade" id="loading" data-backdrop="false">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title">Finding the best quotes for you.</h5>
									<span aria-hidden="true">&times;</span>
									</button>
								</div>
								<div class="modal-body">
									<img src="img/loading.gif">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
	</div>
	<center>
		<p style="width: 80%; color: #728c98;">
			<small>
				By submitting this form I verify that I have read and accept the <a data-toggle="modal" data-target="#policyModal" style="font-weight: bold; color: inherit !important; cursor: pointer;">Privacy Policy</a> and <a data-toggle="modal" data-target="#termsModal" style="font-weight: bold; color: inherit !important; cursor: pointer;">Terms of Use</a> and provide written consent via electronic signature to receive communications via automatic telephone dialing system or by artificial/pre-recorded message, email or by text message from multiple insurance companies or their agents, this website, and <a data-toggle="modal" data-target="#partnerModal" style="font-weight: bold; color: inherit !important; cursor: pointer;">partner companies</a> at the telephone number above, including my wireless number if provided. I understand that my consent is not required as a condition of purchasing any goods or services. Your carrier's message and data rates may apply.
			</small>
		</p>
		<p style="width: 70%; color: #728c98;">
			<small style="font-size: 70%;">
				<?php if(in_array($_SESSION['state_abbr'], ['NY', 'MA', 'MN'])) { ?>

					<?=$sitename?> is owned and operated by Michael Whitney, a licensed insurance agent. Invitations for applications for insurance on Health-Insurance.com are made only where licensed and appointed. License information for all states can be found <a data-toggle="modal" data-target="#licensesModal" style="font-weight: bold; color: inherit !important; cursor: pointer;">here</a>.
					
				<?php } else { ?>
						
					<?=$sitename?> is privately owned and operated by HCI Compare LLC. Invitations for applications for insurance on Health-Insurance.com are made through HCI Compare LLC, a subsidiary of Affordable Healthcare, only where licensed and appointed. HCI Compare LLC licensing information can be found <a data-toggle="modal" data-target="#licensesModal" style="font-weight: bold; color: inherit !important; cursor: pointer;">here</a>. Submission of your information constitutes permission for an agent to contact you with additional information about the cost and coverage details of health plans.

				<?php } ?>
			</small>
		</p>
		<?php /*
		<h6 style="width: 70%; color: #728c98;">ACA DISCLAIMER</h6>
		<p style="width: 70%; color: #728c98;">
			<small style="font-size: 70%;">
				Short Term Health Insurance and Health Benefit Indemnity Insurance are not comprehensive medical coverage and do not qualify as minimum essential coverage under the Affordable Care Act (Obamacare).<br>By using this site, you acknowledge that you have read and agree to the <a data-toggle="modal" data-target="#termsModal" style="font-weight: bold; color: inherit !important; cursor: pointer;">Terms of Service</a> an <a data-toggle="modal" data-target="#policyModal" style="font-weight: bold; color: inherit !important; cursor: pointer;">Privacy Policy</a>.
			</small>
		</p>
		*/ ?>
	</center>
	<br>
	<br>
	<!-- Button trigger modal -->
	</div>
	<?php include "inc/footer.php"; ?>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.14.0/jquery.validate.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.min.js"></script>
	<script src="js/vendor/bootstrap.min.js"></script>
	<script src="js/step-2.js"></script>
	<script>
	$(function() {
		var age, state = $('#state'), $year = "<?php echo date('Y'); ?>", $month = "<?php echo date('n'); ?>", monthOptions, yearOptions, monthNumber, monthName = "", monthPos = new Date(), redirectUrl = $('input[name="Redirect_URL"]'), dateCalcNow, dateCalcDob, dobStr, dateCalcDuration, dateCalcDurYrs;
		var monthNames = ["January","February","March","April","May","June","July","August","September","October","November","December"];
		$('#yob').change(function() {
			var mobStore = $('#mob').val();
			monthOptions = "";
			$('#mob').html('<option value="" disabled="disabled" selected hidden style="display: none !important;">Month</option><option value="01">January</option><option value="02">February</option><option value="03">March</option><option value="04">April</option><option value="05">May</option><option value="06">June</option><option value="07">July</option><option value="08">August</option><option value="09">September</option><option value="10">October</option><option value="11">November</option><option value="12">December</option>');
			if (($('#yob').val() == ($year - age)) || ($('#yob').val() == ($year - age - 1))) {
				if ($('#yob').val() == ($year - age)) {
					for (i = 1; i <= $month; i++) {
						monthNumber = ('0'+i).slice(-2);
						monthName = monthNames[i-1];
						monthOptions += '<option value="' + monthNumber + '">' + monthName + '</option>';
					}
				} else if ($('#yob').val() == ($year - age - 1)) {
					for (i = $month; i <= 12; i++) {
						monthNumber = ('0'+i).slice(-2);
						monthName = monthNames[i-1];
						monthOptions += '<option value="' + monthNumber + '">' + monthName + '</option>';
					}
				}
				$('#mob').html('<option value="" disabled="disabled" selected hidden style="display: none !important;">Month</option>' + monthOptions);
				$('#mob').val($('#mob option[disabled] + option:last-child').val());
				if (!isNaN(mobStore) && mobStore != null && mobStore.length > 0) { $('#mob').val(mobStore); }
			}
		});
		$('#mob').change(function() {
			var yobStore = $('#yob').val();
			yearOptions = "";
			if (Number($('#mob').val()) == Number($month)) {
				yearOptions += '<option val="' + ($year - age - 1) + '">' + ($year - age - 1) + '</option><option val="' + ($year - age) + '">' + ($year - age) + '</option>';
			} else if (Number($('#mob').val()) > Number($month)) {
				yearOptions += '<option val="' + ($year - age - 1) + '">' + ($year - age - 1) + '</option>';
			} else if (Number($('#mob').val()) < Number($month)) {
				yearOptions += '<option val="' + ($year - age) + '">' + ($year - age) + '</option>';
			}
			$('#yob').html('<option value="" disabled="disabled" selected hidden style="display: none !important;">Year</option>' + yearOptions);
			$('#yob').val($('#yob option[disabled] + option:last-child').val());
			if (!isNaN(yobStore) && yobStore != null && yobStore.length > 0) { $('#yob').val(yobStore); }
		});
		var redirectUrlUpdate = function() {
			if (age >= 60 && age <= 65) {
				redirectUrl.val('/get-quotes/plans.php' + (dateCalcDurYrs >= 64.5 ? '?type=medicare' : '?type=healthcare') + '&city=' + $('#city').val() + '&state=' + state.val() + '&Household=1&Age=' + (age > 18 ? age : '18'));
			} else {
				redirectUrl.val('/get-quotes/plans.php' + (age >= 65 ? '?type=medicare' : '?type=healthcare') + '&city=' + $('#city').val() + '&state=' + state.val() + '&Household=1&Age=' + (age > 18 ? age : '18')); }
		};
		$('input, select').on('change keydown keyup', function() { redirectUrlUpdate(); });
		$('#age').on('change', function() {
			if ($(this).val() >= 65 ) {
				$('#type').val('23');
				$('#src').val('WebPostM');
			} else {
				$('#type').val('24');
				$('#src').val('Infinix-K-Ping');
			}
		});

		/* Inserts Confirmation to DB */
		function ajaxInsert(response) {
			$('#loading').modal('show');
			$.ajax({
				type: 'POST',
				url: 'https://affordable-healthcare.com/get-quotes/inc/insert_confirmation.php',
				data: 'email=' + $('#email').val() + '&ipaddress=' + $('input[name="IP_Address"]').val() + '&error=' + response,
				success: function (data) {
					console.log('Success: ' + data);
				},
				error: function (data, jqXHR, textStatus, errorThrown) {
					console.log('Error: ' + data);
				}
			});
			return false;
		}

		$("#forms").submit(function() {
			if ($(this).valid()) {
				//insert trusted form token
				trustedformCert = $('#xxTrustedFormToken_0').val();
    		$('#LeadiD_URL').val(trustedformCert);
				$('#loading').modal('show')
				$.ajax({
					url: 'form-processing.php',
					type: "POST",
					data: $("#forms").serialize(),
					dataType: "json",
					success: function(data) {
						console.log('success: '+data)
						response = $(data).find("status").text() + '<br>' + $(data).find("error").text();
          	ajaxInsert(response);
						window.location = redirectUrl.val();
						//make your redirect here or just display a message on the same page
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log('failure: '+errorThrown)
						console.log(jqXHR);
						// handle your error here
						window.location = redirectUrl.val();
					}
				});
				//cancel the submit default behavior
				return false;
			}
		});
	});
	</script>
	<script type="text/javascript">
	function readCookie(name) {
		var n = name + "=";
		var cookie = document.cookie.split(';');
		for (var i = 0; i < cookie.length; i++) {
			var c = cookie[i];
			while (c.charAt(0) == ' ') { c = c.substring(1, c.length); }
			if (c.indexOf(n) == 0) { return c.substring(n.length, c.length); }
		}
		return null;
	}
	window.onload = function() { document.getElementById('gclid_field').value = readCookie('gclid'); }
	</script>
	</body>

	</html>
