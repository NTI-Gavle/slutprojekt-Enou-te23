<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../database/db.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$messageId = $_POST['message_id'] ?? null;

if (!$messageId) {
    echo json_encode(['success' => false, 'message' => 'Missing message ID']);
    exit();
}

try {
    $db = getDBConnection();
    
    // Get message with room info
    $stmt = $db->prepare("SELECT m.sender_id, m.room_id FROM messages m WHERE m.id = ?");
    $stmt->execute([$messageId]);
    $message = $stmt->fetch();
    
    if (!$message) {
        echo json_encode(['success' => false, 'message' => 'Message not found']);
        exit();
    }
    
    // Check if user can delete (own message OR moderator/admin of the room)
    $canDelete = false;
    
    if ($message['sender_id'] == $_SESSION['user_id']) {
        $canDelete = true;
    } elseif ($message['room_id']) {
        // Check if user is moderator/admin in this room
        $stmt = $db->prepare("SELECT role FROM room_members WHERE room_id = ? AND user_id = ?");
        $stmt->execute([$message['room_id'], $_SESSION['user_id']]);
        $member = $stmt->fetch();
        
        if ($member && in_array($member['role'], ['admin', 'moderator'])) {
            $canDelete = true;
        }
    }
    
    if (!$canDelete) {
        echo json_encode(['success' => false, 'message' => 'You cannot delete this message']);
        exit();
    }
    
    $stmt = $db->prepare("UPDATE messages SET is_deleted = 1 WHERE id = ?");
    $stmt->execute([$messageId]);
    
    echo json_encode(['success' => true, 'message' => 'Message deleted']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to delete message']);
}
