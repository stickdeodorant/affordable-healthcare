<?php
/**
 * Lead Management Dashboard - Main Overview
 * Module 1: Dashboard Home with Statistics and Recent Activity
 */

session_start();

// Simple authentication check
if (!isset($_SESSION['admin_authenticated'])) {
    $_SESSION['admin_authenticated'] = true; // For testing - REMOVE IN PRODUCTION
    $_SESSION['admin_user'] = 'admin';
}

require_once __DIR__ . '/inc/config/db-config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead Management Dashboard</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary-color: #2196F3;
            --success-color: #4CAF50;
            --danger-color: #f44336;
            --warning-color: #ff9800;
            --info-color: #00bcd4;
            --sidebar-width: 260px;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .sidebar-nav {
            padding: 20px 0;
        }
        
        .nav-item {
            margin: 5px 15px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        
        .nav-link i {
            width: 24px;
            margin-right: 10px;
            font-size: 1.1rem;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
            min-height: 100vh;
        }
        
        /* Header */
        .dashboard-header {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .dashboard-header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
            color: #333;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .refresh-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .refresh-btn:hover {
            background: #1976D2;
            transform: translateY(-2px);
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        
        .stat-card.success { border-left-color: var(--success-color); }
        .stat-card.danger { border-left-color: var(--danger-color); }
        .stat-card.warning { border-left-color: var(--warning-color); }
        .stat-card.info { border-left-color: var(--info-color); }
        
        .stat-card-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .stat-info h3 {
            font-size: 2.2rem;
            font-weight: 700;
            margin: 0 0 5px 0;
            color: #333;
        }
        
        .stat-info p {
            margin: 0;
            color: #666;
            font-size: 0.95rem;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.8rem;
        }
        
        .stat-icon.primary { background: rgba(33, 150, 243, 0.1); color: var(--primary-color); }
        .stat-icon.success { background: rgba(76, 175, 80, 0.1); color: var(--success-color); }
        .stat-icon.danger { background: rgba(244, 67, 54, 0.1); color: var(--danger-color); }
        .stat-icon.warning { background: rgba(255, 152, 0, 0.1); color: var(--warning-color); }
        
        /* Charts Section */
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        
        .chart-card h5 {
            margin: 0 0 20px 0;
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        /* Recent Activity Table */
        .activity-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        
        .activity-card h5 {
            margin: 0 0 20px 0;
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table {
            margin: 0;
        }
        
        .table thead th {
            background: #f8f9fa;
            border: none;
            color: #666;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            padding: 12px;
        }
        
        .table tbody td {
            padding: 12px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .table tbody tr:hover {
            background: #f8f9fa;
        }
        
        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-badge.success {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
        }
        
        .status-badge.error {
            background: rgba(244, 67, 54, 0.1);
            color: var(--danger-color);
        }
        
        .status-badge.pending {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning-color);
        }
        
        /* Loading Spinner */
        .loading-spinner {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        
        .loading-spinner.show {
            display: flex;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .btn-action {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .btn-primary { background: var(--primary-color); color: white; }
        .btn-success { background: var(--success-color); color: white; }
        .btn-warning { background: var(--warning-color); color: white; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Spinner -->
    <div class="loading-spinner" id="loadingSpinner">
        <div class="spinner"></div>
    </div>
    
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-chart-line"></i> Lead Manager</h3>
            <small>Affordable Healthcare</small>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="#dashboard" class="nav-link active" data-page="dashboard">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#buffer" class="nav-link" data-page="buffer">
                    <i class="fas fa-database"></i>
                    <span>7-Day Buffer</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#history" class="nav-link" data-page="history">
                    <i class="fas fa-history"></i>
                    <span>History</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#blacklist" class="nav-link" data-page="blacklist">
                    <i class="fas fa-ban"></i>
                    <span>Blacklist</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#resubmission" class="nav-link" data-page="resubmission">
                    <i class="fas fa-redo"></i>
                    <span>Resubmission Queue</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#analytics" class="nav-link" data-page="analytics">
                    <i class="fas fa-chart-bar"></i>
                    <span>Analytics</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#activity" class="nav-link" data-page="activity">
                    <i class="fas fa-list"></i>
                    <span>Activity Log</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="system-check.php" class="nav-link">
                    <i class="fas fa-cog"></i>
                    <span>System Check</span>
                </a>
            </div>
        </nav>
    </div>
    
    <!-- Main Content Area -->
    <div class="main-content">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div>
                <h1>Dashboard Overview</h1>
                <small class="text-muted">Real-time lead management statistics</small>
            </div>
            <div class="user-info">
                <button class="refresh-btn" onclick="refreshDashboard()">
                    <i class="fas fa-sync-alt"></i>
                    Refresh
                </button>
                <div>
                    <strong><?php echo $_SESSION['admin_user'] ?? 'Admin'; ?></strong><br>
                    <small class="text-muted" id="lastUpdate">Last updated: Just now</small>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card info">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h3 id="stat-total-today">0</h3>
                        <p>Total Submissions Today</p>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h3 id="stat-successful">0</h3>
                        <p>Successful Submissions</p>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card danger">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h3 id="stat-failed">0</h3>
                        <p>Failed Submissions</p>
                    </div>
                    <div class="stat-icon danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h3 id="stat-pending">0</h3>
                        <p>Pending Submissions</p>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <button class="btn-action btn-primary" onclick="window.location.href='creat-test-lead-fixed.php'">
                <i class="fas fa-plus"></i> Create Test Lead
            </button>
            <button class="btn-action btn-success" onclick="processQueue()">
                <i class="fas fa-play"></i> Process Queue
            </button>
            <button class="btn-action btn-warning" onclick="window.location.href='export-leads.php?date=today'">
                <i class="fas fa-download"></i> Export Today's Leads
            </button>
        </div>
        
        <!-- Charts Section -->
        <div class="charts-grid">
            <div class="chart-card">
                <h5><i class="fas fa-chart-line"></i> 7-Day Trend</h5>
                <div class="chart-container">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <h5><i class="fas fa-chart-pie"></i> Status Distribution</h5>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="activity-card">
            <h5>
                <span><i class="fas fa-clock"></i> Recent Submissions</span>
                <a href="#history" class="btn-action btn-primary btn-sm" style="font-size: 12px; padding: 6px 12px;">
                    View All
                </a>
            </h5>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Lead ID</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>State</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="recentLeadsTable">
                        <tr>
                            <td colspan="7" class="text-center">
                                <i class="fas fa-spinner fa-spin"></i> Loading recent submissions...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Global variables
        let trendChart, statusChart;
        let autoRefreshInterval;
        
        // Initialize dashboard
        $(document).ready(function() {
            initializeCharts();
            loadDashboardData();
            startAutoRefresh();
            
            // Navigation handling
            $('.nav-link').on('click', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page) {
                    navigateToPage(page);
                }
            });
        });
        
        /**
         * Initialize Charts
         */
        function initializeCharts() {
            // Trend Chart
            const trendCtx = document.getElementById('trendChart').getContext('2d');
            trendChart = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Total Submissions',
                        data: [],
                        borderColor: '#2196F3',
                        backgroundColor: 'rgba(33, 150, 243, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Successful',
                        data: [],
                        borderColor: '#4CAF50',
                        backgroundColor: 'rgba(76, 175, 80, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Failed',
                        data: [],
                        borderColor: '#f44336',
                        backgroundColor: 'rgba(244, 67, 54, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
            
            // Status Distribution Chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            statusChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Successful', 'Failed', 'Pending', 'Blacklisted'],
                    datasets: [{
                        data: [0, 0, 0, 0],
                        backgroundColor: [
                            '#4CAF50',
                            '#f44336',
                            '#ff9800',
                            '#757575'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        }
        
        /**
         * Load Dashboard Data
         */
        function loadDashboardData() {
            showLoading();
            
            // Load statistics
            $.ajax({
                url: 'inc/api/get-stats.php',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        
                        // Update stat cards
                        $('#stat-total-today').text(data.total_today || 0);
                        $('#stat-successful').text(data.successful || 0);
                        $('#stat-failed').text(data.failed || 0);
                        $('#stat-pending').text(data.pending || 0);
                        
                        // Update status chart
                        statusChart.data.datasets[0].data = [
                            data.successful || 0,
                            data.failed || 0,
                            data.pending || 0,
                            data.blacklisted || 0
                        ];
                        statusChart.update();
                    }
                },
                error: function() {
                    console.error('Failed to load statistics');
                }
            });
            
            // Load trend data
            $.ajax({
                url: 'inc/api/get-trends.php',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        
                        // Update trend chart
                        trendChart.data.labels = data.map(item => item.label || item.day);
                        trendChart.data.datasets[0].data = data.map(item => item.total || 0);
                        trendChart.data.datasets[1].data = data.map(item => item.successful || 0);
                        trendChart.data.datasets[2].data = data.map(item => item.failed || 0);
                        trendChart.update();
                    }
                },
                error: function() {
                    console.error('Failed to load trend data');
                }
            });
            
            // Load recent submissions
            $.ajax({
                url: 'inc/api/get-recent.php',
                type: 'GET',
                data: { limit: 10 },
                success: function(response) {
                    if (response.success && response.data) {
                        updateRecentTable(response.data);
                    } else {
                        $('#recentLeadsTable').html('<tr><td colspan="7" class="text-center">No recent submissions</td></tr>');
                    }
                },
                error: function() {
                    $('#recentLeadsTable').html('<tr><td colspan="7" class="text-center text-danger">Failed to load recent submissions</td></tr>');
                },
                complete: function() {
                    hideLoading();
                    updateLastUpdateTime();
                }
            });
        }
        
        /**
         * Update Recent Submissions Table
         */
        function updateRecentTable(data) {
            if (!data || data.length === 0) {
                $('#recentLeadsTable').html('<tr><td colspan="7" class="text-center">No submissions yet today</td></tr>');
                return;
            }
            
            let html = '';
            data.forEach(function(lead) {
                html += `
                    <tr>
                        <td>${lead.time_ago || formatTime(lead.created_at)}</td>
                        <td><small>${lead.lead_id || 'N/A'}</small></td>
                        <td>${lead.email || 'N/A'}</td>
                        <td>${lead.phone || lead.primary_phone || 'N/A'}</td>
                        <td>${lead.state || 'N/A'}</td>
                        <td>${getStatusBadge(lead.status || lead.boberdoo_status)}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="viewLeadDetails('${lead.lead_id}')" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            $('#recentLeadsTable').html(html);
        }
        
        /**
         * Get Status Badge HTML
         */
        function getStatusBadge(status) {
            const statusLower = (status || 'pending').toLowerCase();
            
            const badges = {
                'success': '<span class="status-badge success"><i class="fas fa-check"></i> Success</span>',
                'error': '<span class="status-badge error"><i class="fas fa-times"></i> Error</span>',
                'failed': '<span class="status-badge error"><i class="fas fa-times"></i> Failed</span>',
                'pending': '<span class="status-badge pending"><i class="fas fa-clock"></i> Pending</span>'
            };
            
            return badges[statusLower] || `<span class="status-badge">${status}</span>`;
        }
        
        /**
         * Format Time
         */
        function formatTime(datetime) {
            if (!datetime) return 'N/A';
            const date = new Date(datetime);
            return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        }
        
        /**
         * View Lead Details (placeholder)
         */
        function viewLeadDetails(leadId) {
            alert('Lead details view coming in next module! Lead ID: ' + leadId);
        }
        
        /**
         * Process Queue
         */
        function processQueue() {
            if (confirm('Process the resubmission queue now?')) {
                showLoading();
                
                // This will be implemented in the Queue module
                setTimeout(function() {
                    hideLoading();
                    alert('Queue processing will be implemented in Module 5: Resubmission Queue');
                }, 1000);
            }
        }
        
        /**
         * Refresh Dashboard
         */
        function refreshDashboard() {
            loadDashboardData();
        }
        
        /**
         * Start Auto-Refresh
         */
        function startAutoRefresh() {
            autoRefreshInterval = setInterval(function() {
                loadDashboardData();
            }, 60000); // Refresh every minute
        }
        
        /**
         * Navigate to Page
         */
        function navigateToPage(page) {
            // Update active nav
            $('.nav-link').removeClass('active');
            $(`.nav-link[data-page="${page}"]`).addClass('active');
            
            // Show message for upcoming modules
            if (page !== 'dashboard') {
                alert(`Module "${page}" will be implemented next!\n\nWe'll build it once this Dashboard Overview is tested and working.`);
            }
        }
        
        /**
         * Update Last Update Time
         */
        function updateLastUpdateTime() {
            const now = new Date();
            $('#lastUpdate').text('Last updated: ' + now.toLocaleTimeString());
        }
        
        /**
         * Loading Helpers
         */
        function showLoading() {
            $('#loadingSpinner').addClass('show');
        }
        
        function hideLoading() {
            $('#loadingSpinner').removeClass('show');
        }
    </script>
</body>
</html>