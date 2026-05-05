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
    
    $stmt = $db->prepare("SELECT sender_id FROM messages WHERE id = ?");
    $stmt->execute([$messageId]);
    $message = $stmt->fetch();
    
    if (!$message) {
        echo json_encode(['success' => false, 'message' => 'Message not found']);
        exit();
    }
    
    if ($message['sender_id'] != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'You can only delete your own messages']);
        exit();
    }
    
    $stmt = $db->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->execute([$messageId]);
    
    echo json_encode(['success' => true, 'message' => 'Message deleted']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to delete message']);
}
