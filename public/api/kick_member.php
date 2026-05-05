<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../database/db.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$roomId = $_POST['room_id'] ?? null;
$userId = $_POST['user_id'] ?? null;

if (!$roomId || !$userId) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

if ($userId == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'You cannot kick yourself']);
    exit();
}

try {
    $db = getDBConnection();
    
    // Check if current user is admin
    $stmt = $db->prepare("SELECT role FROM room_members WHERE room_id = ? AND user_id = ?");
    $stmt->execute([$roomId, $_SESSION['user_id']]);
    $currentMember = $stmt->fetch();
    
    if (!$currentMember || $currentMember['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Not authorized']);
        exit();
    }
    
    // Remove user from room
    $stmt = $db->prepare("DELETE FROM room_members WHERE room_id = ? AND user_id = ?");
    $stmt->execute([$roomId, $userId]);
    
    echo json_encode(['success' => true, 'message' => 'Member kicked']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to kick member']);
}
