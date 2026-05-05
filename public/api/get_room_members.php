<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../database/db.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$roomId = $_GET['room_id'] ?? null;

if (!$roomId) {
    echo json_encode(['success' => false, 'message' => 'Missing room_id']);
    exit();
}

try {
    $db = getDBConnection();
    
    // Check if current user is admin
    $stmt = $db->prepare("SELECT role FROM room_members WHERE room_id = ? AND user_id = ?");
    $stmt->execute([$roomId, $_SESSION['user_id']]);
    $currentMember = $stmt->fetch();
    $isAdmin = $currentMember && $currentMember['role'] === 'admin';
    
    // Get all members
    $stmt = $db->prepare("
        SELECT u.id, u.display_name, u.profile_image, rm.role
        FROM room_members rm
        JOIN users u ON u.id = rm.user_id
        WHERE rm.room_id = ?
        ORDER BY rm.role DESC, u.display_name ASC
    ");
    $stmt->execute([$roomId]);
    $members = $stmt->fetchAll();
    
    // Add is_admin flag and validate profile images
    foreach ($members as &$member) {
        $member['profile_image'] = getValidProfileImage($member['profile_image'] ?? null);
        $member['is_admin'] = ($member['id'] == $_SESSION['user_id'] && $isAdmin);
    }
    
    echo json_encode(['success' => true, 'members' => $members, 'is_admin' => $isAdmin]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
