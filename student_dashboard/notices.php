<?php
// ============================================================
//  Adaxy Academy · Student Notices
//  Clean and professional announcements page
// ============================================================

session_start();
include('../config/db_connect.php');

if (empty($_SESSION['slogin'])) {
    header('Location: ../Auth/login.php?role=student');
    exit;
}

$username = $_SESSION['slogin'];

$stmt = $conn->prepare("
    SELECT s.*, c.class_name, c.form_level, c.programme
    FROM   students s
    LEFT JOIN classes c ON c.class_id = s.class_id
    WHERE  s.username = ?
    LIMIT  1
");
$stmt->bind_param("s", $username);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    session_destroy();
    header('Location: ../Auth/login.php?role=student');
    exit;
}

$full_name  = $student['first_name'] . ' ' . $student['last_name'];
$first_name = $student['first_name'];
$class_name = $student['class_name'] ?? 'N/A';
$initials   = strtoupper(substr($student['first_name'],0,1) . substr($student['last_name'],0,1));

// Fetch notices for students
$notices = $conn->query("
    SELECT * FROM notices
    WHERE  is_published = 1
      AND  (audience = 'students' OR audience = 'all')
    ORDER  BY created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Helper function for time ago
function time_ago(string $dt): string {
    $diff = time() - strtotime($dt);
    if ($diff < 60) return 'just now';
    if ($diff < 3600)  return round($diff/60) . 'm ago';
    if ($diff < 86400) return round($diff/3600) . 'h ago';
    if ($diff < 604800) return round($diff/86400) . 'd ago';
    return date('M j', strtotime($dt));
}

$audience_colors = [
    'all'      => ['bg' => '#DBEAFE', 'color' => '#1E40AF', 'label' => 'All'],
    'students' => ['bg' => '#DCFCE7', 'color' => '#15803D', 'label' => 'Students'],
    'teachers' => ['bg' => '#EDE9FE', 'color' => '#6D28D9', 'label' => 'Teachers'],
    'parents'  => ['bg' => '#FFEDD5', 'color' => '#C2410C', 'label' => 'Parents'],
];

$page_title = 'Notices';
$conn->close();

include 'includes/header.php';
?>

<style>
    .notices-container {
        max-width: 1000px;
        margin: 0 auto;
    }
    
    .notice-card {
        background: white;
        border-radius: 20px;
        padding: 24px;
        margin-bottom: 20px;
        transition: all 0.2s;
        border: 1px solid #E5E7EB;
    }
    
    .notice-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        border-color: #2563EB;
    }
    
    .notice-title {
        font-size: 18px;
        font-weight: 700;
        color: #0F172A;
        margin-bottom: 8px;
    }
    
    .notice-meta {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 16px;
        font-size: 12px;
        color: #6B7280;
    }
    
    .notice-content {
        color: #374151;
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 16px;
    }
    
    .audience-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 30px;
        font-size: 11px;
        font-weight: 600;
        margin-left: 12px;
        vertical-align: middle;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 24px;
        background: white;
        border-radius: 20px;
        border: 1px solid #E5E7EB;
    }
    
    .empty-state i {
        font-size: 56px;
        color: #CBD5E1;
        margin-bottom: 16px;
    }
    
    .fade-up {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.5s ease, transform 0.5s ease;
    }
    
    .fade-up.visible {
        opacity: 1;
        transform: translateY(0);
    }
    
    @media (max-width: 768px) {
        .notice-card {
            padding: 20px;
        }
        .notice-title {
            font-size: 16px;
        }
    }
</style>

<div class="notices-container" style="padding: 0 20px 40px;">

    <!-- Header -->
    <div class="text-center mb-5 fade-up">
        <div class="section-tag">Stay Informed</div>
        <h2 style="font-size: 28px; margin: 8px 0 4px;">Official Notices</h2>
        <p style="color: var(--muted);">Important announcements from school administration</p>
        
        <!-- Notice Count Badge -->
        <div style="margin-top: 12px;">
            <span style="background: #EFF6FF; color: #2563EB; padding: 4px 16px; border-radius: 40px; font-size: 13px; font-weight: 500;">
                <i class="fa fa-bell me-1"></i> <?= count($notices) ?> notice<?= count($notices) !== 1 ? 's' : '' ?> available
            </span>
        </div>
    </div>

    <!-- Notices List -->
    <?php if ($notices): ?>
        <?php foreach ($notices as $i => $notice):
            $aud = $audience_colors[$notice['audience']] ?? $audience_colors['all'];
        ?>
        <div class="notice-card fade-up" style="transition-delay: <?= $i * 0.05 ?>s">
            <!-- Title and Badge -->
            <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 12px;">
                <h3 class="notice-title"><?= htmlspecialchars($notice['title']) ?></h3>
                <span class="audience-badge" style="background: <?= $aud['bg'] ?>; color: <?= $aud['color'] ?>;">
                    <?= $aud['label'] ?>
                </span>
            </div>
            
            <!-- Meta Info -->
            <div class="notice-meta">
                <span>
                    <i class="fa fa-user-circle" style="color: #2563EB;"></i> 
                    <?= htmlspecialchars($notice['posted_by']) ?>
                </span>
                <span>
                    <i class="fa fa-calendar-alt" style="color: #2563EB;"></i> 
                    <?= date('F j, Y', strtotime($notice['created_at'])) ?>
                </span>
                <span>
                    <i class="fa fa-clock" style="color: #2563EB;"></i> 
                    <?= time_ago($notice['created_at']) ?>
                </span>
            </div>
            
            <!-- Content -->
            <div class="notice-content">
                <?= nl2br(htmlspecialchars($notice['content'])) ?>
            </div>
        </div>
        <?php endforeach; ?>
        
        <!-- Footer Note -->
        <div class="text-center mt-4 fade-up" style="transition-delay: 0.2s;">
            <p style="font-size: 12px; color: #9CA3AF;">
                <i class="fa fa-envelope me-1"></i> For urgent matters, contact the school office directly.
            </p>
        </div>
        
    <?php else: ?>
        <div class="empty-state fade-up">
            <i class="fa fa-bell-slash"></i>
            <h4 style="color: #4B5563; margin-bottom: 8px;">No Notices Available</h4>
            <p style="color: #9CA3AF;">There are no announcements at the moment. Check back later for updates.</p>
            <a href="index.php" class="btn-enroll mt-3" style="display: inline-flex; background: #2563EB;">
                <i class="fa fa-home me-1"></i> Return to Dashboard
            </a>
        </div>
    <?php endif; ?>

</div>

<script>
// Fade up animation
document.addEventListener('DOMContentLoaded', function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
});
</script>

