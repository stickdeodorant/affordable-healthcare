<?php
/**
 * Blacklist Management Page
 * Manage blocked emails and phone numbers
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
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-dark">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="totalBlacklisted">0</h3>
                        <p>Total Blacklisted</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-danger">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="permanentlyBlocked">0</h3>
                        <p>Permanent Blocks</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="temporarilyBlocked">0</h3>
                        <p>Temporary Blocks</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-info">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="blockedToday">0</h3>
                        <p>Blocked Today</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add to Blacklist Card -->
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <i class="fas fa-plus-circle"></i> Add to Blacklist
            </div>
            <div class="card-body">
                <form id="addBlacklistForm">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="blacklistEmail" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="blacklistEmail" placeholder="email@example.com">
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="blacklistPhone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="blacklistPhone" placeholder="5551234567">
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="blacklistType" class="form-label">Block Type</label>
                                <select class="form-select" id="blacklistType">
                                    <option value="temporary">Temporary (8 hours)</option>
                                    <option value="permanent">Permanent</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-ban"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Note:</strong> At least one field (email or phone) is required. Permanent blocks cannot be automatically removed.
                    </div>
                </form>
            </div>
        </div>

        <!-- Blacklist Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list"></i> Blacklist Entries</span>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-primary" onclick="exportBlacklist()">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="loadBlacklistData()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="blacklistTable">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Attempts</th>
                                <th>First Blocked</th>
                                <th>Last Attempt</th>
                                <th>Block Until</th>
                                <th>Reason</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="9" class="text-center">
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

        <!-- Help Section -->
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <i class="fas fa-info-circle"></i> Blacklist Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>How Blacklisting Works:</h6>
                        <ul>
                            <li><strong>Automatic:</strong> Emails/phones are blacklisted after 5 failed submission attempts</li>
                            <li><strong>Temporary:</strong> 8-hour blocks for suspicious activity</li>
                            <li><strong>Permanent:</strong> Never allowed to submit again</li>
                            <li><strong>Manual:</strong> Admins can add entries at any time</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Best Practices:</h6>
                        <ul>
                            <li>Review temporary blocks before making them permanent</li>
                            <li>Document the reason for manual blacklisting</li>
                            <li>Export regularly for backup and analysis</li>
                            <li>Check for false positives in high-volume periods</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Page-specific JavaScript
$additional_js = <<<'SCRIPT'
<script>
let blacklistTable;

$(document).ready(function() {
    initBlacklistTable();
    loadBlacklistData();
    
    // Form submission
    $('#addBlacklistForm').on('submit', function(e) {
        e.preventDefault();
        addToBlacklist();
    });
    
    // Auto-refresh every 60 seconds
    setInterval(loadBlacklistData, 60000);
});

function initBlacklistTable() {
    blacklistTable = $('#blacklistTable').DataTable({
        order: [[5, 'desc']], // Sort by last attempt
        pageLength: 25,
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-sm btn-success'
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-sm btn-success'
            }
        ],
        columns: [
            { data: 'email' },
            { 
                data: 'phone',
                render: function(data) {
                    return data ? formatPhone(data) : 'N/A';
                }
            },
            { 
                data: 'status',
                render: function(data, type, row) {
                    if (data === 'Permanent') {
                        return '<span class="badge bg-danger">Permanent</span>';
                    } else if (data === 'Temporary') {
                        return '<span class="badge bg-warning">Temporary</span>';
                    } else {
                        return '<span class="badge bg-secondary">Expired</span>';
                    }
                }
            },
            { data: 'attempts' },
            { 
                data: 'first_blocked',
                render: function(data) {
                    return data ? formatDate(data) : 'N/A';
                }
            },
            { 
                data: 'last_attempt',
                render: function(data) {
                    return data ? data : 'N/A';
                }
            },
            { 
                data: 'block_until',
                render: function(data, type, row) {
                    if (row.is_permanent) {
                        return '<span class="text-danger">Forever</span>';
                    }
                    return data ? data : 'N/A';
                }
            },
            { 
                data: 'reason',
                render: function(data) {
                    return data ? '<small>' + data + '</small>' : 'N/A';
                }
            },
            { 
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group">
                            <button class="btn btn-sm btn-info" onclick="viewDetails('${row.email}')" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="removeFromBlacklist('${row.email}')" title="Remove">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });
}

function loadBlacklistData() {
    apiRequest('get-blacklist.php', 'GET')
        .done(function(response) {
            if (response.success) {
                // Update statistics
                $('#totalBlacklisted').text(formatNumber(response.stats.total || 0));
                $('#permanentlyBlocked').text(formatNumber(response.stats.permanent || 0));
                $('#temporarilyBlocked').text(formatNumber(response.stats.temporary || 0));
                $('#blockedToday').text(formatNumber(response.stats.today || 0));
                
                // Update table
                blacklistTable.clear();
                if (response.data && response.data.length > 0) {
                    blacklistTable.rows.add(response.data);
                }
                blacklistTable.draw();
            }
        })
        .fail(function() {
            showError('Failed to load blacklist data');
        });
}

function addToBlacklist() {
    const email = $('#blacklistEmail').val().trim();
    const phone = $('#blacklistPhone').val().trim();
    const type = $('#blacklistType').val();
    
    // Validation
    if (!email && !phone) {
        showWarning('Please enter at least an email or phone number');
        return;
    }
    
    if (email && !validateEmail(email)) {
        showWarning('Please enter a valid email address');
        return;
    }
    
    if (phone && !validatePhone(phone)) {
        showWarning('Please enter a valid phone number (10 digits)');
        return;
    }
    
    // Confirm action
    const message = type === 'permanent' 
        ? 'This will PERMANENTLY block this contact. Are you sure?' 
        : 'This will temporarily block this contact for 8 hours. Continue?';
    
    confirmAction(message, 'Confirm Blacklist').then((result) => {
        if (result.isConfirmed) {
            showLoading();
            
            apiRequest('blacklist.php', 'POST', {
                action: 'add',
                email: email,
                phone: phone,
                type: type
            })
            .done(function(response) {
                hideLoading();
                if (response.success) {
                    showSuccess('Successfully added to blacklist');
                    $('#addBlacklistForm')[0].reset();
                    loadBlacklistData();
                } else {
                    showError(response.error || 'Failed to add to blacklist');
                }
            })
            .fail(function() {
                hideLoading();
                showError('Failed to add to blacklist');
            });
        }
    });
}

function removeFromBlacklist(email) {
    confirmAction('Remove this entry from the blacklist?', 'Confirm Removal').then((result) => {
        if (result.isConfirmed) {
            showLoading();
            
            apiRequest('blacklist.php', 'POST', {
                action: 'remove',
                email: email
            })
            .done(function(response) {
                hideLoading();
                if (response.success) {
                    showSuccess('Successfully removed from blacklist');
                    loadBlacklistData();
                } else {
                    showError(response.error || 'Failed to remove from blacklist');
                }
            })
            .fail(function() {
                hideLoading();
                showError('Failed to remove from blacklist');
            });
        }
    });
}

function viewDetails(email) {
    showLoading();
    
    apiRequest('blacklist.php', 'GET', { action: 'get', email: email })
        .done(function(response) {
            hideLoading();
            if (response.success && response.data && response.data.length > 0) {
                const entry = response.data.find(e => e.email === email);
                if (entry) {
                    let detailsHtml = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Contact Information</h6>
                                <p><strong>Email:</strong> ${entry.email}</p>
                                <p><strong>Phone:</strong> ${entry.phone || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Block Details</h6>
                                <p><strong>Status:</strong> ${entry.status}</p>
                                <p><strong>Type:</strong> ${entry.is_permanent ? 'Permanent' : 'Temporary'}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Activity</h6>
                                <p><strong>Attempts:</strong> ${entry.attempts}</p>
                                <p><strong>First Blocked:</strong> ${entry.first_blocked || 'N/A'}</p>
                                <p><strong>Last Attempt:</strong> ${entry.last_attempt || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Block Information</h6>
                                <p><strong>Block Until:</strong> ${entry.is_permanent ? 'Forever' : (entry.block_until || 'N/A')}</p>
                                <p><strong>Reason:</strong> ${entry.reason || 'N/A'}</p>
                            </div>
                        </div>
                    `;
                    
                    Swal.fire({
                        title: 'Blacklist Entry Details',
                        html: detailsHtml,
                        width: 600,
                        showCloseButton: true
                    });
                }
            }
        })
        .fail(function() {
            hideLoading();
            showError('Failed to load details');
        });
}

function exportBlacklist() {
    blacklistTable.button('.buttons-csv').trigger();
}

// Set up as refresh function for auto-refresh
window.refreshData = loadBlacklistData;
</script>
SCRIPT;

// Include footer
include 'includes/footer.php';
?>