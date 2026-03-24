<?php
// ============================================================
//  Adaxy Academy · Website Content Manager
//  Edit homepage sections, about content, etc.
// ============================================================

session_start();
include('../config/db_connect.php');

if (empty($_SESSION['alogin'])) {
    header('Location: ../Auth/login.php?role=admin');
    exit;
}

$username = $_SESSION['alogin'];

$stmt = $conn->prepare("SELECT * FROM admin WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$admin) {
    session_destroy();
    header('Location: ../Auth/login.php?role=admin');
    exit;
}

$full_name = $admin['full_name'];
$initials = strtoupper(substr($full_name, 0, 1) . substr(explode(' ', $full_name)[1] ?? '', 0, 1));

// Create website_content table if not exists
$conn->query("
    CREATE TABLE IF NOT EXISTS `website_content` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `section` varchar(100) NOT NULL,
        `content` text,
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `section` (`section`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// Insert default content if empty
$check = $conn->query("SELECT COUNT(*) as count FROM website_content")->fetch_assoc()['count'];
if ($check == 0) {
    $defaults = [
        ['hero_title', 'Shaping Minds,<br><span>Building Futures</span>'],
        ['hero_subtitle', 'Adaxy Academy delivers outstanding secondary education grounded in academic excellence, character development, and a passion for lifelong learning.'],
        ['mission', 'To provide a stimulating, inclusive, and values-driven learning environment that empowers every student to achieve their highest academic and personal potential.'],
        ['vision', 'To be the leading centre of academic excellence in Malawi, producing graduates who are confident, compassionate, and globally competitive.'],
        ['values', 'We are guided by integrity, excellence, discipline, respect, and community service — values that define every aspect of school life at Adaxy Academy.'],
        ['about_description', 'Founded in 1985, Adaxy Academy has grown into one of Malawi\'s most respected secondary schools, nurturing students through the JCE and MSCE programmes.'],
        ['address', 'P.O. Box 1204, Area 47, Lilongwe, Malawi'],
        ['phone', '+265 (0)1 234 567 / +265 (0)99 123 4567'],
        ['email', 'info@Adaxyacademy.mw'],
        ['hours', 'Mon – Fri: 07:30 – 17:00<br>Sat: 08:00 – 12:00'],
        ['jce_description', 'Our Junior Certificate of Education programme provides a strong academic foundation across core and elective subjects, preparing students for MSCE and beyond.'],
        ['msce_description', 'Our Malawi School Certificate of Education programme offers a broad range of advanced subjects, with pathways into university, technical colleges, and professional training.']
    ];
    
    $stmt = $conn->prepare("INSERT INTO website_content (section, content) VALUES (?, ?)");
    foreach ($defaults as $default) {
        $stmt->bind_param("ss", $default[0], $default[1]);
        $stmt->execute();
    }
    $stmt->close();
}

// Handle form submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $sections = ['hero_title', 'hero_subtitle', 'mission', 'vision', 'values', 'about_description', 
                 'address', 'phone', 'email', 'hours', 'jce_description', 'msce_description'];
    
    foreach ($sections as $section) {
        $content = $conn->real_escape_string($_POST[$section]);
        $conn->query("UPDATE website_content SET content = '$content' WHERE section = '$section'");
    }
    $success = "Website content updated successfully!";
}

// Fetch all content
$content = [];
$result = $conn->query("SELECT section, content FROM website_content");
while ($row = $result->fetch_assoc()) {
    $content[$row['section']] = $row['content'];
}

$conn->close();
$page_title = 'Website Content';
include 'includes/admin_header.php';
?>

