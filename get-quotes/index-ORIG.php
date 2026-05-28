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
				<?php if ($_SESSION['fb'] === 'true') { ?>
					<input type="hidden" name="TYPE" id="type" value="19">
					<input type="hidden" name="SRC" id="src" value="Infinix-KFB">
				<?php } elseif (isset($_SESSION['campaign'])) { ?>
					<input type="hidden" name="TYPE" id="type" value="19">
					<input type="hidden" name="SRC" id="src" value="<?=$_SESSION['campaign']?>">
				<?php } else { ?>
					<input type="hidden" name="TYPE" id="type" value="29">
					<input type="hidden" name="SRC" id="src" value="InfinixMedia-K">
				<?php } ?>
				<?php if(isset($_GET['gclid'])): ?>
				<input type="hidden" name="gclid" id="gclid_field" value="<?=$_GET['gclid']?>">
				<?php endif; ?>
				<input type="hidden" name="IP_Address" value="<?php echo $ip; ?>">
				<input type="hidden" name="Pub_ID" value="<?php if($_SESSION['affiliate_ID']) { echo $_SESSION['affiliate_ID']; } else { if(isset($_GET['Pub_ID'])) { echo $_GET['Pub_ID']; }} ?>">
				<input type="hidden" name="Sub_ID" value="<?php if($_SESSION['Sub_ID']) { echo $_SESSION['Sub_ID']; } else { echo substr(htmlentities($_GET['keyword']),0,100); } ?>">
				<?php if($_SESSION['HIT_ID']) { ?>
					<input type="hidden" name="ad_id" value="<?=$_SESSION['HIT_ID']?>">
					<?php } elseif ($_GET['ad_id']) { ?>
						<input type="hidden" name="ad_id" value="<?=$_GET['ad_id']?>">
				<?php } ?>
				<?php if($_SESSION['Notes']) { ?>
					<input type="hidden" name="notes" value="<?=$_SESSION['Notes']?>">
				<?php } ?>
				<input type="hidden" name="Search_Engine" value="<?php echo substr(htmlentities($_GET['engine']),0,100); ?>">
				<input type="hidden" name="Landing_Page" value="<?php echo ($_GET['page']) ? $_GET['page'] : 'https://' . $domain . '/'?>">
				<input type="hidden" class="LandingPageId" name="LandingPageId" value="<?php echo $pivot_lpid; ?>">
				<input type="hidden" id="Redirect_URL" name="Redirect_URL" value="/get-quotes/plans.php" /></script>
				<input type="hidden" name="LeadiD_URL" id="LeadiD_URL" value="" /></script>
        <input type="hidden" name="Age" id="age" value="<?php if(isset($_GET['Age'])) { echo $_GET['Age']; }?>" />
					<div class="row">
						<div class="col-sm-6 padding-left">
							<div class="row">
								<label for="pre-existing" id="#pre-ex" class="nopadding">Pre‑existing Conditions</label>
								<div class="form-group inline-list">
									<input type="radio" name="Preexisting_Condition" value="No" required checked> No
									<input type="radio" name="Preexisting_Condition" value="Yes" class="ml-2" required> Yes
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
									<input name="First_Name" type="text" class="form-control" placeholder="First Name" autocomplete="given-name" required minlength="2" maxlength="100" <?php if(isset($_GET['First_Name'])) { echo 'value="'.$_GET['First_Name'].'"'; }?>>
								</div>
							</div>
							<div class="row">
								<label for="full_name" class="col-xs-3 nopadding">Last Name</label>
								<div class="form-group col-xs-9 nopadding">
									<input name="Last_Name" type="text" class="form-control" placeholder="Last Name" autocomplete="family-name" required minlength="2" maxlength="100" <?php if(isset($_GET['First_Name'])) { echo 'value="'.$_GET['Last_Name'].'"'; }?>>
								</div>
							</div>
							<div class="row">
								<label class="col-xs-3 nopadding">Phone</label>
								<div class="form-group col-xs-9 nopadding">
									<input name="Primary_Phone" type="tel" class="phone form-control" placeholder="5555555555" autocomplete="tel" required <?php if(isset($_GET['First_Name'])) { echo 'value="'.$_GET['Primary_Phone'].'"'; }?>>
								</div>
							</div>
							<div class="row">
								<label class="col-xs-3 nopadding">Email</label>
								<div class="form-group col-xs-9 nopadding">
									<input id="email" name="Email" v-model="email" type="email" class="form-control" placeholder="name@email.com" autocomplete="email" required <?php if(isset($_GET['First_Name'])) { echo 'value="'.$_GET['Email'].'"'; }?>>
								</div>
							</div>
						</div>
						<div class="col-sm-6 padding-right">
							<div class="row">
								<label class="col-xs-3 nopadding">Household</label>
								<div class="form-group col-xs-9 nopadding">
									<select id="household-income" name="Household_Income" class="household-income form-control" required="" aria-required="true">
										<option value="">Income</option>
										<option value="11999" <?php if($_GET['Household_Income'] == '11999'){ echo 'selected'; }?>>Below $12,000</option>
										<option value="28999" <?php if($_GET['Household_Income'] == '28999'){ echo 'selected'; }?>>$12,000 - $28,999</option>
										<option value="46999" <?php if($_GET['Household_Income'] == '46999'){ echo 'selected'; }?>>$29,000 - $46,999</option>
										<option value="99999" <?php if($_GET['Household_Income'] == '99999'){ echo 'selected'; }?>>$47,000 and Over</option>
									</select>
								</div>
							</div>
							<!-- <div class="row">
								<label class="col-xs-3 nopadding">Age</label>
								<div class="form-group col-xs-9 nopadding">
									<select id="age" name="Age" class="age form-control" required>
										<option value="">Select</option>
										<?php for($age = 18; $age <= 100; $age++): ?><option value="<?php echo $age;?>"><?php echo $age; ?></option><?php endfor; ?>
									</select>
								</div>
							</div> -->
							<div class="row">
								<label class="col-xs-3 nopadding" style="margin: 40px 0px -40px 0px!important;">DOB</label>
								<div class="form-group col-xs-4 nopadding">
									<input id="dob" id="dob" name="DOB" type="hidden" autocomplete="bday" maxlength="10" minlength="10" class="form-control" placeholder="mm/dd/yyyy" required <?php if(isset($_GET['DOB'])) { echo 'value="'.$_GET['DOB'].'"'; }?>>
									<div>
										<select name="birthmonth" id="birthmonth" required="" class="form-control" aria-required="true">
											<option disabled="" selected="" value="">MM</option>
											<option value="01">01</option><option value="02">02</option><option value="03">03</option><option value="04">04</option><option value="05">05</option><option value="06">06</option><option value="07">07</option><option value="08">08</option><option value="09">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option>
										</select>
									</div>
								</div>
								<div class="form-group col-xs-4 nopadding col-xs-push-1">
									<div>
										<select id="birthday" name="birthday" required="" class="form-control" aria-required="true">
											<option disabled="" selected="" value="">DD</option>
											<option value="01">01</option><option value="02">02</option><option value="03">03</option><option value="04">04</option><option value="05">05</option><option value="06">06</option><option value="07">07</option><option value="08">08</option><option value="09">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option>
										</select>
									</div>
								</div>
							</div>
							<div class="row">
								<label class="col-xs-3 nopadding"></label>
								<div class=" form-group col-xs-9 nopadding">
									<input name="birthyear" type="number" id="birthyear" pattern="\d{4}" maxlength="4" min="1900" max="2022" oninvalid="setCustomValidity('Please enter a valid 4-digit year.')" oninput="setCustomValidity('')" required="" placeholder="YYYY" class="form-control" aria-required="true">
									<div data-lastpass-icon-root="true" style="position: relative !important; height: 0px !important; width: 0px !important; float: left !important;">
									</div>
								</div>
							</div>
							<div class="row">
								<label class="col-xs-3 nopadding">Address</label>
								<div class="form-group col-xs-9 nopadding">
									<input name="Address" type="text" placeholder="Address" autocomplete="address-line1" class="form-control" required maxlength="100" <?php if(isset($_GET['First_Name'])) { echo 'value="'.$_GET['Address'].'"'; }?>>
								</div>
							</div>
							<div class="row">
								<label class="col-xs-3 nopadding">City</label>
								<div class="form-group col-xs-9 nopadding">
									<input name="city" id="city" type="text" class="form-control" placeholder="City" autocomplete="address-level2" required maxlength="100" style="text-transform: capitalize;" <?php if(isset($_GET['First_Name'])) { echo 'value="'.$_GET['city'].'"'; }?>>
								</div>
							</div>
							<div class="row">
								<label class="col-xs-3 nopadding">State</label>
								<div class="form-group col-xs-4 nopadding">
									<select name="state" id="state" required class="form-control" autocomplete="address-level1">
										<option value="">Select</option>
										<option value="AL" <?php if($_GET['state'] == 'AL'){ echo 'selected'; }?>>Alabama</option>
										<option value="AK" <?php if($_GET['state'] == 'AK'){ echo 'selected'; }?>>Alaska</option>
										<option value="AZ" <?php if($_GET['state'] == 'AZ'){ echo 'selected'; }?>>Arizona</option>
										<option value="AR" <?php if($_GET['state'] == 'AR'){ echo 'selected'; }?>>Arkansas</option>
										<option value="CA" <?php if($_GET['state'] == 'CA'){ echo 'selected'; }?>>California</option>
										<option value="CO" <?php if($_GET['state'] == 'CO'){ echo 'selected'; }?>>Colorado</option>
										<option value="CT" <?php if($_GET['state'] == 'CT'){ echo 'selected'; }?>>Connecticut</option>
										<option value="DE" <?php if($_GET['state'] == 'DE'){ echo 'selected'; }?>>Delaware</option>
										<option value="DC" <?php if($_GET['state'] == 'DC'){ echo 'selected'; }?>>District Of Columbia</option>
										<option value="FL" <?php if($_GET['state'] == 'FL'){ echo 'selected'; }?>>Florida</option>
										<option value="GA" <?php if($_GET['state'] == 'GA'){ echo 'selected'; }?>>Georgia</option>
										<option value="HI" <?php if($_GET['state'] == 'HI'){ echo 'selected'; }?>>Hawaii</option>
										<option value="ID" <?php if($_GET['state'] == 'ID'){ echo 'selected'; }?>>Idaho</option>
										<option value="IL" <?php if($_GET['state'] == 'IL'){ echo 'selected'; }?>>Illinois</option>
										<option value="IN" <?php if($_GET['state'] == 'IN'){ echo 'selected'; }?>>Indiana</option>
										<option value="IA" <?php if($_GET['state'] == 'IA'){ echo 'selected'; }?>>Iowa</option>
										<option value="KS" <?php if($_GET['state'] == 'KS'){ echo 'selected'; }?>>Kansas</option>
										<option value="KY" <?php if($_GET['state'] == 'KY'){ echo 'selected'; }?>>Kentucky</option>
										<option value="LA" <?php if($_GET['state'] == 'LA'){ echo 'selected'; }?>>Louisiana</option>
										<option value="ME" <?php if($_GET['state'] == 'ME'){ echo 'selected'; }?>>Maine</option>
										<option value="MD" <?php if($_GET['state'] == 'MD'){ echo 'selected'; }?>>Maryland</option>
										<option value="MA" <?php if($_GET['state'] == 'MA'){ echo 'selected'; }?>>Massachusetts</option>
										<option value="MI" <?php if($_GET['state'] == 'MI'){ echo 'selected'; }?>>Michigan</option>
										<option value="MN" <?php if($_GET['state'] == 'MN'){ echo 'selected'; }?>>Minnesota</option>
										<option value="MS" <?php if($_GET['state'] == 'MS'){ echo 'selected'; }?>>Mississippi</option>
										<option value="MO" <?php if($_GET['state'] == 'MO'){ echo 'selected'; }?>>Missouri</option>
										<option value="MT" <?php if($_GET['state'] == 'MT'){ echo 'selected'; }?>>Montana</option>
										<option value="NE" <?php if($_GET['state'] == 'NE'){ echo 'selected'; }?>>Nebraska</option>
										<option value="NV" <?php if($_GET['state'] == 'NV'){ echo 'selected'; }?>>Nevada</option>
										<option value="NH" <?php if($_GET['state'] == 'NH'){ echo 'selected'; }?>>New Hampshire</option>
										<option value="NJ" <?php if($_GET['state'] == 'NJ'){ echo 'selected'; }?>>New Jersey</option>
										<option value="NM" <?php if($_GET['state'] == 'NM'){ echo 'selected'; }?>>New Mexico</option>
										<option value="NY" <?php if($_GET['state'] == 'NY'){ echo 'selected'; }?>>New York</option>
										<option value="NC" <?php if($_GET['state'] == 'NC'){ echo 'selected'; }?>>North Carolina</option>
										<option value="ND" <?php if($_GET['state'] == 'ND'){ echo 'selected'; }?>>North Dakota</option>
										<option value="OH" <?php if($_GET['state'] == 'OH'){ echo 'selected'; }?>>Ohio</option>
										<option value="OK" <?php if($_GET['state'] == 'OK'){ echo 'selected'; }?>>Oklahoma</option>
										<option value="OR" <?php if($_GET['state'] == 'OR'){ echo 'selected'; }?>>Oregon</option>
										<option value="PA" <?php if($_GET['state'] == 'PA'){ echo 'selected'; }?>>Pennsylvania</option>
										<option value="RI" <?php if($_GET['state'] == 'RI'){ echo 'selected'; }?>>Rhode Island</option>
										<option value="SC" <?php if($_GET['state'] == 'SC'){ echo 'selected'; }?>>South Carolina</option>
										<option value="SD" <?php if($_GET['state'] == 'SD'){ echo 'selected'; }?>>South Dakota</option>
										<option value="TN" <?php if($_GET['state'] == 'TN'){ echo 'selected'; }?>>Tennessee</option>
										<option value="TX" <?php if($_GET['state'] == 'TX'){ echo 'selected'; }?>>Texas</option>
										<option value="UT" <?php if($_GET['state'] == 'UT'){ echo 'selected'; }?>>Utah</option>
										<option value="VT" <?php if($_GET['state'] == 'VT'){ echo 'selected'; }?>>Vermont</option>
										<option value="VA" <?php if($_GET['state'] == 'VA'){ echo 'selected'; }?>>Virginia</option>
										<option value="WA" <?php if($_GET['state'] == 'WA'){ echo 'selected'; }?>>Washington</option>
										<option value="WV" <?php if($_GET['state'] == 'WV'){ echo 'selected'; }?>>West Virginia</option>
										<option value="WI" <?php if($_GET['state'] == 'WI'){ echo 'selected'; }?>>Wisconsin</option>
										<option value="WY" <?php if($_GET['state'] == 'WY'){ echo 'selected'; }?>>Wyoming</option>
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
    /* Get Query String Parameter From URL */
    $.urlParam = function (name) {
      var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.search);
      return (results !== null) ? results[1] || 0 : false;
    }
    var zipCode = $.urlParam('zip');

    /* Capitalize */
    String.prototype.capitalize = function () {
      return this.toLowerCase().replace(/\b\w/g, function (m) {
        return m.toUpperCase();
      });
    };
    /* Set City & State From Zip Code */
    function setLocationFromZip(zip) {
      $.get('https://zip.getziptastic.com/v2/US/' + zip, function (data) {
        if (typeof data.error === 'undefined') {
          var city = data.city.capitalize();
          $('#city').val(city);
          $('#state').val(data.state_short).attr('selected', true);
          $('[name=Zip]').val(zip);
        }
      })
    }
    setLocationFromZip(zipCode);
    $('[name=Zip]').on('change', function () {
      var newZip = $('[name=Zip]').val();
      setLocationFromZip(newZip);
    });

    $('#dob').mask("99/99/9999");
		var age, state = $('#state'), $year = "<?php echo date('Y'); ?>", $month = "<?php echo date('n'); ?>", monthOptions, yearOptions, monthNumber, monthName = "", monthPos = new Date(), redirectUrl = $('input[name="Redirect_URL"]'), dateCalcNow, dateCalcDob, dobStr, dateCalcDuration, dateCalcDurYrs;
		// var monthNames = ["January","February","March","April","May","June","July","August","September","October","November","December"];
		// $('#yob').change(function() {
		// 	var mobStore = $('#mob').val();
		// 	monthOptions = "";
		// 	$('#mob').html('<option value="" disabled="disabled" selected hidden style="display: none !important;">Month</option><option value="01">January</option><option value="02">February</option><option value="03">March</option><option value="04">April</option><option value="05">May</option><option value="06">June</option><option value="07">July</option><option value="08">August</option><option value="09">September</option><option value="10">October</option><option value="11">November</option><option value="12">December</option>');
		// 	if (($('#yob').val() == ($year - age)) || ($('#yob').val() == ($year - age - 1))) {
		// 		if ($('#yob').val() == ($year - age)) {
		// 			for (i = 1; i <= $month; i++) {
		// 				monthNumber = ('0'+i).slice(-2);
		// 				monthName = monthNames[i-1];
		// 				monthOptions += '<option value="' + monthNumber + '">' + monthName + '</option>';
		// 			}
		// 		} else if ($('#yob').val() == ($year - age - 1)) {
		// 			for (i = $month; i <= 12; i++) {
		// 				monthNumber = ('0'+i).slice(-2);
		// 				monthName = monthNames[i-1];
		// 				monthOptions += '<option value="' + monthNumber + '">' + monthName + '</option>';
		// 			}
		// 		}
		// 		$('#mob').html('<option value="" disabled="disabled" selected hidden style="display: none !important;">Month</option>' + monthOptions);
		// 		$('#mob').val($('#mob option[disabled] + option:last-child').val());
		// 		if (!isNaN(mobStore) && mobStore != null && mobStore.length > 0) { $('#mob').val(mobStore); }
		// 	}
		// });
		// $('#mob').change(function() {
		// 	var yobStore = $('#yob').val();
		// 	yearOptions = "";
		// 	if (Number($('#mob').val()) == Number($month)) {
		// 		yearOptions += '<option val="' + ($year - age - 1) + '">' + ($year - age - 1) + '</option><option val="' + ($year - age) + '">' + ($year - age) + '</option>';
		// 	} else if (Number($('#mob').val()) > Number($month)) {
		// 		yearOptions += '<option val="' + ($year - age - 1) + '">' + ($year - age - 1) + '</option>';
		// 	} else if (Number($('#mob').val()) < Number($month)) {
		// 		yearOptions += '<option val="' + ($year - age) + '">' + ($year - age) + '</option>';
		// 	}
		// 	$('#yob').html('<option value="" disabled="disabled" selected hidden style="display: none !important;">Year</option>' + yearOptions);
		// 	$('#yob').val($('#yob option[disabled] + option:last-child').val());
		// 	if (!isNaN(yobStore) && yobStore != null && yobStore.length > 0) { $('#yob').val(yobStore); }
		// });
		var redirectUrlUpdate = function() {
			if ($('#src').val() == 'Magenta' || $('#src').val() == 'Magenta2') {
				madrivo = $('#src').val();
			} else {
				madrivo = '';
			}
			if (age >= 60 && age <= 65) {
				redirectUrl.val('/get-quotes/plans'+(age >= 65 ? '-medicare' : '')+'.php' + (dateCalcDurYrs >= 64.5 ? '?type=medicare' : '?type=healthcare') + '&city=' + $('#city').val() + '&state=' + state.val() + '&Household=1&Age=' + (age > 18 ? age : '18') + (madrivo = true ? '&src='+btoa($('#src').val()) : ''));
			} else {
				redirectUrl.val('/get-quotes/plans'+(age >= 65 ? '-medicare' : '')+'.php' + (age >= 65 ? '?type=medicare' : '?type=healthcare') + '&city=' + $('#city').val() + '&state=' + state.val() + '&Household=1&Age=' + (age > 18 ? age : '18') + (madrivo = true ? '&src='+btoa($('#src').val()) : ''));
			}
			var BM = $('#birthmonth').val();
			var BD = $('#birthday').val();
			var BY = $('#birthyear').val();
			$('#dob').val(BM+'/'+BD+'/'+BY);
			console.log($('#dob').val());
		};
		$('input, select').on('change keydown keyup', function() { redirectUrlUpdate(); });
		$('#age').on('change', function() {
			if ($('#src').val() != 'Infinix-KFB' && $('#src').val() != 'Magenta' && $('#src').val() != 'Magenta2') {
				if ($(this).val() >= 65 ) {
					$('#type').val('23');
					$('#src').val('WebPostMk');
				} else {
					$('#type').val('29');
					$('#src').val('InfinixMedia-K');
				}
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
					console.log('Success: ' + response);
					sessionStorage.setItem('entryStatus', 'success');
					window.location = redirectUrl.val();
				},
				error: function (data, jqXHR, textStatus, errorThrown) {
					console.log('Error: ' + data);
					console.log('Error: ' + response);
					sessionStorage.setItem('entryStatus', 'error');
					window.location = redirectUrl.val();
				}
			});
			return false;
		}
    /* Get Age From DOB */
    function getAge() {
  		var DOB = $('#dob').val();
			var today = new Date();
			var birthDate = new Date(DOB);
			var age = today.getFullYear() - birthDate.getFullYear();
			var m = today.getMonth() - birthDate.getMonth();
			if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
					age = age - 1;
    	}

    	console.log(age);
      $('#age').val(Math.floor(age));
			var nonstandard_srcs = ['Infinix-KFB', 'Magenta', 'Magenta2', 'Falcons1', 'Falcons2', 'Falcons3'];
			if($.inArray($('#src').val(), nonstandard_srcs) === -1) { /* Checks to make sure nonstandard srcs are not used before checking for age switch */
				if (age >= 65 ) {
					$('#type').val('23');
					$('#src').val('WebPostMk');
				} else {
					if ($('#changeSRC').val()) {
						$('#type').val($('#changeSRC').val());
						$('#src').val('InfinixMedia-K');
					} else {
						$('#type').val('29');
						$('#src').val('InfinixMedia-K');
					}					
				}
			}
		}

		$("#forms").submit(function() {
			if ($(this).valid()) {
				// Insert Trusted Form Token
				trustedformCert = $('#xxTrustedFormToken_0').val();
				$('input[name="LeadiD_URL"]').val(trustedformCert);

				// Loading Modal - Turned On
				$('#loading').modal('show');

				// Claim Trusted Form Certificate
				$.ajax({
					url: 'https://affordable-healthcare.com/get-quotes/trusted-form-processing.php',
					type: "POST",
					data: {certID: trustedformCert},
					// dataType: "json",
					success: function(data) {
					console.log(data);
					},
					error: function(data, jqXHR, textStatus, errorThrown) {
					console.log(data);
					}
				});
				getAge();

				sessionStorage.setItem('userPhone', $('input[name="Primary_Phone"]').val());
				var dms_srcs = ['Falcons1', 'Falcons2', 'Falcons3'];
				if($.inArray($('#src').val(), dms_srcs) >= -1) { /* Checks to make sure dms srcs are being used */
					sessionStorage.setItem('dms_lead', 'true');
					sessionStorage.setItem('src', 'input[name="SRC"]');
					sessionStorage.setItem('cid', $('input[name="ad_id"]').val());
					sessionStorage.setItem('gclid', $('input[name="gclid"]').val());
				}
				//if landing page equals alpha
				if ($('input[name=Landing_Page]').val().indexOf('alpha') != -1) {
					$('#type').val('19');
					$('#src').val('Kobe');
				}
				console.log($($("#forms")[0].elements).not("#Redirect_URL,#birthmonth,#birthday,#birthyear").serialize());
				$.ajax({
					url: 'form-processing.php',
					type: "POST",
					data: $($("#forms")[0].elements).not("#Redirect_URL,#birthmonth,#birthday,#birthyear").serialize(),
					dataType: "json",
					success: function(data) {

						let response = $(data.responseText);						
						let status = $(response).find('status').text();
						let error = $(response).find('error').text();
						let leadID = $(response).find('lead_id').text();
						
						let statusLength, errorLength, leadIDlength;
								statusLength = status.length;
								errorLength = error.length;
								leadIDLength = leadID.length;
						
						let statusFinal = status.slice(0, statusLength/2);
						let errorFinal = error.slice(0, errorLength/2);
						let leadIDFinal = leadID.slice(0, leadIDLength/2);
						
						let message;

						if (errorLength > 1) {
							message = ' Message: ' + errorFinal;
						} 
						if (leadIDlength > 1) {
							message = ' Lead ID: ' + leadIDFinal;
						}
						let responseMessage = 'Status: ' + statusFinal + '   ' + message;

						console.log(data);
						
						ajaxInsert(responseMessage);

					},
					error: function(data, jqXHR, textStatus, errorThrown) {
						
						let response = $(data.responseText);						
						let status = $(response).find('status').text();
						let error = $(response).find('error').text();
						let leadID = $(response).find('lead_id').text();
						
						let statusLength, errorLength, leadIDlength;
								statusLength = status.length;
								errorLength = error.length;
								leadIDLength = leadID.length;
						
						let statusFinal = status.slice(0, statusLength/2);
						let errorFinal = error.slice(0, errorLength/2);
						let leadIDFinal = leadID.slice(0, leadIDLength/2);
						
						let message;

						if (errorLength > 1) {
							message = ' Message: ' + errorFinal;
						} 
						if (leadIDlength > 1) {
							message = ' Lead ID: ' + leadIDFinal;
						}
						let responseMessage = 'Status: ' + statusFinal + '   ' + message;

						console.log(data);
						
						ajaxInsert(responseMessage);

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
	// window.onload = function() { document.getElementById('gclid_field').value = readCookie('gclid'); }
	</script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.maskedinput/1.4.1/jquery.maskedinput.min.js" integrity="sha512-d4KkQohk+HswGs6A1d6Gak6Bb9rMWtxjOa0IiY49Q3TeFd5xAzjWXDCBW9RS7m86FQ4RzM2BdHmdJnnKRYknxw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
	<script type="text/javascript">
		window._mfq = window._mfq || [];
		(function() {
			var mf = document.createElement("script");
			mf.type = "text/javascript"; mf.defer = true;
			mf.src = "//cdn.mouseflow.com/projects/8ca073b2-3928-4c00-b64c-bd49cda80ae8.js";
			document.getElementsByTagName("head")[0].appendChild(mf);
		})();
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
		<?php if($call_now === 'true') { ?>
			<script type="text/javascript" src="//cdn.callrail.com/companies/447996446/375307dddfb93a0d4e5c/12/swap.js"></script>
		<?php } ?>
</body>
</html>