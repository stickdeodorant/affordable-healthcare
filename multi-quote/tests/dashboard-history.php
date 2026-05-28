<?php
/**
 * Lead Management Dashboard - Module 3: Lead History
 * Complete historical records with advanced search and analytics
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
    <title>Lead History - Dashboard</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Date Range Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    
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
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
            min-height: 100vh;
        }
        
        /* Page Header */
        .page-header {
            background: white;
            padding: 25px 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        
        .page-header h1 {
            margin: 0 0 5px 0;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        /* Statistics Cards */
        .history-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            border-left: 4px solid var(--primary-color);
        }
        
        .stat-card.success { border-left-color: var(--success-color); }
        .stat-card.info { border-left-color: var(--info-color); }
        .stat-card.warning { border-left-color: var(--warning-color); }
        
        .stat-card h4 {
            font-size: 2rem;
            margin: 0 0 5px 0;
            font-weight: 700;
            color: #333;
        }
        
        .stat-card p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .stat-card small {
            color: #999;
            font-size: 0.8rem;
        }
        
        /* Search & Filters Section */
        .search-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        
        .search-section h6 {
            margin: 0 0 20px 0;
            font-weight: 600;
            color: #333;
        }
        
        .search-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .search-field label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
            font-size: 0.9rem;
        }
        
        .form-control, .form-select {
            border-radius: 6px;
            border: 1px solid #ddd;
            padding: 10px 12px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
        }
        
        /* Action Buttons */
        .action-bar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn-action {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .btn-primary { background: var(--primary-color); color: white; }
        .btn-success { background: var(--success-color); color: white; }
        .btn-warning { background: var(--warning-color); color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        
        /* Data Table Card */
        .table-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        
        .table-card h5 {
            margin: 0 0 20px 0;
            font-size: 1.3rem;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        /* DataTables Styling */
        table.dataTable thead th {
            background: #f8f9fa;
            border: none;
            color: #666;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            padding: 15px 12px;
        }
        
        table.dataTable tbody td {
            padding: 12px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
        }
        
        table.dataTable tbody tr:hover {
            background: #f8f9fa;
        }
        
        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
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
        
        /* Modal Styling */
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0;
        }
        
        .modal-content {
            border-radius: 10px;
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        .lead-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .detail-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
        
        .detail-section h6 {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #dee2e6;
        }
        
        .detail-item {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            flex: 0 0 140px;
            font-weight: 500;
            color: #666;
        }
        
        .detail-value {
            flex: 1;
            color: #333;
        }
        
        /* Export Options */
        .export-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .export-section h6 {
            margin: 0 0 15px 0;
            font-weight: 600;
        }
        
        .export-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
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
        
        /* DataTables Buttons */
        .dt-buttons {
            margin-bottom: 15px;
        }
        
        .dt-button {
            background: var(--primary-color) !important;
            color: white !important;
            border: none !important;
            padding: 8px 15px !important;
            border-radius: 6px !important;
            margin-right: 5px !important;
        }
        
        .dt-button:hover {
            background: #1976D2 !important;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .history-stats {
                grid-template-columns: 1fr;
            }
            
            .search-grid {
                grid-template-columns: 1fr;
            }
            
            .lead-detail-grid {
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
    
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-chart-line"></i> Lead Manager</h3>
            <small>Affordable Healthcare</small>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="dashboard-main.php" class="nav-link">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
            <div class="nav-item">
                <a href="dashboard-buffer.php" class="nav-link">
                    <i class="fas fa-database"></i> 7-Day Buffer
                </a>
            </div>
            <div class="nav-item">
                <a href="dashboard-history.php" class="nav-link active">
                    <i class="fas fa-history"></i> History
                </a>
            </div>
            <div class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-ban"></i> Blacklist
                </a>
            </div>
            <div class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-redo"></i> Resubmission Queue
                </a>
            </div>
            <div class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-chart-bar"></i> Analytics
                </a>
            </div>
            <div class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-list"></i> Activity Log
                </a>
            </div>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-history"></i> Lead History</h1>
            <p class="text-muted mb-0">Complete historical record of all lead submissions</p>
        </div>
        
        <!-- Statistics Cards -->
        <div class="history-stats">
            <div class="stat-card">
                <h4 id="total-leads">0</h4>
                <p>Total Leads</p>
                <small>All-time submissions</small>
            </div>
            
            <div class="stat-card success">
                <h4 id="conversion-rate">0%</h4>
                <p>Conversion Rate</p>
                <small>Success ratio</small>
            </div>
            
            <div class="stat-card info">
                <h4 id="avg-response-time">0ms</h4>
                <p>Avg Response Time</p>
                <small>API performance</small>
            </div>
            
            <div class="stat-card warning">
                <h4 id="total-revenue">$0</h4>
                <p>Total Revenue</p>
                <small>Estimated value</small>
            </div>
        </div>
        
        <!-- Search & Filters -->
        <div class="search-section">
            <h6><i class="fas fa-search"></i> Search & Filter History</h6>
            
            <div class="search-grid">
                <div class="search-field">
                    <label>Date Range</label>
                    <input type="text" class="form-control" id="dateRange" placeholder="Select date range">
                </div>
                
                <div class="search-field">
                    <label>Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="success">Success</option>
                        <option value="error">Error</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                
                <div class="search-field">
                    <label>State</label>
                    <select class="form-select" id="stateFilter">
                        <option value="">All States</option>
                        <option value="CA">California</option>
                        <option value="TX">Texas</option>
                        <option value="FL">Florida</option>
                        <option value="NY">New York</option>
                        <option value="PA">Pennsylvania</option>
                        <!-- Add more states as needed -->
                    </select>
                </div>
                
                <div class="search-field">
                    <label>Campaign</label>
                    <input type="text" class="form-control" id="campaignFilter" placeholder="Campaign name">
                </div>
            </div>
            
            <div class="action-bar">
                <button class="btn-action btn-primary" onclick="applySearch()">
                    <i class="fas fa-search"></i> Search
                </button>
                <button class="btn-action btn-secondary" onclick="resetSearch()">
                    <i class="fas fa-undo"></i> Reset
                </button>
                <button class="btn-action btn-success" onclick="exportData('csv')">
                    <i class="fas fa-file-csv"></i> Export CSV
                </button>
                <button class="btn-action btn-warning" onclick="exportData('excel')">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>
        </div>
        
        <!-- Data Table -->
        <div class="table-card">
            <h5>
                <span><i class="fas fa-table"></i> Historical Records</span>
                <button class="btn btn-sm btn-primary" onclick="refreshTable()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </h5>
            
            <div class="table-responsive">
                <table id="historyTable" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Lead ID</th>
                            <th>Date/Time</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>State</th>
                            <th>Campaign</th>
                            <th>Status</th>
                            <th>Response Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Lead Details Modal -->
    <div class="modal fade" id="leadDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-info-circle"></i> Lead Details - <span id="modalLeadId"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="leadDetailsContent">
                    <!-- Content loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="downloadLeadReport()">
                        <i class="fas fa-download"></i> Download Report
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    
    <script>
        // Global variables
        let historyTable;
        let currentLeadId = null;
        let dateRangeStart = null;
        let dateRangeEnd = null;
        
        // Initialize
        $(document).ready(function() {
            initializeDateRange();
            initializeTable();
            loadStatistics();
        });
        
        /**
         * Initialize Date Range Picker
         */
        function initializeDateRange() {
            $('#dateRange').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'MM/DD/YYYY'
                },
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            });
            
            $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
                dateRangeStart = picker.startDate.format('YYYY-MM-DD');
                dateRangeEnd = picker.endDate.format('YYYY-MM-DD');
            });
            
            $('#dateRange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                dateRangeStart = null;
                dateRangeEnd = null;
            });
        }
        
        /**
         * Initialize DataTable with Server-Side Processing
         */
        function initializeTable() {
            historyTable = $('#historyTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: 'inc/api/get-history.php',
                    type: 'POST',
                    data: function(d) {
                        // Add custom filters
                        d.status = $('#statusFilter').val();
                        d.state = $('#stateFilter').val();
                        d.campaign = $('#campaignFilter').val();
                        d.start_date = dateRangeStart;
                        d.end_date = dateRangeEnd;
                    }
                },
                columns: [
                    { data: 'lead_id', render: function(data) {
                        return '<small>' + (data || 'N/A') + '</small>';
                    }},
                    { data: 'timestamp' },
                    { data: 'name' },
                    { data: 'email' },
                    { data: 'phone' },
                    { data: 'state' },
                    { data: 'campaign', render: function(data) {
                        return data || 'N/A';
                    }},
                    { data: 'status', render: function(data) {
                        return getStatusBadge(data);
                    }},
                    { data: 'response_time' },
                    { data: 'id', render: function(data, type, row) {
                        return `
                            <button class="btn btn-sm btn-primary" onclick="viewLeadDetails('${row.id}')" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                        `;
                    }}
                ],
                pageLength: 50,
                order: [[1, 'desc']], // Sort by date
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'copy',
                        text: '<i class="fas fa-copy"></i> Copy',
                        className: 'btn-sm'
                    },
                    {
                        extend: 'csv',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        className: 'btn-sm'
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn-sm'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Print',
                        className: 'btn-sm'
                    }
                ],
                language: {
                    emptyTable: "No historical records found",
                    loadingRecords: "Loading history...",
                    processing: "Processing...",
                    search: "Search:",
                    lengthMenu: "Show _MENU_ records per page"
                }
            });
        }
        
        /**
         * Load Statistics
         */
        function loadStatistics() {
            $.ajax({
                url: 'inc/api/get-history-stats.php',
                type: 'GET',
                success: function(response) {
                    if (response.success && response.data) {
                        $('#total-leads').text(formatNumber(response.data.total || 0));
                        $('#conversion-rate').text((response.data.conversionRate || 0) + '%');
                        $('#avg-response-time').text((response.data.avgResponseTime || 0) + 'ms');
                        $('#total-revenue').text('$' + formatNumber(response.data.totalRevenue || 0));
                    }
                },
                error: function() {
                    console.error('Failed to load statistics');
                }
            });
        }
        
        /**
         * Get Status Badge HTML
         */
        function getStatusBadge(status) {
            const statusLower = (status || 'unknown').toLowerCase();
            
            const badges = {
                'success': '<span class="status-badge success"><i class="fas fa-check"></i> Success</span>',
                'error': '<span class="status-badge error"><i class="fas fa-times"></i> Error</span>',
                'failed': '<span class="status-badge error"><i class="fas fa-times"></i> Failed</span>',
                'pending': '<span class="status-badge pending"><i class="fas fa-clock"></i> Pending</span>'
            };
            
            return badges[statusLower] || `<span class="status-badge">${status}</span>`;
        }
        
        /**
         * View Lead Details
         */
        function viewLeadDetails(leadId) {
            currentLeadId = leadId;
            showLoading();
            
            $.ajax({
                url: 'inc/api/get-lead-details.php',
                type: 'GET',
                data: { id: leadId },
                success: function(response) {
                    if (response.success) {
                        displayLeadDetails(response.data);
                    } else {
                        Swal.fire('Error', 'Lead not found', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to load lead details', 'error');
                },
                complete: function() {
                    hideLoading();
                }
            });
        }
        
        /**
         * Display Lead Details in Modal
         */
        function displayLeadDetails(lead) {
            $('#modalLeadId').text(lead.lead_id);
            
            let html = `
                <div class="lead-detail-grid">
                    <div class="detail-section">
                        <h6><i class="fas fa-user"></i> Personal Information</h6>
                        <div class="detail-item">
                            <div class="detail-label">Full Name:</div>
                            <div class="detail-value">${lead.first_name} ${lead.last_name}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Email:</div>
                            <div class="detail-value">${lead.email}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Phone:</div>
                            <div class="detail-value">${lead.phone || lead.primary_phone || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Age:</div>
                            <div class="detail-value">${lead.age || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Gender:</div>
                            <div class="detail-value">${lead.gender || 'N/A'}</div>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h6><i class="fas fa-map-marker-alt"></i> Location</h6>
                        <div class="detail-item">
                            <div class="detail-label">City:</div>
                            <div class="detail-value">${lead.city || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">State:</div>
                            <div class="detail-value">${lead.state || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">ZIP Code:</div>
                            <div class="detail-value">${lead.zip || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">IP Address:</div>
                            <div class="detail-value">${lead.ip_address || 'N/A'}</div>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h6><i class="fas fa-file-alt"></i> Submission Details</h6>
                        <div class="detail-item">
                            <div class="detail-label">Lead ID:</div>
                            <div class="detail-value"><small>${lead.lead_id}</small></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Submitted:</div>
                            <div class="detail-value">${lead.submission_timestamp || lead.created_at}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Source:</div>
                            <div class="detail-value">${lead.source || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Campaign:</div>
                            <div class="detail-value">${lead.campaign || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Type:</div>
                            <div class="detail-value">${lead.type || 'N/A'}</div>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h6><i class="fas fa-server"></i> Boberdoo Response</h6>
                        <div class="detail-item">
                            <div class="detail-label">Status:</div>
                            <div class="detail-value">${getStatusBadge(lead.boberdoo_status)}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Response Code:</div>
                            <div class="detail-value">${lead.boberdoo_response_code || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Boberdoo Lead ID:</div>
                            <div class="detail-value">${lead.boberdoo_lead_id || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Response Time:</div>
                            <div class="detail-value">${lead.response_time_ms || 0}ms</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Price:</div>
                            <div class="detail-value">$${lead.boberdoo_price || '0.00'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Buyer:</div>
                            <div class="detail-value">${lead.boberdoo_buyer || 'N/A'}</div>
                        </div>
                    </div>
                </div>
            `;
            
            // Add error message if exists
            if (lead.boberdoo_error_message) {
                html += `
                    <div class="alert alert-danger mt-3">
                        <h6><i class="fas fa-exclamation-triangle"></i> Error Message</h6>
                        <p class="mb-0">${lead.boberdoo_error_message}</p>
                    </div>
                `;
            }
            
            // Add blacklist status if applicable
            if (lead.is_blacklisted) {
                html += `
                    <div class="alert alert-warning mt-3">
                        <h6><i class="fas fa-ban"></i> Blacklist Status</h6>
                        <p class="mb-0">This lead was blacklisted during submission.</p>
                    </div>
                `;
            }
            
            $('#leadDetailsContent').html(html);
            $('#leadDetailsModal').modal('show');
        }
        
        /**
         * Apply Search Filters
         */
        function applySearch() {
            historyTable.ajax.reload();
        }
        
        /**
         * Reset Search Filters
         */
        function resetSearch() {
            $('#statusFilter').val('');
            $('#stateFilter').val('');
            $('#campaignFilter').val('');
            $('#dateRange').val('');
            dateRangeStart = null;
            dateRangeEnd = null;
            historyTable.ajax.reload();
        }
        
        /**
         * Refresh Table
         */
        function refreshTable() {
            historyTable.ajax.reload();
            loadStatistics();
        }
        
        /**
         * Export Data
         */
        function exportData(format) {
            const params = new URLSearchParams({
                format: format,
                status: $('#statusFilter').val(),
                state: $('#stateFilter').val(),
                campaign: $('#campaignFilter').val(),
                start_date: dateRangeStart || '',
                end_date: dateRangeEnd || ''
            });
            
            window.location.href = `inc/api/export-leads.php?${params.toString()}`;
            
            Swal.fire({
                icon: 'success',
                title: 'Export Started',
                text: 'Your download will begin shortly',
                timer: 2000,
                showConfirmButton: false
            });
        }
        
        /**
         * Download Lead Report
         */
        function downloadLeadReport() {
            if (!currentLeadId) return;
            
            Swal.fire({
                title: 'Generate Report',
                text: 'Generate a detailed PDF report for this lead?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Generate PDF',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open(`inc/api/generate-lead-report.php?id=${currentLeadId}`, '_blank');
                }
            });
        }
        
        /**
         * Format Number with Commas
         */
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
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