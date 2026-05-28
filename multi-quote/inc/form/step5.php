<form id="msform" aria-step="5" action="./?step6" method="get">

  <?php include 'non-questions.php'; ?>

  <input type="hidden" name="step" id="step-5" value="5" />

  <fieldset>
      <div class="form-card text-center">
          <h3 class="fs-title">What is your email?</h3>
          <input id="email" name="Email" type="email" class="form-control" placeholder="name@email.com" autocomplete="email" required <?php if(isset($_GET['Email'])) { echo 'value="'.$_GET['Email'].'"'; }?> tabindex="1">
          <input type="submit" class="next btn action-button" tabindex="2" value="Continue" />
          <?php /* <div class="previous" tabindex="3">Back to previous step</div> */ ?>
      </div>
  </fieldset>

</form>