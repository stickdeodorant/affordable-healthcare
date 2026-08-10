<?php
/**
 * Manage approved snippet presets used by non-technical editors.
 */

require_once __DIR__ . '/../cms/bootstrap.php';
require_once __DIR__ . '/_layout.php';

cms_require_role('admin');

$base = CMS_ADMIN_PATH;
$snippets = cms_snippets_load();

function admin_snippet_key(string $raw): string {
    $key = preg_replace('/[^a-z0-9_]+/i', '_', $raw);
    return trim((string)$key, '_');
}

function admin_clean_snippet_blocks(array $raw): array {
    $out = [];
    foreach ($raw as $b) {
        if (!is_array($b) || empty($b['type'])) {
            continue;
        }
        if ($b['type'] === 'rich_text') {
            $html = cms_sanitize_html((string)($b['html'] ?? ''));
            if ($html !== '') {
                $out[] = ['type' => 'rich_text', 'html' => $html];
            }
        } elseif ($b['type'] === 'cta_banner') {
            $href = trim((string)($b['href'] ?? ''));
            if (!cms_url_is_safe($href)) {
                $href = '/multi-quote/';
            }
            $out[] = [
                'type' => 'cta_banner',
                'text' => trim((string)($b['text'] ?? '')),
                'href' => $href,
                'cta_text' => trim((string)($b['cta_text'] ?? '')),
            ];
        } elseif ($b['type'] === 'faq_list') {
            $items = [];
            foreach (($b['items'] ?? []) as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $q = trim((string)($item['q'] ?? ''));
                $a = cms_sanitize_html((string)($item['a'] ?? ''));
                if ($q !== '' && $a !== '') {
                    $items[] = ['q' => $q, 'a' => $a];
                }
            }
            if ($items) {
                $out[] = [
                    'type' => 'faq_list',
                    'heading' => trim((string)($b['heading'] ?? '')),
                    'items' => $items,
                ];
            }
        } elseif ($b['type'] === 'legacy_section') {
            $sectionKey = trim((string)($b['section_key'] ?? ''));
            if ($sectionKey !== '' && cms_legacy_section_exists($sectionKey)) {
                $out[] = [
                    'type' => 'legacy_section',
                    'section_key' => $sectionKey,
                ];
            }
        }
    }
    return $out;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cms_csrf_require();

    $keys = (array)($_POST['snippet_key'] ?? []);
    $labels = (array)($_POST['snippet_label'] ?? []);
    $orders = (array)($_POST['snippet_order'] ?? []);
    $blockJsonList = (array)($_POST['snippet_blocks_json'] ?? []);
    $deleteFlags = (array)($_POST['snippet_delete'] ?? []);

    $rows = [];
    foreach ($keys as $i => $rawKey) {
        if (!isset($blockJsonList[$i])) {
            continue;
        }
        $rows[] = [
            'key' => admin_snippet_key((string)$rawKey),
            'label' => trim((string)($labels[$i] ?? '')),
            'order' => (int)($orders[$i] ?? 9999),
            'blocks_json' => (string)$blockJsonList[$i],
            'delete' => !empty($deleteFlags[$i]),
        ];
    }

    $newKey = admin_snippet_key((string)($_POST['new_snippet_key'] ?? ''));
    $newLabel = trim((string)($_POST['new_snippet_label'] ?? ''));
    $newBlocksJson = trim((string)($_POST['new_snippet_blocks_json'] ?? ''));
    if ($newKey !== '' && $newBlocksJson !== '') {
        $rows[] = [
            'key' => $newKey,
            'label' => $newLabel,
            'order' => 100000,
            'blocks_json' => $newBlocksJson,
            'delete' => false,
        ];
    }

    usort($rows, function (array $a, array $b): int {
        return ($a['order'] <=> $b['order']);
    });

    $updated = [];
    $seen = [];
    $issues = [];
    foreach ($rows as $row) {
        if ($row['delete']) {
            continue;
        }
        $key = (string)$row['key'];
        if ($key === '') {
            $issues[] = 'Skipped a snippet row with an empty key.';
            continue;
        }
        if (isset($seen[$key])) {
            $issues[] = 'Duplicate key "' . $key . '" was skipped.';
            continue;
        }

        $decoded = cms_json_decode($row['blocks_json']);
        if (!is_array($decoded)) {
            $issues[] = 'Snippet "' . $key . '" has invalid blocks JSON and was skipped.';
            continue;
        }
        $cleanBlocks = admin_clean_snippet_blocks($decoded);
        if (!$cleanBlocks) {
            $issues[] = 'Snippet "' . $key . '" has no valid blocks after sanitizing and was skipped.';
            continue;
        }

        $label = (string)$row['label'];
        if ($label === '') {
            $label = ucwords(str_replace('_', ' ', $key));
        }
        $updated[$key] = [
            'label' => $label,
            'blocks' => $cleanBlocks,
        ];
        $seen[$key] = true;
    }

    if (!$updated) {
        $updated = cms_snippets_defaults();
        $issues[] = 'No valid snippets remained, so defaults were restored.';
    }

    if (cms_snippets_save($updated)) {
        cms_audit('snippet_save', 'cms_snippets', 'approved', ['editor' => cms_current_user()['email']]);
        $message = 'Approved snippets updated.';
        if ($issues) {
            $message .= ' ' . implode(' ', $issues);
        }
        admin_flash_set('success', $message);
    } else {
        admin_flash_set('error', 'Could not save snippets. Check file permissions for cms/data/.');
    }

    header('Location: ' . $base . '/snippets.php');
    exit;
}

