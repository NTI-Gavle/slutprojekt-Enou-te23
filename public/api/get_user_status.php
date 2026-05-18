<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../database/db.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false]);
    exit();
}

$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false]);
    exit();
}

try {
    $db = getDBConnection();
    $stmt = $db->prepare("
        SELECT CASE WHEN last_activity >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 1 ELSE 0 END as is_online 
        FROM users WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'is_online' => (bool)($result['is_online'] ?? 0)
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false]);
}
