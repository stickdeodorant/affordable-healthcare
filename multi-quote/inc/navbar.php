<nav class="d-flex align-items-center">
  <?php
    $logoSrc = isset($activeLogoSrc) ? $activeLogoSrc : '/img/logo.svg';
    $logoPath = isset($activeLogoPath) ? $activeLogoPath : (__DIR__ . '/../../img/logo.svg');
    $logoVersion = isset($activeLogoVersion) ? $activeLogoVersion : (@filemtime($logoPath) ?: time());
    $siteNameForAlt = isset($sitename) ? $sitename : 'affordable-healthcare.com';
    $showVariantDebug = isset($_GET['debug']) && $_GET['debug'] === '1';
    $activeThemeName = isset($activeTheme) ? $activeTheme : 'default';
    $themeControlValue = '1';
    if ($activeThemeName === 'logo-match') {
      $themeControlValue = '2';
    } elseif ($activeThemeName === 'ohio-healthplans') {
      $themeControlValue = '3';
    }
    $logoDebugLabel = isset($activeLogoNumber) ? intval($activeLogoNumber) : 1;
    $activeOverrides = isset($storedOverrides) && is_array($storedOverrides) ? $storedOverrides : [];
    $colorVariantSets = isset($colorVariants) && is_array($colorVariants) ? $colorVariants : [];
    $defaultSwatchHex = [
      'primary' => isset($resolvedPalette['primary']) ? $resolvedPalette['primary'] : '',
      'secondary' => isset($resolvedPalette['secondary']) ? $resolvedPalette['secondary'] : '',
      'accent' => isset($resolvedPalette['accent']) ? $resolvedPalette['accent'] : '',
      'tertiary' => isset($resolvedPalette['tertiary']) ? $resolvedPalette['tertiary'] : '',
      'input' => isset($resolvedPalette['input-border']) ? $resolvedPalette['input-border'] : '',
      'background' => isset($resolvedPalette['bg-page']) ? $resolvedPalette['bg-page'] : '',
      'panelbg' => isset($resolvedPalette['bg-panel']) ? $resolvedPalette['bg-panel'] : '',
      'svg' => isset($resolvedPalette['svg']) ? $resolvedPalette['svg'] : '',
    ];
    $debugLogoOptions = [];
    $logoAssetDir = __DIR__ . '/../../img';
    if (file_exists($logoAssetDir . '/logo.svg')) {
      $debugLogoOptions[1] = '/img/logo.svg';
    }
    for ($logoIdx = 2; $logoIdx <= 20; $logoIdx++) {
      foreach (['svg', 'png', 'webp'] as $ext) {
        $candidatePath = $logoAssetDir . '/logo-' . $logoIdx . '.' . $ext;
        if (file_exists($candidatePath)) {
          $debugLogoOptions[$logoIdx] = '/img/logo-' . $logoIdx . '.' . $ext;
          break;
        }
      }
    }
    if (empty($debugLogoOptions)) {
      $debugLogoOptions[1] = '/img/logo.svg';
    }

    $renderVariantOptions = function ($key, $selected) use ($colorVariantSets) {
      $html = '<option value="0" data-hex="">Theme default</option>';
      if (!isset($colorVariantSets[$key]) || !is_array($colorVariantSets[$key])) {
        return $html;
      }
      foreach ($colorVariantSets[$key] as $idx => $hex) {
        $isSelected = intval($selected) === intval($idx) ? ' selected' : '';
        $safeHex = htmlspecialchars($hex, ENT_QUOTES, 'UTF-8');
        $html .= '<option value="' . intval($idx) . '" data-hex="' . $safeHex . '"' . $isSelected . '>#' . intval($idx) . ' ' . $safeHex . '</option>';
      }
      return $html;
    };
  ?>
  <div class="container h-100" style="color: #333;">
    <div class="row align-items-center h-100">
      <div class="col-lg-4 col-xl-5 text-center text-sm-left">
        <img class="logo" src="<?= htmlspecialchars($logoSrc, ENT_QUOTES, 'UTF-8') ?>?v=<?= $logoVersion ?>" alt="<?php echo $siteNameForAlt; ?> logo">
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
<?php include __DIR__ . '/../../inc/debug-panel.php'; ?>
<div id="banner" style="display: block; position: relative; margin: 0 0 -1px; padding: 10px 15px; background: #124085; font-family: Montserrat,Helvetica,Arial,sans-serif; color: #fff; text-align: center; font-size: 18px;">
  <span>Plans Available - <span style="font-weight: 600; color: #fff;">Shop&nbsp;Now!</span></span>
</div>