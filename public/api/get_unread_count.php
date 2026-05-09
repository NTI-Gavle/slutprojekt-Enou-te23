<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../database/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'unread_count' => 0, 'messages' => []]);
    exit();
}

try {
    $db = getDBConnection();
    $userId = $_SESSION['user_id'];
    
    // Get unread count
    $stmt = $db->prepare("
        SELECT COUNT(*) as cnt FROM messages 
        WHERE receiver_id = ? AND is_deleted = 0
        AND (read_status IS NULL OR read_status = 0)
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get unread messages with sender info (latest 5)
    $stmt = $db->prepare("
        SELECT m.id, m.content, m.created_at, 
               u.id as sender_id, u.username, u.display_name, u.profile_image
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.receiver_id = ? AND m.is_deleted = 0
        AND (m.read_status IS NULL OR m.read_status = 0)
        ORDER BY m.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$userId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group by sender
    $bySender = [];
    foreach ($messages as $msg) {
        $sid = $msg['sender_id'];
        if (!isset($bySender[$sid])) {
            $bySender[$sid] = [
                'sender_id' => $msg['sender_id'],
                'username' => $msg['username'],
                'display_name' => $msg['display_name'],
                'profile_image' => $msg['profile_image'],
                'preview' => $msg['content'],
                'count' => 0
            ];
        }
        $bySender[$sid]['count']++;
    }
    
    $senders = array_values($bySender);
    
    echo json_encode([
        'success' => true,
        'unread_count' => (int)$result['cnt'],
        'senders' => $senders
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'unread_count' => 0, 'senders' => [], 'error' => $e->getMessage()]);
}