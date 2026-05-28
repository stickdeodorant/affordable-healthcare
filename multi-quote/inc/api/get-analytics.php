<?php
/**
 * API Endpoint: get-analytics.php
 * Comprehensive analytics with date ranges, grouping, and detailed breakdowns
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
$startDate = $_POST['start_date'] ?? $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
$endDate = $_POST['end_date'] ?? $_GET['end_date'] ?? date('Y-m-d');
$groupBy = $_POST['group_by'] ?? $_GET['group_by'] ?? 'day';

// Validate dates
$startDate = date('Y-m-d', strtotime($startDate));
$endDate = date('Y-m-d', strtotime($endDate));

// Initialize response data
$response = [
    'success' => true,
    'data' => [
        'summary' => [],
        'volume_trend' => [],
        'status_breakdown' => [],
        'success_rate_trend' => [],
        'response_time_trend' => [],
        'top_states' => [],
        'top_campaigns' => [],
        'hourly_distribution' => [],
        'detailed_stats' => []
    ]
];

try {
    // === SUMMARY STATISTICS ===
    $summaryQuery = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN boberdoo_status = 'success' THEN 1 ELSE 0 END) as successful,
            SUM(CASE WHEN boberdoo_status IN ('error', 'rejected', 'failed') THEN 1 ELSE 0 END) as failed,
            SUM(CASE WHEN boberdoo_status = 'pending' THEN 1 ELSE 0 END) as pending,
            AVG(CASE WHEN response_time_ms > 0 THEN response_time_ms ELSE NULL END) as avg_response_time,
            SUM(boberdoo_price) as total_revenue
        FROM lead_history
        WHERE DATE(submission_timestamp) BETWEEN ? AND ?
    ";
    
    $stmt = $mysqli->prepare($summaryQuery);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $summaryResult = $stmt->get_result();
    $summary = $summaryResult->fetch_assoc();
    $stmt->close();
    
    // Calculate success rate
    $successRate = $summary['total'] > 0 
        ? round(($summary['successful'] / $summary['total']) * 100, 2) 
        : 0;
    
    // Get previous period for comparison
    $daysDiff = (strtotime($endDate) - strtotime($startDate)) / 86400;
    $prevStartDate = date('Y-m-d', strtotime($startDate . " -" . ceil($daysDiff) . " days"));
    $prevEndDate = date('Y-m-d', strtotime($startDate . " -1 day"));
    
    $prevQuery = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN boberdoo_status = 'success' THEN 1 ELSE 0 END) as successful,
            AVG(CASE WHEN response_time_ms > 0 THEN response_time_ms ELSE NULL END) as avg_response_time,
            SUM(boberdoo_price) as total_revenue
        FROM lead_history
        WHERE DATE(submission_timestamp) BETWEEN ? AND ?
    ";
    
    $stmt = $mysqli->prepare($prevQuery);
    $stmt->bind_param("ss", $prevStartDate, $prevEndDate);
    $stmt->execute();
    $prevResult = $stmt->get_result();
    $prevData = $prevResult->fetch_assoc();
    $stmt->close();
    
    // Calculate changes
    $volumeChange = $prevData['total'] > 0 
        ? round((($summary['total'] - $prevData['total']) / $prevData['total']) * 100, 1)
        : 0;
    
    $prevSuccessRate = $prevData['total'] > 0 
        ? ($prevData['successful'] / $prevData['total']) * 100 
        : 0;
    $rateChange = $prevSuccessRate > 0 
        ? round($successRate - $prevSuccessRate, 1)
        : 0;
    
    $timeChange = $prevData['avg_response_time'] > 0 && $summary['avg_response_time'] > 0
        ? round((($summary['avg_response_time'] - $prevData['avg_response_time']) / $prevData['avg_response_time']) * 100, 1)
        : 0;
    
    $revenueChange = $prevData['total_revenue'] > 0 && $summary['total_revenue'] > 0
        ? round((($summary['total_revenue'] - $prevData['total_revenue']) / $prevData['total_revenue']) * 100, 1)
        : 0;
    
    $response['data']['summary'] = [
        'total' => (int)$summary['total'],
        'successful' => (int)$summary['successful'],
        'failed' => (int)$summary['failed'],
        'pending' => (int)$summary['pending'],
        'success_rate' => $successRate,
        'avg_response_time' => round($summary['avg_response_time'] ?? 0, 0),
        'total_revenue' => round($summary['total_revenue'] ?? 0, 2),
        'volume_change' => $volumeChange,
        'rate_change' => $rateChange,
        'time_change' => $timeChange,
        'revenue_change' => $revenueChange
    ];
    
    // === VOLUME TREND ===
    if ($groupBy === 'week') {
        $dateFormat = '%Y-%U';
    } elseif ($groupBy === 'month') {
        $dateFormat = '%Y-%m';
    } else {
        $dateFormat = '%Y-%m-%d';
    }
    
    if ($groupBy === 'week') {
        $labelFormat = 'Week of %m/%d';
    } elseif ($groupBy === 'month') {
        $labelFormat = '%b %Y';
    } else {
        $labelFormat = '%m/%d';
    }
    
    $trendQuery = "
        SELECT 
            DATE_FORMAT(submission_timestamp, ?) as period,
            COUNT(*) as total,
            SUM(CASE WHEN boberdoo_status = 'success' THEN 1 ELSE 0 END) as successful,
            SUM(CASE WHEN boberdoo_status IN ('error', 'rejected', 'failed') THEN 1 ELSE 0 END) as failed,
            SUM(CASE WHEN boberdoo_status = 'pending' THEN 1 ELSE 0 END) as pending
        FROM lead_history
        WHERE DATE(submission_timestamp) BETWEEN ? AND ?
        GROUP BY period
        ORDER BY period ASC
    ";
    
    $stmt = $mysqli->prepare($trendQuery);
    $stmt->bind_param("sss", $dateFormat, $startDate, $endDate);
    $stmt->execute();
    $trendResult = $stmt->get_result();
    
    $trendLabels = [];
    $trendData = [];
    
    while ($row = $trendResult->fetch_assoc()) {
        // Format label
        if ($groupBy === 'week') {
            $label = 'Week ' . substr($row['period'], -2);
        } elseif ($groupBy === 'month') {
            $label = date('M Y', strtotime($row['period'] . '-01'));
        } else {
            $label = date('m/d', strtotime($row['period']));
        }
        
        $trendLabels[] = $label;
        $trendData[] = (int)$row['total'];
    }
    $stmt->close();
    
    $response['data']['volume_trend'] = [
        'labels' => $trendLabels,
        'data' => $trendData
    ];
    
    // === STATUS BREAKDOWN ===
    $response['data']['status_breakdown'] = [
        'success' => (int)$summary['successful'],
        'failed' => (int)$summary['failed'],
        'pending' => (int)$summary['pending']
    ];
    
    // === SUCCESS RATE TREND ===
    $stmt = $mysqli->prepare($trendQuery);
    $stmt->bind_param("sss", $dateFormat, $startDate, $endDate);
    $stmt->execute();
    $trendResult = $stmt->get_result();
    
    $successRateLabels = [];
    $successRateData = [];
    
    while ($row = $trendResult->fetch_assoc()) {
        if ($groupBy === 'week') {
            $label = 'Week ' . substr($row['period'], -2);
        } elseif ($groupBy === 'month') {
            $label = date('M Y', strtotime($row['period'] . '-01'));
        } else {
            $label = date('m/d', strtotime($row['period']));
        }
        
        $rate = $row['total'] > 0 
            ? round(($row['successful'] / $row['total']) * 100, 1) 
            : 0;
        
        $successRateLabels[] = $label;
        $successRateData[] = $rate;
    }
    $stmt->close();
    
    $response['data']['success_rate_trend'] = [
        'labels' => $successRateLabels,
        'data' => $successRateData
    ];
    
    // === RESPONSE TIME TREND ===
    $responseTimeQuery = "
        SELECT 
            DATE_FORMAT(submission_timestamp, ?) as period,
            AVG(CASE WHEN response_time_ms > 0 THEN response_time_ms ELSE NULL END) as avg_time
        FROM lead_history
        WHERE DATE(submission_timestamp) BETWEEN ? AND ?
        GROUP BY period
        ORDER BY period ASC
    ";
    
    $stmt = $mysqli->prepare($responseTimeQuery);
    $stmt->bind_param("sss", $dateFormat, $startDate, $endDate);
    $stmt->execute();
    $timeResult = $stmt->get_result();
    
    $timeLabels = [];
    $timeData = [];
    
    while ($row = $timeResult->fetch_assoc()) {
        if ($groupBy === 'week') {
            $label = 'Week ' . substr($row['period'], -2);
        } elseif ($groupBy === 'month') {
            $label = date('M Y', strtotime($row['period'] . '-01'));
        } else {
            $label = date('m/d', strtotime($row['period']));
        }
        
        $timeLabels[] = $label;
        $timeData[] = round($row['avg_time'] ?? 0, 0);
    }
    $stmt->close();
    
    $response['data']['response_time_trend'] = [
        'labels' => $timeLabels,
        'data' => $timeData
    ];
    
    // === TOP STATES ===
    $statesQuery = "
        SELECT 
            state,
            COUNT(*) as count
        FROM lead_history
        WHERE DATE(submission_timestamp) BETWEEN ? AND ?
        AND state IS NOT NULL AND state != ''
        GROUP BY state
        ORDER BY count DESC
        LIMIT 10
    ";
    
    $stmt = $mysqli->prepare($statesQuery);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $statesResult = $stmt->get_result();
    
    $topStates = [];
    while ($row = $statesResult->fetch_assoc()) {
        $topStates[$row['state']] = (int)$row['count'];
    }
    $stmt->close();
    
    $response['data']['top_states'] = $topStates;
    
    // === TOP CAMPAIGNS ===
    $campaignsQuery = "
        SELECT 
            campaign,
            COUNT(*) as count
        FROM lead_history
        WHERE DATE(submission_timestamp) BETWEEN ? AND ?
        AND campaign IS NOT NULL AND campaign != ''
        GROUP BY campaign
        ORDER BY count DESC
        LIMIT 10
    ";
    
    $stmt = $mysqli->prepare($campaignsQuery);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $campaignsResult = $stmt->get_result();
    
    $topCampaigns = [];
    while ($row = $campaignsResult->fetch_assoc()) {
        $topCampaigns[$row['campaign']] = (int)$row['count'];
    }
    $stmt->close();
    
    $response['data']['top_campaigns'] = $topCampaigns;
    
    // === HOURLY DISTRIBUTION ===
    $hourlyQuery = "
        SELECT 
            HOUR(submission_timestamp) as hour,
            COUNT(*) as count
        FROM lead_history
        WHERE DATE(submission_timestamp) BETWEEN ? AND ?
        GROUP BY hour
        ORDER BY hour ASC
    ";
    
    $stmt = $mysqli->prepare($hourlyQuery);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $hourlyResult = $stmt->get_result();
    
    // Initialize all hours with 0
    $hourlyDistribution = array_fill(0, 24, 0);
    
    while ($row = $hourlyResult->fetch_assoc()) {
        $hourlyDistribution[(int)$row['hour']] = (int)$row['count'];
    }
    $stmt->close();
    
    $response['data']['hourly_distribution'] = $hourlyDistribution;
    
    // === DETAILED STATS TABLE ===
    $detailedQuery = "
        SELECT 
            DATE_FORMAT(submission_timestamp, ?) as period,
            COUNT(*) as total,
            SUM(CASE WHEN boberdoo_status = 'success' THEN 1 ELSE 0 END) as successful,
            SUM(CASE WHEN boberdoo_status IN ('error', 'rejected', 'failed') THEN 1 ELSE 0 END) as failed,
            SUM(CASE WHEN boberdoo_status = 'pending' THEN 1 ELSE 0 END) as pending,
            AVG(CASE WHEN response_time_ms > 0 THEN response_time_ms ELSE NULL END) as avg_response,
            SUM(boberdoo_price) as revenue
        FROM lead_history
        WHERE DATE(submission_timestamp) BETWEEN ? AND ?
        GROUP BY period
        ORDER BY period DESC
    ";
    
    $stmt = $mysqli->prepare($detailedQuery);
    $stmt->bind_param("sss", $dateFormat, $startDate, $endDate);
    $stmt->execute();
    $detailedResult = $stmt->get_result();
    
    $detailedStats = [];
    while ($row = $detailedResult->fetch_assoc()) {
        if ($groupBy === 'week') {
            $periodLabel = 'Week ' . substr($row['period'], -2);
        } elseif ($groupBy === 'month') {
            $periodLabel = date('M Y', strtotime($row['period'] . '-01'));
        } else {
            $periodLabel = date('m/d/Y', strtotime($row['period']));
        }
        
        $detailedStats[] = [
            'period' => $periodLabel,
            'total' => (int)$row['total'],
            'successful' => (int)$row['successful'],
            'failed' => (int)$row['failed'],
            'pending' => (int)$row['pending'],
            'avg_response' => round($row['avg_response'] ?? 0, 0),
            'revenue' => round($row['revenue'] ?? 0, 2)
        ];
    }
    $stmt->close();
    
    $response['data']['detailed_stats'] = $detailedStats;
    
} catch (Exception $e) {
    error_log("Analytics error: " . $e->getMessage());
    $response = [
        'success' => false,
        'error' => 'Failed to generate analytics: ' . $e->getMessage()
    ];
}

$mysqli->close();

echo json_encode($response);
?>