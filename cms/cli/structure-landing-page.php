<?php
/**
 * CLI: convert snapshot landing pages into structured, editable CMS blocks.
 *
 * The landing pages share one layout (hero -> steps -> cta banner -> FAQ), so a
 * single structured template fits them all. Each converted page keeps its own
 * hero/title/meta and reuses the ORIGINAL steps + cta-banner section markup
 * (via legacy_section blocks, so it still looks the same), while the FAQ becomes
 * a fully editable faq_list.
 *
 * SAFE BY DEFAULT: dry run unless --commit. The current body_json is backed up
 * to cms/backups/legacy-pages/{slug}.body.json before it is replaced, so a page
 * can be restored to its exact snapshot at any time.
 *
 * Usage (from project root):
 *   php cms/cli/structure-landing-page.php --slug=aetna              # dry run (one page)
 *   php cms/cli/structure-landing-page.php --slug=aetna --commit     # convert one page
 *   php cms/cli/structure-landing-page.php --slug=aetna --commit --set-hero
 *   php cms/cli/structure-landing-page.php --all --commit --set-hero # convert every snapshot page
 *   php cms/cli/structure-landing-page.php --slug=aetna --restore    # restore from backup
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script is CLI-only.\n");
}

require_once __DIR__ . '/../bootstrap.php';

$options = getopt('', ['slug:', 'all', 'commit', 'set-hero', 'restore']);
$commit = array_key_exists('commit', $options);
$setHero = array_key_exists('set-hero', $options);
$restore = array_key_exists('restore', $options);
$backupDir = dirname(__DIR__) . '/backups/legacy-pages';

// Resolve the target slugs.
$slugs = [];
if (array_key_exists('all', $options)) {
    foreach (cms_select("SELECT slug FROM cms_pages WHERE body_json LIKE '%snapshot_html%' ORDER BY slug") as $r) {
        $slugs[] = $r['slug'];
    }
} elseif (!empty($options['slug'])) {
    $slugs[] = cms_slug((string)$options['slug']);
} else {
    fwrite(STDERR, "Specify --slug=NAME or --all.\n");
    exit(1);
}

fwrite(STDOUT, ($restore ? 'RESTORE' : ($commit ? 'CONVERT (commit)' : 'DRY RUN (no changes)')) . PHP_EOL);
fwrite(STDOUT, str_repeat('-', 60) . PHP_EOL);

$done = 0;
$skipped = 0;

foreach ($slugs as $slug) {
    $page = cms_select_one('SELECT id, body_json FROM cms_pages WHERE slug = ? LIMIT 1', 's', [$slug]);
    if (!$page) {
        fwrite(STDOUT, sprintf('%-28s %s', $slug, 'skip: no CMS page') . PHP_EOL);
        $skipped++;
        continue;
    }
    $backupPath = $backupDir . '/' . $slug . '.body.json';

    /* ---- restore mode ---- */
    if ($restore) {
        if (!is_file($backupPath)) {
            fwrite(STDOUT, sprintf('%-28s %s', $slug, 'skip: no backup found') . PHP_EOL);
            $skipped++;
            continue;
        }
        if ($commit) {
            $original = (string)file_get_contents($backupPath);
            cms_write('UPDATE cms_pages SET body_json = ?, updated_by = ? WHERE slug = ?', 'sss', [$original, 'structure-tool', $slug]);
            fwrite(STDOUT, sprintf('%-28s %s', $slug, 'restored from backup') . PHP_EOL);
            $done++;
        } else {
            fwrite(STDOUT, sprintf('%-28s %s', $slug, 'would restore from backup') . PHP_EOL);
            $done++;
        }
        continue;
    }

    /* ---- convert mode ---- */
    // Source snapshot: current body, else the original backup.
    $snapshot = cms_snapshot_from_body((string)$page['body_json']);
    if ($snapshot === '' && is_file($backupPath)) {
        $snapshot = cms_snapshot_from_body((string)file_get_contents($backupPath));
    }
    if ($snapshot === '') {
        fwrite(STDOUT, sprintf('%-28s %s', $slug, 'skip: no snapshot to split') . PHP_EOL);
        $skipped++;
        continue;
    }

    $blocks = cms_blocks_from_snapshot($snapshot);
    if (!$blocks) {
        fwrite(STDOUT, sprintf('%-28s %s', $slug, 'skip: could not parse <main>') . PHP_EOL);
        $skipped++;
        continue;
    }
    $summary = cms_summarize_blocks($blocks);

    if (!$commit) {
        fwrite(STDOUT, sprintf('%-28s %s', $slug, 'would convert -> ' . $summary . ($setHero ? ' (+clean hero)' : '')) . PHP_EOL);
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

    $bodyJson = json_encode($blocks, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    if ($setHero) {
        cms_write(
            "UPDATE cms_pages SET body_json = ?, template = 'default',
                hero_headline = ?, hero_subtitle = ?, cta_text = 'Find Plans', cta_href = '/multi-quote/',
                updated_by = 'structure-tool' WHERE slug = ?",
            'ssss',
            [$bodyJson, 'Find Affordable Healthcare!', 'You may qualify for a plan with no monthly cost', $slug]
        );
    } else {
        cms_write(
            "UPDATE cms_pages SET body_json = ?, template = 'default', updated_by = 'structure-tool' WHERE slug = ?",
            'ss',
            [$bodyJson, $slug]
        );
    }
    fwrite(STDOUT, sprintf('%-28s %s', $slug, 'converted -> ' . $summary) . PHP_EOL);
    $done++;
}

fwrite(STDOUT, str_repeat('-', 60) . PHP_EOL);
fwrite(STDOUT, sprintf('%s: %d, skipped: %d%s', $restore ? 'Restored' : ($commit ? 'Converted' : 'Would convert'), $done, $skipped, PHP_EOL));
if (!$commit) {
    fwrite(STDOUT, 'Re-run with --commit to apply. Originals are backed up to cms/backups/legacy-pages/{slug}.body.json.' . PHP_EOL);
}
exit(0);

/** Extract the html of the first snapshot_html block in a body_json string. */
function cms_snapshot_from_body(string $bodyJson): string {
    $blocks = json_decode($bodyJson, true);
    if (!is_array($blocks)) {
        return '';
    }
    foreach ($blocks as $b) {
        if (is_array($b) && ($b['type'] ?? '') === 'snapshot_html' && trim((string)($b['html'] ?? '')) !== '') {
            return (string)$b['html'];
        }
    }
    return '';
}

/** Build ordered CMS blocks from a captured full-page snapshot. */
function cms_blocks_from_snapshot(string $snapshot): array {
    if (!preg_match('#<main\b[^>]*>(.*)</main>#is', $snapshot, $m)) {
        return [];
    }
    $chunks = cms_split_top_level_html($m[1]);
    $blocks = [];
    foreach ($chunks as $chunk) {
        if (trim($chunk) === '') {
            continue;
        }
        $blocks[] = cms_classify_chunk($chunk);
    }
    return $blocks;
}

/** Map one top-level section to its best block representation. */
function cms_classify_chunk(string $chunk): array {
    if (stripos($chunk, 'id="steps"') !== false || stripos($chunk, 'id="steps-quote"') !== false) {
        return ['type' => 'legacy_section', 'section_key' => 'steps_tabs'];
    }
    if (stripos($chunk, 'waiting-room.svg') !== false) {
        return ['type' => 'legacy_section', 'section_key' => 'cta_banner'];
    }
    if (stripos($chunk, 'id="faq"') !== false || stripos($chunk, 'id="accordion"') !== false) {
        return ['type' => 'faq_list', 'heading' => 'Healthcare FAQ', 'items' => cms_default_faq_items()];
    }
    return ['type' => 'snapshot_html', 'html' => trim($chunk)];
}

/** Short human summary of a block list for the CLI report. */
function cms_summarize_blocks(array $blocks): string {
    $parts = [];
    foreach ($blocks as $b) {
        $t = $b['type'] ?? '?';
        if ($t === 'legacy_section') {
            $parts[] = (string)($b['section_key'] ?? 'section');
        } elseif ($t === 'snapshot_html') {
            $parts[] = 'kept-section';
        } elseif ($t === 'faq_list') {
            $parts[] = 'editable-FAQ';
        } else {
            $parts[] = $t;
        }
    }
    return implode(' + ', $parts);
}

/**
 * Split an HTML fragment into its top-level element strings, verbatim.
 * Preserves raw markup (including SVG attribute casing) by never re-serializing.
 * Only reliably-paired container tags move the nesting depth; inline and
 * optional-close tags (p, li, a, span, img, path, ...) are treated as neutral,
 * so legacy markup that omits closing </p>/</li> does not desync the scan.
 */
function cms_split_top_level_html(string $html): array {
    $count = ['section','div','article','aside','header','footer','nav','main','form',
        'ul','ol','dl','table','thead','tbody','tfoot','figure','picture',
        'svg','g','defs','symbol','clippath','lineargradient','radialgradient','mask','pattern'];
    $chunks = [];
    $len = strlen($html);
    $i = 0;
    $depth = 0;
    $start = -1;

    while ($i < $len) {
        $lt = strpos($html, '<', $i);
        if ($lt === false) {
            break;
        }
        if (substr($html, $lt, 4) === '<!--') {
            $end = strpos($html, '-->', $lt + 4);
            $i = ($end === false) ? $len : $end + 3;
            continue;
        }
        if (isset($html[$lt + 1]) && ($html[$lt + 1] === '!' || $html[$lt + 1] === '?')) {
            $end = strpos($html, '>', $lt + 1);
            $i = ($end === false) ? $len : $end + 1;
            continue;
        }
        $gt = cms_find_tag_end($html, $lt);
        if ($gt === false) {
            break;
        }
        $tagText = substr($html, $lt, $gt - $lt + 1);
        if (!preg_match('#^<\s*(/?)\s*([a-zA-Z][a-zA-Z0-9:-]*)#', $tagText, $mm)) {
            $i = $gt + 1;
            continue;
        }
        $isClose = ($mm[1] === '/');
        $name = strtolower($mm[2]);
        $selfClose = (substr(rtrim($tagText, " \t\r\n"), -2, 1) === '/');

        // Treat <script>/<style> bodies as opaque so stray "<" inside can't confuse the scan.
        if (!$isClose && !$selfClose && ($name === 'script' || $name === 'style')) {
            $closePos = stripos($html, '</' . $name, $gt + 1);
            if ($closePos === false) {
                $end = $len;
            } else {
                $endGt = strpos($html, '>', $closePos);
                $end = ($endGt === false) ? $len : $endGt + 1;
            }
            if ($depth === 0) {
                $chunks[] = substr($html, $lt, $end - $lt);
                $start = -1;
            }
            $i = $end;
            continue;
        }

        if (!in_array($name, $count, true) || $selfClose) {
            $i = $gt + 1;
            continue;
        }

        if ($isClose) {
            if ($depth > 0) {
                $depth--;
                if ($depth === 0 && $start !== -1) {
                    $chunks[] = substr($html, $start, $gt - $start + 1);
                    $start = -1;
                }
            }
            $i = $gt + 1;
            continue;
        }

        if ($depth === 0) {
            $start = $lt;
        }
        $depth++;
        $i = $gt + 1;
    }

    return $chunks;
}

/** Find the closing ">" of a tag starting at $lt, respecting quoted attributes. */
function cms_find_tag_end(string $html, int $lt) {
    $len = strlen($html);
    $i = $lt + 1;
    $quote = '';
    while ($i < $len) {
        $c = $html[$i];
        if ($quote !== '') {
            if ($c === $quote) {
                $quote = '';
            }
        } elseif ($c === '"' || $c === "'") {
            $quote = $c;
        } elseif ($c === '>') {
            return $i;
        }
        $i++;
    }
    return false;
}

/** Standard landing-page FAQ, made editable. */
function cms_default_faq_items(): array {
    return [
        [
            'q' => 'When can I switch to a more affordable plan?',
            'a' => '<p>You can switch to a new individual healthcare plan during open enrollment. Certain qualifying events &mdash; such as having a baby, losing your current coverage, moving counties, or getting married &mdash; can make you eligible for a 60-day special enrollment period. Otherwise, you can switch your plan starting November 1st.</p>',
        ],
        [
            'q' => 'HMOs (Health Maintenance Organizations)',
            'a' => '<p>Members are usually restricted to doctors, providers, or hospitals on the plan&rsquo;s list, and generally do not cover out-of-network care except in emergencies. The plan may require you to live or work in a specific service area to be eligible.</p>',
        ],
        [
            'q' => 'PPOs (Preferred Provider Organizations)',
            'a' => '<p>Coverage usually contracts with medical providers to create a network of participating providers. You typically pay less when using in-network doctors and hospitals, and more when you seek care outside the network.</p>',
        ],
        [
            'q' => 'POS (Point of Service) Plans',
            'a' => '<p>You pay less when you use doctors, hospitals, and other health care providers that belong to the plan&rsquo;s network.</p>',
        ],
    ];
}
