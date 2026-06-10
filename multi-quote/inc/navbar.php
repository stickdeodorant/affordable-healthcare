<nav class="d-flex align-items-center">
  <?php $logoVersion = @filemtime(__DIR__ . '/../../img/logo.svg') ?: time(); ?>
  <div class="container h-100" style="color: #333;">
    <div class="row align-items-center h-100">
      <div class="col-lg-4 col-xl-5 text-center text-sm-left">
        <img class="logo" src="/img/logo.svg?v=<?= $logoVersion ?>" alt="<?php echo $sitename; ?> logo">
      </div>
      <?php /* if($call_now == 'true')  { ?>
        <div class="col-lg-8 col-xl-7 py-2 py-md-0 text-center text-lg-right d-none d-md-flex justify-content-end align-items-center">
          <div class="d-inline-block">
            <p class="d-flex mb-0">Need&nbsp;a&nbsp;quote?&ensp;<b style="font-weight:600;">Call&nbsp;toll&nbsp;free:&ensp;</b></p>
          </div>
          <div class="d-inline-block">
            <div class="d-flex">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon-phone d-inline-block" fill="#124085" width="40px" height="40px">
                <path d="M4.292 2.353S-2.582 7.708 1.07 17.338s7.854 16.674 15.8 20.953 12.988-.835 12.988-.835L21.26 25.9s-3.653 6.422-9.236-.213c-4.083-5.14-4.514-8.56.214-11.77C12.47 9.317 6.212 2.9 4.29 2.354zm13.2 6.14l.285 3.8a5.96 5.96 0 015.081 2.251c.914 1.3 1.342 2.42.564 5.908a31.58 31.58 0 013.668 1.685s1.837-6.6-1.4-10.405a9.03 9.03 0 00-8.188-3.239zm.7-3.794l.064-4.7s11.236.57 15.317 8.915a22.485 22.485 0 01.8 16.98l-4.08-2.136s2.342-8.6-1.72-13.913A13.47 13.47 0 0018.192 4.7z"></path>
              </svg>
              <div class="d-inline-flex align-items-center text-center ml-1">
                <a class="d-block text-primary" style="color: #124085 !important; display: block; font-size: 28px;font-weight:600;line-height: 1;" href="tel:<?=$phonemin['agent']?>"><?=$phone['agent']?></a>
                <span class="d-none">Mon - Sat 9 AM - 6 PM EST</span>
              </div>
            </div>
          </div>
        </div>
      <?php } */ ?>
    </div>
  </div>
</nav>
<div id="banner" style="display: block; position: relative; margin: 0 0 -1px; padding: 10px 15px; background: #124085; font-family: Montserrat,Helvetica,Arial,sans-serif; color: #fff; text-align: center; font-size: 18px;">
  <span>Plans Available - <span style="font-weight: 600; color: #fff;">Shop&nbsp;Now!</span></span>
</div>