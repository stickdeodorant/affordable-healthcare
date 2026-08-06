<?php
/**
 * Starts Google OAuth login flow.
 */

require_once __DIR__ . '/../cms/bootstrap.php';

if (!cms_oauth_google_configured()) {
    header('Location: ' . CMS_ADMIN_PATH . '/login.php?error=' . rawurlencode('Google OAuth is not configured.'));
    exit;
}

header('Location: ' . cms_oauth_google_start_url());
exit;
