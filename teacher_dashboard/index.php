<?php
// ============================================================
//  Adaxy Academy · Teacher Dashboard
// ============================================================

session_start();
include('../config/db_connect.php');

// ── Auth guard ───────────────────────────────────────────────
if (empty($_SESSION['tlogin'])) {
    header('Location: ../Auth/login.php?role=teacher');
    exit;
}

$username = $_SESSION['tlogin'];

// ── Fetch teacher info ───────────────────────────────────────
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

// ── Get teacher's classes ────────────────────────────────────
$stmt = $conn->prepare("
    SELECT DISTINCT 
        c.class_id,
        c.class_name,
        c.form_level,
        c.programme,
        COUNT(DISTINCT s.student_id) as student_count
    FROM timetable t
    INNER JOIN classes c ON c.class_id = t.class_id
    LEFT JOIN students s ON s.class_id = c.class_id
    WHERE t.teacher_id = ?
    GROUP BY c.class_id
    ORDER BY c.form_level ASC
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$classes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total_classes = count($classes);
$total_students = array_sum(array_column($classes, 'student_count'));

// ── Get teacher's subjects ───────────────────────────────────
$stmt = $conn->prepare("
    SELECT DISTINCT sub.subject_name, sub.subject_code
    FROM timetable t
    INNER JOIN subjects sub ON sub.subject_id = t.subject_id
    WHERE t.teacher_id = ?
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$subjects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── Get pending grades ───────────────────────────────────────
$stmt = $conn->prepare("
    SELECT COUNT(*) as pending 
    FROM grades 
    WHERE teacher_id = ? 
      AND (exam_score IS NULL OR exam_score = 0)
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$pending_grades = $stmt->get_result()->fetch_assoc()['pending'];
$stmt->close();

// ── Get weekly hours ─────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT COUNT(*) as hours FROM timetable WHERE teacher_id = ?
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$weekly_hours = $stmt->get_result()->fetch_assoc()['hours'];
$stmt->close();

// ── Get notices for teachers ─────────────────────────────────
$notices = $conn->query("
    SELECT * FROM notices
    WHERE is_published = 1
      AND (audience = 'teachers' OR audience = 'all')
    ORDER BY created_at DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

$new_notices = count($notices);

$conn->close();
$page_title = 'Teacher Dashboard';
include 'includes/teacher_header.php';
?>

<!-- Welcome Section -->
<div class="welcome-section fade-up">
    <div class="welcome-content">
        <div>
            <div class="greeting-badge">
                <i class="fas fa-chalkboard-teacher"></i> Teacher Portal
            </div>
            <h1>Welcome back, <?= htmlspecialchars($first_name) ?>!</h1>
            <p><?= htmlspecialchars($department) ?> Department · <?= date('l, F j, Y') ?></p>
        </div>
        <div class="teacher-avatar">
            <div class="avatar-circle">
                <span><?= $initials ?></span>
            </div>
            <div class="avatar-badge">
                <i class="fas fa-check-circle"></i> Active
            </div>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card fade-up">
        <div class="stat-icon"><i class="fas fa-chalkboard"></i></div>
        <div class="stat-info">
            <h3><?= $total_classes ?></h3>
            <p>Classes</p>
        </div>
    </div>
    <div class="stat-card fade-up" style="transition-delay: 0.05s">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <h3><?= $total_students ?></h3>
            <p>Students</p>
        </div>
    </div>
    <div class="stat-card fade-up" style="transition-delay: 0.1s">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <h3><?= $weekly_hours ?></h3>
            <p>Weekly Hours</p>
        </div>
    </div>
    <div class="stat-card fade-up" style="transition-delay: 0.15s">
        <div class="stat-icon"><i class="fas fa-tasks"></i></div>
        <div class="stat-info">
            <h3><?= $pending_grades ?></h3>
            <p>Pending Grades</p>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="dashboard-grid">
    
    <!-- Left Column -->
    <div class="grid-col grid-col-2-3">
        
        <!-- My Classes Card -->
        <div class="dashboard-card fade-up">
            <div class="card-header">
                <div class="header-title">
                    <i class="fas fa-chalkboard"></i>
                    <h3>My Classes</h3>
                </div>
                <a href="#" class="card-link">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="classes-list">
                <?php if ($classes): ?>
                    <?php foreach ($classes as $class): ?>
                    <div class="class-item">
                        <div>
                            <div class="class-name"><?= htmlspecialchars($class['class_name']) ?></div>
                            <div class="class-meta"><?= $class['programme'] ?> Form <?= $class['form_level'] ?> · <?= $class['student_count'] ?> students</div>
                        </div>
                        <div class="class-actions">
                            <a href="#" class="btn-sm">View Class</a>
                            <a href="#" class="btn-sm btn-outline">Enter Grades</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state small">
                        <i class="fas fa-inbox"></i>
                        <p>No classes assigned yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="actions-row fade-up">
            <a href="#" class="action-card">
                <i class="fas fa-pen"></i>
                <h4>Enter Grades</h4>
                <p>Record test scores and assessments</p>
            </a>
            <a href="#" class="action-card">
                <i class="fas fa-calendar-check"></i>
                <h4>Take Attendance</h4>
                <p>Mark student attendance</p>
            </a>
            <a href="#" class="action-card">
                <i class="fas fa-file-alt"></i>
                <h4>Lesson Plans</h4>
                <p>Manage your weekly plans</p>
            </a>
        </div>
    </div>

    <!-- Right Column -->
    <div class="grid-col grid-col-1-3">
        
        <!-- Notifications -->
        <div class="dashboard-card fade-up">
            <div class="card-header">
                <div class="header-title">
                    <i class="fas fa-bell"></i>
                    <h3>Notifications</h3>
                </div>
                <?php if ($new_notices > 0): ?>
                <span class="badge-new"><?= $new_notices ?> new</span>
                <?php endif; ?>
            </div>
            <div class="notices-list">
                <?php if ($notices): ?>
                    <?php foreach ($notices as $notice): ?>
                    <div class="notice-item">
                        <div class="notice-icon"><i class="fas fa-bullhorn"></i></div>
                        <div class="notice-content">
                            <div class="notice-title"><?= htmlspecialchars($notice['title']) ?></div>
                            <div class="notice-time"><?= time_ago($notice['created_at']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state small">
                        <i class="fas fa-check-circle"></i>
                        <p>No new notifications</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Subjects Taught -->
        <div class="dashboard-card fade-up">
            <div class="card-header">
                <div class="header-title">
                    <i class="fas fa-book-open"></i>
                    <h3>Subjects Taught</h3>
                </div>
            </div>
            <div class="subjects-list">
                <?php if ($subjects): ?>
                    <?php foreach ($subjects as $subject): ?>
                    <span class="subject-tag">
                        <?= htmlspecialchars($subject['subject_code']) ?> - <?= htmlspecialchars($subject['subject_name']) ?>
                    </span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted small text-center py-3">No subjects assigned</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="dashboard-card fade-up">
            <div class="card-header">
                <div class="header-title">
                    <i class="fas fa-chart-line"></i>
                    <h3>Quick Stats</h3>
                </div>
            </div>
            <div class="stats-mini">
                <div class="stat-mini">
                    <span class="stat-mini-value"><?= $weekly_hours ?>/30</span>
                    <span class="stat-mini-label">Teaching Hours</span>
                </div>
                <div class="stat-mini">
                    <span class="stat-mini-value"><?= $pending_grades ?></span>
                    <span class="stat-mini-label">Pending Grades</span>
                </div>
                <div class="stat-mini">
                    <span class="stat-mini-value"><?= $total_students ?></span>
                    <span class="stat-mini-label">Total Students</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<div class="dashboard-footer">
    <div class="footer-content">
        <span><i class="fas fa-shield-alt"></i> Secure Teacher Portal</span>
        <span>Adaxy Academy · <?= date('Y') ?></span>
        <span><i class="fas fa-clock"></i> <?= date('l, F j, Y') ?></span>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
    const btn = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    if (btn) btn.addEventListener('click', () => sidebar.classList.toggle('show'));

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(en => {
            if (en.isIntersecting) { 
                en.target.classList.add('visible'); 
                observer.unobserve(en.target); 
            }
        });
    }, { threshold: 0.1 });
    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
})();
</script>

</body>
</html>