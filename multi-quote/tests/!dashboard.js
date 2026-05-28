/**
 * Dashboard JavaScript
 * Handles all dashboard functionality and AJAX requests
 */

// Global variables
let selectedLeads = [];
let dataTables = {};
let charts = {};

// Initialize on document ready
$(document).ready(function() {
    initializeDashboard();
    loadDashboardData();
    setupEventHandlers();
    startAutoRefresh();
});

/**
 * Initialize Dashboard Components
 */
function initializeDashboard() {
    // Initialize DataTables
    initializeDataTables();
    
    // Initialize Charts
    initializeCharts();
    
    // Set default dates
    setDefaultDates();
    
    // Load user preferences
    loadUserPreferences();
}

/**
 * Initialize DataTables
 */
function initializeDataTables() {
    // Recent submissions table
    dataTables.recent = $('#recentTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 10,
        responsive: true,
        columns: [
            { data: 'time' },
            { data: 'email' },
            { data: 'phone' },
            { 
                data: 'status',
                render: function(data) {
                    return renderStatusBadge(data);
                }
            },
            { 
                data: 'actions',
                orderable: false,
                render: function(data, type, row) {
                    return renderActionButtons(row);
                }
            }
        ]
    });
    
    // Buffer table
    dataTables.buffer = $('#bufferTable').DataTable({
        order: [[5, 'desc']],
        pageLength: 25,
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf'
        ],
        columns: [
            { 
                data: 'select',
                orderable: false,
                render: function(data, type, row) {
                    return `<input type="checkbox" class="lead-checkbox" data-id="${row.id}">`;
                }
            },
            { data: 'lead_id' },
            { data: 'name' },
            { data: 'email' },
            { data: 'phone' },
            { data: 'submitted' },
            { data: 'expires' },
            { 
                data: 'status',
                render: function(data) {
                    return renderStatusBadge(data);
                }
            },
            { data: 'resubmits' },
            { 
                data: 'actions',
                orderable: false,
                render: function(data, type, row) {
                    return renderBufferActions(row);
                }
            }
        ]
    });
    
    // History table
    dataTables.history = $('#historyTable').DataTable({
        order: [[1, 'desc']],
        pageLength: 50,
        responsive: true,
        serverSide: true,
        ajax: {
            url: '/multi-quote/inc/api/get-history.php',
            type: 'POST'
        },
        columns: [
            { data: 'lead_id' },
            { data: 'timestamp' },
            { data: 'name' },
            { data: 'email' },
            { data: 'phone' },
            { data: 'state' },
            { data: 'campaign' },
            { 
                data: 'status',
                render: function(data) {
                    return renderStatusBadge(data);
                }
            },
            { data: 'response_time' },
            { 
                data: 'details',
                orderable: false,
                render: function(data, type, row) {
                    return `<button class="btn btn-sm btn-info" onclick="viewLeadDetails('${row.id}')">
                            <i class="fas fa-eye"></i>
                        </button>`;
                }
            }
        ]
    });
    
    // Blacklist table
    dataTables.blacklist = $('#blacklistTable').DataTable({
        order: [[5, 'desc']],
        pageLength: 25,
        responsive: true
    });
    
    // Queue table
    dataTables.queue = $('#queueTable').DataTable({
        order: [[3, 'desc']],
        pageLength: 25,
        responsive: true
    });
    
    // Activity table
    dataTables.activity = $('#activityTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true
    });
}

/**
 * Initialize Charts
 */
