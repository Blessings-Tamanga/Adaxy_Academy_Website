<?php
$page_title = "Student Actions";
include '../includes/auth_check.php';
if ($role !== 'student') { header('Location: ../Auth/login.php'); exit; }

$slogin = $_SESSION['slogin'];

// Fetch student's courses for withdrawal
$courses_stmt = $conn->prepare("SELECT CourseCode, CourseName FROM tblenrollments WHERE RollId=?");
$courses_stmt->bind_param("s", $slogin);
$courses_stmt->execute();
$courses = $courses_stmt->get_result();

include '../includes/header.php';
?>

<h1>Student Actions</h1>
<div class="accordion" id="actionsAccordion">

<!-- Withdraw -->
<div class="accordion-item">
  <h2 class="accordion-header">
    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#withdraw">
      Withdraw From Course
    </button>
  </h2>
  <div id="withdraw" class="accordion-collapse collapse show">
    <div class="accordion-body">
      <form method="POST" action="process_withdraw.php">
        <select name="course" class="form-select mb-2" required>
          <option value="">Select a course</option>
          <?php while($c = $courses->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($c['CourseCode']) ?>"><?= htmlspecialchars($c['CourseName']) ?></option>
          <?php endwhile; ?>
        </select>
        <button type="submit" class="btn btn-danger">Submit Withdrawal</button>
      </form>
    </div>
  </div>
</div>

<!-- Scholarship -->
<div class="accordion-item">
  <h2 class="accordion-header">
    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#scholar">
      Apply for Scholarship / Bursary
    </button>
  </h2>
  <div id="scholar" class="accordion-collapse collapse">
    <div class="accordion-body">
      <form method="POST" action="process_scholar.php">
        <input type="text" name="type" class="form-control mb-2" placeholder="Scholarship Type" required>
        <textarea name="reason" class="form-control mb-2" placeholder="Reason why you qualify..." required></textarea>
        <button type="submit" class="btn btn-success">Submit Application</button>
      </form>
    </div>
  </div>
</div>

<!-- Raise Concern -->
<div class="accordion-item">
  <h2 class="accordion-header">
    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#concern">
      Raise Concern
    </button>
  </h2>
  <div id="concern" class="accordion-collapse collapse">
    <div class="accordion-body">
      <form method="POST" action="process_concern.php">
        <textarea name="concern" class="form-control mb-2" placeholder="Type your concern here..." required></textarea>
        <button type="submit" class="btn btn-warning">Submit Concern</button>
      </form>
    </div>
  </div>
</div>

</div>
<?php include '../includes/footer.php'; ?>