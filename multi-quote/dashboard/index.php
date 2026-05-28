<?php
/**
 * Dashboard Home - Overview Page
 * Main dashboard with statistics and recent activity
 */

// Load configuration
require_once __DIR__ . '/includes/config.php';

// Page-specific configuration
$page_title = 'Dashboard Overview';
$page_icon = 'fa-home';
$use_charts = true;
$use_datatables = false;

// Include header
include __DIR__ . '/includes/header.php';
?>

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main-content">
    <?php include __DIR__ . '/includes/topbar.php'; ?>
    
    <div class="content-wrapper">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="stat-total-today">0</h3>
                        <p>Today's Leads</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="stat-successful">0</h3>
                        <p>Successful</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="stat-failed">0</h3>
                        <p>Failed</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="stat-pending">0</h3>
                        <p>Pending</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row mb-4">
            <div class="col-lg-8 mb-3">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-chart-line"></i> 7-Day Trend
                    </div>
                    <div class="card-body">
                        <div style="height: 400px; position: relative;">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-3">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-chart-pie"></i> Status Distribution
                    </div>
                    <div class="card-body">
                        <div style="height: 400px; position: relative;">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Leads Table -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list"></i> Recent Leads</span>
                <button class="btn btn-sm btn-light" onclick="refreshRecentLeads()">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="recentLeadsTable">
                        <thead>
                            <tr>
                                <th>Lead ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="recentLeadsBody">
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-inbox fa-3x text-primary mb-3"></i>
                        <h5>Buffer Management</h5>
                        <p class="text-muted">7-day buffer</p>
                        <a href="buffer.php" class="btn btn-primary btn-sm">Manage Buffer</a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-history fa-3x text-info mb-3"></i>
                        <h5>Lead History</h5>
                        <p class="text-muted">Historical data</p>
                        <a href="history.php" class="btn btn-info btn-sm">View History</a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-redo fa-3x text-warning mb-3"></i>
                        <h5>Resubmission</h5>
                        <p class="text-muted">Queue management</p>
                        <a href="resubmission.php" class="btn btn-warning btn-sm">View Queue</a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-chart-bar fa-3x text-success mb-3"></i>
                        <h5>Analytics</h5>
                        <p class="text-muted">Detailed reports</p>
                        <a href="analytics.php" class="btn btn-success btn-sm">View Analytics</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lead Details Modal -->
<div class="modal fade" id="leadDetailsModal" tabindex="-1" aria-labelledby="leadDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="leadDetailsModalLabel">
                    <i class="fas fa-user-circle"></i> Lead Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Lead details will be populated here -->
                <div id="leadDetailsContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" id="resubmitLeadBtn" style="display:none;">
                    <i class="fas fa-redo"></i> Resubmit Lead
                </button>
                <button type="button" class="btn btn-danger" id="blacklistLeadBtn" style="display:none;">
                    <i class="fas fa-ban"></i> Blacklist
                </button>
            </div>
        </div>
    </div>
</div>

<?php
// Page-specific JavaScript
$additional_js = <<<'SCRIPT'
<script>
let trendChart, statusChart;
let currentLeadData = {};

$(document).ready(function() {
    initCharts();
    loadDashboardData();
    loadRecentLeads();
    
    // Set up auto-refresh
    setInterval(function() {
        loadDashboardData();
        loadRecentLeads();
    }, 30000); // Refresh every 30 seconds
    
    // Setup modal event handlers
    setupModalHandlers();
});

