<?php 
session_start();
require_once __DIR__ . '/../inc/env.php';

// Create connection using env helper
$conn = get_db_connection();

function query_count($conn, $sql, $field = 'count') {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log('Dashboard count query failed: ' . mysqli_error($conn));
        return 0;
    }

    $row = mysqli_fetch_assoc($result);
    return (int)($row[$field] ?? 0);
}

function query_result($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log('Dashboard data query failed: ' . mysqli_error($conn));
        return null;
    }
    return $result;
}

// Get IP address
function getClientIP() {
    $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    foreach ($ip_keys as $key) {
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
    }
    return 'Unknown';
}

// Handle export
if (isset($_POST['export'])) {
    $export_type = $_POST['export_type'];
    $filename = $export_type . '_export_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    if ($export_type == 'leads') {
        fputcsv($output, ['ID', 'Email', 'IP Address', 'Date', 'Time', 'Error']);
        $query = "SELECT * FROM leads ORDER BY ID DESC";
        $result = query_result($conn, $query);
        if ($result) {
            while($row = mysqli_fetch_assoc($result)) {
                fputcsv($output, [
                    $row['ID'],
                    $row['email'],
                    $row['ip_address'],
                    date('Y-m-d', strtotime($row['timestamp'])),
                    date('H:i:s', strtotime($row['timestamp'])),
                    $row['error']
                ]);
            }
        }
    } else {
        fputcsv($output, ['ID', 'Email', 'IP Address', 'Date', 'Time', 'Referrer']);
        $query = "SELECT * FROM bademail ORDER BY ID DESC";
        $result = query_result($conn, $query);
        if ($result) {
            while($row = mysqli_fetch_assoc($result)) {
                fputcsv($output, [
                    $row['ID'],
                    $row['email'],
                    $row['ip_address'],
                    date('Y-m-d', strtotime($row['timestamp'])),
                    date('H:i:s', strtotime($row['timestamp'])),
                    $row['referrer']
                ]);
            }
        }
    }
    fclose($output);
    exit;
}

// Get statistics
$stats = [];

// Total leads
$stats['total_leads'] = query_count($conn, "SELECT COUNT(*) as count FROM leads");

// Today's leads
$stats['today_leads'] = query_count($conn, "SELECT COUNT(*) as count FROM leads WHERE DATE(timestamp) = CURDATE()");

// Total banned
$stats['total_banned'] = query_count($conn, "SELECT COUNT(*) as count FROM bademail");

// This week's leads
$stats['week_leads'] = query_count($conn, "SELECT COUNT(*) as count FROM leads WHERE YEARWEEK(timestamp) = YEARWEEK(NOW())");

