<?php
/**
 * Dynamic XML sitemap for published CMS pages.
 * Served physically at /sitemap.php (add an optional /sitemap.xml rewrite in
 * .htaccess if desired). Lists every published cms_pages slug with its last
 * modified date; degrades gracefully if the database is unavailable.
 */

require_once __DIR__ . '/cms/config.php';
require_once CMS_LIB . '/db.php';
require_once CMS_LIB . '/sanitize.php';

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = (string)($_SERVER['HTTP_HOST'] ?? 'affordable-healthcare.com');
$base = $scheme . '://' . $host;

$rows = [];
try {
    $rows = cms_select(
        "SELECT slug, updated_at, published_at FROM cms_pages WHERE status = 'published' ORDER BY slug ASC"
    );
} catch (\Throwable $e) {
    error_log('Sitemap DB error: ' . $e->getMessage());
    $rows = [];
}

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Homepage first.
echo '  <url><loc>' . cms_e($base) . '/</loc></url>' . "\n";

foreach ($rows as $row) {
    $slug = cms_slug((string)($row['slug'] ?? ''));
    if ($slug === '') {
        continue;
    }
    $lastmod = (string)($row['updated_at'] ?? $row['published_at'] ?? '');
    $ts = $lastmod !== '' ? strtotime($lastmod) : false;
    echo '  <url>';
    echo '<loc>' . cms_e($base . '/' . $slug) . '</loc>';
    if ($ts !== false) {
        echo '<lastmod>' . cms_e(date('Y-m-d', $ts)) . '</lastmod>';
    }
    echo '</url>' . "\n";
}

echo '</urlset>' . "\n";