$snippets = cms_snippets_load();
$rows = [];
$position = 1;
foreach ($snippets as $key => $snippet) {
    $rows[] = [
        'key' => (string)$key,
        'label' => (string)($snippet['label'] ?? ''),
        'order' => $position,
        'blocks_json' => json_encode(
            array_values((array)($snippet['blocks'] ?? [])),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ),
    ];
    $position++;
}

$defaultTemplates = [
    [
        'key' => 'compliance_disclaimer',
        'label' => 'Compliance disclaimer',
        'blocks' => [['type' => 'rich_text', 'html' => '<p><strong>Important:</strong> Add compliance language here.</p>']],
    ],
    [
        'key' => 'cta_bundle',
        'label' => 'CTA banner block',
        'blocks' => [['type' => 'cta_banner', 'text' => 'Find Affordable Healthcare Options', 'href' => '/multi-quote/', 'cta_text' => 'Get Quotes']],
    ],
    [
        'key' => 'faq_bundle',
        'label' => 'FAQ starter bundle',
        'blocks' => [['type' => 'faq_list', 'heading' => 'Common Questions', 'items' => [['q' => 'Question', 'a' => '<p>Answer</p>']]]],
    ],
    [
        'key' => 'legacy_home_sections',
        'label' => 'Legacy home sections stack',
        'blocks' => [
            ['type' => 'legacy_section', 'section_key' => 'cta_banner'],
            ['type' => 'legacy_section', 'section_key' => 'steps_cards'],
            ['type' => 'legacy_section', 'section_key' => 'plane_banner'],
            ['type' => 'legacy_section', 'section_key' => 'testimonials_slider'],
            ['type' => 'legacy_section', 'section_key' => 'faq_accordion'],
        ],
    ],
];

admin_header('Approved snippets');
?>
<h1>Approved snippets</h1>
<p class="muted">These snippets appear in the page editor for one-click insertion by marketers. You can add, delete, and reorder snippets here.</p>

<form method="post" action="<?= cms_e($base) ?>/snippets.php">
    <?= cms_csrf_field() ?>

    <?php if (!$rows): ?>
        <div class="card muted">No snippets yet. Add your first snippet below.</div>
    <?php endif; ?>

    <?php foreach ($rows as $idx => $row): ?>
        <div class="card">
            <h2 style="font-size:1.05rem;margin:0 0 .5rem;">Snippet <?= (int)($idx + 1) ?></h2>
            <div class="row">
                <div class="col">
                    <label>Snippet key</label>
                    <input type="text" name="snippet_key[]" value="<?= cms_e((string)$row['key']) ?>" pattern="[A-Za-z0-9_]+" required>
                    <div class="hint">Use letters, numbers, and underscores only.</div>
                </div>
                <div class="col">
                    <label>Button label in editor</label>
                    <input type="text" name="snippet_label[]" value="<?= cms_e((string)$row['label']) ?>" required>
                </div>
                <div class="col">
                    <label>Order (lower appears first)</label>
                    <input type="number" name="snippet_order[]" value="<?= (int)$row['order'] ?>" min="1" step="1">
                    <label style="margin-top:.45rem;display:block;">
                        <input type="checkbox" name="snippet_delete[<?= (int)$idx ?>]" value="1"> Delete this snippet
                    </label>
                </div>
            </div>
            <label style="margin-top:.6rem;">Blocks JSON</label>
            <textarea name="snippet_blocks_json[]" style="min-height:190px;font-family:ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;"><?= cms_e((string)$row['blocks_json']) ?></textarea>
            <div class="hint">Allowed block types: rich_text, cta_banner, faq_list. Invalid blocks are removed during save.</div>
        </div>
    <?php endforeach; ?>

    <div class="card">
        <h2 style="font-size:1.05rem;margin:0 0 .5rem;">Add new snippet</h2>
        <div class="row">
            <div class="col">
                <label for="new_snippet_key">Snippet key</label>
                <input type="text" id="new_snippet_key" name="new_snippet_key" placeholder="example_cta" pattern="[A-Za-z0-9_]+">
            </div>
            <div class="col">
                <label for="new_snippet_label">Button label</label>
                <input type="text" id="new_snippet_label" name="new_snippet_label" placeholder="Example CTA">
            </div>
        </div>
        <label for="new_snippet_blocks_json" style="margin-top:.6rem;">Blocks JSON</label>
        <textarea id="new_snippet_blocks_json" name="new_snippet_blocks_json" style="min-height:170px;font-family:ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;"></textarea>
        <div class="hint">Tip: copy one of the template JSON examples below and customize it.</div>
        <details style="margin-top:.45rem;">
            <summary>Template JSON examples</summary>
            <?php foreach ($defaultTemplates as $tpl): ?>
                <div style="margin-top:.45rem;">
                    <strong><?= cms_e($tpl['label']) ?></strong>
                    <pre style="white-space:pre-wrap;background:#f7f7f7;padding:.55rem;border:1px solid #ddd;border-radius:6px;"><?= cms_e(json_encode($tpl['blocks'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?></pre>
                </div>
            <?php endforeach; ?>
        </details>
    </div>

    <div class="actions">
        <button type="submit" class="btn btn-primary">Save snippets</button>
        <a class="btn btn-ghost" href="<?= cms_e($base) ?>/">Back to pages</a>
    </div>
</form>
<?php
admin_footer();
