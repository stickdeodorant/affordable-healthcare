<?php
/**
 * CLI: convert real top-level landing pages into fully CMS-managed pages.
 *
 * Each converted page keeps a byte-for-byte snapshot of its current output (so
 * it looks exactly the same) and becomes editable in the CMS. Conversion is two
 * steps:
 *   1. Import + publish the page into cms_pages (1:1 snapshot_html).
 *   2. Replace the physical /{slug}.php file with a CMS bridge stub so
 *      cms/render.php serves it from then on.
 *
 * SAFE BY DEFAULT: this is a dry run unless you pass --commit. On --commit the
 * original file is backed up to cms/backups/legacy-pages/ before it is replaced.
 *
 * Usage (from project root):
 *   php cms/cli/convert-landing-pages.php                     # dry run: show the plan
 *   php cms/cli/convert-landing-pages.php --commit            # import + publish + bridge
 *   php cms/cli/convert-landing-pages.php --commit --no-import   # bridge only (already imported)
 *   php cms/cli/convert-landing-pages.php --commit --no-bridge   # import + publish only
 *   php cms/cli/convert-landing-pages.php --commit --local-only  # local render, skip production fetch
 *   php cms/cli/convert-landing-pages.php --commit --only=aetna,cigna
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script is CLI-only.\n");
}

require_once __DIR__ . '/../bootstrap.php';

$appRoot = CMS_APP_ROOT;
$options = getopt('', ['commit', 'no-import', 'no-bridge', 'local-only', 'only:']);
$commit = array_key_exists('commit', $options);
$doImport = !array_key_exists('no-import', $options);
$doBridge = !array_key_exists('no-bridge', $options);
$localOnly = array_key_exists('local-only', $options);
$onlySlugs = [];
if (isset($options['only']) && $options['only'] !== '') {
    $onlySlugs = array_filter(array_map('trim', explode(',', (string)$options['only'])));
}

$importerPath = __DIR__ . '/import-top-level-pages.php';
$backupDir = dirname(__DIR__) . '/backups/legacy-pages';

$BRIDGE_STUB = "<?php require __DIR__ . '/inc/cms-page-bridge.php';\n";

fwrite(STDOUT, ($commit ? 'CONVERT (commit)' : 'DRY RUN (no changes will be written)') . PHP_EOL);
fwrite(STDOUT, str_repeat('-', 60) . PHP_EOL);

/* ------------------------------------------------------------------ *
 * Step 1: import + publish into the CMS (delegates to the importer).
 * ------------------------------------------------------------------ */
if ($doImport) {
    if ($commit) {
        $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($importerPath) . ' --publish';
        if ($localOnly) {
            $cmd .= ' --local-only';
        }
        fwrite(STDOUT, 'Importing + publishing landing pages...' . PHP_EOL);
        $importOutput = [];
        $importCode = 0;
        exec($cmd . ' 2>&1', $importOutput, $importCode);
        fwrite(STDOUT, '  ' . str_replace("\n", "\n  ", implode("\n", $importOutput)) . PHP_EOL);
        if ($importCode !== 0) {
            fwrite(STDERR, 'Import step failed (exit ' . $importCode . '). Aborting before bridging.' . PHP_EOL);
            exit(1);
        }
        fwrite(STDOUT, str_repeat('-', 60) . PHP_EOL);
    } else {
        fwrite(STDOUT, 'Would run importer: import-top-level-pages.php --publish'
            . ($localOnly ? ' --local-only' : '') . PHP_EOL);
        fwrite(STDOUT, str_repeat('-', 60) . PHP_EOL);
    }
}

/* ------------------------------------------------------------------ *
 * Step 2: replace real landing-page files with CMS bridge stubs.
 * ------------------------------------------------------------------ */
$files = glob($appRoot . DIRECTORY_SEPARATOR . '*.php') ?: [];
sort($files, SORT_NATURAL | SORT_FLAG_CASE);

$converted = 0;
$already = 0;
$skipped = 0;
$report = [];

