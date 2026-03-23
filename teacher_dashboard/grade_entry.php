<?php
// ============================================================
//  Adaxy Academy · Enter Grades
//  Teacher grade entry and management system
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

// ── Get Teacher's Classes ────────────────────────────────────
$classes = $conn->query("
    SELECT DISTINCT 
        c.class_id,
        c.class_name,
        c.form_level,
        c.programme
    FROM timetable t
    INNER JOIN classes c ON c.class_id = t.class_id
    WHERE t.teacher_id = $teacher_id
    ORDER BY c.form_level ASC, c.class_name ASC
")->fetch_all(MYSQLI_ASSOC);

// ── Get Teacher's Subjects ───────────────────────────────────
$subjects = $conn->query("
    SELECT DISTINCT 
        sub.subject_id,
        sub.subject_name,
        sub.subject_code
    FROM timetable t
    INNER JOIN subjects sub ON sub.subject_id = t.subject_id
    WHERE t.teacher_id = $teacher_id
    ORDER BY sub.subject_name ASC
")->fetch_all(MYSQLI_ASSOC);

// ── Handle Grade Submission ──────────────────────────────────
$success = '';
$error = '';
$selected_class = isset($_GET['class']) ? (int)$_GET['class'] : 0;
$selected_subject = isset($_GET['subject']) ? (int)$_GET['subject'] : 0;
$selected_term = isset($_GET['term']) ? (int)$_GET['term'] : (date('n') <= 3 ? 1 : (date('n') <= 6 ? 2 : 3));
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Fetch students for selected class
$students = [];
if ($selected_class > 0) {
    $stmt = $conn->prepare("
        SELECT student_id, roll_number, first_name, last_name
        FROM students
        WHERE class_id = ?
        ORDER BY roll_number ASC
    ");
    $stmt->bind_param("i", $selected_class);
    $stmt->execute();
    $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Fetch existing grades for the selected class/subject/term
$existing_grades = [];
if ($selected_class > 0 && $selected_subject > 0) {
    $stmt = $conn->prepare("
        SELECT student_id, ca1_score, ca2_score, ca3_score, exam_score, total_score, letter_grade, remarks
        FROM grades
        WHERE class_id = ? 
          AND subject_id = ?
          AND term = ?
          AND academic_year = ?
          AND grade_type = 'end_of_term'
    ");
    $stmt->bind_param("iiii", $selected_class, $selected_subject, $selected_term, $selected_year);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    foreach ($existing as $grade) {
        $existing_grades[$grade['student_id']] = $grade;
    }
    $stmt->close();
}

// Handle grade submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_grades'])) {
    $class_id = (int)$_POST['class_id'];
    $subject_id = (int)$_POST['subject_id'];
    $term = (int)$_POST['term'];
    $academic_year = (int)$_POST['academic_year'];
    $grade_type = 'end_of_term';
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($students as $student) {
        $student_id = $student['student_id'];
        $ca1 = isset($_POST["ca1_$student_id"]) ? (float)$_POST["ca1_$student_id"] : 0;
        $ca2 = isset($_POST["ca2_$student_id"]) ? (float)$_POST["ca2_$student_id"] : 0;
        $ca3 = isset($_POST["ca3_$student_id"]) ? (float)$_POST["ca3_$student_id"] : 0;
        $exam = isset($_POST["exam_$student_id"]) ? (float)$_POST["exam_$student_id"] : 0;
        $remarks = isset($_POST["remarks_$student_id"]) ? trim($_POST["remarks_$student_id"]) : '';
        
        // Calculate total score (CA average 40% + Exam 60% for end of term)
        $ca_average = ($ca1 + $ca2 + $ca3) / 3;
        $total_score = ($ca_average * 0.4) + ($exam * 0.6);
        $total_score = round($total_score, 2);
        
        // Determine letter grade
        if ($total_score >= 80) {
            $letter = 'A';
        } elseif ($total_score >= 70) {
            $letter = 'B';
        } elseif ($total_score >= 60) {
            $letter = 'C';
        } elseif ($total_score >= 50) {
            $letter = 'D';
        } else {
            $letter = 'F';
        }
        
        // Check if grade exists
        if (isset($existing_grades[$student_id])) {
            // Update existing grade
            $stmt = $conn->prepare("
                UPDATE grades 
                SET ca1_score = ?, ca2_score = ?, ca3_score = ?, exam_score = ?,
                    total_score = ?, letter_grade = ?, remarks = ?, updated_at = NOW()
                WHERE student_id = ? AND subject_id = ? AND term = ? AND academic_year = ? AND grade_type = ?
            ");
            $stmt->bind_param("ddddsssiiiss", $ca1, $ca2, $ca3, $exam, $total_score, $letter, $remarks, $student_id, $subject_id, $term, $academic_year, $grade_type);
        } else {
            // Insert new grade
            $stmt = $conn->prepare("
                INSERT INTO grades (student_id, subject_id, class_id, teacher_id, grade_type,
                    ca1_score, ca2_score, ca3_score, exam_score, total_score, letter_grade, 
                    term, academic_year, remarks)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iiiiisddddssis", $student_id, $subject_id, $class_id, $teacher_id, $grade_type,
                $ca1, $ca2, $ca3, $exam, $total_score, $letter, $term, $academic_year, $remarks);
        }
        
        if ($stmt->execute()) {
            $success_count++;
        } else {
            $error_count++;
        }
        $stmt->close();
    }
    
    if ($success_count > 0) {
        $success = "$success_count grade(s) saved successfully!";
        if ($error_count > 0) {
            $error = "$error_count student(s) had errors.";
        }
        // Refresh existing grades
        $existing_grades = [];
        $stmt = $conn->prepare("
            SELECT student_id, ca1_score, ca2_score, ca3_score, exam_score, total_score, letter_grade, remarks
            FROM grades
            WHERE class_id = ? AND subject_id = ? AND term = ? AND academic_year = ? AND grade_type = ?
        ");
        $stmt->bind_param("iiiss", $class_id, $subject_id, $term, $academic_year, $grade_type);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        foreach ($existing as $grade) {
            $existing_grades[$grade['student_id']] = $grade;
        }
        $stmt->close();
    } else {
        $error = "Failed to save grades. Please try again.";
    }
}

// Get academic years for filter
$years = [];
for ($y = date('Y'); $y >= 2020; $y--) {
    $years[] = $y;
}

$page_title = 'Enter Grades';
include 'includes/teacher_header.php';
?>

<style>
    .grade-form-container {
        background: white;
        border-radius: 24px;
        border: 1px solid #E5E7EB;
        overflow: hidden;
        margin-top: 24px;
    }
    
    .grade-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .grade-table th {
        background: #F9FAFB;
        padding: 14px 16px;
        font-size: 12px;
        font-weight: 600;
        color: #6B7280;
        text-transform: uppercase;
        border-bottom: 1px solid #E5E7EB;
        text-align: left;
    }
    
    .grade-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #F0F2F5;
        vertical-align: middle;
    }
    
    .grade-table tr:hover td {
        background: #FAFDFF;
    }
    
    .score-input {
        width: 70px;
        padding: 8px 10px;
        border: 1px solid #E5E7EB;
        border-radius: 10px;
        font-size: 13px;
        text-align: center;
    }
    
    .score-input:focus {
        border-color: #2563EB;
        outline: none;
        box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    }
    
    .remarks-input {
        width: 150px;
        padding: 8px 10px;
        border: 1px solid #E5E7EB;
        border-radius: 10px;
        font-size: 12px;
    }
    
    .filter-select {
        padding: 10px 16px;
        border: 1px solid #E5E7EB;
        border-radius: 40px;
        font-size: 13px;
        background: white;
        width: 100%;
    }
    
    .total-cell {
        font-weight: 700;
        color: #2563EB;
    }
    
    .grade-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .grade-a { background: #DCFCE7; color: #15803D; }
    .grade-b { background: #DBEAFE; color: #1E40AF; }
    .grade-c { background: #FEF9C3; color: #854D0E; }
    .grade-d { background: #FFEDD5; color: #C2410C; }
    .grade-f { background: #FEE2E2; color: #B91C1C; }
    
    .btn-save {
        background: #2563EB;
        color: white;
        padding: 12px 28px;
        border-radius: 40px;
        font-weight: 600;
        border: none;
        transition: all 0.2s;
        cursor: pointer;
    }
    
    .btn-save:hover {
        background: #1D4ED8;
        transform: translateY(-1px);
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 24px;
        background: white;
        border-radius: 24px;
        border: 1px solid #E5E7EB;
        color: #9CA3AF;
    }
    
    .empty-state i {
        font-size: 56px;
        margin-bottom: 16px;
        opacity: 0.5;
    }
    
    .filter-bar {
        background: white;
        border-radius: 20px;
        padding: 20px 24px;
        margin-bottom: 24px;
        border: 1px solid #E5E7EB;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        align-items: flex-end;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
    }
    
    .filter-group label {
        font-size: 11px;
        font-weight: 600;
        color: #6B7280;
        text-transform: uppercase;
        margin-bottom: 6px;
    }
    
    .btn-load {
        background: #2563EB;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 40px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-load:hover {
        background: #1D4ED8;
    }
    
    @media (max-width: 768px) {
        .grade-table {
            display: block;
            overflow-x: auto;
        }
        .score-input {
            width: 60px;
        }
        .remarks-input {
            width: 120px;
        }
        .filter-bar {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="grades-container" style="max-width: 1400px; margin: 0 auto;">

    <!-- Header -->
    <div class="welcome-section fade-up">
        <div class="welcome-content">
            <div>
                <div class="greeting-badge">
                    <i class="fas fa-pen"></i> Grade Entry
                </div>
                <h1>Enter Grades</h1>
                <p>Record and manage student assessment results</p>
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
    <form method="GET" class="filter-bar fade-up">
        <div class="filter-group">
            <label><i class="fas fa-chalkboard"></i> Select Class</label>
            <select name="class" class="filter-select" required>
                <option value="">-- Select Class --</option>
                <?php foreach ($classes as $class): ?>
                <option value="<?= $class['class_id'] ?>" <?= $selected_class == $class['class_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($class['class_name']) ?> (<?= $class['programme'] ?> Form <?= $class['form_level'] ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label><i class="fas fa-book"></i> Select Subject</label>
            <select name="subject" class="filter-select" required>
                <option value="">-- Select Subject --</option>
                <?php foreach ($subjects as $subject): ?>
                <option value="<?= $subject['subject_id'] ?>" <?= $selected_subject == $subject['subject_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($subject['subject_code']) ?> - <?= htmlspecialchars($subject['subject_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label><i class="fas fa-calendar"></i> Term</label>
            <select name="term" class="filter-select">
                <option value="1" <?= $selected_term == 1 ? 'selected' : '' ?>>Term 1</option>
                <option value="2" <?= $selected_term == 2 ? 'selected' : '' ?>>Term 2</option>
                <option value="3" <?= $selected_term == 3 ? 'selected' : '' ?>>Term 3</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label><i class="fas fa-calendar-alt"></i> Academic Year</label>
            <select name="year" class="filter-select">
                <?php foreach ($years as $year): ?>
                <option value="<?= $year ?>" <?= $selected_year == $year ? 'selected' : '' ?>><?= $year ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label>&nbsp;</label>
            <button type="submit" class="btn-load">
                <i class="fas fa-search"></i> Load Students
            </button>
        </div>
    </form>

    <!-- Grade Entry Form -->
    <?php if ($selected_class > 0 && $selected_subject > 0 && !empty($students)): ?>
    <div class="grade-form-container fade-up">
        <div class="card-header" style="background: #F8FAFE; padding: 20px 24px; border-bottom: 1px solid #E5E7EB;">
            <div class="header-title">
                <i class="fas fa-graduation-cap"></i>
                <h3>Grade Entry: <?php 
                    $subject_name = '';
                    foreach ($subjects as $sub) {
                        if ($sub['subject_id'] == $selected_subject) {
                            $subject_name = $sub['subject_name'];
                            break;
                        }
                    }
                    echo htmlspecialchars($subject_name);
                ?></h3>
            </div>
            <div>
                <span style="background: #10B981; color: white; padding: 4px 12px; border-radius: 40px; font-size: 12px;">
                    Term <?= $selected_term ?> · <?= $selected_year ?>
                </span>
            </div>
        </div>
        
        <?php if ($success): ?>
        <div style="margin: 20px 24px 0;">
            <div style="background: #DCFCE7; border: 1px solid #86EFAC; color: #15803D; padding: 14px 18px; border-radius: 12px;">
                <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($success) ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div style="margin: 20px 24px 0;">
            <div style="background: #FEE2E2; border: 1px solid #FECACA; color: #B91C1C; padding: 14px 18px; border-radius: 12px;">
                <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
            </div>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="class_id" value="<?= $selected_class ?>">
            <input type="hidden" name="subject_id" value="<?= $selected_subject ?>">
            <input type="hidden" name="term" value="<?= $selected_term ?>">
            <input type="hidden" name="academic_year" value="<?= $selected_year ?>">
            <input type="hidden" name="save_grades" value="1">
            
            <div style="overflow-x: auto;">
                <table class="grade-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Roll Number</th>
                            <th>Student Name</th>
                            <th>CA1<br><small>(20%)</small></th>
                            <th>CA2<br><small>(20%)</small></th>
                            <th>CA3<br><small>(20%)</small></th>
                            <th>Exam<br><small>(40%)</small></th>
                            <th>Total<br><small>(100%)</small></th>
                            <th>Grade</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $index => $student):
                            $student_id = $student['student_id'];
                            $grade = isset($existing_grades[$student_id]) ? $existing_grades[$student_id] : null;
                            $ca1 = $grade ? $grade['ca1_score'] : '';
                            $ca2 = $grade ? $grade['ca2_score'] : '';
                            $ca3 = $grade ? $grade['ca3_score'] : '';
                            $exam = $grade ? $grade['exam_score'] : '';
                            $total = $grade ? $grade['total_score'] : '';
                            $letter = $grade ? $grade['letter_grade'] : '';
                            $remarks = $grade ? $grade['remarks'] : '';
                        ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($student['roll_number']) ?></td>
                            <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                            <td>
                                <input type="number" name="ca1_<?= $student_id ?>" class="score-input" 
                                       value="<?= $ca1 ?>" step="0.5" min="0" max="100">
                            </td>
                            <td>
                                <input type="number" name="ca2_<?= $student_id ?>" class="score-input" 
                                       value="<?= $ca2 ?>" step="0.5" min="0" max="100">
                            </td>
                            <td>
                                <input type="number" name="ca3_<?= $student_id ?>" class="score-input" 
                                       value="<?= $ca3 ?>" step="0.5" min="0" max="100">
                            </td>
                            <td>
                                <input type="number" name="exam_<?= $student_id ?>" class="score-input" 
                                       value="<?= $exam ?>" step="0.5" min="0" max="100">
                            </td>
                            <td class="total-cell">
                                <?= $total ? $total . '%' : '—' ?>
                            </td>
                            <td>
                                <?php if ($letter): ?>
                                <span class="grade-badge grade-<?= strtolower($letter) ?>"><?= $letter ?></span>
                                <?php else: ?>
                                —
                                <?php endif; ?>
                            </td>
                            <td>
                                <input type="text" name="remarks_<?= $student_id ?>" class="remarks-input" 
                                       value="<?= htmlspecialchars($remarks) ?>" placeholder="Optional">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div style="padding: 20px 24px; background: #F9FAFB; border-top: 1px solid #E5E7EB; text-align: right;">
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Save All Grades
                </button>
            </div>
        </form>
    </div>
    
    <?php elseif ($selected_class > 0 && $selected_subject > 0 && empty($students)): ?>
    <div class="empty-state fade-up">
        <i class="fas fa-users-slash"></i>
        <h4>No Students Found</h4>
        <p>No students are enrolled in this class.</p>
    </div>
    
    <?php elseif ($selected_class > 0 && $selected_subject == 0): ?>
    <div class="empty-state fade-up">
        <i class="fas fa-book"></i>
        <h4>Select a Subject</h4>
        <p>Please select a subject to enter grades.</p>
    </div>
    
    <?php elseif ($selected_class == 0): ?>
    <div class="empty-state fade-up">
        <i class="fas fa-chalkboard"></i>
        <h4>Select a Class</h4>
        <p>Choose a class from the dropdown above to start entering grades.</p>
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

