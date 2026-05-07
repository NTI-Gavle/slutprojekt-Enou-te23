<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../database/db.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$roomId = $_POST['room_id'] ?? null;
$chatCode = trim($_POST['chat_code'] ?? '');

if (!$roomId || empty($chatCode)) {
    echo json_encode(['success' => false, 'message' => 'Missing room ID or chat code']);
    exit();
}

try {
    $db = getDBConnection();

    $stmt = $db->prepare("SELECT id, name, is_private, chat_code FROM rooms WHERE id = ?");
    $stmt->execute([$roomId]);
    $room = $stmt->fetch();

    if (!$room) {
        echo json_encode(['success' => false, 'message' => 'Room not found']);
        exit();
    }

    if (!$room['is_private']) {
        echo json_encode(['success' => false, 'message' => 'Room is not private']);
        exit();
    }

    if ($room['chat_code'] !== $chatCode) {
        echo json_encode(['success' => false, 'message' => 'Invalid join code']);
        exit();
    }

    $stmt = $db->prepare("INSERT IGNORE INTO room_members (room_id, user_id, role, joined_at) VALUES (?, ?, 'member', NOW())");
    $stmt->execute([$roomId, $_SESSION['user_id']]);

    echo json_encode(['success' => true, 'message' => 'Joined room successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to join room']);
}
