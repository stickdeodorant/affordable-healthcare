<div class="sidebar">
    <!-- Logo / Brand -->
    <div class="sidebar-header">
        <h4><i class="fas fa-chart-line"></i> Lead Manager</h4>
        <p class="text-muted mb-0">v<?php echo DASHBOARD_VERSION; ?></p>
    </div>
    
    <!-- Navigation -->
    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <?php foreach ($nav_items as $page => $item): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isActivePage($page); ?>" href="<?php echo $item['url']; ?>">
                    <i class="fas <?php echo $item['icon']; ?>"></i>
                    <span><?php echo $item['title']; ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    
    <!-- Quick Stats in Sidebar -->
    <div class="sidebar-footer">
        <div class="quick-stat">
            <small class="text-muted">Today's Leads</small>
            <h5 id="sidebar-today-count">-</h5>
        </div>
        <div class="quick-stat">
            <small class="text-muted">Success Rate</small>
            <h5 id="sidebar-success-rate">-</h5>
        </div>
    </div>
</div>

<!-- Mobile Menu Toggle -->
<button class="mobile-menu-toggle" id="mobileMenuToggle">
    <i class="fas fa-bars"></i>
</button>