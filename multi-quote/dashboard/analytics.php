<?php
/**
 * Analytics & Reports Page
 * Detailed analytics and reporting functionality
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
        <!-- Date Range Selector -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-calendar"></i> Date Range Selection
            </div>
            <div class="card-body">
                <form id="analyticsForm">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="startDate" required>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="endDate" required>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="groupBy" class="form-label">Group By</label>
                            <select class="form-select" id="groupBy">
                                <option value="day">Day</option>
                                <option value="week">Week</option>
                                <option value="month">Month</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-chart-line"></i> Generate Report
                            </button>
                        </div>
                    </div>
                    
                    <div class="btn-group mt-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDateRange('today')">Today</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDateRange('yesterday')">Yesterday</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDateRange('7days')">Last 7 Days</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDateRange('30days')">Last 30 Days</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDateRange('thismonth')">This Month</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDateRange('lastmonth')">Last Month</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="totalVolume">0</h3>
                        <p>Total Volume</p>
                        <small class="text-muted" id="volumeChange">-</small>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-details">
                        <h3 id="successRate">0%</h3>
                        <p>Success Rate</p>
                        <small class="text-muted" id="rateChange">-</small>
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
                        <small class="text-muted" id="timeChange">-</small>
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
                        <small class="text-muted" id="revenueChange">-</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="row mb-4">
            <div class="col-lg-8 mb-3">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-area"></i> Volume Trend
                    </div>
                    <div class="card-body">
                        <canvas id="volumeChart" height="80"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-3">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-pie"></i> Status Breakdown
                    </div>
                    <div class="card-body">
                        <canvas id="statusBreakdownChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="row mb-4">
            <div class="col-lg-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-line"></i> Success Rate Over Time
                    </div>
                    <div class="card-body">
                        <canvas id="successRateChart" height="60"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-bar"></i> Response Time Trend
                    </div>
                    <div class="card-body">
                        <canvas id="responseTimeChart" height="60"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Geographic & Source Analysis -->
        <div class="row mb-4">
            <div class="col-lg-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-map-marker-alt"></i> Top States
                    </div>
                    <div class="card-body">
                        <canvas id="statesChart" height="80"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-bullhorn"></i> Top Campaigns
                    </div>
                    <div class="card-body">
                        <canvas id="campaignsChart" height="80"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hourly Distribution -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-clock"></i> Hourly Distribution
                    </div>
                    <div class="card-body">
                        <canvas id="hourlyChart" height="60"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Section -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <i class="fas fa-download"></i> Export Reports
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6>Export Options</h6>
                        <p class="text-muted mb-0">Export analytics data for the selected date range in various formats.</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="btn-group">
                            <button class="btn btn-success" onclick="exportReport('pdf')">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                            <button class="btn btn-success" onclick="exportReport('excel')">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                            <button class="btn btn-success" onclick="exportReport('csv')">
                                <i class="fas fa-file-csv"></i> CSV
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Stats Table -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-table"></i> Detailed Statistics
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="statsTable">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Total</th>
                                <th>Successful</th>
                                <th>Failed</th>
                                <th>Pending</th>
                                <th>Success Rate</th>
                                <th>Avg Response</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    Select a date range and click "Generate Report"
                                </td>
                            </tr>
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
let charts = {};

$(document).ready(function() {
    initCharts();
    setDefaultDates();
    
    // Form submission
    $('#analyticsForm').on('submit', function(e) {
        e.preventDefault();
        generateReport();
    });
});

function initCharts() {
    // Volume Chart
    const volumeCtx = document.getElementById('volumeChart').getContext('2d');
    charts.volume = new Chart(volumeCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Total Submissions',
                data: [],
                borderColor: '#2196F3',
                backgroundColor: 'rgba(33, 150, 243, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
    
    // Status Breakdown Chart
    const statusCtx = document.getElementById('statusBreakdownChart').getContext('2d');
    charts.statusBreakdown = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Success', 'Failed', 'Pending'],
            datasets: [{
                data: [0, 0, 0],
                backgroundColor: ['#4CAF50', '#f44336', '#ff9800']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
    
    // Success Rate Chart
    const successCtx = document.getElementById('successRateChart').getContext('2d');
    charts.successRate = new Chart(successCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Success Rate %',
                data: [],
                borderColor: '#4CAF50',
                backgroundColor: 'rgba(76, 175, 80, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
    
    // Response Time Chart
    const responseCtx = document.getElementById('responseTimeChart').getContext('2d');
    charts.responseTime = new Chart(responseCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Response Time (ms)',
                data: [],
                backgroundColor: '#00BCD4'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
    
    // States Chart
    const statesCtx = document.getElementById('statesChart').getContext('2d');
    charts.states = new Chart(statesCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Submissions',
                data: [],
                backgroundColor: '#9C27B0'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { beginAtZero: true }
            }
        }
    });
    
    // Campaigns Chart
    const campaignsCtx = document.getElementById('campaignsChart').getContext('2d');
    charts.campaigns = new Chart(campaignsCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Submissions',
                data: [],
                backgroundColor: '#FF5722'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { beginAtZero: true }
            }
        }
    });
    
    // Hourly Chart
    const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
    charts.hourly = new Chart(hourlyCtx, {
        type: 'line',
        data: {
            labels: Array.from({length: 24}, (_, i) => i + ':00'),
            datasets: [{
                label: 'Submissions',
                data: new Array(24).fill(0),
                borderColor: '#673AB7',
                backgroundColor: 'rgba(103, 58, 183, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

function setDefaultDates() {
    const today = new Date();
    const lastWeek = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
    
    $('#endDate').val(today.toISOString().split('T')[0]);
    $('#startDate').val(lastWeek.toISOString().split('T')[0]);
}

function setDateRange(range) {
    const today = new Date();
    const endDate = today.toISOString().split('T')[0];
    let startDate;
    
    switch(range) {
        case 'today':
            startDate = endDate;
            break;
        case 'yesterday':
            const yesterday = new Date(today.getTime() - 24 * 60 * 60 * 1000);
            startDate = yesterday.toISOString().split('T')[0];
            $('#endDate').val(startDate);
            break;
        case '7days':
            const lastWeek = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
            startDate = lastWeek.toISOString().split('T')[0];
            break;
        case '30days':
            const last30 = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
            startDate = last30.toISOString().split('T')[0];
            break;
        case 'thismonth':
            startDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
            break;
        case 'lastmonth':
            const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            const lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);
            startDate = lastMonth.toISOString().split('T')[0];
            $('#endDate').val(lastMonthEnd.toISOString().split('T')[0]);
            break;
    }
    
    $('#startDate').val(startDate);
    if (range !== 'yesterday' && range !== 'lastmonth') {
        $('#endDate').val(endDate);
    }
}

function generateReport() {
    const startDate = $('#startDate').val();
    const endDate = $('#endDate').val();
    const groupBy = $('#groupBy').val();
    
    if (!startDate || !endDate) {
        showWarning('Please select both start and end dates');
        return;
    }
    
    showLoading();
    
    apiRequest('get-analytics.php', 'POST', {
        start_date: startDate,
        end_date: endDate,
        group_by: groupBy
    })
    .done(function(response) {
        hideLoading();
        if (response.success) {
            updateAnalytics(response.data);
        } else {
            showError('Failed to generate report');
        }
    })
    .fail(function() {
        hideLoading();
        showError('Failed to generate report');
    });
}

function updateAnalytics(data) {
    // Update key metrics
    $('#totalVolume').text(formatNumber(data.summary.total || 0));
    $('#successRate').text((data.summary.success_rate || 0) + '%');
    $('#avgResponseTime').text((data.summary.avg_response_time || 0) + 'ms');
    $('#totalRevenue').text(formatCurrency(data.summary.total_revenue || 0));
    
    // Update change indicators
    if (data.summary.volume_change !== undefined) {
        $('#volumeChange').html(formatChange(data.summary.volume_change));
    }
    if (data.summary.rate_change !== undefined) {
        $('#rateChange').html(formatChange(data.summary.rate_change));
    }
    if (data.summary.time_change !== undefined) {
        $('#timeChange').html(formatChange(data.summary.time_change, true));
    }
    if (data.summary.revenue_change !== undefined) {
        $('#revenueChange').html(formatChange(data.summary.revenue_change));
    }
    
    // Update charts
    if (data.volume_trend) {
        charts.volume.data.labels = data.volume_trend.labels;
        charts.volume.data.datasets[0].data = data.volume_trend.data;
        charts.volume.update();
    }
    
    if (data.status_breakdown) {
        charts.statusBreakdown.data.datasets[0].data = [
            data.status_breakdown.success || 0,
            data.status_breakdown.failed || 0,
            data.status_breakdown.pending || 0
        ];
        charts.statusBreakdown.update();
    }
    
    if (data.success_rate_trend) {
        charts.successRate.data.labels = data.success_rate_trend.labels;
        charts.successRate.data.datasets[0].data = data.success_rate_trend.data;
        charts.successRate.update();
    }
    
    if (data.response_time_trend) {
        charts.responseTime.data.labels = data.response_time_trend.labels;
        charts.responseTime.data.datasets[0].data = data.response_time_trend.data;
        charts.responseTime.update();
    }
    
    if (data.top_states) {
        charts.states.data.labels = Object.keys(data.top_states);
        charts.states.data.datasets[0].data = Object.values(data.top_states);
        charts.states.update();
    }
    
    if (data.top_campaigns) {
        charts.campaigns.data.labels = Object.keys(data.top_campaigns);
        charts.campaigns.data.datasets[0].data = Object.values(data.top_campaigns);
        charts.campaigns.update();
    }
    
    if (data.hourly_distribution) {
        charts.hourly.data.datasets[0].data = data.hourly_distribution;
        charts.hourly.update();
    }
    
    // Update detailed stats table
    updateStatsTable(data.detailed_stats);
}

function updateStatsTable(stats) {
    let html = '';
    
    if (!stats || stats.length === 0) {
        html = '<tr><td colspan="8" class="text-center text-muted">No data available</td></tr>';
    } else {
        stats.forEach(function(row) {
            const successRate = row.total > 0 ? ((row.successful / row.total) * 100).toFixed(1) : 0;
            html += `
                <tr>
                    <td>${row.period}</td>
                    <td>${formatNumber(row.total)}</td>
                    <td class="text-success">${formatNumber(row.successful)}</td>
                    <td class="text-danger">${formatNumber(row.failed)}</td>
                    <td class="text-warning">${formatNumber(row.pending)}</td>
                    <td>${successRate}%</td>
                    <td>${row.avg_response || 0}ms</td>
                    <td>${formatCurrency(row.revenue || 0)}</td>
                </tr>
            `;
        });
    }
    
    $('#statsTable tbody').html(html);
}

function formatChange(value, inverse = false) {
    if (value === 0) return '<span class="text-muted">No change</span>';
    
    const isPositive = inverse ? value < 0 : value > 0;
    const color = isPositive ? 'success' : 'danger';
    const icon = isPositive ? 'up' : 'down';
    
    return `<span class="text-${color}"><i class="fas fa-arrow-${icon}"></i> ${Math.abs(value).toFixed(1)}%</span>`;
}

function exportReport(format) {
    const startDate = $('#startDate').val();
    const endDate = $('#endDate').val();
    
    if (!startDate || !endDate) {
        showWarning('Please select date range first');
        return;
    }
    
    showLoading();
    
    // Create download link
    const params = new URLSearchParams({
        format: format,
        start_date: startDate,
        end_date: endDate
    });
    
    window.location.href = `../inc/api/export-report.php?${params.toString()}`;
    
    setTimeout(() => {
        hideLoading();
        showSuccess('Report exported successfully');
    }, 2000);
}

// Set up as refresh function for auto-refresh
window.refreshData = function() {
    if ($('#startDate').val() && $('#endDate').val()) {
        generateReport();
    }
};
</script>
SCRIPT;

// Include footer
include 'includes/footer.php';
?>