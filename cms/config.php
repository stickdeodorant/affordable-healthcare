<?php
/**
 * CMS configuration & constants.
 * Foundations layer — no output, safe to include anywhere.
 */

if (defined('CMS_ROOT')) {
    return;
}

require_once dirname(__DIR__) . '/inc/env.php';

define('CMS_ROOT', __DIR__);
define('CMS_APP_ROOT', dirname(__DIR__));
define('CMS_LIB', CMS_ROOT . '/lib');

// Web path where the admin UI lives (built in a later phase).
define('CMS_ADMIN_PATH', '/admin');

// Role hierarchy: higher number = more privilege.
$GLOBALS['CMS_ROLES'] = [
    'marketer' => 1,
    'reviewer' => 2,
    'admin'    => 3,
];

// Page workflow states.
$GLOBALS['CMS_STATUSES'] = ['draft', 'review', 'published', 'archived'];

// Block types the renderer/editor understands.
$GLOBALS['CMS_BLOCK_TYPES'] = ['hero', 'rich_text', 'cta_banner', 'image', 'faq_list'];

// Page template families the CMS can render (mirrors top-level landing page styles).
$GLOBALS['CMS_PAGE_TEMPLATES'] = [
    'default' => 'Modern landing page',
    'feature' => 'Legacy feature layout',
    'feature-og' => 'Legacy feature OG layout',
];

// Login throttling.
define('CMS_LOGIN_MAX_ATTEMPTS', 8);
define('CMS_LOGIN_LOCK_MINUTES', 15);

// Uploads (used by a later media phase).
define('CMS_UPLOAD_DIR', CMS_APP_ROOT . '/img/cms');
define('CMS_UPLOAD_URL', '/img/cms');
$GLOBALS['CMS_UPLOAD_ALLOWED_EXT'] = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
$GLOBALS['CMS_UPLOAD_MAX_BYTES'] = 5 * 1024 * 1024;

// Session cookie name for the admin area (kept separate from the public site).
define('CMS_SESSION_NAME', 'AHCMSSESS');

// Google OAuth login configuration for /admin.
define('CMS_OAUTH_ALLOWED_DOMAIN', strtolower((string)env('CMS_OAUTH_ALLOWED_DOMAIN', 'infinixmedia.com')));
define('CMS_OAUTH_DEFAULT_ROLE', (string)env('CMS_OAUTH_DEFAULT_ROLE', 'marketer'));
define('CMS_OAUTH_GOOGLE_CLIENT_ID', (string)env('GOOGLE_OAUTH_CLIENT_ID', ''));
define('CMS_OAUTH_GOOGLE_CLIENT_SECRET', (string)env('GOOGLE_OAUTH_CLIENT_SECRET', ''));
define(
    'CMS_OAUTH_GOOGLE_REDIRECT_URI',
    (string)env('GOOGLE_OAUTH_REDIRECT_URI', rtrim((string)env('APP_URL', 'http://localhost'), '/') . CMS_ADMIN_PATH . '/oauth-google-callback.php')
);

$GLOBALS['CMS_ROLE_EMAIL_MAP'] = [];
$adminEmail = strtolower(trim((string)env('CMS_ROLE_ADMIN_EMAIL', 'cruby@infinixmedia.com')));
if ($adminEmail !== '') {
    $GLOBALS['CMS_ROLE_EMAIL_MAP'][$adminEmail] = 'admin';
}
foreach (env_array('CMS_ROLE_REVIEWER_EMAILS', []) as $email) {
    $email = strtolower(trim((string)$email));
    if ($email !== '') {
        $GLOBALS['CMS_ROLE_EMAIL_MAP'][$email] = 'reviewer';
    }
}
foreach (env_array('CMS_ROLE_MARKETER_EMAILS', []) as $email) {
    $email = strtolower(trim((string)$email));
    if ($email !== '') {
        $GLOBALS['CMS_ROLE_EMAIL_MAP'][$email] = 'marketer';
    }
}
