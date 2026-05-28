<form id="msform" aria-step="6" action="./?final" method="get">

  <?php //include 'non-questions.php'; ?>

  
  <input type="hidden" name="step" id="step-6" value="6" />
  
  <fieldset aria-headline="Last Step!">
    <div class="form-card text-center">
      <h3 class="fs-title"><span class="name"><?=$_SESSION['First_Name']?></span>, your results are ready. What is your phone number?</h3>
      <input name="Primary_Phone" type="tel" class="phone form-control" placeholder="(555) 555-5555" autocomplete="tel" required tabindex="1" data-parsley-minlength="14" data-parsley-maxlength="14" data-parsley-pattern="\D*([2-9]\d{2})(\D*)([2-9]\d{2})(\D*)(\d{4})\D*" data-parsley-error-message="Please enter a valid phone number area code first."/>
      <input type="submit" class="next btn action-button submit" tabindex="2" value="Submit" />
      <p class="small">By submitting this form I verify that I have read and accept the <a data-toggle="modal" data-target="#policyModal" style="font-weight: bold; color: inherit !important; cursor: pointer;">Privacy Policy</a> and <a data-toggle="modal" data-target="#termsModal" style="font-weight: bold; color: inherit !important; cursor: pointer;">Terms of Use</a> and provide written consent via electronic signature to receive communications via automatic telephone dialing system or by artificial/pre-recorded message, email or by text message from multiple insurance companies or their agents, this website, and <a data-toggle="modal" data-target="#partnerModal" style="font-weight: bold; color: inherit !important; cursor: pointer;">partner companies</a> at the telephone number above, including my wireless number if provided. I understand that my consent is not required as a condition of purchasing any goods or services. Your carrier's message and data rates may apply.</p>
      <?php /* <div class="previous pt-3" tabindex="1">Back to previous step</div> */ ?>
    </div>
  </fieldset>
  
  <?php 
    foreach($_SESSION as $key=>$value) {
      if($key == "SRC") { ?>
        <input type="hidden" name="<?=$key?>" value="<?=base64_decode($value)?>" />
      <?php } else { ?>
        <input type="hidden" name="<?=$key?>" value="<?=$value?>" />
      <?php } ?>
  <?php } ?>
  
</form>