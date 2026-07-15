<?php
/**
 * GET-STATS.PHP - Dashboard Statistics API
 * Location: /multi-quote/inc/api/get-stats.php
 */

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_authenticated']) || $_SESSION['admin_authenticated'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Include database configuration
require_once __DIR__ . '/../config/db-config.php';

// Get database connection
$mysqli = getDBConnection();

if (!$mysqli) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed']));
}

// Initialize stats array
$stats = [
    'total' => 0,
    'total_today' => 0,
    'successful' => 0,
    'failed' => 0,
    'pending' => 0,
    'error' => 0,
    'blacklisted' => 0,
    'total_buffer' => 0,
    'total_history' => 0,
    'resubmission_queue' => 0
];

// Get today's total from lead_buffer
$today = date('Y-m-d');
$result = $mysqli->query("
    SELECT COUNT(*) as count 
    FROM lead_buffer 
    WHERE DATE(created_at) = '$today'
");
if ($result) {
    $stats['total_today'] = $result->fetch_assoc()['count'];
}

// Get total leads in buffer
$result = $mysqli->query("SELECT COUNT(*) as count FROM lead_buffer");
if ($result) {
    $stats['total_buffer'] = $result->fetch_assoc()['count'];
    $stats['total'] = $stats['total_buffer']; // Use buffer count as total for dashboard
}

// Get status counts from lead_buffer
$result = $mysqli->query("
    SELECT 
        boberdoo_status,
        COUNT(*) as count 
    FROM lead_buffer 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY boberdoo_status
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $status = strtolower($row['boberdoo_status']);
        if ($status == 'success') {
            $stats['successful'] = $row['count'];
        } elseif ($status == 'error' || $status == 'failed') {
            $stats['failed'] += $row['count'];
            $stats['error'] += $row['count'];
        } elseif ($status == 'pending') {
            $stats['pending'] = $row['count'];
        }
    }
}

// Get total from history
$result = $mysqli->query("SELECT COUNT(*) as count FROM lead_history");
if ($result) {
    $stats['total_history'] = $result->fetch_assoc()['count'];
}

// Get blacklisted count
$result = $mysqli->query("SELECT COUNT(*) as count FROM email_blacklist");
if ($result) {
    $stats['blacklisted'] = $result->fetch_assoc()['count'];
}

// Get resubmission queue count
$result = $mysqli->query("
    SELECT COUNT(*) as count 
    FROM resubmission_queue 
    WHERE status IN ('pending', 'scheduled')
");
if ($result) {
    $stats['resubmission_queue'] = $result->fetch_assoc()['count'];
}

// Check if dashboard_stats table exists and update it
$tableCheck = $mysqli->query("SHOW TABLES LIKE 'dashboard_stats'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    // Update dashboard stats for caching
    foreach ($stats as $key => $value) {
        $mysqli->query("
            INSERT INTO dashboard_stats (stat_name, stat_value, updated_at) 
            VALUES ('$key', '$value', NOW())
            ON DUPLICATE KEY UPDATE 
                stat_value = '$value', 
                updated_at = NOW()
        ");
    }
}

$mysqli->close();

// Return the stats
echo json_encode([
    'success' => true,
    'data' => $stats,
    'timestamp' => date('Y-m-d H:i:s')
]);
?>