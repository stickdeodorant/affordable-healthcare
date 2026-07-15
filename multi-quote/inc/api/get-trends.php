<?php
/**
 * GET-TRENDS.PHP - Trends Data API
 * Location: /multi-quote/inc/api/get-trends.php
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

// Initialize trends array for last 7 days
$trends = [];
$days = 7;

// Generate dates for last 7 days
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dayName = date('D', strtotime("-$i days"));
    
    // Initialize day data
    $trends[] = [
        'date' => $date,
        'day' => $dayName,
        'label' => ($i == 0) ? 'Today' : (($i == 1) ? 'Yesterday' : $dayName),
        'successful' => 0,
        'failed' => 0,
        'pending' => 0,
        'total' => 0
    ];
}

// Query lead_buffer for recent data
$query = "
    SELECT 
        DATE(created_at) as date,
        boberdoo_status,
        COUNT(*) as count
    FROM lead_buffer
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at), boberdoo_status
    ORDER BY date ASC
";

$result = $mysqli->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Find the corresponding day in trends array
        foreach ($trends as &$day) {
            if ($day['date'] == $row['date']) {
                $status = strtolower($row['boberdoo_status']);
                
                if ($status == 'success') {
                    $day['successful'] += $row['count'];
                } elseif ($status == 'error' || $status == 'failed') {
                    $day['failed'] += $row['count'];
                } elseif ($status == 'pending') {
                    $day['pending'] += $row['count'];
                }
                
                $day['total'] += $row['count'];
                break;
            }
        }
    }
}

// Also check lead_history for completed leads
$query = "
    SELECT 
        DATE(submission_timestamp) as date,
        boberdoo_status,
        COUNT(*) as count
    FROM lead_history
    WHERE submission_timestamp >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(submission_timestamp), boberdoo_status
    ORDER BY date ASC
";

$result = $mysqli->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        foreach ($trends as &$day) {
            if ($day['date'] == $row['date']) {
                $status = strtolower($row['boberdoo_status']);
                
                if ($status == 'success') {
                    $day['successful'] += $row['count'];
                } elseif ($status == 'error' || $status == 'failed') {
                    $day['failed'] += $row['count'];
                }
                
                $day['total'] += $row['count'];
                break;
            }
        }
    }
}

$mysqli->close();

// Return the trends
echo json_encode([
    'success' => true,
    'data' => $trends,
    'days' => $days,
    'timestamp' => date('Y-m-d H:i:s')
]);
?>