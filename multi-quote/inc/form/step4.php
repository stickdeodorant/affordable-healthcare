<form id="msform" aria-step="4" action="./?step5" method="get">

  <?php include 'non-questions.php'; ?>

  <input type="hidden" name="step" id="step-4" value="4" />

  <fieldset>
      <div class="form-card text-center">
          <h3 class="fs-title">What is your name?</h3>
          <input name="First_Name" id="first_name" type="text" class="form-control" placeholder="First Name" autocomplete="given-name" required="" data-parsley-required="parsley" minlength="2" maxlength="100" <?php if(isset($_GET['First_Name'])) { echo 'value="' . htmlspecialchars((string)$_GET['First_Name'], ENT_QUOTES, 'UTF-8') . '"'; }?> tabindex="1">
          <input name="Last_Name" id="last_name" type="text" class="form-control" placeholder="Last Name" autocomplete="family-name" required="" data-parsley-required="parsley" minlength="2" maxlength="100" <?php if(isset($_GET['Last_Name'])) { echo 'value="' . htmlspecialchars((string)$_GET['Last_Name'], ENT_QUOTES, 'UTF-8') . '"'; }?> tabindex="2">
          <input type="submit" class="next btn action-button" tabindex="3" value="Continue" />
          <?php /* <div class="previous" tabindex="4">Back to previous step</div> */ ?>
      </div>
  </fieldset>

</form>