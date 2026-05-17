<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../database/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$maxFiles = 4;
$maxSize = 5 * 1024 * 1024; // 5MB
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'text/plain', 'application/doc', 'application/docx'];

$uploadDir = dirname(__DIR__) . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (!isset($_FILES['files'])) {
    echo json_encode(['success' => false, 'message' => 'No files uploaded', 'debug' => $_FILES]);
    exit();
}

$files = $_FILES['files'];
$uploadedFiles = [];
$errors = [];

// Handle both single and multiple file uploads
$fileList = [];
if (is_array($files['name'])) {
    // Multiple files
    for ($i = 0; $i < count($files['name']); $i++) {
        $fileList[] = [
            'name' => $files['name'][$i],
            'type' => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i]
        ];
    }
} else {
    // Single file
    $fileList[] = [
        'name' => $files['name'],
        'type' => $files['type'],
        'tmp_name' => $files['tmp_name'],
        'error' => $files['error'],
        'size' => $files['size']
    ];
}

foreach ($fileList as $file) {
    if (empty($file['name'])) continue;
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Error uploading file: " . $file['name'];
        continue;
    }
    
    if ($file['size'] > $maxSize) {
        $errors[] = "File too large: " . $file['name'] . " (max 5MB)";
        continue;
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'txt', 'doc', 'docx'];
    
    if (!in_array($extension, $allowedExtensions)) {
        $errors[] = "Invalid file type: " . $file['name'];
        continue;
    }
    
    $newFilename = uniqid('file_', true) . '.' . $extension;
    $destination = $uploadDir . $newFilename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $uploadedFiles[] = [
            'name' => $file['name'],
            'path' => 'uploads/' . $newFilename,
            'type' => $file['type'] ?: 'application/octet-stream',
            'size' => $file['size']
        ];
    } else {
        $errors[] = "Failed to move file: " . $file['name'];
    }
}

if (empty($uploadedFiles) && !empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit();
}

echo json_encode([
    'success' => true,
    'files' => $uploadedFiles,
    'errors' => $errors
]);