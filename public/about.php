<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../database/db.php';

$pageTitle = "About Us";

$stats = [
    'rooms' => 0,
    'users' => 0,
    'messages' => 0
];

try {
    $dbconn = getDBConnection();
    if ($dbconn) {
        $stmt = $dbconn->prepare("SELECT COUNT(*) FROM rooms");
        $stmt->execute();
        $stats['rooms'] = $stmt->fetchColumn();
        
        $stmt = $dbconn->prepare("SELECT COUNT(*) FROM users");
        $stmt->execute();
        $stats['users'] = $stmt->fetchColumn();
        
        $stmt = $dbconn->prepare("SELECT COUNT(*) FROM messages");
        $stmt->execute();
        $stats['messages'] = $stmt->fetchColumn();
    }
} catch (Exception $e) {
    // Use default values if DB fails
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="about-container">
    <div class="about-main">
        <a href="index.php" class="btn btn-back">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        
        <h1 class="about-title">About Quacko</h1>
        
        <div class="profile-section">
            <div class="profile-card-small">
                <img src="img/default-avatar.svg" alt="Creator" class="profile-avatar-small">
                <h3>Enno Keanu Elbert</h3>
                <p class="text-muted">Creator & Developer</p>
            </div>
            
            <div class="profile-card-large">
                <img src="img/default-avatar.svg" alt="Creator" class="profile-avatar-large">
                <h2>Enno Keanu Elbert</h2>
                <p class="bio">
                    I am a passionate developer with a vision to create a modern, seamless chat platform. 
                    Quacko represents my journey in web development, combining creativity with technical expertise 
                    to build something meaningful for the community.
                </p>
            </div>
        </div>
        
        <div class="content-sections">
            <div class="content-box">
                <h2><i class="bi bi-lightbulb"></i> Why Us?</h2>
                <p>
                    Quacko stands out from other chat platforms because we prioritize 
                    <strong>simplicity, privacy, and community</strong>. Unlike mainstream platforms 
                    cluttered with ads and tracking, we focus on what matters most - 
                    connecting people in a clean, distraction-free environment.
                </p>
                <ul>
                    <li>✨ Clean, modern interface</li>
                    <li>🔒 End-to-end privacy focus</li>
                    <li>🚀 Fast and responsive</li>
                    <li>💬 Public and private rooms</li>
                    <li>👥 Friend system with direct messaging</li>
                </ul>
            </div>
            
            <div class="content-box">
                <h2><i class="bi bi-briefcase"></i> Our Experience</h2>
                <p>
                    With expertise in web development using PHP, MySQL, Bootstrap 5, and JavaScript,
                    I've built Quacko from the ground up. This project demonstrates proficiency in:
                </p>
                <div class="skills-grid">
                    <span class="skill-tag">PHP</span>
                    <span class="skill-tag">MySQL</span>
                    <span class="skill-tag">Bootstrap 5</span>
                    <span class="skill-tag">JavaScript</span>
                    <span class="skill-tag">HTML/CSS</span>
                    <span class="skill-tag">Session Security</span>
                    <span class="skill-tag">Responsive Design</span>
                </div>
                <p class="mt-3">
                    This is a school project for Webbutveckling 2 and Webbserverprogrammering 1,
                    showcasing full-stack development skills.
                </p>
            </div>
        </div>
    </div>
    
    <aside class="about-sidebar">
        <h3>Live Statistics</h3>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-chat-dots"></i></div>
            <div class="stat-info">
                <span class="stat-number"><?= $stats['rooms'] ?></span>
                <span class="stat-label">Active Rooms</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <div class="stat-info">
                <span class="stat-number"><?= $stats['users'] ?></span>
                <span class="stat-label">Active Users</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-chat-left-text"></i></div>
            <div class="stat-info">
                <span class="stat-number"><?= $stats['messages'] ?></span>
                <span class="stat-label">Messages Sent</span>
            </div>
        </div>
    </aside>
</div>

<style>
.about-container {
    display: flex;
    gap: 20px;
    padding: 20px;
}

.about-main {
    flex: 1;
}

.about-sidebar {
    width: 300px;
    min-width: 280px;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 20px;
    color: #333;
    text-decoration: none;
    font-weight: 500;
}

.btn-back:hover {
    color: var(--primary);
}

.about-title {
    margin-bottom: 30px;
}

.profile-section {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
}

.profile-card-small {
    width: 200px;
    padding: 20px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 10px;
    text-align: center;
}

.profile-card-large {
    flex: 1;
    padding: 30px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 10px;
}

.profile-avatar-small {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    margin-bottom: 10px;
}

.profile-avatar-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    float: left;
    margin-right: 20px;
    margin-bottom: 10px;
}

.profile-card-large h2 {
    margin-bottom: 10px;
}

.profile-card-large .bio {
    color: #666;
    line-height: 1.6;
}

.content-sections {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.content-box {
    background: white;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 25px;
}

.content-box h2 {
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.content-box ul {
    list-style: none;
    padding: 0;
}

.content-box li {
    padding: 5px 0;
}

.skills-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 15px;
}

.skill-tag {
    background: #f0f0f0;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 0.9rem;
}

.about-sidebar h3 {
    margin-bottom: 20px;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 15px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 15px;
}

.stat-icon {
    width: 50px;
    height: 50px;
    background: var(--primary);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.stat-number {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
}

.stat-label {
    color: #666;
    font-size: 0.9rem;
}

@media (max-width: 991px) {
    .about-container {
        flex-direction: column;
    }
    .about-sidebar {
        width: 100%;
    }
    .profile-section {
        flex-direction: column;
    }
    .profile-card-small {
        width: 100%;
    }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>