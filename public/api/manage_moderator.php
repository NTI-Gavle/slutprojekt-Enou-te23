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
$action = $_POST['action'] ?? null;

if (!$roomId || !$userId || !$action) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

if (!in_array($action, ['promote', 'demote'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}

try {
    $db = getDBConnection();
    
    // Get current user's role
    $stmt = $db->prepare("SELECT role FROM room_members WHERE room_id = ? AND user_id = ?");
    $stmt->execute([$roomId, $_SESSION['user_id']]);
    $currentMember = $stmt->fetch();
    
    if (!$currentMember || $currentMember['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Only admins can manage moderators']);
        exit();
    }
    
    // Get target user's role
    $stmt = $db->prepare("SELECT role FROM room_members WHERE room_id = ? AND user_id = ?");
    $stmt->execute([$roomId, $userId]);
    $targetMember = $stmt->fetch();
    
    if (!$targetMember) {
        echo json_encode(['success' => false, 'message' => 'User not in this room']);
        exit();
    }
    
    $newRole = $action === 'promote' ? 'moderator' : 'member';
    
    $stmt = $db->prepare("UPDATE room_members SET role = ? WHERE room_id = ? AND user_id = ?");
    $stmt->execute([$newRole, $roomId, $userId]);
    
    $message = $action === 'promote' ? 'User promoted to moderator' : 'User demoted to member';
    echo json_encode(['success' => true, 'message' => $message]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to update role']);
}