// Get search parameter
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$view = isset($_GET['view']) ? $_GET['view'] : 'leads';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leads Management Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --primary-dark: #2e59d9;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .dashboard-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            margin: 30px auto;
            padding: 0;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 1400px;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 30px;
            border-radius: 20px 20px 0 0;
            position: relative;
            overflow: hidden;
        }
        
        .dashboard-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-left: 4px solid;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .stat-card.primary { border-left-color: var(--primary-color); }
        .stat-card.success { border-left-color: var(--success-color); }
        .stat-card.warning { border-left-color: var(--warning-color); }
        .stat-card.info { border-left-color: var(--info-color); }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: var(--dark-color);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .nav-tabs {
            border: none;
            background: var(--light-color);
            border-radius: 10px;
            padding: 5px;
            margin-bottom: 30px;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: var(--dark-color);
            padding: 12px 30px;
            border-radius: 8px;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .nav-tabs .nav-link.active {
            background: white;
            color: var(--primary-color);
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .nav-tabs .nav-link:hover {
            color: var(--primary-color);
        }
        
        .search-bar {
            background: white;
            border-radius: 50px;
            padding: 10px 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .search-bar input {
            border: none;
            outline: none;
            width: 100%;
            padding: 5px 10px;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .custom-table {
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .custom-table thead th {
            background: var(--light-color);
            color: var(--dark-color);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
            padding: 15px;
            border: none;
        }
        
        .custom-table tbody tr {
            transition: all 0.3s;
        }
        
        .custom-table tbody tr:hover {
            background: var(--light-color);
            transform: scale(1.01);
        }
        
        .custom-table td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .badge-email {
            background: #e3f2fd;
            color: #1976d2;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .badge-ip {
            background: #f3e5f5;
            color: #7b1fa2;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .btn-export {
            background: linear-gradient(135deg, var(--success-color), #17a673);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(28, 200, 138, 0.3);
        }
        
        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(28, 200, 138, 0.4);
            color: white;
        }
        
        .error-badge {
            background: #ffebee;
            color: #c62828;
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 0.85rem;
        }
        
        .timestamp-cell {
            font-size: 0.9rem;
            color: var(--dark-color);
        }
        
        .icon-stat {
            font-size: 2.5rem;
            opacity: 0.3;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        
        .spinner-grow {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>
<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-grow text-light" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 class="mb-0 position-relative">
                <i class="bi bi-speedometer2 me-3"></i>
                Leads Management Dashboard
            </h1>
            <p class="mb-0 mt-2 opacity-75 position-relative">Real-time monitoring and management system</p>
        </div>
        
        <div class="p-4">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card primary position-relative">
                        <div class="stat-number text-primary"><?php echo number_format($stats['total_leads']); ?></div>
                        <div class="stat-label">Total Leads</div>
                        <i class="bi bi-people-fill icon-stat text-primary"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card success position-relative">
                        <div class="stat-number text-success"><?php echo number_format($stats['today_leads']); ?></div>
                        <div class="stat-label">Today's Leads</div>
                        <i class="bi bi-calendar-check icon-stat text-success"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card info position-relative">
                        <div class="stat-number text-info"><?php echo number_format($stats['week_leads']); ?></div>
                        <div class="stat-label">This Week</div>
                        <i class="bi bi-graph-up icon-stat text-info"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card warning position-relative">
                        <div class="stat-number text-warning"><?php echo number_format($stats['total_banned']); ?></div>
                        <div class="stat-label">Banned Emails</div>
                        <i class="bi bi-shield-exclamation icon-stat text-warning"></i>
                    </div>
                </div>
            </div>
            
            <!-- Navigation Tabs -->
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link <?php echo $view == 'leads' ? 'active' : ''; ?>" href="?view=leads">
                        <i class="bi bi-envelope-check me-2"></i>Active Leads
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $view == 'banned' ? 'active' : ''; ?>" href="?view=banned">
                        <i class="bi bi-ban me-2"></i>Banned Emails
                    </a>
                </li>
            </ul>
            
            <!-- Search and Export -->
            <div class="row align-items-center mb-4">
                <div class="col-md-8">
                    <form method="GET" action="" class="search-bar d-flex align-items-center">
                        <i class="bi bi-search text-muted me-2"></i>
                        <input type="text" name="search" placeholder="Search by email or IP address..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <input type="hidden" name="view" value="<?php echo $view; ?>">
                    </form>
                </div>
                <div class="col-md-4 text-end">
                    <form method="POST" action="" class="d-inline">
                        <input type="hidden" name="export_type" value="<?php echo $view; ?>">
                        <button type="submit" name="export" class="btn btn-export">
                            <i class="bi bi-download me-2"></i>Export CSV
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Data Table -->
            <div class="table-container">
                <?php if ($view == 'leads'): ?>
                    <?php
                    $where = "";
                    if ($search) {
                        $where = "WHERE email LIKE '%$search%' OR ip_address LIKE '%$search%'";
                    }
                    
                    $count_query = "SELECT COUNT(*) as total FROM leads $where";
                    $total_records = query_count($conn, $count_query, 'total');
                    $total_pages = ceil($total_records / $limit);
                    
                    $query = "SELECT * FROM leads $where ORDER BY ID DESC LIMIT $offset, $limit";
                    $result = query_result($conn, $query);
                    ?>
                    
                    <div class="table-responsive">
                        <table class="table custom-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Email</th>
                                    <th>IP Address</th>
                                    <th>Date & Time</th>
                                    <th>Error</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result): while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><strong>#<?php echo $row['ID']; ?></strong></td>
                                    <td><span class="badge-email"><?php echo htmlspecialchars($row['email']); ?></span></td>
                                    <td><span class="badge-ip"><?php echo $row['ip_address']; ?></span></td>
                                    <td class="timestamp-cell">
                                        <i class="bi bi-clock me-1"></i>
                                        <?php echo date('M d, Y - h:i A', strtotime($row['timestamp'])); ?>
                                    </td>
                                    <td>
                                        <?php if($row['error']): ?>
                                            <span class="error-badge"><?php echo htmlspecialchars($row['error']); ?></span>
                                        <?php else: ?>
                                            <span class="text-success"><i class="bi bi-check-circle"></i> None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewDetails(<?php echo $row['ID']; ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="banEmail('<?php echo $row['email']; ?>')">
                                            <i class="bi bi-ban"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                <?php else: ?>
                    <?php
                    $where = "";
                    if ($search) {
                        $where = "WHERE email LIKE '%$search%' OR ip_address LIKE '%$search%'";
                    }
                    
                    $count_query = "SELECT COUNT(*) as total FROM bademail $where";
                    $total_records = query_count($conn, $count_query, 'total');
                    $total_pages = ceil($total_records / $limit);
                    
                    $query = "SELECT * FROM bademail $where ORDER BY ID DESC LIMIT $offset, $limit";
                    $result = query_result($conn, $query);
                    ?>
                    
                    <div class="table-responsive">
                        <table class="table custom-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Email</th>
                                    <th>IP Address</th>
                                    <th>Date & Time</th>
                                    <th>Referrer</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result): while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><strong>#<?php echo $row['ID']; ?></strong></td>
                                    <td><span class="badge-email"><?php echo htmlspecialchars($row['email']); ?></span></td>
                                    <td><span class="badge-ip"><?php echo $row['ip_address']; ?></span></td>
                                    <td class="timestamp-cell">
                                        <i class="bi bi-clock me-1"></i>
                                        <?php echo date('M d, Y - h:i A', strtotime($row['timestamp'])); ?>
                                    </td>
                                    <td>
                                        <?php if($row['referrer']): ?>
                                            <small class="text-muted"><?php echo htmlspecialchars(substr($row['referrer'], 0, 50)); ?>...</small>
                                        <?php else: ?>
                                            <span class="text-muted">Direct</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-success" onclick="unbanEmail('<?php echo $row['email']; ?>')">
                                            <i class="bi bi-check-circle"></i> Unban
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?view=<?php echo $view; ?>&page=<?php echo $page-1; ?>&search=<?php echo $search; ?>">
                                Previous
                            </a>
                        </li>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?view=<?php echo $view; ?>&page=<?php echo $i; ?>&search=<?php echo $search; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?view=<?php echo $view; ?>&page=<?php echo $page+1; ?>&search=<?php echo $search; ?>">
                                Next
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }
        
        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }
        
        function viewDetails(id) {
            alert('View details for lead #' + id + ' - You can implement a modal or redirect here');
        }
        
        function banEmail(email) {
            if (confirm('Are you sure you want to ban this email: ' + email + '?')) {
                // Implement ban functionality
                alert('Email banned: ' + email + ' - Implement the backend functionality');
            }
        }
        
        function unbanEmail(email) {
            if (confirm('Are you sure you want to unban this email: ' + email + '?')) {
                // Implement unban functionality
                alert('Email unbanned: ' + email + ' - Implement the backend functionality');
            }
        }
        
        // Auto-refresh every 60 seconds (optional)
        // setTimeout(function() { location.reload(); }, 60000);
    </script>
</body>
</html>