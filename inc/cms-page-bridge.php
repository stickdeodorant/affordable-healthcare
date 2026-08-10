<?php
/**
 * Shared bridge used by top-level landing-page stubs.
 */

$scriptFile = (string)($_SERVER['SCRIPT_FILENAME'] ?? '');
$slug = pathinfo(basename($scriptFile !== '' ? $scriptFile : __FILE__), PATHINFO_FILENAME);

if ($slug === '' || $slug === 'index') {
    return;
}

$_GET['slug'] = $slug;
require __DIR__ . '/../cms/render.php';
exit;