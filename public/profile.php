<?php
// Load session functions and database connection
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../database/db.php';
use PDO;

// Set page title for the browser tab
$pageTitle = "Profile Settings";

// Redirect to login if user is not logged in
if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit();
}

// Get current user ID from session
$userId = $_SESSION['user_id'];

// Fetch current user data from database
$stmt = getDBConnection()->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle username update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_username'])) {
    $newUsername = trim($_POST['username']);
    if (!empty($newUsername)) {
        try {
            // Update username and display name in database
            $stmt = getDBConnection()->prepare("UPDATE users SET username = ?, display_name = ? WHERE id = ?");
            $stmt->execute([$newUsername, $newUsername, $userId]);
            
            // Update session data
            $_SESSION['username'] = $newUsername;
            $_SESSION['display_name'] = $newUsername;
            setFlashMessage('success', 'Username updated successfully!');
        } catch (Exception $e) {
            setFlashMessage('error', 'Username already exists or invalid.');
        }
    }
    header('Location: profile.php');
    exit();
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $code = trim($_POST['reset_code']);
    $newPass = $_POST['new_password'];
    $confPass = $_POST['confirm_password'];
    
    // Validate passwords match and are at least 6 characters
    if ($newPass === $confPass && strlen($newPass) >= 6) {
        try {
            // Verify reset code is valid and not expired
            $stmt = getDBConnection()->prepare("SELECT * FROM password_resets WHERE user_id = ? AND token = ? AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$userId, $code]);
            $reset = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($reset) {
                // Hash new password and update database
                $hashedPassword = password_hash($newPass, PASSWORD_DEFAULT);
                $stmt = getDBConnection()->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $userId]);
                
                // Delete used reset code
                $stmt = getDBConnection()->prepare("DELETE FROM password_resets WHERE user_id = ?");
                $stmt->execute([$userId]);
                
                setFlashMessage('success', 'Password updated successfully!');
            } else {
                setFlashMessage('error', 'Invalid or expired reset code.');
            }
        } catch (Exception $e) {
            setFlashMessage('error', 'Failed to update password.');
        }
    } else {
        setFlashMessage('error', 'Passwords must match and be at least 6 characters.');
    }
    header('Location: profile.php');
    exit();
}

// Handle reset code request (sends code via email)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_code'])) {
    $email = trim($_POST['email']);
    // Auto-fill email from user data
    if (empty($email)) {
        $email = $user['email'];
    }
    if ($email === $user['email']) {
        // Generate secure random token
        $token = bin2hex(random_bytes(16));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        try {
            // Save reset token to database
            $stmt = getDBConnection()->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $token, $expires]);
            
            // Send email with reset code
            $subject = 'Quacko Password Reset Code';
            $message = "Your password reset code is: {$token}\n\nThis code expires in 1 hour.";
            $headers = 'From: noreply@quacko.com' . "\r\n" .
                        'Content-Type: text/plain; charset=utf-8';
            
            mail($email, $subject, $message, $headers);
            
            setFlashMessage('success', 'Reset code sent to your email! Code: ' . $token);
        } catch (Exception $e) {
            setFlashMessage('error', 'Failed to generate reset code.');
        }
    } else {
        setFlashMessage('error', 'Invalid email address.');
    }
    header('Location: profile.php');
    exit();
}

// Load header (includes HTML head, nav, etc.)
require_once __DIR__ . '/../includes/header.php';
?>

<a href="index.php" class="btn btn-back">
    <i class="bi bi-arrow-left"></i> Back
</a>

<h1 class="profile-title">Profile Settings</h1>
<?= displayFlashMessages() ?>
<div class="profile-row">
    <?php // Left card - Profile picture with upload button ?>
    <div class="profile-card">
        <div class="profile-avatar-large">
            <img src="<?= htmlspecialchars($profileImage) ?>" alt="Profile" id="profileAvatar">
        </div>
        <label class="btn btn-upload">
            <i class="bi bi-cloud-upload"></i> Upload a new Profile picture
            <input type="file" accept="image/*" style="display: none;" onchange="uploadAvatar(this)">
        </label>
    </div>
    
    <?php // Right card - Username update form ?>
    <div class="profile-card">
        <label class="form-label">Username</label>
        <form method="POST">
            <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            <button type="submit" name="update_username" class="btn btn-primary w-100 mt-3">Update Username</button>
        </form>
    </div>
