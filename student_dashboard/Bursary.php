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

// handle submission — store as a notice for management review
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason         = trim($_POST['reason']          ?? '');
    $amount         = trim($_POST['amount']          ?? '');
    $guardian_job   = trim($_POST['guardian_job']    ?? '');
    $siblings       = trim($_POST['siblings']        ?? '');
    $extra_info     = trim($_POST['extra_info']      ?? '');

    if (!$reason || !$amount) {
        $error = 'Please fill in all required fields.';
    } else {
        $title   = '[BURSARY APPLICATION] ' . $full_name . ' — ' . $class_name;
        $content = "Student: $full_name | Roll: {$student['roll_number']} | Class: $class_name\n"
                 . "Amount Requested: MWK $amount\n"
                 . "Guardian Occupation: $guardian_job\n"
                 . "Number of Siblings: $siblings\n\n"
                 . "Reason:\n$reason\n\n"
                 . "Additional Info:\n$extra_info";

        $stmt = $conn->prepare("
            INSERT INTO notices (title, content, audience, posted_by, posted_role, is_published)
            VALUES (?, ?, 'teachers', ?, 'admin', 0)
        ");
        $stmt->bind_param("sss", $title, $content, $full_name);

        if ($stmt->execute()) {
            $success = 'Your bursary application has been submitted. The bursar will review it within 5 working days.';
        } else {
            $error = 'Something went wrong. Please try again.';
        }
        $stmt->close();
    }
}

$page_title = 'Apply for Bursary';
$conn->close();

include 'includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
  <div>
    <div class="section-tag">Financial Aid</div>
    <h2 style="font-size:26px;margin-bottom:4px;">Apply for Bursary</h2>
    <p style="color:var(--muted);">Submit a financial assistance application to the bursar</p>
  </div>
</div>

<div class="row g-4">

  <!-- form -->
  <div class="col-lg-8 fade-up">
    <div class="card-box">
      <div class="card-box-header">
        <h4><i class="fa fa-hand-holding-heart me-2" style="color:var(--gold);"></i>Bursary Application Form</h4>
      </div>

      <?php if ($success): ?>
        <div class="alert-success-custom"><i class="fa fa-check-circle me-2"></i><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert-error-custom"><i class="fa fa-circle-xmark me-2"></i><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <!-- auto-filled student info -->
      <div style="background:var(--cream);border-radius:12px;padding:16px;margin-bottom:24px;">
        <div style="font-size:12px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;">Applicant Details (auto-filled)</div>
        <div class="row g-2">
          <div class="col-sm-4">
            <div style="font-size:12px;color:var(--muted);">Full Name</div>
            <div style="font-size:14px;font-weight:600;"><?= htmlspecialchars($full_name) ?></div>
          </div>
          <div class="col-sm-4">
            <div style="font-size:12px;color:var(--muted);">Roll Number</div>
            <div style="font-size:14px;font-weight:600;"><?= htmlspecialchars($student['roll_number']) ?></div>
          </div>
          <div class="col-sm-4">
            <div style="font-size:12px;color:var(--muted);">Class</div>
            <div style="font-size:14px;font-weight:600;"><?= htmlspecialchars($class_name) ?></div>
          </div>
        </div>
      </div>

      <form method="POST">
        <div class="row g-3">

          <div class="col-sm-6">
            <label class="form-label">Amount Requested (MWK) <span style="color:#dc2626;">*</span></label>
            <input type="number" name="amount" class="form-control" placeholder="e.g. 150000" required min="1000"
                   value="<?= htmlspecialchars($_POST['amount'] ?? '') ?>"/>
          </div>

          <div class="col-sm-6">
            <label class="form-label">Guardian's Occupation</label>
            <input type="text" name="guardian_job" class="form-control" placeholder="e.g. Farmer, Nurse"
                   value="<?= htmlspecialchars($_POST['guardian_job'] ?? '') ?>"/>
          </div>

          <div class="col-sm-6">
            <label class="form-label">Number of Siblings in School</label>
            <input type="number" name="siblings" class="form-control" placeholder="e.g. 2" min="0"
                   value="<?= htmlspecialchars($_POST['siblings'] ?? '') ?>"/>
          </div>

          <div class="col-12">
            <label class="form-label">Reason for Application <span style="color:#dc2626;">*</span></label>
            <textarea name="reason" class="form-control" rows="5" required
                      placeholder="Explain why you need financial assistance..."
                      style="resize:vertical;"><?= htmlspecialchars($_POST['reason'] ?? '') ?></textarea>
          </div>

          <div class="col-12">
            <label class="form-label">Additional Information</label>
            <textarea name="extra_info" class="form-control" rows="3"
                      placeholder="Any other relevant information..."
                      style="resize:vertical;"><?= htmlspecialchars($_POST['extra_info'] ?? '') ?></textarea>
          </div>

          <div class="col-12">
            <button type="submit" class="btn-enroll">
              <i class="fa fa-paper-plane"></i> Submit Application
            </button>
          </div>

        </div>
      </form>
    </div>
  </div>

  <!-- eligibility info -->
  <div class="col-lg-4 fade-up" style="transition-delay:.1s">
    <div class="card-box mb-4">
      <div class="card-box-header">
        <h4><i class="fa fa-circle-info me-2" style="color:var(--gold);"></i>Eligibility</h4>
      </div>
      <div style="display:flex;flex-direction:column;gap:12px;font-size:14px;">
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <i class="fa fa-check-circle" style="color:#15803d;margin-top:3px;flex-shrink:0;"></i>
          <span>Must be an enrolled, active student</span>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <i class="fa fa-check-circle" style="color:#15803d;margin-top:3px;flex-shrink:0;"></i>
          <span>Demonstrated financial need</span>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <i class="fa fa-check-circle" style="color:#15803d;margin-top:3px;flex-shrink:0;"></i>
          <span>Good academic standing (minimum grade C average)</span>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <i class="fa fa-check-circle" style="color:#15803d;margin-top:3px;flex-shrink:0;"></i>
          <span>No outstanding disciplinary issues</span>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <i class="fa fa-times-circle" style="color:#dc2626;margin-top:3px;flex-shrink:0;"></i>
          <span>Only one application per academic year</span>
        </div>
      </div>
    </div>

    <div class="card-box">
      <div class="card-box-header">
        <h4><i class="fa fa-phone me-2" style="color:var(--gold);"></i>Need Help?</h4>
      </div>
      <p style="font-size:14px;color:var(--muted);margin-bottom:14px;">Contact the bursar's office for assistance with your application.</p>
      <div style="font-size:13.5px;display:flex;flex-direction:column;gap:8px;">
        <div><i class="fa fa-envelope me-2" style="color:var(--gold);"></i>bursar@adaxy.mw</div>
        <div><i class="fa fa-phone me-2" style="color:var(--gold);"></i>+265 991 000 004</div>
        <div><i class="fa fa-clock me-2" style="color:var(--gold);"></i>Mon–Fri: 08:00–16:00</div>
      </div>
    </div>
  </div>

</div>

