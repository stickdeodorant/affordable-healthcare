<?php $currentStep = $_SESSION['form-step']; ?>
<?php $nextStep = $currentStep + 1; ?>
<form id="msform" aria-step="<?=$currentStep?>" action="./?step<?=$nextStep?>" method="get">

  <?php include 'non-questions.php'; ?>

  <input type="hidden" name="step" id="step-<?=$currentStep?>" value="<?=$currentStep?>" />

  <fieldset>
      <div class="form-card text-center">
          <h3 class="fs-title">What is your Zip Code?</h3>
          <input id="zip" name="zip" type="tel" class="form-control" placeholder="12345" autocomplete="postal-code" required <?php if(isset($_GET['zip'])) { echo 'value="' . htmlspecialchars((string)$_GET['zip'], ENT_QUOTES, 'UTF-8') . '"'; }?> tabindex="1">
          <input type="submit" class="next btn action-button" tabindex="2" value="Continue" />
          <?php /* <div class="previous" tabindex="3">Back to previous step</div> */ ?>
      </div>
  </fieldset>

</form>