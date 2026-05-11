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
$attachments = $_POST['attachments'] ?? null;
$messageType = $roomId ? 'room' : 'private';

if (empty($message) && empty($attachments)) {
    echo json_encode(['success' => false, 'message' => 'Message or file required']);
    exit();
}

if (!$roomId && !$receiverId) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

try {
    $db = getDBConnection();
    
    if ($roomId) {
        $stmt = $db->prepare("SELECT 1 FROM room_members WHERE room_id = ? AND user_id = ?");
        $stmt->execute([$roomId, $_SESSION['user_id']]);
        
        if (!$stmt->fetch()) {
            $stmt = $db->prepare("SELECT is_private FROM rooms WHERE id = ?");
            $stmt->execute([$roomId]);
            $room = $stmt->fetch();
            
            if ($room && !$room['is_private']) {
                $stmt = $db->prepare("INSERT IGNORE INTO room_members (room_id, user_id, role) VALUES (?, ?, 'member')");
                $stmt->execute([$roomId, $_SESSION['user_id']]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Not a member of this room']);
                exit();
            }
        }
        
        $attachmentsJson = $attachments ? json_encode(json_decode($attachments)) : null;
        
        $stmt = $db->prepare("INSERT INTO messages (sender_id, room_id, content, attachments, message_type, created_at) VALUES (?, ?, ?, ?, 'room', NOW())");
        $stmt->execute([$_SESSION['user_id'], $roomId, $message, $attachmentsJson]);
        
    } else if ($receiverId) {
        $attachmentsJson = $attachments ? json_encode(json_decode($attachments)) : null;
        $stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, content, attachments, message_type, created_at) VALUES (?, ?, ?, ?, 'private', NOW())");
        $stmt->execute([$_SESSION['user_id'], $receiverId, $message, $attachmentsJson]);
    }
    
    $messageId = $db->lastInsertId();
    echo json_encode(['success' => true, 'message_id' => $messageId]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
}
