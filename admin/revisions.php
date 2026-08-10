<?php
/**
 * Revision history + content rollback for a single page.
 * Restores content fields from a snapshot (keeps the current slug to avoid
 * routing surprises) and records the rollback as a new revision.
 */

require_once __DIR__ . '/../cms/bootstrap.php';
require_once __DIR__ . '/_layout.php';

cms_require_login();

$base = CMS_ADMIN_PATH;
$pageId = (int)($_GET['page_id'] ?? $_POST['page_id'] ?? 0);

function admin_revision_body_summary(string $bodyJson): string {
    $blocks = cms_json_decode($bodyJson);
    if (!is_array($blocks) || !$blocks) {
        return '0 blocks';
    }
    $counts = [];
    foreach ($blocks as $block) {
        if (!is_array($block)) {
            continue;
        }
        $type = trim((string)($block['type'] ?? 'unknown'));
        if ($type === '') {
            $type = 'unknown';
        }
        $counts[$type] = ($counts[$type] ?? 0) + 1;
    }
    ksort($counts);
    $parts = [];
    foreach ($counts as $type => $count) {
        $parts[] = $type . ': ' . $count;
    }
    return count($blocks) . ' blocks (' . implode(', ', $parts) . ')';
}

function admin_revision_field_preview(string $field, string $value): string {
    if ($field === 'body_json') {
        return admin_revision_body_summary($value);
    }
    $trimmed = trim($value);
    if ($trimmed === '') {
        return '(empty)';
    }
    if (mb_strlen($trimmed) > 180) {
        return mb_substr($trimmed, 0, 177) . '...';
    }
    return $trimmed;
}

function admin_revision_diff_rows(array $before, array $after, array $fields): array {
    $rows = [];
    foreach ($fields as $field) {
        $oldValue = (string)($before[$field] ?? '');
        $newValue = (string)($after[$field] ?? '');
        if ($oldValue === $newValue) {
            continue;
        }
        $rows[] = [
            'field' => $field,
            'before' => admin_revision_field_preview($field, $oldValue),
            'after' => admin_revision_field_preview($field, $newValue),
        ];
    }
    return $rows;
}

function admin_revision_block_title(array $block): string {
    $type = strtolower(trim((string)($block['type'] ?? 'unknown')));
    if ($type === '') {
        $type = 'unknown';
    }
    return str_replace('_', ' ', $type);
}

