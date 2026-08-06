<?php
/**
 * Admin login. Uses the hardened CMS session + throttled cms_login().
 */

require_once __DIR__ . '/../cms/bootstrap.php';
require_once __DIR__ . '/_layout.php';

if (cms_is_logged_in()) {
    header('Location: ' . CMS_ADMIN_PATH . '/');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cms_csrf_require();
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $result = cms_login($email, $password);
    if ($result['ok']) {
        header('Location: ' . CMS_ADMIN_PATH . '/');
        exit;
    }
    $error = $result['error'];
}

admin_header('Sign in');
?>
<div class="login-wrap">
    <h1>CMS Admin</h1>
    <div class="card">
        <?php if ($error !== ''): ?>
            <div class="flash error"><?= cms_e($error) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= cms_e(CMS_ADMIN_PATH) ?>/login.php" autocomplete="off">
            <?= cms_csrf_field() ?>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= cms_e($email) ?>" required autofocus>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <div style="margin-top:1.25rem;">
                <button class="btn btn-primary" type="submit">Sign in</button>
            </div>
        </form>
    </div>
    <p class="muted">Accounts are provisioned by an administrator.</p>
</div>
<?php
admin_footer();
