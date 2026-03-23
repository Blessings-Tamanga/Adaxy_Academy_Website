<?php
// ============================================================
//  Adaxy Academy · Teacher Settings
//  Account management and password change
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

// ── Handle Password Change ───────────────────────────────────
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_pw = $_POST['current_password'] ?? '';
    $new_pw = $_POST['new_password'] ?? '';
    $confirm_pw = $_POST['confirm_password'] ?? '';

    if (!$current_pw || !$new_pw || !$confirm_pw) {
        $error = 'Please fill in all fields.';
    } elseif (!password_verify($current_pw, $teacher['password'])) {
        $error = 'Your current password is incorrect.';
    } elseif (strlen($new_pw) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($new_pw !== $confirm_pw) {
        $error = 'New passwords do not match.';
    } else {
        $new_hash = password_hash($new_pw, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE teachers SET password = ? WHERE teacher_id = ?");
        $stmt->bind_param("si", $new_hash, $teacher_id);

        if ($stmt->execute()) {
            $success = 'Password changed successfully! Please use your new password next time you log in.';
            // Clear form fields
            $_POST = [];
        } else {
            $error = 'Something went wrong. Please try again.';
        }
        $stmt->close();
    }
}

// ── Get Teacher's Statistics ─────────────────────────────────
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT class_id) as total_classes
    FROM timetable
    WHERE teacher_id = ?
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$total_classes = $stmt->get_result()->fetch_assoc()['total_classes'];
$stmt->close();

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

$conn->close();
$page_title = 'Settings';
include 'includes/teacher_header.php';
?>

