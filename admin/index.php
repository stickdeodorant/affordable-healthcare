<?php
/**
 * Admin pages list: search, filter, quick status changes, delete.
 */

require_once __DIR__ . '/../cms/bootstrap.php';
require_once __DIR__ . '/_layout.php';

cms_require_login();

$base = CMS_ADMIN_PATH;

// State-changing actions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cms_csrf_require();
    $action = (string)($_POST['action'] ?? '');
    $pageId = (int)($_POST['page_id'] ?? 0);

    if ($pageId > 0 && $action === 'set_status') {
        $status = (string)($_POST['status'] ?? '');
        if (in_array($status, $GLOBALS['CMS_STATUSES'], true)) {
            $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;
            if ($status === 'published') {
                cms_write(
                    'UPDATE cms_pages
                        SET status = ?, updated_by = ?,
                            published_at = COALESCE(published_at, ?)
                      WHERE id = ?',
                    'sssi',
                    [$status, cms_current_user()['email'], $publishedAt, $pageId]
                );
            } else {
                cms_write(
                    'UPDATE cms_pages SET status = ?, updated_by = ? WHERE id = ?',
                    'ssi',
                    [$status, cms_current_user()['email'], $pageId]
                );
            }
            cms_audit('page_status', 'cms_page', $pageId, ['status' => $status]);
            admin_flash_set('success', 'Page status updated to ' . $status . '.');
        }
    } elseif ($pageId > 0 && $action === 'delete') {
        if (cms_user_can('admin')) {
            $victim = cms_select_one('SELECT slug FROM cms_pages WHERE id = ? LIMIT 1', 'i', [$pageId]);
            cms_write('DELETE FROM cms_pages WHERE id = ?', 'i', [$pageId]);
            cms_write('DELETE FROM cms_page_revisions WHERE page_id = ?', 'i', [$pageId]);
            cms_audit('page_delete', 'cms_page', $pageId, ['slug' => $victim['slug'] ?? '']);
            admin_flash_set('success', 'Page deleted.');
        } else {
            admin_flash_set('error', 'Only administrators can delete pages.');
        }
    }
    header('Location: ' . $base . '/');
    exit;
}

// Filters.
$q = trim((string)($_GET['q'] ?? ''));
$statusFilter = (string)($_GET['status'] ?? '');

$where = [];
$types = '';
$params = [];
if ($q !== '') {
    $where[] = '(slug LIKE ? OR title LIKE ?)';
    $types .= 'ss';
    $like = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
}
if (in_array($statusFilter, $GLOBALS['CMS_STATUSES'], true)) {
    $where[] = 'status = ?';
    $types .= 's';
    $params[] = $statusFilter;
}
$sql = 'SELECT id, slug, title, status, theme, updated_at, updated_by FROM cms_pages';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY updated_at DESC LIMIT 500';

$pages = cms_select($sql, $types, $params);
$canDelete = cms_user_can('admin');

admin_header('Pages');
?>
<h1>Pages</h1>

<form class="toolbar" method="get" action="<?= cms_e($base) ?>/">
    <div>
        <label for="q">Search</label>
        <input type="text" id="q" name="q" value="<?= cms_e($q) ?>" placeholder="slug or title" style="min-width:240px;">
    </div>
    <div>
        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="">All</option>
            <?php foreach ($GLOBALS['CMS_STATUSES'] as $st): ?>
                <option value="<?= cms_e($st) ?>" <?= $statusFilter === $st ? 'selected' : '' ?>><?= cms_e(ucfirst($st)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div><button class="btn btn-ghost" type="submit">Filter</button></div>
    <div><a class="btn btn-primary" href="<?= cms_e($base) ?>/edit.php">New page</a></div>
</form>

<?php if (!$pages): ?>
    <div class="card muted">No pages found.</div>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>Title</th>
            <th>Slug</th>
            <th>Status</th>
            <th>Theme</th>
            <th>Updated</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pages as $p): ?>
            <tr>
                <td><a href="<?= cms_e($base) ?>/edit.php?id=<?= (int)$p['id'] ?>"><?= cms_e($p['title'] !== '' ? $p['title'] : '(untitled)') ?></a></td>
                <td><code>/<?= cms_e($p['slug']) ?></code></td>
                <td><?= admin_status_badge($p['status']) ?></td>
                <td class="muted"><?= cms_e($p['theme']) ?></td>
                <td class="muted">
                    <?= cms_e(date('M j, Y g:i a', strtotime($p['updated_at']))) ?><br>
                    <?= cms_e($p['updated_by'] ?? '') ?>
                </td>
                <td>
                    <div class="actions">
                        <a class="btn btn-ghost btn-sm" href="<?= cms_e($base) ?>/edit.php?id=<?= (int)$p['id'] ?>">Edit</a>
                        <?php if ($p['status'] === 'published'): ?>
                            <a class="btn btn-ghost btn-sm" href="/<?= cms_e($p['slug']) ?>" target="_blank" rel="noopener">View</a>
                            <form class="inline-form" method="post" action="<?= cms_e($base) ?>/">
                                <?= cms_csrf_field() ?>
                                <input type="hidden" name="action" value="set_status">
                                <input type="hidden" name="page_id" value="<?= (int)$p['id'] ?>">
                                <input type="hidden" name="status" value="draft">
                                <button class="btn btn-ghost btn-sm" type="submit">Unpublish</button>
                            </form>
                        <?php else: ?>
                            <form class="inline-form" method="post" action="<?= cms_e($base) ?>/">
                                <?= cms_csrf_field() ?>
                                <input type="hidden" name="action" value="set_status">
                                <input type="hidden" name="page_id" value="<?= (int)$p['id'] ?>">
                                <input type="hidden" name="status" value="published">
                                <button class="btn btn-primary btn-sm" type="submit">Publish</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($canDelete): ?>
                            <form class="inline-form" method="post" action="<?= cms_e($base) ?>/" onsubmit="return confirm('Delete this page permanently?');">
                                <?= cms_csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="page_id" value="<?= (int)$p['id'] ?>">
                                <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php
admin_footer();
