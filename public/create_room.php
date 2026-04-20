<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../database/db.php';

if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit();
}

$errors = [];

$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$tag = trim($_POST['tag'] ?? '');
$isPrivate = isset($_POST['is_private']);
$chatCode = trim($_POST['chat_code'] ?? '');

if (empty($name)) {
    $errors['name'] = 'Room name is required';
}

if (strlen($name) > 100) {
    $errors['name'] = 'Room name must be 100 characters or less';
}

if (!empty($errors)) {
    $_SESSION['room_errors'] = $errors;
    $_SESSION['old_room'] = $_POST;
    header('Location: index.php');
    exit();
}

try {
    $dbconn = getDBConnection();
    
    if (!$dbconn) {
        $_SESSION['room_errors'] = ['general' => 'Database connection failed.'];
        header('Location: index.php');
        exit();
    }
    
    $stmt = $dbconn->prepare("INSERT INTO rooms (name, description, tag, chat_code, is_private, creator_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$name, $description, $tag, $isPrivate ? $chatCode : null, $isPrivate ? 1 : 0, $_SESSION['user_id']]);
    
    $roomId = $dbconn->lastInsertId();
    
    $stmt = $dbconn->prepare("INSERT INTO room_members (room_id, user_id, role, joined_at) VALUES (?, ?, 'admin', NOW())");
    $stmt->execute([$roomId, $_SESSION['user_id']]);
    
    $_SESSION['room_success'] = 'Room "' . htmlspecialchars($name) . '" created successfully!';
    header('Location: index.php');
    exit();
    
} catch (Exception $e) {
    error_log('Create room error: ' . $e->getMessage());
    $_SESSION['room_errors'] = ['general' => 'Unable to create room. Please try again.'];
    header('Location: index.php');
    exit();
}