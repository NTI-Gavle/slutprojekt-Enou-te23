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
    
    // Check current user's role
    $stmt = $db->prepare("SELECT role FROM room_members WHERE room_id = ? AND user_id = ?");
    $stmt->execute([$roomId, $_SESSION['user_id']]);
    $currentMember = $stmt->fetch();
    $currentRole = $currentMember['role'] ?? null;
    $isAdmin = $currentRole === 'admin';
    $isModerator = $currentRole === 'moderator';
    
    // Get all members
    $stmt = $db->prepare("
        SELECT u.id, u.display_name, u.profile_image, rm.role
        FROM room_members rm
        JOIN users u ON u.id = rm.user_id
        WHERE rm.room_id = ?
        ORDER BY FIELD(rm.role, 'admin', 'moderator', 'member'), u.display_name ASC
    ");
    $stmt->execute([$roomId]);
    $members = $stmt->fetchAll();
    
    // Add flags for each member
    foreach ($members as &$member) {
        $member['profile_image'] = getValidProfileImage($member['profile_image'] ?? null);
        
        // Can this member be kicked by current user?
        $member['can_kick'] = false;
        
        if ($isAdmin) {
            // Admin can kick anyone except other admins
            $member['can_kick'] = ($member['role'] !== 'admin');
        } elseif ($isModerator) {
            // Moderator can only kick regular members
            $member['can_kick'] = ($member['role'] === 'member' && $member['id'] !== $_SESSION['user_id']);
        }
        
        // Is this the current user?
        $member['is_me'] = ($member['id'] == $_SESSION['user_id']);
    }
    
    echo json_encode(['success' => true, 'members' => $members, 'is_admin' => $isAdmin, 'is_moderator' => $isModerator, 'user_role' => $currentRole]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
