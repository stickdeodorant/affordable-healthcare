<?php
require_once __DIR__ . '/classes/SessionManager.php';
require_once __DIR__ . '/classes/SecurityHelper.php';
require_once __DIR__ . '/config/app.php';

SessionManager::init();
$config = AppConfig::getInstance();

header('Content-Type: application/json');

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Validate CSRF token
if (!SecurityHelper::validateCSRFToken($_POST['csrf_token'] ?? '')) {
    SecurityHelper::logSecurityEvent('Invalid CSRF token in zip submission');
    echo json_encode(['success' => false, 'error' => 'Invalid security token']);
    exit;
}

// Sanitize and validate zip code
$zip = SecurityHelper::sanitize($_POST['zip'] ?? '', 'zip');
if (!SecurityHelper::validate($zip, 'zip')) {
    echo json_encode(['success' => false, 'error' => 'Invalid zip code format']);
    exit;
}

// Fetch location data from API
$apiUrl = $config->get('api.zip_api') . $zip;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$response) {
    echo json_encode(['success' => false, 'error' => 'Unable to verify zip code']);
    exit;
}

$data = json_decode($response, true);

if (isset($data['error']) || empty($data)) {
    echo json_encode(['success' => false, 'error' => 'Invalid zip code']);
    exit;
}

// Check for excluded states
if ($config->isStateExcluded($data['state_short'] ?? '')) {
    echo json_encode([
        'success' => false,
        'error' => 'restricted_state',
        'state' => $data['state'] ?? '',
        'state_abbr' => $data['state_short'] ?? '',
        'message' => "Unfortunately, we do not offer coverage in {$data['state']} at this time."
    ]);
    exit;
}

// Format city name properly
$city = ucwords(strtolower($data['city'] ?? ''));

// Store location data in session
SessionManager::setLocation($city, $data['state_short'], $zip);

// Also store in form data
SessionManager::setFormData('Zip', $zip);
SessionManager::setFormData('City', $city);
SessionManager::setFormData('State', $data['state_short']);
SessionManager::setFormData('state_name', $data['state']);

// Set initial step if needed
if (SessionManager::getCurrentStep() < 1) {
    SessionManager::setCurrentStep(1);
}

echo json_encode([
    'success' => true,
    'data' => [
        'zip' => $zip,
        'city' => $city,
        'state' => $data['state_short'],
        'state_name' => $data['state']
    ]
]);
?>