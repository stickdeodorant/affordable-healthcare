<?php
/**
 * Resubmission Queue Management Page
 * Manage and monitor lead resubmission queue
 */

// Load configuration
require_once __DIR__ . '/includes/config.php';

// Page-specific configuration
$page_title = 'Resubmission Queue';
$page_icon = 'fa-redo';
$use_charts = false;
$use_datatables = true;

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
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="pendingCount">0</h3>
                        <p>Pending</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="failedCount">0</h3>
                        <p>Failed/Error</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-info">
                        <i class="fas fa-redo"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="queuedCount">0</h3>
                        <p>In Queue</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="successCount">0</h3>
                        <p>Success</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Queue Controls -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-sliders-h"></i> Queue Controls
            </div>
            <div class="card-body">
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-success" onclick="processAllPending()">
                        <i class="fas fa-play"></i> Process All Pending
                    </button>
                    <button class="btn btn-warning" onclick="retryFailed()">
                        <i class="fas fa-redo"></i> Retry Failed
                    </button>
                    <button class="btn btn-info" onclick="exportData()">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <button class="btn btn-secondary ms-auto" onclick="refreshData()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Combined Table showing both pending leads and queue items -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                <span><i class="fas fa-list"></i> Resubmission Queue</span>
                <span class="badge bg-light text-dark" id="totalItems">0 items</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="queueTable">
                        <thead>
                            <tr>
                                <th>Lead ID</th>
                                <th>Email</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Source</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Page-specific JavaScript
$additional_js = <<<'SCRIPT'
<script>
let queueTable = null;
let allData = [];

$(document).ready(function() {
    // Initialize DataTable
    initializeTable();
    
    // Load data
    loadAllData();
    
    // Auto-refresh every 30 seconds
    setInterval(loadAllData, 30000);
});

function initializeTable() {
    queueTable = $('#queueTable').DataTable({
        data: [],
        columns: [
            {
                data: 'lead_id',
                render: function(data) {
                    return '<small>' + (data || '-') + '</small>';
                }
            },
            { 
                data: 'email',
                render: function(data) {
                    return data || '-';
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    const firstName = row.first_name || '';
                    const lastName = row.last_name || '';
                    const name = row.name || (firstName + ' ' + lastName).trim();
                    return name || '-';
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    return row.primary_phone || row.phone || '-';
                }
            },
            {
                data: null,
                className: 'text-center',
                render: function(data, type, row) {
                    let status = row.boberdoo_status || row.status || 'pending';
                    let badgeClass = 'warning';
                    
                    if (status === 'success' || status === 'completed') {
                        badgeClass = 'success';
                    } else if (status === 'error' || status === 'failed') {
                        badgeClass = 'danger';
                    } else if (status === 'queued' || status === 'scheduled') {
                        badgeClass = 'info';
                    }
                    
                    return '<span class="badge bg-' + badgeClass + '">' + status + '</span>';
                }
            },
            {
                data: 'source',
                render: function(data) {
                    let badge = 'secondary';
                    let text = data || 'buffer';
                    if (text === 'buffer') badge = 'warning';
                    else if (text === 'queue') badge = 'info';
                    return '<span class="badge bg-' + badge + '">' + text + '</span>';
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    const dateStr = row.created_at || row.created || '';
                    return formatDate(dateStr);
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    const leadId = row.lead_external_id || row.lead_id || '';
                    return '<button class="btn btn-sm btn-success" onclick="resubmitLead(\'' + leadId + '\')" title="Resubmit">' +
                           '<i class="fas fa-redo"></i></button> ' +
                           '<button class="btn btn-sm btn-info" onclick="viewLead(\'' + leadId + '\')" title="View Details">' +
                           '<i class="fas fa-eye"></i></button>';
                }
            }
        ],
        order: [[6, 'desc']], // Sort by created date
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        language: {
            emptyTable: "No pending leads or queue items found",
            loadingRecords: "Loading...",
            processing: "Processing..."
        }
    });
}

