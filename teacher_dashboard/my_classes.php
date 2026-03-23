<?php
// ============================================================
//  Adaxy Academy · My Classes
//  Teacher class management and student overview
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

// ── Get Teacher's Classes with Details ───────────────────────
$classes = $conn->query("
    SELECT DISTINCT 
        c.class_id,
        c.class_name,
        c.form_level,
        c.programme,
        c.stream,
        COUNT(DISTINCT s.student_id) as student_count,
        COUNT(DISTINCT CASE WHEN s.is_active = 1 THEN s.student_id END) as active_students,
        GROUP_CONCAT(DISTINCT sub.subject_name SEPARATOR ', ') as subjects_taught
    FROM timetable t
    INNER JOIN classes c ON c.class_id = t.class_id
    INNER JOIN subjects sub ON sub.subject_id = t.subject_id
    LEFT JOIN students s ON s.class_id = c.class_id
    WHERE t.teacher_id = $teacher_id
    GROUP BY c.class_id
    ORDER BY c.form_level ASC, c.class_name ASC
")->fetch_all(MYSQLI_ASSOC);

$total_classes = count($classes);
$total_students = array_sum(array_column($classes, 'student_count'));

// ── Get Selected Class Details for Student List ──────────────
$selected_class = isset($_GET['class']) ? (int)$_GET['class'] : 0;
$class_details = null;
$students = [];

if ($selected_class > 0) {
    // Get class details
    $stmt = $conn->prepare("
        SELECT c.*, 
               COUNT(DISTINCT s.student_id) as total_students,
               COUNT(DISTINCT CASE WHEN s.gender = 'Male' THEN s.student_id END) as male_students,
               COUNT(DISTINCT CASE WHEN s.gender = 'Female' THEN s.student_id END) as female_students
        FROM classes c
        LEFT JOIN students s ON s.class_id = c.class_id
        WHERE c.class_id = ?
        GROUP BY c.class_id
    ");
    $stmt->bind_param("i", $selected_class);
    $stmt->execute();
    $class_details = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Get students in this class
    $stmt = $conn->prepare("
        SELECT student_id, roll_number, first_name, last_name, gender, 
               email, phone, is_active
        FROM students
        WHERE class_id = ?
        ORDER BY roll_number ASC
    ");
    $stmt->bind_param("i", $selected_class);
    $stmt->execute();
    $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// ── Get Subjects Taught in Selected Class ────────────────────
$class_subjects = [];
if ($selected_class > 0) {
    $stmt = $conn->prepare("
        SELECT DISTINCT sub.subject_id, sub.subject_name, sub.subject_code,
               COUNT(DISTINCT t.period_no) as periods_per_week
        FROM timetable t
        INNER JOIN subjects sub ON sub.subject_id = t.subject_id
        WHERE t.class_id = ? AND t.teacher_id = ?
        GROUP BY sub.subject_id
        ORDER BY sub.subject_name ASC
    ");
    $stmt->bind_param("ii", $selected_class, $teacher_id);
    $stmt->execute();
    $class_subjects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();
$page_title = 'My Classes';
include 'includes/teacher_header.php';
?>

<style>
    .class-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #E5E7EB;
        overflow: hidden;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .class-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.1);
        border-color: #2563EB;
    }
    
    .class-card.selected {
        border: 2px solid #2563EB;
        background: #F8FAFE;
    }
    
    .class-header {
        background: linear-gradient(135deg, #F8FAFE, #FFFFFF);
        padding: 20px;
        border-bottom: 1px solid #E5E7EB;
    }
    
    .class-name {
        font-size: 20px;
        font-weight: 700;
        color: #0F172A;
        margin-bottom: 4px;
    }
    
    .class-meta {
        font-size: 12px;
        color: #6B7280;
    }
    
    .class-stats {
        display: flex;
        gap: 16px;
        padding: 16px 20px;
        border-bottom: 1px solid #F0F2F5;
    }
    
    .stat-badge {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #475569;
    }
    
    .stat-badge i {
        color: #2563EB;
    }
    
    .subjects-list {
        padding: 16px 20px;
    }
    
    .subject-tag {
        display: inline-block;
        background: #EFF6FF;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        color: #2563EB;
        margin: 0 6px 6px 0;
    }
    
    /* Student Table */
    .student-table-container {
        background: white;
        border-radius: 24px;
        border: 1px solid #E5E7EB;
        overflow: hidden;
        margin-top: 24px;
    }
    
    .student-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .student-table th {
        background: #F9FAFB;
        padding: 14px 16px;
        font-size: 12px;
        font-weight: 600;
        color: #6B7280;
        text-transform: uppercase;
        border-bottom: 1px solid #E5E7EB;
        text-align: left;
    }
    
    .student-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #F0F2F5;
    }
    
    .student-table tr:hover td {
        background: #FAFDFF;
    }
    
    .status-active {
        display: inline-block;
        padding: 4px 10px;
        background: #DCFCE7;
        color: #15803D;
        border-radius: 30px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .status-inactive {
        display: inline-block;
        padding: 4px 10px;
        background: #FEE2E2;
        color: #B91C1C;
        border-radius: 30px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .action-buttons {
        display: flex;
        gap: 8px;
    }
    
    .btn-icon {
        padding: 6px 12px;
        background: #F3F4F6;
        border-radius: 30px;
        font-size: 11px;
        text-decoration: none;
        color: #4B5563;
        transition: all 0.2s;
    }
    
    .btn-icon:hover {
        background: #2563EB;
        color: white;
    }
    
    .class-summary {
        background: linear-gradient(135deg, #2563EB, #1E40AF);
        border-radius: 20px;
        padding: 20px;
        color: white;
        margin-bottom: 24px;
    }
    
    .summary-stat {
        text-align: center;
        padding: 12px;
        background: rgba(255,255,255,0.1);
        border-radius: 16px;
    }
    
    .summary-stat-value {
        font-size: 28px;
        font-weight: 700;
    }
    
    .summary-stat-label {
        font-size: 11px;
        opacity: 0.8;
    }
    
    @media (max-width: 768px) {
        .student-table {
            display: block;
            overflow-x: auto;
        }
        .class-stats {
            flex-wrap: wrap;
        }
        .summary-stat-value {
            font-size: 20px;
        }
    }
</style>

<div class="classes-container" style="max-width: 1400px; margin: 0 auto;">

    <!-- Header -->
    <div class="welcome-section fade-up">
        <div class="welcome-content">
            <div>
                <div class="greeting-badge">
                    <i class="fas fa-users"></i> Class Management
                </div>
                <h1>My Classes</h1>
                <p>View and manage your assigned classes and students</p>
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

    <!-- Stats Overview -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card fade-up">
                <div class="stat-icon"><i class="fas fa-chalkboard"></i></div>
                <div class="stat-info">
                    <h3><?= $total_classes ?></h3>
                    <p>Total Classes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 fade-up" style="transition-delay: 0.05s">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <h3><?= $total_students ?></h3>
                    <p>Total Students</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 fade-up" style="transition-delay: 0.1s">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-book"></i></div>
                <div class="stat-info">
                    <h3><?= count($class_subjects) ?></h3>
                    <p>Subjects Taught</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 fade-up" style="transition-delay: 0.15s">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                <div class="stat-info">
                    <h3><?= $total_classes * 5 ?></h3>
                    <p>Weekly Periods</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Classes Grid -->
    <div class="row g-4 mb-5">
        <?php if ($classes): ?>
            <?php foreach ($classes as $class): 
                $is_selected = ($selected_class == $class['class_id']);
            ?>
            <div class="col-md-6 col-lg-4 fade-up">
                <div class="class-card <?= $is_selected ? 'selected' : '' ?>" onclick="window.location.href='?class=<?= $class['class_id'] ?>'">
                    <div class="class-header">
                        <div class="class-name"><?= htmlspecialchars($class['class_name']) ?></div>
                        <div class="class-meta">
                            <?= $class['programme'] ?> Form <?= $class['form_level'] ?> 
                            <?= $class['stream'] ? '· Stream ' . htmlspecialchars($class['stream']) : '' ?>
                        </div>
                    </div>
                    <div class="class-stats">
                        <div class="stat-badge">
                            <i class="fas fa-user-graduate"></i>
                            <span><?= $class['student_count'] ?> Students</span>
                        </div>
                        <div class="stat-badge">
                            <i class="fas fa-user-check"></i>
                            <span><?= $class['active_students'] ?> Active</span>
                        </div>
                    </div>
                    <div class="subjects-list">
                        <small class="text-muted d-block mb-2">Subjects you teach:</small>
                        <?php 
                        $subjects_list = explode(', ', $class['subjects_taught']);
                        foreach (array_slice($subjects_list, 0, 3) as $subject): 
                        ?>
                        <span class="subject-tag"><?= htmlspecialchars($subject) ?></span>
                        <?php endforeach; ?>
                        <?php if (count($subjects_list) > 3): ?>
                        <span class="subject-tag">+<?= count($subjects_list) - 3 ?> more</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="empty-state fade-up">
                    <i class="fas fa-chalkboard"></i>
                    <h4>No Classes Assigned</h4>
                    <p>You haven't been assigned to any classes yet. Please contact the academic office.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Selected Class Details -->
    <?php if ($selected_class > 0 && $class_details): ?>
    <div class="fade-up">
        <!-- Class Summary -->
        <div class="class-summary">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h3 style="color: white; margin: 0;"><?= htmlspecialchars($class_details['class_name']) ?></h3>
                    <p style="color: rgba(255,255,255,0.8); margin: 0;">
                        <?= $class_details['programme'] ?> Form <?= $class_details['form_level'] ?>
                        <?= $class_details['stream'] ? '· Stream ' . htmlspecialchars($class_details['stream']) : '' ?>
                    </p>
                </div>
                <a href="grades.php?class=<?= $selected_class ?>" class="btn-sm" style="background: white; color: #2563EB;">
                    <i class="fas fa-pen"></i> Enter Grades
                </a>
            </div>
            <div class="row g-3">
                <div class="col-md-3 col-6">
                    <div class="summary-stat">
                        <div class="summary-stat-value"><?= $class_details['total_students'] ?? 0 ?></div>
                        <div class="summary-stat-label">Total Students</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="summary-stat">
                        <div class="summary-stat-value"><?= $class_details['male_students'] ?? 0 ?></div>
                        <div class="summary-stat-label">Male</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="summary-stat">
                        <div class="summary-stat-value"><?= $class_details['female_students'] ?? 0 ?></div>
                        <div class="summary-stat-label">Female</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="summary-stat">
                        <div class="summary-stat-value"><?= count($class_subjects) ?></div>
                        <div class="summary-stat-label">Subjects You Teach</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Subjects You Teach in This Class -->
        <?php if ($class_subjects): ?>
        <div class="dashboard-card mb-4">
            <div class="card-header">
                <div class="header-title">
                    <i class="fas fa-book-open"></i>
                    <h3>Subjects You Teach in This Class</h3>
                </div>
            </div>
            <div style="padding: 16px 24px;">
                <div class="row g-3">
                    <?php foreach ($class_subjects as $subject): ?>
                    <div class="col-md-4 col-sm-6">
                        <div style="background: #F8FAFE; border-radius: 12px; padding: 12px 16px;">
                            <div style="font-weight: 600; color: #0F172A;"><?= htmlspecialchars($subject['subject_name']) ?></div>
                            <div style="font-size: 12px; color: #6B7280;">
                                <i class="fas fa-clock"></i> <?= $subject['periods_per_week'] ?> periods/week
                                <span class="subject-tag" style="margin-left: 8px;"><?= htmlspecialchars($subject['subject_code']) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Students List -->
        <div class="student-table-container">
            <div class="card-header" style="background: #F8FAFE;">
                <div class="header-title">
                    <i class="fas fa-users"></i>
                    <h3>Student Roster (<?= count($students) ?> students)</h3>
                </div>
                <div>
                    <button onclick="window.print()" class="btn-sm" style="background: #F3F4F6;">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
            
            <?php if ($students): ?>
            <div style="overflow-x: auto;">
                <table class="student-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Roll Number</th>
                            <th>Student Name</th>
                            <th>Gender</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $index => $student): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($student['roll_number']) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></strong>
                            </td>
                            <td>
                                <?php if ($student['gender'] == 'Male'): ?>
                                <span style="color: #2563EB;"><i class="fas fa-mars"></i> Male</span>
                                <?php else: ?>
                                <span style="color: #EC489A;"><i class="fas fa-venus"></i> Female</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 12px;">
                                <?php if ($student['email']): ?>
                                <div><i class="fas fa-envelope"></i> <?= htmlspecialchars($student['email']) ?></div>
                                <?php endif; ?>
                                <?php if ($student['phone']): ?>
                                <div><i class="fas fa-phone"></i> <?= htmlspecialchars($student['phone']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($student['is_active']): ?>
                                <span class="status-active"><i class="fas fa-check-circle"></i> Active</span>
                                <?php else: ?>
                                <span class="status-inactive"><i class="fas fa-ban"></i> Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="student-profile.php?id=<?= $student['student_id'] ?>" class="btn-icon">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="grades.php?class=<?= $selected_class ?>&student=<?= $student['student_id'] ?>" class="btn-icon">
                                        <i class="fas fa-chart-line"></i> Grades
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state small">
                <i class="fas fa-user-graduate"></i>
                <p>No students enrolled in this class yet.</p>
            </div>
            <?php endif; ?>
        </div>
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

