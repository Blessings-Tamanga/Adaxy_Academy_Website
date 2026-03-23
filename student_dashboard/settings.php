<?php
// ============================================================
//  Adaxy Academy · Student Settings & Profile
//  Manage account, view profile, change password
// ============================================================

session_start();
include('../config/db_connect.php');

// ── Authentication Guard ─────────────────────────────────────
if (empty($_SESSION['slogin'])) {
    header('Location: ../Auth/login.php?role=student');
    exit;
}

$username = $_SESSION['slogin'];

// ── Fetch Student Information ────────────────────────────────
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

$student_id = (int)$student['student_id'];
$full_name  = $student['first_name'] . ' ' . $student['last_name'];
$first_name = $student['first_name'];
$class_name = $student['class_name'] ?? 'N/A';
$programme  = $student['programme'];
$form_level = (int)$student['form_level'];
$roll_number = $student['roll_number'];
$username_display = $student['username'];
$initials   = strtoupper(substr($student['first_name'],0,1) . substr($student['last_name'],0,1));

$success = '';
$error   = '';

// ── Handle Password Change ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_pw = $_POST['current_password'] ?? '';
    $new_pw = $_POST['new_password'] ?? '';
    $confirm_pw = $_POST['confirm_password'] ?? '';

    if (!$current_pw || !$new_pw || !$confirm_pw) {
        $error = 'Please fill in all fields.';
    } elseif (!password_verify($current_pw, $student['password'])) {
        $error = 'Your current password is incorrect.';
    } elseif (strlen($new_pw) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($new_pw !== $confirm_pw) {
        $error = 'New passwords do not match.';
    } else {
        $new_hash = password_hash($new_pw, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE students SET password = ? WHERE student_id = ?");
        $stmt->bind_param("si", $new_hash, $student_id);

        if ($stmt->execute()) {
            $success = 'Password changed successfully.';
            // Clear form fields
            $_POST = [];
        } else {
            $error = 'Something went wrong. Please try again.';
        }
        $stmt->close();
    }
}

$page_title = 'Settings & Profile';
$conn->close();

include 'includes/header.php';
?>

<style>
    .profile-header {
        background: linear-gradient(135deg, #0F2B3D 0%, #1A4A6F 100%);
        border-radius: 24px;
        padding: 28px 32px;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
    }
    
    .profile-avatar {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #2563EB, #60A5FA);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: 700;
        color: white;
        box-shadow: 0 8px 20px rgba(37,99,235,0.3);
    }
    
    .info-section {
        background: white;
        border-radius: 20px;
        padding: 20px 24px;
        margin-bottom: 24px;
        border: 1px solid #E5E7EB;
    }
    
    .section-title {
        font-size: 16px;
        font-weight: 600;
        color: #0F172A;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #2563EB;
        display: inline-block;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .info-label {
        font-size: 11px;
        font-weight: 600;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .info-value {
        font-size: 15px;
        font-weight: 500;
        color: #0F172A;
    }
    
    .status-active {
        background: #DCFCE7;
        color: #15803D;
        padding: 4px 12px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }
    
    .alert-success {
        background: #DCFCE7;
        border: 1px solid #86EFAC;
        color: #15803D;
        padding: 14px 18px;
        border-radius: 12px;
        margin-bottom: 20px;
    }
    
    .alert-error {
        background: #FEE2E2;
        border: 1px solid #FECACA;
        color: #B91C1C;
        padding: 14px 18px;
        border-radius: 12px;
        margin-bottom: 20px;
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
        .profile-header { padding: 20px; }
        .info-section { padding: 16px 20px; }
        .info-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="settings-container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px 40px;">

    <!-- Profile Header -->
    <div class="profile-header fade-up">
        <div style="display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
            <div class="profile-avatar">
                <?= $initials ?>
            </div>
            <div style="flex: 1;">
                <div class="section-tag" style="color: #FFD966;">Account Settings</div>
                <h1 style="color: white; margin: 8px 0 4px; font-size: 28px;"><?= htmlspecialchars($full_name) ?></h1>
                <p style="color: #B0C4DE; margin: 0;">
                    <i class="fa fa-id-card"></i> <?= $roll_number ?> · 
                    <i class="fa fa-graduation-cap"></i> <?= $class_name ?> · 
                    <i class="fa fa-user"></i> @<?= $username_display ?>
                </p>
            </div>
            <div>
                <span class="status-active"><i class="fa fa-circle-check"></i> Active Student</span>
            </div>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="row g-4">
        
        <!-- Left Column: Personal & Academic Info -->
        <div class="col-lg-7">
            
            <!-- Personal Information -->
            <div class="info-section fade-up">
                <h4 class="section-title"><i class="fa fa-user me-2" style="color: #2563EB;"></i>Personal Information</h4>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">First Name</span>
                        <span class="info-value"><?= htmlspecialchars($student['first_name']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Last Name</span>
                        <span class="info-value"><?= htmlspecialchars($student['last_name']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Username</span>
                        <span class="info-value"><?= htmlspecialchars($student['username']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Roll Number</span>
                        <span class="info-value"><?= htmlspecialchars($student['roll_number']) ?></span>
                    </div>
                    <?php if (!empty($student['phone'])): ?>
                    <div class="info-item">
                        <span class="info-label">Phone</span>
                        <span class="info-value"><?= htmlspecialchars($student['phone']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($student['email'])): ?>
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?= htmlspecialchars($student['email']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($student['date_of_birth'])): ?>
                    <div class="info-item">
                        <span class="info-label">Date of Birth</span>
                        <span class="info-value"><?= date('F j, Y', strtotime($student['date_of_birth'])) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($student['gender'])): ?>
                    <div class="info-item">
                        <span class="info-label">Gender</span>
                        <span class="info-value"><?= htmlspecialchars($student['gender']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($student['address'])): ?>
                    <div class="info-item">
                        <span class="info-label">Address</span>
                        <span class="info-value"><?= htmlspecialchars($student['address']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Academic Information -->
            <div class="info-section fade-up" style="transition-delay: 0.05s">
                <h4 class="section-title"><i class="fa fa-graduation-cap me-2" style="color: #2563EB;"></i>Academic Information</h4>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Class</span>
                        <span class="info-value"><?= htmlspecialchars($class_name) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Programme</span>
                        <span class="info-value"><?= htmlspecialchars($programme) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Form Level</span>
                        <span class="info-value">Form <?= $form_level ?></span>
                    </div>
                    <?php if ($programme === 'MSCE'): ?>
                    <div class="info-item">
                        <span class="info-label">Years to Graduate</span>
                        <span class="info-value"><?= max(0, 6 - $form_level) ?> year(s)</span>
                    </div>
                    <?php else: ?>
                    <div class="info-item">
                        <span class="info-label">Years to JCE</span>
                        <span class="info-value"><?= max(0, 4 - $form_level) ?> year(s)</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Parent/Guardian Information -->
            <?php if (!empty($student['parent_name']) || !empty($student['parent_phone']) || !empty($student['parent_email'])): ?>
            <div class="info-section fade-up" style="transition-delay: 0.1s">
                <h4 class="section-title"><i class="fa fa-people-roof me-2" style="color: #2563EB;"></i>Parent / Guardian</h4>
                <div class="info-grid">
                    <?php if (!empty($student['parent_name'])): ?>
                    <div class="info-item">
                        <span class="info-label">Name</span>
                        <span class="info-value"><?= htmlspecialchars($student['parent_name']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($student['parent_phone'])): ?>
                    <div class="info-item">
                        <span class="info-label">Phone</span>
                        <span class="info-value"><?= htmlspecialchars($student['parent_phone']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($student['parent_email'])): ?>
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?= htmlspecialchars($student['parent_email']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
        
        <!-- Right Column: Password Change & Security -->
        <div class="col-lg-5">
            
            <!-- Change Password Form -->
            <div class="info-section fade-up">
                <h4 class="section-title"><i class="fa fa-lock me-2" style="color: #2563EB;"></i>Change Password</h4>
                
                <?php if ($success): ?>
                    <div class="alert-success">
                        <i class="fa fa-check-circle me-2"></i> <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert-error">
                        <i class="fa fa-circle-xmark me-2"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Minimum 6 characters" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password" required>
                    </div>
                    <button type="submit" class="btn-enroll" style="background: #2563EB; width: 100%;">
                        <i class="fa fa-floppy-disk"></i> Update Password
                    </button>
                </form>
            </div>
            
            <!-- Password Rules -->
            <div class="info-section fade-up" style="transition-delay: 0.05s">
                <h4 class="section-title"><i class="fa fa-shield-halved me-2" style="color: #2563EB;"></i>Password Rules</h4>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div><i class="fa fa-check-circle" style="color: #10B981;"></i> At least 6 characters long</div>
                    <div><i class="fa fa-check-circle" style="color: #10B981;"></i> Use a mix of letters and numbers</div>
                    <div><i class="fa fa-check-circle" style="color: #10B981;"></i> Do not share your password with anyone</div>
                    <div><i class="fa fa-check-circle" style="color: #10B981;"></i> Change your password regularly</div>
                    <div><i class="fa fa-check-circle" style="color: #10B981;"></i> Avoid using personal information</div>
                </div>
            </div>
            
            <!-- Session Management -->
            <div class="info-section fade-up" style="transition-delay: 0.1s; border-color: #FECACA;">
                <h4 class="section-title" style="color: #DC2626;"><i class="fa fa-triangle-exclamation me-2"></i>Session</h4>
                <p style="color: #6B7280; font-size: 14px; margin-bottom: 16px;">
                    Sign out of your account on this device. You'll need to log in again to access your dashboard.
                </p>
                <a href="../Auth/logout.php" class="btn-danger-soft" style="display: inline-block;">
                    <i class="fa fa-sign-out-alt"></i> Sign Out
                </a>
            </div>
            
            <!-- Quick Links -->
            <div class="info-section fade-up" style="transition-delay: 0.15s; background: #F8FAFE;">
                <h4 class="section-title"><i class="fa fa-link me-2" style="color: #2563EB;"></i>Quick Links</h4>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <a href="index.php" style="color: #2563EB; text-decoration: none;">
                        <i class="fa fa-chart-pie"></i> Dashboard
                    </a>
                    <a href="grades.php" style="color: #2563EB; text-decoration: none;">
                        <i class="fa fa-file-lines"></i> My Grades
                    </a>
                    <a href="timetable.php" style="color: #2563EB; text-decoration: none;">
                        <i class="fa fa-calendar-alt"></i> Timetable
                    </a>
                    <a href="notices.php" style="color: #2563EB; text-decoration: none;">
                        <i class="fa fa-bell"></i> Notices
                    </a>
                </div>
            </div>
            
        </div>
        
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
