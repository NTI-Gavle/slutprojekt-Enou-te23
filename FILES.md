# File Documentation

## Core Configuration
- **config/env.php** - Loads environment variables from .env file
- **config/config.php** - Database configuration constants
- **database/db.php** - PDO connection with UTC timezone
- **database/setup.sql** - Database schema (users, rooms, messages, friends, password_resets tables)

## Authentication (public/auth/)
- **login.php** - Login form with gradient background
- **register.php** - Registration form with gradient background
- **forgot-password.php** - Password reset request form
- **reset-password.php** - Password reset form
- **process-login.php** - Handles login logic
- **process-register.php** - Handles registration logic
- **process-logout.php** - Destroys session
- **process-reset.php** - Updates password after reset
- **process-reset-request.php** - Sends reset email

## Main Pages (public/)
- **index.php** - Dashboard with sidebar (rooms, friends, notifications)
- **profile.php** - User profile with Chart.js statistics
- **admin.php** - Global admin panel (search users/rooms, delete, warnings)
- **create_room.php** - Form to create group chat room
- **add_friend.php** - Friend request management page
- **upload_avatar.php** - Avatar upload handler
- **about.php** - About page
- **legal.php** - Legal information page
- **contact.php** - Contact form

## Include Files (includes/)
- **header.php** - Page header with hamburger menu, nav links, notifications badge
- **nav.php** - Navigation menu
- **sidebar.php** - Left sidebar with friends, pending requests, chat badge
- **groupchat.php** - Group chat UI (messages, file upload, member list, admin controls)
- **userchat.php** - Private chat UI (messages, file upload)
- **session.php** - Session validation, auth helpers, profile image validation
- **footer.php** - Page footer

## API Endpoints (public/api/)
- **send_message.php** - Send message to room or private chat
- **get_messages.php** - Fetch messages (group or private)
- **upload_file.php** - Handle file uploads (max 4 files, 5MB)
- **join_room.php** - Join group chat (uses room code)
- **get_room_members.php** - List members with roles/banned status
- **kick_member.php** - Remove user from room
- **ban_member.php** - Ban/unban user from room
- **manage_moderator.php** - Promote/demote moderator
- **update_room_code.php** - Change room code (owner only)
- **toggle_room_visibility.php** - Toggle public/private (owner only)
- **delete_message.php** - Delete message (author, mod, admin)
- **add_friend.php** - Send friend request
- **respond_friend.php** - Accept/reject request
- **unfriend.php** - Remove friend
- **search.php** - Search users
- **get_unread_count.php** - Get unread notification count
- **mark_read.php** - Mark notifications as read
- **get_user_status.php** - Get online/offline status
- **update_activity.php** - Update last activity timestamp
- **get_message_stats.php** - Get message stats for profile charts
- **get_all_message_stats.php** - Get global stats for admin

## Frontend
- **public/css/styles.css** - All styling
- **public/js/app.js** - JavaScript (chat polling, modals, file upload, search, etc.)
- **public/img/** - Default avatars and images