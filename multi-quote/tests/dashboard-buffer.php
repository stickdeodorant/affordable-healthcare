<?php
/**
 * Lead Management Dashboard - Module 2: Buffer Management
 * Manages leads in the 7-day buffer with filtering and bulk actions
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
    <title>Buffer Management - Lead Dashboard</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        :root {
            --primary-color: #2196F3;
            --success-color: #4CAF50;
            --danger-color: #f44336;
            --warning-color: #ff9800;
            --sidebar-width: 260px;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f5f7fa;
        }
        
        /* Sidebar (matching Module 1) */
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
        
        /* Header */
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
        
        /* Stats Bar */
        .buffer-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            border-left: 4px solid var(--primary-color);
        }
        
        .stat-box.success { border-left-color: var(--success-color); }
        .stat-box.danger { border-left-color: var(--danger-color); }
        .stat-box.warning { border-left-color: var(--warning-color); }
        
        .stat-box h4 {
            font-size: 1.8rem;
            margin: 0 0 5px 0;
            font-weight: 700;
        }
        
        .stat-box p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        /* Filters Card */
        .filters-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        
        .filters-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
            font-size: 0.9rem;
        }
        
        .form-select, .form-control {
            border-radius: 6px;
            border: 1px solid #ddd;
            padding: 8px 12px;
        }
        
        .form-select:focus, .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
        }
        
        /* Bulk Actions Bar */
        .bulk-actions-bar {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            display: none;
            align-items: center;
            justify-content: space-between;
        }
        
        .bulk-actions-bar.show {
            display: flex;
        }
        
        .bulk-info {
            font-weight: 500;
            color: #333;
        }
        
        .bulk-actions {
            display: flex;
            gap: 10px;
        }
        
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
        }
        
        /* DataTables Customization */
        table.dataTable {
            border-collapse: separate !important;
            border-spacing: 0;
        }
        
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
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .btn-action {
            padding: 6px 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .btn-primary { background: var(--primary-color); color: white; }
        .btn-success { background: var(--success-color); color: white; }
        .btn-warning { background: var(--warning-color); color: white; }
        .btn-danger { background: var(--danger-color); color: white; }
        
        /* Modal Customization */
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
        
        .lead-detail-section {
            margin-bottom: 20px;
        }
        
        .lead-detail-section h6 {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .detail-row {
            display: grid;
            grid-template-columns: 140px 1fr;
            padding: 8px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .detail-label {
            font-weight: 500;
            color: #666;
        }
        
        .detail-value {
            color: #333;
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
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .buffer-stats {
                grid-template-columns: 1fr;
            }
            
            .filters-row {
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
            <small>Healthcare Insurance</small>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="dashboard-main.php" class="nav-link">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
            <div class="nav-item">
                <a href="dashboard-buffer.php" class="nav-link active">
                    <i class="fas fa-database"></i> 7-Day Buffer
                </a>
            </div>
            <div class="nav-item">
                <a href="#" class="nav-link">
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
            <h1><i class="fas fa-database"></i> 7-Day Buffer Management</h1>
            <p class="text-muted mb-0">Manage leads in the temporary buffer (expires after 7 days)</p>
        </div>
        
        <!-- Buffer Statistics -->
        <div class="buffer-stats">
            <div class="stat-box">
                <h4 id="total-buffer">0</h4>
                <p>Total in Buffer</p>
            </div>
            <div class="stat-box success">
                <h4 id="success-buffer">0</h4>
                <p>Successful</p>
            </div>
            <div class="stat-box danger">
                <h4 id="error-buffer">0</h4>
                <p>Failed/Error</p>
            </div>
            <div class="stat-box warning">
                <h4 id="pending-buffer">0</h4>
                <p>Pending</p>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters-card">
            <h6 class="mb-3"><i class="fas fa-filter"></i> Filters</h6>
            <div class="filters-row">
                <div class="filter-group">
                    <label>Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="success">Success</option>
                        <option value="error">Error</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Date Range</label>
                    <select class="form-select" id="dateFilter">
                        <option value="all">All Time</option>
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="3days">Last 3 Days</option>
                        <option value="7days">Last 7 Days</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Resubmits</label>
                    <select class="form-select" id="resubmitFilter">
                        <option value="">Any</option>
                        <option value="0">0 Times</option>
                        <option value="1">1 Time</option>
                        <option value="2">2 Times</option>
                        <option value="3+">3+ Times</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <button class="btn btn-primary w-100" onclick="applyFilters()">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                </div>
                
                <div class="filter-group">
                    <button class="btn btn-secondary w-100" onclick="resetFilters()">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Bulk Actions Bar -->
        <div class="bulk-actions-bar" id="bulkActionsBar">
            <div class="bulk-info">
                <i class="fas fa-check-square"></i>
                <strong id="selectedCount">0</strong> leads selected
            </div>
            <div class="bulk-actions">
                <button class="btn-action btn-success" onclick="bulkResubmit()">
                    <i class="fas fa-redo"></i> Resubmit Selected
                </button>
                <button class="btn-action btn-warning" onclick="bulkQueue()">
                    <i class="fas fa-clock"></i> Add to Queue
                </button>
                <button class="btn-action btn-danger" onclick="bulkBlacklist()">
                    <i class="fas fa-ban"></i> Blacklist
                </button>
                <button class="btn-action btn-secondary" onclick="clearSelection()">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>
        </div>
        
        <!-- Data Table -->
        <div class="table-card">
            <h5>
                <i class="fas fa-table"></i> Buffer Leads
                <button class="btn btn-sm btn-primary float-end" onclick="refreshTable()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </h5>
            
            <div class="table-responsive">
                <table id="bufferTable" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 30px;">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>Lead ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>State</th>
                            <th>Submitted</th>
                            <th>Expires</th>
                            <th>Status</th>
                            <th>Resubmits</th>
                            <th style="width: 120px;">Actions</th>
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-info-circle"></i> Lead Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="leadDetailsContent">
                    <!-- Content loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Resubmit Options Modal -->
    <div class="modal fade" id="resubmitModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-redo"></i> Resubmit Options</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>You are about to resubmit <strong id="resubmitCount">0</strong> lead(s).</p>
                    
                    <div class="mb-3">
                        <label class="form-label">Priority</label>
                        <select class="form-select" id="resubmitPriority">
                            <option value="high">High - Process Immediately</option>
                            <option value="normal" selected>Normal</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Schedule</label>
                        <select class="form-select" id="resubmitSchedule">
                            <option value="now">Resubmit Now</option>
                            <option value="1">In 1 Hour</option>
                            <option value="3">In 3 Hours</option>
                            <option value="6">In 6 Hours</option>
                            <option value="24">In 24 Hours</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="confirmResubmit()">
                        <i class="fas fa-check"></i> Confirm Resubmit
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Global variables
        let bufferTable;
        let selectedLeads = [];
        
        // Initialize
        $(document).ready(function() {
            initializeTable();
            loadBufferStats();
            loadBufferData();
            setupEventHandlers();
        });
        
        /**
         * Initialize DataTable
         */
        function initializeTable() {
            bufferTable = $('#bufferTable').DataTable({
                pageLength: 25,
                order: [[6, 'desc']], // Sort by submitted date
                columnDefs: [
                    { orderable: false, targets: [0, 10] } // Checkbox and actions
                ],
                language: {
                    emptyTable: "No leads in buffer",
                    loadingRecords: "Loading buffer data...",
                    processing: "Processing..."
                }
            });
        }
        
        /**
         * Setup Event Handlers
         */
        function setupEventHandlers() {
            // Select all checkbox
            $('#selectAll').on('change', function() {
                const isChecked = $(this).prop('checked');
                $('.lead-checkbox').prop('checked', isChecked);
                updateSelectedLeads();
            });
            
            // Individual checkbox
            $(document).on('change', '.lead-checkbox', function() {
                updateSelectedLeads();
            });
        }
        
        /**
         * Load Buffer Statistics
         */
        function loadBufferStats() {
            $.ajax({
                url: 'inc/api/get-stats.php',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#total-buffer').text(response.data.total_buffer || 0);
                        $('#success-buffer').text(response.data.successful || 0);
                        $('#error-buffer').text(response.data.error || 0);
                        $('#pending-buffer').text(response.data.pending || 0);
                    }
                }
            });
        }
        
        /**
         * Load Buffer Data
         */
        function loadBufferData() {
            showLoading();
            
            $.ajax({
                url: 'inc/api/get-buffer.php',
                type: 'POST',
                data: {
                    status: $('#statusFilter').val(),
                    dateRange: $('#dateFilter').val(),
                    resubmits: $('#resubmitFilter').val()
                },
                success: function(response) {
                    if (response.success) {
                        updateTable(response.data);
                    } else {
                        Swal.fire('Error', 'Failed to load buffer data', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to connect to server', 'error');
                },
                complete: function() {
                    hideLoading();
                }
            });
        }
        
        /**
         * Update Table with Data
         */
        function updateTable(data) {
            bufferTable.clear();
            
            if (!data || data.length === 0) {
                bufferTable.draw();
                return;
            }
            
            data.forEach(function(lead) {
                bufferTable.row.add([
                    `<input type="checkbox" class="lead-checkbox" data-id="${lead.id}">`,
                    `<small>${lead.lead_id || 'N/A'}</small>`,
                    lead.name || lead.first_name + ' ' + lead.last_name,
                    lead.email || 'N/A',
                    lead.phone || lead.primary_phone || 'N/A',
                    lead.state || 'N/A',
                    formatDate(lead.submitted || lead.created_at),
                    formatDate(lead.expires || lead.expires_at),
                    getStatusBadge(lead.status || lead.boberdoo_status),
                    `<span class="badge bg-secondary">${lead.resubmits || lead.resubmit_count || 0}</span>`,
                    getActionButtons(lead)
                ]);
            });
            
            bufferTable.draw();
        }
        
        /**
         * Get Status Badge HTML
         */
        function getStatusBadge(status) {
            const statusLower = (status || 'pending').toLowerCase();
            
            const badges = {
                'success': '<span class="status-badge success"><i class="fas fa-check"></i> Success</span>',
                'error': '<span class="status-badge error"><i class="fas fa-times"></i> Error</span>',
                'pending': '<span class="status-badge pending"><i class="fas fa-clock"></i> Pending</span>'
            };
            
            return badges[statusLower] || `<span class="status-badge">${status}</span>`;
        }
        
        /**
         * Get Action Buttons HTML
         */
        function getActionButtons(lead) {
            let html = '<div class="action-buttons">';
            
            // View button
            html += `<button class="btn-action btn-primary" onclick="viewLeadDetails('${lead.id}')" title="View Details">
                        <i class="fas fa-eye"></i>
                     </button>`;
            
            // Resubmit button (only if not success)
            if ((lead.status || lead.boberdoo_status) !== 'success') {
                html += `<button class="btn-action btn-success" onclick="resubmitSingle('${lead.id}')" title="Resubmit">
                            <i class="fas fa-redo"></i>
                         </button>`;
            }
            
            // Blacklist button
            html += `<button class="btn-action btn-danger" onclick="blacklistLead('${lead.id}', '${lead.email}')" title="Blacklist">
                        <i class="fas fa-ban"></i>
                     </button>`;
            
            html += '</div>';
            return html;
        }
        
        /**
         * Format Date
         */
        function formatDate(dateStr) {
            if (!dateStr) return 'N/A';
            const date = new Date(dateStr);
            return date.toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        /**
         * View Lead Details
         */
        function viewLeadDetails(leadId) {
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
         * Display Lead Details
         */
        function displayLeadDetails(lead) {
            let html = `
                <div class="lead-detail-section">
                    <h6><i class="fas fa-user"></i> Personal Information</h6>
                    <div class="detail-row">
                        <div class="detail-label">Full Name:</div>
                        <div class="detail-value">${lead.first_name} ${lead.last_name}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Email:</div>
                        <div class="detail-value">${lead.email}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Phone:</div>
                        <div class="detail-value">${lead.phone || lead.primary_phone}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Location:</div>
                        <div class="detail-value">${lead.city}, ${lead.state} ${lead.zip}</div>
                    </div>
                </div>
                
                <div class="lead-detail-section">
                    <h6><i class="fas fa-file-alt"></i> Submission Details</h6>
                    <div class="detail-row">
                        <div class="detail-label">Lead ID:</div>
                        <div class="detail-value">${lead.lead_id}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Submitted:</div>
                        <div class="detail-value">${lead.created_at || lead.submission_time}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Expires:</div>
                        <div class="detail-value">${lead.expires_at}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">IP Address:</div>
                        <div class="detail-value">${lead.ip_address || 'N/A'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Campaign:</div>
                        <div class="detail-value">${lead.campaign || 'N/A'}</div>
                    </div>
                </div>
                
                <div class="lead-detail-section">
                    <h6><i class="fas fa-server"></i> Boberdoo Response</h6>
                    <div class="detail-row">
                        <div class="detail-label">Status:</div>
                        <div class="detail-value">${getStatusBadge(lead.boberdoo_status)}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Boberdoo ID:</div>
                        <div class="detail-value">${lead.boberdoo_lead_id || 'N/A'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Resubmit Count:</div>
                        <div class="detail-value">${lead.resubmit_count || 0}</div>
                    </div>
                    ${lead.boberdoo_error ? `
                        <div class="detail-row">
                            <div class="detail-label">Error Message:</div>
                            <div class="detail-value text-danger">${lead.boberdoo_error}</div>
                        </div>
                    ` : ''}
                </div>
            `;
            
            $('#leadDetailsContent').html(html);
            $('#leadDetailsModal').modal('show');
        }
        
        /**
         * Update Selected Leads
         */
        function updateSelectedLeads() {
            selectedLeads = [];
            $('.lead-checkbox:checked').each(function() {
                selectedLeads.push($(this).data('id'));
            });
            
            $('#selectedCount').text(selectedLeads.length);
            
            if (selectedLeads.length > 0) {
                $('#bulkActionsBar').addClass('show');
            } else {
                $('#bulkActionsBar').removeClass('show');
            }
        }
        
        /**
         * Clear Selection
         */
        function clearSelection() {
            $('.lead-checkbox').prop('checked', false);
            $('#selectAll').prop('checked', false);
            updateSelectedLeads();
        }
        
        /**
         * Resubmit Single Lead
         */
        function resubmitSingle(leadId) {
            Swal.fire({
                title: 'Resubmit Lead?',
                text: 'This will resubmit the lead to Boberdoo immediately.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Resubmit',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    performResubmit([leadId]);
                }
            });
        }
        
        /**
         * Bulk Resubmit
         */
        function bulkResubmit() {
            if (selectedLeads.length === 0) {
                Swal.fire('No Selection', 'Please select leads to resubmit', 'warning');
                return;
            }
            
            $('#resubmitCount').text(selectedLeads.length);
            $('#resubmitModal').modal('show');
        }
        
        /**
         * Confirm Resubmit
         */
        function confirmResubmit() {
            const priority = $('#resubmitPriority').val();
            const schedule = $('#resubmitSchedule').val();
            
            $('#resubmitModal').modal('hide');
            
            if (schedule === 'now') {
                performResubmit(selectedLeads);
            } else {
                queueResubmit(selectedLeads, priority, schedule);
            }
        }
        
        /**
         * Perform Resubmit
         */
        function performResubmit(leadIds) {
            showLoading();
            
            $.ajax({
                url: 'inc/api/resubmit.php',
                type: 'POST',
                data: {
                    action: 'resubmit_bulk',
                    lead_ids: leadIds
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success', response.message || 'Leads resubmitted successfully', 'success');
                        clearSelection();
                        refreshTable();
                    } else {
                        Swal.fire('Error', response.message || 'Resubmission failed', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to resubmit leads', 'error');
                },
                complete: function() {
                    hideLoading();
                }
            });
        }
        
        /**
         * Queue Resubmit
         */
        function queueResubmit(leadIds, priority, hours) {
            showLoading();
            
            const scheduledTime = new Date(Date.now() + (hours * 3600000)).toISOString();
            
            $.ajax({
                url: 'inc/api/resubmit.php',
                type: 'POST',
                data: {
                    action: 'queue_bulk',
                    lead_ids: leadIds,
                    priority: priority,
                    scheduled_time: scheduledTime
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success', `${leadIds.length} leads added to queue`, 'success');
                        clearSelection();
                    } else {
                        Swal.fire('Error', response.message || 'Failed to queue leads', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to queue leads', 'error');
                },
                complete: function() {
                    hideLoading();
                }
            });
        }
        
        /**
         * Bulk Queue
         */
        function bulkQueue() {
            if (selectedLeads.length === 0) {
                Swal.fire('No Selection', 'Please select leads to add to queue', 'warning');
                return;
            }
            
            bulkResubmit(); // Reuse the resubmit modal
        }
        
        /**
         * Blacklist Lead
         */
        function blacklistLead(leadId, email) {
            Swal.fire({
                title: 'Blacklist Lead?',
                text: `This will permanently blacklist ${email}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Blacklist',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#f44336'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Blacklist implementation
                    Swal.fire('Success', 'Lead blacklisted (feature in Module 4)', 'success');
                }
            });
        }
        
        /**
         * Bulk Blacklist
         */
        function bulkBlacklist() {
            if (selectedLeads.length === 0) {
                Swal.fire('No Selection', 'Please select leads to blacklist', 'warning');
                return;
            }
            
            Swal.fire({
                title: 'Blacklist Selected Leads?',
                text: `This will blacklist ${selectedLeads.length} leads`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Blacklist All',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#f44336'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Bulk blacklist implementation
                    Swal.fire('Coming Soon', 'Bulk blacklist will be implemented in Module 4', 'info');
                }
            });
        }
        
        /**
         * Apply Filters
         */
        function applyFilters() {
            loadBufferData();
        }
        
        /**
         * Reset Filters
         */
        function resetFilters() {
            $('#statusFilter').val('');
            $('#dateFilter').val('all');
            $('#resubmitFilter').val('');
            loadBufferData();
        }
        
        /**
         * Refresh Table
         */
        function refreshTable() {
            loadBufferStats();
            loadBufferData();
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