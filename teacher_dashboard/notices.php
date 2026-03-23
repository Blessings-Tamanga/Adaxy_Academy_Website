<?php
// ============================================================
//  Adaxy Academy · Teacher Notifications
//  Official announcements and notices for teachers
// ============================================================

session_start();
include('../config/db_connect.php');

// ── Authentication Guard ─────────────────────────────────────
if (empty($_SESSION['tlogin'])) {
    header('Location: ../Auth/login.php?role=teacher');
    exit;
}

$username = $_SESSION['tlogin'];

// ── Fetch Teacher Information ────────────────────────────────
$stmt = $conn->prepare("
    SELECT t.*, d.department_name
    FROM teachers t
    LEFT JOIN departments d ON d.department_id = t.department_id
    WHERE t.username = ?
    LIMIT 1
");
$stmt->bind_param("s", $username);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$teacher) {
    session_destroy();
    header('Location: ../Auth/login.php?role=teacher');
    exit;
}

$teacher_id = (int)$teacher['teacher_id'];
$full_name = $teacher['first_name'] . ' ' . $teacher['last_name'];
$first_name = $teacher['first_name'];
$department = $teacher['department_name'] ?? 'Not Assigned';
$initials = strtoupper(substr($teacher['first_name'],0,1) . substr($teacher['last_name'],0,1));

