<nav class="ah-header">
  <?php $logoVersion = @filemtime(__DIR__ . '/../img/logo.svg') ?: time(); ?>
  <!-- <div style="padding: 5px 20px; font-size: 12px; text-align: center; background: #d2d2d285; margin-bottom: 5px;" class="disc-aff">This website is not affiliated with, endorsed by, or operated by any federal or state government agency.</div> -->
  <div class="ah-header-note">This is a marketing website and is not affiliated or endorsed by any state, government or federal agency.</div>
  <div id="banner" class="ah-header-promo">
    <span>Health Plans Available - <span class="availability-strong">Shop&nbsp;Now!</span></span>
  </div>
  <div class="ah-header-bar d-flex align-items-center">
    <div class="container h-100 ah-header-shell">
      <div class="row align-items-center h-100">
        <div class="col-lg-4 col-xl-5 text-center text-sm-left ah-header-brand"><img class="ah-header-logo" src="/img/logo.svg?v=<?= $logoVersion ?>" alt="<?php echo $sitename; ?> logo"></div>
        <div class="col-lg-8 col-xl-7 py-2 py-md-0 text-center text-lg-right d-none d-md-flex justify-content-end align-items-center ah-header-links">
          <a href="/">Home</a>
          <a href="/smart-shopping.php">Smart Shopping</a>
          <a href="/consumer-caution.php">Consumer Caution</a>
          <a href="/faq.php">FAQ</a>
          <a href="/terms.php">Terms</a>
        </div>
      </div>
    </div>
  </div>
</nav>

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