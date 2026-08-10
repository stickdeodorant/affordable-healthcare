<?php
/**
 * CLI: reset every landing page to the home page's section layout.
 *
 * Rebuilds each converted landing page's body_json to the canonical home-page
 * <main> stack (same partials index.php includes), so every page starts out
 * identical to the home page and can then be customized per page:
 *
 *   cta_banner -> steps_cards -> plane_banner -> searching_intro
 *   -> open_enrollment -> consumer_caution -> faq_accordion
 *
 * Per-page hero/title/meta are left untouched. SAFE BY DEFAULT: dry run unless
 * --commit. Current body_json is backed up to cms/backups/home-layout/{slug}.body.json
 * (restore with --restore --commit).
 *
 * Usage (from project root):
 *   php cms/cli/apply-home-layout.php            # dry run
 *   php cms/cli/apply-home-layout.php --commit   # apply to all landing pages
 *   php cms/cli/apply-home-layout.php --restore --commit
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script is CLI-only.\n");
}

require_once __DIR__ . '/../bootstrap.php';

$options = getopt('', ['commit', 'restore']);
$commit = array_key_exists('commit', $options);
$restore = array_key_exists('restore', $options);
$backupDir = dirname(__DIR__) . '/backups/home-layout';

// Canonical home-page section stack (order matters).
function cms_home_layout_blocks(): array {
    return [
        ['type' => 'legacy_section', 'section_key' => 'cta_banner'],
        ['type' => 'legacy_section', 'section_key' => 'steps_cards'],
        ['type' => 'legacy_section', 'section_key' => 'plane_banner'],
        ['type' => 'legacy_section', 'section_key' => 'searching_intro'],
        ['type' => 'legacy_section', 'section_key' => 'open_enrollment'],
        ['type' => 'legacy_section', 'section_key' => 'consumer_caution'],
        ['type' => 'legacy_section', 'section_key' => 'faq_accordion'],
    ];
}

// Target: converted landing pages (they carry a steps legacy_section block).
$rows = cms_select(
    "SELECT id, slug, body_json FROM cms_pages
     WHERE (body_json LIKE '%\"section_key\":\"steps_cards\"%'
         OR body_json LIKE '%\"section_key\":\"steps_tabs\"%')
       AND slug NOT LIKE '%-structured-preview'
     ORDER BY slug"
);

fwrite(STDOUT, ($restore ? 'RESTORE' : ($commit ? 'APPLY (commit)' : 'DRY RUN (no changes)')) . PHP_EOL);
fwrite(STDOUT, str_repeat('-', 60) . PHP_EOL);

$done = 0;
$skipped = 0;
$bodyJson = json_encode(cms_home_layout_blocks(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

foreach ($rows as $page) {
    $slug = (string)$page['slug'];
    $backupPath = $backupDir . '/' . $slug . '.body.json';

    if ($restore) {
        if (!is_file($backupPath)) {
            fwrite(STDOUT, sprintf('%-28s %s', $slug, 'skip: no backup found') . PHP_EOL);
            $skipped++;
            continue;
        }
        if ($commit) {
            $original = (string)file_get_contents($backupPath);
            cms_write("UPDATE cms_pages SET body_json = ?, updated_by = 'home-layout' WHERE id = ?", 'si', [$original, (int)$page['id']]);
            fwrite(STDOUT, sprintf('%-28s %s', $slug, 'restored from backup') . PHP_EOL);
        } else {
            fwrite(STDOUT, sprintf('%-28s %s', $slug, 'would restore from backup') . PHP_EOL);
        }
        $done++;
        continue;
    }

    if (!$commit) {
        fwrite(STDOUT, sprintf('%-28s %s', $slug, 'would apply home layout (7 sections)') . PHP_EOL);
        $done++;
        continue;
    }

    if (!is_dir($backupDir) && !mkdir($backupDir, 0775, true) && !is_dir($backupDir)) {
        fwrite(STDERR, 'Could not create backup directory: ' . $backupDir . PHP_EOL);
        exit(1);
    }
    if (!is_file($backupPath)) {
        file_put_contents($backupPath, (string)$page['body_json']);
    }

    cms_write("UPDATE cms_pages SET body_json = ?, template = 'default', updated_by = 'home-layout' WHERE id = ?", 'si', [$bodyJson, (int)$page['id']]);
    fwrite(STDOUT, sprintf('%-28s %s', $slug, 'applied home layout') . PHP_EOL);
    $done++;
}

fwrite(STDOUT, str_repeat('-', 60) . PHP_EOL);
fwrite(STDOUT, sprintf('%s: %d, skipped: %d%s', $restore ? 'Restored' : ($commit ? 'Applied' : 'Would apply'), $done, $skipped, PHP_EOL));
if (!$commit) {
    fwrite(STDOUT, 'Re-run with --commit to apply. Originals are backed up to cms/backups/home-layout/{slug}.body.json.' . PHP_EOL);
}
exit(0);
