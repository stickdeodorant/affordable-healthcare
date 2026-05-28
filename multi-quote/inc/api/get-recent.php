<?php
/**
 * GET-RECENT.PHP - Recent Leads API
 * Location: /multi-quote/inc/api/get-recent.php
 */

session_start();
header('Content-Type: application/json');

// Include database configuration
require_once __DIR__ . '/../config/db-config.php';

// Get database connection
$mysqli = getDBConnection();

if (!$mysqli) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed']));
}

// Get limit parameter
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$limit = min(100, max(1, $limit)); // Ensure between 1 and 100

// Initialize leads array
$leads = [];

// Query recent leads from lead_buffer (most recent activity)
$query = "
    SELECT 
        lead_id,
        email,
        phone,
        primary_phone,
        first_name,
        last_name,
        city,
        state,
        zip,
        boberdoo_status,
        boberdoo_lead_id,
        created_at,
        expires_at,
        resubmit_count
    FROM lead_buffer 
    ORDER BY created_at DESC 
    LIMIT $limit
";

$result = $mysqli->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Format the lead data
        $leads[] = [
            'lead_id' => $row['lead_id'],
            'email' => $row['email'],
            'phone' => $row['phone'] ?: $row['primary_phone'],
            'primary_phone' => $row['primary_phone'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'full_name' => trim($row['first_name'] . ' ' . $row['last_name']),
            'city' => $row['city'],
            'state' => $row['state'],
            'zip' => $row['zip'],
            'status' => $row['boberdoo_status'] ?: 'pending',
            'boberdoo_status' => $row['boberdoo_status'],
            'boberdoo_lead_id' => $row['boberdoo_lead_id'],
            'created_at' => $row['created_at'],
            'expires_at' => $row['expires_at'],
            'resubmit_count' => $row['resubmit_count'],
            'time_ago' => getTimeAgo($row['created_at'])
        ];
    }
}

// If no leads in buffer, check history table
if (empty($leads)) {
    $query = "
        SELECT 
            lead_id,
            email,
            phone,
            first_name,
            last_name,
            city,
            state,
            zip,
            boberdoo_status,
            boberdoo_lead_id,
            submission_timestamp as created_at
        FROM lead_history 
        ORDER BY submission_timestamp DESC 
        LIMIT $limit
    ";
    
    $result = $mysqli->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $leads[] = [
                'lead_id' => $row['lead_id'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'primary_phone' => $row['phone'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'full_name' => trim($row['first_name'] . ' ' . $row['last_name']),
                'city' => $row['city'],
                'state' => $row['state'],
                'zip' => $row['zip'],
                'status' => $row['boberdoo_status'] ?: 'completed',
                'boberdoo_status' => $row['boberdoo_status'],
                'boberdoo_lead_id' => $row['boberdoo_lead_id'],
                'created_at' => $row['created_at'],
                'time_ago' => getTimeAgo($row['created_at']),
                'from_history' => true
            ];
        }
    }
}

$mysqli->close();

// Helper function for time ago
function getTimeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    if ($diff->d > 0) {
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    } elseif ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    } elseif ($diff->i > 0) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    } else {
        return 'Just now';
    }
}

// Return the leads
echo json_encode([
    'success' => true,
    'data' => $leads,
    'count' => count($leads),
    'timestamp' => date('Y-m-d H:i:s')
]);
?>