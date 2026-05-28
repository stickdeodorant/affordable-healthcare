<?php
/**
 * Dashboard - Module 3: History
 * Long-term historical data with advanced filtering and exports
 */

// Load configuration - FIXED PATH
require_once __DIR__ . '/includes/config.php';

// Page-specific configuration
$page_title = 'Dashboard Overview';
$page_icon = 'fa-home';
$use_charts = true;
$use_datatables = false;

// Include header - FIXED PATH
include __DIR__ . '/includes/header.php';
?>

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main-content">
    <?php include __DIR__ . '/includes/topbar.php'; ?>
    
    <div class="content-wrapper">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1><i class="fas fa-history"></i> Lead History</h1>
                <p class="text-muted">View and analyze all historical lead data</p>
            </div>
            <div>
                <button class="btn btn-success" onclick="exportHistory()">
                    <i class="fas fa-download"></i> Export to CSV
                </button>
                <button class="btn btn-primary" onclick="refreshHistory()">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="historyTotal">0</h3>
                        <p>Total Records</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="successRate">0%</h3>
                        <p>Success Rate</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-info">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="avgResponseTime">0ms</h3>
                        <p>Avg Response Time</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="totalRevenue">$0</h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-filter"></i> Filters
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Date Range</label>
                        <select class="form-select" id="dateRangeFilter">
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="7days" selected>Last 7 Days</option>
                            <option value="30days">Last 30 Days</option>
                            <option value="thismonth">This Month</option>
                            <option value="lastmonth">Last Month</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3" id="customDateRange" style="display: none;">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="startDate">
                    </div>
                    
                    <div class="col-md-3" id="customDateRangeEnd" style="display: none;">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" id="endDate">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">All Statuses</option>
                            <option value="success">Success</option>
                            <option value="error">Error</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">State</label>
                        <select class="form-select" id="stateFilter">
                            <option value="">All States</option>
                            <?php
                            $states = ['AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA', 
                                      'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD', 
                                      'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ', 
                                      'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', 
                                      'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY'];
                            foreach ($states as $state) {
                                echo "<option value='$state'>$state</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Campaign</label>
                        <select class="form-select" id="campaignFilter">
                            <option value="">All Campaigns</option>
                            <!-- Populated dynamically -->
                        </select>
                    </div>
                    
                    <div class="col-md-12">
                        <button class="btn btn-primary" onclick="applyFilters()">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <button class="btn btn-secondary" onclick="resetFilters()">
                            <i class="fas fa-times"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- History Table -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list"></i> Historical Leads
            </div>
            <div class="card-body">
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
                            <!-- Populated via DataTables AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lead Details Modal -->
<div class="modal fade" id="leadDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lead Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="leadDetailsContent">
                <!-- Populated dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="exportLeadDetails()">
                    <i class="fas fa-download"></i> Export This Lead
                </button>
            </div>
        </div>
    </div>
</div>

<?php
// Page-specific JavaScript
$additional_js = <<<'SCRIPT'
<script>
let historyTable;
let currentLeadId = null;

$(document).ready(function() {
    initializeHistoryTable();
    loadStatistics();
    loadCampaigns();
    setupEventHandlers();
});

function initializeHistoryTable() {
    historyTable = initDataTable('#historyTable', {
        processing: true,
        serverSide: true,
        ajax: {
            url: '../inc/api/get-history.php',
            type: 'POST',
            data: function(d) {
                // Add filter parameters
                d.dateRange = $('#dateRangeFilter').val();
                d.status = $('#statusFilter').val();
                d.state = $('#stateFilter').val();
                d.campaign = $('#campaignFilter').val();
                d.startDate = $('#startDate').val();
                d.endDate = $('#endDate').val();
                return d;
            }
        },
        columns: [
            { 
                data: 'lead_id',
                width: '140px',
                render: function(data) {
                    return `<small style="font-size: 11px;">${data}</small>`;
                }
            },
            { 
                data: 'timestamp',
                width: '140px',
                render: function(data) {
                    return data ? formatDate(data, 'short') : 'N/A';
                }
            },
            { 
                data: 'name',
                width: '150px'
            },
            { 
                data: 'email',
                width: '180px'
            },
            { 
                data: 'phone',
                width: '120px',
                render: function(data) {
                    return formatPhone(data || '');
                }
            },
            { 
                data: 'state',
                width: '60px',
                className: 'text-center'
            },
            { 
                data: 'campaign',
                width: '100px',
                render: function(data) {
                    return data || 'N/A';
                }
            },
            { 
                data: 'status',
                width: '100px',
                render: function(data) {
                    return getStatusBadge(data);
                }
            },
            { 
                data: 'response_time',
                width: '100px',
                className: 'text-center',
                render: function(data) {
                    if (!data || data === 'N/A') return 'N/A';
                    const time = parseInt(data);
                    if (time < 1000) return time + 'ms';
                    return (time / 1000).toFixed(2) + 's';
                }
            },
            { 
                data: 'id',
                width: '80px',
                orderable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-info" onclick='viewLeadDetails(${JSON.stringify(row)})' title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                    `;
                }
            }
        ],
        order: [[1, 'desc']], // Sort by date descending
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
    });
}

