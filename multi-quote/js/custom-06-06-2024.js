$(document).ready(function(){
    
  var current_fs, next_fs, previous_fs, element_clicked; //fieldsets
  var opacity;

  special_headline = $('fieldset').attr('aria-headline');
  if (special_headline) {
    $('.form-headline').text(special_headline);
  } else {
    $('.form-headline').text($('.form-headline').attr('aria-headline-default'));
  }
  
  $(".next, .radio.btn, .submit").click(function(index){
      
    current_fs = $(this).closest('form').attr('aria-step');
    next_fs = current_fs++;
    
    
    if ($(this).hasClass('submit')) {
      element_clicked = 'submit';
    }
      
    // Add Class Active
    $("#progressbar li#"+current_fs).addClass("active");

    // Form Validation
    if (element_clicked == 'submit') {

      $('#msform').parsley().validate();
      if ($('#msform').parsley().isValid()) {
        submitForm();
      }

    } else {

      if ($(this).hasClass('radio')) {

        var radioValue = $(this).data('value');
        $('.form-control').val(radioValue);
        $('#msform .next').click();
      
      } else if ($(this).hasClass('submit')) {
      
        $('#msform').parsley().validate();
        if ($('#msform').parsley().isValid()) {
          submitForm();
      
        }
      } else {
      
        $('#msform').parsley().validate();
        if ($('#msform').parsley().isValid()) {
          $('#msform').submit();
        }
      
      }
      
    }
  });

  // For back button not currently used
  /*
    $(".previous").click(function(index){
      window.history.go(-1);
    });
  */

  $('.radio-group .radio').click(function(){
      $(this).parent().find('.radio').removeClass('selected');
      $(this).addClass('selected');
  });
  
  $(".phone").inputmask("(999) 999-9999");
  
  $(".submit").click(function(){
      return false;
  })


  // Form Validation
  var $sections = $('fieldset');

  // Prepare sections by setting the `data-parsley-group` attribute to 'block-0', 'block-1', etc.
  $sections.each(function(index, section) {
    $(section).find(':input').attr('data-parsley-group', 'block-' + index);
  });


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
    $.get('//zip.getziptastic.com/v2/US/' + zip, function (data) {
      //var parsed = $.parseJSON(data);
      console.log(data);
      if (typeof data.error === 'undefined') {
        var city = data.city.capitalize();
        sessionStorage.setItem('state', data.state_short);
        $('input[name="city"]').val(city);
        $('input[name="City"]').val(city);
        $('input[name="state"]').val(data.state_short);
        $('input[name=Zip]').val(zip);
      }
    })
  }
  
  function redirectUrlUpdate() {
    
    var redirectUrl = $('input[name="Redirect_URL"]');
    var age = $('input[name="Age"]').val();
    var city = $('input[name="city"]').val();
    var state = $('input[name="state"]').val();
    var src = $('input[name="SRC"]').val();
    var fname = $('input[name="First_Name"]').val();
    var healthTwoStates = ["AZ","FL","GA","KS","MS","MO","NC","OH","OK","SC","TN","TX"];
    // var healthOneHighIncomes = ['55000-69999','70000-99999','100000+'];
    // var healthTwoHighIncomes = ['40000-54999','55000-69999','70000-99999','100000+'];
    var income = $('input[name="Household_Income"]').val();
    var lander, type;

    // People over 65 get a Medicare SRC
    if( age >= 65 ) {

      lander = '../thank-you/thank-you-h1-b.php';
      type = 'medicare';

    } else {

      // Set Landing Page Based on State and Income
      if ($.inArray(state, healthTwoStates) !== -1) {
        // Check Income for Health2 is over 40K
        // if($.inArray(income, healthTwoHighIncomes) !== -1) {
        if(income > 39999) {
            
          lander = '../thank-you/thank-you-h1-b.php';
          did = 'premium';
          
        } else {
          
          lander = '../thank-you/thank-you-h2-b.php';
          did = 'h2';
          
        }
        
      } else {

        // Check Income for Health1 is over 55K
        // if($.inArray(income, healthOneHighIncomes) !== -1) {
        if(income > 54999) {
            
          lander = '../thank-you/thank-you-h1-b.php';
          did = 'premium';
          
        } else {
          
          lander = '../thank-you/thank-you-h1-b.php';
          did = 'standard';

        }

      }

      type = 'healthcare';
  
    }

    // redirectUrl.val(lander + '?type=' + type + '&city=' + city + '&state=' + state + '&Household=1&Age=' + (age > 18 ? age : '18') + '&src=' + btoa(src) );
    $('input[name="Redirect_URL"]').val( lander + '?type=' + type + '&city=' + city + '&state=' + state + '&did=' + did + '&Household=1&first_name=' + fname + '&age=' + age + '&src=' + btoa(src));

  };


  $('#email').on('keyup', function() {
    checkEmail();
  });

  // Check for banned email addresses
  function checkEmail() {
    var emailAddress = $('#email').val();
    var badDomain = "@mailinator";
    if(emailAddress.indexOf(badDomain) != -1){
      console.log('Banned Email Found');
      $('.action-button').prop('disabled', true);
      ajaxInsertBadEmail();
    }
  }

  /* Inserts Bad Email Info to DB */
  function ajaxInsertBadEmail() {
    $.ajax({
      type: 'POST',
      url: 'https://healthcare-quotes.com/get-quotes/inc/insert_bademail.php',
      data: 'email=' + $('input[name="Email"]').val() + '&ipaddress=' + $('input[name="IP_Address"]').val() + '&referrer=' + $('input[name="notes"]').val() + '&city' + $('input[name="city"]').val() + '&state' + $('input[name="state"]').val() + '&zip' + $('input[name="zip"]').val(),
      success: function (response) {
        console.log(response);
        window.location = $('input[name="Redirect_URL"]').val();
      },
      error: function (response, jqXHR, textStatus, errorThrown) {
        console.log(response);
        window.location = $('input[name="Redirect_URL"]').val();
      } 
    });
    return false;
  }

  /* Inserts Confirmation to DB */
  function ajaxInsert(response) {
    $.ajax({
      type: 'POST',
      url: 'https://healthcare-quotes.com/get-quotes/inc/insert_confirmation.php',
      data: 'first_name=' + $('input[name="First_Name"]').val() + '&email=' + $('input[name="Email"]').val() + '&ipaddress=' + $('input[name="IP_Address"]').val() + '&error=' + response,
      success: function (data) {
        sessionStorage.setItem('entryStatus', 'success');
        console.log(sessionStorage.getItem('entryStatus'));
        console.log(sessionStorage.getItem('flex_lead'));
        if (sessionStorage.getItem('flex_lead') == 'true') {
          flexPostback();
        } else {
          window.location = $('input[name="Redirect_URL"]').val();
        }
      },
      error: function (data, jqXHR, textStatus, errorThrown) {
        sessionStorage.setItem('entryStatus', 'error');
        console.log(sessionStorage.getItem('entryStatus'));
        console.log(sessionStorage.getItem('flex_lead'));
        if (sessionStorage.getItem('flex_lead') == 'true') {
          flexPostback();
        } else {
          window.location = $('input[name="Redirect_URL"]').val();
        }
      }
    });
    return false;
  }

  /* Postback to Flex Media on Successful Submission */
  function flexPostback(response) {

    $.ajax({
      url: 'https://healthcare-quotes.com/fc/inc/postback-flex.php',
      type: "POST",
      data: {
        hid: sessionStorage.getItem('hid'),
        sid: '31917',
        transid: sessionStorage.getItem('userPhone')},
      dataType: "json",
      success: function(data) {
        sessionStorage.setItem('flexStatus', 'success');
        console.log(sessionStorage.getItem('flexStatus'));
        console.log(data);
        window.location = $('input[name="Redirect_URL"]').val();
      },
      error: function (data, jqXHR, textStatus, errorThrown) {
        sessionStorage.setItem('flexStatus', 'error');
        console.log(sessionStorage.getItem('flexStatus'));
        window.location = $('input[name="Redirect_URL"]').val();
      }
    })
    
    return false;
  }

  /* Get Age From DOB */
  function getAge() {
    
    // Build DOB from Birthdate Fields
    var BM = $('input[name="birthmonth"]').val();
    var BD = $('input[name="birthday"]').val();
    var BY = $('input[name="birthyear"]').val();
    var DOB = BM+'/'+BD+'/'+BY
    $('input[name="DOB"]').val(DOB);

    // Calculate Age from DOB
    var today = new Date();
    var birthDate = new Date(DOB);
    var age = today.getFullYear() - birthDate.getFullYear();
    var m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age = age - 1;
    }

    // Set Age Field
    $('input[name="Age"]').val(Math.floor(age));
    
  }

  // Assign SRC
  function setSRC() {

    // Assign SRC based on State & Income
    // var healthTwoStates = ["AL","AZ","FL","GA","KS","MS","MO","NC","OH","OK","SC","TN","TX"];
    // var comeState = sessionStorage.getItem('state');
    // var healthOneHighIncomes = ['55000-69999','70000-99999','100000+'];
    // var healthTwoHighIncomes = ['40000-54999','55000-69999','70000-99999','100000+'];
    // var income = $('input[name="Household_Income"]').val();

    // if ($.inArray(comeState, healthTwoStates)) {
    //   // Check Income and Reassign Health2 to Health1 if over 40K
    //   if($.inArray(income, healthTwoHighIncomes)) {
    //     $('input[name="SRC"]').val('Health1');
    //   } else {
    //     $('input[name="SRC"]').val('Health2');
    //   }
    // } else {
    //   // Check Income is not too low for Health1
    //   if($.inArray(income, healthOneHighIncomes) === -1) {
    //     $('input[name="SRC"]').val('Health1');
    //   } else {
    //     $('input[name="SRC"]').val('Health2');
    //   }
    // }
    // console.log($('input[name="SRC"]').val());
    
    ageSetSRC();

  }

  function ageSetSRC() {
    // People over 65 get a Medicare SRC
    var age = $('input[name="Age"]').val();
    if( age >= 65 ) {

      $('input[name="TYPE"]').val('23');
      $('input[name="SRC"]').val('WebPostMk');

    }
  }

  // Run function on Zip Code Step 
  if ( $('#msform').attr('aria-step') == '1' ) {
    setLocationFromZip(zipCode);
  }
  // Run functions on last step prior to form submission
  if ( $('#msform').attr('aria-step') == '6' ) {
    
    getAge();
    setSRC();
    setLocationFromZip($('[name=Zip]').val());
    redirectUrlUpdate();
    
  }
  function submitForm() {
    // Set Phone Number to Session
    var phoneNumber = $('input[name="Primary_Phone"]').val();
    phoneNumber = phoneNumber.replace(/[^0-9]/g, '');
    sessionStorage.setItem('userPhone', phoneNumber);
    console.log(sessionStorage.getItem('userPhone'));

    // US Area Codes
    // var all_us_area_codes = ["201","202","203","205","206","207","208","209","210","212","213","214","215","216","217","218","219","220","223","224","225","228","229","231","234","239","240","248","251","252","253","254","256","260","262","267","269","270","272","276","279","281","301","302","303","304","305","307","308","309","310","312","313","314","315","316","317","318","319","320","321","323","325","326","330","331","332","334","336","337","339","341","346","347","350","351","352","360","361","363","364","380","385","386","401","402","404","405","406","407","408","409","410","412","413","414","415","417","419","423","424","425","430","432","434","435","440","442","443","445","447","448","458","463","464","469","470","472","475","478","479","480","484","501","502","503","504","505","507","508","509","510","512","513","515","516","517","518","520","530","531","534","539","540","541","551","557","559","561","562","563","564","567","570","571","572","573","574","575","580","582","585","586","601","602","603","605","606","607","608","609","610","612","614","615","616","617","618","619","620","623","626","628","629","630","631","636","640","641","646","650","651","656","657","659","660","661","662","667","669","678","680","681","682","689","701","702","703","704","706","707","708","712","713","714","715","716","717","718","719","720","724","725","726","727","731","732","734","737","740","743","747","754","757","760","762","763","765","769","770","771","772","773","774","775","779","781","785","786","801","802","803","804","805","806","808","810","812","813","814","815","816","817","818","820","826","828","830","831","832","835","838","839","840","843","845","847","848","850","854","856","857","858","859","860","862","863","864","865","870","872","878","901","903","904","906","907","908","909","910","912","913","914","915","916","917","918","919","920","925","928","929","930","931","934","936","937","938","940","941","943","945","947","948","949","951","952","954","956","959","970","971","972","973","978","979","980","983","984","985","986","989","227","235","274","283","324","327","353","382","428","436","624","645","679","686","730","821","879","975"];
    var toll_free = ["800","888","877","866","855","844"];
    var fully_repeated = /(\d)(\1){9}/;
    var phone_prefix_repeated = /(\d)\1{2}/;

    // Perform the checks
    if ($.inArray(phoneNumber.substr(0, 3), toll_free) < 0 && 
        fully_repeated.test(phoneNumber.substr(0, 10)) == false &&
        phone_prefix_repeated.test(phoneNumber.substr(3, 3)) == false) {

      // Insert Trusted Form Token
      trustedformCert = $('input[name="xxTrustedFormToken"]').val();
      $('input[name="LeadiD_URL"]').val(trustedformCert);

      // Loading Modal - Turned On
      $('#loading').modal('show');

      // Claim Trusted Form Certificate
      $.ajax({
        url: 'https://healthcare-quotes.com/get-quotes/trusted-form-processing.php',
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

      // Progress Modal Popup
      const cp = new CircleProgress('.submit-progress', {
        value: 0,
        textFormat: 'percent',
        animation: 'easeInOutCubic',
        max: 100,
      })

      let currentValue = 0;
      const duration = 3000; // 3 seconds in milliseconds
      const intervalDuration = 100; // Choosing a small interval for smoother increments
      const totalIntervals = duration / intervalDuration;
      let intervalsPassed = 0;
      let cpText = 'Verifying Your Information';

      const interval = setInterval(function() {
        intervalsPassed++;

        if (currentValue <= 30) {
          console.log(cpText);
          cpText = 'Verifying Your Information';
        }
        if(currentValue > 30 && currentValue < 50) {
          console.log(cpText);
          cpText = 'Submitting Your Application';
        }
        if(currentValue > 50 && currentValue > 85) {
          console.log(cpText);
          cpText = 'Finalizing Application';
        }
        if(currentValue > 85) {
          console.log(cpText);
          cpText = 'Done. Your Information has been received!';
        }

        // If it's the last interval, set the currentValue to 100
        if (intervalsPassed === totalIntervals) {
          currentValue = 100;
          clearInterval(interval);

          setTimeout(function() {
            console.log($("#msform").not("#Redirect_URL,#birthmonth,#birthday,#birthyear").serialize());
            $.ajax({
              url: 'https://healthcare-quotes.com/get-quotes/form-processing.php',
              type: "POST",
              data: $("#msform").not("#Redirect_URL,#birthmonth,#birthday,#birthyear").serialize(),
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
      
                
                ajaxInsert(responseMessage);
      
              }
            });
  
        }, 600);
          
        } else {
          let maxPossibleIncrement = (100 - currentValue) / (totalIntervals - intervalsPassed);
          let randomIncrement = Math.random() * maxPossibleIncrement;
          currentValue += randomIncrement;
        }

        cp.value = Math.floor(currentValue); // Replace this with your action, e.g., updating an element
        $('.submit-text').text(cpText);

      }, intervalDuration);
      

    } else {

      alert('Invalid Phone Number');
      $('input[name=Primary_Phone]').removeClass('parsley-success').addClass('parsley-error')
      $('input[name=Primary_Phone]').after('<ul class="parsley-errors-list filled" id="parsley-id-5" aria-hidden="false"><li class="parsley-custom-error-message">The phone number you entered appears to be invalid.<br> Please re-enter and try again.</li></ul>');
      $('input[name=Primary_Phone]').val('');

    }

    //cancel the submit default behavior
    return false;

  }

  /* Last Call on exit popup 
  if (screen.width < 992) {
    var timer = 6000;
  } else {
    console.log()
    var timer = 8000;
		$(document).on('mouseleave', function (e) {
			if (e.pageY - $(window).scrollTop() <= 1) {
				$('#lastcall').modal('show');
			}
		});
	}
  */
      
// End Ready Function
});