<?php
/**
 * Media library: upload, browse, copy path, and delete image assets used by
 * CMS image blocks and hero/OG fields. Uploads are validated by extension,
 * MIME type, and size, stored under /img/cms, and that directory is hardened
 * against script execution.
 */

require_once __DIR__ . '/../cms/bootstrap.php';
require_once __DIR__ . '/_layout.php';

cms_require_login();

$base = CMS_ADMIN_PATH;
$allowedExt = $GLOBALS['CMS_UPLOAD_ALLOWED_EXT'] ?? ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
$maxBytes = (int)($GLOBALS['CMS_UPLOAD_MAX_BYTES'] ?? 5 * 1024 * 1024);
$allowedMime = [
    'image/jpeg' => ['jpg', 'jpeg'],
    'image/png' => ['png'],
    'image/gif' => ['gif'],
    'image/webp' => ['webp'],
    'image/svg+xml' => ['svg'],
    'text/plain' => ['svg'], // some servers sniff SVG as text/plain
    'text/xml' => ['svg'],
    'application/xml' => ['svg'],
];

/** Ensure the upload directory exists and cannot execute uploaded scripts. */
function media_ensure_dir(): void {
    if (!is_dir(CMS_UPLOAD_DIR)) {
        @mkdir(CMS_UPLOAD_DIR, 0755, true);
    }
    $ht = CMS_UPLOAD_DIR . '/.htaccess';
    if (!is_file($ht)) {
        @file_put_contents(
            $ht,
            "# Auto-generated: serve uploads as static files only, never execute.\n"
            . "php_flag engine off\n"
            . "RemoveHandler .php .phtml .php3 .php4 .php5 .php7 .phar\n"
            . "<FilesMatch \"\\.(php|phtml|php3|php4|php5|php7|phar|pl|py|cgi|asp|aspx|sh)$\">\n"
            . "  Require all denied\n"
            . "</FilesMatch>\n"
        );
    }
}

/** Reject SVGs that contain active content. */
function media_svg_is_safe(string $path): bool {
    $data = (string)@file_get_contents($path);
    if ($data === '') {
        return false;
    }
    if (preg_match('/<script|<!ENTITY|\son\w+\s*=|javascript:|<foreignObject/i', $data)) {
        return false;
    }
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cms_csrf_require();
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'upload') {
        $file = $_FILES['file'] ?? null;
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            admin_flash_set('error', 'Choose a file to upload.');
        } elseif (($file['error'] ?? 1) !== UPLOAD_ERR_OK) {
            admin_flash_set('error', 'Upload failed (server error code ' . (int)$file['error'] . ').');
        } elseif ((int)$file['size'] > $maxBytes) {
            admin_flash_set('error', 'File is too large. Max ' . round($maxBytes / (1024 * 1024), 1) . ' MB.');
        } elseif (!is_uploaded_file($file['tmp_name'])) {
            admin_flash_set('error', 'Invalid upload.');
        } else {
            $origName = (string)$file['name'];
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = (string)$finfo->file($file['tmp_name']);

            $extOk = in_array($ext, $allowedExt, true);
            $mimeOk = isset($allowedMime[$mime]) && in_array($ext, $allowedMime[$mime], true);

            if (!$extOk) {
                admin_flash_set('error', 'Unsupported file type. Allowed: ' . implode(', ', $allowedExt) . '.');
            } elseif (!$mimeOk) {
                admin_flash_set('error', 'File contents do not match its extension (' . cms_e($mime) . ').');
            } elseif ($ext === 'svg' && !media_svg_is_safe($file['tmp_name'])) {
                admin_flash_set('error', 'That SVG contains script or event attributes and was rejected.');
            } else {
                media_ensure_dir();
                $stem = cms_slug(pathinfo($origName, PATHINFO_FILENAME));
                if ($stem === '') {
                    $stem = 'image';
                }
                $name = $stem . '-' . date('Ymd-His') . '-' . substr(bin2hex(random_bytes(3)), 0, 6) . '.' . $ext;
                $dest = CMS_UPLOAD_DIR . '/' . $name;
                if (@move_uploaded_file($file['tmp_name'], $dest)) {
                    @chmod($dest, 0644);
                    cms_audit('media_upload', 'media', $name, ['mime' => $mime, 'size' => (int)$file['size']]);
                    admin_flash_set('success', 'Uploaded: ' . $name);
                } else {
                    admin_flash_set('error', 'Could not save the file. Check folder permissions.');
                }
            }
        }
    } elseif ($action === 'delete') {
        $name = basename((string)($_POST['name'] ?? ''));
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $target = CMS_UPLOAD_DIR . '/' . $name;
        if ($name !== '' && in_array($ext, $allowedExt, true) && is_file($target) && strpos(realpath($target) ?: '', realpath(CMS_UPLOAD_DIR) ?: "\0") === 0) {
            @unlink($target);
            cms_audit('media_delete', 'media', $name);
            admin_flash_set('success', 'Deleted: ' . $name);
        } else {
            admin_flash_set('error', 'File not found.');
        }
    }
    header('Location: ' . $base . '/media.php');
    exit;
}

