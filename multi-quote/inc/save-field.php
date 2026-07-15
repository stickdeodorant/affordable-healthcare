<?php
require_once __DIR__ . '/classes/SessionManager.php';
require_once __DIR__ . '/classes/SecurityHelper.php';

SessionManager::init();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed',
    ]);
    exit;
}

if (!SecurityHelper::validateCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid security token',
    ]);
    exit;
}

$field = trim((string)($_POST['field'] ?? ''));
$value = $_POST['value'] ?? null;

$allowedFields = ['Household'];
if (!in_array($field, $allowedFields, true)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Field not allowed',
    ]);
    exit;
}

if ($field === 'Household') {
    $value = (string)intval($value);
    if (!in_array($value, ['1', '2', '3', '4', '5', '6'], true)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid household value',
        ]);
        exit;
    }
}

SessionManager::setFormData($field, $value);

echo json_encode([
    'success' => true,
    'field' => $field,
    'value' => $value,
]);
