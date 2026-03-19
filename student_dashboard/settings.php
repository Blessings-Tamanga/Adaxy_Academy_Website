<?php
session_start();
include('../config/db_connect.php');

if (empty($_SESSION['slogin'])) { header('Location: ../Auth/login.php?role=student'); exit; }

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

if (!$student) { session_destroy(); header('Location: ../Auth/login.php?role=student'); exit; }

$student_id = (int)$student['student_id'];
$full_name  = $student['first_name'] . ' ' . $student['last_name'];
$first_name = $student['first_name'];
$class_name = $student['class_name'] ?? 'N/A';
$initials   = strtoupper(substr($student['first_name'],0,1) . substr($student['last_name'],0,1));

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_pw  = $_POST['current_password']  ?? '';
    $new_pw      = $_POST['new_password']      ?? '';
    $confirm_pw  = $_POST['confirm_password']  ?? '';

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
        } else {
            $error = 'Something went wrong. Please try again.';
        }
        $stmt->close();
    }
}

$page_title = 'Settings';
$conn->close();

include 'includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
  <div>
    <div class="section-tag">Account</div>
    <h2 style="font-size:26px;margin-bottom:4px;">Settings</h2>
    <p style="color:var(--muted);">Manage your account and password</p>
  </div>
</div>

<div class="row g-4">

  <!-- change password -->
  <div class="col-lg-6 fade-up">
    <div class="card-box">
      <div class="card-box-header">
        <h4><i class="fa fa-lock me-2" style="color:var(--gold);"></i>Change Password</h4>
      </div>

      <?php if ($success): ?>
        <div class="alert-success-custom"><i class="fa fa-check-circle me-2"></i><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert-error-custom"><i class="fa fa-circle-xmark me-2"></i><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Current Password</label>
          <input type="password" name="current_password" class="form-control" placeholder="••••••••" required/>
        </div>
        <div class="mb-3">
          <label class="form-label">New Password</label>
          <input type="password" name="new_password" class="form-control" placeholder="Minimum 6 characters" required/>
        </div>
        <div class="mb-4">
          <label class="form-label">Confirm New Password</label>
          <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password" required/>
        </div>
        <button type="submit" class="btn-enroll">
          <i class="fa fa-floppy-disk"></i> Update Password
        </button>
      </form>
    </div>
  </div>

  <!-- account info -->
  <div class="col-lg-6 fade-up" style="transition-delay:.1s">
    <div class="card-box mb-4">
      <div class="card-box-header">
        <h4><i class="fa fa-user-circle me-2" style="color:var(--gold);"></i>Account Information</h4>
      </div>
      <div class="info-row">
        <span class="info-label">Full Name</span>
        <span class="info-value"><?= htmlspecialchars($full_name) ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Username</span>
        <span class="info-value"><?= htmlspecialchars($student['username']) ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Roll Number</span>
        <span class="info-value"><?= htmlspecialchars($student['roll_number']) ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Class</span>
        <span class="info-value"><?= htmlspecialchars($class_name) ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Account Status</span>
        <span class="info-value"><span class="badge-pill badge-active">Active</span></span>
      </div>
    </div>

    <!-- password rules -->
    <div class="card-box">
      <div class="card-box-header">
        <h4><i class="fa fa-shield-halved me-2" style="color:var(--gold);"></i>Password Rules</h4>
      </div>
      <div style="display:flex;flex-direction:column;gap:10px;font-size:14px;color:var(--muted);">
        <div><i class="fa fa-check me-2" style="color:#15803d;"></i>At least 6 characters long</div>
        <div><i class="fa fa-check me-2" style="color:#15803d;"></i>Use a mix of letters and numbers</div>
        <div><i class="fa fa-check me-2" style="color:#15803d;"></i>Do not share your password</div>
        <div><i class="fa fa-check me-2" style="color:#15803d;"></i>Change it regularly</div>
      </div>
    </div>
  </div>

  <!-- danger zone -->
  <div class="col-12 fade-up">
    <div class="card-box" style="border-color:#fecaca;">
      <div class="card-box-header">
        <h4 style="color:#b91c1c;"><i class="fa fa-triangle-exclamation me-2"></i>Session</h4>
      </div>
      <p style="color:var(--muted);font-size:14px;margin-bottom:16px;">Sign out of your account on this device.</p>
      <a href="../Auth/logout.php" class="btn-danger-soft">
        <i class="fa fa-sign-out-alt me-2"></i>Sign Out
      </a>
    </div>
  </div>

</div>

