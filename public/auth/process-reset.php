<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../database/db.php';

if (isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

$code = $_POST['reset_code'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($newPassword !== $confirmPassword) {
    $_SESSION['reset_error'] = 'Passwords do not match.';
    header('Location: reset-password.php');
    exit();
}

if (strlen($newPassword) < 8) {
    $_SESSION['reset_error'] = 'Password must be at least 8 characters.';
    header('Location: reset-password.php');
    exit();
}

if (empty($code)) {
    $_SESSION['reset_error'] = 'Please enter the reset code.';
    header('Location: reset-password.php');
    exit();
}

try {
    $dbconn = getDBConnection();
    
    if ($dbconn) {
        $stmt = $dbconn->prepare("SELECT user_id, expires_at FROM password_resets WHERE token = ? AND used = 0 LIMIT 1");
        $stmt->execute([$code]);
        $reset = $stmt->fetch();
        
        if (!$reset) {
            $_SESSION['reset_error'] = 'Invalid or expired reset code.';
            header('Location: reset-password.php');
            exit();
        }
        
        if (strtotime($reset['expires_at']) < time()) {
            $_SESSION['reset_error'] = 'Reset code has expired.';
            header('Location: reset-password.php');
            exit();
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $dbconn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $reset['user_id']]);
        
        $stmt = $dbconn->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
        $stmt->execute([$code]);
        
        unset($_SESSION['reset_token']);
        unset($_SESSION['reset_email']);
        
        $_SESSION['login_success'] = 'Password reset successfully! You can now login.';
        header('Location: login.php');
        exit();
    }
    
    $_SESSION['login_success'] = 'Password reset successfully!';
    header('Location: login.php');
    exit();
    
} catch (Exception $e) {
    $_SESSION['reset_error'] = 'Unable to reset password. Please try again.';
    header('Location: reset-password.php');
    exit();
}