function initCharts() {
    // Trend Chart with fixed height
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Total',
                data: [],
                borderColor: '#2196F3',
                backgroundColor: 'rgba(33, 150, 243, 0.1)',
                tension: 0.4
            }, {
                label: 'Successful',
                data: [],
                borderColor: '#4CAF50',
                backgroundColor: 'rgba(76, 175, 80, 0.1)',
                tension: 0.4
            }, {
                label: 'Failed',
                data: [],
                borderColor: '#f44336',
                backgroundColor: 'rgba(244, 67, 54, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    
    // Status Distribution Chart with fixed height
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Success', 'Failed', 'Pending'],
            datasets: [{
                data: [0, 0, 0],
                backgroundColor: [
                    '#4CAF50',
                    '#f44336',
                    '#ff9800'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

function loadDashboardData() {
    // Load statistics
    $.ajax({
        url: '../inc/api/get-stats.php',
        type: 'GET',
        data: { period: 'today' },
        success: function(response) {
            if (response.success) {
                $('#stat-total-today').text(formatNumber(response.data.total_today || 0));
                $('#stat-successful').text(formatNumber(response.data.successful || 0));
                $('#stat-failed').text(formatNumber(response.data.failed || 0));
                $('#stat-pending').text(formatNumber(response.data.pending || 0));
                
                // Update status chart
                statusChart.data.datasets[0].data = [
                    response.data.successful || 0,
                    response.data.failed || 0,
                    response.data.pending || 0
                ];
                statusChart.update();
            }
        },
        error: function() {
            console.error('Failed to load dashboard statistics');
        }
    });
    
    // Load trend data
    $.ajax({
        url: '../inc/api/get-trends.php',
        type: 'GET',
        data: { days: 7 },
        success: function(response) {
            if (response.success && response.data) {
                // Format dates for display
                const labels = response.data.map(d => {
                    if (d.date) {
                        const date = new Date(d.date);
                        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    }
                    return d.label || '';
                });
                
                trendChart.data.labels = labels;
                trendChart.data.datasets[0].data = response.data.map(d => d.total || 0);
                trendChart.data.datasets[1].data = response.data.map(d => d.successful || 0);
                trendChart.data.datasets[2].data = response.data.map(d => d.failed || 0);
                trendChart.update();
            }
        },
        error: function() {
            console.error('Failed to load trend data');
        }
    });
}

function loadRecentLeads() {
    $.ajax({
        url: '../inc/api/get-recent.php',
        type: 'GET',
        data: { limit: 10 },
        success: function(response) {
            if (response.success && response.data) {
                let html = '';
                
                if (response.data.length === 0) {
                    html = '<tr><td colspan="8" class="text-center text-muted">No recent leads found</td></tr>';
                } else {
                    response.data.forEach(function(lead) {
                        html += `
                            <tr>
                                <td><span class="badge bg-secondary">${lead.lead_id || '-'}</span></td>
                                <td><strong>${escapeHtml(lead.full_name || '-')}</strong></td>
                                <td>${escapeHtml(lead.email || '-')}</td>
                                <td>${escapeHtml(lead.phone || '-')}</td>
                                <td>${escapeHtml(lead.city || '-')}, ${escapeHtml(lead.state || '-')}</td>
                                <td>${getStatusBadge(lead.status)}</td>
                                <td><small>${formatDateTime(lead.created_at)}</small></td>
                                <td>
                                    <button class="btn btn-sm btn-info view-lead-btn" 
                                            data-lead='${JSON.stringify(lead).replace(/'/g, "&apos;")}'>
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }
                
                $('#recentLeadsBody').html(html);
                
                // Attach click handlers to view buttons
                $('.view-lead-btn').on('click', function() {
                    const leadData = JSON.parse($(this).attr('data-lead'));
                    showLeadDetails(leadData);
                });
            }
        },
        error: function() {
            $('#recentLeadsBody').html(
                '<tr><td colspan="8" class="text-center text-danger">Failed to load recent leads</td></tr>'
            );
        }
    });
}

function showLeadDetails(lead) {
    currentLeadData = lead;
    
    // Build the details HTML
    let detailsHtml = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary mb-3"><i class="fas fa-user"></i> Personal Information</h6>
                <table class="table table-sm">
                    <tr>
                        <td class="text-muted" width="40%">Lead ID:</td>
                        <td><strong>${lead.lead_id || '-'}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Name:</td>
                        <td><strong>${escapeHtml(lead.full_name || '-')}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Email:</td>
                        <td>${escapeHtml(lead.email || '-')}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Phone:</td>
                        <td>${escapeHtml(lead.phone || '-')}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Primary Phone:</td>
                        <td>${escapeHtml(lead.primary_phone || '-')}</td>
                    </tr>
                </table>
            </div>
            
            <div class="col-md-6">
                <h6 class="text-primary mb-3"><i class="fas fa-map-marker-alt"></i> Location</h6>
                <table class="table table-sm">
                    <tr>
                        <td class="text-muted" width="40%">City:</td>
                        <td>${escapeHtml(lead.city || '-')}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">State:</td>
                        <td>${escapeHtml(lead.state || '-')}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">ZIP Code:</td>
                        <td>${escapeHtml(lead.zip || '-')}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <hr>
        
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary mb-3"><i class="fas fa-info-circle"></i> Lead Status</h6>
                <table class="table table-sm">
                    <tr>
                        <td class="text-muted" width="40%">Status:</td>
                        <td>${getStatusBadge(lead.status)}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Boberdoo ID:</td>
                        <td>${lead.boberdoo_lead_id || '-'}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Resubmit Count:</td>
                        <td>${lead.resubmit_count || 0}</td>
                    </tr>
                </table>
            </div>
            
            <div class="col-md-6">
                <h6 class="text-primary mb-3"><i class="fas fa-clock"></i> Timestamps</h6>
                <table class="table table-sm">
                    <tr>
                        <td class="text-muted" width="40%">Created:</td>
                        <td>${formatDateTime(lead.created_at)}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Expires:</td>
                        <td>${lead.expires_at ? formatDateTime(lead.expires_at) : '-'}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        ${lead.response_message ? `
        <hr>
        <div class="row">
            <div class="col-12">
                <h6 class="text-primary mb-3"><i class="fas fa-comment"></i> Response Message</h6>
                <div class="alert alert-info">
                    ${escapeHtml(lead.response_message)}
                </div>
            </div>
        </div>
        ` : ''}
    `;
    
    $('#leadDetailsContent').html(detailsHtml);
    
    // Show/hide action buttons based on lead status
    if (lead.status === 'error' || lead.status === 'failed') {
        $('#resubmitLeadBtn').show();
        $('#blacklistLeadBtn').show();
    } else {
        $('#resubmitLeadBtn').hide();
        $('#blacklistLeadBtn').hide();
    }
    
    // Show the modal
    $('#leadDetailsModal').modal('show');
}

function setupModalHandlers() {
    // Resubmit lead button
    $('#resubmitLeadBtn').on('click', function() {
        if (confirm('Are you sure you want to resubmit this lead?')) {
            resubmitLead(currentLeadData.lead_id);
        }
    });
    
    // Blacklist lead button
    $('#blacklistLeadBtn').on('click', function() {
        if (confirm('Are you sure you want to blacklist this email/phone? This action cannot be undone.')) {
            blacklistLead(currentLeadData);
        }
    });
}

function resubmitLead(leadId) {
    $.ajax({
        url: '../inc/api/resubmit.php',
        type: 'POST',
        data: { lead_id: leadId },
        success: function(response) {
            if (response.success) {
                showToast('Lead resubmitted successfully', 'success');
                $('#leadDetailsModal').modal('hide');
                loadRecentLeads();
            } else {
                showToast(response.error || 'Failed to resubmit lead', 'error');
            }
        },
        error: function() {
            showToast('Failed to resubmit lead', 'error');
        }
    });
}

function blacklistLead(lead) {
    $.ajax({
        url: '../inc/api/blacklist.php',
        type: 'POST',
        data: {
            action: 'add',
            email: lead.email,
            phone: lead.phone,
            reason: 'Manual blacklist from dashboard'
        },
        success: function(response) {
            if (response.success) {
                showToast('Lead blacklisted successfully', 'success');
                $('#leadDetailsModal').modal('hide');
                loadRecentLeads();
            } else {
                showToast(response.error || 'Failed to blacklist lead', 'error');
            }
        },
        error: function() {
            showToast('Failed to blacklist lead', 'error');
        }
    });
}

function refreshRecentLeads() {
    $('#recentLeadsBody').html(
        '<tr><td colspan="8" class="text-center"><div class="spinner-border text-primary" role="status">' +
        '<span class="visually-hidden">Loading...</span></div></td></tr>'
    );
    loadRecentLeads();
}

// Helper functions
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function formatDateTime(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getStatusBadge(status) {
    const badges = {
        'success': '<span class="badge bg-success">Success</span>',
        'error': '<span class="badge bg-danger">Error</span>',
        'failed': '<span class="badge bg-danger">Failed</span>',
        'pending': '<span class="badge bg-warning">Pending</span>',
        'rejected': '<span class="badge bg-secondary">Rejected</span>',
        'blacklisted': '<span class="badge bg-dark">Blacklisted</span>'
    };
    return badges[status?.toLowerCase()] || '<span class="badge bg-secondary">' + escapeHtml(status || 'Unknown') + '</span>';
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, m => map[m]);
}
</script>

<style>
/* Additional styles for the modal */
.modal-body table td {
    padding: 0.5rem;
    vertical-align: middle;
}

.modal-body h6 {
    font-weight: 600;
    margin-bottom: 1rem;
}

#leadDetailsModal .modal-dialog {
    max-width: 800px;
}

/* Ensure charts maintain aspect ratio */
canvas {
    max-height: 100% !important;
}
</style>
SCRIPT;

// Include footer
include __DIR__ . '/includes/footer.php';
?>