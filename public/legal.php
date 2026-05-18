<?php
require_once __DIR__ . '/../includes/session.php';

$pageTitle = "Legal";

require_once __DIR__ . '/../includes/header.php';
?>

<div class="legal-container">
    <a href="index.php" class="btn btn-back">
        <i class="bi bi-arrow-left"></i> Back
    </a>
    
    <h1 class="legal-title">Legal & Policies</h1>
    
    <div class="legal-tabs">
        <button class="tab-btn active" data-tab="terms">Terms of Service</button>
        <button class="tab-btn" data-tab="privacy">Privacy Policy</button>
        <button class="tab-btn" data-tab="cookies">Cookie Policy</button>
    </div>
    
    <div class="tab-content">
        <div class="tab-pane active" id="terms">
            <div class="policy-card">
                <h2>1. Acceptance of Terms</h2>
                <p>By accessing or using Quacko, you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our services.</p>
            </div>
            
            <div class="policy-card">
                <h2>2. User Accounts</h2>
                <p>To use certain features, such as adding friends, you must create an account. You are responsible for:</p>
                <ul>
                    <li>Maintaining the confidentiality of your login credentials.</li>
                    <li>All activities that occur under your account.</li>
                    <li>Providing accurate and up-to-date information.</li>
                </ul>
            </div>
            
            <div class="policy-card">
                <h2>3. User Conduct</h2>
                <p>You agree not to use Quacko to:</p>
                <ul>
                    <li>Harass, abuse, or harm other users.</li>
                    <li>Post or share illegal, offensive, or copyrighted content without permission.</li>
                    <li>Spam or attempt to disrupt the platform's security.</li>
                </ul>
            </div>
            
            <div class="policy-card">
                <h2>4. Content Ownership</h2>
                <p><strong>Your Content:</strong> You retain ownership of the text and media you post, but you grant Quacko a license to display and distribute it within the platform.</p>
                <p><strong>Our Content:</strong> The Quacko logo, design, and software are the property of Quacko and are protected by intellectual property laws.</p>
            </div>
            
            <div class="policy-card">
                <h2>5. Termination</h2>
                <p>We reserve the right to suspend or terminate your account at any time, without notice, if we believe you have violated these terms.</p>
            </div>
            
            <div class="policy-card">
                <h2>6. Limitation of Liability</h2>
                <p>Quacko is provided "as is." We are not liable for any damages resulting from your use of the platform, including data loss or interactions with other users.</p>
            </div>
            
            <div class="policy-card">
                <h2>7. Changes to Terms</h2>
                <p>We may update these terms from time to time. Continued use of the site after changes are posted constitutes your acceptance of the new terms.</p>
            </div>
        </div>
        
        <div class="tab-pane" id="privacy">
            <div class="policy-card">
                <h2>1. Information We Collect</h2>
                <p>We collect information you provide when creating an account, such as username, email, and profile information. We also collect usage data to improve our services.</p>
            </div>
            
            <div class="policy-card">
                <h2>2. How We Use Your Information</h2>
                <p>Your information is used to:</p>
                <ul>
                    <li>Provide and maintain our services</li>
                    <li>Improve and personalize your experience</li>
                    <li>Communicate with you about updates and support</li>
                    <li>Ensure security and prevent fraud</li>
                </ul>
            </div>
            
            <div class="policy-card">
                <h2>3. Data Protection</h2>
                <p>We implement appropriate security measures to protect your personal information. Your data is stored securely and accessed only by authorized personnel.</p>
            </div>
            
            <div class="policy-card">
                <h2>4. Sharing Your Information</h2>
                <p>We do not sell your personal information. We may share data with service providers who help operate our platform, or when required by law.</p>
            </div>
            
            <div class="policy-card">
                <h2>5. Your Rights</h2>
                <p>You have the right to:</p>
                <ul>
                    <li>Access your personal data</li>
                    <li>Request correction of inaccurate data</li>
                    <li>Request deletion of your data</li>
                    <li>Opt-out of certain data collection</li>
                </ul>
            </div>
            
            <div class="policy-card">
                <h2>6. Contact Us</h2>
                <p>If you have questions about this Privacy Policy, please contact us through the platform.</p>
            </div>
        </div>
        
        <div class="tab-pane" id="cookies">
            <div class="policy-card">
                <h2>1. What Are Cookies?</h2>
                <p>Cookies are small text files stored on your device when you visit websites. They help remember your preferences and improve your experience.</p>
            </div>
            
            <div class="policy-card">
                <h2>2. How We Use Cookies</h2>
                <p>Quacko uses cookies for:</p>
                <ul>
                    <li>Keeping you logged in</li>
                    <li>Remembering your preferences</li>
                    <li>Analyzing site traffic and performance</li>
                    <li>Enhancing security</li>
                </ul>
            </div>
            
            <div class="policy-card">
                <h2>3. Types of Cookies We Use</h2>
                <p><strong>Essential Cookies:</strong> Required for basic site functionality.</p>
                <p><strong>Analytics Cookies:</strong> Help us understand how visitors use our site.</p>
                <p><strong>Functional Cookies:</strong> Remember your preferences and settings.</p>
            </div>
            
            <div class="policy-card">
                <h2>4. Managing Cookies</h2>
                <p>You can control or delete cookies through your browser settings. Please note that disabling essential cookies may affect site functionality.</p>
            </div>
            
            <div class="policy-card">
                <h2>5. Updates to Cookie Policy</h2>
                <p>We may update this policy periodically. Any changes will be posted on this page.</p>
            </div>
        </div>
    </div>
    
    <button class="btn btn-read-more" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
        <i class="bi bi-arrow-up"></i> Back to Top
    </button>
</div>

<style>
.legal-container {
    padding: 20px;
    max-width: 900px;
    margin: 0 auto;
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

.legal-title {
    margin-bottom: 25px;
}

.legal-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 25px;
    border-bottom: 2px solid #ddd;
    padding-bottom: 10px;
}

.tab-btn {
    padding: 10px 20px;
    background: transparent;
    border: none;
    cursor: pointer;
    font-size: 1rem;
    color: #666;
    border-radius: 5px 5px 0 0;
    transition: all 0.2s;
}

.tab-btn:hover {
    background: #f0f0f0;
}

.tab-btn.active {
    background: var(--primary);
    color: white;
}

.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
}

.policy-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 15px;
}

.policy-card h2 {
    font-size: 1.1rem;
    margin-bottom: 10px;
    color: var(--primary);
}

.policy-card p {
    margin-bottom: 10px;
    line-height: 1.6;
}

.policy-card ul {
    padding-left: 20px;
    margin-bottom: 10px;
}

.policy-card li {
    margin-bottom: 5px;
}

.btn-read-more {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 15px;
    margin-top: 20px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 10px;
    cursor: pointer;
    font-size: 1rem;
}

.btn-read-more:hover {
    background: #f0f0f0;
}
</style>

<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        
        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).classList.add('active');
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>