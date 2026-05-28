<form id="msform" aria-step="2" action="./?step3" method="get">
    
  <?php include 'non-questions.php'; ?>

  <input type="hidden" name="step" id="step-2" value="2" />

  <fieldset>
      <div class="form-card">
          <h2 class="fs-title">What is your date of birth?</h2>
          <input id="dob" name="DOB" type="hidden" autocomplete="bday" maxlength="10" minlength="10" <?php if(isset($_GET['DOB'])) { echo 'value="'.$_GET['DOB'].'"'; }?>>
          <div class="row">
              <div class="form-group col-4">
                  <div>
                      <select name="birthmonth" id="birthmonth" required="" data-parsley-required="parsley" class="form-control" aria-required="true" data-parsley-error-message="Birth Month Required" tabindex="1">
                          <option disabled="" selected="" value="">MONTH</option>
                          <option value="01">01</option><option value="02">02</option><option value="03">03</option><option value="04">04</option><option value="05">05</option><option value="06">06</option><option value="07">07</option><option value="08">08</option><option value="09">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option>
                      </select>
                  </div>
              </div>
              <div class="form-group col-4">
                  <div>
                      <select id="birthday" name="birthday" required="" data-parsley-required="parsley" class="form-control" aria-required="true" data-parsley-error-message="Birthday Required" tabindex="2">
                          <option disabled="" selected="" value="">DAY</option>
                          <option value="01">01</option><option value="02">02</option><option value="03">03</option><option value="04">04</option><option value="05">05</option><option value="06">06</option><option value="07">07</option><option value="08">08</option><option value="09">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option>
                      </select>
                  </div>
              </div>
              <div class="form-group col-4">
                  <div>
                      <select id="birthyear" name="birthyear" required="" data-parsley-required="parsley" class="form-control" aria-required="true" data-parsley-error-message="Birth Year Required" tabindex="3">
                          <option disabled="" selected="" value="">YEAR</option>
                          <?php
                              $range = 64;
                              $limit = 18;
                              $current = date('Y');
                              $eldest = $current - $range;
                              $recent = $current - $limit;
                              foreach (range($recent, $eldest) as $year) { echo "<option value=\"".$year."\">".$year."</option>"; }
                          ?>
                      </select>
                  </div>
              </div>
          </div>
          <input type="submit" class="next btn action-button" tabindex="4" value="Continue" />
          <?php /* <div class="previous" tabindex="5">Back to previous step</div> */ ?>
      </div>
  </fieldset>

</form>