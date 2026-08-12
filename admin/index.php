<?php
/**
 * Admin pages list: search, filter, quick status changes, delete.
 */

require_once __DIR__ . '/../cms/bootstrap.php';
require_once __DIR__ . '/_layout.php';

cms_require_login();

$base = CMS_ADMIN_PATH;

function admin_page_risk(array $page): array {
    $score = 0;
    $reasons = [];

    if (trim((string)($page['hero_headline'] ?? '')) === '') {
        $score += 2;
        $reasons[] = 'hero missing';
    }
    if (trim((string)($page['cta_text'] ?? '')) === '' || trim((string)($page['cta_href'] ?? '')) === '') {
        $score += 2;
        $reasons[] = 'CTA incomplete';
    }
    $blocks = cms_json_decode((string)($page['body_json'] ?? '[]'));
    if (!is_array($blocks) || count($blocks) === 0) {
        $score += 3;
        $reasons[] = 'no content blocks';
    }
    $metaLen = mb_strlen(trim((string)($page['meta_description'] ?? '')));
    if ($metaLen < 80 || $metaLen > 160) {
        $score += 1;
        $reasons[] = 'meta length';
    }
    if (stripos((string)($page['canonical'] ?? ''), 'localhost') !== false) {
        $score += 2;
        $reasons[] = 'localhost canonical';
    }

    if ($score >= 5) {
        $level = 'high';
    } elseif ($score >= 3) {
        $level = 'medium';
    } else {
        $level = 'low';
    }

    return [
        'level' => $level,
        'score' => $score,
        'reason' => $reasons ? implode(', ', array_slice($reasons, 0, 2)) : 'healthy baseline',
    ];
}

