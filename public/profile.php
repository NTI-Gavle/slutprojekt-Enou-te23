<?php
// Load session functions and database connection
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../database/db.php';

// Set page title for the browser tab
$pageTitle = "Profile Settings";

// Redirect to login if user is not logged in
if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit();
}

// Get current user ID from session
$userId = $_SESSION['user_id'];
$viewUserId = isset($_GET['user']) ? (int)$_GET['user'] : $userId;
$isOwnProfile = $viewUserId === $userId;

// Fetch current user data from database
$stmt = getDBConnection()->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get valid profile image path
$profileImage = getValidProfileImage($user['profile_image'] ?? $_SESSION['profile_image'] ?? null);

// Public profile view for other users
$viewUser = null;
$friendStatus = null;

if (!$isOwnProfile) {
    $stmt = getDBConnection()->prepare("SELECT id, username, display_name, profile_image FROM users WHERE id = ?");
    $stmt->execute([$viewUserId]);
    $viewUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$viewUser) {
        header('Location: index.php');
        exit();
    }

    // Determine friend status between current user and viewed user
    $stmt = getDBConnection()->prepare("SELECT id, status, user_id FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?) LIMIT 1");
    $stmt->execute([$userId, $viewUserId, $viewUserId, $userId]);
    $friendship = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($friendship) {
        $friendStatus = $friendship['status'];
        if ($friendStatus === 'pending') {
            $friendStatus = $friendship['user_id'] === $userId ? 'pending_sent' : 'pending_received';
        }
    }

    $viewUser['profile_image'] = getValidProfileImage($viewUser['profile_image'] ?? null);
    $pageTitle = ($viewUser['display_name'] ?? $viewUser['username']) . ' | Quacko';
}

