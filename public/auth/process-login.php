<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../database/db.php';

if (isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['login_error'] = 'Please fill in all fields.';
    header('Location: login.php');
    exit();
}

try {
    $dbconn = getDBConnection();
    
    if ($dbconn) {
        $stmt = $dbconn->prepare("SELECT id, username, email, password, display_name, profile_image, is_admin FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            loginUser($user['id'], $user['username'], $user['email'], $user['display_name'], $user['profile_image'], $user['is_admin'] ?? 0);
            header('Location: ../index.php');
            exit();
        }
    }
    
    if ($username === 'demo' && $password === 'demo123') {
        loginUser(1, 'demo', 'demo@example.com', 'Demo User', null, 0);
        header('Location: ../index.php');
        exit();
    }
    
    $_SESSION['login_error'] = 'Invalid username or password.';
    header('Location: login.php');
    exit();
    
} catch (Exception $e) {
    $_SESSION['login_error'] = 'Invalid username or password.';
    header('Location: login.php');
    exit();
}
