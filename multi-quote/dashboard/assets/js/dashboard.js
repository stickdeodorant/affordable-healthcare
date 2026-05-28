/**
 * Lead Management Dashboard - Main JavaScript
 * Common functions and utilities
 */

// Global variables
const Dashboard = {
    apiBaseUrl: '../inc/api/',
    refreshInterval: null,
    autoRefreshEnabled: true
};

/**
 * Initialize Dashboard
 */
$(document).ready(function() {
    console.log('Dashboard initialized');
    
    // Setup global AJAX error handler
    setupAjaxErrorHandler();
    
    // Setup auto-refresh if enabled
    if (Dashboard.autoRefreshEnabled) {
        startAutoRefresh();
    }
});

/**
 * Setup AJAX Error Handler
 */
function setupAjaxErrorHandler() {
    $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
        console.error('AJAX Error:', {
            url: settings.url,
            status: jqxhr.status,
            error: thrownError
        });
        
        if (jqxhr.status === 401) {
            showError('Session expired. Please login again.');
            setTimeout(() => {
                window.location.href = '../login.php';
            }, 2000);
        } else if (jqxhr.status === 500) {
            showError('Server error. Please try again later.');
        }
    });
}

/**
 * API Request Helper
 */
function apiRequest(endpoint, method = 'GET', data = {}) {
    return $.ajax({
        url: Dashboard.apiBaseUrl + endpoint,
        type: method,
        data: data,
        dataType: 'json',
        beforeSend: function() {
            showLoading();
        },
        complete: function() {
            hideLoading();
        }
    });
}

/**
 * Show Loading Overlay
 */
function showLoading() {
    $('#loadingOverlay').addClass('show');
}

/**
 * Hide Loading Overlay
 */
function hideLoading() {
    $('#loadingOverlay').removeClass('show');
}

/**
 * Show Success Message
 */
function showSuccess(message, title = 'Success') {
    Swal.fire({
        icon: 'success',
        title: title,
        text: message,
        timer: 3000,
        showConfirmButton: false
    });
}

/**
 * Show Error Message
 */
function showError(message, title = 'Error') {
    Swal.fire({
        icon: 'error',
        title: title,
        text: message
    });
}

/**
 * Show Warning Message
 */
function showWarning(message, title = 'Warning') {
    Swal.fire({
        icon: 'warning',
        title: title,
        text: message
    });
}

/**
 * Show Toast Notification
 */
function showToast(message, type = 'success') {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: type,
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}

/**
 * Confirm Dialog
 */
function confirmAction(message, title = 'Are you sure?') {
    return Swal.fire({
        title: title,
        text: message,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel'
    });
}

/**
 * Format Date
 */
function formatDate(dateString, format = 'short') {
    const date = new Date(dateString);
    
    if (format === 'short') {
        return date.toLocaleDateString();
    } else if (format === 'long') {
        return date.toLocaleString();
    } else if (format === 'time') {
        return date.toLocaleTimeString();
    }
    
    return date.toLocaleString();
}

/**
 * Format Currency
 */
function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

/**
 * Format Phone Number
 */
function formatPhone(phone) {
    const cleaned = ('' + phone).replace(/\D/g, '');
    const match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/);
    
    if (match) {
        return '(' + match[1] + ') ' + match[2] + '-' + match[3];
    }
    
    return phone;
}

/**
 * Get Status Badge HTML
 */
function getStatusBadge(status) {
    const badges = {
        'success': '<span class="badge bg-success">Success</span>',
        'error': '<span class="badge bg-danger">Error</span>',
        'pending': '<span class="badge bg-warning">Pending</span>',
        'rejected': '<span class="badge bg-secondary">Rejected</span>',
        'blacklisted': '<span class="badge bg-dark">Blacklisted</span>'
    };
    
    return badges[status.toLowerCase()] || `<span class="badge bg-secondary">${status}</span>`;
}

/**
 * Export to CSV
 */