// ── Fetch Notices for Teachers ───────────────────────────────
$notices = $conn->query("
    SELECT * FROM notices
    WHERE is_published = 1
      AND (audience = 'teachers' OR audience = 'all')
    ORDER BY created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// ── Audience colors for badges ──────────────────────────────
$audience_colors = [
    'all'      => ['bg' => '#DBEAFE', 'color' => '#1E40AF', 'label' => 'All Staff'],
    'teachers' => ['bg' => '#DCFCE7', 'color' => '#15803D', 'label' => 'Teachers'],
    'students' => ['bg' => '#EDE9FE', 'color' => '#6D28D9', 'label' => 'Students'],
    'parents'  => ['bg' => '#FFEDD5', 'color' => '#C2410C', 'label' => 'Parents'],
];

$conn->close();
$page_title = 'Notifications';
include 'includes/teacher_header.php';
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
    
    .filter-bar {
        background: white;
        border-radius: 20px;
        padding: 16px 24px;
        margin-bottom: 24px;
        border: 1px solid #E5E7EB;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
    }
    
    .filter-badge {
        background: #F3F4F6;
        border-radius: 40px;
        padding: 6px 18px;
        font-size: 13px;
        font-weight: 500;
        color: #4B5563;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .filter-badge:hover {
        background: #E5E7EB;
        color: #1F2937;
        text-decoration: none;
    }
    
    .filter-badge.active {
        background: #2563EB;
        color: white;
    }
    
    .search-box {
        display: flex;
        align-items: center;
        background: #F3F4F6;
        border-radius: 40px;
        padding: 6px 16px;
    }
    
    .search-box input {
        border: none;
        background: transparent;
        padding: 8px;
        font-size: 13px;
        outline: none;
        width: 200px;
    }
    
    .search-box i {
        color: #9CA3AF;
    }
    
    .notice-count {
        background: #EFF6FF;
        color: #2563EB;
        padding: 4px 16px;
        border-radius: 40px;
        font-size: 13px;
        font-weight: 500;
    }
    
    @media (max-width: 768px) {
        .notice-card {
            padding: 20px;
        }
        .notice-title {
            font-size: 16px;
        }
        .filter-bar {
            flex-direction: column;
            align-items: stretch;
        }
        .search-box input {
            width: 100%;
        }
        .filter-badge {
            justify-content: center;
        }
    }
</style>

<div class="notices-container" style="padding: 0 20px 40px;">

    <!-- Header -->
    <div class="welcome-section fade-up">
        <div class="welcome-content">
            <div>
                <div class="greeting-badge">
                    <i class="fas fa-bell"></i> Announcements
                </div>
                <h1>Notifications</h1>
                <p>Official announcements from school administration</p>
            </div>
            <div class="teacher-avatar">
                <div class="avatar-circle">
                    <span><?= $initials ?></span>
                </div>
                <div class="avatar-badge">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($department) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar fade-up">
        <div class="d-flex flex-wrap gap-2">
            <a href="?filter=all" class="filter-badge <?= !isset($_GET['filter']) || $_GET['filter'] == 'all' ? 'active' : '' ?>">
                <i class="fas fa-bell"></i> All
            </a>
            <a href="?filter=teachers" class="filter-badge <?= isset($_GET['filter']) && $_GET['filter'] == 'teachers' ? 'active' : '' ?>">
                <i class="fas fa-chalkboard-user"></i> For Teachers
            </a>
        </div>
        
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search notices..." onkeyup="searchNotices()">
        </div>
    </div>

    <!-- Notice Count -->
    <div class="text-center mb-4 fade-up">
        <span class="notice-count">
            <i class="fas fa-file-alt"></i> <?= count($notices) ?> notice<?= count($notices) !== 1 ? 's' : '' ?> available
        </span>
    </div>

    <!-- Notices List -->
    <?php if ($notices): ?>
        <div id="noticesList">
            <?php 
            $display_count = 0;
            $filter = $_GET['filter'] ?? 'all';
            
            foreach ($notices as $i => $notice):
                // Apply filter if needed
                if ($filter == 'teachers' && $notice['audience'] != 'teachers' && $notice['audience'] != 'all') {
                    continue;
                }
                $display_count++;
                $aud = $audience_colors[$notice['audience']] ?? $audience_colors['all'];
            ?>
            <div class="notice-card fade-up notice-item" style="transition-delay: <?= $i * 0.05 ?>s" 
                 data-title="<?= strtolower(htmlspecialchars($notice['title'])) ?>"
                 data-content="<?= strtolower(htmlspecialchars($notice['content'])) ?>">
                
                <!-- Title and Badge -->
                <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 12px;">
                    <h3 class="notice-title"><?= htmlspecialchars($notice['title']) ?></h3>
                    <span class="audience-badge" style="background: <?= $aud['bg'] ?>; color: <?= $aud['color'] ?>;">
                        <i class="fas fa-<?= $notice['audience'] == 'teachers' ? 'chalkboard-user' : ($notice['audience'] == 'students' ? 'user-graduate' : 'users') ?>"></i>
                        <?= $aud['label'] ?>
                    </span>
                </div>
                
                <!-- Meta Info -->
                <div class="notice-meta">
                    <span>
                        <i class="fas fa-user-circle" style="color: #2563EB;"></i> 
                        <?= htmlspecialchars($notice['posted_by']) ?>
                    </span>
                    <span>
                        <i class="fas fa-calendar-alt" style="color: #2563EB;"></i> 
                        <?= date('F j, Y', strtotime($notice['created_at'])) ?>
                    </span>
                    <span>
                        <i class="fas fa-clock" style="color: #2563EB;"></i> 
                        <?= time_ago($notice['created_at']) ?>
                    </span>
                </div>
                
                <!-- Content -->
                <div class="notice-content">
                    <?= nl2br(htmlspecialchars($notice['content'])) ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if ($display_count == 0): ?>
            <div class="empty-state fade-up">
                <i class="fas fa-bell-slash"></i>
                <h4 style="color: #4B5563; margin-bottom: 8px;">No Notices Found</h4>
                <p style="color: #9CA3AF;">No notices match the selected filter.</p>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer Note -->
        <div class="text-center mt-4 fade-up" style="transition-delay: 0.2s;">
            <p style="font-size: 12px; color: #9CA3AF;">
                <i class="fas fa-envelope me-1"></i> For urgent matters, contact the administration office directly.
            </p>
        </div>
        
    <?php else: ?>
        <div class="empty-state fade-up">
            <i class="fas fa-bell-slash"></i>
            <h4 style="color: #4B5563; margin-bottom: 8px;">No Notices Available</h4>
            <p style="color: #9CA3AF;">There are no announcements at the moment. Check back later for updates.</p>
            <a href="index.php" class="btn-sm mt-3" style="display: inline-flex; background: #2563EB; color: white;">
                <i class="fas fa-home me-1"></i> Return to Dashboard
            </a>
        </div>
    <?php endif; ?>

</div>

<script>
// Search functionality
function searchNotices() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const notices = document.querySelectorAll('.notice-item');
    let visibleCount = 0;
    
    notices.forEach(notice => {
        const title = notice.getAttribute('data-title') || '';
        const content = notice.getAttribute('data-content') || '';
        
        if (title.includes(searchTerm) || content.includes(searchTerm) || searchTerm === '') {
            notice.style.display = 'block';
            visibleCount++;
        } else {
            notice.style.display = 'none';
        }
    });
    
    // Show/hide empty state message
    const emptyMessage = document.getElementById('noResultsMessage');
    
    if (visibleCount === 0 && searchTerm !== '') {
        if (!emptyMessage) {
            const container = document.getElementById('noticesList');
            const msg = document.createElement('div');
            msg.id = 'noResultsMessage';
            msg.className = 'empty-state';
            msg.style.marginTop = '20px';
            msg.innerHTML = `
                <i class="fas fa-search"></i>
                <h4 style="color: #4B5563;">No Results Found</h4>
                <p style="color: #9CA3AF;">No notices matching "${searchTerm}"</p>
            `;
            container.appendChild(msg);
        }
    } else {
        const emptyMessage = document.getElementById('noResultsMessage');
        if (emptyMessage) emptyMessage.remove();
    }
}

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
    
    // Add search input event listener
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', searchNotices);
    }
});
</script>

