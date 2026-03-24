<?php
// ============================================================
//  Adaxy Academy · Admin Dashboard
//  Content management system for website content
// ============================================================

session_start();
include('../config/db_connect.php');

// ── Authentication Guard ─────────────────────────────────────
if (empty($_SESSION['alogin'])) {
    header('Location: ../Auth/login.php?role=admin');
    exit;
}

$username = $_SESSION['alogin'];

// ── Fetch Admin Information ──────────────────────────────────
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

// ── Website Content Statistics ───────────────────────────────
// Get counts
$notices_count = $conn->query("SELECT COUNT(*) as count FROM notices")->fetch_assoc()['count'];
$students_count = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$teachers_count = $conn->query("SELECT COUNT(*) as count FROM teachers")->fetch_assoc()['count'];
$classes_count = $conn->query("SELECT COUNT(*) as count FROM classes")->fetch_assoc()['count'];

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

<style>
    .stat-card-admin {
        background: white;
        border-radius: 20px;
        padding: 24px;
        border: 1px solid #E5E7EB;
        transition: all 0.2s;
    }
    .stat-card-admin:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.1);
    }
    .stat-icon-admin {
        width: 52px;
        height: 52px;
        background: #EFF6FF;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
    }
    .stat-icon-admin i {
        font-size: 24px;
        color: #2563EB;
    }
    .quick-action {
        background: white;
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        transition: all 0.2s;
        border: 1px solid #E5E7EB;
        text-decoration: none;
        display: block;
        color: inherit;
    }
    .quick-action:hover {
        transform: translateY(-4px);
        border-color: #2563EB;
        box-shadow: 0 8px 20px rgba(37,99,235,0.1);
    }
</style>

<div class="admin-container" style="max-width: 1400px; margin: 0 auto;">

    <!-- Welcome Header -->
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

    <!-- Stats Grid -->
    <div class="row g-4 mb-5">
        <div class="col-md-3 col-6 fade-up">
            <div class="stat-card-admin text-center">
                <div class="stat-icon-admin mx-auto"><i class="fas fa-newspaper"></i></div>
                <h3 class="mb-0"><?= $notices_count ?></h3>
                <p class="text-muted">Total Notices</p>
            </div>
        </div>
        <div class="col-md-3 col-6 fade-up" style="transition-delay: 0.05s">
            <div class="stat-card-admin text-center">
                <div class="stat-icon-admin mx-auto"><i class="fas fa-users"></i></div>
                <h3 class="mb-0"><?= $students_count ?></h3>
                <p class="text-muted">Students</p>
            </div>
        </div>
        <div class="col-md-3 col-6 fade-up" style="transition-delay: 0.1s">
            <div class="stat-card-admin text-center">
                <div class="stat-icon-admin mx-auto"><i class="fas fa-chalkboard-user"></i></div>
                <h3 class="mb-0"><?= $teachers_count ?></h3>
                <p class="text-muted">Teachers</p>
            </div>
        </div>
        <div class="col-md-3 col-6 fade-up" style="transition-delay: 0.15s">
            <div class="stat-card-admin text-center">
                <div class="stat-icon-admin mx-auto"><i class="fas fa-building"></i></div>
                <h3 class="mb-0"><?= $classes_count ?></h3>
                <p class="text-muted">Classes</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-5">
        <div class="col-md-3 col-6 fade-up">
            <a href="notices.php" class="quick-action">
                <i class="fas fa-bullhorn" style="font-size: 32px; color: #2563EB;"></i>
                <h5 class="mt-2 mb-0">Manage Notices</h5>
                <small class="text-muted">Create & publish announcements</small>
            </a>
        </div>
        <div class="col-md-3 col-6 fade-up" style="transition-delay: 0.05s">
            <a href="content.php" class="quick-action">
                <i class="fas fa-edit" style="font-size: 32px; color: #10B981;"></i>
                <h5 class="mt-2 mb-0">Website Content</h5>
                <small class="text-muted">Edit homepage, about, etc.</small>
            </a>
        </div>
        <div class="col-md-3 col-6 fade-up" style="transition-delay: 0.1s">
            <a href="settings.php" class="quick-action">
                <i class="fas fa-cog" style="font-size: 32px; color: #F59E0B;"></i>
                <h5 class="mt-2 mb-0">System Settings</h5>
                <small class="text-muted">Configure school info</small>
            </a>
        </div>
        <div class="col-md-3 col-6 fade-up" style="transition-delay: 0.15s">
            <a href="../Auth/logout.php" class="quick-action">
                <i class="fas fa-sign-out-alt" style="font-size: 32px; color: #EF4444;"></i>
                <h5 class="mt-2 mb-0">Logout</h5>
                <small class="text-muted">End session</small>
            </a>
        </div>
    </div>

    <!-- Recent Notices -->
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
                            <span><i class="fas fa-tag"></i> <?= ucfirst($notice['audience']) ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state small">
                    <i class="fas fa-inbox"></i>
                    <p>No notices yet. Create your first notice!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

