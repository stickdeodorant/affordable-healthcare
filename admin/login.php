<?php
/**
 * Admin login via Google OAuth (restricted to allowed company domain).
 */

require_once __DIR__ . '/../cms/bootstrap.php';
require_once __DIR__ . '/_layout.php';

if (cms_is_logged_in()) {
    header('Location: ' . CMS_ADMIN_PATH . '/');
    exit;
}

$error = '';
if (!empty($_GET['error'])) {
    $error = (string)$_GET['error'];
}

admin_header('Sign in');
?>
<div class="login-wrap">
    <h1>CMS Admin</h1>
    <div class="card">
        <?php if (!cms_oauth_google_configured()): ?>
            <div class="flash error">Google OAuth is not configured. Set GOOGLE_OAUTH_CLIENT_ID, GOOGLE_OAUTH_CLIENT_SECRET, and GOOGLE_OAUTH_REDIRECT_URI in .env.</div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="flash error"><?= cms_e($error) ?></div>
        <?php endif; ?>
        <p style="margin:0 0 1rem;">Sign in with your company Google account to access the CMS.</p>
        <p class="muted" style="margin-top:0;">Allowed domain: @<?= cms_e(CMS_OAUTH_ALLOWED_DOMAIN) ?></p>
        <?php if (cms_oauth_google_configured()): ?>
            <div style="margin-top:1.25rem;">
                <a class="btn btn-primary" href="<?= cms_e(CMS_ADMIN_PATH) ?>/oauth-google-start.php">Sign in with Google</a>
            </div>
        <?php endif; ?>
    </div>
    <p class="muted">Roles are assigned by configured email mapping.</p>
</div>
<?php
admin_footer();
