<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

require_once __DIR__ . '/../../inc/globalvars.php';
$faqItems = require __DIR__ . '/faqs.php';
$stateFaqs = require __DIR__ . '/state-faqs.php';

// Resolve which FAQ to render
$faqKey = isset($faqSlug) ? $faqSlug : '';
if (!$faqKey && isset($_GET['faq'])) {
	$faqKey = preg_replace('/[^a-z0-9-]/i', '', $_GET['faq']);
}

if (!$faqKey || !isset($faqItems[$faqKey])) {
	http_response_code(404);
	echo 'FAQ not found.';
	exit;
}

$faq = $faqItems[$faqKey];

// Determine state for optional, conditional content
$stateCode = '';
if (isset($_GET['st'])) {
	$stateCode = strtoupper($_GET['st']);
} elseif (!empty($_SESSION['location']['state'])) {
	$stateCode = strtoupper($_SESSION['location']['state']);
}

$stateContent = ($stateCode && isset($stateFaqs[$stateCode])) ? $stateFaqs[$stateCode] : '';
$stateName = $stateCode ? ($states[$stateCode] ?? $stateCode) : 'Your State';

$title = $sitename . ' | ' . $faq['title'];
$ctaHref = '/multi-quote/' . ($stateCode ? '?st=' . urlencode($stateCode) : '');

// Build list of other FAQs for quick navigation
$otherFaqs = array_filter($faqItems, function($key) use ($faqKey) {
	return $key !== $faqKey;
}, ARRAY_FILTER_USE_KEY);
?><!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
	<title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
	<?php include __DIR__ . '/../../inc/header.php'; ?>
</head>
<body>
<?php include __DIR__ . '/../../inc/nav.php'; ?>
<main role="main">
	<section class="container py-5">
		<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
			<div>
				<h1 class="h2 mb-2"><?= htmlspecialchars($faq['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
				<p class="lead mb-0">Get a clear answer and start your quote.</p>
			</div>
			<a class="btn btn-primary btn-lg mt-3 mt-md-0" href="<?= htmlspecialchars($ctaHref, ENT_QUOTES, 'UTF-8'); ?>">Start My Quote</a>
		</div>
	</section>
	<section class="container pb-5">
		<div class="row">
			<div class="col-lg-8 mb-4">
				<div class="card shadow-sm">
					<div class="card-body">
						<?= $faq['body']; ?>
					</div>
				</div>
				<?php if ($stateContent): ?>
					<div class="card shadow-sm mt-4">
						<div class="card-header">
							State-specific details for <?= htmlspecialchars($stateName, ENT_QUOTES, 'UTF-8'); ?>
						</div>
						<div class="card-body">
							<?= $stateContent; ?>
						</div>
					</div>
				<?php endif; ?>
				<div class="card shadow-sm mt-4">
					<div class="card-body">
						<h2 class="h5">Check plan options<?= $stateCode ? ' in ' . htmlspecialchars($stateName, ENT_QUOTES, 'UTF-8') : ''; ?></h2>
						<form action="/multi-quote/" method="get" class="form-inline">
							<div class="form-group mb-2 mr-sm-2">
								<label class="sr-only" for="zip-input">ZIP Code</label>
								<input id="zip-input" name="zip" class="form-control" placeholder="Enter ZIP" pattern="\d{5}" required>
							</div>
							<?php if ($stateCode): ?>
								<input type="hidden" name="st" value="<?= htmlspecialchars($stateCode, ENT_QUOTES, 'UTF-8'); ?>">
							<?php endif; ?>
							<button type="submit" class="btn btn-primary mb-2">See Plans</button>
						</form>
					</div>
				</div>
			</div>
			<div class="col-lg-4">
				<div class="card shadow-sm">
					<div class="card-header">Other FAQs</div>
					<ul class="list-group list-group-flush">
						<?php foreach ($otherFaqs as $key => $item): ?>
							<li class="list-group-item d-flex justify-content-between align-items-center">
								<a href="/content/faq/<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>.php<?= $stateCode ? '?st=' . urlencode($stateCode) : ''; ?>"><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></a>
								<span aria-hidden="true">›</span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</div>
	</section>
</main>
<?php include __DIR__ . '/../../inc/footer.php'; ?>
</body>
</html>
