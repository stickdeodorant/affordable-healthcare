<?php
/**
 * CLI: import current top-level landing pages into the CMS.
 *
 * Usage (from project root):
 *   php cms/cli/import-top-level-pages.php [--publish] [--dry-run] [--local-only]
 *
 * By default the imported pages are created as review pages so the current
 * physical PHP files can continue serving traffic until they are retired.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script is CLI-only.\n");
}

require_once __DIR__ . '/../bootstrap.php';

$appRoot = CMS_APP_ROOT;
$options = getopt('', ['publish', 'dry-run', 'local-only']);
$publish = array_key_exists('publish', $options);
$dryRun = array_key_exists('dry-run', $options);
$preferProductionSnapshot = !array_key_exists('local-only', $options);
$status = $publish ? 'published' : 'review';

$files = glob($appRoot . DIRECTORY_SEPARATOR . '*.php') ?: [];
sort($files, SORT_NATURAL | SORT_FLAG_CASE);

$imported = 0;
$skipped = 0;
$report = [];

foreach ($files as $file) {
    $base = basename($file);
    if ($base === 'index.php') {
        $report[] = [$base, 'skipped', 'root homepage is not routable through CMS slugs'];
        $skipped++;
        continue;
    }

    $source = file_get_contents($file);
    if ($source === false) {
        $report[] = [$base, 'skipped', 'unable to read source file'];
        $skipped++;
        continue;
    }

    $legacySource = cms_git_show_file($base);
    if (cms_is_bridge_stub($source) && $legacySource !== '') {
        $source = $legacySource;
    }

    if (!cms_is_importable_landing_page($source)) {
        $report[] = [$base, 'skipped', 'does not match the shared landing-page template structure'];
        $skipped++;
        continue;
    }

    $slug = pathinfo($base, PATHINFO_FILENAME);
    $template = cms_infer_template_family($source);
    [$rendered, $title, $headline, $subtitle, $metaDescription, $ctaText, $ctaHref] = cms_capture_legacy_page($file, $slug, $preferProductionSnapshot);
    if ($rendered === '') {
        $report[] = [$base, 'skipped', 'rendered output was empty'];
        $skipped++;
        continue;
    }

    $body = [[
        'type' => 'snapshot_html',
        'html' => $rendered,
    ]];
    $bodyJson = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $canonical = rtrim((string)env('APP_URL', ''), '/');
    if ($canonical !== '') {
        $canonical .= '/' . $slug;
    } else {
        $canonical = '/' . $slug;
    }

    if (!$dryRun) {
        $result = cms_write(
            "INSERT INTO cms_pages
                (slug, title, meta_description, canonical, og_image, template, theme, status,
                 hero_headline, hero_subtitle, cta_text, cta_href, body_json, created_by, updated_by, published_at)
             VALUES (?, ?, ?, ?, '', ?, 'default', ?, ?, ?, ?, ?, ?, 'import', 'import', ?)
             ON DUPLICATE KEY UPDATE
                title = VALUES(title),
                meta_description = VALUES(meta_description),
                canonical = VALUES(canonical),
                template = VALUES(template),
                status = VALUES(status),
                hero_headline = VALUES(hero_headline),
                hero_subtitle = VALUES(hero_subtitle),
                cta_text = VALUES(cta_text),
                cta_href = VALUES(cta_href),
                body_json = VALUES(body_json),
                updated_by = 'import',
                published_at = VALUES(published_at)",
            'ssssssssssss',
            [
                $slug,
                $title,
                $metaDescription,
                $canonical,
                $template,
                $status,
                $headline,
                $subtitle,
                $ctaText,
                $ctaHref,
                $bodyJson,
                $publish ? date('Y-m-d H:i:s') : null,
            ]
        );

        if ($result === false) {
            $report[] = [$base, 'error', 'database write failed'];
            $skipped++;
            continue;
        }
    }

    $report[] = [$base, $dryRun ? 'dry-run' : 'imported', $template . ' / ' . $status];
    $imported++;
}

$lines = [];
$lines[] = $dryRun ? 'Dry run completed.' : 'Import completed.';
$lines[] = 'Imported: ' . $imported;
$lines[] = 'Skipped: ' . $skipped;
$lines[] = '';
foreach ($report as [$file, $state, $message]) {
    $lines[] = sprintf('%-28s %-10s %s', $file, $state, $message);
}

fwrite(STDOUT, implode(PHP_EOL, $lines) . PHP_EOL);
exit(0);

/**
 * Determine whether a root PHP file looks like one of the shared landing pages.
 */
