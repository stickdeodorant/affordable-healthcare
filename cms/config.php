<?php
/**
 * CMS configuration & constants.
 * Foundations layer — no output, safe to include anywhere.
 */

if (defined('CMS_ROOT')) {
    return;
}

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
