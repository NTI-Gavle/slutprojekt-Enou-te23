<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../database/db.php';

if (isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

$errors = [];

if (empty($username) || strlen($username) < 3) {
    $errors['username'] = 'Username must be at least 3 characters.';
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors['username'] = 'Only letters, numbers, and underscores allowed.';
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

if (!empty($errors)) {
    $_SESSION['register_errors'] = $errors;
    $_SESSION['old_register'] = ['username' => $username, 'email' => $email];
    header('Location: register.php');
    exit();
}

try {
    $dbconn = getDBConnection();
    
    if (!$dbconn) {
        $_SESSION['register_errors'] = ['general' => 'Database connection failed. Please try again later.'];
        $_SESSION['old_register'] = ['username' => $username, 'email' => $email];
        header('Location: register.php');
        exit();
    }
    
    $stmt = $dbconn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->execute([$username, $email]);
    
    if ($stmt->fetch()) {
        $errors['general'] = 'Username or email already exists.';
        $_SESSION['register_errors'] = $errors;
        $_SESSION['old_register'] = ['username' => $username, 'email' => $email];
        header('Location: register.php');
        exit();
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $dbconn->prepare("INSERT INTO users (username, email, password, display_name, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$username, $email, $hashedPassword, $username]);
    
    loginUser($dbconn->lastInsertId(), $username, $email, $username);
    header('Location: ../index.php');
    exit();
    
} catch (Exception $e) {
    error_log('Registration error: ' . $e->getMessage());
    $_SESSION['register_errors'] = ['general' => 'Error: ' . $e->getMessage()];
    $_SESSION['old_register'] = ['username' => $username, 'email' => $email];
    header('Location: register.php');
    exit();
}
