<div class="topbar">
    <div class="topbar-left">
        <h5 class="page-title mb-0">
            <?php echo isset($page_title) ? '<i class="fas ' . (isset($page_icon) ? $page_icon : 'fa-file') . '"></i> ' . $page_title : 'Dashboard'; ?>
        </h5>
    </div>
    
    <div class="topbar-right">
        <!-- Refresh Button -->
        <button class="btn btn-sm btn-outline-primary me-2" onclick="location.reload()">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
        
        <!-- Notifications -->
        <div class="dropdown me-2">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="notificationsDropdown" data-bs-toggle="dropdown">
                <i class="fas fa-bell"></i>
                <span class="badge bg-danger" id="notification-count">0</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown" style="width: 300px;">
                <li><h6 class="dropdown-header">Notifications</h6></li>
                <li><hr class="dropdown-divider"></li>
                <li class="dropdown-item" id="notifications-list">
                    <small class="text-muted">No new notifications</small>
                </li>
            </ul>
        </div>
        
        <!-- User Menu -->
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                <i class="fas fa-user-circle"></i> <?php echo $_SESSION['admin_user'] ?? 'Admin'; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="#"><i class="fas fa-user"></i> Profile</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-cog"></i> Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</div>