function initializeCharts() {
    // Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    charts.trend = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Total Submissions',
                data: [],
                borderColor: '#005D75',
                backgroundColor: 'rgba(0, 93, 117, 0.1)',
                tension: 0.3
            }, {
                label: 'Successful',
                data: [],
                borderColor: '#1B8335',
                backgroundColor: 'rgba(27, 131, 53, 0.1)',
                tension: 0.3
            }, {
                label: 'Failed',
                data: [],
                borderColor: '#B2282E',
                backgroundColor: 'rgba(178, 40, 46, 0.1)',
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    charts.status = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Success', 'Error', 'Pending', 'Blacklisted'],
            datasets: [{
                data: [0, 0, 0, 0],
                backgroundColor: [
                    '#1B8335',
                    '#B2282E',
                    '#FF8300',
                    '#666666'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    
    // Volume Chart
    const volumeCtx = document.getElementById('volumeChart')?.getContext('2d');
    if (volumeCtx) {
        charts.volume = new Chart(volumeCtx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Submissions',
                    data: [],
                    backgroundColor: '#00B9E9'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    // Success Rate Chart
    const successRateCtx = document.getElementById('successRateChart')?.getContext('2d');
    if (successRateCtx) {
        charts.successRate = new Chart(successRateCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Success Rate %',
                    data: [],
                    borderColor: '#1B8335',
                    backgroundColor: 'rgba(27, 131, 53, 0.1)',
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    }
}

/**
 * Setup Event Handlers
 */
function setupEventHandlers() {
    // Navigation
    $('.sidebar .nav-link').on('click', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        navigateToPage(page);
    });
    
    // Filter changes
    $('#bufferStatusFilter, #bufferDateFilter, #bufferResubmitFilter').on('change', function() {
        applyBufferFilters();
    });
    
    // Lead selection
    $(document).on('change', '.lead-checkbox', function() {
        const leadId = $(this).data('id');
        if ($(this).is(':checked')) {
            selectedLeads.push(leadId);
        } else {
            selectedLeads = selectedLeads.filter(id => id !== leadId);
        }
        updateBulkActions();
    });
    
    // Select all checkbox
    $('#selectAll').on('change', function() {
        $('.lead-checkbox').prop('checked', $(this).is(':checked')).trigger('change');
    });
    
    // Mobile menu toggle
    $('#menuToggle').on('click', function() {
        $('.sidebar').toggleClass('show');
    });
}

/**
 * Load Dashboard Data
 */
function loadDashboardData() {
    // Load today's stats
    $.ajax({
        url: '/multi-quote/inc/api/get-stats.php',
        type: 'GET',
        data: { period: 'today' },
        success: function(response) {
            if (response.success) {
                $('#stat-total-today').text(response.data.total);
                $('#stat-successful').text(response.data.successful);
                $('#stat-failed').text(response.data.failed);
                $('#stat-pending').text(response.data.pending);
                
                // Update status chart
                charts.status.data.datasets[0].data = [
                    response.data.successful,
                    response.data.failed,
                    response.data.pending,
                    response.data.blacklisted
                ];
                charts.status.update();
            }
        }
    });
    
    // Load trend data
    loadTrendData();
    
    // Load recent submissions
    loadRecentSubmissions();
}

/**
 * Load Trend Data
 */
function loadTrendData() {
    $.ajax({
        url: '/multi-quote/inc/api/get-trends.php',
        type: 'GET',
        data: { days: 7 },
        success: function(response) {
            if (response.success) {
                const labels = response.data.map(item => item.date);
                const total = response.data.map(item => item.total);
                const successful = response.data.map(item => item.successful);
                const failed = response.data.map(item => item.failed);
                
                charts.trend.data.labels = labels;
                charts.trend.data.datasets[0].data = total;
                charts.trend.data.datasets[1].data = successful;
                charts.trend.data.datasets[2].data = failed;
                charts.trend.update();
            }
        }
    });
}

/**
 * Load Recent Submissions
 */
function loadRecentSubmissions() {
    $.ajax({
        url: '/multi-quote/inc/api/get-recent.php',
        type: 'GET',
        data: { limit: 10 },
        success: function(response) {
            if (response.success) {
                dataTables.recent.clear();
                dataTables.recent.rows.add(response.data);
                dataTables.recent.draw();
            }
        }
    });
}

/**
 * Navigate to Page
 */
function navigateToPage(page) {
    // Update navigation
    $('.sidebar .nav-link').removeClass('active');
    $(`.sidebar .nav-link[data-page="${page}"]`).addClass('active');
    
    // Hide all sections
    $('.page-section').hide();
    
    // Show selected section
    $(`#${page}-section`).show();
    
    // Load page-specific data
    switch(page) {
        case 'dashboard':
            loadDashboardData();
            break;
        case 'buffer':
            loadBufferData();
            break;
        case 'history':
            loadHistoryData();
            break;
        case 'blacklist':
            loadBlacklistData();
            break;
        case 'resubmission':
            loadQueueData();
            break;
        case 'analytics':
            loadAnalytics();
            break;
        case 'activity':
            loadActivityLog();
            break;
    }
    
    // Update URL without reload
    history.pushState({ page: page }, '', `#${page}`);
}

/**
 * Load Buffer Data
 */
function loadBufferData() {
    showLoading();
    
    $.ajax({
        url: '/multi-quote/inc/api/get-buffer.php',
        type: 'POST',
        data: {
            status: $('#bufferStatusFilter').val(),
            dateRange: $('#bufferDateFilter').val(),
            resubmits: $('#bufferResubmitFilter').val()
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                dataTables.buffer.clear();
                dataTables.buffer.rows.add(response.data);
                dataTables.buffer.draw();
            }
        },
        error: function() {
            hideLoading();
            showError('Failed to load buffer data');
        }
    });
}

/**
 * Load Blacklist Data
 */
function loadBlacklistData() {
    showLoading();
    
    $.ajax({
        url: '/multi-quote/inc/api/get-blacklist.php',
        type: 'GET',
        success: function(response) {
            hideLoading();
            if (response.success) {
                // Update stats
                $('#totalBlacklisted').text(response.stats.total);
                $('#permanentlyBlocked').text(response.stats.permanent);
                $('#temporarilyBlocked').text(response.stats.temporary);
                $('#blockedToday').text(response.stats.today);
                
                // Update table
                dataTables.blacklist.clear();
                dataTables.blacklist.rows.add(response.data);
                dataTables.blacklist.draw();
            }
        },
        error: function() {
            hideLoading();
            showError('Failed to load blacklist data');
        }
    });
}

/**
 * Load Queue Data
 */
function loadQueueData() {
    showLoading();
    
    $.ajax({
        url: '/multi-quote/inc/api/get-queue.php',
        type: 'GET',
        success: function(response) {
            hideLoading();
            if (response.success) {
                $('#queueCount').text(response.count);
                $('#queueStatus').text(response.status);
                
                dataTables.queue.clear();
                dataTables.queue.rows.add(response.data);
                dataTables.queue.draw();
            }
        },
        error: function() {
            hideLoading();
            showError('Failed to load queue data');
        }
    });
}

/**
 * Individual Lead Resubmit
 */
function resubmitLead(leadId) {
    Swal.fire({
        title: 'Confirm Resubmission',
        text: 'Are you sure you want to resubmit this lead?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Resubmit',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            showLoading();
            
            $.ajax({
                url: '/multi-quote/inc/api/resubmit.php',
                type: 'POST',
                data: {
                    action: 'resubmit_single',
                    lead_ids: [leadId]
                },
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        Swal.fire('Success', response.message, 'success');
                        loadBufferData();
                    } else {
                        Swal.fire('Error', response.message || 'Resubmission failed', 'error');
                    }
                },
                error: function() {
                    hideLoading();
                    Swal.fire('Error', 'Failed to resubmit lead', 'error');
                }
            });
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
    
    let scheduledTime = null;
    if (schedule !== 'now') {
        const hours = parseInt(schedule);
        scheduledTime = new Date(Date.now() + hours * 3600000).toISOString();
    }
    
    $('#resubmitModal').modal('hide');
    showLoading();
    
    const action = schedule === 'now' ? 'resubmit_bulk' : 'queue_bulk';
    
    $.ajax({
        url: '/multi-quote/inc/api/resubmit.php',
        type: 'POST',
        data: {
            action: action,
            lead_ids: selectedLeads,
            priority: priority,
            scheduled_time: scheduledTime
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                Swal.fire('Success', response.message, 'success');
                clearSelection();
                loadBufferData();
            } else {
                Swal.fire('Error', response.message || 'Operation failed', 'error');
            }
        },
        error: function() {
            hideLoading();
            Swal.fire('Error', 'Failed to process request', 'error');
        }
    });
}

/**
 * View Lead Details
 */
function viewLeadDetails(leadId) {
    showLoading();
    
    $.ajax({
        url: '/multi-quote/inc/api/get-lead-details.php',
        type: 'GET',
        data: { id: leadId },
        success: function(response) {
            hideLoading();
            if (response.success) {
                const lead = response.data;
                
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Personal Information</h6>
                            <p><strong>Name:</strong> ${lead.first_name} ${lead.last_name}</p>
                            <p><strong>Email:</strong> ${lead.email}</p>
                            <p><strong>Phone:</strong> ${lead.phone}</p>
                            <p><strong>DOB:</strong> ${lead.dob}</p>
                            <p><strong>Gender:</strong> ${lead.gender}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Location</h6>
                            <p><strong>City:</strong> ${lead.city}</p>
                            <p><strong>State:</strong> ${lead.state}</p>
                            <p><strong>ZIP:</strong> ${lead.zip}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Submission Details</h6>
                            <p><strong>Lead ID:</strong> ${lead.lead_id}</p>
                            <p><strong>Submitted:</strong> ${lead.submission_time}</p>
                            <p><strong>IP Address:</strong> ${lead.ip_address}</p>
                            <p><strong>Campaign:</strong> ${lead.campaign || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Boberdoo Response</h6>
                            <p><strong>Status:</strong> ${renderStatusBadge(lead.boberdoo_status)}</p>
                            <p><strong>Lead ID:</strong> ${lead.boberdoo_lead_id || 'N/A'}</p>
                            <p><strong>Response Time:</strong> ${lead.response_time_ms || 'N/A'}ms</p>
                        </div>
                    </div>
                `;
                
                if (lead.resubmit_history && lead.resubmit_history.length > 0) {
                    html += `
                        <hr>
                        <h6>Resubmission History</h6>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Admin</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    
                    lead.resubmit_history.forEach(attempt => {
                        html += `
                            <tr>
                                <td>${attempt.timestamp}</td>
                                <td>${renderStatusBadge(attempt.status)}</td>
                                <td>${attempt.admin_user}</td>
                            </tr>
                        `;
                    });
                    
                    html += '</tbody></table>';
                }
                
                $('#leadDetailsContent').html(html);
                $('#leadDetailsModal').modal('show');
            }
        },
        error: function() {
            hideLoading();
            showError('Failed to load lead details');
        }
    });
}

/**
 * Add to Blacklist
 */
function addToBlacklist() {
    const email = $('#blacklistEmail').val();
    const phone = $('#blacklistPhone').val();
    const type = $('#blacklistType').val();
    
    if (!email && !phone) {
        Swal.fire('Error', 'Please enter an email or phone number', 'error');
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: '/multi-quote/inc/api/blacklist.php',
        type: 'POST',
        data: {
            action: 'add',
            email: email,
            phone: phone,
            type: type
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                Swal.fire('Success', 'Added to blacklist', 'success');
                $('#blacklistEmail').val('');
                $('#blacklistPhone').val('');
                loadBlacklistData();
            } else {
                Swal.fire('Error', response.message || 'Failed to add to blacklist', 'error');
            }
        },
        error: function() {
            hideLoading();
            Swal.fire('Error', 'Failed to add to blacklist', 'error');
        }
    });
}

/**
 * Remove from Blacklist
 */
function removeFromBlacklist(email) {
    Swal.fire({
        title: 'Remove from Blacklist',
        text: 'Are you sure you want to remove this entry from the blacklist?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Remove',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            showLoading();
            
            $.ajax({
                url: '/multi-quote/inc/api/blacklist.php',
                type: 'POST',
                data: {
                    action: 'remove',
                    email: email
                },
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        Swal.fire('Success', 'Removed from blacklist', 'success');
                        loadBlacklistData();
                    } else {
                        Swal.fire('Error', response.message || 'Failed to remove', 'error');
                    }
                },
                error: function() {
                    hideLoading();
                    Swal.fire('Error', 'Failed to remove from blacklist', 'error');
                }
            });
        }
    });
}