function cms_is_importable_landing_page(string $source): bool {
    if (stripos($source, 'require_once __DIR__ . \'/cms/bootstrap.php\'') !== false) {
        return false;
    }
    if (stripos($source, 'require_once __DIR__ . \'/cms/render.php\'') !== false) {
        return false;
    }
    if (stripos($source, 'inc/header.php') === false) {
        return false;
    }
    if (stripos($source, 'inc/hero.php') === false && stripos($source, 'inc/feature.php') === false && stripos($source, 'inc/feature-OG.php') === false) {
        return false;
    }
    if (preg_match('/\b(exit|die)\s*\(/i', $source)) {
        return false;
    }
    return true;
}

/**
 * Detect a one-line CMS bridge stub so the importer can fall back to git history.
 */
function cms_is_bridge_stub(string $source): bool {
    $normalized = preg_replace('/\s+/', ' ', trim($source));
    return $normalized === '<?php require __DIR__ . \'/inc/cms-page.php\';'
        || $normalized === '<?php require __DIR__ . \'/inc/cms-page-bridge.php\';';
}

/**
 * Map the source file to the closest CMS template family.
 */
function cms_infer_template_family(string $source): string {
    if (stripos($source, 'inc/feature-OG.php') !== false) {
        return 'feature-og';
    }
    if (stripos($source, 'inc/feature.php') !== false) {
        return 'feature';
    }
    return 'default';
}

/**
 * Render a legacy landing page and extract the main content plus a few headline fields.
 */
function cms_capture_legacy_page(string $file, string $slug, bool $preferProductionSnapshot = true): array {
    $oldServer = $_SERVER;
    $oldGet = $_GET;
    $oldErrorHandler = set_error_handler(function () {
        return true;
    });
    $envOverrides = [
        'APP_ENV' => 'production',
        'ENABLE_ANALYTICS' => '1',
        'APP_DOMAIN' => 'affordable-healthcare.com',
        'APP_URL' => 'https://affordable-healthcare.com',
    ];
    $oldEnv = [];
    foreach ($envOverrides as $k => $v) {
        $oldEnv[$k] = getenv($k);
        putenv($k . '=' . $v);
        $_ENV[$k] = $v;
    }
    $gitSource = cms_git_show_file($slug . '.php');

    $_SERVER['HTTP_HOST'] = 'affordable-healthcare.com';
    $_SERVER['SERVER_NAME'] = 'affordable-healthcare.com';
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['REQUEST_URI'] = '/' . $slug . '.php';
    $_SERVER['SCRIPT_NAME'] = '/' . $slug . '.php';
    $_SERVER['PHP_SELF'] = '/' . $slug . '.php';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET = ['state' => ''];
    $state = '';
    $state_abbr = '';
    $call = '';
    $featureFlags = ['enable_legacy_pages' => true];
    $nophone = false;
    require_once CMS_APP_ROOT . '/inc/globalvars.php';
        if ($gitSource !== '') {
            $gitSource = cms_inject_call_fallback($gitSource);
        }

    try {
        $rendered = '';
        if ($preferProductionSnapshot) {
            $rendered = cms_fetch_production_snapshot($slug);
        }
        if ($rendered === '') {
            if ($gitSource !== '') {
                $tempFile = CMS_APP_ROOT . DIRECTORY_SEPARATOR . '__cms_import_' . $slug . '_' . uniqid() . '.php';
                file_put_contents($tempFile, $gitSource);
                $file = $tempFile;
            }
            ob_start();
            include $file;
            $rendered = (string)ob_get_clean();
        }
    } finally {
        if (isset($tempFile) && is_file($tempFile)) {
            unlink($tempFile);
        }
        foreach ($envOverrides as $k => $_) {
            if ($oldEnv[$k] === false) {
                putenv($k);
                unset($_ENV[$k]);
            } else {
                putenv($k . '=' . $oldEnv[$k]);
                $_ENV[$k] = $oldEnv[$k];
            }
        }
        $_GET = $oldGet;
        $_SERVER = $oldServer;
        if ($oldErrorHandler !== null) {
            restore_error_handler();
        }
    }

    $title = cms_extract_title($rendered);
    $bodyHtml = cms_extract_main_html($rendered);
    $headline = $title !== '' ? $title : ucwords(str_replace('-', ' ', $slug));
    $summary = cms_extract_summary($bodyHtml, $headline);
    $cta = cms_extract_cta($bodyHtml);

    return [$rendered, $title !== '' ? $title : $headline, $headline, $summary, $summary, $cta[0], $cta[1]];
}

    /**
     * Insert a real fb-call fallback into legacy source immediately after the header include.
     */
    function cms_inject_call_fallback(string $source): string {
        $snippet = "\nif (!isset(\$phone['fb-call']) || trim((string)\$phone['fb-call']) === '') {\n    \$phone['fb-call'] = \$phone['popup'] ?? (\$phone['typ'] ?? '(888) 555-0199');\n}\nif (!isset(\$phonemin['fb-call']) || trim((string)\$phonemin['fb-call']) === '') {\n    \$phonemin['fb-call'] = preg_replace('/[^0-9]/', '', (string)\$phone['fb-call']);\n}\n";

        $patterns = [
            "include 'inc/header.php';",
            'include "inc/header.php";',
            "require_once 'inc/header.php';",
            'require_once "inc/header.php";',
            "include __DIR__ . '/inc/header.php';",
            'include __DIR__ . "/inc/header.php";',
            "require_once __DIR__ . '/inc/header.php';",
            'require_once __DIR__ . "/inc/header.php";',
        ];

        foreach ($patterns as $pattern) {
            if (strpos($source, $pattern) !== false) {
                return preg_replace('/' . preg_quote($pattern, '/') . '/', $pattern . $snippet, $source, 1);
            }
        }

        return $source;
    }

