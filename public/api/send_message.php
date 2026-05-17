<?php
/**
 * API Endpoint: Send Message
 * Handles sending messages to both group chat rooms and private conversations.
 * Accepts POST requests with room_id or receiver_id (one required).
 * Automatically joins public rooms if user is not yet a member.
 */

// Required files for session check and database connection
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../database/db.php';

// Security check: ensure user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Extract and validate POST parameters
$roomId = $_POST['room_id'] ?? null;          // For group chat messages
$receiverId = $_POST['receiver_id'] ?? null;   // For private messages
$message = trim($_POST['message'] ?? '');     // Message text content
$attachments = $_POST['attachments'] ?? null;  // JSON string of file attachments
$messageType = $roomId ? 'room' : 'private';   // Track message type for logging

// Validation: must provide either room_id or receiver_id
if (!$roomId && !$receiverId) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

// Validation: message must contain text or attachments
if (empty($message) && empty($attachments)) {
    echo json_encode(['success' => false, 'message' => 'Message or file required']);
    exit();
}

try {
    $db = getDBConnection();

    // ============================================================
    // Handle GROUP CHAT messages
    // ============================================================
    if ($roomId) {
        // Check if user is already a member of this room
        $stmt = $db->prepare("SELECT 1 FROM room_members WHERE room_id = ? AND user_id = ?");
        $stmt->execute([$roomId, $_SESSION['user_id']]);

        if (!$stmt->fetch()) {
            // Not a member - check if room is public or private
            $stmt = $db->prepare("SELECT is_private FROM rooms WHERE id = ?");
            $stmt->execute([$roomId]);
            $room = $stmt->fetch();

            if ($room && !$room['is_private']) {
                // Auto-join public room as regular member
                $stmt = $db->prepare("INSERT IGNORE INTO room_members (room_id, user_id, role) VALUES (?, ?, 'member')");
                $stmt->execute([$roomId, $_SESSION['user_id']]);
            } else {
                // Cannot join private room without explicit joining process
                echo json_encode(['success' => false, 'message' => 'Not a member of this room']);
                exit();
            }
        }

        // Prepare attachments as JSON (may be null if no files)
        $attachmentsJson = $attachments ? json_encode(json_decode($attachments)) : null;

        // Insert message into messages table with room_id
        // Both 'content' and 'message' columns store the text (legacy support)
        $stmt = $db->prepare("INSERT INTO messages (sender_id, room_id, content, message, attachments, created_at) VALUES (:sender, :room, :content, :message, :attach, NOW())");
        $stmt->execute([
            ':sender' => $_SESSION['user_id'],
            ':room' => $roomId,
            ':content' => $message,
            ':message' => $message,
            ':attach' => $attachmentsJson
        ]);

    // ============================================================
    // Handle PRIVATE messages
    // ============================================================
    } else if ($receiverId) {
        // Prepare attachments as JSON
        $attachmentsJson = $attachments ? json_encode(json_decode($attachments)) : null;

        // Insert private message with receiver_id
        $stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, content, message, attachments, created_at) VALUES (:sender, :receiver, :content, :message, :attach, NOW())");
        $stmt->execute([
            ':sender' => $_SESSION['user_id'],
            ':receiver' => $receiverId,
            ':content' => $message,
            ':message' => $message,
            ':attach' => $attachmentsJson
        ]);
    }

    // Return success with new message ID to client
    $messageId = $db->lastInsertId();
    echo json_encode(['success' => true, 'message_id' => $messageId]);

} catch (Exception $e) {
    // Log error and return failure response
    echo json_encode(['success' => false, 'message' => 'Failed to send message: ' . $e->getMessage()]);
}