function exportToCSV(data, filename = 'export.csv') {
    const csv = convertToCSV(data);
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

/**
 * Convert Array to CSV
 */
function convertToCSV(data) {
    if (data.length === 0) return '';
    
    const headers = Object.keys(data[0]);
    const csv = [headers.join(',')];
    
    data.forEach(row => {
        const values = headers.map(header => {
            const value = row[header] || '';
            return `"${value.toString().replace(/"/g, '""')}"`;
        });
        csv.push(values.join(','));
    });
    
    return csv.join('\n');
}

/**
 * Debounce Function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Copy to Clipboard
 */
function copyToClipboard(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = 0;
    document.body.appendChild(textarea);
    textarea.select();
    
    try {
        document.execCommand('copy');
        showToast('Copied to clipboard!', 'success');
    } catch (err) {
        showError('Failed to copy to clipboard');
    }
    
    document.body.removeChild(textarea);
}

/**
 * Start Auto-Refresh
 */
function startAutoRefresh(interval = 30000) {
    if (Dashboard.refreshInterval) {
        clearInterval(Dashboard.refreshInterval);
    }
    
    Dashboard.refreshInterval = setInterval(() => {
        if (typeof refreshData === 'function') {
            refreshData();
        }
    }, interval);
}

/**
 * Stop Auto-Refresh
 */
function stopAutoRefresh() {
    if (Dashboard.refreshInterval) {
        clearInterval(Dashboard.refreshInterval);
        Dashboard.refreshInterval = null;
    }
}

/**
 * Toggle Auto-Refresh
 */
function toggleAutoRefresh() {
    Dashboard.autoRefreshEnabled = !Dashboard.autoRefreshEnabled;
    
    if (Dashboard.autoRefreshEnabled) {
        startAutoRefresh();
        showToast('Auto-refresh enabled', 'info');
    } else {
        stopAutoRefresh();
        showToast('Auto-refresh disabled', 'info');
    }
}

/**
 * Validate Email
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Validate Phone
 */
function validatePhone(phone) {
    const cleaned = ('' + phone).replace(/\D/g, '');
    return cleaned.length === 10;
}

/**
 * Time Ago Helper
 */
function timeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    const intervals = {
        year: 31536000,
        month: 2592000,
        week: 604800,
        day: 86400,
        hour: 3600,
        minute: 60
    };
    
    for (const [key, value] of Object.entries(intervals)) {
        const interval = Math.floor(seconds / value);
        if (interval >= 1) {
            return interval + ' ' + key + (interval > 1 ? 's' : '') + ' ago';
        }
    }
    
    return 'Just now';
}

/**
 * Number Format Helper
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

/**
 * Percentage Format Helper
 */
function formatPercentage(value, total) {
    if (total === 0) return '0%';
    return Math.round((value / total) * 100) + '%';
}

/**
 * Initialize DataTable with Common Settings
 */
function initDataTable(selector, options = {}) {
    const defaultOptions = {
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            emptyTable: 'No data available',
            zeroRecords: 'No matching records found',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            infoEmpty: 'Showing 0 to 0 of 0 entries',
            infoFiltered: '(filtered from _MAX_ total entries)',
            search: 'Search:',
            paginate: {
                first: 'First',
                last: 'Last',
                next: 'Next',
                previous: 'Previous'
            }
        }
    };
    
    return $(selector).DataTable({...defaultOptions, ...options});
}

// Export functions for use in other scripts
window.Dashboard = Dashboard;
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.showSuccess = showSuccess;
window.showError = showError;
window.showWarning = showWarning;
window.showToast = showToast;
window.confirmAction = confirmAction;
window.apiRequest = apiRequest;
window.formatDate = formatDate;
window.formatCurrency = formatCurrency;
window.formatPhone = formatPhone;
window.getStatusBadge = getStatusBadge;
window.copyToClipboard = copyToClipboard;
window.exportToCSV = exportToCSV;
window.validateEmail = validateEmail;
window.validatePhone = validatePhone;
window.timeAgo = timeAgo;
window.formatNumber = formatNumber;
window.formatPercentage = formatPercentage;
window.initDataTable = initDataTable;