<?php
// ============================================================
//  Adaxy Academy · Teacher Profile
//  View and manage teacher profile information
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

// ── Get Teacher's Statistics ─────────────────────────────────
// Total classes taught
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT class_id) as total_classes
    FROM timetable
    WHERE teacher_id = ?
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$total_classes = $stmt->get_result()->fetch_assoc()['total_classes'];
$stmt->close();

// Total students taught
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT s.student_id) as total_students
    FROM timetable t
    INNER JOIN students s ON s.class_id = t.class_id
    WHERE t.teacher_id = ?
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$total_students = $stmt->get_result()->fetch_assoc()['total_students'];
$stmt->close();

// Subjects taught
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT subject_id) as total_subjects
    FROM timetable
    WHERE teacher_id = ?
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$total_subjects = $stmt->get_result()->fetch_assoc()['total_subjects'];
$stmt->close();

// Years of service
$date_joined = new DateTime($teacher['date_joined']);
$now = new DateTime();
$years_of_service = $date_joined->diff($now)->y;

// ── Get Teacher's Subjects ───────────────────────────────────
$stmt = $conn->prepare("
    SELECT DISTINCT sub.subject_id, sub.subject_name, sub.subject_code
    FROM timetable t
    INNER JOIN subjects sub ON sub.subject_id = t.subject_id
    WHERE t.teacher_id = ?
    ORDER BY sub.subject_name ASC
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$subjects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── Get Teacher's Classes ────────────────────────────────────
$stmt = $conn->prepare("
    SELECT DISTINCT c.class_id, c.class_name, c.form_level, c.programme, c.stream
    FROM timetable t
    INNER JOIN classes c ON c.class_id = t.class_id
    WHERE t.teacher_id = ?
    ORDER BY c.form_level ASC, c.class_name ASC
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$classes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── Handle Profile Update ────────────────────────────────────
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    if ($email && $phone) {
        $stmt = $conn->prepare("
            UPDATE teachers 
            SET email = ?, phone = ?
            WHERE teacher_id = ?
        ");
        $stmt->bind_param("ssi", $email, $phone, $teacher_id);
        
        if ($stmt->execute()) {
            $success = "Profile updated successfully!";
            // Refresh teacher data
            $teacher['email'] = $email;
            $teacher['phone'] = $phone;
        } else {
            $error = "Failed to update profile. Please try again.";
        }
        $stmt->close();
    } else {
        $error = "Please fill in all fields.";
    }
}

$conn->close();
$page_title = 'My Profile';
include 'includes/teacher_header.php';
?>

<style>
    .profile-header {
        background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
        border-radius: 28px;
        padding: 32px 36px;
        margin-bottom: 28px;
    }
    
    .profile-avatar-large {
        width: 120px;
        height: 120px;
        background: linear-gradient(135deg, #2563EB, #60A5FA);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        font-weight: 700;
        color: white;
        margin: 0 auto 16px;
        box-shadow: 0 8px 24px rgba(37,99,235,0.3);
    }
    
    .info-section {
        background: white;
        border-radius: 24px;
        border: 1px solid #E5E7EB;
        overflow: hidden;
        margin-bottom: 24px;
    }
    
    .info-header {
        padding: 20px 24px;
        border-bottom: 1px solid #EFF3F8;
        background: #F8FAFE;
    }
    
    .info-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #0F172A;
    }
    
    .info-body {
        padding: 20px 24px;
    }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #F0F2F5;
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-size: 13px;
        font-weight: 500;
        color: #6B7280;
    }
    
    .info-value {
        font-size: 14px;
        font-weight: 500;
        color: #0F172A;
    }
    
    .stat-badge-profile {
        background: #EFF6FF;
        padding: 8px 16px;
        border-radius: 12px;
        text-align: center;
    }
    
    .stat-number {
        font-size: 24px;
        font-weight: 700;
        color: #2563EB;
    }
    
    .stat-label {
        font-size: 11px;
        color: #6B7280;
        text-transform: uppercase;
    }
    
    .subject-badge {
        display: inline-block;
        background: #EFF6FF;
        padding: 6px 14px;
        border-radius: 30px;
        font-size: 12px;
        color: #2563EB;
        margin: 0 8px 8px 0;
    }
    
    .class-badge {
        display: inline-block;
        background: #F3F4F6;
        padding: 6px 14px;
        border-radius: 30px;
        font-size: 12px;
        color: #374151;
        margin: 0 8px 8px 0;
    }
    
    .edit-form {
        background: white;
        border-radius: 24px;
        border: 1px solid #E5E7EB;
        overflow: hidden;
    }
    
    .form-input {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        font-size: 14px;
        transition: all 0.2s;
    }
    
    .form-input:focus {
        border-color: #2563EB;
        outline: none;
        box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    }
    
    .btn-save {
        background: #2563EB;
        color: white;
        padding: 10px 24px;
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
    
    .btn-edit {
        background: #F3F4F6;
        color: #374151;
        padding: 8px 20px;
        border-radius: 40px;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s;
    }
    
    .btn-edit:hover {
        background: #E5E7EB;
    }
    
    .status-active {
        display: inline-block;
        padding: 4px 12px;
        background: #DCFCE7;
        color: #15803D;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 600;
    }
    
    @media (max-width: 768px) {
        .profile-header {
            padding: 24px;
        }
        .info-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }
        .stat-number {
            font-size: 20px;
        }
    }
</style>

<div class="profile-container" style="max-width: 1200px; margin: 0 auto;">

    <!-- Profile Header -->
    <div class="profile-header fade-up">
        <div class="text-center">
            <div class="profile-avatar-large">
                <?= $initials ?>
            </div>
            <h1 style="color: white; margin: 16px 0 8px;"><?= htmlspecialchars($full_name) ?></h1>
            <p style="color: #94A3B8; margin-bottom: 12px;">
                <i class="fas fa-briefcase"></i> <?= htmlspecialchars($department) ?> Department
            </p>
            <span class="status-active"><i class="fas fa-check-circle"></i> Active Staff Member</span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Personal Information -->
        <div class="col-lg-6">
            
            <!-- Personal Information Card -->
            <div class="info-section fade-up">
                <div class="info-header">
                    <h3><i class="fas fa-user-circle"></i> Personal Information</h3>
                </div>
                <div class="info-body">
                    <div class="info-row">
                        <span class="info-label">Full Name</span>
                        <span class="info-value"><?= htmlspecialchars($full_name) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Employee Number</span>
                        <span class="info-value"><?= htmlspecialchars($teacher['employee_no']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Username</span>
                        <span class="info-value"><?= htmlspecialchars($teacher['username']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Gender</span>
                        <span class="info-value"><?= htmlspecialchars($teacher['gender']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date Joined</span>
                        <span class="info-value"><?= date('F j, Y', strtotime($teacher['date_joined'])) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Years of Service</span>
                        <span class="info-value"><?= $years_of_service ?> year(s)</span>
                    </div>
                </div>
            </div>
            
            <!-- Professional Information -->
            <div class="info-section fade-up" style="transition-delay: 0.05s">
                <div class="info-header">
                    <h3><i class="fas fa-graduation-cap"></i> Professional Information</h3>
                </div>
                <div class="info-body">
                    <div class="info-row">
                        <span class="info-label">Qualification</span>
                        <span class="info-value"><?= htmlspecialchars($teacher['qualification'] ?? 'Not specified') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Department</span>
                        <span class="info-value"><?= htmlspecialchars($department) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Subjects Taught</span>
                        <span class="info-value"><?= $total_subjects ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Classes Assigned</span>
                        <span class="info-value"><?= $total_classes ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Total Students</span>
                        <span class="info-value"><?= $total_students ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Column: Contact & Stats -->
        <div class="col-lg-6">
            
            <!-- Contact Information -->
            <div class="info-section fade-up" style="transition-delay: 0.1s">
                <div class="info-header">
                    <h3><i class="fas fa-address-card"></i> Contact Information</h3>
                </div>
                <div class="info-body">
                    <div class="info-row">
                        <span class="info-label">Email Address</span>
                        <span class="info-value"><?= htmlspecialchars($teacher['email']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone Number</span>
                        <span class="info-value"><?= htmlspecialchars($teacher['phone'] ?? 'Not provided') ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="info-section fade-up" style="transition-delay: 0.15s">
                <div class="info-header">
                    <h3><i class="fas fa-chart-simple"></i> Quick Stats</h3>
                </div>
                <div class="info-body">
                    <div class="row g-3">
                        <div class="col-4">
                            <div class="stat-badge-profile">
                                <div class="stat-number"><?= $total_classes ?></div>
                                <div class="stat-label">Classes</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-badge-profile">
                                <div class="stat-number"><?= $total_subjects ?></div>
                                <div class="stat-label">Subjects</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-badge-profile">
                                <div class="stat-number"><?= $total_students ?></div>
                                <div class="stat-label">Students</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Subjects Taught List -->
            <div class="info-section fade-up" style="transition-delay: 0.2s">
                <div class="info-header">
                    <h3><i class="fas fa-book"></i> Subjects You Teach</h3>
                </div>
                <div class="info-body">
                    <?php if ($subjects): ?>
                        <?php foreach ($subjects as $subject): ?>
                        <span class="subject-badge">
                            <?= htmlspecialchars($subject['subject_code']) ?> - <?= htmlspecialchars($subject['subject_name']) ?>
                        </span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No subjects assigned yet.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Classes Assigned List -->
            <div class="info-section fade-up" style="transition-delay: 0.25s">
                <div class="info-header">
                    <h3><i class="fas fa-chalkboard"></i> Your Classes</h3>
                </div>
                <div class="info-body">
                    <?php if ($classes): ?>
                        <?php foreach ($classes as $class): ?>
                        <span class="class-badge">
                            <?= htmlspecialchars($class['class_name']) ?> (<?= $class['programme'] ?> Form <?= $class['form_level'] ?>)
                        </span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No classes assigned yet.</p>
                    <?php endif; ?>
                    <div class="mt-3">
                        <a href="classes.php" class="btn-edit">
                            <i class="fas fa-arrow-right"></i> View All Classes
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Edit Profile Form -->
            <div class="edit-form fade-up" style="transition-delay: 0.3s">
                <div class="info-header">
                    <h3><i class="fas fa-pen"></i> Update Contact Information</h3>
                </div>
                <div class="info-body">
                    <?php if ($success): ?>
                    <div style="background: #DCFCE7; border: 1px solid #86EFAC; color: #15803D; padding: 12px 16px; border-radius: 12px; margin-bottom: 20px;">
                        <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($success) ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                    <div style="background: #FEE2E2; border: 1px solid #FECACA; color: #B91C1C; padding: 12px 16px; border-radius: 12px; margin-bottom: 20px;">
                        <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-input" 
                                   value="<?= htmlspecialchars($teacher['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-input" 
                                   value="<?= htmlspecialchars($teacher['phone'] ?? '') ?>" required>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Password Change Link -->
            <div class="edit-form fade-up mt-4" style="transition-delay: 0.35s">
                <div class="info-header">
                    <h3><i class="fas fa-lock"></i> Security</h3>
                </div>
                <div class="info-body">
                    <p class="text-muted mb-3">Change your password to keep your account secure.</p>
                    <a href="change-password.php" class="btn-edit">
                        <i class="fas fa-key"></i> Change Password
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer Note -->
    <div class="text-center mt-4 pb-4">
        <p class="text-muted" style="font-size: 12px;">
            <i class="fas fa-shield-alt"></i> For any corrections, please contact the HR department.
        </p>
    </div>

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
