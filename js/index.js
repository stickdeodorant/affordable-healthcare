$(function() {
	function featureFill() {
		$('header').outerHeight($(window).height() - $('header').offset().top + 1).css('min-height', $('header > .container').outerHeight() + 80);
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
				$('#plane-banner').css('transform', 'translateX(0%)');
			}
		}
	});

	$('.nav-item').on('click', function() {
		$(this).find('.fa-layers svg.fa-circle').attr('data-prefix', 'fas');
		$(this).siblings('.nav-item').find('.fa-layers svg.fa-circle').attr('data-prefix', 'fal');
	});

	$('#zip').keyup(function() {
		if($(this).val().length >= 5) {
			$('body').addClass('zip-filled');
			$('.zip-submit').prop('disabled', false);
			if ($(window).width() < 768) { $(this).blur(); }
		}
		else {
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