function loadAllData() {
    console.log('Loading all resubmission data...');
    
    let pendingLeads = [];
    let queueItems = [];
    let stats = {
        pending: 0,
        failed: 0,
        error: 0,
        success: 0,
        queued: 0,
        total: 0
    };
    
    // First, load pending leads from buffer
    $.ajax({
        url: '../inc/api/get-buffer.php',
        type: 'GET',
        success: function(bufferResponse) {
            console.log('Buffer response:', bufferResponse);
            
            if (bufferResponse.success && bufferResponse.data) {
                // Process buffer data
                bufferResponse.data.forEach(function(lead) {
                    const status = lead.boberdoo_status || 'pending';
                    
                    // Count statistics
                    if (status === 'pending') stats.pending++;
                    else if (status === 'failed') stats.failed++;
                    else if (status === 'error') stats.error++;
                    else if (status === 'success') stats.success++;
                    stats.total++;
                    
                    // Only add non-success leads to table
                    if (status !== 'success') {
                        lead.source = 'buffer';
                        pendingLeads.push(lead);
                    }
                });
            }
            
            // Then load queue items
            $.ajax({
                url: '../inc/api/get-queue.php',
                type: 'GET',
                data: { limit: 1000 },
                success: function(queueResponse) {
                    console.log('Queue response:', queueResponse);
                    
                    if (queueResponse.success && queueResponse.data) {
                        // Process queue data
                        queueResponse.data.forEach(function(item) {
                            // Map queue item to common format
                            const mappedItem = {
                                lead_id: item.lead_external_id || item.lead_id,
                                email: item.email,
                                name: item.name,
                                first_name: item.first_name,
                                last_name: item.last_name,
                                primary_phone: item.primary_phone || item.phone,
                                status: item.status,
                                source: 'queue',
                                created_at: item.created,
                                queue_id: item.queue_id,
                                priority: item.priority,
                                attempts: item.attempts,
                                max_attempts: item.max_attempts
                            };
                            
                            queueItems.push(mappedItem);
                            stats.queued++;
                        });
                        
                        // Update queue statistics from response summary
                        if (queueResponse.summary) {
                            $('#queuedCount').text(queueResponse.summary.total_queued || 0);
                        }
                    }
                    
                    // Combine all data
                    allData = pendingLeads.concat(queueItems);
                    
                    // Update DataTable
                    queueTable.clear();
                    queueTable.rows.add(allData);
                    queueTable.draw();
                    
                    // Update statistics
                    updateStatistics(stats);
                    $('#totalItems').text(allData.length + ' items');
                },
                error: function(xhr, status, error) {
                    console.log('Queue API not available or empty, showing buffer items only');
                    
                    // Even if queue fails, show buffer data
                    allData = pendingLeads;
                    queueTable.clear();
                    queueTable.rows.add(allData);
                    queueTable.draw();
                    
                    updateStatistics(stats);
                    $('#totalItems').text(allData.length + ' items');
                }
            });
        },
        error: function(xhr, status, error) {
            console.error('Failed to load buffer data:', error);
            showError('Failed to load pending leads');
        }
    });
}

function updateStatistics(stats) {
    $('#pendingCount').text(stats.pending || 0);
    $('#failedCount').text((stats.failed + stats.error) || 0);
    $('#successCount').text(stats.success || 0);
    // queuedCount is updated from the queue API response
}

function processAllPending() {
    if (!confirm('Process all pending leads now? This will submit them to Boberdoo.')) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: '../inc/api/resubmit.php',
        type: 'POST',
        data: { 
            action: 'process_all_pending'
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                showSuccess('Processing ' + (response.processed || 0) + ' leads...');
                setTimeout(loadAllData, 2000);
            } else {
                showError(response.message || 'Failed to process pending leads');
            }
        },
        error: function(xhr) {
            hideLoading();
            let errorMsg = 'Failed to process leads. The resubmit API may not be configured.';
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.message) errorMsg = response.message;
            } catch(e) {}
            showError(errorMsg);
        }
    });
}

