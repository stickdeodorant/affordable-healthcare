<?php
/**
 * GET-BUFFER.PHP - Buffer Data API
 * Returns leads currently in the 7-day buffer
 */

session_start();
header('Content-Type: application/json');

// Include database configuration
require_once dirname(__DIR__) . '/config/db-config.php';

// Get database connection
$mysqli = getDBConnection();

if (!$mysqli) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed']));
}

// Get filter parameters from GET request
$status = isset($_GET['status']) ? $_GET['status'] : '';
$dateRange = isset($_GET['dateRange']) ? $_GET['dateRange'] : 'all';
$resubmits = isset($_GET['resubmits']) ? $_GET['resubmits'] : '';

// Build query
$query = "SELECT * FROM lead_buffer WHERE 1=1";
$params = [];
$types = "";

// Filter by expiration (only show non-expired by default)
$query .= " AND (expires_at > NOW() OR expires_at IS NULL)";

// Filter by status
if ($status) {
    $query .= " AND boberdoo_status = ?";
    $params[] = $status;
    $types .= "s";
}

// Filter by resubmit count
if ($resubmits !== '') {
    switch($resubmits) {
        case '0':
            $query .= " AND resubmit_count = 0";
            break;
        case '1':
            $query .= " AND resubmit_count = 1";
            break;
        case '2':
            $query .= " AND resubmit_count = 2";
            break;
        case '3+':
            $query .= " AND resubmit_count >= 3";
            break;
    }
}

// Filter by date range
if ($dateRange !== 'all') {
    switch($dateRange) {
        case 'today':
            $query .= " AND DATE(created_at) = CURDATE()";
            break;
        case 'yesterday':
            $query .= " AND DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
            break;
        case '3days':
            $query .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY)";
            break;
        case '7days':
            $query .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
    }
}

// Sort by created date descending
$query .= " ORDER BY created_at DESC";

// Execute query
if ($params) {
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        die(json_encode(['success' => false, 'error' => 'Query preparation failed']));
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $mysqli->query($query);
    if (!$result) {
        die(json_encode(['success' => false, 'error' => 'Query execution failed']));
    }
}

// Fetch data
$data = [];
while ($row = $result->fetch_assoc()) {
    // Add any computed fields
    $row['full_name'] = trim($row['first_name'] . ' ' . $row['last_name']);
    
    // Ensure all expected fields are present
    $row['lead_id'] = $row['lead_id'] ?? '';
    $row['email'] = $row['email'] ?? '';
    $row['phone'] = $row['phone'] ?? $row['primary_phone'] ?? '';
    $row['first_name'] = $row['first_name'] ?? '';
    $row['last_name'] = $row['last_name'] ?? '';
    $row['city'] = $row['city'] ?? '';
    $row['state'] = $row['state'] ?? '';
    $row['zip'] = $row['zip'] ?? '';
    $row['boberdoo_status'] = $row['boberdoo_status'] ?? 'pending';
    $row['resubmit_count'] = $row['resubmit_count'] ?? 0;
    $row['created_at'] = $row['created_at'] ?? date('Y-m-d H:i:s');
    $row['expires_at'] = $row['expires_at'] ?? date('Y-m-d H:i:s', strtotime('+7 days'));
    
    $data[] = $row;
}

// Clean up
if (isset($stmt)) {
    $stmt->close();
}
$mysqli->close();

// Return success response
echo json_encode([
    'success' => true,
    'data' => $data,
    'count' => count($data),
    'timestamp' => date('Y-m-d H:i:s')
]);
?>