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
    <div class="login-brand">
        <span class="login-logo" aria-hidden="true">
            <svg viewBox="0 0 24 24" role="img" aria-hidden="true">
                <defs>
                    <linearGradient id="cmsLoginGrad" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0" stop-color="#F98A1E"/>
                        <stop offset="1" stop-color="#E5431C"/>
                    </linearGradient>
                </defs>
                <path d="M7 3 L20 12 L7 21 L12 12 Z" fill="url(#cmsLoginGrad)"/>
            </svg>
        </span>
        <span>Health Plans CMS</span>
    </div>
    <div class="card login-card">
        <h1 class="login-title">Sign in</h1>
        <p class="login-sub">Use your company Google account to access the content manager.</p>
        <?php if (!cms_oauth_google_configured()): ?>
            <div class="flash error">Google OAuth is not configured. Set GOOGLE_OAUTH_CLIENT_ID, GOOGLE_OAUTH_CLIENT_SECRET, and GOOGLE_OAUTH_REDIRECT_URI in .env.</div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="flash error"><?= cms_e($error) ?></div>
        <?php endif; ?>
        <?php if (cms_oauth_google_configured()): ?>
            <a class="btn btn-google" href="<?= cms_e(CMS_ADMIN_PATH) ?>/oauth-google-start.php">
                <svg width="18" height="18" viewBox="0 0 18 18" aria-hidden="true"><path fill="#EA4335" d="M9 3.58c1.32 0 2.5.45 3.44 1.35l2.58-2.58C13.46.9 11.43 0 9 0A9 9 0 0 0 .96 4.95l3.01 2.34C4.68 5.17 6.66 3.58 9 3.58z"/><path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.72v2.26h2.92c1.71-1.57 2.68-3.89 2.68-6.62z"/><path fill="#FBBC05" d="M3.97 10.72A5.41 5.41 0 0 1 3.68 9c0-.6.1-1.18.29-1.72V4.95H.96A9 9 0 0 0 0 9c0 1.45.35 2.82.96 4.05l3.01-2.33z"/><path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.92-2.26c-.81.54-1.85.86-3.04.86-2.34 0-4.32-1.58-5.03-3.7L.96 13.05A9 9 0 0 0 9 18z"/></svg>
                Sign in with Google
            </a>
        <?php endif; ?>
        <p class="login-domain">Allowed domain <strong>@<?= cms_e(CMS_OAUTH_ALLOWED_DOMAIN) ?></strong></p>
    </div>
    <p class="muted login-foot">Roles are assigned by configured email mapping.</p>
</div>
<?php
admin_footer();
