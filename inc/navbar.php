<nav class="ah-header">
  <?php
    $logoSrc = isset($activeLogoSrc) ? $activeLogoSrc : '/img/logo.svg';
    $logoPath = isset($activeLogoPath) ? $activeLogoPath : (__DIR__ . '/../img/logo.svg');
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
    $logoAssetDir = __DIR__ . '/../img';
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
  <!-- <div style="padding: 5px 20px; font-size: 12px; text-align: center; background: #d2d2d285; margin-bottom: 5px;" class="disc-aff">This website is not affiliated with, endorsed by, or operated by any federal or state government agency.</div> -->
  <div class="ah-header-note">This is a marketing website and is not affiliated or endorsed by any state, government or federal agency.</div>
  <div id="banner" class="ah-header-promo">
    <span>Health Plans Available - <span class="availability-strong">Shop&nbsp;Now!</span></span>
  </div>
  <div class="ah-header-bar d-flex align-items-center">
    <div class="container h-100 ah-header-shell">
      <div class="row align-items-center h-100">
        <div class="col-lg-4 col-xl-5 text-center text-sm-left ah-header-brand"><img class="ah-header-logo" src="<?= htmlspecialchars($logoSrc, ENT_QUOTES, 'UTF-8') ?>?v=<?= $logoVersion ?>" alt="<?php echo $siteNameForAlt; ?> logo"></div>
        <div class="col-lg-8 col-xl-7 py-2 py-md-0 text-center text-lg-right d-none d-md-flex justify-content-end align-items-center ah-header-links">
          <a href="/">Home</a>
          <a href="/smart-shopping.php">Smart Shopping</a>
          <a href="/consumer-caution.php">Consumer Caution</a>
          <a href="/faq.php">FAQ</a>
          <a href="/terms.php">Terms</a>
          <?php $ahHeaderPhone = isset($phone['fb-call']) ? $phone['fb-call'] : '(888) 555-0199'; ?>
          <a href="tel:<?= preg_replace('/\D/', '', $ahHeaderPhone) ?>" class="ah-header-phone ml-2 font-weight-bold">&#9742;&nbsp;<?= htmlspecialchars($ahHeaderPhone, ENT_QUOTES, 'UTF-8') ?></a>
        </div>
      </div>
    </div>
  </div>
</nav>
<?php include __DIR__ . '/debug-panel.php'; ?>

<script>
  (function() {
    var header = document.querySelector('.ah-header');
    if (!header) return;

    var updateHeaderState = function() {
      var isScrolled = window.scrollY > 8;
      if (isScrolled) {
        header.classList.add('ah-header-scrolled');
      } else {
        header.classList.remove('ah-header-scrolled');
      }

      var note = header.querySelector('.ah-header-note');
      var promo = header.querySelector('.ah-header-promo');
      [note, promo].forEach(function(el) {
        if (!el) return;

        if (!el.dataset.collapseInit) {
          el.style.overflow = 'hidden';
          el.style.transition = 'max-height 0.22s ease, padding 0.22s ease, opacity 0.18s ease';
          el.dataset.collapseInit = '1';
        }

        if (isScrolled) {
          el.style.setProperty('max-height', '0px', 'important');
          el.style.setProperty('padding-top', '0px', 'important');
          el.style.setProperty('padding-bottom', '0px', 'important');
          el.style.setProperty('opacity', '0', 'important');
          el.style.setProperty('pointer-events', 'none', 'important');
          el.setAttribute('hidden', 'hidden');
        } else {
          el.style.removeProperty('max-height');
          el.style.removeProperty('padding-top');
          el.style.removeProperty('padding-bottom');
          el.style.removeProperty('opacity');
          el.style.removeProperty('pointer-events');
          el.removeAttribute('hidden');
        }
      });
    };

    updateHeaderState();
    window.addEventListener('scroll', updateHeaderState, { passive: true });
    window.addEventListener('resize', updateHeaderState);
  })();
</script>