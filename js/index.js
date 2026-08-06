$(function() {
	function featureFill() {
		var $header = $('header');
		if (!$header.length || !$header.offset()) {
			return;
		}

		var $headerContainer = $header.find('> .container');
		var minHeight = ($headerContainer.length ? $headerContainer.outerHeight() : 0) + 80;
		$header.outerHeight($(window).height() - $header.offset().top + 1).css('min-height', minHeight);
	}
	featureFill();
	function planeTextOffset() {
		$('#plane-banner svg').each(function() {
			$(this).find('textPath').attr('startOffset', ((((($(this).find('#SVGID_plane_bannersize')[0].getBoundingClientRect().height * 1.0) + ($(this).find('#SVGID_plane_bannersize')[0].getBoundingClientRect().width * 1.5)) - ($(this).find('tspan')[0].getBoundingClientRect().width / 2)) / (($(this).find('#SVGID_plane_bannersize')[0].getBoundingClientRect().height * 2.0) + ($(this).find('#SVGID_plane_bannersize')[0].getBoundingClientRect().width * 2.0))) * 100) + "%");
		});
	}
	planeTextOffset();
	$(window).resize(function() {
		featureFill();
		planeTextOffset();
	})
	$(window).on('scroll', function() {
		if($('#plane-banner').length){ 
			if ($(this).scrollTop() > ($('#plane-banner').offset().top + $('#plane-banner').height() - $(window).height())) {
				$('#plane-banner').addClass('is-visible');
			}
		}
	});

	function toggleHeaderScrolledState() {
		var header = document.querySelector('.ah-header');
		if (!header) {
			return;
		}

		var isScrolled = window.scrollY > 8;
		header.classList.toggle('ah-header-scrolled', isScrolled);

		var note = header.querySelector('.ah-header-note');
		var promo = header.querySelector('.ah-header-promo');
		[note, promo].forEach(function(el) {
			if (!el) {
				return;
			}

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
	}

	toggleHeaderScrolledState();
	window.addEventListener('scroll', toggleHeaderScrolledState, { passive: true });
	window.addEventListener('resize', toggleHeaderScrolledState);

	$('.nav-item').on('click', function() {
		$(this).find('.fa-layers svg.fa-circle').attr('data-prefix', 'fas');
		$(this).siblings('.nav-item').find('.fa-layers svg.fa-circle').attr('data-prefix', 'fal');
	});

	$('#zip').on('input', function() {
		var digitsOnly = $(this).val().replace(/\D/g, '').slice(0, 5);
		if ($(this).val() !== digitsOnly) {
			$(this).val(digitsOnly);
		}

		if (digitsOnly.length >= 5) {
			$('body').addClass('zip-filled');
			$('.zip-submit').prop('disabled', false);
			if ($(window).width() < 768) {
				$(this).blur();
			}
		} else {
			$('body').removeClass('zip-filled');
			$('.zip-submit').prop('disabled', true);
		}
	});

	var referrerURL = document.referrer;
	if($('input[name="notes"]').val().length == 0) {
		$('input[name="notes"]').val(referrerURL);
	}

	/* Last Call on exit popup */
  if (screen.width < 992) {
    var timer = 6000;
  } else {
    var timer = 8000;
		$(document).on('mouseleave', function (e) {
			console.log(e.pageY);
			if (e.pageY - $(window).scrollTop() <= 1) {
				$('#lastcall').modal('show');
			}
		});
	}

	setTimeout(function() {
    featureFill();
  }, 1000);

	// Scroll To Top
	$('.scroll-top').click(function(event) {
		event.preventDefault();
		window.scrollTo({top: 0, behavior: 'smooth'});
	});
});