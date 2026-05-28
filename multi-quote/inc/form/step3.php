<form id="msform" aria-step="3" action="./?step4" method="get">

  <?php include 'non-questions.php'; ?>
  
  <input type="hidden" name="step" id="step-3" value="3" />

  <fieldset>
    <div class="form-card text-center">
    <h3 class="fs-title">Which of the following matches your household income?</h3>
    <div class="radio-group">
      <input type="hidden" name="Household_Income" id="household-income" class="form-control"/>
      
      <?php /* ORIGINAL VALUES
        <div class='radio btn' data-value="11999" tabindex="1">Below $12,000</div>
        <div class='radio btn' data-value="28999" tabindex="2">$12,000 - $28,999</div>
        <div class='radio btn' data-value="46999" tabindex="3">$29,000 - $46,999</div>
        <div class='radio btn' data-value="99999" tabindex="4">$47,000 and Over</div>

        <div class='radio btn' tabindex="1" data-value="Below 24999">Below $25,000</div>
        <div class='radio btn' tabindex="2" data-value="25000-39999">$25,000 - $39,999</div>
        <div class='radio btn' tabindex="3" data-value="40000-54999">$40,000 - $54,999</div>
        <div class='radio btn' tabindex="4" data-value="55000-69999">$55,000 - $69,999</div>
        <div class='radio btn' tabindex="5" data-value="70000-99999">$70,000 - $99,999</div>
        <div class='radio btn' tabindex="6" data-value="100000+">$100,000+</div>
      */ ?>

      <div class='radio btn' tabindex="1" data-value="100000">$100,000+</div>
      <div class='radio btn' tabindex="2" data-value="99999">$70,000 - $99,999</div>
      <div class='radio btn' tabindex="3" data-value="69999">$55,000 - $69,999</div>
      <div class='radio btn' tabindex="4" data-value="54999">$40,000 - $54,999</div>
      <div class='radio btn' tabindex="5" data-value="39999">$25,000 - $39,999</div>
      <div class='radio btn' tabindex="6" data-value="24999">Below $25,000</div>

      <p class="small"><sup>*</sup>Household income helps determine how much financial help you qualify for.</p>
      <br>
      <input type="submit" class="next btn action-button hidden" tabindex="999" value="Continue" />
      <?php /* <div class="previous" tabindex="5">Back to previous step</div> */ ?>
    </div>
  </fieldset>

</form>