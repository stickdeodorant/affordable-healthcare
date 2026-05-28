let bufferTable;
let selectedLeads = [];
let currentLeadData = {};

$(document).ready(function() {
    initializeBufferTable();
    loadBufferStats();
    setupEventHandlers();
    
    // Refresh every 60 seconds
    setInterval(function() {
        refreshBuffer();
    }, 60000);
});

function initializeBufferTable() {
    bufferTable = $('#bufferTable').DataTable({
        processing: true,
        ajax: {
            url: '../inc/api/get-buffer.php',
            type: 'GET',
            dataSrc: function(json) {
                if (json.success) {
                    updateStatistics(json.data);
                    return json.data;
                }
                console.error('Failed to load buffer data');
                return [];
            },
            error: function(xhr, error, code) {
                console.error('DataTable error:', error);
                showError('Failed to load buffer data');
            }
        },
        columns: [
            { 
                data: null,
                orderable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    return `<input type="checkbox" class="lead-checkbox" value="${row.lead_id}" data-row='${JSON.stringify(row).replace(/'/g, "&apos;")}'>`;
                }
            },
            { 
                data: 'lead_id',
                render: function(data) {
                    return `<span class="badge bg-secondary">${data || '-'}</span>`;
                }
            },
            { 
                data: null,
                render: function(data, type, row) {
                    const name = `${row.first_name || ''} ${row.last_name || ''}`.trim();
                    return `<strong>${escapeHtml(name || '-')}</strong>`;
                }
            },
            { 
                data: 'email',
                render: function(data) {
                    return escapeHtml(data || '-');
                }
            },
            { 
                data: null,
                render: function(data, type, row) {
                    return escapeHtml(row.phone || row.primary_phone || '-');
                }
            },
            { 
                data: null,
                render: function(data, type, row) {
                    return `${escapeHtml(row.city || '-')}, ${escapeHtml(row.state || '-')}`;
                }
            },
            { 
                data: 'boberdoo_status',
                render: function(data) {
                    return getStatusBadge(data);
                }
            },
            { 
                data: 'created_at',
                render: function(data) {
                    return formatDateTime(data);
                }
            },
            { 
                data: 'expires_at',
                render: function(data) {
                    if (!data) return '-';
                    const expires = new Date(data);
                    const now = new Date();
                    const daysLeft = Math.ceil((expires - now) / (1000 * 60 * 60 * 24));
                    
                    let badge = 'bg-success';
                    if (daysLeft <= 1) badge = 'bg-danger';
                    else if (daysLeft <= 3) badge = 'bg-warning';
                    
                    return `<span class="badge ${badge}">${daysLeft} days</span>`;
                }
            },
            { 
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-info" onclick='viewLeadDetails(${JSON.stringify(row).replace(/'/g, "&apos;")})'
                                    title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-success" onclick="resubmitSingle('${row.lead_id}')" 
                                    title="Resubmit" ${row.boberdoo_status === 'success' ? 'disabled' : ''}>
                                <i class="fas fa-redo"></i>
                            </button>
                            <button class="btn btn-danger" onclick="blacklistSingle('${row.email}', '${row.phone || row.primary_phone}')" 
                                    title="Blacklist">
                                <i class="fas fa-ban"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[7, 'desc']], // Sort by created date
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        responsive: true,
        language: {
            emptyTable: "No leads in buffer",
            zeroRecords: "No matching leads found",
            processing: "Loading buffer data..."
        }
    });
}

function loadBufferStats() {
    $.ajax({
        url: '../inc/api/get-stats.php',
        type: 'GET',
        data: { period: 'buffer' },
        success: function(response) {
            if (response.success) {
                $('#totalBuffer').text(formatNumber(response.data.total_buffer || 0));
                $('#errorCount').text(formatNumber(response.data.error || 0));
            }
        },
        error: function() {
            console.error('Failed to load buffer statistics');
        }
    });
}

function updateStatistics(data) {
    if (!data) return;
    
    const total = data.length;
    const errors = data.filter(l => l.boberdoo_status === 'error' || l.boberdoo_status === 'failed').length;
    const expiringSoon = data.filter(l => {
        if (!l.expires_at) return false;
        const expires = new Date(l.expires_at);
        const now = new Date();
        const daysLeft = Math.ceil((expires - now) / (1000 * 60 * 60 * 24));
        return daysLeft <= 1;
    }).length;
    const readyForResubmit = data.filter(l => 
        (l.boberdoo_status === 'error' || l.boberdoo_status === 'failed') && 
        (parseInt(l.resubmit_count) || 0) < 3
    ).length;
    
    $('#totalBuffer').text(formatNumber(total));
    $('#errorCount').text(formatNumber(errors));
    $('#expiringCount').text(formatNumber(expiringSoon));
    $('#readyResubmit').text(formatNumber(readyForResubmit));
}