// State-changing actions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cms_csrf_require();
    $action = (string)($_POST['action'] ?? '');
    $pageId = (int)($_POST['page_id'] ?? 0);
    $pageIds = array_values(array_filter(array_map('intval', (array)($_POST['page_ids'] ?? [])), function ($id) {
        return $id > 0;
    }));

    if ($action === 'bulk_action') {
        $bulkAction = (string)($_POST['bulk_action'] ?? '');
        $bulkStatus = (string)($_POST['bulk_status'] ?? '');
        $selected = $pageIds;

        if (!$selected) {
            admin_flash_set('error', 'Select at least one page first.');
        } elseif ($bulkAction === 'set_status' && !cms_user_can('reviewer')) {
            admin_flash_set('error', 'You do not have permission to change publish status.');
        } elseif ($bulkAction === 'delete' && !cms_user_can('admin')) {
            admin_flash_set('error', 'Only administrators can delete pages.');
        } elseif ($bulkAction === 'set_status' && !in_array($bulkStatus, $GLOBALS['CMS_STATUSES'], true)) {
            admin_flash_set('error', 'Choose a valid status for the selected pages.');
        } else {
            $changed = 0;
            $skipped = 0;
            foreach ($selected as $selectedId) {
                $page = cms_select_one('SELECT id, slug, status, published_at FROM cms_pages WHERE id = ? LIMIT 1', 'i', [$selectedId]);
                if (!$page) {
                    $skipped++;
                    continue;
                }

                if ($bulkAction === 'delete') {
                    cms_write('DELETE FROM cms_pages WHERE id = ?', 'i', [$selectedId]);
                    cms_write('DELETE FROM cms_page_revisions WHERE page_id = ?', 'i', [$selectedId]);
                    cms_audit('page_delete', 'cms_page', $selectedId, ['slug' => $page['slug'] ?? 'bulk']);
                    $changed++;
                    continue;
                }

                if ($bulkAction === 'set_status') {
                    $publishedAt = $bulkStatus === 'published' ? date('Y-m-d H:i:s') : null;
                    if ($bulkStatus === 'published') {
                        cms_write(
                            'UPDATE cms_pages
                                SET status = ?, updated_by = ?,
                                    published_at = COALESCE(published_at, ?)
                              WHERE id = ?',
                            'sssi',
                            [$bulkStatus, cms_current_user()['email'], $publishedAt, $selectedId]
                        );
                    } else {
                        cms_write(
                            'UPDATE cms_pages SET status = ?, updated_by = ? WHERE id = ?',
                            'ssi',
                            [$bulkStatus, cms_current_user()['email'], $selectedId]
                        );
                    }
                    cms_audit('page_status', 'cms_page', $selectedId, ['status' => $bulkStatus, 'slug' => $page['slug'] ?? '']);
                    $changed++;
                }
            }

            if ($changed > 0) {
                $message = 'Bulk action completed for ' . $changed . ' page' . ($changed === 1 ? '' : 's') . '.';
                if ($skipped > 0) {
                    $message .= ' ' . $skipped . ' page' . ($skipped === 1 ? '' : 's') . ' were skipped.';
                }
                admin_flash_set('success', $message);
            } else {
                admin_flash_set('error', 'No pages were updated.');
            }
        }
        header('Location: ' . $base . '/');
        exit;
    }

    if ($pageId > 0 && $action === 'set_status') {
        $status = (string)($_POST['status'] ?? '');
        if (!cms_user_can('reviewer')) {
            admin_flash_set('error', 'You do not have permission to change publish status.');
        } elseif (in_array($status, $GLOBALS['CMS_STATUSES'], true)) {
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
$activityDays = (int)($_GET['activity_days'] ?? 7);
$allowedActivityDays = [1, 7, 30, 90];
if (!in_array($activityDays, $allowedActivityDays, true)) {
    $activityDays = 7;
}

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
$sql = 'SELECT id, slug, title, status, theme, meta_description, canonical, hero_headline, cta_text, cta_href, body_json, updated_at, updated_by FROM cms_pages';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY updated_at DESC LIMIT 500';

$pages = cms_select($sql, $types, $params);
$statusCounts = ['draft' => 0, 'review' => 0, 'published' => 0, 'archived' => 0];
foreach (cms_select('SELECT status, COUNT(*) AS total FROM cms_pages GROUP BY status') as $row) {
    $st = strtolower((string)($row['status'] ?? ''));
    if (array_key_exists($st, $statusCounts)) {
        $statusCounts[$st] = (int)($row['total'] ?? 0);
    }
}
$activityCounts = ['page_save' => 0, 'page_submit_review' => 0, 'page_status' => 0, 'page_rollback' => 0];
foreach (cms_select(
    "SELECT action_type, COUNT(*) AS total
     FROM admin_activity_log
         WHERE action_type IN ('page_save','page_submit_review','page_status','page_rollback')
       AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
     GROUP BY action_type",
    'i',
    [$activityDays]
) as $row) {
    $type = (string)($row['action_type'] ?? '');
    if (array_key_exists($type, $activityCounts)) {
        $activityCounts[$type] = (int)($row['total'] ?? 0);
    }
}

$recentActivity = cms_select(
    "SELECT created_at, admin_user, action_type, target_id
     FROM admin_activity_log
         WHERE action_type IN ('page_save','page_submit_review','page_status','page_rollback','page_delete')
       AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
     ORDER BY created_at DESC, id DESC
     LIMIT 8",
    'i',
    [$activityDays]
);

$revisionHotspots = cms_select(
    "SELECT p.id, p.slug, p.title, COUNT(r.id) AS revision_count, MAX(r.created_at) AS last_revision_at
     FROM cms_pages p
     LEFT JOIN cms_page_revisions r ON r.page_id = p.id
     GROUP BY p.id, p.slug, p.title
     ORDER BY revision_count DESC, last_revision_at DESC
     LIMIT 5"
);

$recentPublished = cms_select(
    "SELECT id, slug, title, published_at, updated_by
     FROM cms_pages
     WHERE status = 'published'
     ORDER BY published_at DESC, updated_at DESC
     LIMIT 5"
);

if ((string)($_GET['export_activity'] ?? '') === '1') {
    $csvRows = cms_select(
        "SELECT created_at, admin_user, action_type, target_type, target_id, status
         FROM admin_activity_log
                 WHERE action_type IN ('page_save','page_submit_review','page_status','page_rollback','page_delete')
           AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
         ORDER BY created_at DESC, id DESC
         LIMIT 2000",
        'i',
        [$activityDays]
    );

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="cms-activity-last-' . $activityDays . '-days.csv"');
    $out = fopen('php://output', 'w');
    if ($out !== false) {
        fputcsv($out, ['created_at', 'admin_user', 'action_type', 'target_type', 'target_id', 'status']);
        foreach ($csvRows as $row) {
            fputcsv($out, [
                (string)($row['created_at'] ?? ''),
                (string)($row['admin_user'] ?? ''),
                (string)($row['action_type'] ?? ''),
                (string)($row['target_type'] ?? ''),
                (string)($row['target_id'] ?? ''),
                (string)($row['status'] ?? ''),
            ]);
        }
        fclose($out);
    }
    exit;
}
$canDelete = cms_user_can('admin');

$newPageBtn = '<a class="btn btn-primary" href="' . cms_e($base) . '/edit.php">'
    . '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>'
    . ' New page</a>';

admin_header('Pages', 'Create, review, and publish every landing page.', $newPageBtn);
?>

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
</form>

<div class="card">
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-label"><span class="stat-dot dot-draft"></span> Drafts</div>
            <div class="stat-value"><?= (int)$statusCounts['draft'] ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label"><span class="stat-dot dot-review"></span> In review</div>
            <div class="stat-value"><?= (int)$statusCounts['review'] ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label"><span class="stat-dot dot-published"></span> Published</div>
            <div class="stat-value"><?= (int)$statusCounts['published'] ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label"><span class="stat-dot dot-archived"></span> Archived</div>
            <div class="stat-value"><?= (int)$statusCounts['archived'] ?></div>
        </div>
    </div>
    <div class="hint" style="margin-top:.75rem;">Use <strong>Submit for review</strong> in the editor for approval workflows. Reviewers and admins can publish.</div>
</div>

<div class="card">
    <h2 style="font-size:1.05rem;margin:0 0 .5rem;">Activity and performance snapshot</h2>
    <form class="toolbar" method="get" action="<?= cms_e($base) ?>/" style="margin-bottom:.5rem;">
        <input type="hidden" name="q" value="<?= cms_e($q) ?>">
        <input type="hidden" name="status" value="<?= cms_e($statusFilter) ?>">
        <div>
            <label for="activity_days">Activity window</label>
            <select id="activity_days" name="activity_days">
                <option value="1" <?= $activityDays === 1 ? 'selected' : '' ?>>Last 24 hours</option>
                <option value="7" <?= $activityDays === 7 ? 'selected' : '' ?>>Last 7 days</option>
                <option value="30" <?= $activityDays === 30 ? 'selected' : '' ?>>Last 30 days</option>
                <option value="90" <?= $activityDays === 90 ? 'selected' : '' ?>>Last 90 days</option>
            </select>
        </div>
        <div><button type="submit" class="btn btn-ghost btn-sm">Apply window</button></div>
        <div><a class="btn btn-ghost btn-sm" href="<?= cms_e($base) ?>/?q=<?= urlencode($q) ?>&status=<?= urlencode($statusFilter) ?>&activity_days=<?= (int)$activityDays ?>&export_activity=1">Export CSV</a></div>
    </form>
    <div class="stat-grid" style="margin-bottom:.85rem;">
        <div class="stat-card">
            <div class="stat-label">Saves</div>
            <div class="stat-value"><?= (int)$activityCounts['page_save'] ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Review submissions</div>
            <div class="stat-value"><?= (int)$activityCounts['page_submit_review'] ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Status changes</div>
            <div class="stat-value"><?= (int)$activityCounts['page_status'] ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Rollbacks</div>
            <div class="stat-value"><?= (int)$activityCounts['page_rollback'] ?></div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <strong>Most revised pages</strong>
            <?php if (!$revisionHotspots): ?>
                <div class="muted">No revision activity yet.</div>
            <?php else: ?>
                <?php foreach ($revisionHotspots as $hot): ?>
                    <div class="muted" style="margin-top:.25rem;">
                        <a href="<?= cms_e($base) ?>/edit.php?id=<?= (int)$hot['id'] ?>"><?= cms_e($hot['title'] !== '' ? $hot['title'] : '/' . $hot['slug']) ?></a>
                        &middot; <?= (int)$hot['revision_count'] ?> revisions
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="col">
            <strong>Recently published</strong>
            <?php if (!$recentPublished): ?>
                <div class="muted">No published pages yet.</div>
            <?php else: ?>
                <?php foreach ($recentPublished as $pub): ?>
                    <?php $pubAt = !empty($pub['published_at']) ? date('M j, g:i a', strtotime((string)$pub['published_at'])) : 'date unavailable'; ?>
                    <div class="muted" style="margin-top:.25rem;">
                        <a href="<?= cms_e($base) ?>/edit.php?id=<?= (int)$pub['id'] ?>"><?= cms_e($pub['title'] !== '' ? $pub['title'] : '/' . $pub['slug']) ?></a>
                        &middot; <?= cms_e($pubAt) ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="col">
            <strong>Recent page activity</strong>
            <?php if (!$recentActivity): ?>
                <div class="muted">No tracked activity yet.</div>
            <?php else: ?>
                <?php foreach ($recentActivity as $act): ?>
                    <div class="muted" style="margin-top:.25rem;">
                        <?= cms_e(date('M j, g:i a', strtotime($act['created_at']))) ?>
                        &middot; <?= cms_e((string)$act['action_type']) ?>
                        &middot; <?= cms_e((string)$act['admin_user']) ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!$pages): ?>
    <div class="card muted">No pages found.</div>
<?php else: ?>
<form method="post" action="<?= cms_e($base) ?>/" id="bulk-pages-form">
    <?= cms_csrf_field() ?>
    <div class="card" style="margin-bottom:1rem;">
        <div class="toolbar" style="margin-bottom:0;">
            <div>
                <label for="bulk_action">Bulk action</label>
                <select id="bulk_action" name="bulk_action">
                    <option value="set_status">Change status</option>
                    <option value="delete">Delete</option>
                </select>
            </div>
            <div>
                <label for="bulk_status">Status</label>
                <select id="bulk_status" name="bulk_status">
                    <?php foreach ($GLOBALS['CMS_STATUSES'] as $st): ?>
                        <option value="<?= cms_e($st) ?>"><?= cms_e(ucfirst($st)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button class="btn btn-primary" type="submit">Apply to selected</button>
            </div>
            <div class="muted">Status changes require reviewer access. Delete requires admin access.</div>
        </div>
    </div>
<div class="table-wrap">
<table>
    <thead>
        <tr>
            <th style="width:34px;"><input type="checkbox" id="select-all-pages" aria-label="Select all pages"></th>
            <th>Title</th>
            <th>Slug</th>
            <th>Status</th>
            <th>Risk</th>
            <th>Updated</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pages as $p): ?>
            <?php $risk = admin_page_risk($p); ?>
            <tr>
                <td><input type="checkbox" class="page-select" name="page_ids[]" value="<?= (int)$p['id'] ?>" aria-label="Select <?= cms_e($p['title'] !== '' ? $p['title'] : $p['slug']) ?>"></td>
                <td><a href="<?= cms_e($base) ?>/edit.php?id=<?= (int)$p['id'] ?>"><?= cms_e($p['title'] !== '' ? $p['title'] : '(untitled)') ?></a></td>
                <td><code>/<?= cms_e($p['slug']) ?></code></td>
                <td><?= admin_status_badge($p['status']) ?></td>
                <td>
                    <span class="badge badge-risk-<?= cms_e($risk['level']) ?>"><?= cms_e(ucfirst($risk['level'])) ?></span>
                    <div class="muted"><?= cms_e((string)$risk['reason']) ?></div>
                </td>
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
</div>
</form>
<script>
(function () {
    var selectAll = document.getElementById('select-all-pages');
    if (!selectAll) {
        return;
    }
    var items = Array.prototype.slice.call(document.querySelectorAll('.page-select'));
    selectAll.addEventListener('change', function () {
        items.forEach(function (box) {
            box.checked = selectAll.checked;
        });
    });
})();
</script>
<?php endif; ?>
<?php
admin_footer();
