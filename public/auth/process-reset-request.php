<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../database/db.php';

if (isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

$email = trim($_POST['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['reset_error'] = 'Please enter a valid email address.';
    header('Location: forgot-password.php');
    exit();
}

try {
    $dbconn = getDBConnection();
    
    if ($dbconn) {
        $stmt = $dbconn->prepare("SELECT id, email, display_name FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            $token = random_int(100000, 999999);
            $expires = date('Y-m-d H:i:s', time() + 7200);
            
            $stmt = $dbconn->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            
            $stmt = $dbconn->prepare("INSERT INTO password_resets (user_id, token, expires_at, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$user['id'], $token, $expires]);
            
            // Send email with reset code using PHPMailer
            require_once __DIR__ . '/../../config/Mailer/sendmail.php';
            $result = sendMail(
                $email,
                'Quacko Password Reset Code',
                "Your password reset code is: <b>{$token}</b><br><br>This code expires in 2 hours."
            );
            
            $_SESSION['reset_token'] = $token;
            $_SESSION['reset_email'] = $email;
            
            if ($result['success']) {
                $_SESSION['reset_success'] = 'A reset code has been sent to your email.';
            } else {
                $_SESSION['reset_error'] = 'Failed to send email: ' . $result['message'];
            }
            
            header('Location: reset-password.php');
            exit();
        }
    }
    
    $_SESSION['reset_success'] = 'If an account exists, a reset code was sent.';
    header('Location: forgot-password.php');
    exit();
    
} catch (Exception $e) {
    $_SESSION['reset_success'] = 'If an account exists, a reset code was sent.';
    header('Location: forgot-password.php');
    exit();
}