function viewLeadDetails(lead) {
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
                        <td><strong>${escapeHtml(lead.first_name || '')} ${escapeHtml(lead.last_name || '')}</strong></td>
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
                    <tr>
                        <td class="text-muted">IP Address:</td>
                        <td>${escapeHtml(lead.ip_address || '-')}</td>
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
                        <td>${getStatusBadge(lead.boberdoo_status)}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Boberdoo ID:</td>
                        <td>${lead.boberdoo_lead_id || '-'}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Resubmit Count:</td>
                        <td><span class="badge bg-info">${lead.resubmit_count || 0}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Response Code:</td>
                        <td>${lead.response_code ? `<span class="badge bg-secondary">${lead.response_code}</span>` : '-'}</td>
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
                        <td>${formatDateTime(lead.expires_at)}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Last Updated:</td>
                        <td>${formatDateTime(lead.updated_at)}</td>
                    </tr>
                </table>
            </div>
        </div>`;
    
    // Add error information if status is error or failed
    if (lead.boberdoo_status === 'error' || lead.boberdoo_status === 'failed') {
        detailsHtml += `
        <hr>
        <div class="row">
            <div class="col-12">
                <h6 class="text-danger mb-3"><i class="fas fa-exclamation-triangle"></i> Error Information</h6>
                <div class="alert alert-danger">
                    <strong>Error Status:</strong> ${escapeHtml(lead.boberdoo_status || 'Unknown')}<br>
                    ${lead.response_code ? `<strong>Response Code:</strong> ${escapeHtml(lead.response_code)}<br>` : ''}
                    ${lead.error_message ? `<strong>Error Message:</strong> ${escapeHtml(lead.error_message)}<br>` : ''}
                    ${lead.rejection_reason ? `<strong>Rejection Reason:</strong> ${escapeHtml(lead.rejection_reason)}<br>` : ''}
                    <strong>Resubmit Attempts:</strong> ${lead.resubmit_count || 0} of 3 maximum
                </div>
            </div>
        </div>`;
    }
    
    // Add Boberdoo response if available
    if (lead.boberdoo_response) {
        detailsHtml += `
        <hr>
        <div class="row">
            <div class="col-12">
                <h6 class="text-primary mb-3"><i class="fas fa-code"></i> API Response</h6>
                <div class="card bg-light">
                    <div class="card-body">
                        <pre style="max-height: 300px; overflow-y: auto; white-space: pre-wrap; word-wrap: break-word;">
${escapeHtml(typeof lead.boberdoo_response === 'object' ? JSON.stringify(lead.boberdoo_response, null, 2) : lead.boberdoo_response)}
                        </pre>
                    </div>
                </div>
            </div>
        </div>`;
    }
    
    // Add additional fields if present
    if (lead.campaign || lead.source || lead.utm_source) {
        detailsHtml += `
        <hr>
        <div class="row">
            <div class="col-12">
                <h6 class="text-primary mb-3"><i class="fas fa-chart-line"></i> Campaign Information</h6>
                <table class="table table-sm">
                    ${lead.campaign ? `<tr><td class="text-muted" width="40%">Campaign:</td><td>${escapeHtml(lead.campaign)}</td></tr>` : ''}
                    ${lead.source ? `<tr><td class="text-muted">Source:</td><td>${escapeHtml(lead.source)}</td></tr>` : ''}
                    ${lead.utm_source ? `<tr><td class="text-muted">UTM Source:</td><td>${escapeHtml(lead.utm_source)}</td></tr>` : ''}
                    ${lead.utm_medium ? `<tr><td class="text-muted">UTM Medium:</td><td>${escapeHtml(lead.utm_medium)}</td></tr>` : ''}
                    ${lead.utm_campaign ? `<tr><td class="text-muted">UTM Campaign:</td><td>${escapeHtml(lead.utm_campaign)}</td></tr>` : ''}
                </table>
            </div>
        </div>`;
    }
    
    $('#leadDetailsContent').html(detailsHtml);
    
    // Show/hide action buttons based on lead status
    if (lead.boberdoo_status === 'error' || lead.boberdoo_status === 'failed') {
        $('#resubmitLeadBtn').show().off('click').on('click', function() {
            if (confirm('Are you sure you want to resubmit this lead?')) {
                resubmitSingle(currentLeadData.lead_id);
            }
        });
        $('#blacklistLeadBtn').show().off('click').on('click', function() {
            if (confirm('Are you sure you want to blacklist this email/phone? This action cannot be undone.')) {
                blacklistSingle(currentLeadData.email, currentLeadData.phone || currentLeadData.primary_phone);
            }
        });
    } else {
        $('#resubmitLeadBtn').hide();
        $('#blacklistLeadBtn').hide();
    }
    
    // Show the modal
    $('#leadDetailsModal').modal('show');
}

function setupEventHandlers() {
    // Select all checkbox
    $('#selectAllCheckbox').on('change', function() {
        $('.lead-checkbox').prop('checked', this.checked);
        updateSelectedLeads();
    });
    
    // Individual checkboxes
    $(document).on('change', '.lead-checkbox', function() {
        updateSelectedLeads();
    });
    
    // Bulk resubmit button
    $('#bulkResubmitBtn').on('click', function() {
        if (selectedLeads.length > 0) {
            showBulkOperationsModal();
        }
    });
}

function updateSelectedLeads() {
    selectedLeads = [];
    $('.lead-checkbox:checked').each(function() {
        const rowData = $(this).data('row');
        if (rowData) {
            selectedLeads.push(rowData);
        }
    });
    
    $('#selectedCount').text(selectedLeads.length);
    $('#bulkResubmitBtn').prop('disabled', selectedLeads.length === 0);
}

function showBulkOperationsModal() {
    $('#selectedLeadsCount').text(selectedLeads.length);
    
    let listHtml = '<ul class="list-group">';
    selectedLeads.forEach(lead => {
        const name = `${lead.first_name || ''} ${lead.last_name || ''}`.trim() || 'Unknown';
        listHtml += `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><i class="fas fa-user"></i> ${escapeHtml(name)}</span>
                <span class="badge bg-secondary">${escapeHtml(lead.email || '-')}</span>
            </li>`;
    });
    listHtml += '</ul>';
    
    $('#selectedLeadsList').html(listHtml);
    $('#bulkOperationsModal').modal('show');
}

function performBulkResubmit() {
    const leadIds = selectedLeads.map(l => l.lead_id);
    
    $.ajax({
        url: '../inc/api/resubmit.php',
        type: 'POST',
        data: { 
            lead_ids: leadIds,
            bulk: true 
        },
        success: function(response) {
            if (response.success) {
                showToast(`Successfully resubmitted ${response.count || leadIds.length} leads`, 'success');
                $('#bulkOperationsModal').modal('hide');
                refreshBuffer();
            } else {
                showError(response.error || 'Failed to resubmit leads');
            }
        },
        error: function() {
            showError('Failed to resubmit leads');
        }
    });
}

function resubmitSingle(leadId) {
    $.ajax({
        url: '../inc/api/resubmit.php',
        type: 'POST',
        data: { lead_id: leadId },
        success: function(response) {
            if (response.success) {
                showToast('Lead resubmitted successfully', 'success');
                $('#leadDetailsModal').modal('hide');
                refreshBuffer();
            } else {
                showError(response.error || 'Failed to resubmit lead');
            }
        },
        error: function() {
            showError('Failed to resubmit lead');
        }
    });
}

function blacklistSingle(email, phone) {
    $.ajax({
        url: '../inc/api/blacklist.php',
        type: 'POST',
        data: { 
            action: 'add',
            email: email,
            phone: phone,
            reason: 'Manual blacklist from buffer'
        },
        success: function(response) {
            if (response.success) {
                showToast('Lead blacklisted successfully', 'success');
                $('#leadDetailsModal').modal('hide');
                refreshBuffer();
            } else {
                showError(response.error || 'Failed to blacklist lead');
            }
        },
        error: function() {
            showError('Failed to blacklist lead');
        }
    });
}

function clearExpired() {
    if (confirm('This will remove all expired leads from the buffer. Continue?')) {
        $.ajax({
            url: '../inc/api/buffer-maintenance.php',
            type: 'POST',
            data: { action: 'clear_expired' },
            success: function(response) {
                if (response.success) {
                    showToast(`Cleared ${response.count || 0} expired leads`, 'success');
                    refreshBuffer();
                } else {
                    showError(response.error || 'Failed to clear expired leads');
                }
            },
            error: function() {
                showError('Failed to clear expired leads');
            }
        });
    }
}

function applyFilters() {
    const filters = {
        status: $('#statusFilter').val(),
        dateRange: $('#dateRangeFilter').val(),
        resubmits: $('#resubmitFilter').val()
    };
    
    bufferTable.ajax.url('../inc/api/get-buffer.php?' + $.param(filters)).load();
}

function refreshBuffer() {
    if (bufferTable) {
        bufferTable.ajax.reload(null, false);
    }
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