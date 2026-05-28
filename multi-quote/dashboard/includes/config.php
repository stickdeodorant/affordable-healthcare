<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authentication check
if (!isset($_SESSION['admin_authenticated'])) {
    header('Location: ../admin-login.php');
    exit;
}

// Include database configuration - FIXED PATH
require_once __DIR__ . '/../../inc/config/db-config.php';

// Dashboard configuration
define('DASHBOARD_TITLE', 'Lead Management Dashboard');
define('DASHBOARD_VERSION', '1.0.0');
define('ITEMS_PER_PAGE', 25);
define('DATE_FORMAT', 'Y-m-d H:i:s');
define('TIMEZONE', 'America/New_York');

// Set timezone
date_default_timezone_set(TIMEZONE);

// Get current page
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Navigation items with correct paths
$nav_items = [
    'index' => [
        'title' => 'Dashboard',
        'icon' => 'fa-home',
        'url' => 'index.php'
    ],
    'buffer' => [
        'title' => '7-Day Buffer',
        'icon' => 'fa-inbox',
        'url' => 'buffer.php'
    ],
    'history' => [
        'title' => 'History',
        'icon' => 'fa-history',
        'url' => 'history.php'
    ],
    'blacklist' => [
        'title' => 'Blacklist',
        'icon' => 'fa-ban',
        'url' => 'blacklist.php'
    ],
    'resubmission' => [
        'title' => 'Resubmission Queue',
        'icon' => 'fa-redo',
        'url' => 'resubmission.php'
    ],
    'analytics' => [
        'title' => 'Analytics',
        'icon' => 'fa-chart-bar',
        'url' => 'analytics.php'
    ],
    'activity' => [
        'title' => 'Activity Log',
        'icon' => 'fa-list',
        'url' => 'activity.php'
    ]
];

// Helper functions
function isActivePage($page) {
    global $current_page;
    return $current_page === $page ? 'active' : '';
}

function formatDate($date, $format = 'm/d/Y H:i') {
    return date($format, strtotime($date));
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

function getStatusBadge($status) {
    $badges = [
        'success' => '<span class="badge bg-success">Success</span>',
        'error' => '<span class="badge bg-danger">Error</span>',
        'pending' => '<span class="badge bg-warning">Pending</span>',
        'rejected' => '<span class="badge bg-secondary">Rejected</span>',
        'blacklisted' => '<span class="badge bg-dark">Blacklisted</span>'
    ];
    return $badges[strtolower($status)] ?? '<span class="badge bg-secondary">' . htmlspecialchars($status) . '</span>';
}

function getStatusClass($status) {
    $classes = [
        'success' => 'success',
        'error' => 'danger',
        'pending' => 'warning',
        'rejected' => 'secondary'
    ];
    return $classes[strtolower($status)] ?? 'secondary';
}