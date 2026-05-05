<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../database/db.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$roomId = $_POST['room_id'] ?? null;
$receiverId = $_POST['receiver_id'] ?? null;
$message = trim($_POST['message'] ?? '');

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit();
}

if (!$roomId && !$receiverId) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

try {
    $db = getDBConnection();
    
    if ($roomId) {
        // Check if user is member of room
        $stmt = $db->prepare("SELECT 1 FROM room_members WHERE room_id = ? AND user_id = ?");
        $stmt->execute([$roomId, $_SESSION['user_id']]);
        
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Not a member of this room']);
            exit();
        }
        
        $stmt = $db->prepare("INSERT INTO messages (sender_id, room_id, message, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$_SESSION['user_id'], $roomId, $message]);
        
    } else if ($receiverId) {
        $stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$_SESSION['user_id'], $receiverId, $message]);
    }
    
    $messageId = $db->lastInsertId();
    echo json_encode(['success' => true, 'message_id' => $messageId]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
}
