<script>
// Scroll to ZIP form and focus input
document.querySelectorAll('.scroll-to-zip').forEach(function(el) {
	el.addEventListener('click', function(e) {
		e.preventDefault();
		var zipForm = document.getElementById('zip-form');
		var zipInput = zipForm.querySelector('.zip-input');
		zipForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
		setTimeout(function() {
			zipInput.focus();
		}, 500);
	});
});

// FAQ Tabs Mobile Toggle
(function() {
	var toggle = document.querySelector('.faq-tabs-toggle');
	var nav = document.querySelector('.faq-tabs-nav');
	
	if (toggle && nav) {
		toggle.addEventListener('click', function() {
			var isExpanded = toggle.getAttribute('aria-expanded') === 'true';
			toggle.setAttribute('aria-expanded', !isExpanded);
			nav.classList.toggle('show');
		});
		
		// Close dropdown when clicking outside
		document.addEventListener('click', function(e) {
			if (!e.target.closest('.faq-tabs')) {
				toggle.setAttribute('aria-expanded', 'false');
				nav.classList.remove('show');
			}
		});
		
		// Close dropdown when a link is clicked
		nav.querySelectorAll('a').forEach(function(link) {
			link.addEventListener('click', function() {
				toggle.setAttribute('aria-expanded', 'false');
				nav.classList.remove('show');
			});
		});
	}
})();
</script>