/**
 * Update Analytics
 */
function updateAnalytics() {
    const startDate = $('#analyticsStartDate').val();
    const endDate = $('#analyticsEndDate').val();
    const groupBy = $('#analyticsGroupBy').val();
    
    if (!startDate || !endDate) {
        Swal.fire('Error', 'Please select date range', 'error');
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: '/multi-quote/inc/api/get-analytics.php',
        type: 'POST',
        data: {
            start_date: startDate,
            end_date: endDate,
            group_by: groupBy
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                updateAnalyticsCharts(response.data);
            }
        },
        error: function() {
            hideLoading();
            showError('Failed to load analytics');
        }
    });
}

/**
 * Update Analytics Charts
 */
function updateAnalyticsCharts(data) {
    // Update volume chart
    if (charts.volume) {
        charts.volume.data.labels = data.dates;
        charts.volume.data.datasets[0].data = data.volumes;
        charts.volume.update();
    }
    
    // Update success rate chart
    if (charts.successRate) {
        charts.successRate.data.labels = data.dates;
        charts.successRate.data.datasets[0].data = data.successRates;
        charts.successRate.update();
    }
    
    // Update states chart
    if (data.topStates && $('#statesChart').length) {
        if (!charts.states) {
            const ctx = document.getElementById('statesChart').getContext('2d');
            charts.states = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: Object.keys(data.topStates),
                    datasets: [{
                        label: 'Submissions',
                        data: Object.values(data.topStates),
                        backgroundColor: '#005D75'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        } else {
            charts.states.data.labels = Object.keys(data.topStates);
            charts.states.data.datasets[0].data = Object.values(data.topStates);
            charts.states.update();
        }
    }
    
    // Update hourly chart
    if (data.hourlyDistribution && $('#hourlyChart').length) {
        if (!charts.hourly) {
            const ctx = document.getElementById('hourlyChart').getContext('2d');
            charts.hourly = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: Array.from({length: 24}, (_, i) => i + ':00'),
                    datasets: [{
                        label: 'Submissions',
                        data: data.hourlyDistribution,
                        borderColor: '#00B9E9',
                        backgroundColor: 'rgba(0, 185, 233, 0.1)',
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        } else {
            charts.hourly.data.datasets[0].data = data.hourlyDistribution;
            charts.hourly.update();
        }
    }
}

/**
 * Export Report
 */
function exportReport(format) {
    const startDate = $('#analyticsStartDate').val();
    const endDate = $('#analyticsEndDate').val();
    
    if (!startDate || !endDate) {
        Swal.fire('Error', 'Please select date range', 'error');
        return;
    }
    
    showLoading();
    
    // Create download link
    const params = new URLSearchParams({
        format: format,
        start_date: startDate,
        end_date: endDate
    });
    
    window.location.href = `/multi-quote/inc/api/export-report.php?${params.toString()}`;
    
    setTimeout(() => {
        hideLoading();
        Swal.fire('Success', 'Report exported successfully', 'success');
    }, 2000);
}

/**
 * Helper Functions
 */
function renderStatusBadge(status) {
    const badges = {
        'success': '<span class="badge badge-status badge-success">Success</span>',
        'error': '<span class="badge badge-status badge-error">Error</span>',
        'pending': '<span class="badge badge-status badge-pending">Pending</span>',
        'resubmitted': '<span class="badge badge-status badge-success">Resubmitted</span>',
        'blacklisted': '<span class="badge badge-status badge-blacklisted">Blacklisted</span>',
        'expired': '<span class="badge badge-status badge-error">Expired</span>'
    };
    
    return badges[status] || `<span class="badge badge-status">${status}</span>`;
}

function renderActionButtons(row) {
    return `
        <div class="action-buttons">
            <button class="btn btn-sm btn-info" onclick="viewLeadDetails('${row.id}')">
                <i class="fas fa-eye"></i>
            </button>
        </div>
    `;
}

function renderBufferActions(row) {
    let actions = `
        <div class="action-buttons">
            <button class="btn btn-sm btn-info" onclick="viewLeadDetails('${row.id}')" title="View Details">
                <i class="fas fa-eye"></i>
            </button>
    `;
    
    if (row.status !== 'success') {
        actions += `
            <button class="btn btn-sm btn-success" onclick="resubmitLead('${row.id}')" title="Resubmit">
                <i class="fas fa-redo"></i>
            </button>
        `;
    }
    
    actions += `
            <button class="btn btn-sm btn-warning" onclick="addToQueue('${row.id}')" title="Add to Queue">
                <i class="fas fa-clock"></i>
            </button>
        </div>
    `;
    
    return actions;
}

function updateBulkActions() {
    $('#selectedCount').text(selectedLeads.length);
    
    if (selectedLeads.length > 0) {
        $('#bulkActions').addClass('show');
    } else {
        $('#bulkActions').removeClass('show');
    }
}

function clearSelection() {
    selectedLeads = [];
    $('.lead-checkbox').prop('checked', false);
    $('#selectAll').prop('checked', false);
    updateBulkActions();
}

function toggleSelectAll() {
    const isChecked = $('#selectAll').is(':checked');
    $('.lead-checkbox').prop('checked', isChecked);
    
    if (isChecked) {
        selectedLeads = $('.lead-checkbox').map(function() {
            return $(this).data('id');
        }).get();
    } else {
        selectedLeads = [];
    }
    
    updateBulkActions();
}

function setDefaultDates() {
    const today = new Date();
    const lastWeek = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
    
    $('#analyticsEndDate').val(today.toISOString().split('T')[0]);
    $('#analyticsStartDate').val(lastWeek.toISOString().split('T')[0]);
}

function loadUserPreferences() {
    const preferences = localStorage.getItem('dashboardPreferences');
    if (preferences) {
        const prefs = JSON.parse(preferences);
        // Apply saved preferences
    }
}

function showLoading() {
    $('.loading-spinner').addClass('show');
}

function hideLoading() {
    $('.loading-spinner').removeClass('show');
}

function showError(message) {
    Swal.fire('Error', message, 'error');
}

function showSuccess(message) {
    Swal.fire('Success', message, 'success');
}

/**
 * Auto-refresh functionality
 */
function startAutoRefresh() {
    // Refresh dashboard data every 30 seconds
    setInterval(function() {
        if ($('#dashboard-section').is(':visible')) {
            loadDashboardData();
        }
    }, 30000);
    
    // Refresh buffer data every minute
    setInterval(function() {
        if ($('#buffer-section').is(':visible')) {
            loadBufferData();
        }
    }, 60000);
}

/**
 * Process Queue
 */
function processQueue() {
    showLoading();
    
    $.ajax({
        url: '/multi-quote/inc/api/resubmit.php',
        type: 'POST',
        data: {
            action: 'process_queue'
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                Swal.fire('Success', 
                    `Processed ${response.processed} items: ${response.successful} successful, ${response.failed} failed`, 
                    'success'
                );
                loadQueueData();
            } else {
                showError('Failed to process queue');
            }
        },
        error: function() {
            hideLoading();
            showError('Failed to process queue');
        }
    });
}

/**
 * Load History Data
 */
function loadHistoryData() {
    // History data is loaded via DataTables server-side processing
    dataTables.history.ajax.reload();
    
    // Load history statistics
    $.ajax({
        url: '/multi-quote/inc/api/get-history-stats.php',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                $('#historyTotal').text(response.data.total);
                $('#avgResponseTime').text(response.data.avgResponseTime + 'ms');
                $('#conversionRate').text(response.data.conversionRate + '%');
                $('#totalRevenue').text('$' + response.data.totalRevenue);
            }
        }
    });
}

/**
 * Load Activity Log
 */
function loadActivityLog() {
    showLoading();
    
    $.ajax({
        url: '/multi-quote/inc/api/get-activity.php',
        type: 'GET',
        data: {
            user: $('#activityUserFilter').val(),
            type: $('#activityTypeFilter').val(),
            date: $('#activityDateFilter').val()
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                dataTables.activity.clear();
                dataTables.activity.rows.add(response.data);
                dataTables.activity.draw();
                
                // Populate user filter if not already done
                if ($('#activityUserFilter option').length === 1) {
                    const users = [...new Set(response.data.map(item => item.user))];
                    users.forEach(user => {
                        $('#activityUserFilter').append(`<option value="${user}">${user}</option>`);
                    });
                }
            }
        },
        error: function() {
            hideLoading();
            showError('Failed to load activity log');
        }
    });
}

/**
 * Filter Activity Log
 */
function filterActivityLog() {
    loadActivityLog();
}

/**
 * Email Report
 */
function emailReport() {
    const startDate = $('#analyticsStartDate').val();
    const endDate = $('#analyticsEndDate').val();
    
    if (!startDate || !endDate) {
        Swal.fire('Error', 'Please select date range', 'error');
        return;
    }
    
    Swal.fire({
        title: 'Email Report',
        input: 'email',
        inputLabel: 'Enter email address',
        inputPlaceholder: 'email@example.com',
        showCancelButton: true,
        confirmButtonText: 'Send Report',
        inputValidator: (value) => {
            if (!value) {
                return 'Please enter an email address';
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                return 'Please enter a valid email address';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            showLoading();
            
            $.ajax({
                url: '/multi-quote/inc/api/email-report.php',
                type: 'POST',
                data: {
                    email: result.value,
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        Swal.fire('Success', 'Report has been sent to ' + result.value, 'success');
                    } else {
                        showError('Failed to send report');
                    }
                },
                error: function() {
                    hideLoading();
                    showError('Failed to send report');
                }
            });
        }
    });
}
