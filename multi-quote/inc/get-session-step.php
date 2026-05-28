<?php
require_once __DIR__ . '/classes/SessionManager.php';
require_once __DIR__ . '/classes/SecurityHelper.php';

SessionManager::init();
header('Content-Type: application/json');

// Check if zip code exists in session
$hasZip = !empty(SessionManager::getZip());
$currentStep = SessionManager::getCurrentStep();

// Get location data
$locationData = [
    'zip' => SessionManager::getZip(),
    'city' => SessionManager::getCity(),
    'state' => SessionManager::getState(),
    'hasZip' => $hasZip
];

// Get all form data
$formData = SessionManager::getFormData();

echo json_encode([
    'success' => true,
    'hasZip' => $hasZip,
    'needsZipStep' => !$hasZip,
    'currentStep' => $currentStep,
    'locationData' => $locationData,
    'formData' => $formData
]);
?>