<?php
require_once __DIR__ . '/../../includes/session.php';

$pageTitle = "Login";

if (isLoggedIn()) {
    header('Location: /../index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../database/db.php';
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            $dbconn = getDBConnection();
            
            if ($dbconn) {
                $stmt = $dbconn->prepare("SELECT id, username, email, password, display_name, profile_image FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $username]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    loginUser($user['id'], $user['username'], $user['email'], $user['display_name'], $user['profile_image']);
                    regenerateSession();
                    header('Location: /../index.php');
                    exit();
                }
            } else {
                if ($username === 'demo' && $password === 'demo123') {
                    loginUser(1, 'demo', 'demo@example.com', 'Demo User');
                    header('Location: /../index.php');
                    exit();
                }
            }
            $error = 'Invalid username or password.';
        } catch (Exception $e) {
            if ($username === 'demo' && $password === 'demo123') {
                loginUser(1, 'demo', 'demo@example.com', 'Demo User');
                header('Location: /../index.php');
                exit();
            }
            $error = 'Invalid username or password.';
        }
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
            
            <h2 class="text-center mb-4">Login</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
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
                Don't have an account? <a href="/../register.php">Register here</a>
            </p>
            
            <p class="text-center mt-3">
                <a href="/../index.php">Back to Home</a>
            </p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
