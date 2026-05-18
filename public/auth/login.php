<?php
require_once __DIR__ . '/../../includes/session.php';

if (isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

$pageTitle = "Login";
$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
$success = $_SESSION['login_success'] ?? null;
unset($_SESSION['login_success']);
$deleted = isset($_GET['deleted']) && $_GET['deleted'] == '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | Quacko</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body style="background: linear-gradient(-60deg, #B148FF 0%, #F4369E 50%, #427EFF 100%); min-height: 100vh;">
    <div class="auth-container">
        <div class="auth-card">
            <div class="d-flex align-items-center justify-content-center mb-4">
                <div class="logo me-3">
                    <span>Q</span>
                </div>
                <span class="brand">Quacko</span>
            </div>
            
            <h2 class="text-center mb-4">Login</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <?php if ($deleted): ?>
                <div class="alert alert-success">Your account has been deleted.</div>
            <?php endif; ?>
            
            <form action="process-login.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username or Email</label>
                    <input type="text" class="form-control" id="username" name="username" required autofocus>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="mb-3 text-end">
                    <a href="forgot-password.php">Forgot password?</a>
                </div>
                
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg">Login</button>
                </div>
            </form>
            
            <p class="text-center">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
            
            <p class="text-center mt-3">
                <a href="../index.php">Back to Home</a>
            </p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
