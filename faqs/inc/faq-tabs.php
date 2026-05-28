<?php
/**
 * FAQ Tab Navigation
 * 
 * Include this file after the hero section in each FAQ page.
 * Set $currentFaqPage before including to highlight the active tab.
 * 
 * Example: $currentFaqPage = 'aca-information';
 */

$faqPages = [
	'aca-information' => 'ACA Basics',
	'open-enrollment' => 'Open Enrollment',
	'subsidies-tax-credits' => 'Subsidies & Savings',
	'ppo-vs-hmo' => 'PPO vs HMO',
	'medicare-basics' => 'Medicare',
	'short-term-health' => 'Short-Term Plans',
];

$currentLabel = $faqPages[$currentFaqPage] ?? 'Select Topic';
?>
<nav class="faq-tabs">
	<div class="container">
		<!-- Mobile Toggle Button -->
		<button class="faq-tabs-toggle" type="button" aria-expanded="false" aria-controls="faqTabsNav">
			<span class="faq-tabs-label">
				<svg class="faq-tabs-icon" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M2 4H14M2 8H14M2 12H14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
				<span class="faq-tabs-text">FAQ Topics: <strong><?= htmlspecialchars($currentLabel) ?></strong></span>
			</span>
			<svg class="faq-tabs-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M2 4L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</button>
		
		<!-- Navigation List -->
		<ul class="faq-tabs-nav" id="faqTabsNav">
			<?php foreach ($faqPages as $slug => $label): ?>
				<li>
					<a href="/faqs/<?= $slug ?>.php"<?= ($currentFaqPage === $slug) ? ' class="active"' : '' ?>><?= htmlspecialchars($label) ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</nav>
