<!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables -->
    <?php if (isset($use_datatables) && $use_datatables): ?>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <?php endif; ?>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom Dashboard JS -->
    <script src="assets/js/dashboard.js"></script>
    
    <!-- Additional page-specific scripts -->
    <?php if (isset($additional_js)) echo $additional_js; ?>
    
    <script>
    // Initialize dashboard on page load
    $(document).ready(function() {
        // Load sidebar quick stats
        loadSidebarStats();
        
        // Setup mobile menu
        setupMobileMenu();
        
        // Load notifications
        loadNotifications();
    });
    
    function loadSidebarStats() {
        $.ajax({
            url: '../inc/api/get-stats.php',
            type: 'GET',
            data: { period: 'today' },
            success: function(response) {
                if (response.success) {
                    $('#sidebar-today-count').text(response.data.total_today || 0);
                    const successRate = response.data.successful && response.data.total_today 
                        ? Math.round((response.data.successful / response.data.total_today) * 100) 
                        : 0;
                    $('#sidebar-success-rate').text(successRate + '%');
                }
            }
        });
    }
    
    function setupMobileMenu() {
        $('#mobileMenuToggle').on('click', function() {
            $('.sidebar').toggleClass('show');
        });
        
        // Close sidebar when clicking outside on mobile
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.sidebar, #mobileMenuToggle').length) {
                $('.sidebar').removeClass('show');
            }
        });
    }
    
    function loadNotifications() {
        // TODO: Implement notifications loading
        $('#notification-count').text('0');
    }
    
    // Global helper functions
    function showLoading() {
        $('#loadingOverlay').addClass('show');
    }
    
    function hideLoading() {
        $('#loadingOverlay').removeClass('show');
    }
    
    function showToast(title, message, type = 'success') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: type,
            title: title,
            text: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }
    </script>
</body>
</html>