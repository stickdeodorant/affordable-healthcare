<?php
/**
 * Google OAuth callback endpoint.
 */

require_once __DIR__ . '/../cms/bootstrap.php';

if (!cms_oauth_google_configured()) {
    header('Location: ' . CMS_ADMIN_PATH . '/login.php?error=' . rawurlencode('Google OAuth is not configured.'));
    exit;
}

$code = trim((string)($_GET['code'] ?? ''));
$state = trim((string)($_GET['state'] ?? ''));

if ($code === '' || $state === '') {
    header('Location: ' . CMS_ADMIN_PATH . '/login.php?error=' . rawurlencode('Missing Google OAuth response.'));
    exit;
}

$result = cms_oauth_google_callback($code, $state);
if (!$result['ok']) {
    header('Location: ' . CMS_ADMIN_PATH . '/login.php?error=' . rawurlencode($result['error']));
    exit;
}

header('Location: ' . CMS_ADMIN_PATH . '/');
exit;