foreach ($files as $file) {
    $base = basename($file);
    $slug = pathinfo($base, PATHINFO_FILENAME);

    if ($base === 'index.php') {
        continue; // homepage is not routable through CMS slugs
    }
    if ($onlySlugs && !in_array($slug, $onlySlugs, true)) {
        continue;
    }

    $source = (string)file_get_contents($file);

    if (convert_is_bridge_stub($source)) {
        $report[] = [$slug, 'already-cms', 'file already forwards to the CMS'];
        $already++;
        continue;
    }
    if (!convert_is_importable_landing_page($source)) {
        continue; // not one of the shared landing pages (handlers, apis, etc.)
    }

    // Only bridge a page that the CMS can actually serve (published row).
    $row = cms_select_one(
        'SELECT id, status FROM cms_pages WHERE slug = ? LIMIT 1',
        's',
        [$slug]
    );
    $cmsStatus = $row['status'] ?? 'none';
    $willBePublished = $doImport || $cmsStatus === 'published';

    if (!$doBridge) {
        $report[] = [$slug, 'import-only', 'CMS status after import: published'];
        continue;
    }
    if (!$willBePublished) {
        $report[] = [$slug, 'skipped', 'no published CMS page (run without --no-import first)'];
        $skipped++;
        continue;
    }

    if (!$commit) {
        $report[] = [$slug, 'would-convert', 'publish snapshot + replace file with bridge stub'];
        $converted++;
        continue;
    }

    // --- commit: back up original, then write the bridge stub. ---
    if (!is_dir($backupDir) && !mkdir($backupDir, 0775, true) && !is_dir($backupDir)) {
        fwrite(STDERR, 'Could not create backup directory: ' . $backupDir . PHP_EOL);
        exit(1);
    }
    $backupPath = $backupDir . '/' . $slug . '.php.bak';
    if (!is_file($backupPath)) {
        if (file_put_contents($backupPath, $source) === false) {
            $report[] = [$slug, 'error', 'failed to write backup; file left unchanged'];
            $skipped++;
            continue;
        }
    }
    if (file_put_contents($file, $BRIDGE_STUB) === false) {
        $report[] = [$slug, 'error', 'failed to write bridge stub'];
        $skipped++;
        continue;
    }
    $report[] = [$slug, 'converted', 'backup: cms/backups/legacy-pages/' . $slug . '.php.bak'];
    $converted++;
}

fwrite(STDOUT, sprintf('%-26s %-14s %s', 'PAGE', 'RESULT', 'DETAIL') . PHP_EOL);
foreach ($report as [$slug, $state, $detail]) {
    fwrite(STDOUT, sprintf('%-26s %-14s %s', $slug, $state, $detail) . PHP_EOL);
}

fwrite(STDOUT, str_repeat('-', 60) . PHP_EOL);
fwrite(STDOUT, sprintf(
    '%s: %d page(s), already CMS: %d, skipped: %d%s',
    $commit ? 'Converted' : 'Would convert',
    $converted,
    $already,
    $skipped,
    PHP_EOL
));
if (!$commit) {
    fwrite(STDOUT, 'Re-run with --commit to apply. Originals are backed up to cms/backups/legacy-pages/.' . PHP_EOL);
} else {
    fwrite(STDOUT, 'Done. Spot-check a few converted pages in the browser, then edit them in the CMS.' . PHP_EOL);
    fwrite(STDOUT, 'To roll back a page, restore its file from cms/backups/legacy-pages/{slug}.php.bak.' . PHP_EOL);
}
exit(0);

/**
 * A physical file that only forwards to the CMS is already converted.
 */
function convert_is_bridge_stub(string $source): bool {
    $normalized = preg_replace('/\s+/', ' ', trim($source));
    return $normalized === "<?php require __DIR__ . '/inc/cms-page.php';"
        || $normalized === "<?php require __DIR__ . '/inc/cms-page-bridge.php';";
}

/**
 * Whether a root PHP file looks like one of the shared landing pages.
 * Mirrors the guard used by import-top-level-pages.php so we never bridge a
 * functional handler (form endpoints, apis, redirects, etc.).
 */
function convert_is_importable_landing_page(string $source): bool {
    if (stripos($source, "require_once __DIR__ . '/cms/bootstrap.php'") !== false) {
        return false;
    }
    if (stripos($source, "require_once __DIR__ . '/cms/render.php'") !== false) {
        return false;
    }
    if (stripos($source, 'inc/header.php') === false) {
        return false;
    }
    if (stripos($source, 'inc/hero.php') === false
        && stripos($source, 'inc/feature.php') === false
        && stripos($source, 'inc/feature-OG.php') === false) {
        return false;
    }
    if (preg_match('/\b(exit|die)\s*\(/i', $source)) {
        return false;
    }
    return true;
}
