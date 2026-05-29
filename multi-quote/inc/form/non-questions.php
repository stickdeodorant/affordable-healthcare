<?php

// Initialize session if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required classes
require_once __DIR__ . '/../TrackingConfig.php';
require_once __DIR__ . '/../FormFieldGenerator.php';

// Configuration
$config = [
    'domain' => $_SERVER['HTTP_HOST'] ?? 'healthcare-quotes.com',
    'pivot_lpid' => defined('PIVOT_LPID') ? PIVOT_LPID : '1003'
];

// Initialize generator with sanitized superglobals
$generator = new FormFieldGenerator(
    $_SESSION,
    $_GET,
    $_SERVER,
    $config
);

// Generate and output fields
echo $generator->generateAllFields();

// Add CSRF token for security
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!-- CSRF Protection -->
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>" />
<pre>
    <?php print_r($_SESSION); ?>
</pre>