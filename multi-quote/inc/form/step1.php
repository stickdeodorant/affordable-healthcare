<form id="msform" aria-step="1" action="./?step2" method="get">

  <?php include 'non-questions.php'; ?>

  <input type="hidden" name="step" id="step-1" value="1" />
  
  <!-- fieldsets -->
  <fieldset>
    <div class="form-card text-center">
      <h3 class="fs-title">How many people are you insuring?</h3>
      <div class="radio-group">
        <input type="hidden" name="Household" id="household" class="form-control"/>
        <div class='radio btn' data-value="1" tabindex="1">1 person</div>
        <div class='radio btn' data-value="2" tabindex="2">2 people</div>
        <div class='radio btn' data-value="3" tabindex="3">Family (3 or more)</div>
        <br>
        <input type="submit" class="next btn action-button hidden" tabindex="999" value="Continue" />
      </div>
    </div>
  </fieldset>

</form>