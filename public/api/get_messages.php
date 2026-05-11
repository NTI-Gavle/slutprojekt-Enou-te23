<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../database/db.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$roomId = $_GET['room_id'] ?? null;
$userId = $_GET['user_id'] ?? null;
$after = $_GET['after'] ?? 0;

if (!$roomId && !$userId) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

try {
    $db = getDBConnection();
    
    if ($roomId) {
        // Check if user is member of room or auto-join public rooms
        $stmt = $db->prepare("SELECT 1 FROM room_members WHERE room_id = ? AND user_id = ?");
        $stmt->execute([$roomId, $_SESSION['user_id']]);
        
        if (!$stmt->fetch() && !$after) {
            // Auto-join public rooms
            $stmt = $db->prepare("SELECT is_private FROM rooms WHERE id = ?");
            $stmt->execute([$roomId]);
            $room = $stmt->fetch();
            
            if ($room && !$room['is_private']) {
                $stmt = $db->prepare("INSERT IGNORE INTO room_members (room_id, user_id, role) VALUES (?, ?, 'member')");
                $stmt->execute([$roomId, $_SESSION['user_id']]);
            } else if ($room && $room['is_private']) {
                echo json_encode(['success' => false, 'message' => 'Access denied - private room']);
                exit();
            }
        }
        
        // Get messages
        if ($after > 0) {
            $stmt = $db->prepare("
                SELECT m.id, m.room_id, m.sender_id, m.receiver_id, m.content as message, m.attachments, m.created_at, u.display_name as sender_name, u.profile_image as sender_avatar
                FROM messages m
                JOIN users u ON u.id = m.sender_id
                WHERE m.room_id = ? AND m.id > ? AND m.is_deleted = 0
                ORDER BY m.created_at ASC
                LIMIT 50
            ");
            $stmt->execute([$roomId, $after]);
        } else {
            $stmt = $db->prepare("
                SELECT m.id, m.room_id, m.sender_id, m.receiver_id, m.content as message, m.attachments, m.created_at, u.display_name as sender_name, u.profile_image as sender_avatar
                FROM messages m
                JOIN users u ON u.id = m.sender_id
                WHERE m.room_id = ? AND m.is_deleted = 0
                ORDER BY m.created_at DESC
                LIMIT 50
            ");
            $stmt->execute([$roomId]);
            $messages = array_reverse($stmt->fetchAll());
        }
        
    } else if ($userId) {
        // Private messages between current user and another user
        if ($after > 0) {
            $stmt = $db->prepare("
                SELECT m.id, m.room_id, m.sender_id, m.receiver_id, m.content as message, m.attachments, m.created_at, u.display_name as sender_name, u.profile_image as sender_avatar
                FROM messages m
                JOIN users u ON u.id = m.sender_id
                WHERE ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
                AND m.id > ? AND m.is_deleted = 0
                ORDER BY m.created_at ASC
                LIMIT 50
            ");
            $stmt->execute([$_SESSION['user_id'], $userId, $userId, $_SESSION['user_id'], $after]);
        } else {
            $stmt = $db->prepare("
                SELECT m.id, m.room_id, m.sender_id, m.receiver_id, m.content as message, m.attachments, m.created_at, u.display_name as sender_name, u.profile_image as sender_avatar
                FROM messages m
                JOIN users u ON u.id = m.sender_id
                WHERE ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
                AND m.is_deleted = 0
                ORDER BY m.created_at DESC
                LIMIT 50
            ");
            $stmt->execute([$_SESSION['user_id'], $userId, $userId, $_SESSION['user_id']]);
            $messages = array_reverse($stmt->fetchAll());
        }
    }
    
    $messages = $messages ?? $stmt->fetchAll();
    
    // Add is_own flag and validate profile images
    foreach ($messages as &$msg) {
        $msg['is_own'] = ($msg['sender_id'] == $_SESSION['user_id']);
        $msg['sender_avatar'] = getValidProfileImage($msg['sender_avatar'] ?? null);
    }
    
    echo json_encode(['success' => true, 'messages' => $messages]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
