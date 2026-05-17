-- ============================================================
-- Quacko Chat Application - Database Schema
-- ============================================================
-- Import this file to create the complete database schema
-- Use phpMyAdmin, HeidiSQL, or MySQL command line
-- Database: quacko (will be created if not exists)
-- ============================================================

-- Create database
CREATE DATABASE IF NOT EXISTS quacko CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quacko;

-- ============================================================
-- USERS TABLE
-- Stores all user accounts
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    display_name VARCHAR(100),
    profile_image VARCHAR(255) DEFAULT 'img/default-avatar.svg',
    last_activity DATETIME DEFAULT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    warning TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_last_activity (last_activity)
);

-- ============================================================
-- FRIENDS TABLE
-- Manages friend relationships between users
-- Relationship: users can send friend requests (pending)
--               which become friendships when accepted (accepted)
-- ============================================================
CREATE TABLE IF NOT EXISTS friends (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    friend_id INT NOT NULL,
    status ENUM('pending', 'accepted') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (friend_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_friendship (user_id, friend_id),
    INDEX idx_user_status (user_id, status),
    INDEX idx_friend_status (friend_id, status)
);

-- ============================================================
-- ROOMS TABLE
-- Chat rooms (public or private)
-- Private rooms require a chat code to join
-- ============================================================
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    tag VARCHAR(50),
    chat_code VARCHAR(8) UNIQUE,
    is_private BOOLEAN DEFAULT FALSE,
    creator_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_name (name),
    INDEX idx_tag (tag),
    INDEX idx_chat_code (chat_code),
    INDEX idx_creator (creator_id)
);

-- ============================================================
-- ROOM MEMBERS TABLE
-- Links users to rooms with roles and permissions
-- Roles: admin (owner), moderator, member
-- is_banned: 1 = banned, 0 = not banned
-- ============================================================
CREATE TABLE IF NOT EXISTS room_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('admin', 'moderator', 'member') DEFAULT 'member',
    is_banned TINYINT(1) DEFAULT 0,
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_member (room_id, user_id),
    INDEX idx_room (room_id),
    INDEX idx_user (user_id),
    INDEX idx_role (role),
    INDEX idx_banned (is_banned)
);

-- ============================================================
-- MESSAGES TABLE
-- Stores all messages (group and private)
-- Either room_id (group chat) or receiver_id (private chat)
-- Both 'message' and 'content' columns exist for compatibility
-- ============================================================
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NULL,
    room_id INT NULL,
    content TEXT,
    message TEXT NOT NULL,
    attachments JSON,
    is_deleted TINYINT(1) DEFAULT 0,
    read_status TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    INDEX idx_sender (sender_id),
    INDEX idx_receiver (receiver_id),
    INDEX idx_room_message (room_id, created_at),
    INDEX idx_receiver_read (receiver_id, read_status),
    INDEX idx_created (created_at)
);

-- ============================================================
-- PASSWORD RESETS TABLE
-- Stores password reset tokens
-- ============================================================
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(100) NOT NULL,
    expires_at DATETIME NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user (user_id)
);

-- ============================================================
-- TABLE RELATIONSHIPS DIAGRAM (ASCII)
-- ============================================================
--
--    users ───────────────┐
--    (id)                 │
--                         │
--    friends             │
--    (user_id)────────────┼──► users (id)
--    (friend_id)──────────┤
--                         │
--    rooms               │
--    (creator_id)────────┼──► users (id)
--                         │
--    room_members        │
--    (room_id)───────────┼──► rooms (id)
--    (user_id)───────────┼──► users (id)
--                         │
--    messages            │
--    (sender_id)─────────┼──► users (id)
--    (receiver_id)───────┼──► users (id)
--    (room_id)───────────┼──► rooms (id)
--                         │
--    password_resets     │
--    (user_id)───────────┼──► users (id)
--

-- Verify all tables were created
SHOW TABLES;

-- Optional: Insert sample test user (password: password123)
-- INSERT INTO users (username, email, password, display_name) 
-- VALUES ('testuser', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test User');