function loadStatistics() {
    apiRequest('get-history-stats.php', 'GET')
        .done(function(response) {
            if (response.success && response.data) {
                $('#historyTotal').text(formatNumber(response.data.total || 0));
                $('#successRate').text(response.data.conversionRate || 0 + '%');
                $('#avgResponseTime').text(Math.round(response.data.avgResponseTime || 0) + 'ms');
                $('#totalRevenue').text(formatCurrency(response.data.totalRevenue || 0));
            }
        })
        .fail(function() {
            console.error('Failed to load statistics');
        });
}

function loadCampaigns() {
    // Load unique campaigns from database
    $.ajax({
        url: '../inc/api/get-campaigns.php',
        type: 'GET',
        success: function(response) {
            if (response.success && response.data) {
                const select = $('#campaignFilter');
                response.data.forEach(function(campaign) {
                    select.append(`<option value="${campaign}">${campaign}</option>`);
                });
            }
        }
    });
}

function setupEventHandlers() {
    // Show/hide custom date range
    $('#dateRangeFilter').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#customDateRange').show();
            $('#customDateRangeEnd').show();
        } else {
            $('#customDateRange').hide();
            $('#customDateRangeEnd').hide();
        }
    });
}

function applyFilters() {
    historyTable.ajax.reload();
    loadStatistics();
}

function resetFilters() {
    $('#dateRangeFilter').val('7days');
    $('#statusFilter').val('');
    $('#stateFilter').val('');
    $('#campaignFilter').val('');
    $('#startDate').val('');
    $('#endDate').val('');
    $('#customDateRange').hide();
    $('#customDateRangeEnd').hide();
    applyFilters();
}

function refreshHistory() {
    historyTable.ajax.reload();
    loadStatistics();
    showToast('History refreshed', 'success');
}

function viewLeadDetails(lead) {
    currentLeadId = lead.id;
    
    let html = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="border-bottom pb-2 mb-3">Personal Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>Name:</strong></td><td>${lead.name || (lead.first_name + ' ' + lead.last_name)}</td></tr>
                    <tr><td><strong>Email:</strong></td><td>${lead.email}</td></tr>
                    <tr><td><strong>Phone:</strong></td><td>${formatPhone(lead.phone)}</td></tr>
                    <tr><td><strong>Age:</strong></td><td>${lead.age || 'N/A'}</td></tr>
                    <tr><td><strong>Gender:</strong></td><td>${lead.gender || 'N/A'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="border-bottom pb-2 mb-3">Location</h6>
                <table class="table table-sm">
                    <tr><td><strong>City:</strong></td><td>${lead.city || 'N/A'}</td></tr>
                    <tr><td><strong>State:</strong></td><td>${lead.state}</td></tr>
                    <tr><td><strong>ZIP:</strong></td><td>${lead.zip || 'N/A'}</td></tr>
                </table>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-6">
                <h6 class="border-bottom pb-2 mb-3">Submission Details</h6>
                <table class="table table-sm">
                    <tr><td><strong>Lead ID:</strong></td><td><small>${lead.lead_id}</small></td></tr>
                    <tr><td><strong>Submitted:</strong></td><td>${formatDate(lead.timestamp || lead.submission_timestamp, 'long')}</td></tr>
                    <tr><td><strong>IP Address:</strong></td><td>${lead.ip_address || 'N/A'}</td></tr>
                    <tr><td><strong>Campaign:</strong></td><td>${lead.campaign || 'N/A'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="border-bottom pb-2 mb-3">Boberdoo Response</h6>
                <table class="table table-sm">
                    <tr><td><strong>Status:</strong></td><td>${getStatusBadge(lead.status || lead.boberdoo_status)}</td></tr>
                    <tr><td><strong>Response Code:</strong></td><td>${lead.boberdoo_response_code || lead.response_code || 'N/A'}</td></tr>
                    <tr><td><strong>Boberdoo Lead ID:</strong></td><td>${lead.boberdoo_lead_id || 'N/A'}</td></tr>
                    <tr><td><strong>Response Time:</strong></td><td>${lead.response_time || 'N/A'}</td></tr>
                    <tr><td><strong>Price:</strong></td><td>${lead.boberdoo_price ? formatCurrency(lead.boberdoo_price) : 'N/A'}</td></tr>
                    <tr><td><strong>Buyer:</strong></td><td>${lead.boberdoo_buyer || 'N/A'}</td></tr>
                </table>
            </div>
        </div>
    `;
    
    if (lead.boberdoo_error_message || lead.error_message) {
        html += `
            <div class="row mt-3">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2 mb-3">Error Details</h6>
                    <div class="alert alert-danger">
                        ${lead.boberdoo_error_message || lead.error_message}
                    </div>
                </div>
            </div>
        `;
    }
    
    $('#leadDetailsContent').html(html);
    $('#leadDetailsModal').modal('show');
}

function exportHistory() {
    const filters = {
        dateRange: $('#dateRangeFilter').val(),
        status: $('#statusFilter').val(),
        state: $('#stateFilter').val(),
        campaign: $('#campaignFilter').val(),
        startDate: $('#startDate').val(),
        endDate: $('#endDate').val()
    };
    
    const params = new URLSearchParams(filters);
    window.location.href = `../inc/api/export-history.php?${params.toString()}`;
    
    showToast('Export started - download will begin shortly', 'success');
}

function exportLeadDetails() {
    if (currentLeadId) {
        window.location.href = `../inc/api/export-lead.php?id=${currentLeadId}`;
        showToast('Exporting lead details', 'success');
    }
}

// Set up refresh function for auto-refresh
window.refreshData = refreshHistory;
</script>
SCRIPT;

// Include footer
include 'includes/footer.php';
?>