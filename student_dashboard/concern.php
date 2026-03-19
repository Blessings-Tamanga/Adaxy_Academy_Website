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

// handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject  = trim($_POST['subject'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $message  = trim($_POST['message'] ?? '');

    if (!$subject || !$category || !$message) {
        $error = 'Please fill in all fields.';
    } else {
        // save concern as a notice sent to management
        // we store it in the notices table with posted_role = 'admin' for now
        // a proper concerns table can be added later
        $stmt = $conn->prepare("
            INSERT INTO notices (title, content, audience, posted_by, posted_role, is_published)
            VALUES (?, ?, 'teachers', ?, 'admin', 0)
        ");
        $title   = '[CONCERN] ' . $subject . ' — ' . $full_name . ' (' . $class_name . ')';
        $content = "Category: $category\n\n$message\n\nSubmitted by: $full_name | Roll: {$student['roll_number']} | Class: $class_name";
        $stmt->bind_param("sss", $title, $content, $full_name);

        if ($stmt->execute()) {
            $success = 'Your concern has been submitted successfully. Management will get back to you.';
        } else {
            $error = 'Something went wrong. Please try again.';
        }
        $stmt->close();
    }
}

// fetch previously submitted concerns
$prev = $conn->query("
    SELECT * FROM notices
    WHERE  posted_by = '{$conn->real_escape_string($full_name)}'
      AND  is_published = 0
    ORDER  BY created_at DESC
    LIMIT  5
")->fetch_all(MYSQLI_ASSOC);

$page_title = 'Raise a Concern';
$conn->close();

include 'includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
  <div>
    <div class="section-tag">Support</div>
    <h2 style="font-size:26px;margin-bottom:4px;">Raise a Concern</h2>
    <p style="color:var(--muted);">Submit an issue or concern to school management</p>
  </div>
</div>

<div class="row g-4">

  <!-- form -->
  <div class="col-lg-7 fade-up">
    <div class="card-box">
      <div class="card-box-header">
        <h4><i class="fa fa-triangle-exclamation me-2" style="color:var(--gold);"></i>Submit a Concern</h4>
      </div>

      <?php if ($success): ?>
        <div class="alert-success-custom"><i class="fa fa-check-circle me-2"></i><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert-error-custom"><i class="fa fa-circle-xmark me-2"></i><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Category</label>
          <select name="category" class="form-control form-select" required>
            <option value="">— Select a category —</option>
            <option value="Academic">Academic</option>
            <option value="Bullying / Harassment">Bullying / Harassment</option>
            <option value="Fees / Financial">Fees / Financial</option>
            <option value="Health / Wellbeing">Health / Wellbeing</option>
            <option value="Facilities">Facilities</option>
            <option value="Teacher Conduct">Teacher Conduct</option>
            <option value="Other">Other</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Subject</label>
          <input type="text" name="subject" class="form-control" placeholder="Brief title of your concern" required maxlength="150"
                 value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>"/>
        </div>

        <div class="mb-4">
          <label class="form-label">Message</label>
          <textarea name="message" class="form-control" rows="6"
                    placeholder="Describe your concern in detail..." required
                    style="resize:vertical;"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn-enroll">
          <i class="fa fa-paper-plane"></i> Submit Concern
        </button>
      </form>
    </div>
  </div>

  <!-- info + previous -->
  <div class="col-lg-5 fade-up" style="transition-delay:.1s">
    <div class="card-box mb-4">
      <div class="card-box-header">
        <h4><i class="fa fa-circle-info me-2" style="color:var(--gold);"></i>What happens next?</h4>
      </div>
      <div style="display:flex;flex-direction:column;gap:14px;">
        <div style="display:flex;gap:12px;align-items:flex-start;">
          <div style="width:32px;height:32px;background:var(--navy);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <span style="color:var(--gold);font-weight:700;font-size:13px;">1</span>
          </div>
          <div>
            <div style="font-weight:600;font-size:14px;">Your concern is submitted</div>
            <div style="font-size:13px;color:var(--muted);">It is sent directly to school management.</div>
          </div>
        </div>
        <div style="display:flex;gap:12px;align-items:flex-start;">
          <div style="width:32px;height:32px;background:var(--navy);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <span style="color:var(--gold);font-weight:700;font-size:13px;">2</span>
          </div>
          <div>
            <div style="font-weight:600;font-size:14px;">Management reviews it</div>
            <div style="font-size:13px;color:var(--muted);">Within 2–3 working days.</div>
          </div>
        </div>
        <div style="display:flex;gap:12px;align-items:flex-start;">
          <div style="width:32px;height:32px;background:var(--navy);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <span style="color:var(--gold);font-weight:700;font-size:13px;">3</span>
          </div>
          <div>
            <div style="font-weight:600;font-size:14px;">You will be contacted</div>
            <div style="font-size:13px;color:var(--muted);">Via your parent's contact or in person.</div>
          </div>
        </div>
      </div>
    </div>

    <?php if ($prev): ?>
    <div class="card-box">
      <div class="card-box-header">
        <h4><i class="fa fa-clock-rotate-left me-2" style="color:var(--gold);"></i>Previously Submitted</h4>
      </div>
      <?php foreach ($prev as $p): ?>
      <div style="padding:10px 0;border-bottom:1px solid var(--border);">
        <div style="font-weight:600;font-size:13.5px;color:var(--navy);"><?= htmlspecialchars(str_replace('[CONCERN] ', '', $p['title'])) ?></div>
        <div style="font-size:12px;color:var(--muted);"><?= date('d M Y', strtotime($p['created_at'])) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

</div>

