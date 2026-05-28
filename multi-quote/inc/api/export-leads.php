<?php
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

$date = $_GET['date'] ?? 'today';
$filename = "leads_export_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Headers
fputcsv($output, [
    'Lead ID', 'Date/Time', 'Email', 'Phone', 'First Name', 'Last Name',
    'City', 'State', 'ZIP', 'Status', 'Boberdoo ID', 'Response Time'
]);

// Query
$whereClause = "WHERE DATE(submission_timestamp) = CURDATE()";
if ($date === 'yesterday') {
    $whereClause = "WHERE DATE(submission_timestamp) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
} elseif ($date === 'week') {
    $whereClause = "WHERE submission_timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
}

$query = "
    SELECT lead_id, submission_timestamp, email, phone, first_name, last_name,
           city, state, zip, boberdoo_status, boberdoo_lead_id, response_time_ms
    FROM lead_history
    $whereClause
    ORDER BY submission_timestamp DESC
";

$result = $mysqli->query($query);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['lead_id'],
        $row['submission_timestamp'],
        $row['email'],
        $row['phone'],
        $row['first_name'],
        $row['last_name'],
        $row['city'],
        $row['state'],
        $row['zip'],
        $row['boberdoo_status'],
        $row['boberdoo_lead_id'],
        $row['response_time_ms'] ? $row['response_time_ms'] . 'ms' : ''
    ]);
}

fclose($output);
$mysqli->close();
exit;
?>