<style>
    .content-editor {
        background: white;
        border-radius: 24px;
        border: 1px solid #E5E7EB;
        overflow: hidden;
        margin-bottom: 24px;
    }
    .editor-header {
        padding: 20px 24px;
        background: #F8FAFE;
        border-bottom: 1px solid #E5E7EB;
    }
    .editor-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .editor-body {
        padding: 24px;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }
    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        font-size: 14px;
        transition: all 0.2s;
    }
    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }
    .form-control:focus {
        border-color: #2563EB;
        outline: none;
        box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    }
    .btn-save {
        background: #2563EB;
        color: white;
        padding: 12px 28px;
        border-radius: 40px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-save:hover {
        background: #1D4ED8;
        transform: translateY(-1px);
    }
    .alert-success {
        background: #DCFCE7;
        border: 1px solid #86EFAC;
        color: #15803D;
        padding: 14px 18px;
        border-radius: 12px;
        margin-bottom: 20px;
    }
    .preview-link {
        background: #F3F4F6;
        padding: 8px 16px;
        border-radius: 40px;
        font-size: 13px;
        text-decoration: none;
        color: #374151;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .preview-link:hover {
        background: #E5E7EB;
    }
    @media (max-width: 768px) {
        .editor-body {
            padding: 20px;
        }
    }
</style>

<div class="content-container" style="max-width: 1000px; margin: 0 auto;">

    <div class="welcome-section fade-up">
        <div class="welcome-content">
            <div>
                <div class="greeting-badge">
                    <i class="fas fa-edit"></i> Content Management
                </div>
                <h1>Website Content Editor</h1>
                <p>Edit text content on the public landing page</p>
            </div>
            <div>
                <a href="../index.php" target="_blank" class="preview-link">
                    <i class="fas fa-eye"></i> Preview Website
                </a>
            </div>
        </div>
    </div>

    <?php if ($success): ?>
    <div class="alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <!-- Hero Section -->
        <div class="content-editor fade-up">
            <div class="editor-header">
                <h3><i class="fas fa-home"></i> Hero Section</h3>
            </div>
            <div class="editor-body">
                <div class="form-group">
                    <label>Hero Title</label>
                    <input type="text" name="hero_title" class="form-control" 
                           value="<?= htmlspecialchars($content['hero_title'] ?? '') ?>">
                    <small class="text-muted">Use &lt;br&gt; for line breaks</small>
                </div>
                <div class="form-group">
                    <label>Hero Subtitle</label>
                    <textarea name="hero_subtitle" class="form-control" rows="3"><?= htmlspecialchars($content['hero_subtitle'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- About Section -->
        <div class="content-editor fade-up">
            <div class="editor-header">
                <h3><i class="fas fa-info-circle"></i> About Section</h3>
            </div>
            <div class="editor-body">
                <div class="form-group">
                    <label>Mission Statement</label>
                    <textarea name="mission" class="form-control" rows="3"><?= htmlspecialchars($content['mission'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Vision Statement</label>
                    <textarea name="vision" class="form-control" rows="3"><?= htmlspecialchars($content['vision'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Core Values</label>
                    <textarea name="values" class="form-control" rows="3"><?= htmlspecialchars($content['values'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>About Description</label>
                    <textarea name="about_description" class="form-control" rows="4"><?= htmlspecialchars($content['about_description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Enrollment Section -->
        <div class="content-editor fade-up">
            <div class="editor-header">
                <h3><i class="fas fa-graduation-cap"></i> Enrollment Section</h3>
            </div>
            <div class="editor-body">
                <div class="form-group">
                    <label>JCE Programme Description</label>
                    <textarea name="jce_description" class="form-control" rows="4"><?= htmlspecialchars($content['jce_description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>MSCE Programme Description</label>
                    <textarea name="msce_description" class="form-control" rows="4"><?= htmlspecialchars($content['msce_description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="content-editor fade-up">
            <div class="editor-header">
                <h3><i class="fas fa-address-card"></i> Contact Information</h3>
            </div>
            <div class="editor-body">
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($content['address'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Phone Number(s)</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($content['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($content['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Office Hours</label>
                    <textarea name="hours" class="form-control" rows="2"><?= htmlspecialchars($content['hours'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div style="text-align: right; margin-top: 20px;">
            <button type="submit" name="update" class="btn-save">
                <i class="fas fa-save"></i> Save All Changes
            </button>
        </div>
    </form>

    <div class="dashboard-card fade-up" style="margin-top: 24px; background: #FEF3C7; border-color: #FDE68A;">
        <div class="card-header">
            <div class="header-title">
                <i class="fas fa-info-circle" style="color: #F59E0B;"></i>
                <h3>Instructions</h3>
            </div>
        </div>
        <div class="p-4">
            <ul class="mb-0">
                <li>Changes are saved immediately to the database</li>
                <li>Visit the <a href="../index.php" target="_blank">homepage</a> to see your updates</li>
                <li>Use &lt;br&gt; to create line breaks in the hero title</li>
                <li>All other text will display as entered</li>
            </ul>
        </div>
    </div>

</div>

<script>
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert-success');
    alerts.forEach(alert => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    });
}, 4000);
</script>

