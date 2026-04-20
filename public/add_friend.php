<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../database/db.php';
use PDO;

if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit();
}

$username = trim($_POST['username'] ?? '');

if (empty($username)) {
    $_SESSION['friend_error'] = 'Please enter a username';
    header('Location: index.php');
    exit();
}

try {
    $dbconn = getDBConnection();
    
    if (!$dbconn) {
        $_SESSION['friend_error'] = 'Database connection failed.';
        header('Location: index.php');
        exit();
    }
    
    $stmt = $dbconn->prepare("SELECT id, username, display_name, profile_image FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $_SESSION['friend_error'] = 'User not found';
        header('Location: index.php');
        exit();
    }
    
    if ($user['id'] == $_SESSION['user_id']) {
        $_SESSION['friend_error'] = 'You cannot add yourself';
        header('Location: index.php');
        exit();
    }
    
    $stmt = $dbconn->prepare("SELECT id FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?) LIMIT 1");
    $stmt->execute([$_SESSION['user_id'], $user['id'], $user['id'], $_SESSION['user_id']]);
    
    if ($stmt->fetch()) {
        $_SESSION['friend_error'] = 'Already friends or request pending';
        header('Location: index.php');
        exit();
    }
    
    $stmt = $dbconn->prepare("INSERT INTO friends (user_id, friend_id, status, created_at) VALUES (?, ?, 'pending', NOW())");
    $stmt->execute([$_SESSION['user_id'], $user['id']]);
    
    $_SESSION['friend_success'] = 'Friend request sent to ' . htmlspecialchars($user['display_name'] ?? $user['username']);
    header('Location: index.php');
    exit();
    
} catch (Exception $e) {
    error_log('Add friend error: ' . $e->getMessage());
    $_SESSION['friend_error'] = 'Unable to add friend. Please try again.';
    header('Location: index.php');
    exit();
}