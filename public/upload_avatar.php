<?php
// Load session and database functions
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../database/db.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Check if file was uploaded
if (!isset($_FILES['avatar'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit();
}

$file = $_FILES['avatar'];
$userId = $_SESSION['user_id'];

// Validate file type (only allow image formats)
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, SVG allowed.']);
    exit();
}

// Validate file size (max 2MB)
if ($file['size'] > 2 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File too large. Max 2MB.']);
    exit();
}

// Generate unique filename to avoid conflicts
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
$uploadPath = __DIR__ . '/img/' . $filename;

// Move uploaded file to img directory
if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    $profileImage = 'img/' . $filename;
    
    try {
        // Update database with new profile picture path
        $dbconn = getDBConnection();
        $stmt = $dbconn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
        $stmt->execute([$profileImage, $userId]);
        
        // Update session so header shows new picture immediately
        $_SESSION['profile_image'] = $profileImage;
        
        echo json_encode(['success' => true, 'path' => $profileImage]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save file']);
}
