<?php
require_once __DIR__ . '/classes/SessionManager.php';
require_once __DIR__ . '/classes/SecurityHelper.php';

SessionManager::init();
header('Content-Type: application/json');

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false, 
        'error' => 'Method not allowed'
    ]);
    exit;
}

// Get tokens for comparison
$sessionToken = $_SESSION['csrf_token'] ?? '';
$submittedToken = $_POST['csrf_token'] ?? '';

if (empty($submittedToken)) {
    echo json_encode([
        'success' => false,
        'error' => 'CSRF token required'
    ]);
    exit;
}

if (empty($sessionToken)) {
    echo json_encode([
        'success' => false,
        'error' => 'No session CSRF token found'
    ]);
    exit;
}

// Validate CSRF token
if (!hash_equals($sessionToken, $submittedToken)) {
    echo json_encode([
        'success' => false,
        'error' => 'CSRF token mismatch'
    ]);
    exit;
}

// Validate and sanitize step parameter
$step = $_POST['step'] ?? null;

if ($step === null) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'error' => 'Step parameter is required'
    ]);
    exit;
}

// Validate step is numeric and within valid range
$step = intval($step);
if ($step < 0 || $step > 7) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'error' => 'Invalid step number'
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
        'message' => 'Step saved successfully'
    ]);
    
} catch (Exception $e) {
    // Log the error
    error_log("Error saving step: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Unable to save step'
    ]);
}
?>