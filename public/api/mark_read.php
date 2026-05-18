<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../database/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false]);
    exit();
}

$otherUserId = $_GET['user_id'] ?? null;

if (!$otherUserId) {
    echo json_encode(['success' => false]);
    exit();
}

try {
    $db = getDBConnection();
    $userId = $_SESSION['user_id'];
    
    $stmt = $db->prepare("
        UPDATE messages 
        SET read_status = 1 
        WHERE receiver_id = ? AND sender_id = ? AND is_deleted = 0 AND (read_status IS NULL OR read_status = 0)
    ");
    $stmt->execute([$userId, $otherUserId]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}