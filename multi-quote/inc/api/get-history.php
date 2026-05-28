<?php
/**
 * API Endpoint: get-history.php
 * Returns historical lead data for DataTable with server-side processing
 */

session_start();

// Check authentication
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

$mysqli->set_charset("utf8mb4");

// DataTables parameters
$draw = intval($_POST['draw'] ?? 1);
$start = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 10);
$searchValue = $_POST['search']['value'] ?? '';
$orderColumn = intval($_POST['order'][0]['column'] ?? 1);
$orderDirection = $_POST['order'][0]['dir'] ?? 'desc';

// Column mapping for ordering
$columns = [
    'lead_id',
    'submission_timestamp',
    'CONCAT(first_name, " ", last_name)',
    'email',
    'phone',
    'state',
    'campaign',
    'boberdoo_status',
    'response_time_ms',
    'id'
];

$orderBy = $columns[$orderColumn] ?? 'submission_timestamp';

// Build WHERE clause for search
$whereClause = "";
$params = [];
$types = "";

if (!empty($searchValue)) {
    $whereClause = "WHERE (
        lead_id LIKE ? OR
        email LIKE ? OR
        phone LIKE ? OR
        first_name LIKE ? OR
        last_name LIKE ? OR
        state LIKE ? OR
        campaign LIKE ?
    )";
    $searchPattern = "%{$searchValue}%";
    $params = array_fill(0, 7, $searchPattern);
    $types = str_repeat("s", 7);
}

// Get total count
$totalQuery = "SELECT COUNT(*) as total FROM lead_history";
$totalResult = $mysqli->query($totalQuery);
$totalRecords = $totalResult->fetch_assoc()['total'];

// Get filtered count
if (!empty($whereClause)) {
    $filteredQuery = "SELECT COUNT(*) as total FROM lead_history $whereClause";
    $stmt = $mysqli->prepare($filteredQuery);
    if ($stmt && !empty($params)) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $filteredRecords = $result->fetch_assoc()['total'];
        $stmt->close();
    } else {
        $filteredRecords = $totalRecords;
    }
} else {
    $filteredRecords = $totalRecords;
}

// Get paginated data
$dataQuery = "
    SELECT 
        id,
        lead_id,
        DATE_FORMAT(submission_timestamp, '%Y-%m-%d %H:%i') as timestamp,
        CONCAT(first_name, ' ', last_name) as name,
        email,
        phone,
        state,
        campaign,
        boberdoo_status as status,
        response_time_ms as response_time,
        city,
        zip,
        source,
        ip_address,
        boberdoo_lead_id,
        boberdoo_error_message
    FROM lead_history
    $whereClause
    ORDER BY $orderBy $orderDirection
    LIMIT ? OFFSET ?
";

$stmt = $mysqli->prepare($dataQuery);

if ($stmt) {
    if (!empty($params)) {
        $params[] = $length;
        $params[] = $start;
        $types .= "ii";
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt->bind_param("ii", $length, $start);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        // Format response time
        if ($row['response_time']) {
            $row['response_time'] = $row['response_time'] . 'ms';
        } else {
            $row['response_time'] = 'N/A';
        }
        
        // Ensure all required fields exist
        $row['campaign'] = $row['campaign'] ?? 'N/A';
        $row['state'] = $row['state'] ?? 'N/A';
        
        $data[] = $row;
    }
    
    $stmt->close();
} else {
    $data = [];
}

$mysqli->close();

// Return DataTables formatted response
echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $totalRecords,
    'recordsFiltered' => $filteredRecords,
    'data' => $data
]);
?>