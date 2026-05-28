<?php
/**
 * Activity Log Page
 * Track and monitor admin actions
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
        <!-- Filters Card -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-filter"></i> Activity Filters
            </div>
            <div class="card-body">
                <form id="activityFilters">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label for="userFilter" class="form-label">User</label>
                            <select class="form-select" id="userFilter">
                                <option value="">All Users</option>
                                <!-- Will be populated dynamically -->
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="actionTypeFilter" class="form-label">Action Type</label>
                            <select class="form-select" id="actionTypeFilter">
                                <option value="">All Actions</option>
                                <option value="create">Create</option>
                                <option value="update">Update</option>
                                <option value="delete">Delete</option>
                                <option value="resubmit">Resubmit</option>
                                <option value="blacklist">Blacklist</option>
                                <option value="export">Export</option>
                                <option value="login">Login</option>
                                <option value="logout">Logout</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="dateFilter" class="form-label">Date</label>
                            <input type="date" class="form-control" id="dateFilter">
                        </div>
                        
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearFilters()">
                            <i class="fas fa-times"></i> Clear Filters
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info" onclick="setDateFilter('today')">Today</button>
                        <button type="button" class="btn btn-sm btn-outline-info" onclick="setDateFilter('yesterday')">Yesterday</button>
                        <button type="button" class="btn btn-sm btn-outline-info" onclick="setDateFilter('7days')">Last 7 Days</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="totalActions">0</h3>
                        <p>Total Actions</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="createActions">0</h3>
                        <p>Creates</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="updateActions">0</h3>
                        <p>Updates</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-danger">
                        <i class="fas fa-trash"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="deleteActions">0</h3>
                        <p>Deletes</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Log Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list-ul"></i> Activity History</span>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-success" onclick="exportActivity()">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="loadActivityLog()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="activityTable">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Target</th>
                                <th>Details</th>
                                <th>IP Address</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be populated by DataTable -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Activity Stats by User -->
        <div class="card mt-4">
            <div class="card-header">
                <i class="fas fa-users"></i> Activity by User
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Total Actions</th>
                                <th>Creates</th>
                                <th>Updates</th>
                                <th>Deletes</th>
                                <th>Last Activity</th>
                            </tr>
                        </thead>
                        <tbody id="userStatsTable">
                            <tr>
                                <td colspan="6" class="text-center text-muted">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Critical Actions -->
        <div class="card mt-4">
            <div class="card-header bg-warning">
                <i class="fas fa-exclamation-triangle"></i> Recent Critical Actions
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Critical actions include: blacklist additions, bulk deletes, system configuration changes
                </p>
                <div id="criticalActions" class="list-group">
                    <div class="text-center text-muted py-3">
                        No critical actions in the last 24 hours
                    </div>
                </div>
            </div>
        </div>

        <!-- Help Section -->
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <i class="fas fa-info-circle"></i> Activity Log Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>What is Logged:</h6>
                        <ul>
                            <li>All administrative actions (create, update, delete)</li>
                            <li>Lead resubmissions and status changes</li>
                            <li>Blacklist modifications</li>
                            <li>System configuration changes</li>
                            <li>Login and logout events</li>
                            <li>Export and report generation</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Log Retention:</h6>
                        <ul>
                            <li><strong>Standard logs:</strong> 90 days</li>
                            <li><strong>Critical actions:</strong> 1 year</li>
                            <li><strong>Login attempts:</strong> 30 days</li>
                            <li>Older logs are automatically archived</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activity Details Modal -->
<div class="modal fade" id="activityModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Activity Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="activityDetails">
                Loading...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php
// Page-specific JavaScript
$additional_js = <<<'SCRIPT'
<script>
let activityTable;
let activityModal;

$(document).ready(function() {
    initActivityTable();
    loadActivityLog();
    loadUserStats();
    loadCriticalActions();
    
    // Initialize modal
    activityModal = new bootstrap.Modal(document.getElementById('activityModal'));
    
    // Form submission
    $('#activityFilters').on('submit', function(e) {
        e.preventDefault();
        loadActivityLog();
    });
    
    // Auto-refresh every 30 seconds
    setInterval(function() {
        loadActivityLog();
        loadUserStats();
    }, 30000);
});

function initActivityTable() {
    try {
        activityTable = $('#activityTable').DataTable({
        order: [[0, 'desc']], // Sort by timestamp descending
        pageLength: 25,
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-sm btn-success'
            }
        ],
        columns: [
            { 
                data: 'timestamp',
                render: function(data) {
                    return formatDate(data, 'long');
                }
            },
            { 
                data: 'user',
                render: function(data) {
                    return `<span class="badge bg-primary">${data}</span>`;
                }
            },
            { 
                data: 'action',
                render: function(data) {
                    const colors = {
                        'create': 'success',
                        'update': 'warning',
                        'delete': 'danger',
                        'resubmit': 'info',
                        'blacklist': 'dark',
                        'export': 'secondary',
                        'login': 'primary',
                        'logout': 'secondary'
                    };
                    return `<span class="badge bg-${colors[data.toLowerCase()] || 'secondary'}">${data}</span>`;
                }
            },
            { 
                data: 'target',
                render: function(data) {
                    return data ? `<small>${data}</small>` : '-';
                }
            },
            { 
                data: 'details',
                render: function(data) {
                    if (!data) return '-';
                    const short = data.substring(0, 50);
                    return `<small>${short}${data.length > 50 ? '...' : ''}</small>`;
                }
            },
            { 
                data: 'ip_address',
                render: function(data) {
                    return data ? `<small>${data}</small>` : '-';
                }
            },
            { 
                data: 'status',
                render: function(data) {
                    if (data === 'success') {
                        return '<span class="badge bg-success">Success</span>';
                    } else if (data === 'failed') {
                        return '<span class="badge bg-danger">Failed</span>';
                    }
                    return '<span class="badge bg-secondary">-</span>';
                }
            },
            { 
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-info" onclick="viewActivityDetails(${JSON.stringify(row).replace(/"/g, '&quot;')})">
                            <i class="fas fa-eye"></i>
                        </button>
                    `;
                }
            }
        ]
    });
        console.log('Activity table initialized successfully');
    } catch (error) {
        console.error('Failed to initialize activity table:', error);
        showError('Failed to initialize activity table. Please refresh the page.');
    }
}

function loadActivityLog() {
    const user = $('#userFilter').val();
    const actionType = $('#actionTypeFilter').val();
    const date = $('#dateFilter').val();
    
    apiRequest('get-activity.php', 'GET', {
        user: user,
        type: actionType,
        date: date
    })
    .done(function(response) {
        if (response.success) {
            // Calculate statistics
            const stats = calculateActivityStats(response.data);
            $('#totalActions').text(formatNumber(stats.total));
            $('#createActions').text(formatNumber(stats.creates));
            $('#updateActions').text(formatNumber(stats.updates));
            $('#deleteActions').text(formatNumber(stats.deletes));
            
            // Populate user filter if empty
            if ($('#userFilter option').length === 1 && response.data.length > 0) {
                const users = [...new Set(response.data.map(item => item.user))];
                users.forEach(user => {
                    $('#userFilter').append(`<option value="${user}">${user}</option>`);
                });
            }
            
            // Update table
            if (activityTable && $.fn.DataTable.isDataTable('#activityTable')) {
                activityTable.clear();
                if (response.data && response.data.length > 0) {
                    activityTable.rows.add(response.data);
                }
                activityTable.draw();
            } else {
                console.error('Activity table not initialized');
            }
        }
    })
    .fail(function() {
        showError('Failed to load activity log');
    });
}

function calculateActivityStats(data) {
    return {
        total: data.length,
        creates: data.filter(item => item.action.toLowerCase() === 'create').length,
        updates: data.filter(item => item.action.toLowerCase() === 'update').length,
        deletes: data.filter(item => item.action.toLowerCase() === 'delete').length
    };
}

function loadUserStats() {
    apiRequest('get-activity.php', 'GET', { group_by: 'user' })
        .done(function(response) {
            if (response.success && response.user_stats) {
                let html = '';
                
                if (response.user_stats.length === 0) {
                    html = '<tr><td colspan="6" class="text-center text-muted">No user activity data</td></tr>';
                } else {
                    response.user_stats.forEach(function(stat) {
                        html += `
                            <tr>
                                <td><strong>${stat.user}</strong></td>
                                <td>${formatNumber(stat.total)}</td>
                                <td class="text-success">${formatNumber(stat.creates || 0)}</td>
                                <td class="text-warning">${formatNumber(stat.updates || 0)}</td>
                                <td class="text-danger">${formatNumber(stat.deletes || 0)}</td>
                                <td>${stat.last_activity ? timeAgo(stat.last_activity) : 'N/A'}</td>
                            </tr>
                        `;
                    });
                }
                
                $('#userStatsTable').html(html);
            }
        });
}

function loadCriticalActions() {
    apiRequest('get-activity.php', 'GET', { 
        critical_only: true,
        limit: 10
    })
    .done(function(response) {
        if (response.success && response.data && response.data.length > 0) {
            let html = '';
            
            response.data.forEach(function(action) {
                const iconMap = {
                    'blacklist': 'ban',
                    'delete': 'trash',
                    'config': 'cog'
                };
                const icon = iconMap[action.action.toLowerCase()] || 'exclamation-triangle';
                
                html += `
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">
                                <i class="fas fa-${icon} text-warning"></i>
                                ${action.action} by ${action.user}
                            </h6>
                            <small>${timeAgo(action.timestamp)}</small>
                        </div>
                        <p class="mb-1"><small>${action.details || 'No details'}</small></p>
                        <small class="text-muted">Target: ${action.target || 'N/A'}</small>
                    </div>
                `;
            });
            
            $('#criticalActions').html(html);
        }
    });
}

function viewActivityDetails(activity) {
    let detailsHtml = `
        <div class="row">
            <div class="col-md-6">
                <h6>Action Information</h6>
                <table class="table table-sm">
                    <tr><th>User:</th><td>${activity.user}</td></tr>
                    <tr><th>Action:</th><td>${activity.action}</td></tr>
                    <tr><th>Target:</th><td>${activity.target || 'N/A'}</td></tr>
                    <tr><th>Status:</th><td>${activity.status || 'N/A'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Technical Details</h6>
                <table class="table table-sm">
                    <tr><th>Timestamp:</th><td>${formatDate(activity.timestamp, 'long')}</td></tr>
                    <tr><th>IP Address:</th><td>${activity.ip_address || 'N/A'}</td></tr>
                    <tr><th>User Agent:</th><td><small>${activity.user_agent || 'N/A'}</small></td></tr>
                </table>
            </div>
        </div>
        <hr>
        <h6>Details</h6>
        <div class="alert alert-info">
            ${activity.details || 'No additional details available'}
        </div>
    `;
    
    if (activity.changes) {
        detailsHtml += `
            <h6>Changes Made</h6>
            <pre class="bg-light p-3 rounded"><code>${JSON.stringify(activity.changes, null, 2)}</code></pre>
        `;
    }
    
    $('#activityDetails').html(detailsHtml);
    activityModal.show();
}

function clearFilters() {
    $('#userFilter').val('');
    $('#actionTypeFilter').val('');
    $('#dateFilter').val('');
    loadActivityLog();
}

function setDateFilter(range) {
    const today = new Date();
    let date;
    
    switch(range) {
        case 'today':
            date = today;
            break;
        case 'yesterday':
            date = new Date(today.getTime() - 24 * 60 * 60 * 1000);
            break;
        case '7days':
            // For 7 days, we'll leave it empty and handle on backend
            $('#dateFilter').val('');
            loadActivityLog();
            return;
    }
    
    $('#dateFilter').val(date.toISOString().split('T')[0]);
    loadActivityLog();
}

function exportActivity() {
    activityTable.button('.buttons-csv').trigger();
}

// Set up as refresh function for auto-refresh
window.refreshData = loadActivityLog;
</script>
SCRIPT;

// Include footer
include 'includes/footer.php';
?>