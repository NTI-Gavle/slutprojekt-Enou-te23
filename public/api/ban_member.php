<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../database/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$roomId = $_POST['room_id'] ?? null;
$userId = $_POST['user_id'] ?? null;
$action = $_POST['action'] ?? null; // 'ban' or 'unban'

if (!$roomId || !$userId || !$action) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

if (!in_array($action, ['ban', 'unban'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}

try {
    $db = getDBConnection();
    
    // Check if user is admin of the room
    $stmt = $db->prepare("SELECT role FROM room_members WHERE room_id = ? AND user_id = ?");
    $stmt->execute([$roomId, $_SESSION['user_id']]);
    $member = $stmt->fetch();
    
    if (!$member || $member['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Only room admins can ban users']);
        exit();
    }
    
    // Get target user's role
    $stmt = $db->prepare("SELECT role, is_banned FROM room_members WHERE room_id = ? AND user_id = ?");
    $stmt->execute([$roomId, $userId]);
    $targetMember = $stmt->fetch();
    
    if (!$targetMember) {
        echo json_encode(['success' => false, 'message' => 'User not in this room']);
        exit();
    }
    
    if ($targetMember['role'] === 'admin') {
        echo json_encode(['success' => false, 'message' => 'Cannot ban another admin']);
        exit();
    }
    
    if ($action === 'ban') {
        $stmt = $db->prepare("UPDATE room_members SET is_banned = 1 WHERE room_id = ? AND user_id = ?");
        $stmt->execute([$roomId, $userId]);
        echo json_encode(['success' => true, 'message' => 'User banned from room']);
    } else {
        $stmt = $db->prepare("UPDATE room_members SET is_banned = 0 WHERE room_id = ? AND user_id = ?");
        $stmt->execute([$roomId, $userId]);
        echo json_encode(['success' => true, 'message' => 'User unbanned']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to update ban status']);
}