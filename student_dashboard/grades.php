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

// filter by type
$filter = $_GET['type'] ?? 'all';
$allowed = ['all', 'test', 'end_of_term'];
if (!in_array($filter, $allowed)) $filter = 'all';

// build query based on filter
if ($filter === 'all') {
    $stmt = $conn->prepare("
        SELECT g.*, sub.subject_name, sub.subject_code
        FROM   grades g
        JOIN   subjects sub ON sub.subject_id = g.subject_id
        WHERE  g.student_id = ?
        ORDER  BY g.grade_type, sub.subject_name
    ");
    $stmt->bind_param("i", $student_id);
} else {
    $stmt = $conn->prepare("
        SELECT g.*, sub.subject_name, sub.subject_code
        FROM   grades g
        JOIN   subjects sub ON sub.subject_id = g.subject_id
        WHERE  g.student_id = ? AND g.grade_type = ?
        ORDER  BY sub.subject_name
    ");
    $stmt->bind_param("is", $student_id, $filter);
}
$stmt->execute();
$grades = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// overall average
$avg = count($grades) > 0
    ? round(array_sum(array_column($grades, 'total_score')) / count($grades), 1)
    : 0;

$page_title = 'My Grades';

$conn->close();

function grade_badge(string $letter): string {
    return match($letter) {
        'A'     => 'badge-a',
        'B'     => 'badge-b',
        'C'     => 'badge-c',
        'D'     => 'badge-d',
        default => 'badge-f'
    };
}

include 'includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
  <div>
    <div class="section-tag">Academics</div>
    <h2 style="font-size:26px;margin-bottom:4px;">My Grades</h2>
    <p style="color:var(--muted);">All your recorded grades · <?= htmlspecialchars($class_name) ?></p>
  </div>
  <div class="d-flex gap-2 mt-2 mt-md-0">
    <a href="grades.php?type=all"          class="btn-outline-gold <?= $filter==='all'?'active':'' ?>"         style="<?= $filter==='all'?'background:var(--gold);color:white;':'' ?>">All</a>
    <a href="grades.php?type=test"         class="btn-outline-gold <?= $filter==='test'?'active':'' ?>"        style="<?= $filter==='test'?'background:var(--gold);color:white;':'' ?>">Tests</a>
    <a href="grades.php?type=end_of_term"  class="btn-outline-gold <?= $filter==='end_of_term'?'active':'' ?>" style="<?= $filter==='end_of_term'?'background:var(--gold);color:white;':'' ?>">End of Term</a>
  </div>
</div>

<!-- summary stat -->
<div class="row g-4 mb-4 fade-up">
  <div class="col-sm-4">
    <div class="card-box text-center">
      <div style="font-size:36px;font-weight:700;color:var(--navy);"><?= $avg ?>%</div>
      <div style="font-size:13px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;">Overall Average</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="card-box text-center">
      <div style="font-size:36px;font-weight:700;color:var(--navy);"><?= count($grades) ?></div>
      <div style="font-size:13px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;">Grades Recorded</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="card-box text-center">
      <?php
        $a_count = count(array_filter($grades, fn($g) => $g['letter_grade'] === 'A'));
      ?>
      <div style="font-size:36px;font-weight:700;color:#15803d;"><?= $a_count ?></div>
      <div style="font-size:13px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;">A Grades</div>
    </div>
  </div>
</div>

<!-- grades table -->
<div class="card-box fade-up">
  <div class="card-box-header">
    <h4>
      <i class="fa fa-table me-2" style="color:var(--gold);"></i>
      <?= $filter === 'end_of_term' ? 'End of Term Results' : ($filter === 'test' ? 'Test Scores' : 'All Grades') ?>
    </h4>
    <span style="font-size:13px;color:var(--muted);"><?= count($grades) ?> record<?= count($grades)!==1?'s':'' ?></span>
  </div>

  <?php if ($grades): ?>
  <div style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr style="border-bottom:2px solid var(--border);">
          <th style="padding:10px 12px;font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;text-align:left;">Subject</th>
          <th style="padding:10px 12px;font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;text-align:left;">Type</th>
          <th style="padding:10px 12px;font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;text-align:center;">CA1</th>
          <th style="padding:10px 12px;font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;text-align:center;">CA2</th>
          <th style="padding:10px 12px;font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;text-align:center;">CA3</th>
          <th style="padding:10px 12px;font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;text-align:center;">Exam</th>
          <th style="padding:10px 12px;font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;text-align:center;">Total</th>
          <th style="padding:10px 12px;font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;text-align:center;">Grade</th>
          <th style="padding:10px 12px;font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;text-align:left;">Term</th>
          <th style="padding:10px 12px;font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;text-align:left;">Remarks</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($grades as $g):
          $letter = $g['letter_grade'] ?? 'N/A';
          $badge  = grade_badge($letter);
        ?>
        <tr style="border-bottom:1px solid var(--border);">
          <td style="padding:12px;font-weight:600;color:var(--navy);"><?= htmlspecialchars($g['subject_name']) ?></td>
          <td style="padding:12px;">
            <span class="badge-pill" style="background:<?= $g['grade_type']==='end_of_term'?'#ede9fe':'#dbeafe' ?>;color:<?= $g['grade_type']==='end_of_term'?'#6d28d9':'#1d4ed8' ?>;">
              <?= $g['grade_type'] === 'end_of_term' ? 'End of Term' : 'Test' ?>
            </span>
          </td>
          <td style="padding:12px;text-align:center;"><?= $g['ca1_score'] ?></td>
          <td style="padding:12px;text-align:center;"><?= $g['ca2_score'] ?></td>
          <td style="padding:12px;text-align:center;"><?= $g['ca3_score'] ?></td>
          <td style="padding:12px;text-align:center;"><?= $g['exam_score'] ?></td>
          <td style="padding:12px;text-align:center;font-weight:700;color:var(--navy);"><?= $g['total_score'] ?>%</td>
          <td style="padding:12px;text-align:center;"><span class="badge-pill <?= $badge ?>"><?= htmlspecialchars($letter) ?></span></td>
          <td style="padding:12px;color:var(--muted);font-size:13px;">Term <?= $g['term'] ?> · <?= $g['academic_year'] ?></td>
          <td style="padding:12px;color:var(--muted);font-size:13px;"><?= htmlspecialchars($g['remarks'] ?? '—') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
    <div style="text-align:center;padding:40px;color:var(--muted);">
      <i class="fa fa-file-circle-xmark" style="font-size:36px;color:var(--border);display:block;margin-bottom:12px;"></i>
      No grades recorded yet for this filter.
    </div>
  <?php endif; ?>
</div>

<?php include 'incudes/footer.php'; ?>