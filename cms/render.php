<?php
/**
 * Public front controller for CMS-managed pages.
 * Reached via the root .htaccess rewrite for slugs that don't map to a
 * physical file: /{slug} -> cms/render.php?slug={slug}
 *
 * Resolution order: published page -> redirect -> 404.
 * Uses the SAME public session as the rest of the site (started by inc/header.php),
 * so it does NOT include cms/bootstrap.php (which is for the admin area).
 */

require_once __DIR__ . '/config.php';
require_once CMS_LIB . '/db.php';
require_once CMS_LIB . '/sanitize.php';
require_once CMS_LIB . '/render-blocks.php';

$appRoot = CMS_APP_ROOT;

$slug = isset($_GET['slug']) ? cms_slug((string)$_GET['slug']) : '';
if ($slug === '') {
    cms_render_404($appRoot);
}

// Fetch the published page, guarding against DB unavailability.
try {
    $page = cms_select_one(
        "SELECT * FROM cms_pages WHERE slug = ? AND status = 'published' LIMIT 1",
        's',
        [$slug]
    );
} catch (\Throwable $e) {
    error_log('CMS render DB error: ' . $e->getMessage());
    http_response_code(503);
    header('Retry-After: 60');
    echo 'Service temporarily unavailable.';
    exit;
}

if (!$page) {
    // No page — honor a configured redirect, else 404.
    try {
        $redirect = cms_select_one(
            'SELECT to_path, code FROM cms_redirects WHERE from_path = ? LIMIT 1',
            's',
            ['/' . $slug]
        );
    } catch (\Throwable $e) {
        $redirect = null;
    }
    if ($redirect && cms_url_is_safe($redirect['to_path'])) {
        $code = in_array((int)$redirect['code'], [301, 302, 307, 308], true) ? (int)$redirect['code'] : 301;
        header('Location: ' . $redirect['to_path'], true, $code);
        exit;
    }
    cms_render_404($appRoot);
}

// Apply the page's theme via the public session (same mechanism as ?theme=).
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!empty($page['theme'])) {
    $_SESSION['ah_theme'] = $page['theme'];
}

// Variables consumed by inc/header.php and inc/hero.php.
$pageName = 'cms-' . $slug;
$title = $page['title'] !== '' ? $page['title'] : ucwords(str_replace('-', ' ', $slug));
$metaDescription = (string)($page['meta_description'] ?? '');
$canonical = (string)($page['canonical'] ?? '');
$ogImage = (string)($page['og_image'] ?? '');
$template = strtolower(trim((string)($page['template'] ?? 'default')));
$headline = $page['hero_headline'] !== '' ? $page['hero_headline'] : $title;
$featureTitle = cms_e($headline);
$subtitle = cms_e($page['hero_subtitle']);
$featureSubtitle = cms_e($page['hero_subtitle']);
$featureCaption = ['default' => '', 'mobile' => '', 'filled' => '', 'mfilled' => ''];

$blocks = cms_json_decode($page['body_json']);

include $appRoot . '/inc/header.php';
if ($template === 'feature') {
    $url = '/' . $slug;
    include $appRoot . '/inc/feature.php';
} elseif ($template === 'feature-og') {
    $url = '/' . $slug;
    include $appRoot . '/inc/feature-OG.php';
} else {
    include $appRoot . '/inc/hero.php';
}
echo '<main role="main">';
cms_render_blocks($blocks, $page);
echo '</main>';
include $appRoot . '/inc/footer.php';
exit;

/**
 * Render a branded 404 using the site shell, then stop.
 */
function cms_render_404(string $appRoot): void {
    http_response_code(404);
    $GLOBALS['title'] = 'Page Not Found';
    $title = 'Page Not Found';
    $featureTitle = 'Page Not Found';
    $subtitle = '';
    $featureSubtitle = '';
    $featureCaption = ['default' => '', 'mobile' => '', 'filled' => '', 'mfilled' => ''];
    include $appRoot . '/inc/header.php';
    echo '<main role="main"><section class="container py-5 my-5 text-center">'
        . '<h1 class="h1">We couldn\'t find that page</h1>'
        . '<p class="lead">The page you were looking for isn\'t available.</p>'
        . '<a class="button bg-primary text-white" href="/">Return Home</a>'
        . '</section></main>';
    include $appRoot . '/inc/footer.php';
    exit;
}