function cms_git_show_file(string $relativePath): string {
    $cmd = 'git show HEAD:' . escapeshellarg($relativePath);
    $output = [];
    $code = 0;
    exec($cmd . ' 2>NUL', $output, $code);
    return $code === 0 ? implode("\n", $output) : '';
}

function cms_fetch_production_snapshot(string $slug): string {
    $url = 'https://affordable-healthcare.com/' . $slug . '.php';

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 20,
            'header' => "User-Agent: CMSImporter/1.0\r\n",
        ],
    ]);

    $html = @file_get_contents($url, false, $context);
    if (is_string($html) && cms_is_snapshot_html($html)) {
        return $html;
    }

    $cmd = 'curl -sL --max-time 20 ' . escapeshellarg($url);
    $output = [];
    $code = 0;
    exec($cmd . ' 2>NUL', $output, $code);
    if ($code !== 0) {
        return '';
    }

    $html = implode("\n", $output);
    return cms_is_snapshot_html($html) ? $html : '';
}

function cms_is_snapshot_html(string $html): bool {
    if (trim($html) === '') {
        return false;
    }
    return stripos($html, '<html') !== false && stripos($html, '<body') !== false;
}

function cms_extract_title(string $html): string {
    $dom = cms_dom_from_html($html);
    if (!$dom) {
        return '';
    }
    $titles = $dom->getElementsByTagName('title');
    if ($titles->length > 0) {
        return trim((string)$titles->item(0)->textContent);
    }
    return '';
}

function cms_extract_main_html(string $html): string {
    $dom = cms_dom_from_html($html);
    if (!$dom) {
        return '';
    }
    $xpath = new DOMXPath($dom);
    $main = $xpath->query('//main')->item(0);
    if (!$main instanceof DOMNode) {
        $body = $xpath->query('//body')->item(0);
        $main = $body instanceof DOMNode ? $body : null;
    }
    if (!$main) {
        return '';
    }
    return cms_dom_inner_html($dom, $main);
}

function cms_extract_summary(string $html, string $fallback): string {
    $text = preg_replace('/\s+/', ' ', trim(strip_tags($html)));
    if ($text === '') {
        return $fallback;
    }
    if (function_exists('mb_substr')) {
        return mb_substr($text, 0, 160);
    }
    return substr($text, 0, 160);
}

function cms_extract_cta(string $html): array {
    $dom = cms_dom_from_html($html);
    if (!$dom) {
        return ['Get Started', '/multi-quote/'];
    }
    $xpath = new DOMXPath($dom);
    $links = $xpath->query('//a[@href]');
    foreach ($links as $link) {
        if (!$link instanceof DOMElement) {
            continue;
        }
        $href = trim((string)$link->getAttribute('href'));
        if ($href === '' || !cms_url_is_safe($href)) {
            continue;
        }
        if (strpos($href, '/multi-quote') !== false || strpos($href, '/get-health-plan-quotes') !== false || strpos($href, '/find-healthcare-quotes') !== false) {
            $label = trim(preg_replace('/\s+/', ' ', (string)$link->textContent));
            return [$label !== '' ? $label : 'Get Started', $href];
        }
    }
    return ['Get Started', '/multi-quote/'];
}

function cms_dom_from_html(string $html): ?DOMDocument {
    if (trim($html) === '') {
        return null;
    }
    $dom = new DOMDocument('1.0', 'UTF-8');
    $prev = libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($prev);
    return $dom;
}

function cms_dom_inner_html(DOMDocument $dom, DOMNode $node): string {
    $html = '';
    foreach ($node->childNodes as $child) {
        $html .= $dom->saveHTML($child);
    }
    return trim($html);
}