</div>
<div class="profile-card reset-card">
    <h3>Reset Password</h3>
    <form method="POST" class="reset-form">
        <?php // Top row - Email input + Get Code button ?>
        <div class="reset-row">
            <input type="email" class="form-control" name="email" placeholder="Enter email" value="<?= htmlspecialchars($user['email']) ?>" required>
            <button type="submit" name="request_code" class="btn btn-secondary">Get Reset Code</button>
        </div>
        <?php // Bottom row - Three input fields (code, new pass, confirm pass) ?>
        <div class="reset-row three-cols">
            <input type="text" class="form-control" name="reset_code" placeholder="Enter Code (sent to email)" value="<?= htmlspecialchars($_POST['reset_code'] ?? '') ?>" required>
            <input type="password" class="form-control" name="new_password" placeholder="New Password" required>
            <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password" required>
        </div>
        <button type="submit" name="reset_password" class="btn btn-dark w-100">Update Password</button>
    </form>
</div>

<style>
/* Page title styling */
.profile-title {
    font-size: 2rem;
    margin: 20px 0;
}

/* Two-column layout for profile cards */
.profile-row {
    display: flex;
    gap: 20px;
    margin-top: 20px;
    max-width: 100%;
}

/* Individual profile card styling */
.profile-card {
    flex: 1;
    min-width: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 16px;
    padding: 30px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* Large circular avatar container */
.profile-avatar-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: var(--logoColor);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    margin-bottom: 20px;
}

/* Profile image inside avatar container */
.profile-avatar-large img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Upload button styling */
.btn-upload {
    cursor: pointer;
    background: #f8f8f8;
    border: 1px solid #ddd;
    padding: 10px 20px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
}

.btn-upload:hover {
    background: #f0f0f0;
}

/* Password reset card - full width */
.reset-card {
    margin-top: 20px;
    width: 100%;
}

.reset-card h3 {
    margin-bottom: 20px;
}

/* Form layout for reset section */
.reset-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

/* Horizontal row for email + button or 3 inputs */
.reset-row {
    display: flex;
    gap: 10px;
}

/* Three equal columns for reset code + passwords */
.reset-row.three-cols input {
    flex: 1;
}

/* Dark button (Update Password) */
.btn-dark {
    background: #080110;
    color: white;
    border: none;
    padding: 12px;
    border-radius: 8px;
    font-size: 1rem;
    cursor: pointer;
}

.btn-dark:hover {
    background: #1a1a2e;
}

/* Secondary button (Reset Code) */
.btn-secondary {
    background: #6c757d;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
}

/* Utility classes */
.w-100 {
    width: 100%;
}

.mt-3 {
    margin-top: 1rem;
}

/* Responsive - stack cards on mobile */
@media (max-width: 768px) {
    .profile-row {
        flex-direction: column;
    }
    
    .reset-card {
        width: 100%;
    }
    
    .reset-row {
        flex-direction: column;
    }
}
</style>

<script>
// Handle profile picture upload
function uploadAvatar(input) {
    // Check if a file was selected
    if (!input.files || !input.files[0]) return;
    
    // Allow only 1 file at a time
    if (input.files.length > 1) {
        alert('Only 1 image allowed at a time.');
        input.value = '';
        return;
    }
    
    // Show preview immediately
    var reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('profileAvatar').src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
    
    // Upload to server
    var formData = new FormData();
    formData.append('avatar', input.files[0]);
    
    fetch('upload_avatar.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert(data.message || 'Upload failed');
        }
    })
    .catch(err => {
        alert('Upload failed. Please try again.');
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>