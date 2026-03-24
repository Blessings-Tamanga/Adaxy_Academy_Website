<?php
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

// Get counts
$notices_count = $conn->query("SELECT COUNT(*) as count FROM notices")->fetch_assoc()['count'];
$students_count = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$teachers_count = $conn->query("SELECT COUNT(*) as count FROM teachers")->fetch_assoc()['count'];
$website_content_count = $conn->query("SELECT COUNT(*) as count FROM website_content")->fetch_assoc()['count'] ?? 0;

// Recent notices
$recent_notices = $conn->query("
    SELECT * FROM notices 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

$conn->close();
$page_title = 'Admin Dashboard';
include 'includes/admin_header.php';
?>

<div class="admin-container" style="max-width: 1400px; margin: 0 auto;">

    <div class="welcome-section fade-up">
        <div class="welcome-content">
            <div>
                <div class="greeting-badge">
                    <i class="fas fa-user-shield"></i> Administrator Panel
                </div>
                <h1>Welcome, <?= htmlspecialchars($full_name) ?>!</h1>
                <p>Manage website content, announcements, and system settings</p>
            </div>
            <div class="admin-avatar">
                <div class="avatar-circle">
                    <span><?= $initials ?></span>
                </div>
                <div class="avatar-badge">
                    <i class="fas fa-check-circle"></i> Admin
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-3 col-6 fade-up">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-newspaper"></i></div>
                <div class="stat-info">
                    <h3><?= $notices_count ?></h3>
                    <p>Total Notices</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 fade-up" style="transition-delay: 0.05s">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <h3><?= $students_count ?></h3>
                    <p>Students</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 fade-up" style="transition-delay: 0.1s">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-chalkboard-user"></i></div>
                <div class="stat-info">
                    <h3><?= $teachers_count ?></h3>
                    <p>Teachers</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 fade-up" style="transition-delay: 0.15s">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-edit"></i></div>
                <div class="stat-info">
                    <h3><?= $website_content_count ?></h3>
                    <p>Content Items</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4 fade-up">
            <a href="notices.php" class="dashboard-card" style="display: block; text-decoration: none; color: inherit; text-align: center; padding: 24px;">
                <i class="fas fa-bullhorn" style="font-size: 48px; color: #2563EB;"></i>
                <h4 class="mt-3 mb-2">Manage Notices</h4>
                <p class="text-muted small">Create, edit, and publish announcements</p>
            </a>
        </div>
        <div class="col-md-4 fade-up" style="transition-delay: 0.05s">
            <a href="content.php" class="dashboard-card" style="display: block; text-decoration: none; color: inherit; text-align: center; padding: 24px;">
                <i class="fas fa-edit" style="font-size: 48px; color: #10B981;"></i>
                <h4 class="mt-3 mb-2">Website Content</h4>
                <p class="text-muted small">Edit homepage text, mission, vision</p>
            </a>
        </div>
        <div class="col-md-4 fade-up" style="transition-delay: 0.1s">
            <a href="../Auth/logout.php" class="dashboard-card" style="display: block; text-decoration: none; color: inherit; text-align: center; padding: 24px;">
                <i class="fas fa-sign-out-alt" style="font-size: 48px; color: #EF4444;"></i>
                <h4 class="mt-3 mb-2">Logout</h4>
                <p class="text-muted small">End your session</p>
            </a>
        </div>
    </div>

    <div class="dashboard-card fade-up">
        <div class="card-header">
            <div class="header-title">
                <i class="fas fa-bell"></i>
                <h3>Recent Notices</h3>
            </div>
            <a href="notices.php" class="card-link">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="notices-list">
            <?php if ($recent_notices): ?>
                <?php foreach ($recent_notices as $notice): ?>
                <div class="notice-item">
                    <div class="notice-icon"><i class="fas fa-bullhorn"></i></div>
                    <div class="notice-content">
                        <div class="notice-title"><?= htmlspecialchars($notice['title']) ?></div>
                        <div class="notice-meta">
                            <span><i class="fas fa-user"></i> <?= htmlspecialchars($notice['posted_by']) ?></span>
                            <span><i class="fas fa-calendar"></i> <?= date('M j, Y', strtotime($notice['created_at'])) ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No notices yet. Create your first notice!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="dashboard-footer">
        <div class="footer-content">
            <span><i class="fas fa-shield-alt"></i> Secure Admin Portal</span>
            <span>Adaxy Academy · <?= date('Y') ?></span>
            <span><i class="fas fa-clock"></i> <?= date('l, F j, Y') ?></span>
        </div>
    </div>

</div>

<?php include 'includes/admin_footer.php'; ?>