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
                    [$pageId, json_encode($fresh, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $user, 'rollback from revision #' . $revId]
                );
            }
            cms_audit('page_rollback', 'cms_page', $pageId, ['revision_id' => $revId]);
            admin_flash_set('success', 'Content restored from revision #' . $revId . '.');
        }
    }
    header('Location: ' . $base . '/revisions.php?page_id=' . $pageId);
    exit;
}

$revisions = cms_select(
    'SELECT id, editor, note, created_at FROM cms_page_revisions WHERE page_id = ? ORDER BY created_at DESC, id DESC LIMIT 200',
    'i',
    [$pageId]
);

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
                    <?php if ($i === 0): ?>
                        <span class="muted">current</span>
                    <?php else: ?>
                        <form class="inline-form" method="post" action="<?= cms_e($base) ?>/revisions.php" onsubmit="return confirm('Restore content from this revision?');">
                            <?= cms_csrf_field() ?>
                            <input type="hidden" name="action" value="rollback">
                            <input type="hidden" name="page_id" value="<?= (int)$pageId ?>">
                            <input type="hidden" name="revision_id" value="<?= (int)$rev['id'] ?>">
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
