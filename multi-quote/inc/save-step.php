<?php
require_once __DIR__ . '/classes/SessionManager.php';
require_once __DIR__ . '/classes/SecurityHelper.php';

SessionManager::init();
header('Content-Type: application/json');

// Detailed debugging
$debugInfo = [
    'session_id' => session_id(),
    'session_name' => session_name(),
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'post_data' => $_POST,
    'session_csrf_token' => $_SESSION['csrf_token'] ?? 'NOT SET',
    'submitted_csrf_token' => $_POST['csrf_token'] ?? 'NOT SET',
    'session_data' => $_SESSION,
    'timestamp' => time(),
    'last_regeneration' => $_SESSION['last_regeneration'] ?? 'NOT SET'
];

error_log('Save-step debug: ' . json_encode($debugInfo));

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false, 
        'error' => 'Method not allowed',
        'debug' => $debugInfo
    ]);
    exit;
}

// Get tokens for comparison
$sessionToken = $_SESSION['csrf_token'] ?? '';
$submittedToken = $_POST['csrf_token'] ?? '';

// Detailed CSRF token analysis
$csrfDebug = [
    'session_token_exists' => !empty($sessionToken),
    'submitted_token_exists' => !empty($submittedToken),
    'session_token_length' => strlen($sessionToken),
    'submitted_token_length' => strlen($submittedToken),
    'session_token_preview' => substr($sessionToken, 0, 10) . '...',
    'submitted_token_preview' => substr($submittedToken, 0, 10) . '...',
    'tokens_identical' => ($sessionToken === $submittedToken),
    'hash_equals_result' => !empty($sessionToken) && !empty($submittedToken) ? hash_equals($sessionToken, $submittedToken) : false
];

if (empty($submittedToken)) {
    echo json_encode([
        'success' => false,
        'error' => 'CSRF token required',
        'debug' => array_merge($debugInfo, ['csrf_debug' => $csrfDebug])
    ]);
    exit;
}

if (empty($sessionToken)) {
    echo json_encode([
        'success' => false,
        'error' => 'No session CSRF token found',
        'debug' => array_merge($debugInfo, ['csrf_debug' => $csrfDebug])
    ]);
    exit;
}

// Validate CSRF token
if (!hash_equals($sessionToken, $submittedToken)) {
    echo json_encode([
        'success' => false,
        'error' => 'CSRF token mismatch',
        'debug' => array_merge($debugInfo, ['csrf_debug' => $csrfDebug])
    ]);
    exit;
}

// Validate and sanitize step parameter
$step = $_POST['step'] ?? null;

if ($step === null) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'error' => 'Step parameter is required',
        'debug' => $debugInfo
    ]);
    exit;
}

// Validate step is numeric and within valid range
$step = intval($step);
if ($step < 0 || $step > 7) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'error' => 'Invalid step number',
        'debug' => $debugInfo
    ]);
    exit;
}

try {
    // Save the step using SessionManager
    SessionManager::setCurrentStep($step);
    
    // Return success response with debug info
    echo json_encode([
        'success' => true,
        'step' => $step,
        'message' => 'Step saved successfully',
        'debug' => array_merge($debugInfo, ['csrf_debug' => $csrfDebug])
    ]);
    
} catch (Exception $e) {
    // Log the error
    error_log("Error saving step: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Unable to save step: ' . $e->getMessage(),
        'debug' => $debugInfo
    ]);
}
?>