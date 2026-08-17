/**
 * Mobile only: size the #grad1 form container so the trust badges (.badges-wrap)
 * sit completely visible at the bottom of the screen when scrolled to the top.
 * Runs on load/resize/orientation change and whenever the visible form step changes.
 */
(function () {
	'use strict';

	var MOBILE_MQ = '(max-width: 767px)';

	function fit() {
		var grad1 = document.getElementById('grad1');
		var badges = document.querySelector('.badges-wrap');
		if (!grad1 || !badges) {
			return;
		}

		// Desktop/tablet: clear any inline sizing and let CSS handle it.
		if (!window.matchMedia(MOBILE_MQ).matches) {
			grad1.style.minHeight = '';
			return;
		}

		// Measure natural layout without our previous override.
		grad1.style.minHeight = '';

		var grad1Top = grad1.getBoundingClientRect().top + window.pageYOffset;
		var badgesHeight = badges.getBoundingClientRect().height + 5;
		var contentHeight = grad1.offsetHeight;

		// On iOS Safari the fade strip is visible; reserve its height so the
		// badges are not covered by it.
		var reserve = 0;
		var fade = document.querySelector('.ah-safari-bottom-fade');
		if (fade && getComputedStyle(fade).display !== 'none') {
			reserve = fade.getBoundingClientRect().height;
		}

		var desired = window.innerHeight - grad1Top - badgesHeight - reserve;

		// Never clip the form: keep at least the natural content height.
		grad1.style.minHeight = Math.max(desired, contentHeight) + 'px';
	}

	var raf;
	function scheduleFit() {
		if (raf) {
			window.cancelAnimationFrame(raf);
		}
		raf = window.requestAnimationFrame(fit);
	}

	function init() {
		fit();

		window.addEventListener('resize', scheduleFit);
		window.addEventListener('orientationchange', scheduleFit);
		window.addEventListener('pageshow', scheduleFit);

		// Recompute when the visible fieldset changes (step navigation animates
		// display/opacity/height on .form-step elements inside #grad1).
		var grad1 = document.getElementById('grad1');
		if (grad1 && window.MutationObserver) {
			var debounce;
			var observer = new MutationObserver(function () {
				window.clearTimeout(debounce);
				debounce = window.setTimeout(fit, 120);
			});
			observer.observe(grad1, {
				attributes: true,
				attributeFilter: ['style', 'data-active'],
				subtree: true,
				childList: true
			});
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	// Re-fit after full load in case web fonts/images shift the layout.
	window.addEventListener('load', scheduleFit);
})();