media_ensure_dir();
$files = [];
foreach ((array)@scandir(CMS_UPLOAD_DIR) as $entry) {
    if ($entry === '.' || $entry === '..') {
        continue;
    }
    $ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        continue;
    }
    $full = CMS_UPLOAD_DIR . '/' . $entry;
    if (!is_file($full)) {
        continue;
    }
    $files[] = ['name' => $entry, 'url' => CMS_UPLOAD_URL . '/' . $entry, 'size' => (int)@filesize($full), 'mtime' => (int)@filemtime($full)];
}
usort($files, fn($a, $b) => $b['mtime'] <=> $a['mtime']);

admin_header('Media library');
?>
<h1>Media library</h1>

<div class="card">
    <form method="post" action="<?= cms_e($base) ?>/media.php" enctype="multipart/form-data">
        <?= cms_csrf_field() ?>
        <input type="hidden" name="action" value="upload">
        <label for="file">Upload an image</label>
        <input type="file" id="file" name="file" accept="<?= cms_e('.' . implode(',.', $allowedExt)) ?>" required>
        <div class="hint">Allowed: <?= cms_e(implode(', ', $allowedExt)) ?>. Max <?= cms_e((string)round($maxBytes / (1024 * 1024), 1)) ?> MB. Stored in <code><?= cms_e(CMS_UPLOAD_URL) ?></code>.</div>
        <div style="margin-top:1rem;">
            <button class="btn btn-primary" type="submit">Upload</button>
        </div>
    </form>
</div>

<?php if (!$files): ?>
    <div class="card muted">No media uploaded yet.</div>
<?php else: ?>
<div class="media-grid">
    <?php foreach ($files as $f): ?>
        <div class="media-item">
            <div class="media-thumb"><img src="<?= cms_e($f['url']) ?>" alt="<?= cms_e($f['name']) ?>" loading="lazy"></div>
            <div class="media-meta">
                <div class="media-name" title="<?= cms_e($f['name']) ?>"><?= cms_e($f['name']) ?></div>
                <div class="muted"><?= cms_e((string)round($f['size'] / 1024, 1)) ?> KB</div>
                <div class="media-path"><code><?= cms_e($f['url']) ?></code></div>
                <div class="media-actions">
                    <button type="button" class="btn btn-ghost btn-sm media-copy" data-path="<?= cms_e($f['url']) ?>">Copy path</button>
                    <form class="inline-form" method="post" action="<?= cms_e($base) ?>/media.php" onsubmit="return confirm('Delete this file?');">
                        <?= cms_csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="name" value="<?= cms_e($f['name']) ?>">
                        <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<style>
    .media-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:1rem; }
    .media-item { border:1px solid #e2e8ef; border-radius:10px; overflow:hidden; background:#fff; display:flex; flex-direction:column; }
    .media-thumb { aspect-ratio:16/10; background:#f4f7fa; display:flex; align-items:center; justify-content:center; overflow:hidden; }
    .media-thumb img { max-width:100%; max-height:100%; object-fit:contain; }
    .media-meta { padding:.6rem .7rem; font-size:.85rem; }
    .media-name { font-weight:600; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .media-path code { font-size:.72rem; word-break:break-all; }
    .media-actions { display:flex; gap:.4rem; margin-top:.5rem; align-items:center; flex-wrap:wrap; }
</style>
<script>
    document.querySelectorAll('.media-copy').forEach(function (b) {
        b.addEventListener('click', function () {
            var p = b.getAttribute('data-path') || '';
            if (navigator.clipboard) { navigator.clipboard.writeText(p); }
            var t = b.textContent; b.textContent = 'Copied!';
            setTimeout(function () { b.textContent = t; }, 1200);
        });
    });
</script>
<?php endif; ?>
<?php
admin_footer();
