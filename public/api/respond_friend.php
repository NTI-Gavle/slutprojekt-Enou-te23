<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../database/db.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$userId = $_POST['user_id'] ?? null;
$action = $_POST['action'] ?? ''; // 'accept' or 'decline'

if (!$userId || !in_array($action, ['accept', 'decline'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

try {
    $db = getDBConnection();
    
    // Find the pending request (where the other user sent the request to current user)
    $stmt = $db->prepare("SELECT * FROM friends WHERE user_id = ? AND friend_id = ? AND status = 'pending'");
    $stmt->execute([$userId, $_SESSION['user_id']]);
    $request = $stmt->fetch();
    
    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'No pending request found']);
        exit();
    }
    
    if ($action === 'accept') {
        // Update status to accepted
        $stmt = $db->prepare("UPDATE friends SET status = 'accepted' WHERE id = ?");
        $stmt->execute([$request['id']]);
        
        // Add reciprocal entry
        $stmt = $db->prepare("INSERT IGNORE INTO friends (user_id, friend_id, status, created_at) VALUES (?, ?, 'accepted', NOW())");
        $stmt->execute([$_SESSION['user_id'], $userId]);
        
        echo json_encode(['success' => true, 'message' => 'Friend request accepted']);
    } else {
        // Decline - delete the request
        $stmt = $db->prepare("DELETE FROM friends WHERE id = ?");
        $stmt->execute([$request['id']]);
        
        echo json_encode(['success' => true, 'message' => 'Friend request declined']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
