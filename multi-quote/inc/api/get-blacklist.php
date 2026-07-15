<?php
session_start();
if (!isset($_SESSION['admin_authenticated']) || $_SESSION['admin_authenticated'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

// Include database configuration
require_once __DIR__ . '/../config/db-config.php';

// Get database connection
$mysqli = getDBConnection();

if (!$mysqli) {
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Database connection failed']));
}

// Set response header if not already set
if (!headers_sent()) {
    header('Content-Type: application/json');
}

// Get statistics
$statsQuery = "
    SELECT 
        COUNT(*) as total,
        SUM(is_permanent) as permanent,
        SUM(NOT is_permanent AND block_until > NOW()) as temporary,
        SUM(DATE(last_attempt_date) = CURDATE()) as today
    FROM email_blacklist
";

$statsResult = $mysqli->query($statsQuery);
$stats = $statsResult->fetch_assoc();

// Ensure no null values
$stats['permanent'] = $stats['permanent'] ?? 0;
$stats['temporary'] = $stats['temporary'] ?? 0;
$stats['today'] = $stats['today'] ?? 0;

// Get blacklist data
$dataQuery = "
    SELECT 
        email,
        phone,
        CASE 
            WHEN is_permanent THEN 'Permanent'
            WHEN block_until > NOW() THEN 'Temporary'
            ELSE 'Expired'
        END as status,
        submission_count as attempts,
        DATE_FORMAT(first_blacklisted_date, '%Y-%m-%d') as first_blocked,
        DATE_FORMAT(last_attempt_date, '%Y-%m-%d %H:%i') as last_attempt,
        DATE_FORMAT(block_until, '%Y-%m-%d %H:%i') as block_until,
        blacklist_reason as reason,
        is_permanent
    FROM email_blacklist
    ORDER BY last_attempt_date DESC
";

$dataResult = $mysqli->query($dataQuery);
$data = [];

while ($row = $dataResult->fetch_assoc()) {
    $data[] = $row;
}

$mysqli->close();

echo json_encode([
    'success' => true,
    'stats' => $stats,
    'data' => $data
]);
?>