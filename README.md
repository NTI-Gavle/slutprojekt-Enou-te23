## Features

### User System
- Registration with email/password
- Login with session-based authentication
- Profile pages with avatar upload
- Online/offline status tracking

### Chat Features
- **Group Chats**: Create public or private rooms with custom room codes
- **Private Chats**: One-on-one messaging with friends
- **File Upload**: Attach images/files (max 4 files, 5MB each) with preview
- **Real-time**: Polling-based message refresh

### Social Features
- **Friend System**: Send requests, accept/reject, unfriend
- **Notifications**: Red badge + unread message section

### Admin Features
- **Global Admins**: Access admin.php to manage all rooms/users, issue warnings
- **Room Owners**: Kick users, promote moderators, change room code/visibility, ban users
- **Moderators**: Delete any message in room, kick regular members

### Additional
- Hamburger menu (Home, About Us, Legal)
- Warning system (modal popup on every page until acknowledged)
- Chat statistics charts (Chart.js) on profile pages

---

## Database

Tables: `users`, `rooms`, `room_members`, `messages`, `friends`, `notifications`

Key columns:
- `users`: id, username, email, password_hash, avatar, is_admin, warning
- `rooms`: id, name, code, is_private, creator_id
- `room_members`: user_id, room_id, role (owner/moderator/member), is_banned

---

## Folder Structure

```
project/
├── config/
│   ├── config.php       # Database config
│   ├── env.php          # .env loader
│   └── Mailer/          # Email functionality
├── database/
│   ├── db.php           # PDO connection (UTC timezone)
│   └── setup.sql        # Database schema
├── includes/
│   ├── header.php       # Page header with nav
│   ├── nav.php          # Navigation menu
│   ├── sidebar.php      # Room/friend list
│   ├── groupchat.php    # Group chat UI
│   ├── userchat.php     # Private chat UI
│   ├── footer.php       # Page footer
│   └── session.php      # Session check & auth helpers
├── public/
│   ├── index.php        # Home/dashboard
│   ├── profile.php      # User profile with charts
│   ├── admin.php        # Global admin panel
│   ├── create_room.php  # Create new room
│   ├── add_friend.php   # Friend management
│   ├── about.php        # About page
│   ├── legal.php        # Legal info
│   ├── contact.php      # Contact form
│   ├── upload_avatar.php # Profile picture upload
│   ├── auth/            # Login/register/logout/password reset
│   ├── api/             # AJAX endpoints (messages, users, rooms)
│   ├── css/styles.css   # All styles
│   ├── js/app.js        # JavaScript
│   └── img/             # Default avatars and images
└── uploads/             # Uploaded files (avatars, attachments)
```

---

## API Endpoints

| Endpoint | Description |
|----------|-------------|
| `send_message.php` | Send message to room or private chat |
| `upload_file.php` | Handle file uploads with preview |
| `join_room.php` | Join public or private group chat |
| `get_messages.php` | Fetch messages for room/private chat |
| `delete_message.php` | Delete a message (sender only) |
| `get_room_members.php` | List room members with roles/actions |
| `kick_member.php` | Remove user from room |
| `ban_member.php` | Ban/unban user from room |
| `manage_moderator.php` | Promote/demote to moderator |
| `update_room_code.php` | Change room chat code |
| `toggle_room_visibility.php` | Toggle public/private room |
| `add_friend.php` | Send friend request (via main page) |
| `respond_friend.php` | Accept/reject friend request |
| `unfriend.php` | Remove friend connection |
| `mark_read.php` | Mark messages as read |
| `get_unread_count.php` | Get count of unread messages |
| `get_user_status.php` | Check if user is online |
| `update_activity.php` | Update user's last activity timestamp |
| `search.php` | Search users and rooms |
| `get_message_stats.php` | Get chat history stats (per friend) |
| `get_all_message_stats.php` | Get total message stats (own profile) |

---

## Requirements

- PHP 7.4+
- MySQL/MariaDB
- Web server (Apache/Nginx/PHP built-in)

---

## Getting Started

1. Import `database/setup.sql` to create tables
2. Copy `.env.example` to `.env` and configure DB credentials
3. Ensure `uploads/` folder is writable
4. Start PHP server: `php -S localhost:8000`