<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../database/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$roomId = $_POST['room_id'] ?? null;
$newCode = $_POST['chat_code'] ?? null;

if (!$roomId || !$newCode) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

if (strlen($newCode) < 4) {
    echo json_encode(['success' => false, 'message' => 'Chat code must be at least 4 characters']);
    exit();
}

try {
    $db = getDBConnection();
    
    // Check if user is admin of the room
    $stmt = $db->prepare("SELECT role FROM room_members WHERE room_id = ? AND user_id = ?");
    $stmt->execute([$roomId, $_SESSION['user_id']]);
    $member = $stmt->fetch();
    
    if (!$member || $member['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Only room admins can change the chat code']);
        exit();
    }
    
    $stmt = $db->prepare("UPDATE rooms SET chat_code = ? WHERE id = ?");
    $stmt->execute([$newCode, $roomId]);
    
    echo json_encode(['success' => true, 'message' => 'Chat code updated']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to update chat code']);
}