// Handle username update form submission (own profile only)
if ($isOwnProfile && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_username'])) {
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

// Handle password reset form submission (own profile only)
if ($isOwnProfile && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
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

// Handle reset code request (own profile only)
if ($isOwnProfile && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_code'])) {
    $email = trim($_POST['email']);
    // Auto-fill email from user data
    if (empty($email)) {
        $email = $user['email'];
    }
    if ($email === $user['email']) {
        // Generate 6-digit numeric code
        $token = random_int(100000, 999999);
        $expires = date('Y-m-d H:i:s', strtotime('+2 hours'));
        
        try {
            // Save reset token to database
            $stmt = getDBConnection()->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $token, $expires]);
            
            // Send email with reset code using PHPMailer
            require_once __DIR__ . '/../config/Mailer/sendmail.php';
            $result = sendMail(
                $email,
                'Quacko Password Reset Code',
                "Your password reset code is: <b>{$token}</b><br><br>This code expires in 2 hours."
            );
            
            if ($result['success']) {
                setFlashMessage('success', 'Reset code sent to your email!');
            } else {
                setFlashMessage('error', 'Failed to send email: ' . $result['message']);
            }
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

<?php if (!$isOwnProfile && $viewUser): ?>
<a href="index.php" class="btn btn-back">
    <i class="bi bi-arrow-left"></i> Back
</a>
<?= displayFlashMessages() ?>
<div class="profile-row">
    <div class="profile-card">
        <div class="profile-avatar-large">
            <img src="<?= htmlspecialchars($viewUser['profile_image']) ?>" alt="">
        </div>
        <h3 class="public-profile-name"><?= htmlspecialchars($viewUser['display_name'] ?? $viewUser['username']) ?></h3>
        <p class="text-muted">@<?= htmlspecialchars($viewUser['username']) ?></p>
        <div class="friend-actions mt-2">
            <?php if ($friendStatus === null): ?>
                <button class="btn btn-primary btn-sm" onclick="addFriendAndRefresh(<?= $viewUser['id'] ?>, '<?= htmlspecialchars($viewUser['username'], ENT_QUOTES) ?>')">Add Friend</button>
            <?php elseif ($friendStatus === 'pending_sent'): ?>
                <span class="badge bg-warning text-dark p-2">Friend request sent</span>
            <?php elseif ($friendStatus === 'pending_received'): ?>
                <button class="btn btn-success btn-sm me-1" onclick="respondToRequest(<?= $viewUser['id'] ?>, 'accept')">Accept</button>
                <button class="btn btn-outline-danger btn-sm" onclick="respondToRequest(<?= $viewUser['id'] ?>, 'decline')">Decline</button>
            <?php elseif ($friendStatus === 'accepted'): ?>
                <span class="badge bg-success p-2 me-2">Friends</span>
                <button class="btn btn-danger btn-sm" onclick="unfriend(<?= $viewUser['id'] ?>)">Unfriend</button>
            <?php endif; ?>
        </div>
        
        <?php if ($friendStatus === 'accepted'): ?>
        <div class="friend-actions mt-3">
            <div class="dropdown">
                <button class="btn btn-outline btn-sm" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); openUserChatById(<?= $viewUser['id'] ?>)"><i class="bi bi-chat-left-text me-2"></i>Chat</a></li>
                    <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); initiateCall(<?= $viewUser['id'] ?>)"><i class="bi bi-telephone me-2"></i>Call</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); unfriend(<?= $viewUser['id'] ?>)"><i class="bi bi-person-x me-2"></i>Unfriend</a></li>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($friendStatus === 'accepted'): ?>
<div class="chat-stats-section mt-4">
    <div class="profile-card">
        <h6 class="text-muted mb-3">Chat History with <?= htmlspecialchars($viewUser['display_name'] ?? $viewUser['username']) ?></h6>
        <div class="chart-container"><canvas id="chatHistoryChart"></canvas></div>
        <div class="stats-info mt-2" id="statsInfo">
            <span class="text-muted">Loading stats...</span>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php if ($isOwnProfile): ?>
<a href="index.php" class="btn btn-back">
    <i class="bi bi-arrow-left"></i> Back
</a>

<h1 class="profile-title">Profile Settings</h1>
<?= displayFlashMessages() ?>
<div class="profile-row">
    <?php // Left card - Profile picture with upload button ?>
    <div class="profile-card">
        <div class="profile-avatar-large">
            <img src="<?= htmlspecialchars($user['profile_image'] ?? $_SESSION['profile_image'] ?? 'img/default-avatar.svg') ?>" alt="Profile" id="profileAvatar">
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
    <?php // Form for requesting reset code ?>
    <form method="POST" class="reset-form mb-3">
        <div class="reset-row" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <input type="email" class="form-control" name="email" placeholder="Enter email" value="<?= htmlspecialchars($user['email']) ?>" required style="flex: 1; min-width: 150px;">
            <button type="submit" name="request_code" class="btn btn-secondary" style="white-space: nowrap;">Get Reset Code</button>
        </div>
    </form>
    <?php // Form for entering code and resetting password ?>
    <form method="POST" class="reset-form">
        <div class="reset-row three-cols">
            <input type="text" class="form-control" name="reset_code" placeholder="Enter Code (sent to email)" value="<?= htmlspecialchars($_POST['reset_code'] ?? '') ?>" required>
            <input type="password" class="form-control" name="new_password" placeholder="New Password" required minlength="6">
            <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password" required minlength="6">
        </div>
        <button type="submit" name="reset_password" class="btn btn-dark w-100">Update Password</button>
    </form>
</div>

<div class="chat-stats-section mt-4">
    <div class="profile-card">
        <h6 class="text-muted mb-3">My Chat Activity (All Messages)</h6>
        <div class="chart-container"><canvas id="allChatHistoryChart"></canvas></div>
        <div class="stats-info mt-2" id="allStatsInfo">
            <span class="text-muted">Loading stats...</span>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.public-profile-name {
    font-size: 1.4rem;
    margin-bottom: 2px;
}

.friend-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.chat-stats {
    text-align: center;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 12px;
    margin-top: 20px;
}

.chat-stats-section {
    width: 100%;
}

.chat-stats-section .profile-card {
    text-align: center;
}

.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
    max-width: 600px;
    margin: 0 auto;
    padding: 15px;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

@media (max-width: 576px) {
    .chart-container {
        height: 220px;
        padding: 10px;
    }
}

.chat-stats h6 {
    margin-bottom: 10px;
}

#chatHistoryChart,
#allChatHistoryChart {
    display: block;
    margin: 0 auto;
    background: white;
    border-radius: 8px;
}

.stats-info {
    font-size: 0.85rem;
}

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
    background: #6c5ce7;
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
    object-fit: contain;
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
    gap: 15px;
}

@media (max-width: 576px) {
    .reset-row:not(.three-cols) {
        flex-direction: column;
    }
    .reset-row:not(.three-cols) .btn-secondary {
        width: 100%;
    }
}

