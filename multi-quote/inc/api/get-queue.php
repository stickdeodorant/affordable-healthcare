<?php
/**
 * API Endpoint: get-queue.php - ENHANCED VERSION
 * Resubmission queue data with filtering, sorting, and pagination
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
$queueId = isset($_GET['queue_id']) ? intval($_GET['queue_id']) : null;
$status = $_GET['status'] ?? '';
$priority = $_GET['priority'] ?? '';
$dateRange = $_GET['date_range'] ?? '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$sortBy = $_GET['sort_by'] ?? 'created_at';
$sortOrder = $_GET['sort_order'] ?? 'DESC';

try {
    // If specific queue_id requested, return just that item
    if ($queueId) {
        $query = "
            SELECT 
                q.id as queue_id,
                q.buffer_lead_id as lead_id,
                q.priority,
                q.status,
                q.scheduled_time,
                q.attempts,
                q.max_attempts,
                q.last_attempt,
                q.error_message,
                q.created_at,
                q.updated_at,
                lb.lead_id as lead_external_id,
                lb.email,
                lb.phone,
                lb.primary_phone,
                lb.first_name,
                lb.last_name,
                lb.city,
                lb.state,
                lb.zip,
                lb.boberdoo_status,
                lb.boberdoo_error,
                lb.resubmit_count
            FROM resubmission_queue q
            LEFT JOIN lead_buffer lb ON q.buffer_lead_id = lb.id
            WHERE q.id = ?
        ";
        
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $queueId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $data = $row;
            
            // Parse JSON fields if present
            if (isset($data['error_message']) && $data['error_message']) {
                $data['error_details'] = json_decode($data['error_message'], true);
            }
            
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Queue item not found'
            ]);
        }
        
        $stmt->close();
        $mysqli->close();
        exit;
    }
    
    // Build query with filters
    $whereConditions = [];
    $params = [];
    $types = "";
    
    // Status filter
    if (!empty($status)) {
        $whereConditions[] = "q.status = ?";
        $params[] = $status;
        $types .= "s";
    } else {
        // Default: exclude completed and failed after max attempts
        $whereConditions[] = "q.status IN ('queued', 'scheduled', 'processing')";
    }
    
    // Priority filter
    if (!empty($priority)) {
        $whereConditions[] = "q.priority = ?";
        $params[] = $priority;
        $types .= "s";
    }
    
    // Date range filter
    if (!empty($dateRange)) {
        switch($dateRange) {
            case 'today':
                $whereConditions[] = "DATE(q.created_at) = CURDATE()";
                break;
            case 'yesterday':
                $whereConditions[] = "DATE(q.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
                break;
            case '7days':
                $whereConditions[] = "q.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case '30days':
                $whereConditions[] = "q.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
        }
    }
    
    $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
    
    // Validate sort column
    $validSortColumns = ['created_at', 'scheduled_time', 'priority', 'attempts', 'status'];
    if (!in_array($sortBy, $validSortColumns)) {
        $sortBy = 'created_at';
    }
    
    // Validate sort order
    $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM resubmission_queue q $whereClause";
    
    if (!empty($params)) {
        $stmt = $mysqli->prepare($countQuery);
        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $countResult = $stmt->get_result();
        $totalCount = $countResult->fetch_assoc()['total'];
        $stmt->close();
    } else {
        $countResult = $mysqli->query($countQuery);
        $totalCount = $countResult->fetch_assoc()['total'];
    }
    
    // Get queue status counts
    $statusQuery = "
        SELECT 
            status,
            COUNT(*) as count
        FROM resubmission_queue
        GROUP BY status
    ";
    $statusResult = $mysqli->query($statusQuery);
    $statusCounts = [];
    while ($row = $statusResult->fetch_assoc()) {
        $statusCounts[$row['status']] = (int)$row['count'];
    }
    
    // Get queue data with pagination
    $dataQuery = "
        SELECT 
            q.id as queue_id,
            q.buffer_lead_id as lead_id,
            q.priority,
            q.status,
            DATE_FORMAT(q.scheduled_time, '%Y-%m-%d %H:%i') as scheduled,
            q.attempts,
            q.max_attempts,
            DATE_FORMAT(q.last_attempt, '%Y-%m-%d %H:%i') as last_attempt,
            DATE_FORMAT(q.created_at, '%Y-%m-%d %H:%i') as created,
            q.error_message,
            lb.lead_id as lead_external_id,
            lb.email,
            lb.phone,
            lb.primary_phone,
            CONCAT(lb.first_name, ' ', lb.last_name) as name,
            lb.state,
            lb.boberdoo_status as lead_status,
            lb.resubmit_count
        FROM resubmission_queue q
        LEFT JOIN lead_buffer lb ON q.buffer_lead_id = lb.id
        $whereClause
        ORDER BY 
            CASE WHEN q.priority = 'high' THEN 1 
                 WHEN q.priority = 'medium' THEN 2 
                 ELSE 3 END,
            q.$sortBy $sortOrder
        LIMIT ? OFFSET ?
    ";
    
    // Add limit and offset to params
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $mysqli->prepare($dataQuery);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        // Calculate status for display
        $attemptStatus = 'active';
        if ($row['attempts'] >= $row['max_attempts']) {
            $attemptStatus = 'max_attempts';
        } elseif ($row['status'] === 'failed') {
            $attemptStatus = 'failed';
        } elseif ($row['status'] === 'completed') {
            $attemptStatus = 'completed';
        }
        
        $row['attempt_status'] = $attemptStatus;
        $row['progress'] = $row['max_attempts'] > 0 
            ? round(($row['attempts'] / $row['max_attempts']) * 100) 
            : 0;
        
        // Parse error message if JSON
        if ($row['error_message'] && strpos($row['error_message'], '{') === 0) {
            $row['error_details'] = json_decode($row['error_message'], true);
        }
        
        $data[] = $row;
    }
    
    $stmt->close();
    
    // Calculate pagination info
    $totalPages = $limit > 0 ? ceil($totalCount / $limit) : 1;
    $currentPage = $limit > 0 ? floor($offset / $limit) + 1 : 1;
    
    // Get priority distribution
    $priorityQuery = "
        SELECT 
            priority,
            COUNT(*) as count
        FROM resubmission_queue
        WHERE status IN ('queued', 'scheduled', 'processing')
        GROUP BY priority
    ";
    $priorityResult = $mysqli->query($priorityQuery);
    $priorityCounts = [
        'high' => 0,
        'medium' => 0,
        'low' => 0
    ];
    while ($row = $priorityResult->fetch_assoc()) {
        $priorityCounts[$row['priority']] = (int)$row['count'];
    }
    
    // Build response
    $response = [
        'success' => true,
        'data' => $data,
        'pagination' => [
            'total' => (int)$totalCount,
            'limit' => $limit,
            'offset' => $offset,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'has_more' => ($offset + $limit) < $totalCount
        ],
        'summary' => [
            'total_queued' => $statusCounts['queued'] ?? 0,
            'total_scheduled' => $statusCounts['scheduled'] ?? 0,
            'total_processing' => $statusCounts['processing'] ?? 0,
            'total_completed' => $statusCounts['completed'] ?? 0,
            'total_failed' => $statusCounts['failed'] ?? 0,
            'priority_high' => $priorityCounts['high'],
            'priority_medium' => $priorityCounts['medium'],
            'priority_low' => $priorityCounts['low']
        ],
        'filters' => [
            'status' => $status,
            'priority' => $priority,
            'date_range' => $dateRange
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Queue endpoint error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to retrieve queue data: ' . $e->getMessage()
    ]);
}

$mysqli->close();
?>