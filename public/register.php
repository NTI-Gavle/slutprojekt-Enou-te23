<?php
require_once __DIR__ . '/../includes/session.php';

$pageTitle = "Register";

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../database/db.php';
    
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($username) || strlen($username) < 3) {
        $errors['username'] = 'Username must be at least 3 characters.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Valid email required.';
    }
    if (empty($password) || strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }
    
    if (empty($errors)) {
        try {
            $dbconn = getDBConnection();
            
            if ($dbconn) {
                $stmt = $dbconn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                
                if (!$stmt->fetch()) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $dbconn->prepare("INSERT INTO users (username, email, password, display_name, created_at) VALUES (?, ?, ?, ?, NOW())");
                    $stmt->execute([$username, $email, $hashedPassword, $username]);
                    loginUser($dbconn->lastInsertId(), $username, $email, $username);
                    header('Location: index.php');
                    exit();
                }
                $errors['general'] = 'Username or email already exists.';
            } else {
                loginUser(2, $username, $email, $username);
                header('Location: index.php');
                exit();
            }
        } catch (Exception $e) {
            $errors['general'] = 'Unable to create account.';
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
    <link rel="stylesheet" href="css/styles.css">
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
            
            <h2 class="text-center mb-4">Create Account</h2>
            
            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                           id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                    <?php if (isset($errors['username'])): ?>
                        <div class="invalid-feedback"><?= $errors['username'] ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                           id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback"><?= $errors['email'] ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                           id="password" name="password" required>
                    <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback"><?= $errors['password'] ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-4">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                           id="confirm_password" name="confirm_password" required>
                    <?php if (isset($errors['confirm_password'])): ?>
                        <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg">Create Account</button>
                </div>
            </form>
            
            <p class="text-center">
                Already have an account? <a href="login.php">Login here</a>
            </p>
            
            <p class="text-center mt-3">
                <a href="index.php">Back to Home</a>
            </p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
