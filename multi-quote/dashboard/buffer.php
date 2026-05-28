<?php
/**
 * Dashboard - Module 2: Buffer Management
 * 7-Day buffer with individual and bulk resubmission
 */

// Load configuration
require_once __DIR__ . '/includes/config.php';

// Page-specific configuration
$page_title = '7-Day Buffer';
$page_icon = 'fa-inbox';
$use_charts = false;
$use_datatables = true;

// Include header
include __DIR__ . '/includes/header.php';
?>

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main-content">
    <?php include __DIR__ . '/includes/topbar.php'; ?>
    
    <div class="content-wrapper">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1><i class="fas fa-inbox"></i> 7-Day Buffer</h1>
                <p class="text-muted">Temporary lead storage with resubmission capabilities</p>
            </div>
            <div>
                <button class="btn btn-success" id="bulkResubmitBtn" disabled>
                    <i class="fas fa-redo"></i> Resubmit Selected (<span id="selectedCount">0</span>)
                </button>
                <button class="btn btn-danger" id="clearExpiredBtn" onclick="clearExpired()">
                    <i class="fas fa-trash"></i> Clear Expired
                </button>
                <button class="btn btn-primary" onclick="refreshBuffer()">
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
                        <h3 id="totalBuffer">0</h3>
                        <p>Total in Buffer</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-danger">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="errorCount">0</h3>
                        <p>Failed Leads</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="expiringCount">0</h3>
                        <p>Expiring Soon</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success">
                        <i class="fas fa-redo"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="readyResubmit">0</h3>
                        <p>Ready for Resubmit</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-filter"></i> Filters
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">All Statuses</option>
                            <option value="success">Success</option>
                            <option value="error">Error</option>
                            <option value="failed">Failed</option>
                            <option value="pending">Pending</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Date Range</label>
                        <select class="form-select" id="dateRangeFilter">
                            <option value="all">All Time</option>
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="3days">Last 3 Days</option>
                            <option value="7days" selected>Last 7 Days</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Resubmit Count</label>
                        <select class="form-select" id="resubmitFilter">
                            <option value="">All</option>
                            <option value="0">Never Resubmitted</option>
                            <option value="1">1 Attempt</option>
                            <option value="2">2 Attempts</option>
                            <option value="3+">3+ Attempts</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button class="btn btn-primary w-100" onclick="applyFilters()">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Buffer Table -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <span><i class="fas fa-list"></i> Lead Buffer</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="bufferTable">
                        <thead>
                            <tr>
                                <th width="30"><input type="checkbox" id="selectAllCheckbox"></th>
                                <th>Lead ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Expires</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via DataTables -->
                        </tbody>
                    </table>
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

<!-- Bulk Operations Modal -->
<div class="modal fade" id="bulkOperationsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-redo"></i> Bulk Resubmission
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>You are about to resubmit <strong id="selectedLeadsCount">0</strong> selected lead(s).</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> This will attempt to reprocess all selected leads.
                </div>
                <div id="selectedLeadsList" style="max-height: 200px; overflow-y: auto;">
                    <!-- Selected leads will be listed here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="performBulkResubmit()">
                    <i class="fas fa-redo"></i> Confirm Resubmit
                </button>
            </div>
        </div>
    </div>
</div>

<?php $additional_js = '<script src="assets/js/buffer.js"></script>'; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>