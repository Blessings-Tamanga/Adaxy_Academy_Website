<?php
// ============================================================
//  Adaxy Academy · Student Attendance
//  Take and manage student attendance records
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
$classes = [];
$result = $conn->query("
    SELECT DISTINCT 
        c.class_id,
        c.class_name,
        c.form_level,
        c.programme,
        c.stream
    FROM timetable t
    INNER JOIN classes c ON c.class_id = t.class_id
    WHERE t.teacher_id = $teacher_id
    ORDER BY c.form_level ASC, c.class_name ASC
");

if ($result) {
    $classes = $result->fetch_all(MYSQLI_ASSOC);
}

// ── Handle Form Submission ───────────────────────────────────
$success = '';
$error = '';
$selected_class = isset($_GET['class']) ? (int)$_GET['class'] : 0;
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$students = [];
$attendance_records = [];

if ($selected_class > 0) {
    // Get students in this class
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
    
    // Get existing attendance for this date
    $stmt = $conn->prepare("
        SELECT student_id, status, remarks
        FROM attendance
        WHERE class_id = ? AND date = ?
    ");
    $stmt->bind_param("is", $selected_class, $selected_date);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    foreach ($existing as $record) {
        $attendance_records[$record['student_id']] = $record;
    }
    $stmt->close();
}

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance'])) {
    $class_id = (int)$_POST['class_id'];
    $date = $_POST['date'];
    $success_count = 0;
    $error_count = 0;
    
    foreach ($students as $student) {
        $student_id = $student['student_id'];
        $status = $_POST["status_$student_id"] ?? 'absent';
        $remarks = $_POST["remarks_$student_id"] ?? '';
        
        // Check if attendance table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'attendance'");
        if ($table_check && $table_check->num_rows > 0) {
            // Check if attendance already exists
            $stmt = $conn->prepare("
                SELECT attendance_id FROM attendance
                WHERE class_id = ? AND student_id = ? AND date = ?
            ");
            $stmt->bind_param("iis", $class_id, $student_id, $date);
            $stmt->execute();
            $exists = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if ($exists) {
                // Update existing
                $stmt = $conn->prepare("
                    UPDATE attendance 
                    SET status = ?, remarks = ?, updated_at = NOW()
                    WHERE class_id = ? AND student_id = ? AND date = ?
                ");
                $stmt->bind_param("ssiis", $status, $remarks, $class_id, $student_id, $date);
            } else {
                // Insert new
                $stmt = $conn->prepare("
                    INSERT INTO attendance (class_id, student_id, date, status, remarks)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("iisss", $class_id, $student_id, $date, $status, $remarks);
            }
            
            if ($stmt->execute()) {
                $success_count++;
            } else {
                $error_count++;
            }
            $stmt->close();
        } else {
            $error = "Attendance table not found. Please contact administrator.";
            break;
        }
    }
    
    if ($success_count > 0) {
        $success = "Attendance saved for $success_count student(s)!";
        if ($error_count > 0) {
            $error .= " $error_count student(s) had errors.";
        }
        // Refresh attendance records
        $stmt = $conn->prepare("
            SELECT student_id, status, remarks
            FROM attendance
            WHERE class_id = ? AND date = ?
        ");
        $stmt->bind_param("is", $class_id, $date);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $attendance_records = [];
        foreach ($existing as $record) {
            $attendance_records[$record['student_id']] = $record;
        }
        $stmt->close();
    } else if (!$error) {
        $error = "Failed to save attendance. Please try again.";
    }
}

$conn->close();
$page_title = 'Attendance';
include 'includes/teacher_header.php';
?>

<style>
    .attendance-container {
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .attendance-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 24px;
        overflow: hidden;
    }
    
    .attendance-table th {
        background: #F9FAFB;
        padding: 14px 16px;
        font-size: 12px;
        font-weight: 600;
        color: #6B7280;
        text-transform: uppercase;
        border-bottom: 1px solid #E5E7EB;
        text-align: left;
    }
    
    .attendance-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #F0F2F5;
        vertical-align: middle;
    }
    
    .attendance-table tr:hover td {
        background: #FAFDFF;
    }
    
    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .status-present {
        background: #DCFCE7;
        color: #15803D;
    }
    
    .status-absent {
        background: #FEE2E2;
        color: #B91C1C;
    }
    
    .status-late {
        background: #FEF3C7;
        color: #B45309;
    }
    
    .status-excused {
        background: #DBEAFE;
        color: #1E40AF;
    }
    
    .radio-group {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    
    .radio-option {
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
    }
    
    .radio-option input {
        cursor: pointer;
    }
    
    .radio-option label {
        cursor: pointer;
        font-size: 12px;
    }
    
    .remarks-input {
        width: 150px;
        padding: 8px 12px;
        border: 1px solid #E5E7EB;
        border-radius: 10px;
        font-size: 12px;
    }
    
    .filter-bar {
        background: white;
        border-radius: 20px;
        padding: 20px 24px;
        margin-bottom: 24px;
        border: 1px solid #E5E7EB;
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: flex-end;
    }
    
    .filter-group {
        flex: 1;
        min-width: 180px;
    }
    
    .filter-group label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        color: #6B7280;
        text-transform: uppercase;
        margin-bottom: 6px;
    }
    
    .filter-select {
        width: 100%;
        padding: 10px 16px;
        border: 1px solid #E5E7EB;
        border-radius: 40px;
        font-size: 13px;
        background: white;
    }
    
    .btn-load {
        background: #2563EB;
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 40px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-load:hover {
        background: #1D4ED8;
    }
    
    .btn-save {
        background: #10B981;
        color: white;
        padding: 12px 28px;
        border-radius: 40px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-save:hover {
        background: #059669;
        transform: translateY(-1px);
    }
    
    .summary-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        border: 1px solid #E5E7EB;
        margin-bottom: 24px;
    }
    
    .summary-stat {
        text-align: center;
        padding: 12px;
    }
    
    .summary-number {
        font-size: 32px;
        font-weight: 700;
        color: #2563EB;
    }
    
    .summary-label {
        font-size: 12px;
        color: #6B7280;
    }
    
    .date-picker {
        padding: 10px 16px;
        border: 1px solid #E5E7EB;
        border-radius: 40px;
        font-size: 13px;
        width: 100%;
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
    
    @media (max-width: 768px) {
        .attendance-table {
            display: block;
            overflow-x: auto;
        }
        .radio-group {
            gap: 8px;
        }
        .remarks-input {
            width: 100px;
        }
        .filter-bar {
            flex-direction: column;
        }
        .filter-group {
            width: 100%;
        }
    }
</style>

<div class="attendance-container" style="padding: 0 20px 40px;">

    <!-- Header -->
    <div class="welcome-section fade-up">
        <div class="welcome-content">
            <div>
                <div class="greeting-badge">
                    <i class="fas fa-calendar-check"></i> Attendance Management
                </div>
                <h1>Take Attendance</h1>
                <p>Record student attendance for your classes</p>
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
            <label><i class="fas fa-calendar"></i> Date</label>
            <input type="date" name="date" class="date-picker" value="<?= $selected_date ?>">
        </div>
        
        <div class="filter-group">
            <label>&nbsp;</label>
            <button type="submit" class="btn-load">
                <i class="fas fa-search"></i> Load Students
            </button>
        </div>
    </form>

    <!-- Attendance Form -->
    <?php if ($selected_class > 0 && !empty($students)): ?>
    
    <!-- Summary Card -->
    <div class="summary-card fade-up">
        <div class="row">
            <div class="col-md-3 col-6">
                <div class="summary-stat">
                    <div class="summary-number" id="totalCount"><?= count($students) ?></div>
                    <div class="summary-label">Total Students</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="summary-stat">
                    <div class="summary-number" id="presentCount">0</div>
                    <div class="summary-label">Present</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="summary-stat">
                    <div class="summary-number" id="absentCount">0</div>
                    <div class="summary-label">Absent</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="summary-stat">
                    <div class="summary-number" id="attendanceRate">0%</div>
                    <div class="summary-label">Attendance Rate</div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($success): ?>
    <div style="margin-bottom: 20px;">
        <div style="background: #DCFCE7; border: 1px solid #86EFAC; color: #15803D; padding: 14px 18px; border-radius: 12px;">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($success) ?>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div style="margin-bottom: 20px;">
        <div style="background: #FEE2E2; border: 1px solid #FECACA; color: #B91C1C; padding: 14px 18px; border-radius: 12px;">
            <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
        </div>
    </div>
    <?php endif; ?>
    
    <form method="POST" class="fade-up">
        <input type="hidden" name="class_id" value="<?= $selected_class ?>">
        <input type="hidden" name="date" value="<?= $selected_date ?>">
        <input type="hidden" name="save_attendance" value="1">
        
        <div style="background: white; border-radius: 24px; border: 1px solid #E5E7EB; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Roll Number</th>
                            <th>Student Name</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $index => $student):
                            $student_id = $student['student_id'];
                            $existing = $attendance_records[$student_id] ?? null;
                            $status = $existing['status'] ?? 'present';
                            $remarks = $existing['remarks'] ?? '';
                        ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($student['roll_number']) ?></td>
                            <td><strong><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></strong></td>
                            <td>
                                <div class="radio-group">
                                    <label class="radio-option">
                                        <input type="radio" name="status_<?= $student_id ?>" value="present" 
                                               <?= $status == 'present' ? 'checked' : '' ?> onchange="updateStats()">
                                        <span class="status-badge status-present">Present</span>
                                    </label>
                                    <label class="radio-option">
                                        <input type="radio" name="status_<?= $student_id ?>" value="absent" 
                                               <?= $status == 'absent' ? 'checked' : '' ?> onchange="updateStats()">
                                        <span class="status-badge status-absent">Absent</span>
                                    </label>
                                    <label class="radio-option">
                                        <input type="radio" name="status_<?= $student_id ?>" value="late" 
                                               <?= $status == 'late' ? 'checked' : '' ?> onchange="updateStats()">
                                        <span class="status-badge status-late">Late</span>
                                    </label>
                                    <label class="radio-option">
                                        <input type="radio" name="status_<?= $student_id ?>" value="excused" 
                                               <?= $status == 'excused' ? 'checked' : '' ?> onchange="updateStats()">
                                        <span class="status-badge status-excused">Excused</span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <input type="text" name="remarks_<?= $student_id ?>" class="remarks-input" 
                                       placeholder="Optional" value="<?= htmlspecialchars($remarks) ?>">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div style="padding: 20px 24px; background: #F9FAFB; border-top: 1px solid #E5E7EB; text-align: right;">
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Save Attendance
                </button>
            </div>
        </div>
    </form>
    
    <?php elseif ($selected_class > 0 && empty($students)): ?>
    <div class="empty-state fade-up">
        <i class="fas fa-users-slash"></i>
        <h4>No Students Found</h4>
        <p>No students are enrolled in this class.</p>
    </div>
    
    <?php elseif ($selected_class == 0): ?>
    <div class="empty-state fade-up">
        <i class="fas fa-chalkboard"></i>
        <h4>Select a Class</h4>
        <p>Choose a class from the dropdown above to take attendance.</p>
    </div>
    <?php endif; ?>

</div>

<script>
function updateStats() {
    const totalStudents = <?= count($students) ?>;
    let present = 0;
    let absent = 0;
    
    <?php foreach ($students as $student): ?>
    const status_<?= $student['student_id'] ?> = document.querySelector('input[name="status_<?= $student['student_id'] ?>"]:checked');
    if (status_<?= $student['student_id'] ?>) {
        const value = status_<?= $student['student_id'] ?>.value;
        if (value === 'present') present++;
        if (value === 'absent') absent++;
    }
    <?php endforeach; ?>
    
    document.getElementById('presentCount').innerText = present;
    document.getElementById('absentCount').innerText = absent;
    const attendanceRate = totalStudents > 0 ? Math.round((present / totalStudents) * 100) : 0;
    document.getElementById('attendanceRate').innerText = attendanceRate + '%';
}

// Initialize stats on page load
document.addEventListener('DOMContentLoaded', function() {
    updateStats();
    
    // Fade up animation
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
    
    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert-success, .alert-error');
        alerts.forEach(alert => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);
});
</script>
