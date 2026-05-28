<?php
session_start();
if (!isset($_SESSION['admin_authenticated'])) {
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

$query = "
    SELECT 
        COUNT(*) as total,
        AVG(response_time_ms) as avgResponseTime,
        SUM(CASE WHEN boberdoo_status = 'success' THEN 1 ELSE 0 END) as successful,
        SUM(boberdoo_price) as totalRevenue
    FROM lead_history
";

$result = $mysqli->query($query);
$data = $result->fetch_assoc();

// Calculate conversion rate
$conversionRate = 0;
if ($data['total'] > 0) {
    $conversionRate = round(($data['successful'] / $data['total']) * 100, 2);
}

$data['avgResponseTime'] = round($data['avgResponseTime'] ?? 0, 0);
$data['totalRevenue'] = round($data['totalRevenue'] ?? 0, 2);
$data['conversionRate'] = $conversionRate;

$mysqli->close();

echo json_encode(['success' => true, 'data' => $data]);
?>