<style>
    .settings-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .settings-card {
        background: white;
        border-radius: 24px;
        border: 1px solid #E5E7EB;
        overflow: hidden;
        margin-bottom: 24px;
    }
    
    .settings-header {
        padding: 20px 24px;
        border-bottom: 1px solid #EFF3F8;
        background: #F8FAFE;
    }
    
    .settings-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #0F172A;
    }
    
    .settings-body {
        padding: 24px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
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
    
    .password-requirements {
        background: #F9FAFB;
        border-radius: 12px;
        padding: 12px 16px;
        margin-top: 12px;
    }
    
    .password-requirements p {
        font-size: 12px;
        margin: 0 0 8px 0;
        font-weight: 600;
        color: #374151;
    }
    
    .password-requirements ul {
        margin: 0;
        padding-left: 20px;
    }
    
    .password-requirements li {
        font-size: 11px;
        color: #6B7280;
        margin-bottom: 4px;
    }
    
    .btn-save {
        background: #2563EB;
        color: white;
        padding: 10px 28px;
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
    
    .btn-logout {
        background: #FEE2E2;
        color: #B91C1C;
        padding: 10px 28px;
        border-radius: 40px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-logout:hover {
        background: #FECACA;
        transform: translateY(-1px);
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
        color: #6B7280;
    }
    
    .info-value {
        font-size: 14px;
        font-weight: 500;
        color: #0F172A;
    }
    
    .danger-zone {
        background: #FEF2F2;
        border: 1px solid #FECACA;
    }
    
    .danger-zone .settings-header {
        background: #FEF2F2;
        border-bottom-color: #FECACA;
    }
    
    .danger-zone .settings-header h3 {
        color: #B91C1C;
    }
    
    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
    }
    
    .checkbox-label input {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    
    @media (max-width: 768px) {
        .settings-body {
            padding: 20px;
        }
        .info-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }
        .btn-save, .btn-logout {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="settings-container">

    <!-- Header -->
    <div class="welcome-section fade-up">
        <div class="welcome-content">
            <div>
                <div class="greeting-badge">
                    <i class="fas fa-cog"></i> Account Settings
                </div>
                <h1>Settings</h1>
                <p>Manage your account security and preferences</p>
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

    <div class="row g-4">
        <!-- Left Column: Password Change -->
        <div class="col-lg-6">
            
            <!-- Change Password Card -->
            <div class="settings-card fade-up">
                <div class="settings-header">
                    <h3><i class="fas fa-lock"></i> Change Password</h3>
                </div>
                <div class="settings-body">
                    <?php if ($success): ?>
                    <div class="alert-success">
                        <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($success) ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                    <div class="alert-error">
                        <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-input" 
                                   placeholder="Enter your current password" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-input" 
                                   placeholder="Enter new password (min. 6 characters)" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-input" 
                                   placeholder="Confirm your new password" required>
                        </div>
                        
                        <div class="password-requirements">
                            <p><i class="fas fa-shield-alt"></i> Password Requirements:</p>
                            <ul>
                                <li>At least 6 characters long</li>
                                <li>Use a mix of letters and numbers</li>
                                <li>Avoid using personal information</li>
                                <li>Don't share your password with anyone</li>
                            </ul>
                        </div>
                        
                        <div class="text-end mt-4">
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Account Information Card -->
            <div class="settings-card fade-up" style="transition-delay: 0.05s">
                <div class="settings-header">
                    <h3><i class="fas fa-user-circle"></i> Account Information</h3>
                </div>
                <div class="settings-body">
                    <div class="info-row">
                        <span class="info-label">Username</span>
                        <span class="info-value"><?= htmlspecialchars($teacher['username']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email Address</span>
                        <span class="info-value"><?= htmlspecialchars($teacher['email']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone Number</span>
                        <span class="info-value"><?= htmlspecialchars($teacher['phone'] ?? 'Not provided') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Employee Number</span>
                        <span class="info-value"><?= htmlspecialchars($teacher['employee_no']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Department</span>
                        <span class="info-value"><?= htmlspecialchars($department) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date Joined</span>
                        <span class="info-value"><?= date('F j, Y', strtotime($teacher['date_joined'])) ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Column: Preferences & Security -->
        <div class="col-lg-6">
            
            <!-- Session Management Card -->
            <div class="settings-card fade-up" style="transition-delay: 0.1s">
                <div class="settings-header">
                    <h3><i class="fas fa-clock"></i> Session Management</h3>
                </div>
                <div class="settings-body">
                    <div class="info-row">
                        <span class="info-label">Last Login</span>
                        <span class="info-value"><?= date('F j, Y H:i:s') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Session Status</span>
                        <span class="info-value"><span class="status-active">Active</span></span>
                    </div>
                    <div class="mt-3">
                        <a href="../Auth/logout.php" class="btn-logout">
                            <i class="fas fa-sign-out-alt"></i> Sign Out
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Preferences Card -->
            <div class="settings-card fade-up" style="transition-delay: 0.15s">
                <div class="settings-header">
                    <h3><i class="fas fa-sliders-h"></i> Preferences</h3>
                </div>
                <div class="settings-body">
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="emailNotifications" checked>
                            <span>Receive email notifications for announcements</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="gradeAlerts" checked>
                            <span>Get alerts when grades are due</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="darkMode">
                            <span>Dark mode (coming soon)</span>
                        </label>
                    </div>
                    <div class="mt-3 text-end">
                        <button class="btn-save" onclick="savePreferences()" style="background: #F3F4F6; color: #374151;">
                            <i class="fas fa-save"></i> Save Preferences
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Teaching Stats Card -->
            <div class="settings-card fade-up" style="transition-delay: 0.2s">
                <div class="settings-header">
                    <h3><i class="fas fa-chart-line"></i> Teaching Overview</h3>
                </div>
                <div class="settings-body">
                    <div class="row g-3">
                        <div class="col-4">
                            <div class="text-center">
                                <div class="stat-number" style="font-size: 28px;"><?= $total_classes ?></div>
                                <div class="stat-label">Classes</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center">
                                <div class="stat-number" style="font-size: 28px;"><?= $total_students ?></div>
                                <div class="stat-label">Students</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center">
                                <div class="stat-number" style="font-size: 28px;"><?= date('Y') - date('Y', strtotime($teacher['date_joined'])) ?></div>
                                <div class="stat-label">Years</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Security Tips Card -->
            <div class="settings-card fade-up" style="transition-delay: 0.25s">
                <div class="settings-header">
                    <h3><i class="fas fa-shield-alt"></i> Security Tips</h3>
                </div>
                <div class="settings-body">
                    <ul style="margin: 0; padding-left: 20px;">
                        <li>Never share your password with anyone</li>
                        <li>Use a strong, unique password for your account</li>
                        <li>Log out when using public computers</li>
                        <li>Report suspicious activity to IT support</li>
                        <li>Change your password regularly</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Danger Zone -->
    <div class="settings-card danger-zone fade-up mt-4" style="transition-delay: 0.3s">
        <div class="settings-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Danger Zone</h3>
        </div>
        <div class="settings-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <p class="fw-bold mb-1" style="color: #B91C1C;">Deactivate Account</p>
                    <p class="text-muted small">Once deactivated, you will lose access to your account.</p>
                </div>
                <button class="btn-logout" onclick="confirmDeactivate()" style="background: #FEE2E2;">
                    <i class="fas fa-user-slash"></i> Deactivate Account
                </button>
            </div>
        </div>
    </div>
    
    <!-- Footer Note -->
    <div class="text-center mt-4 pb-4">
        <p class="text-muted" style="font-size: 12px;">
            <i class="fas fa-shield-alt"></i> For assistance, contact the IT Help Desk at it@adaxy.mw
        </p>
    </div>

</div>

<script>
// Save preferences function
function savePreferences() {
    const emailNotifications = document.getElementById('emailNotifications').checked;
    const gradeAlerts = document.getElementById('gradeAlerts').checked;
    const darkMode = document.getElementById('darkMode').checked;
    
    // In a real application, you would save these to the database
    alert('Preferences saved! (This is a demo)');
}

// Confirm deactivation
function confirmDeactivate() {
    if (confirm('Are you sure you want to deactivate your account? This action can be reversed by contacting HR.')) {
        alert('Please contact HR to deactivate your account. (Demo)');
    }
}

// Auto-hide alerts after 5 seconds
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert-success, .alert-error');
    alerts.forEach(alert => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    });
}, 5000);

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
    
    // Add password strength indicator
    const newPassword = document.querySelector('input[name="new_password"]');
    const confirmPassword = document.querySelector('input[name="confirm_password"]');
    
    if (newPassword && confirmPassword) {
        function checkPasswordStrength() {
            const pass = newPassword.value;
            const strength = document.getElementById('passwordStrength');
            if (strength) {
                if (pass.length >= 8 && /[A-Z]/.test(pass) && /[0-9]/.test(pass)) {
                    strength.innerHTML = '<span style="color: #10B981;">✓ Strong password</span>';
                } else if (pass.length >= 6) {
                    strength.innerHTML = '<span style="color: #F59E0B;">⚠️ Medium password</span>';
                } else if (pass.length > 0) {
                    strength.innerHTML = '<span style="color: #EF4444;">✗ Weak password</span>';
                } else {
                    strength.innerHTML = '';
                }
            }
        }
        
        newPassword.addEventListener('input', checkPasswordStrength);
    }
});
</script>

