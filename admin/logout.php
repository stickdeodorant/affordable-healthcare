<?php
/**
 * Admin logout. Clears the CMS session and returns to the login page.
 */

require_once __DIR__ . '/../cms/bootstrap.php';

cms_logout();
header('Location: ' . CMS_ADMIN_PATH . '/login.php');
exit;