function retryFailed() {
    if (!confirm('Retry all failed leads?')) {
        return;
    }
    
    showLoading();
    
    // Get all failed lead IDs
    const failedLeads = allData.filter(function(item) {
        const status = item.boberdoo_status || item.status || '';
        return status === 'failed' || status === 'error';
    }).map(function(item) {
        return item.lead_id;
    });
    
    if (failedLeads.length === 0) {
        hideLoading();
        showWarning('No failed leads to retry');
        return;
    }
    
    $.ajax({
        url: '../inc/api/resubmit.php',
        type: 'POST',
        data: { 
            action: 'bulk_resubmit',
            lead_ids: failedLeads
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                showSuccess('Retrying ' + failedLeads.length + ' failed leads...');
                setTimeout(loadAllData, 2000);
            } else {
                showError(response.message || 'Failed to retry leads');
            }
        },
        error: function() {
            hideLoading();
            showError('Failed to retry leads');
        }
    });
}

function resubmitLead(leadId) {
    if (!leadId) {
        showError('Invalid lead ID');
        return;
    }
    
    if (!confirm('Resubmit this lead to Boberdoo?')) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: '../inc/api/resubmit.php',
        type: 'POST',
        data: { 
            lead_id: leadId,
            action: 'resubmit_single'
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                showSuccess('Lead resubmitted successfully');
                setTimeout(loadAllData, 1000);
            } else {
                showError(response.message || 'Failed to resubmit lead');
            }
        },
        error: function(xhr) {
            hideLoading();
            let errorMsg = 'Failed to resubmit lead';
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.message) errorMsg = response.message;
            } catch(e) {}
            showError(errorMsg);
        }
    });
}

function viewLead(leadId) {
    if (!leadId) {
        showError('Invalid lead ID');
        return;
    }
    // Navigate to history page with lead ID
    window.location.href = 'history.php?lead_id=' + encodeURIComponent(leadId);
}

function exportData() {
    const data = queueTable.rows({search: 'applied'}).data().toArray();
    
    if (data.length === 0) {
        showWarning('No data to export');
        return;
    }
    
    // Create CSV
    let csv = 'Lead ID,Email,Name,Phone,Status,Source,Created\n';
    data.forEach(function(row) {
        const name = row.name || ((row.first_name || '') + ' ' + (row.last_name || '')).trim();
        const phone = row.primary_phone || row.phone || '';
        const status = row.boberdoo_status || row.status || 'pending';
        
        csv += '"' + (row.lead_id || '') + '",';
        csv += '"' + (row.email || '') + '",';
        csv += '"' + name + '",';
        csv += '"' + phone + '",';
        csv += '"' + status + '",';
        csv += '"' + (row.source || '') + '",';
        csv += '"' + (row.created_at || row.created || '') + '"\n';
    });
    
    // Download file
    const blob = new Blob([csv], {type: 'text/csv'});
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'resubmission_queue_' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
    window.URL.revokeObjectURL(url);
    
    showSuccess('Export completed');
}

function refreshData() {
    showToast('Refreshing data...', 'info');
    loadAllData();
}

// Helper function for date formatting
function formatDate(dateStr) {
    if (!dateStr) return '-';
    try {
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return dateStr;
        
        const options = {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        return date.toLocaleDateString('en-US', options);
    } catch(e) {
        return dateStr;
    }
}

// Toast notification helper
function showToast(message, type) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: type,
            title: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    } else {
        console.log(type + ': ' + message);
    }
}
</script>

<style>
.stat-card {
    cursor: pointer;
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.table .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>
SCRIPT;

// Include footer
include __DIR__ . '/includes/footer.php';
?>