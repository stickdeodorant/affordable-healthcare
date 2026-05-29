#!/usr/bin/php
<?php
/**
 * Automated Cron Jobs for Lead Management System
 * Run this script via cron to perform automated maintenance tasks
 * 
 * Suggested cron schedule:
 * - Buffer cleanup: Daily at 3 AM
 * - Statistics calculation: Daily at 2 AM
 * - Queue processing: Every 30 minutes
 * - Blacklist cleanup: Weekly on Sunday at 1 AM
 * 
 */

// Prevent web access
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line.');
}

// Include database configuration
require_once __DIR__ . '/config/db-config.php';

// Get command line arguments
$action = $argv[1] ?? 'help';

// Database connection using new config
$mysqli = getDBConnection();

if (!$mysqli) {
    logMessage("ERROR: Database connection failed");
    exit(1);
}

$mysqli->set_charset("utf8mb4");

// Main action handler
switch ($action) {
    case 'cleanup_buffer':
        cleanupExpiredBufferLeads();
        break;
        
    case 'calculate_stats':
        calculateDailyStatistics();
        break;
        
    case 'process_queue':
        processResubmissionQueue();
        break;
        
    case 'cleanup_blacklist':
        cleanupBlacklistEntries();
        break;
        
    case 'send_report':
        sendPeriodicReport();
        break;
        
    case 'optimize_tables':
        optimizeDatabaseTables();
        break;
        
    case 'backup_database':
        backupDatabase();
        break;
        
    case 'help':
    default:
        showHelp();
        break;
}

$mysqli->close();

/**
 * Clean up expired buffer entries
 */
