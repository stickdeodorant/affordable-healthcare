<?php
session_start();
require_once __DIR__ . '/../inc/env.php';

// Authentication using environment variables
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminUser = env('ADMIN_USERNAME', 'admin');
    $adminPass = env('ADMIN_PASSWORD', '');
    
    if ($_POST['username'] === $adminUser && $_POST['password'] === $adminPass) {
        $_SESSION['admin_authenticated'] = true;
        $_SESSION['admin_user'] = $adminUser;
        header('Location: dashboard/');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <h3>Admin Login</h3>
                <form method="POST">
                    <input type="text" name="username" class="form-control mb-2" placeholder="Username" required>
                    <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>