function admin_revision_block_preview(array $block): string {
    $type = strtolower(trim((string)($block['type'] ?? '')));
    if ($type === 'rich_text' || $type === 'legacy_html' || $type === 'snapshot_html') {
        $text = trim(strip_tags((string)($block['html'] ?? '')));
        if ($text === '') {
            return '(empty)';
        }
        return mb_strlen($text) > 220 ? (mb_substr($text, 0, 217) . '...') : $text;
    }
    if ($type === 'cta_banner') {
        $text = trim((string)($block['text'] ?? ''));
        $href = trim((string)($block['href'] ?? ''));
        $cta = trim((string)($block['cta_text'] ?? ''));
        return 'Heading: ' . ($text !== '' ? $text : '(empty)') . "\n"
            . 'Link: ' . ($href !== '' ? $href : '(empty)') . "\n"
            . 'Button: ' . ($cta !== '' ? $cta : '(empty)');
    }
    if ($type === 'image') {
        $src = trim((string)($block['src'] ?? ''));
        $alt = trim((string)($block['alt'] ?? ''));
        return 'Image: ' . ($src !== '' ? $src : '(empty)') . "\n"
            . 'Alt: ' . ($alt !== '' ? $alt : '(empty)');
    }
    if ($type === 'faq_list') {
        $heading = trim((string)($block['heading'] ?? ''));
        $items = is_array($block['items'] ?? null) ? count($block['items']) : 0;
        return 'Heading: ' . ($heading !== '' ? $heading : '(empty)') . "\n"
            . 'FAQ items: ' . $items;
    }
    $flat = trim(json_encode($block, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '');
    if ($flat === '') {
        return '(empty)';
    }
    return mb_strlen($flat) > 220 ? (mb_substr($flat, 0, 217) . '...') : $flat;
}

function admin_revision_body_compare(string $beforeJson, string $afterJson): array {
    $before = cms_json_decode($beforeJson);
    $after = cms_json_decode($afterJson);
    $before = is_array($before) ? array_values($before) : [];
    $after = is_array($after) ? array_values($after) : [];
    $max = max(count($before), count($after));
    $rows = [];
    for ($i = 0; $i < $max; $i++) {
        $left = isset($before[$i]) && is_array($before[$i]) ? $before[$i] : null;
        $right = isset($after[$i]) && is_array($after[$i]) ? $after[$i] : null;
        $leftJson = $left ? json_encode($left, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
        $rightJson = $right ? json_encode($right, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
        $rows[] = [
            'index' => $i + 1,
            'left' => $left,
            'right' => $right,
            'changed' => $leftJson !== $rightJson,
        ];
    }
    return $rows;
}

$page = $pageId > 0 ? cms_select_one('SELECT * FROM cms_pages WHERE id = ? LIMIT 1', 'i', [$pageId]) : null;
if (!$page) {
    admin_flash_set('error', 'Page not found.');
    header('Location: ' . $base . '/');
    exit;
}

// Fields restored on rollback (slug intentionally excluded).
$restorable = [
    'title', 'meta_description', 'canonical', 'og_image', 'theme', 'status',
    'hero_headline', 'hero_subtitle', 'cta_text', 'cta_href', 'body_json',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cms_csrf_require();
    if ((string)($_POST['action'] ?? '') === 'rollback') {
        if (!cms_user_can('reviewer')) {
            admin_flash_set('error', 'You do not have permission to restore revisions.');
            header('Location: ' . $base . '/revisions.php?page_id=' . $pageId);
            exit;
        }
        $rollbackNote = mb_substr(trim((string)($_POST['rollback_note'] ?? '')), 0, 500);
        if ($rollbackNote === '' || mb_strlen($rollbackNote) < 10) {
            admin_flash_set('error', 'Add a rollback note (at least 10 characters) so the decision is documented.');
            header('Location: ' . $base . '/revisions.php?page_id=' . $pageId);
            exit;
        }
        $revId = (int)($_POST['revision_id'] ?? 0);
        $rev = cms_select_one(
            'SELECT snapshot_json FROM cms_page_revisions WHERE id = ? AND page_id = ? LIMIT 1',
            'ii',
            [$revId, $pageId]
        );
        $snap = $rev ? cms_json_decode($rev['snapshot_json']) : [];
        if (!$snap) {
            admin_flash_set('error', 'Revision could not be loaded.');
        } else {
            $user = cms_current_user()['email'];
            $status = in_array((string)($snap['status'] ?? 'draft'), $GLOBALS['CMS_STATUSES'], true)
                ? (string)$snap['status'] : 'draft';
            $publishedAt = null;
            if ($status === 'published') {
                $publishedAt = !empty($page['published_at']) ? $page['published_at'] : date('Y-m-d H:i:s');
            }
            cms_write(
                'UPDATE cms_pages SET
                    title = ?, meta_description = ?, canonical = ?, og_image = ?, theme = ?, status = ?,
                    hero_headline = ?, hero_subtitle = ?, cta_text = ?, cta_href = ?, body_json = ?,
                    updated_by = ?, published_at = ?
                 WHERE id = ?',
                'sssssssssssssi',
                [
                    (string)($snap['title'] ?? ''), (string)($snap['meta_description'] ?? ''),
                    (string)($snap['canonical'] ?? ''), (string)($snap['og_image'] ?? ''),
                    (string)($snap['theme'] ?? 'default'), $status,
                    (string)($snap['hero_headline'] ?? ''), (string)($snap['hero_subtitle'] ?? ''),
                    (string)($snap['cta_text'] ?? ''), (string)($snap['cta_href'] ?? ''),
                    (string)($snap['body_json'] ?? '[]'),
                    $user, $publishedAt, $pageId,
                ]
            );
            // Snapshot the post-rollback state so the timeline stays complete.
            $fresh = cms_select_one('SELECT * FROM cms_pages WHERE id = ? LIMIT 1', 'i', [$pageId]);
            if ($fresh) {
                cms_write(
                    'INSERT INTO cms_page_revisions (page_id, snapshot_json, editor, note) VALUES (?, ?, ?, ?)',
                    'isss',
                    [$pageId, json_encode($fresh, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $user, 'rollback from revision #' . $revId . ' - ' . $rollbackNote]
                );
            }
            cms_audit('page_rollback', 'cms_page', $pageId, ['revision_id' => $revId, 'rollback_note' => $rollbackNote]);
            admin_flash_set('success', 'Content restored from revision #' . $revId . '.');
        }
    }
    header('Location: ' . $base . '/revisions.php?page_id=' . $pageId);
    exit;
}

$revisions = cms_select(
    'SELECT id, editor, note, created_at, snapshot_json FROM cms_page_revisions WHERE page_id = ? ORDER BY created_at DESC, id DESC LIMIT 200',
    'i',
    [$pageId]
);

$compareRevisionId = (int)($_GET['compare'] ?? 0);
$compareAgainst = (string)($_GET['against'] ?? 'current');
if (!in_array($compareAgainst, ['current', 'previous'], true)) {
    $compareAgainst = 'current';
}

$compareRevision = null;
$compareBaseline = null;
$compareRows = [];
$bodyCompareRows = [];
$compareFieldChangeCount = 0;
$compareBodyChangeCount = 0;
if ($compareRevisionId > 0) {
    foreach ($revisions as $idx => $rev) {
        if ((int)$rev['id'] !== $compareRevisionId) {
            continue;
        }
        $compareRevision = $rev;
        $snapshot = cms_json_decode((string)$rev['snapshot_json']);
        $selected = is_array($snapshot) ? $snapshot : [];

        if ($compareAgainst === 'previous') {
            $older = $revisions[$idx + 1] ?? null;
            if ($older) {
                $olderSnapshot = cms_json_decode((string)$older['snapshot_json']);
                $compareBaseline = [
                    'label' => 'previous revision #' . (int)$older['id'],
                    'snapshot' => is_array($olderSnapshot) ? $olderSnapshot : [],
                ];
            }
        }

        if (!$compareBaseline) {
            $compareBaseline = [
                'label' => 'current page state',
                'snapshot' => $page,
            ];
            $compareAgainst = 'current';
        }

        $compareRows = admin_revision_diff_rows(
            (array)($compareBaseline['snapshot'] ?? []),
            $selected,
            array_merge(['slug'], $restorable)
        );
        $compareFieldChangeCount = count($compareRows);

        $beforeBody = (string)($compareBaseline['snapshot']['body_json'] ?? '[]');
        $afterBody = (string)($selected['body_json'] ?? '[]');
        if ($beforeBody !== $afterBody) {
            $bodyCompareRows = admin_revision_body_compare($beforeBody, $afterBody);
            foreach ($bodyCompareRows as $row) {
                if (!empty($row['changed'])) {
                    $compareBodyChangeCount++;
                }
            }
        }
        break;
    }
}

admin_header('Revisions');
?>
<h1>Revisions &middot; <?= cms_e($page['title'] !== '' ? $page['title'] : '/' . $page['slug']) ?></h1>
<p>
    <a class="btn btn-ghost btn-sm" href="<?= cms_e($base) ?>/edit.php?id=<?= (int)$pageId ?>">&larr; Back to editor</a>
    <span class="muted">Restoring keeps the current slug (<code>/<?= cms_e($page['slug']) ?></code>).</span>
</p>

<?php if (!$revisions): ?>
    <div class="card muted">No revisions yet. Saving the page creates one.</div>
<?php else: ?>
<?php if ($compareRevision): ?>
    <div class="card" style="margin-bottom:1rem;">
        <h2 style="font-size:1.02rem;margin:0 0 .5rem;">Compare revision #<?= (int)$compareRevision['id'] ?> against <?= cms_e((string)$compareBaseline['label']) ?></h2>
        <div class="actions" style="margin-bottom:.55rem;">
            <span class="badge badge-review">Field changes: <?= (int)$compareFieldChangeCount ?></span>
            <span class="badge badge-published">Changed body blocks: <?= (int)$compareBodyChangeCount ?></span>
        </div>
        <?php if (!$compareRows): ?>
            <div class="muted">No differences found across page fields.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Baseline value</th>
                        <th>Revision value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($compareRows as $row): ?>
                        <tr>
                            <td><strong><?= cms_e($row['field']) ?></strong></td>
                            <td class="muted" style="max-width:380px;"><?= nl2br(cms_e($row['before'])) ?></td>
                            <td style="max-width:380px;"><?= nl2br(cms_e($row['after'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if ($bodyCompareRows): ?>
            <h3 style="font-size:.95rem;margin:1rem 0 .45rem;">Body block diff (side-by-side)</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width:70px;">#</th>
                        <th>Baseline block</th>
                        <th>Revision block</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bodyCompareRows as $row): ?>
                        <tr class="<?= $row['changed'] ? 'diff-changed' : '' ?>">
                            <td>#<?= (int)$row['index'] ?></td>
                            <td style="max-width:420px;">
                                <?php if ($row['left']): ?>
                                    <strong><?= cms_e(admin_revision_block_title($row['left'])) ?></strong>
                                    <div class="muted" style="white-space:pre-wrap;"><?= nl2br(cms_e(admin_revision_block_preview($row['left']))) ?></div>
                                <?php else: ?>
                                    <span class="muted">(not present)</span>
                                <?php endif; ?>
                            </td>
                            <td style="max-width:420px;">
                                <?php if ($row['right']): ?>
                                    <strong><?= cms_e(admin_revision_block_title($row['right'])) ?></strong>
                                    <div class="muted" style="white-space:pre-wrap;"><?= nl2br(cms_e(admin_revision_block_preview($row['right']))) ?></div>
                                <?php else: ?>
                                    <span class="muted">(not present)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
<?php endif; ?>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>When</th>
            <th>Editor</th>
            <th>Note</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($revisions as $i => $rev): ?>
            <tr>
                <td>#<?= (int)$rev['id'] ?></td>
                <td class="muted"><?= cms_e(date('M j, Y g:i a', strtotime($rev['created_at']))) ?></td>
                <td class="muted"><?= cms_e($rev['editor'] ?? '') ?></td>
                <td><?= cms_e($rev['note']) ?></td>
                <td>
                    <a class="btn btn-ghost btn-sm" href="<?= cms_e($base) ?>/revisions.php?page_id=<?= (int)$pageId ?>&compare=<?= (int)$rev['id'] ?>&against=current">Compare to current</a>
                    <?php if (($i + 1) < count($revisions)): ?>
                        <a class="btn btn-ghost btn-sm" href="<?= cms_e($base) ?>/revisions.php?page_id=<?= (int)$pageId ?>&compare=<?= (int)$rev['id'] ?>&against=previous">Compare to previous</a>
                    <?php endif; ?>
                    <?php if ($i === 0): ?>
                        <span class="muted">current</span>
                    <?php else: ?>
                        <form class="inline-form" method="post" action="<?= cms_e($base) ?>/revisions.php" onsubmit="return confirm('Restore content from this revision?');" style="display:inline-flex;align-items:center;gap:.35rem;flex-wrap:wrap;">
                            <?= cms_csrf_field() ?>
                            <input type="hidden" name="action" value="rollback">
                            <input type="hidden" name="page_id" value="<?= (int)$pageId ?>">
                            <input type="hidden" name="revision_id" value="<?= (int)$rev['id'] ?>">
                            <input type="text" name="rollback_note" maxlength="500" placeholder="Rollback reason (required)" required style="min-width:220px;">
                            <button class="btn btn-ghost btn-sm" type="submit">Restore</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php
admin_footer();
