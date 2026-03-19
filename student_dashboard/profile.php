<?php
session_start();
include('../config/db_connect.php');

if (empty($_SESSION['slogin'])) { header('Location: ../Auth/login.php?role=student'); exit; }

$username = $_SESSION['slogin'];

// fetch student + class
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

$page_title = 'My Profile';

$conn->close();

include 'includes/header.php';
?>

  <!-- RIGHT: full details -->
  <div class="col-lg-8">

    <!-- personal info -->
    <div class="card-box mb-4">
      <div class="card-box-header">
        <h4><i class="fa fa-user me-2" style="color:var(--gold);"></i>Personal Information</h4>
      </div>
      <div class="row g-3">
        <div class="col-sm-6">
          <div class="info-row">
            <span class="info-label">First Name</span>
            <span class="info-value"><?= htmlspecialchars($student['first_name']) ?></span>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="info-row">
            <span class="info-label">Last Name</span>
            <span class="info-value"><?= htmlspecialchars($student['last_name']) ?></span>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="info-row">
            <span class="info-label">Roll Number</span>
            <span class="info-value"><?= htmlspecialchars($student['roll_number']) ?></span>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="info-row">
            <span class="info-label">Username</span>
            <span class="info-value"><?= htmlspecialchars($student['username']) ?></span>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="info-row">
            <span class="info-label">Phone</span>
            <span class="info-value"><?= htmlspecialchars($student['phone'] ?? 'N/A') ?></span>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="info-row">
            <span class="info-label">Email</span>
            <span class="info-value"><?= htmlspecialchars($student['email'] ?? 'N/A') ?></span>
          </div>
        </div>
        <div class="col-12">
          <div class="info-row">
            <span class="info-label">Address</span>
            <span class="info-value"><?= htmlspecialchars($student['address'] ?? 'N/A') ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- academic info -->
    <div class="card-box mb-4">
      <div class="card-box-header">
        <h4><i class="fa fa-graduation-cap me-2" style="color:var(--gold);"></i>Academic Information</h4>
      </div>
      <div class="info-row">
        <span class="info-label">Class</span>
        <span class="info-value"><?= htmlspecialchars($class_name) ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Programme</span>
        <span class="info-value"><?= htmlspecialchars($student['programme']) ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Form Level</span>
        <span class="info-value">Form <?= htmlspecialchars($student['form_level']) ?></span>
      </div>
    </div>

    <!-- parent info -->
    <div class="card-box">
      <div class="card-box-header">
        <h4><i class="fa fa-people-roof me-2" style="color:var(--gold);"></i>Parent / Guardian</h4>
      </div>
      <div class="info-row">
        <span class="info-label">Name</span>
        <span class="info-value"><?= htmlspecialchars($student['parent_name'] ?? 'N/A') ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Phone</span>
        <span class="info-value"><?= htmlspecialchars($student['parent_phone'] ?? 'N/A') ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Email</span>
        <span class="info-value"><?= htmlspecialchars($student['parent_email'] ?? 'N/A') ?></span>
      </div>
      <div style="margin-top:16px;">
        <a href="settings.php" class="btn-outline-gold">
          <i class="fa fa-lock me-2"></i>Change Password
        </a>
      </div>
    </div>

  </div>
</div>

