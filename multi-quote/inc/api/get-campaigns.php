<?php
/**
 * API Endpoint: get-campaigns.php
 * Campaign performance analysis and metrics
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
    die(json_encode(['error' => 'Database connection failed']));
}

$mysqli->set_charset("utf8mb4");

// Get parameters
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;

// Validate dates
$startDate = date('Y-m-d', strtotime($startDate));
$endDate = date('Y-m-d', strtotime($endDate));

$response = [
    'success' => true,
    'data' => []
];

try {
    // Get campaign performance data
    $query = "
        SELECT 
            COALESCE(campaign, 'Unknown') as campaign,
            COUNT(*) as total_leads,
            SUM(CASE WHEN boberdoo_status = 'success' THEN 1 ELSE 0 END) as successful,
            SUM(CASE WHEN boberdoo_status IN ('error', 'rejected', 'failed') THEN 1 ELSE 0 END) as failed,
            SUM(CASE WHEN boberdoo_status = 'pending' THEN 1 ELSE 0 END) as pending,
            ROUND((SUM(CASE WHEN boberdoo_status = 'success' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as success_rate,
            AVG(CASE WHEN response_time_ms > 0 THEN response_time_ms ELSE NULL END) as avg_response_time,
            SUM(boberdoo_price) as total_revenue,
            AVG(boberdoo_price) as avg_price_per_lead,
            MIN(submission_timestamp) as first_submission,
            MAX(submission_timestamp) as last_submission,
            source,
            COUNT(DISTINCT state) as states_count
        FROM lead_history
        WHERE DATE(submission_timestamp) BETWEEN ? AND ?
        GROUP BY campaign, source
        ORDER BY total_leads DESC
        LIMIT ?
    ";
    
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssi", $startDate, $endDate, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $campaigns = [];
    while ($row = $result->fetch_assoc()) {
        $campaigns[] = [
            'campaign' => $row['campaign'],
            'source' => $row['source'],
            'total_leads' => (int)$row['total_leads'],
            'successful' => (int)$row['successful'],
            'failed' => (int)$row['failed'],
            'pending' => (int)$row['pending'],
            'success_rate' => (float)$row['success_rate'],
            'avg_response_time' => round($row['avg_response_time'] ?? 0, 0),
            'total_revenue' => round($row['total_revenue'] ?? 0, 2),
            'avg_price_per_lead' => round($row['avg_price_per_lead'] ?? 0, 2),
            'first_submission' => $row['first_submission'],
            'last_submission' => $row['last_submission'],
            'states_count' => (int)$row['states_count']
        ];
    }
    $stmt->close();
    
    // Get overall summary
    $summaryQuery = "
        SELECT 
            COUNT(DISTINCT campaign) as total_campaigns,
            COUNT(DISTINCT source) as total_sources,
            SUM(CASE WHEN boberdoo_status = 'success' THEN 1 ELSE 0 END) as total_successful,
            COUNT(*) as total_leads
        FROM lead_history
        WHERE DATE(submission_timestamp) BETWEEN ? AND ?
    ";
    
    $stmt = $mysqli->prepare($summaryQuery);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $summaryResult = $stmt->get_result();
    $summary = $summaryResult->fetch_assoc();
    $stmt->close();
    
    $response['data'] = [
        'campaigns' => $campaigns,
        'summary' => [
            'total_campaigns' => (int)$summary['total_campaigns'],
            'total_sources' => (int)$summary['total_sources'],
            'total_leads' => (int)$summary['total_leads'],
            'total_successful' => (int)$summary['total_successful'],
            'overall_success_rate' => $summary['total_leads'] > 0 
                ? round(($summary['total_successful'] / $summary['total_leads']) * 100, 2) 
                : 0
        ],
        'period' => [
            'start' => $startDate,
            'end' => $endDate
        ]
    ];
    
} catch (Exception $e) {
    error_log("Campaign analytics error: " . $e->getMessage());
    $response = [
        'success' => false,
        'error' => 'Failed to retrieve campaign data: ' . $e->getMessage()
    ];
}

$mysqli->close();

echo json_encode($response);
?>