function cleanupExpiredBufferLeads() {
    global $mysqli;
    
    logMessage("Starting buffer cleanup...");
    
    // Get count of expired entries
    $countStmt = $mysqli->prepare("
        SELECT COUNT(*) as count 
        FROM lead_buffer 
        WHERE expires_at < NOW() 
        AND marked_for_resubmit = FALSE
    ");
    
    $countStmt->execute();
    $result = $countStmt->get_result();
    $row = $result->fetch_assoc();
    $expiredCount = $row['count'];
    $countStmt->close();
    
    if ($expiredCount > 0) {
        // Archive expired entries to history before deletion (optional)
        $archiveStmt = $mysqli->prepare("
            INSERT INTO lead_history (
                lead_id, email, phone, first_name, last_name,
                age, gender, city, state, zip,
                source, campaign, type,
                ip_address, user_agent,
                boberdoo_status, boberdoo_lead_id,
                submission_timestamp
            )
            SELECT 
                lead_id, email, primary_phone, first_name, last_name,
                age, gender, city, state, zip,
                src, campaign, type,
                ip_address, user_agent,
                CASE 
                    WHEN boberdoo_status = 'success' THEN 'success'
                    WHEN boberdoo_status = 'resubmitted' THEN 'success'
                    ELSE 'expired'
                END,
                boberdoo_lead_id,
                submission_time
            FROM lead_buffer
            WHERE expires_at < NOW() 
            AND marked_for_resubmit = FALSE
            AND boberdoo_status != 'success'
        ");
        
        $archiveStmt->execute();
        $archiveStmt->close();
        
        // Delete expired entries
        $deleteStmt = $mysqli->prepare("
            DELETE FROM lead_buffer 
            WHERE expires_at < NOW() 
            AND marked_for_resubmit = FALSE
        ");
        
        $deleteStmt->execute();
        $deletedCount = $mysqli->affected_rows;
        $deleteStmt->close();
        
        logMessage("Cleaned up $deletedCount expired buffer entries (archived $expiredCount)");
    } else {
        logMessage("No expired buffer entries to clean up");
    }
    
    // Also clean up old resubmission queue entries
    $queueStmt = $mysqli->prepare("
        DELETE FROM resubmission_queue 
        WHERE status IN ('completed', 'failed') 
        AND completed_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    
    $queueStmt->execute();
    $queueDeleted = $mysqli->affected_rows;
    $queueStmt->close();
    
    if ($queueDeleted > 0) {
        logMessage("Cleaned up $queueDeleted old queue entries");
    }
}

/**
 * Calculate daily statistics
 */
function calculateDailyStatistics() {
    global $mysqli;
    
    logMessage("Calculating daily statistics...");
    
    // Calculate for yesterday by default
    $statDate = date('Y-m-d', strtotime('-1 day'));
    
    // Get submission statistics
    $statsStmt = $mysqli->prepare("
        SELECT 
            COUNT(*) as total_submissions,
            SUM(CASE WHEN boberdoo_status = 'success' THEN 1 ELSE 0 END) as successful_submissions,
            SUM(CASE WHEN boberdoo_status IN ('error', 'rejected') THEN 1 ELSE 0 END) as failed_submissions,
            SUM(CASE WHEN is_blacklisted = TRUE THEN 1 ELSE 0 END) as blacklisted_attempts,
            COUNT(DISTINCT ip_address) as unique_visitors,
            AVG(response_time_ms) as avg_response_time,
            COALESCE(SUM(boberdoo_price), 0) as revenue_total
        FROM lead_history
        WHERE DATE(submission_timestamp) = ?
    ");
    
    $statsStmt->bind_param("s", $statDate);
    $statsStmt->execute();
    $result = $statsStmt->get_result();
    $stats = $result->fetch_assoc();
    $statsStmt->close();
    
    if ($stats['total_submissions'] > 0) {
        $conversionRate = round(($stats['successful_submissions'] / $stats['total_submissions']) * 100, 2);
        
        // Get top states
        $statesStmt = $mysqli->prepare("
            SELECT state, COUNT(*) as count 
            FROM lead_history 
            WHERE DATE(submission_timestamp) = ?
            GROUP BY state 
            ORDER BY count DESC 
            LIMIT 5
        ");
        
        $statesStmt->bind_param("s", $statDate);
        $statesStmt->execute();
        $statesResult = $statesStmt->get_result();
        
        $topStates = [];
        while ($row = $statesResult->fetch_assoc()) {
            $topStates[$row['state']] = $row['count'];
        }
        $statesStmt->close();
        
        // Get top campaigns
        $campaignsStmt = $mysqli->prepare("
            SELECT campaign, COUNT(*) as count 
            FROM lead_history 
            WHERE DATE(submission_timestamp) = ?
            AND campaign IS NOT NULL
            GROUP BY campaign 
            ORDER BY count DESC 
            LIMIT 5
        ");
        
        $campaignsStmt->bind_param("s", $statDate);
        $campaignsStmt->execute();
        $campaignsResult = $campaignsStmt->get_result();
        
        $topCampaigns = [];
        while ($row = $campaignsResult->fetch_assoc()) {
            $topCampaigns[$row['campaign']] = $row['count'];
        }
        $campaignsStmt->close();
        
        // Get hourly breakdown
        $hourlyStmt = $mysqli->prepare("
            SELECT HOUR(submission_timestamp) as hour, COUNT(*) as count 
            FROM lead_history 
            WHERE DATE(submission_timestamp) = ?
            GROUP BY HOUR(submission_timestamp) 
            ORDER BY hour
        ");
        
        $hourlyStmt->bind_param("s", $statDate);
        $hourlyStmt->execute();
        $hourlyResult = $hourlyStmt->get_result();
        
        $hourlyBreakdown = [];
        while ($row = $hourlyResult->fetch_assoc()) {
            $hourlyBreakdown[$row['hour']] = $row['count'];
        }
        $hourlyStmt->close();
        
        // Insert or update statistics
        $insertStmt = $mysqli->prepare("
            INSERT INTO dashboard_stats (
                stat_date,
                total_submissions,
                successful_submissions,
                failed_submissions,
                blacklisted_attempts,
                unique_visitors,
                conversion_rate,
                avg_response_time_ms,
                revenue_total,
                top_states,
                top_campaigns,
                hourly_breakdown
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                total_submissions = VALUES(total_submissions),
                successful_submissions = VALUES(successful_submissions),
                failed_submissions = VALUES(failed_submissions),
                blacklisted_attempts = VALUES(blacklisted_attempts),
                unique_visitors = VALUES(unique_visitors),
                conversion_rate = VALUES(conversion_rate),
                avg_response_time_ms = VALUES(avg_response_time_ms),
                revenue_total = VALUES(revenue_total),
                top_states = VALUES(top_states),
                top_campaigns = VALUES(top_campaigns),
                hourly_breakdown = VALUES(hourly_breakdown),
                calculated_at = CURRENT_TIMESTAMP
        ");
        
        $topStatesJson = json_encode($topStates);
        $topCampaignsJson = json_encode($topCampaigns);
        $hourlyBreakdownJson = json_encode($hourlyBreakdown);
        
        $insertStmt->bind_param("siiiidddsss",
            $statDate,
            $stats['total_submissions'],
            $stats['successful_submissions'],
            $stats['failed_submissions'],
            $stats['blacklisted_attempts'],
            $stats['unique_visitors'],
            $conversionRate,
            $stats['avg_response_time'],
            $stats['revenue_total'],
            $topStatesJson,
            $topCampaignsJson,
            $hourlyBreakdownJson
        );
        
        $insertStmt->execute();
        $insertStmt->close();
        
        logMessage("Statistics calculated for $statDate: " . 
            "{$stats['total_submissions']} submissions, " .
            "{$stats['successful_submissions']} successful, " .
            "$conversionRate% conversion rate");
    } else {
        logMessage("No submissions found for $statDate");
    }
}

/**
 * Process resubmission queue
 */
function processResubmissionQueue() {
    global $mysqli;
    
    logMessage("Processing resubmission queue...");
    
    // Include the resubmission handler
    $resubmitScript = __DIR__ . '/resubmit-handler.php';
    
    if (file_exists($resubmitScript)) {
        // Call the resubmission handler via HTTP or directly
        $ch = curl_init('http://localhost/multi-quote/inc/resubmit-handler.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['action' => 'process_queue']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minutes timeout
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            if ($result && $result['success']) {
                logMessage("Queue processed: {$result['processed']} items, " .
                    "{$result['successful']} successful, {$result['failed']} failed");
            } else {
                logMessage("Queue processing failed: " . $response);
            }
        } else {
            logMessage("Failed to call resubmission handler: HTTP $httpCode");
        }
    } else {
        logMessage("Resubmission handler not found");
    }
}

/**
 * Clean up old blacklist entries
 */
function cleanupBlacklistEntries() {
    global $mysqli;
    
    logMessage("Cleaning up blacklist entries...");
    
    // Remove expired temporary blocks that are older than 30 days
    $cleanupStmt = $mysqli->prepare("
        DELETE FROM email_blacklist 
        WHERE is_permanent = FALSE 
        AND block_until < DATE_SUB(NOW(), INTERVAL 30 DAY)
        AND submission_count < 3
    ");
    
    $cleanupStmt->execute();
    $deleted = $mysqli->affected_rows;
    $cleanupStmt->close();
    
    if ($deleted > 0) {
        logMessage("Removed $deleted expired temporary blacklist entries");
    }
    
    // Update stats for permanent blacklist entries
    $updateStmt = $mysqli->prepare("
        UPDATE email_blacklist 
        SET blacklist_reason = 'Auto-flagged: Multiple violations'
        WHERE is_permanent = TRUE 
        AND blacklist_reason IS NULL
    ");
    
    $updateStmt->execute();
    $updated = $mysqli->affected_rows;
    $updateStmt->close();
    
    if ($updated > 0) {
        logMessage("Updated $updated permanent blacklist entries");
    }
}

/**
 * Send periodic report
 */
function sendPeriodicReport() {
    global $mysqli;
    
    logMessage("Generating periodic report...");
    
    // Get today's statistics
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $weekAgo = date('Y-m-d', strtotime('-7 days'));
    
    // Get summary statistics
    $summaryStmt = $mysqli->prepare("
        SELECT 
            (SELECT COUNT(*) FROM lead_history WHERE DATE(submission_timestamp) = ?) as today_count,
            (SELECT COUNT(*) FROM lead_history WHERE DATE(submission_timestamp) = ?) as yesterday_count,
            (SELECT COUNT(*) FROM lead_history WHERE DATE(submission_timestamp) >= ?) as week_count,
            (SELECT COUNT(*) FROM lead_buffer WHERE expires_at > NOW()) as buffer_count,
            (SELECT COUNT(*) FROM email_blacklist WHERE is_permanent = TRUE) as blacklist_count,
            (SELECT COUNT(*) FROM resubmission_queue WHERE status = 'queued') as queue_count
    ");
    
    $summaryStmt->bind_param("sss", $today, $yesterday, $weekAgo);
    $summaryStmt->execute();
    $result = $summaryStmt->get_result();
    $summary = $result->fetch_assoc();
    $summaryStmt->close();
    
    // Build email content
    $emailBody = "Lead Management System - Daily Report\n";
    $emailBody .= "=====================================\n\n";
    $emailBody .= "Report Date: " . date('Y-m-d H:i:s') . "\n\n";
    $emailBody .= "TODAY'S STATISTICS:\n";
    $emailBody .= "- Total Submissions: {$summary['today_count']}\n";
    $emailBody .= "- Yesterday: {$summary['yesterday_count']}\n";
    $emailBody .= "- Last 7 Days: {$summary['week_count']}\n\n";
    $emailBody .= "SYSTEM STATUS:\n";
    $emailBody .= "- Leads in Buffer: {$summary['buffer_count']}\n";
    $emailBody .= "- Permanent Blacklists: {$summary['blacklist_count']}\n";
    $emailBody .= "- Items in Queue: {$summary['queue_count']}\n\n";
    
    // Send email (using your existing email configuration)
    $to = 'kelliott@infinixmedia.com';
    $subject = 'Lead Management Daily Report - ' . date('Y-m-d');
    $headers = "From: no-reply@healthcare-quotes.com\r\n";
    $headers .= "Reply-To: no-reply@healthcare-quotes.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    if (mail($to, $subject, $emailBody, $headers)) {
        logMessage("Daily report sent to $to");
    } else {
        logMessage("Failed to send daily report");
    }
}

/**
 * Optimize database tables
 */
function optimizeDatabaseTables() {
    global $mysqli;
    
    logMessage("Optimizing database tables...");
    
    $tables = [
        'lead_buffer',
        'lead_history',
        'email_blacklist',
        'resubmission_queue',
        'dashboard_stats',
        'admin_activity_log'
    ];
    
    foreach ($tables as $table) {
        $mysqli->query("OPTIMIZE TABLE $table");
        logMessage("Optimized table: $table");
    }
    
    // Analyze tables for better query performance
    foreach ($tables as $table) {
        $mysqli->query("ANALYZE TABLE $table");
    }
    
    logMessage("Database optimization complete");
}

/**
 * Backup database (requires mysqldump)
 */
function backupDatabase() {
    global $dbConfig;
    
    logMessage("Starting database backup...");
    
    $backupDir = '/var/backups/lead_management/';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    $backupFile = $backupDir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    
    $command = sprintf(
        'mysqldump -h %s -u %s -p%s %s > %s 2>&1',
        escapeshellarg($dbConfig['host']),
        escapeshellarg($dbConfig['user']),
        escapeshellarg($dbConfig['pass']),
        escapeshellarg($dbConfig['name']),
        escapeshellarg($backupFile)
    );
    
    exec($command, $output, $returnCode);
    
    if ($returnCode === 0) {
        // Compress the backup
        exec("gzip $backupFile");
        logMessage("Database backup completed: {$backupFile}.gz");
        
        // Remove old backups (keep last 30 days)
        exec("find $backupDir -name '*.gz' -mtime +30 -delete");
    } else {
        logMessage("Database backup failed: " . implode("\n", $output));
    }
}

/**
 * Show help message
 */
function showHelp() {
    echo "Lead Management System - Cron Jobs\n";
    echo "===================================\n\n";
    echo "Usage: php cron-jobs.php [action]\n\n";
    echo "Available actions:\n";
    echo "  cleanup_buffer     - Remove expired buffer entries (run daily)\n";
    echo "  calculate_stats    - Calculate daily statistics (run daily)\n";
    echo "  process_queue      - Process resubmission queue (run every 30 min)\n";
    echo "  cleanup_blacklist  - Clean old blacklist entries (run weekly)\n";
    echo "  send_report        - Send periodic email report (run daily)\n";
    echo "  optimize_tables    - Optimize database tables (run weekly)\n";
    echo "  backup_database    - Backup database (run daily)\n";
    echo "  help               - Show this help message\n\n";
    echo "Example crontab entries:\n";
    echo "0 3 * * * /usr/bin/php " . __FILE__ . " cleanup_buffer\n";
    echo "0 2 * * * /usr/bin/php " . __FILE__ . " calculate_stats\n";
    echo "*/30 * * * * /usr/bin/php " . __FILE__ . " process_queue\n";
    echo "0 1 * * 0 /usr/bin/php " . __FILE__ . " cleanup_blacklist\n";
}

/**
 * Log message to file and console
 */
function logMessage($message) {
    $logFile = '/var/log/lead_management_cron.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message\n";
    
    // Write to log file
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    // Also output to console
    echo $logEntry;
}
