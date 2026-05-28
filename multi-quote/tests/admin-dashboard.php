<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead Management System - Admin Dashboard</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* CSS Variables for Theming */
        :root[data-theme="light"] {
            --primary-color: #005D75;
            --secondary-color: #00B9E9;
            --success-color: #1B8335;
            --danger-color: #B2282E;
            --warning-color: #FF8300;
            --info-color: #17a2b8;

            --bg-primary: #ffffff;
            --bg-secondary: #f8f9fa;
            --bg-tertiary: #e9ecef;
            --bg-sidebar: linear-gradient(135deg, #005D75 0%, #00B9E9 100%);

            --text-primary: #212529;
            --text-secondary: #6c757d;
            --text-muted: #adb5bd;
            --text-inverse: #ffffff;

            --border-color: #dee2e6;
            --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 5px 20px rgba(0, 0, 0, 0.15);
            --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        :root[data-theme="dark"] {
            --primary-color: #00B9E9;
            --secondary-color: #005D75;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --info-color: #3498db;

            --bg-primary: #1a1d23;
            --bg-secondary: #22252c;
            --bg-tertiary: #2a2d35;
            --bg-sidebar: linear-gradient(135deg, #1a1d23 0%, #2a2d35 100%);

            --text-primary: #e9ecef;
            --text-secondary: #adb5bd;
            --text-muted: #6c757d;
            --text-inverse: #ffffff;

            --border-color: #343a40;
            --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 5px 20px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        body {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            overflow-x: hidden;
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-secondary);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--text-muted);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--text-secondary);
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: var(--bg-sidebar);
            box-shadow: var(--shadow-md);
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h4 {
            color: var(--text-inverse);
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .sidebar .nav {
            padding: 20px 0;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            margin: 5px 15px;
            border-radius: 8px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            font-weight: 500;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }

        .sidebar .nav-link i {
            width: 24px;
            margin-right: 12px;
            font-size: 18px;
        }

        /* Theme Toggle Button */
        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
            background: var(--bg-primary);
            border: 2px solid var(--border-color);
            border-radius: 50px;
            padding: 8px 16px;
            cursor: pointer;
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-primary);
            transition: all 0.3s;
        }

        .theme-toggle:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-md);
        }

        .theme-toggle i {
            font-size: 18px;
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1100;
            background: var(--bg-primary);
            border: 2px solid var(--border-color);
            border-radius: 8px;
            padding: 10px;
            cursor: pointer;
            box-shadow: var(--shadow-sm);
            color: var(--text-primary);
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
            padding-top: 80px;
        }

        /* Cards */
        .stat-card {
            background: var(--bg-primary);
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow-sm);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .stat-card .stat-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 24px;
        }

        .stat-card.primary .stat-icon {
            background-color: rgba(0, 185, 233, 0.1);
            color: var(--primary-color);
        }

        .stat-card.success .stat-icon {
            background-color: rgba(27, 131, 53, 0.1);
            color: var(--success-color);
        }

        .stat-card.danger .stat-icon {
            background-color: rgba(178, 40, 46, 0.1);
            color: var(--danger-color);
        }

        .stat-card.warning .stat-icon {
            background-color: rgba(255, 131, 0, 0.1);
            color: var(--warning-color);
        }

        .stat-card h6 {
            color: var(--text-secondary);
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card h3 {
            color: var(--text-primary);
            font-weight: 700;
            margin-top: 5px;
        }

        /* Data Tables */
        .data-table-container {
            background: var(--bg-primary);
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }

        .data-table-container h5 {
            color: var(--text-primary);
            margin-bottom: 20px;
            font-weight: 600;
        }

        .table {
            color: var(--text-primary);
        }

        .table thead th {
            background-color: var(--bg-tertiary);
            color: var(--text-primary);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border-color);
        }

        .table tbody td {
            color: var(--text-primary);
            border-bottom: 1px solid var(--border-color);
            padding: 12px;
        }

        .table tbody tr:hover {
            background-color: var(--bg-secondary);
        }

        /* Badges */
        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 12px;
        }

        .badge-success {
            background-color: rgba(27, 131, 53, 0.15);
            color: var(--success-color);
        }

        .badge-error {
            background-color: rgba(178, 40, 46, 0.15);
            color: var(--danger-color);
        }

        .badge-pending {
            background-color: rgba(255, 131, 0, 0.15);
            color: var(--warning-color);
        }

        /* Forms */
        .form-control,
        .form-select {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            transition: all 0.3s;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: var(--bg-primary);
            border-color: var(--primary-color);
            color: var(--text-primary);
            box-shadow: 0 0 0 0.25rem rgba(0, 185, 233, 0.25);
        }

        /* Buttons */
        .btn {
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        /* Charts */
        .chart-container {
            background: var(--bg-primary);
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
            height: 400px;
        }

        .chart-container h5 {
            color: var(--text-primary);
            margin-bottom: 20px;
            font-weight: 600;
        }

        /* Filter Section */
        .filter-section {
            background: var(--bg-primary);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }

        .filter-section label {
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 8px;
        }

        /* Modals */
        .modal-content {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .modal-header {
            background-color: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
        }

        .modal-footer {
            background-color: var(--bg-secondary);
            border-top: 1px solid var(--border-color);
        }

        .lead-details .card {
            border: 1px solid var(--border-color);
        }

        .lead-details .card-header {
            font-weight: 600;
        }

        .lead-details details summary {
            cursor: pointer;
            user-select: none;
        }

        .lead-details details summary:hover {
            text-decoration: underline;
        }

        .lead-details pre {
            font-size: 0.85rem;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
        }

        /* Loading Spinner */
        .loading-spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            background: var(--bg-primary);
            padding: 30px;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
        }

        .loading-spinner.show {
            display: block;
        }

        /* Action Buttons */
        .action-btn {
            background: transparent;
            border: none;
            color: var(--text-secondary);
            padding: 5px 10px;
            margin: 0 2px;
            border-radius: 5px;
            transition: all 0.3s;
            cursor: pointer;
        }

        .action-btn:hover {
            background: var(--bg-secondary);
            color: var(--primary-color);
        }

        .action-btn.danger:hover {
            color: var(--danger-color);
        }

        .action-btn.success:hover {
            color: var(--success-color);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 20px;
                padding-top: 70px;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .theme-toggle {
                right: 10px;
                top: 70px;
            }

            .stat-card {
                margin-bottom: 15px;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .page-section {
            animation: fadeIn 0.5s ease;
        }

        /* Utility Classes */
        .text-muted {
            color: var(--text-muted) !important;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .text-success {
            color: var(--success-color) !important;
        }

        .text-danger {
            color: var(--danger-color) !important;
        }

        .text-warning {
            color: var(--warning-color) !important;
        }
    </style>
</head>

<body>
    <!-- Loading Spinner -->
    <div class="loading-spinner" id="loadingSpinner">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 mb-0">Loading data...</p>
    </div>

    <!-- Theme Toggle Button -->
    <button class="theme-toggle" onclick="toggleTheme()">
        <i class="fas fa-sun" id="themeIcon"></i>
        <span id="themeText">Light</span>
    </button>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <nav class="sidebar" id="sidebar">
                <div class="sidebar-header">
                    <h4><i class="fas fa-chart-line"></i> Lead Manager</h4>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#dashboard" data-page="dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#buffer" data-page="buffer">
                            <i class="fas fa-clock"></i> Lead Buffer
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#history" data-page="history">
                            <i class="fas fa-history"></i> Lead History
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#blacklist" data-page="blacklist">
                            <i class="fas fa-ban"></i> Blacklist
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#resubmission" data-page="resubmission">
                            <i class="fas fa-redo"></i> Resubmission
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#analytics" data-page="analytics">
                            <i class="fas fa-chart-bar"></i> Analytics
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#activity" data-page="activity">
                            <i class="fas fa-list"></i> Activity Log
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#settings" data-page="settings">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Dashboard Section -->
                <div id="dashboard-section" class="page-section">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">Dashboard Overview</h2>
                        <button class="btn btn-primary" onclick="refreshDashboard()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>

                    <!-- Stats Row -->
                    <div class="row">
                        <div class="col-xl-3 col-lg-6 col-md-6">
                            <div class="stat-card primary">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="text-muted mb-1">Total Leads Today</h6>
                                        <h3 class="mb-0" id="stat-total-today">0</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-lg-6 col-md-6">
                            <div class="stat-card success">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="text-muted mb-1">Successful</h6>
                                        <h3 class="mb-0" id="stat-successful">0</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-lg-6 col-md-6">
                            <div class="stat-card danger">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon">
                                        <i class="fas fa-times-circle"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="text-muted mb-1">Failed</h6>
                                        <h3 class="mb-0" id="stat-failed">0</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-lg-6 col-md-6">
                            <div class="stat-card warning">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon">
                                        <i class="fas fa-hourglass-half"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="text-muted mb-1">Pending</h6>
                                        <h3 class="mb-0" id="stat-pending">0</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row mt-4">
                        <div class="col-lg-8">
                            <div class="chart-container">
                                <h5 class="mb-3">Submission Trends (Last 7 Days)</h5>
                                <canvas id="trendChart" style="max-height: 300px;"></canvas>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="chart-container">
                                <h5 class="mb-3">Status Distribution</h5>
                                <canvas id="statusChart" style="max-height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="data-table-container mt-4">
                        <h5 class="mb-3">Recent Submissions</h5>
                        <div class="table-responsive">
                            <table id="recentTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Lead ID</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="recentTableBody">
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Other sections (hidden by default) -->
                <div id="buffer-section" class="page-section" style="display: none;">
                    <h2 class="mb-4">Lead Buffer Management</h2>
                    <div class="filter-section">
                        <p>Buffer management content will be loaded here...</p>
                    </div>
                </div>

                <div id="history-section" class="page-section" style="display: none;">
                    <h2 class="mb-4">Lead History</h2>
                    <div class="data-table-container">
                        <p>History content will be loaded here...</p>
                    </div>
                </div>

                <div id="blacklist-section" class="page-section" style="display: none;">
                    <h2 class="mb-4">Blacklist Management</h2>
                    <div class="filter-section">
                        <p>Blacklist management content will be loaded here...</p>
                    </div>
                </div>

                <div id="resubmission-section" class="page-section" style="display: none;">
                    <h2 class="mb-4">Resubmission Queue</h2>
                    <div class="data-table-container">
                        <p>Resubmission queue content will be loaded here...</p>
                    </div>
                </div>

                <div id="analytics-section" class="page-section" style="display: none;">
                    <h2 class="mb-4">Analytics & Reports</h2>
                    <div class="chart-container">
                        <p>Analytics content will be loaded here...</p>
                    </div>
                </div>

                <div id="activity-section" class="page-section" style="display: none;">
                    <h2 class="mb-4">Activity Log</h2>
                    <div class="data-table-container">
                        <p>Activity log content will be loaded here...</p>
                    </div>
                </div>

                <div id="settings-section" class="page-section" style="display: none;">
                    <h2 class="mb-4">System Settings</h2>
                    <div class="filter-section">
                        <h5>API Configuration</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Boberdoo API URL</label>
                                <input type="text" class="form-control" value="https://infinixmedia.leadportal.com/genericPostlead.php" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">API Status</label>
                                <div class="mt-2">
                                    <span class="badge bg-success">Connected</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="leadDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user-circle"></i> Lead Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="leadDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Initialize on DOM ready
        $(document).ready(function() {
            // Load saved theme
            loadTheme();

            // Initialize dashboard
            initializeDashboard();

            // Setup navigation
            setupNavigation();

            // Load initial data
            loadDashboardData();

            // Setup auto-refresh (optional)
            // setInterval(loadDashboardData, 30000);
        });

        // Theme Management
        function loadTheme() {
            const savedTheme = localStorage.getItem('dashboardTheme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            updateThemeIcon(savedTheme);
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';

            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('dashboardTheme', newTheme);
            updateThemeIcon(newTheme);

            // Reinitialize charts with new theme
            if (window.trendChart) {
                updateChartTheme(window.trendChart);
            }
            if (window.statusChart) {
                updateChartTheme(window.statusChart);
            }
        }

        function updateThemeIcon(theme) {
            const icon = document.getElementById('themeIcon');
            const text = document.getElementById('themeText');

            if (theme === 'dark') {
                icon.className = 'fas fa-moon';
                text.textContent = 'Dark';
            } else {
                icon.className = 'fas fa-sun';
                text.textContent = 'Light';
            }
        }

        // Sidebar Toggle (Mobile)
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Navigation Setup
        function setupNavigation() {
            $('.nav-link').on('click', function(e) {
                e.preventDefault();

                // Update active state
                $('.nav-link').removeClass('active');
                $(this).addClass('active');

                // Hide all sections
                $('.page-section').hide();

                // Show selected section
                const page = $(this).data('page');
                $(`#${page}-section`).show();

                // Close sidebar on mobile
                if (window.innerWidth <= 768) {
                    $('#sidebar').removeClass('show');
                }

                // Load section-specific data
                loadSectionData(page);
            });
        }

        // Initialize Dashboard
        function initializeDashboard() {
            // Initialize trend chart
            const trendCtx = document.getElementById('trendChart');
            if (trendCtx) {
                window.trendChart = new Chart(trendCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [{
                            label: 'Successful',
                            data: [],
                            borderColor: 'rgb(27, 131, 53)',
                            backgroundColor: 'rgba(27, 131, 53, 0.1)',
                            tension: 0.3
                        }, {
                            label: 'Failed',
                            data: [],
                            borderColor: 'rgb(178, 40, 46)',
                            backgroundColor: 'rgba(178, 40, 46, 0.1)',
                            tension: 0.3
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
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Initialize status chart
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                window.statusChart = new Chart(statusCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Success', 'Failed', 'Pending'],
                        datasets: [{
                            data: [0, 0, 0],
                            backgroundColor: [
                                'rgba(27, 131, 53, 0.8)',
                                'rgba(178, 40, 46, 0.8)',
                                'rgba(255, 131, 0, 0.8)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
        }

        // Load Dashboard Data - FIXED FOR YOUR API STRUCTURE
        function loadDashboardData() {
            console.log('Loading dashboard data...');
            showLoading();

            // Load statistics
            $.ajax({
                url: 'inc/api/get-stats.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Stats response:', response);

                    // Your API returns data in response.data
                    if (response && response.success && response.data) {
                        updateStatistics(response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Failed to load statistics:', error);
                    // Set default values
                    updateStatistics({
                        total: 0,
                        successful: 0,
                        failed: 0,
                        pending: 0
                    });
                }
            });

            // Load recent leads
            $.ajax({
                url: 'inc/api/get-recent.php',
                method: 'GET',
                data: {
                    limit: 10
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Recent leads response:', response);

                    // Your API returns data in response.data
                    if (response && response.success && response.data) {
                        updateRecentTable(response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Failed to load recent leads:', error);
                    updateRecentTable([]);
                },
                complete: function() {
                    hideLoading();
                }
            });

            // Load trend data
            $.ajax({
                url: 'inc/api/get-trends.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Trends response:', response);

                    // Your API returns data in response.data
                    if (response && response.success && response.data) {
                        updateTrendChart(response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Failed to load trends:', error);
                }
            });
        }

        // Update Statistics - FIXED FOR YOUR DATA STRUCTURE
        function updateStatistics(stats) {
            // Your API uses 'total' not 'total_today'
            $('#stat-total-today').text(stats.total || 0);
            $('#stat-successful').text(stats.successful || 0);
            $('#stat-failed').text(stats.failed || 0);
            $('#stat-pending').text(stats.pending || 0);

            // Update status chart
            if (window.statusChart) {
                window.statusChart.data.datasets[0].data = [
                    stats.successful || 0,
                    stats.failed || 0,
                    stats.pending || 0
                ];
                window.statusChart.update();
            }
        }

        // Update Recent Table
        function updateRecentTable(leads) {
            const tbody = $('#recentTableBody');
            tbody.empty();

            // Ensure leads is an array
            if (!Array.isArray(leads)) {
                console.warn('Leads is not an array:', leads);
                leads = [];
            }

            if (leads.length === 0) {
                tbody.html('<tr><td colspan="6" class="text-center text-muted">No recent leads found</td></tr>');
                return;
            }

            leads.forEach(function(lead) {
                // Safely access properties with fallbacks
                const leadId = lead.lead_id || lead.id || 'N/A';
                const email = lead.email || 'N/A';
                const phone = lead.phone || lead.primary_phone || 'N/A';
                const status = lead.boberdoo_status || lead.status || 'pending';
                const createdAt = lead.created_at || lead.submission_time || 'Unknown';

                const statusBadge = getStatusBadge(status);
                const row = `
                    <tr>
                        <td>${formatTime(createdAt)}</td>
                        <td>${leadId}</td>
                        <td>${email}</td>
                        <td>${phone}</td>
                        <td>${statusBadge}</td>
                        <td>
                            <button class="action-btn" onclick="viewLeadDetails('${leadId}')" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="action-btn success" onclick="resubmitLead('${leadId}')" title="Resubmit">
                                <i class="fas fa-redo"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });
        }

        // Update Trend Chart - FIXED FOR YOUR DATA
        function updateTrendChart(trends) {
            if (!window.trendChart) return;

            // Ensure trends is an array
            if (!Array.isArray(trends)) {
                console.warn('Trends is not an array:', trends);
                return;
            }

            if (trends.length > 0) {
                const labels = trends.map(t => t.date || t.day || 'Day');
                const successful = trends.map(t => parseInt(t.successful || t.success || 0));
                const failed = trends.map(t => parseInt(t.failed || t.error || 0));

                window.trendChart.data.labels = labels;
                window.trendChart.data.datasets[0].data = successful;
                window.trendChart.data.datasets[1].data = failed;
                window.trendChart.update();
            }
        }

        // Load Section Data
        function loadSectionData(section) {
            console.log('Loading section:', section);
            // Add section-specific loading here as needed
        }

        // Utility Functions
        function getStatusBadge(status) {
            status = (status || '').toLowerCase();
            const badges = {
                'success': '<span class="badge badge-status badge-success">Success</span>',
                'error': '<span class="badge badge-status badge-error">Error</span>',
                'pending': '<span class="badge badge-status badge-pending">Pending</span>',
                'failed': '<span class="badge badge-status badge-error">Failed</span>',
                'resubmitted': '<span class="badge badge-status badge-warning">Resubmitted</span>'
            };
            return badges[status] || `<span class="badge badge-status">${status}</span>`;
        }

        function formatTime(datetime) {
            if (!datetime || datetime === 'Unknown') return 'N/A';

            try {
                const date = new Date(datetime);
                const now = new Date();
                const diff = Math.floor((now - date) / 1000); // seconds

                if (isNaN(diff)) return datetime;

                if (diff < 60) return 'Just now';
                if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
                if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';

                return date.toLocaleDateString();
            } catch (e) {
                return datetime;
            }
        }

        function showLoading() {
            $('#loadingSpinner').addClass('show');
        }

        function hideLoading() {
            $('#loadingSpinner').removeClass('show');
        }

        // Action Functions
        function refreshDashboard() {
            loadDashboardData();
            Swal.fire({
                icon: 'success',
                title: 'Dashboard Refreshed',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            });
        }

        function viewLeadDetails(leadId) {
            console.log('Loading details for lead:', leadId);

            // Show loading state in modal
            $('#leadDetailsContent').html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading lead details...</p>
        </div>
    `);

            // Show the modal
            $('#leadDetailsModal').modal('show');

            // Fetch lead details
            $.ajax({
                url: 'inc/api/get-lead-details.php',
                method: 'GET',
                data: {
                    lead_id: leadId
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Lead details response:', response);

                    if (response.success && response.data) {
                        displayLeadDetails(response.data);
                    } else {
                        $('#leadDetailsContent').html(`
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Lead not found
                        <p class="mb-0 mt-2">The lead with ID ${leadId} could not be found.</p>
                    </div>
                `);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading lead details:', error);
                    $('#leadDetailsContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-times-circle"></i> Error loading lead details
                    <p class="mb-0 mt-2">An error occurred while loading the lead information.</p>
                </div>
            `);
                }
            });
        }

        function displayLeadDetails(lead) {
            // Format status badge
            const statusBadge = getStatusBadge(lead.boberdoo_status || lead.status || 'unknown');

            // Format phone numbers
            const formatPhone = (phone) => {
                if (!phone) return 'N/A';
                const cleaned = phone.replace(/\D/g, '');
                if (cleaned.length === 10) {
                    return `(${cleaned.substr(0,3)}) ${cleaned.substr(3,3)}-${cleaned.substr(6)}`;
                }
                return phone;
            };

            // Format dates
            const formatDate = (dateStr) => {
                if (!dateStr) return 'N/A';
                const date = new Date(dateStr);
                return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
            };

            // Build the modal content
            let modalContent = `
        <div class="lead-details">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-1">${lead.full_name || lead.first_name + ' ' + lead.last_name || 'Unknown'}</h5>
                    <p class="text-muted mb-0">Lead ID: <strong>${lead.lead_id}</strong></p>
                </div>
                <div>
                    ${statusBadge}
                </div>
            </div>
            
            <!-- Contact Information -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-user"></i> Contact Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Email:</strong> ${lead.email || 'N/A'}</p>
                            <p class="mb-2"><strong>Phone:</strong> ${formatPhone(lead.phone || lead.primary_phone)}</p>
                            <p class="mb-2"><strong>Age:</strong> ${lead.age || 'N/A'}</p>
                            <p class="mb-2"><strong>Gender:</strong> ${lead.gender || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>City:</strong> ${lead.city || 'N/A'}</p>
                            <p class="mb-2"><strong>State:</strong> ${lead.state || 'N/A'}</p>
                            <p class="mb-2"><strong>ZIP:</strong> ${lead.zip || 'N/A'}</p>
                            <p class="mb-2"><strong>Location:</strong> ${lead.location || 'N/A'}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Submission Details -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-clock"></i> Submission Details</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Created:</strong> ${formatDate(lead.created_at)}</p>
                            <p class="mb-2"><strong>Source:</strong> ${lead.src || lead.source || 'N/A'}</p>
                            <p class="mb-2"><strong>Campaign:</strong> ${lead.campaign || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>IP Address:</strong> ${lead.ip_address || 'N/A'}</p>
                            <p class="mb-2"><strong>Resubmit Count:</strong> ${lead.resubmit_count || 0}</p>
                            <p class="mb-2"><strong>Data Source:</strong> <span class="badge bg-info">${lead.source_table || 'Unknown'}</span></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Boberdoo Response -->
            ${lead.boberdoo_response ? `
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-exchange-alt"></i> Boberdoo Response</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Status:</strong> ${lead.boberdoo_status || 'N/A'}</p>
                        <p class="mb-2"><strong>Lead ID:</strong> ${lead.boberdoo_lead_id || 'N/A'}</p>
                        ${lead.boberdoo_error ? `<p class="mb-2 text-danger"><strong>Error:</strong> ${lead.boberdoo_error}</p>` : ''}
                        ${lead.boberdoo_response ? `
                            <details>
                                <summary class="cursor-pointer text-primary">View Raw Response</summary>
                                <pre class="mt-2 p-2 bg-light rounded" style="max-height: 200px; overflow-y: auto;">${JSON.stringify(lead.boberdoo_response_parsed || lead.boberdoo_response, null, 2)}</pre>
                            </details>
                        ` : ''}
                    </div>
                </div>
            ` : ''}
            
            <!-- Blacklist Status -->
            ${lead.blacklist_status && lead.blacklist_status.is_blacklisted ? `
                <div class="card mb-3 border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="fas fa-ban"></i> Blacklist Status</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Status:</strong> <span class="badge bg-warning text-dark">Blacklisted</span></p>
                        <p class="mb-2"><strong>Type:</strong> ${lead.blacklist_status.is_permanent ? 'Permanent' : 'Temporary'}</p>
                        ${lead.blacklist_status.block_until ? `<p class="mb-2"><strong>Blocked Until:</strong> ${formatDate(lead.blacklist_status.block_until)}</p>` : ''}
                        <p class="mb-2"><strong>Submission Count:</strong> ${lead.blacklist_status.submission_count || 0}</p>
                        ${lead.blacklist_status.blacklist_reason ? `<p class="mb-2"><strong>Reason:</strong> ${lead.blacklist_status.blacklist_reason}</p>` : ''}
                    </div>
                </div>
            ` : ''}
            
            <!-- Queue Status -->
            ${lead.queue_status ? `
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-list"></i> Queue Status</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Status:</strong> <span class="badge bg-info">${lead.queue_status.status}</span></p>
                        <p class="mb-2"><strong>Priority:</strong> ${lead.queue_status.priority || 'Normal'}</p>
                        <p class="mb-2"><strong>Scheduled For:</strong> ${formatDate(lead.queue_status.scheduled_for)}</p>
                        <p class="mb-2"><strong>Attempts:</strong> ${lead.queue_status.attempt_count || 0}</p>
                    </div>
                </div>
            ` : ''}
            
            <!-- Action Buttons -->
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-success" onclick="resubmitLead('${lead.lead_id}')">
                    <i class="fas fa-redo"></i> Resubmit Lead
                </button>
                ${lead.blacklist_status && lead.blacklist_status.is_blacklisted ? `
                    <button class="btn btn-warning" onclick="removeFromBlacklist('${lead.email}')">
                        <i class="fas fa-unlock"></i> Remove from Blacklist
                    </button>
                ` : `
                    <button class="btn btn-warning" onclick="addToBlacklist('${lead.email}')">
                        <i class="fas fa-ban"></i> Add to Blacklist
                    </button>
                `}
                <button class="btn btn-info" onclick="exportLeadData('${lead.lead_id}')">
                    <i class="fas fa-download"></i> Export Data
                </button>
            </div>
        </div>
    `;

            // Update modal content
            $('#leadDetailsContent').html(modalContent);
        }

        // Additional helper functions
        function removeFromBlacklist(email) {
            if (confirm('Remove this email from the blacklist?')) {
                $.ajax({
                    url: 'inc/api/remove-blacklist.php',
                    method: 'POST',
                    data: {
                        email: email
                    },
                    success: function(response) {
                        Swal.fire('Success', 'Email removed from blacklist', 'success');
                        $('#leadDetailsModal').modal('hide');
                        loadDashboardData(); // Refresh dashboard
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to remove from blacklist', 'error');
                    }
                });
            }
        }

        function addToBlacklist(email) {
            if (confirm('Add this email to the blacklist?')) {
                $.ajax({
                    url: 'inc/api/add-blacklist.php',
                    method: 'POST',
                    data: {
                        email: email,
                        type: 'permanent'
                    },
                    success: function(response) {
                        Swal.fire('Success', 'Email added to blacklist', 'success');
                        $('#leadDetailsModal').modal('hide');
                        loadDashboardData(); // Refresh dashboard
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to add to blacklist', 'error');
                    }
                });
            }
        }

        function exportLeadData(leadId) {
            // Open download URL in new window
            window.open('inc/api/export-lead.php?lead_id=' + leadId, '_blank');
        }

        function resubmitLead(leadId) {
            Swal.fire({
                title: 'Resubmit Lead?',
                text: 'Are you sure you want to resubmit this lead?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1B8335',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Resubmit'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Perform resubmit
                    $.ajax({
                        url: 'inc/api/resubmit.php',
                        method: 'POST',
                        data: {
                            lead_id: leadId
                        },
                        success: function(response) {
                            Swal.fire('Success!', 'Lead has been resubmitted.', 'success');
                            loadDashboardData();
                        },
                        error: function() {
                            Swal.fire('Error!', 'Failed to resubmit lead.', 'error');
                        }
                    });
                }
            });
        }

        // Update chart theme when theme changes
        function updateChartTheme(chart) {
            if (!chart) return;

            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

            if (chart.options.plugins && chart.options.plugins.legend) {
                chart.options.plugins.legend.labels.color = isDark ? '#e9ecef' : '#212529';
            }

            if (chart.options.scales) {
                if (chart.options.scales.x) {
                    chart.options.scales.x.ticks.color = isDark ? '#adb5bd' : '#6c757d';
                    chart.options.scales.x.grid.color = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
                }
                if (chart.options.scales.y) {
                    chart.options.scales.y.ticks.color = isDark ? '#adb5bd' : '#6c757d';
                    chart.options.scales.y.grid.color = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
                }
            }

            chart.update();
        }
    </script>
</body>

</html>