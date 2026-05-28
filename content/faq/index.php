<?php
// Single entry point for FAQ content; uses template to render with state selection logic.
require_once __DIR__ . '/../../inc/globalvars.php';
$faqItems = require __DIR__ . '/faqs.php';

$title = $sitename . ' | Health Insurance FAQs';
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
	<title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
	<?php include __DIR__ . '/../../inc/header.php'; ?>
</head>
<body>
<?php include __DIR__ . '/../../inc/nav.php'; ?>
<main role="main" class="container py-5">
	<h1 class="h2 mb-4">Health Insurance FAQs</h1>
	<p class="lead">Choose a topic to see a dedicated answer and start your quote.</p>
	<div class="row">
		<?php foreach ($faqItems as $key => $faq): ?>
			<div class="col-md-6 col-lg-4 mb-3">
				<div class="card h-100 shadow-sm">
					<div class="card-body d-flex flex-column">
						<h2 class="h5"><?= htmlspecialchars($faq['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
						<div class="mt-auto">
							<a class="btn btn-primary btn-sm" href="/content/faq/<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>.php">Read FAQ</a>
						</div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</main>
<?php include __DIR__ . '/../../inc/footer.php'; ?>
</body>
</html>
