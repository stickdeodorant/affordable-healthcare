<?php
/**
 * API Endpoint: export-report.php
 * Export analytics data to CSV or Excel format
 */

session_start();

// Check authentication
if (!isset($_SESSION['admin_authenticated']) || $_SESSION['admin_authenticated'] !== true) {
    http_response_code(401);
    die('Unauthorized');
}

// Include database configuration
require_once __DIR__ . '/../config/db-config.php';

// Get database connection
$mysqli = getDBConnection();

if (!$mysqli) {
    die('Database connection failed');
}

$mysqli->set_charset("utf8mb4");

// Get parameters
$format = $_GET['format'] ?? 'csv';
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Validate dates
$startDate = date('Y-m-d', strtotime($startDate));
$endDate = date('Y-m-d', strtotime($endDate));

// Generate filename
$filename = "lead_analytics_" . $startDate . "_to_" . $endDate . "." . $format;

// Set headers based on format
if ($format === 'excel' || $format === 'xlsx') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
} else {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
}

// Open output stream
$output = fopen('php://output', 'w');

// Add BOM for Excel UTF-8 support
if ($format === 'csv') {
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
}

try {
    // === SUMMARY SECTION ===
    fputcsv($output, ['LEAD ANALYTICS REPORT']);
    fputcsv($output, ['Generated:', date('Y-m-d H:i:s')]);
    fputcsv($output, ['Period:', $startDate . ' to ' . $endDate]);
    fputcsv($output, []);
    
    // Get summary statistics
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
    $summary = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    $successRate = $summary['total'] > 0 
        ? round(($summary['successful'] / $summary['total']) * 100, 2) 
        : 0;
    
    fputcsv($output, ['SUMMARY STATISTICS']);
    fputcsv($output, ['Total Leads:', $summary['total']]);
    fputcsv($output, ['Successful:', $summary['successful']]);
    fputcsv($output, ['Failed:', $summary['failed']]);
    fputcsv($output, ['Pending:', $summary['pending']]);
    fputcsv($output, ['Success Rate:', $successRate . '%']);
    fputcsv($output, ['Avg Response Time:', round($summary['avg_response_time'] ?? 0, 0) . 'ms']);
    fputcsv($output, ['Total Revenue:', '$' . number_format($summary['total_revenue'] ?? 0, 2)]);
    fputcsv($output, []);
    
    // === DAILY BREAKDOWN ===
    fputcsv($output, ['DAILY BREAKDOWN']);
    fputcsv($output, ['Date', 'Total', 'Successful', 'Failed', 'Pending', 'Success Rate', 'Avg Response Time', 'Revenue']);
    
    $dailyQuery = "
        SELECT 
            DATE(submission_timestamp) as date,
            COUNT(*) as total,
            SUM(CASE WHEN boberdoo_status = 'success' THEN 1 ELSE 0 END) as successful,
            SUM(CASE WHEN boberdoo_status IN ('error', 'rejected', 'failed') THEN 1 ELSE 0 END) as failed,
            SUM(CASE WHEN boberdoo_status = 'pending' THEN 1 ELSE 0 END) as pending,
            AVG(CASE WHEN response_time_ms > 0 THEN response_time_ms ELSE NULL END) as avg_response,
            SUM(boberdoo_price) as revenue
        FROM lead_history
        WHERE DATE(submission_timestamp) BETWEEN ? AND ?
        GROUP BY date
        ORDER BY date ASC
    ";
    
    $stmt = $mysqli->prepare($dailyQuery);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $dailyResult = $stmt->get_result();
    
    while ($row = $dailyResult->fetch_assoc()) {
        $daySuccessRate = $row['total'] > 0 
            ? round(($row['successful'] / $row['total']) * 100, 2) 
            : 0;
        
        fputcsv($output, [
            $row['date'],
            $row['total'],
            $row['successful'],
            $row['failed'],
            $row['pending'],
            $daySuccessRate . '%',
            round($row['avg_response'] ?? 0, 0) . 'ms',
            '$' . number_format($row['revenue'] ?? 0, 2)
        ]);
    }
    $stmt->close();
    
    fputcsv($output, []);
    
    // === STATE BREAKDOWN ===
    fputcsv($output, ['STATE BREAKDOWN']);
    fputcsv($output, ['State', 'Total Leads', 'Successful', 'Failed', 'Success Rate']);
    
    $stateQuery = "
        SELECT 
            state,
            COUNT(*) as total,
            SUM(CASE WHEN boberdoo_status = 'success' THEN 1 ELSE 0 END) as successful,
            SUM(CASE WHEN boberdoo_status IN ('error', 'rejected', 'failed') THEN 1 ELSE 0 END) as failed
        FROM lead_history
        WHERE DATE(submission_timestamp) BETWEEN ? AND ?
        AND state IS NOT NULL AND state != ''
        GROUP BY state
        ORDER BY total DESC
    ";
    
    $stmt = $mysqli->prepare($stateQuery);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $stateResult = $stmt->get_result();
    
    while ($row = $stateResult->fetch_assoc()) {
        $stateSuccessRate = $row['total'] > 0 
            ? round(($row['successful'] / $row['total']) * 100, 2) 
            : 0;
        
        fputcsv($output, [
            $row['state'],
            $row['total'],
            $row['successful'],
            $row['failed'],
            $stateSuccessRate . '%'
        ]);
    }
    $stmt->close();
    
    fputcsv($output, []);
    
    // === CAMPAIGN BREAKDOWN ===
    fputcsv($output, ['CAMPAIGN BREAKDOWN']);
    fputcsv($output, ['Campaign', 'Source', 'Total Leads', 'Successful', 'Failed', 'Success Rate', 'Revenue']);
    
    $campaignQuery = "
        SELECT 
            COALESCE(campaign, 'Unknown') as campaign,
            COALESCE(source, 'Unknown') as source,
            COUNT(*) as total,
            SUM(CASE WHEN boberdoo_status = 'success' THEN 1 ELSE 0 END) as successful,
            SUM(CASE WHEN boberdoo_status IN ('error', 'rejected', 'failed') THEN 1 ELSE 0 END) as failed,
            SUM(boberdoo_price) as revenue
        FROM lead_history
        WHERE DATE(submission_timestamp) BETWEEN ? AND ?
        GROUP BY campaign, source
        ORDER BY total DESC
    ";
    
    $stmt = $mysqli->prepare($campaignQuery);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $campaignResult = $stmt->get_result();
    
    while ($row = $campaignResult->fetch_assoc()) {
        $campaignSuccessRate = $row['total'] > 0 
            ? round(($row['successful'] / $row['total']) * 100, 2) 
            : 0;
        
        fputcsv($output, [
            $row['campaign'],
            $row['source'],
            $row['total'],
            $row['successful'],
            $row['failed'],
            $campaignSuccessRate . '%',
            '$' . number_format($row['revenue'] ?? 0, 2)
        ]);
    }
    $stmt->close();
    
    fputcsv($output, []);
    
    // === HOURLY DISTRIBUTION ===
    fputcsv($output, ['HOURLY DISTRIBUTION']);
    fputcsv($output, ['Hour', 'Total Leads']);
    
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
    
    // Initialize all hours
    $hourlyData = array_fill(0, 24, 0);
    while ($row = $hourlyResult->fetch_assoc()) {
        $hourlyData[(int)$row['hour']] = (int)$row['count'];
    }
    $stmt->close();
    
    for ($hour = 0; $hour < 24; $hour++) {
        fputcsv($output, [
            sprintf('%02d:00', $hour),
            $hourlyData[$hour]
        ]);
    }
    
    fputcsv($output, []);
    fputcsv($output, ['Report End']);
    
} catch (Exception $e) {
    error_log("Export error: " . $e->getMessage());
    fputcsv($output, ['Error generating report: ' . $e->getMessage()]);
}

$mysqli->close();
fclose($output);
exit;
?>