.reset-row.three-cols input {
    margin-bottom: 10px;
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

.mb-3 {
    margin-bottom: 1rem;
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

<?php if ($isOwnProfile): ?>
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
<?php endif; ?>

<?php if (!$isOwnProfile && isset($viewUser) && $friendStatus === 'accepted'): ?>
<script>
console.log('Chart loaded:', typeof Chart);
function loadChatStats() {
    console.log('loadChatStats called');
    const canvas = document.getElementById('chatHistoryChart');
    if (!canvas) {
        console.log('Canvas not found!');
        return;
    }
    if (typeof Chart === 'undefined') {
        console.log('Chart.js not loaded!');
        return;
    }
    
    const statsInfo = document.getElementById('statsInfo');
    
    console.log('Fetching API...');
    fetch('api/get_message_stats.php?friend_id=<?= $viewUser['id'] ?>')
        .then(res => res.json())
        .then(data => {
            console.log('API response:', data);
            if (data.success && data.data_points && data.data_points.length > 0) {
                drawChart(canvas, data);
                statsInfo.innerHTML = `<span class="text-muted">${data.total_messages} messages over ${data.days_friends} days</span>`;
            } else {
                statsInfo.innerHTML = '<span class="text-muted">No messages yet</span>';
            }
        })
        .catch(err => {
            console.error('Error:', err);
            statsInfo.innerHTML = '<span class="text-muted">Could not load stats</span>';
        });
}

function drawChart(canvas, data) {
    const points = data.data_points;
    if (!points || points.length === 0) return;
    
    if (canvas.chart) canvas.chart.destroy();
    
    const labels = points.map(p => p.day === 0 ? 'Day 0' : 'Day ' + p.day);
    const values = points.map(p => p.count);
    
    const ctx = canvas.getContext('2d');
    
    canvas.chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Messages',
                data: values,
                borderColor: '#6c5ce7',
                backgroundColor: 'rgba(108, 92, 231, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#6c5ce7',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#888', font: { size: 11 }, maxTicksLimit: 6 }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f0f0f0' },
                    ticks: { color: '#888', font: { size: 11 }, stepSize: 1 }
                }
            }
        }
    });
}

loadChatStats();
</script>
<?php endif; ?>

<script>
function drawChart(canvas, data) {
    const points = data.data_points;
    if (!points || points.length === 0) return;
    
    if (canvas.chart) canvas.chart.destroy();
    
    const labels = points.map(p => p.day === 0 ? 'Day 0' : 'Day ' + p.day);
    const values = points.map(p => p.count);
    
    const ctx = canvas.getContext('2d');
    
    canvas.chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Messages',
                data: values,
                borderColor: '#6c5ce7',
                backgroundColor: 'rgba(108, 92, 231, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#6c5ce7',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#888', font: { size: 11 }, maxTicksLimit: 6 }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f0f0f0' },
                    ticks: { color: '#888', font: { size: 11 }, stepSize: 1 }
                }
            }
        }
    });
}
</script>

<?php if ($isOwnProfile): ?>
<script>
// All messages chart for own profile
function loadAllChatStats() {
    const canvas = document.getElementById('allChatHistoryChart');
    if (!canvas) {
        console.log('Canvas not found for allChatHistoryChart');
        return;
    }
    if (typeof Chart === 'undefined') {
        console.log('Chart.js not loaded!');
        return;
    }
    
    console.log('loadAllChatStats called, canvas found');
    const statsInfo = document.getElementById('allStatsInfo');
    
    fetch('api/get_all_message_stats.php')
        .then(res => res.json())
        .then(data => {
            console.log('All messages API response:', data);
            if (data.success && data.data_points && data.data_points.length > 0) {
                drawChart(canvas, data);
                statsInfo.innerHTML = `<span class="text-muted">${data.total_messages} messages over ${data.days_active} days</span>`;
            } else {
                statsInfo.innerHTML = '<span class="text-muted">No messages yet</span>';
            }
        })
        .catch(err => {
            console.error('Error loading all stats:', err);
            statsInfo.innerHTML = '<span class="text-muted">Could not load stats</span>';
        });
}
</script>
<?php endif; ?>

<?php if ($isOwnProfile): ?>
<script>
console.log('Own profile chart loaded:', typeof Chart);
function loadAllChatStats() {
    const canvas = document.getElementById('allChatHistoryChart');
    if (!canvas) {
        console.log('Canvas not found for allChatHistoryChart');
        return;
    }
    if (typeof Chart === 'undefined') {
        console.log('Chart.js not loaded!');
        return;
    }
    
    console.log('loadAllChatStats called, canvas found');
    const statsInfo = document.getElementById('allStatsInfo');
    
    fetch('api/get_all_message_stats.php')
        .then(res => res.json())
        .then(data => {
            console.log('All messages API response:', data);
            if (data.success && data.data_points && data.data_points.length > 0) {
                drawChart(canvas, data);
                statsInfo.innerHTML = `<span class="text-muted">${data.total_messages} messages over ${data.days_active} days</span>`;
            } else {
                statsInfo.innerHTML = '<span class="text-muted">No messages yet</span>';
            }
        })
        .catch(err => {
            console.error('Error loading all stats:', err);
            statsInfo.innerHTML = '<span class="text-muted">Could not load stats</span>';
        });
}

loadAllChatStats();
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>