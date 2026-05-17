<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../database/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$roomId = $_POST['room_id'] ?? null;
$isPrivate = $_POST['is_private'] ?? null;

if (!$roomId || $isPrivate === null) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

try {
    $db = getDBConnection();
    
    // Check if user is admin of the room
    $stmt = $db->prepare("SELECT role FROM room_members WHERE room_id = ? AND user_id = ?");
    $stmt->execute([$roomId, $_SESSION['user_id']]);
    $member = $stmt->fetch();
    
    if (!$member || $member['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Only room admins can change room visibility']);
        exit();
    }
    
    $isPrivate = $isPrivate ? 1 : 0;
    $stmt = $db->prepare("UPDATE rooms SET is_private = ? WHERE id = ?");
    $stmt->execute([$isPrivate, $roomId]);
    
    $visibility = $isPrivate ? 'Private' : 'Public';
    echo json_encode(['success' => true, 'message' => "Room is now $visibility"]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to update room visibility']);
}