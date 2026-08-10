<?php
/**
 * CLI: switch every page's steps section to the index card version.
 *
 * Rewrites legacy_section blocks with section_key "steps_tabs" (tabbed steps,
 * inc/sections/steps.php) to "steps_cards" (icon-card steps, inc/sections/steps2.php)
 * so all landing pages use the same "Follow A Few Steps For Affordable Healthcare"
 * section that the home page uses.
 *
 * SAFE BY DEFAULT: dry run unless --commit.
 *
 * Usage (from project root):
 *   php cms/cli/switch-steps-to-cards.php            # dry run
 *   php cms/cli/switch-steps-to-cards.php --commit   # apply
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script is CLI-only.\n");
}

require_once __DIR__ . '/../bootstrap.php';

$options = getopt('', ['commit']);
$commit = array_key_exists('commit', $options);

fwrite(STDOUT, ($commit ? 'SWITCH (commit)' : 'DRY RUN (no changes)') . PHP_EOL);
fwrite(STDOUT, str_repeat('-', 60) . PHP_EOL);

$changed = 0;
$skipped = 0;

foreach (cms_select('SELECT id, slug, body_json FROM cms_pages ORDER BY slug') as $page) {
    $blocks = json_decode((string)$page['body_json'], true);
    if (!is_array($blocks)) {
        $skipped++;
        continue;
    }

    $hits = 0;
    foreach ($blocks as &$block) {
        if (is_array($block)
            && ($block['type'] ?? '') === 'legacy_section'
            && ($block['section_key'] ?? '') === 'steps_tabs') {
            $block['section_key'] = 'steps_cards';
            $hits++;
        }
    }
    unset($block);

    if ($hits === 0) {
        $skipped++;
        continue;
    }

    if ($commit) {
        $bodyJson = json_encode($blocks, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        cms_write(
            "UPDATE cms_pages SET body_json = ?, updated_by = 'steps-switch' WHERE id = ?",
            'si',
            [$bodyJson, (int)$page['id']]
        );
        fwrite(STDOUT, sprintf('%-28s switched %d steps block(s) -> steps_cards', $page['slug'], $hits) . PHP_EOL);
    } else {
        fwrite(STDOUT, sprintf('%-28s would switch %d steps block(s) -> steps_cards', $page['slug'], $hits) . PHP_EOL);
    }
    $changed++;
}

fwrite(STDOUT, str_repeat('-', 60) . PHP_EOL);
fwrite(STDOUT, sprintf('%s: %d, unchanged: %d%s', $commit ? 'Switched' : 'Would switch', $changed, $skipped, PHP_EOL));
if (!$commit) {
    fwrite(STDOUT, 'Re-run with --commit to apply.' . PHP_EOL);
}
exit(0);
