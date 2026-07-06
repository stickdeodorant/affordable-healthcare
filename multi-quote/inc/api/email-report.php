<?php
/**
 * API Endpoint: email-report.php
 * Email analytics reports to specified recipients
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
$email = $_POST['email'] ?? '';
$startDate = $_POST['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
$endDate = $_POST['end_date'] ?? date('Y-m-d');
$format = $_POST['format'] ?? 'html'; // html or pdf

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid email address'
    ]);
    exit;
}

// Validate dates
$startDate = date('Y-m-d', strtotime($startDate));
$endDate = date('Y-m-d', strtotime($endDate));

try {
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
    
    // Get daily breakdown for chart data
    $dailyQuery = "
        SELECT 
            DATE(submission_timestamp) as date,
            COUNT(*) as total,
            SUM(CASE WHEN boberdoo_status = 'success' THEN 1 ELSE 0 END) as successful
        FROM lead_history
        WHERE DATE(submission_timestamp) BETWEEN ? AND ?
        GROUP BY date
        ORDER BY date ASC
    ";
    
    $stmt = $mysqli->prepare($dailyQuery);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $dailyResult = $stmt->get_result();
    
    $dailyData = [];
    while ($row = $dailyResult->fetch_assoc()) {
        $dailyData[] = $row;
    }
    $stmt->close();
    
    // Get top states
    $stateQuery = "
        SELECT 
            state,
            COUNT(*) as count
        FROM lead_history
        WHERE DATE(submission_timestamp) BETWEEN ? AND ?
        AND state IS NOT NULL AND state != ''
        GROUP BY state
        ORDER BY count DESC
        LIMIT 5
    ";
    
    $stmt = $mysqli->prepare($stateQuery);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $stateResult = $stmt->get_result();
    
    $topStates = [];
    while ($row = $stateResult->fetch_assoc()) {
        $topStates[] = $row;
    }
    $stmt->close();
    
    // Build HTML email
    $htmlBody = buildEmailHTML($startDate, $endDate, $summary, $successRate, $dailyData, $topStates);
    
    // Send email using Mailtrap - credentials from environment
    require_once __DIR__ . '/../../../inc/env.php';
    $apiKey = 'Bearer ' . env('MAILTRAP_TOKEN', '');
    $fromEmail = env('MAILTRAP_FROM_EMAIL', 'no-reply@affordable-healthcare.com');
    $fromName = env('MAILTRAP_FROM_NAME', 'Affordable Healthcare') . ' - Lead Analytics';
    $subject = "Lead Analytics Report: $startDate to $endDate";
    
    $payload = [
        "from" => [
            "email" => $fromEmail,
            "name" => $fromName
        ],
        "to" => [
            ["email" => $email]
        ],
        "subject" => $subject,
        "html" => $htmlBody,
        "category" => "Analytics Reports"
    ];
    
    $ch = curl_init('https://send.api.mailtrap.io/api/send');
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            'Authorization: ' . $apiKey,
            'Content-Type: application/json'
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 || $httpCode === 201) {
        echo json_encode([
            'success' => true,
            'message' => "Report sent successfully to $email"
        ]);
    } else {
        throw new Exception("Failed to send email. HTTP Code: $httpCode");
    }
    
} catch (Exception $e) {
    error_log("Email report error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to send report: ' . $e->getMessage()
    ]);
}

$mysqli->close();

/**
 * Build HTML email body
 */
function buildEmailHTML($startDate, $endDate, $summary, $successRate, $dailyData, $topStates) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2196F3; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #f9f9f9; padding: 20px; }
            .stat-box { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #2196F3; }
            .stat-label { font-size: 12px; color: #666; text-transform: uppercase; }
            .stat-value { font-size: 24px; font-weight: bold; color: #2196F3; }
            table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            th { background: #2196F3; color: white; padding: 10px; text-align: left; }
            td { padding: 10px; border-bottom: 1px solid #ddd; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            .success { color: #4CAF50; }
            .danger { color: #f44336; }
            .warning { color: #ff9800; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>📊 Lead Analytics Report</h1>
                <p>' . date('M d, Y', strtotime($startDate)) . ' - ' . date('M d, Y', strtotime($endDate)) . '</p>
            </div>
            
            <div class="content">
                <h2>Summary Statistics</h2>
                
                <div class="stat-box">
                    <div class="stat-label">Total Leads</div>
                    <div class="stat-value">' . number_format($summary['total']) . '</div>
                </div>
                
                <div class="stat-box">
                    <div class="stat-label">Success Rate</div>
                    <div class="stat-value success">' . $successRate . '%</div>
                </div>
                
                <div class="stat-box">
                    <div class="stat-label">Successful Submissions</div>
                    <div class="stat-value success">' . number_format($summary['successful']) . '</div>
                </div>
                
                <div class="stat-box">
                    <div class="stat-label">Failed Submissions</div>
                    <div class="stat-value danger">' . number_format($summary['failed']) . '</div>
                </div>
                
                <div class="stat-box">
                    <div class="stat-label">Average Response Time</div>
                    <div class="stat-value">' . round($summary['avg_response_time'] ?? 0, 0) . 'ms</div>
                </div>
                
                <div class="stat-box">
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-value">$' . number_format($summary['total_revenue'] ?? 0, 2) . '</div>
                </div>
                
                <h2>Top 5 States</h2>
                <table>
                    <thead>
                        <tr>
                            <th>State</th>
                            <th>Leads</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($topStates as $state) {
        $html .= '<tr>
                    <td>' . htmlspecialchars($state['state']) . '</td>
                    <td>' . number_format($state['count']) . '</td>
                  </tr>';
    }
    
    $html .= '
                    </tbody>
                </table>
                
                <h2>Daily Breakdown</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Successful</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($dailyData as $day) {
        $html .= '<tr>
                    <td>' . date('M d', strtotime($day['date'])) . '</td>
                    <td>' . number_format($day['total']) . '</td>
                    <td class="success">' . number_format($day['successful']) . '</td>
                  </tr>';
    }
    
    $html .= '
                    </tbody>
                </table>
            </div>
            
            <div class="footer">
                <p>This report was automatically generated by the Lead Management System</p>
                <p>© ' . date('Y') . ' Affordable Healthcare. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}
?>