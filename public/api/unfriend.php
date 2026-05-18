<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../database/db.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$friendId = $_POST['user_id'] ?? null;

if (!$friendId) {
    echo json_encode(['success' => false, 'message' => 'No user specified']);
    exit();
}

try {
    $db = getDBConnection();

    $stmt = $db->prepare("DELETE FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
    $stmt->execute([$_SESSION['user_id'], $friendId, $friendId, $_SESSION['user_id']]);

    echo json_encode(['success' => true, 'message' => 'Unfriended successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
