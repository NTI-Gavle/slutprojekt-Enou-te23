<?php
require_once __DIR__ . '/../../includes/session.php';

$pageTitle = "Forgot Password";

if (isLoggedIn()) {
    header('Location: /../index.php');
    exit();
}

$error = '';
$success = '';
$step = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['request_reset'])) {
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            $success = 'If an account exists, a reset link was sent.';
            $step = 2;
        }
    }
    
    if (isset($_POST['reset_password'])) {
        $step = 3;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | Quacko</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/../css/styles.css">
</head>
<body style="background: var(--auth-background);">
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
            
            <?php if ($step === 1): ?>
                <p class="text-center mb-4">Enter your email to reset your password.</p>
                
                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required autofocus>
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" name="request_reset" class="btn btn-primary btn-lg">Send Reset Link</button>
                    </div>
                </form>
            <?php elseif ($step === 2): ?>
                <div class="text-center">
                    <i class="bi bi-envelope-check" style="font-size: 4rem;"></i>
                    <p class="mt-3">Check your email for a reset link.</p>
                    <a href="/../forgot-password.php">Try again</a>
                </div>
            <?php endif; ?>
            
            <p class="text-center mt-3">
                Remember your password? <a href="/../login.php">Login here</a>
            </p>
            
            <p class="text-center mt-3">
                <a href="/../index.php">Back to Home</a>
            </p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
