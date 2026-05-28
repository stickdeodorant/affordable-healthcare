<?php
require_once __DIR__ . '/classes/SessionManager.php';
require_once __DIR__ . '/classes/SecurityHelper.php';

SessionManager::init();
header('Content-Type: application/json');

echo json_encode([
    'session_id' => session_id(),
    'session_name' => session_name(),
    'session_data' => $_SESSION,
    'csrf_token' => SecurityHelper::getCSRFToken(),
    'cookies' => $_COOKIE,
    'session_cookie_params' => session_get_cookie_params(),
    'server_info' => [
        'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? '',
        'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? '',
        'HTTPS' => isset($_SERVER['HTTPS']) ? 'yes' : 'no'
    ]
], JSON_PRETTY_PRINT);
?>