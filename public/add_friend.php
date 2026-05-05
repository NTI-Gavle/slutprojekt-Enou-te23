<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../database/db.php';

if (!isLoggedIn()) {
    if (isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit();
    }
    header('Location: auth/login.php');
    exit();
}

$username = trim($_POST['username'] ?? '');
$isAjax = isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');

if (empty($username)) {
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'Please enter a username']);
        exit();
    }
    $_SESSION['friend_error'] = 'Please enter a username';
    header('Location: index.php');
    exit();
}

try {
    $dbconn = getDBConnection();
    
    if (!$dbconn) {
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => 'Database connection failed']);
            exit();
        }
        $_SESSION['friend_error'] = 'Database connection failed.';
        header('Location: index.php');
        exit();
    }
    
    $stmt = $dbconn->prepare("SELECT id, username, display_name, profile_image FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit();
        }
        $_SESSION['friend_error'] = 'User not found';
        header('Location: index.php');
        exit();
    }
    
    if ($user['id'] == $_SESSION['user_id']) {
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => 'You cannot add yourself']);
            exit();
        }
        $_SESSION['friend_error'] = 'You cannot add yourself';
        header('Location: index.php');
        exit();
    }
    
    $stmt = $dbconn->prepare("SELECT id, status FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?) LIMIT 1");
    $stmt->execute([$_SESSION['user_id'], $user['id'], $user['id'], $_SESSION['user_id']]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        $msg = $existing['status'] === 'pending' ? 'Friend request already pending' : 'Already friends';
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => $msg]);
            exit();
        }
        $_SESSION['friend_error'] = $msg;
        header('Location: index.php');
        exit();
    }
    
    $stmt = $dbconn->prepare("INSERT INTO friends (user_id, friend_id, status, created_at) VALUES (?, ?, 'pending', NOW())");
    $stmt->execute([$_SESSION['user_id'], $user['id']]);
    
    if ($isAjax) {
        echo json_encode(['success' => true, 'message' => 'Friend request sent to ' . ($user['display_name'] ?? $user['username'])]);
        exit();
    }
    
    $_SESSION['friend_success'] = 'Friend request sent to ' . htmlspecialchars($user['display_name'] ?? $user['username']);
    header('Location: index.php');
    exit();
    
} catch (Exception $e) {
    error_log('Add friend error: ' . $e->getMessage());
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'Unable to add friend']);
        exit();
    }
    $_SESSION['friend_error'] = 'Unable to add friend. Please try again.';
    header('Location: index.php');
    exit();
}
