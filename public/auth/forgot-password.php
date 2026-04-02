<?php
require_once __DIR__ . '/../../includes/session.php';

if (isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

$pageTitle = "Forgot Password";
$error = $_SESSION['reset_error'] ?? null;
unset($_SESSION['reset_error']);
$success = $_SESSION['reset_success'] ?? null;
unset($_SESSION['reset_success']);
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
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="d-flex align-items-center justify-content-center mb-4">
                <div class="logo me-3">
                    <span>Q</span>
                </div>
                <span class="brand">Quacko</span>
            </div>
            
            <h2 class="text-center mb-4">Reset Password</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <?php if (!isset($_SESSION['reset_token'])): ?>
                <p class="text-center mb-4">Enter your email to receive a reset code.</p>
                
                <form action="process-reset-request.php" method="POST">
                    <div class="mb-4">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required autofocus>
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary btn-lg">Send Reset Code</button>
                    </div>
                </form>
            <?php else: ?>
                <p class="text-center mb-4">Enter the reset code from your email.</p>
                
                <form action="process-reset.php" method="POST">
                    <div class="mb-3">
                        <label for="reset_code" class="form-label">Reset Code</label>
                        <input type="text" class="form-control" id="reset_code" name="reset_code" required autofocus>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary btn-lg">Reset Password</button>
                    </div>
                </form>
            <?php endif; ?>
            
            <p class="text-center mt-3">
                Remember your password? <a href="login.php">Login here</a>
            </p>
            
            <p class="text-center mt-3">
                <a href="../index.php">Back to Home</a>
            </p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
