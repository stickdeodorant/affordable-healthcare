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

$user = $_GET['user'] ?? '';
$type = $_GET['type'] ?? '';
$date = $_GET['date'] ?? '';
$groupBy = $_GET['group_by'] ?? '';
$criticalOnly = isset($_GET['critical_only']) ? filter_var($_GET['critical_only'], FILTER_VALIDATE_BOOLEAN) : false;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;

// Handle group_by parameter
if ($groupBy === 'user') {
    $query = "SELECT 
        admin_user as user,
        COUNT(*) as total,
        SUM(CASE WHEN action_type = 'create' THEN 1 ELSE 0 END) as creates,
        SUM(CASE WHEN action_type = 'update' THEN 1 ELSE 0 END) as updates,
        SUM(CASE WHEN action_type = 'delete' THEN 1 ELSE 0 END) as deletes,
        MAX(created_at) as last_activity
    FROM admin_activity_log
    GROUP BY admin_user
    ORDER BY total DESC";
    
    $result = $mysqli->query($query);
    $userStats = [];
    while ($row = $result->fetch_assoc()) {
        $userStats[] = [
            'user' => $row['user'],
            'total' => (int)$row['total'],
            'creates' => (int)$row['creates'],
            'updates' => (int)$row['updates'],
            'deletes' => (int)$row['deletes'],
            'last_activity' => $row['last_activity']
        ];
    }
    
    $mysqli->close();
    echo json_encode(['success' => true, 'user_stats' => $userStats]);
    exit;
}

$query = "SELECT 
    DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') as timestamp,
    admin_user as user,
    action_type as action,
    target_type as target,
    action_details as details,
    ip_address,
    COALESCE(status, 'success') as status
FROM admin_activity_log WHERE 1=1";

$params = [];
$types = "";

if ($user) {
    $query .= " AND admin_user = ?";
    $params[] = $user;
    $types .= "s";
}

if ($type) {
    $query .= " AND action_type = ?";
    $params[] = $type;
    $types .= "s";
}

if ($date) {
    $query .= " AND DATE(created_at) = ?";
    $params[] = $date;
    $types .= "s";
}

// Add critical actions filter if requested
if ($criticalOnly) {
    $query .= " AND action_type IN ('delete', 'blacklist', 'config', 'export')";
}

$query .= " ORDER BY created_at DESC LIMIT " . $limit;

if ($params) {
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $mysqli->query($query);
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

if (isset($stmt)) {
    $stmt->close();
}
$mysqli->close();

echo json_encode(['success' => true, 'data' => $data]);
?>