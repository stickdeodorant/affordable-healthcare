<?php
/**
 * CMS bootstrap. Include this from any CMS/admin entry point:
 *   require_once __DIR__ . '/../cms/bootstrap.php';
 *
 * Sets up a hardened admin session and loads the foundation libraries.
 * Produces no output.
 */

require_once __DIR__ . '/config.php';
require_once CMS_LIB . '/db.php';
require_once CMS_LIB . '/sanitize.php';
require_once CMS_LIB . '/csrf.php';
require_once CMS_LIB . '/auth.php';
require_once CMS_LIB . '/audit.php';

if (session_status() === PHP_SESSION_NONE) {
    $secure = function_exists('is_production') ? is_production() : false;
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name(CMS_SESSION_NAME);
    session_start();
}
