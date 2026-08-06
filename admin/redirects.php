<?php
/**
 * Redirect manager: list, add, and delete CMS redirects (used by render.php).
 */

require_once __DIR__ . '/../cms/bootstrap.php';
require_once __DIR__ . '/_layout.php';

cms_require_role('admin');

$base = CMS_ADMIN_PATH;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cms_csrf_require();
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'add') {
        $from = trim((string)($_POST['from_path'] ?? ''));
        $to = trim((string)($_POST['to_path'] ?? ''));
        $code = (int)($_POST['code'] ?? 301);
        if ($from !== '' && $from[0] !== '/') {
            $from = '/' . $from;
        }
        if (!in_array($code, [301, 302, 307, 308], true)) {
            $code = 301;
        }
        if ($from === '' || $from === '/') {
            admin_flash_set('error', 'From path is required (e.g. /old-page).');
        } elseif (!cms_url_is_safe($to) || $to === '') {
            admin_flash_set('error', 'Destination must be a safe URL or path.');
        } elseif ($from === $to) {
            admin_flash_set('error', 'From and destination cannot be identical.');
        } else {
            cms_write(
                'INSERT INTO cms_redirects (from_path, to_path, code)
                    VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE to_path = VALUES(to_path), code = VALUES(code)',
                'ssi',
                [$from, $to, $code]
            );
            cms_audit('redirect_save', 'cms_redirect', $from, ['to' => $to, 'code' => $code]);
            admin_flash_set('success', 'Redirect saved.');
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $row = cms_select_one('SELECT from_path FROM cms_redirects WHERE id = ? LIMIT 1', 'i', [$id]);
            cms_write('DELETE FROM cms_redirects WHERE id = ?', 'i', [$id]);
            cms_audit('redirect_delete', 'cms_redirect', $row['from_path'] ?? (string)$id);
            admin_flash_set('success', 'Redirect deleted.');
        }
    }
    header('Location: ' . $base . '/redirects.php');
    exit;
}

$redirects = cms_select('SELECT id, from_path, to_path, code, created_at FROM cms_redirects ORDER BY created_at DESC LIMIT 1000');

admin_header('Redirects');
?>
<h1>Redirects</h1>

<div class="card">
    <form method="post" action="<?= cms_e($base) ?>/redirects.php">
        <?= cms_csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <div class="row">
            <div class="col">
                <label for="from_path">From path</label>
                <input type="text" id="from_path" name="from_path" placeholder="/old-page" required>
                <div class="hint">Incoming path to catch (leading slash added automatically).</div>
            </div>
            <div class="col">
                <label for="to_path">Destination</label>
                <input type="text" id="to_path" name="to_path" placeholder="/new-page" required>
                <div class="hint">Target path or full URL.</div>
            </div>
            <div style="max-width:130px;">
                <label for="code">Type</label>
                <select id="code" name="code">
                    <option value="301">301 permanent</option>
                    <option value="302">302 temporary</option>
                    <option value="307">307 temporary</option>
                    <option value="308">308 permanent</option>
                </select>
            </div>
        </div>
        <div style="margin-top:1rem;">
            <button class="btn btn-primary" type="submit">Add redirect</button>
        </div>
    </form>
</div>

<?php if (!$redirects): ?>
    <div class="card muted">No redirects yet.</div>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>From</th>
            <th>To</th>
            <th>Type</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($redirects as $r): ?>
            <tr>
                <td><code><?= cms_e($r['from_path']) ?></code></td>
                <td><code><?= cms_e($r['to_path']) ?></code></td>
                <td><?= (int)$r['code'] ?></td>
                <td class="muted"><?= cms_e(date('M j, Y g:i a', strtotime($r['created_at']))) ?></td>
                <td>
                    <form class="inline-form" method="post" action="<?= cms_e($base) ?>/redirects.php" onsubmit="return confirm('Delete this redirect?');">
                        <?= cms_csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                        <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php
admin_footer();
