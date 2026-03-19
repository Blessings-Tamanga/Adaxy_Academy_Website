<?php
// ============================================================
//  Adaxy Academy · Student Dashboard
// ============================================================
session_start();
include('../config/db_connect.php');

// ── Auth guard ───────────────────────────────────────────────
if (empty($_SESSION['slogin'])) {
    header('Location: ../Auth/login.php?role=student');
    exit;
}

$username = $_SESSION['slogin'];

// ── Fetch student + class info ───────────────────────────────
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
$initials   = strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1));

// ── Years until graduation ───────────────────────────────────
// JCE ends at Form 4, MSCE ends at Form 6
$end_form       = ($programme === 'JCE') ? 4 : 6;
$years_left     = max(0, $end_form - $form_level);

// ── Test grades (CA) this term ───────────────────────────────
$stmt = $conn->prepare("
    SELECT g.*, s.subject_name
    FROM   grades g
    JOIN   subjects s ON s.subject_id = g.subject_id
    WHERE  g.student_id = ?
      AND  g.grade_type = 'test'
    ORDER  BY g.created_at DESC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$test_grades = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── End of term grades ───────────────────────────────────────
$stmt = $conn->prepare("
    SELECT g.*, s.subject_name
    FROM   grades g
    JOIN   subjects s ON s.subject_id = g.subject_id
    WHERE  g.student_id = ?
      AND  g.grade_type = 'end_of_term'
    ORDER  BY g.created_at DESC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$eot_grades = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── GPA calculation (average of total_score / 25 capped at 4.0) ─
$all_grades   = array_merge($test_grades, $eot_grades);
$gpa          = 0;
if (count($all_grades) > 0) {
    $avg_score = array_sum(array_column($all_grades, 'total_score')) / count($all_grades);
    $gpa       = round(min(4.0, $avg_score / 25), 2);
}

// ── Latest test grade for quick preview ──────────────────────
$latest_grade = !empty($test_grades) ? $test_grades[0] : null;

// ── Notices for students ─────────────────────────────────────
$notices = $conn->query("
    SELECT * FROM notices
    WHERE  is_published = 1
      AND  (audience = 'students' OR audience = 'all')
    ORDER  BY created_at DESC
    LIMIT  3
")->fetch_all(MYSQLI_ASSOC);

// ── Pending actions = unread notices ─────────────────────────
$pending_count   = count($notices);
$notice_count    = count($notices);

// ── Helper: time ago ─────────────────────────────────────────
function time_ago(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 3600)  return round($diff / 60) . 'm ago';
    if ($diff < 86400) return round($diff / 3600) . 'h ago';
    return round($diff / 86400) . 'd ago';
}

// ── Notice icons ─────────────────────────────────────────────
$notice_icons = ['fa-file-invoice', 'fa-flask', 'fa-trophy', 'fa-bell', 'fa-people-group'];

$conn->close();

?>
<?php include "includes/header.php"; ?>
<button class="sidebar-toggle" id="sidebarToggle">
  <i class="fa fa-bars me-2"></i> Menu
</button>

<div class="dashboard-wrapper">

      <?php if ($notices): ?>
        <?php foreach ($notices as $i => $notice): ?>
        <div class="notif-item">
          <div class="notif-icon">
            <i class="fa <?= $notice_icons[$i % count($notice_icons)] ?>"></i>
          </div>
          <div style="flex:1;min-width:0;">
            <div style="font-weight:600;color:var(--navy);"><?= htmlspecialchars($notice['title']) ?></div>
            <div style="font-size:13px;color:var(--muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
              <?= htmlspecialchars(mb_substr($notice['content'], 0, 80)) ?>…
            </div>
          </div>
          <div style="margin-left:auto;font-size:12px;color:var(--gold);white-space:nowrap;padding-left:10px;">
            <?= time_ago($notice['created_at']) ?>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="notif-item">
          <div class="notif-icon"><i class="fa fa-check-circle"></i></div>
          <div>
            <div style="font-weight:600;color:var(--navy);">All caught up!</div>
            <div style="font-size:13px;color:var(--muted);">No new notifications right now.</div>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- ── THREE COLUMN ROW ───────────────────────────────── -->
    <div class="row g-4 mb-5">

      <!-- Actions -->
      <div class="col-lg-4 fade-up">
        <div class="dropdown-card h-100">
          <div class="dropdown-header">
            <h4><i class="fa fa-gear me-2" style="color:var(--gold);"></i> Actions</h4>
            <i class="fa fa-chevron-down" style="color:var(--muted);"></i>
          </div>
          <ul class="dropdown-menu-custom">
            <li><a href="withdraw.php"><i class="fa fa-arrow-right-from-bracket"></i> Withdraw from course</a></li>
            <li><a href="concern.php"><i class="fa fa-triangle-exclamation"></i> Raise a concern</a></li>
            <li><a href="bursary.php"><i class="fa fa-hand-holding-heart"></i> Apply for bursary</a></li>
            <li><a href="timetable.php"><i class="fa fa-users"></i> View my timetable</a></li>
          </ul>
          <div class="mt-3 small text-muted ps-3">Select an action to proceed.</div>
        </div>
      </div>

      <!-- Exam results -->
      <div class="col-lg-4 fade-up" style="transition-delay:.1s">
        <div class="dropdown-card h-100">
          <div class="dropdown-header">
            <h4><i class="fa fa-file-lines me-2" style="color:var(--gold);"></i> Exam results</h4>
            <i class="fa fa-chevron-down" style="color:var(--muted);"></i>
          </div>
          <ul class="dropdown-menu-custom">
            <li><a href="grades.php?type=end_of_term"><i class="fa fa-calendar-check"></i> End of term results</a></li>
            <li><a href="grades.php?type=test"><i class="fa fa-pencil"></i> Test &amp; quiz scores</a></li>
            <li><a href="grades.php"><i class="fa fa-chart-simple"></i> Overall summary</a></li>
          </ul>

          <!-- quick preview from DB -->
          <div style="background:var(--cream);border-radius:12px;padding:12px;margin-top:10px;">
            <?php if ($latest_grade): ?>
              <div style="font-size:13px;font-weight:600;">
                Latest: <?= htmlspecialchars($latest_grade['subject_name']) ?>
                <?= $latest_grade['total_score'] ?>%
              </div>
              <?php if (count($test_grades) > 1): ?>
              <div style="font-size:12px;color:var(--muted);">
                <?= htmlspecialchars($test_grades[1]['subject_name'] ?? '') ?>
                <?= $test_grades[1]['total_score'] ?? '' ?>%
                <?php if (!empty($test_grades[2])): ?>
                  · <?= htmlspecialchars($test_grades[2]['subject_name']) ?>
                  <?= $test_grades[2]['total_score'] ?>%
                <?php endif; ?>
              </div>
              <?php endif; ?>
            <?php else: ?>
              <div style="font-size:13px;color:var(--muted);">No grades recorded yet.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Fees placeholder (no fees table yet) -->
      <div class="col-lg-4 fade-up" style="transition-delay:.2s">
        <div class="fee-card h-100">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;">
            <i class="fa fa-coins" style="color:var(--gold);font-size:24px;"></i>
            <h4 style="color:white;margin:0;">Fees &amp; funding</h4>
          </div>
          <div class="fee-row">
            <span class="fee-label">Programme</span>
            <span class="fee-value"><?= htmlspecialchars($programme) ?></span>
          </div>
          <div class="fee-row">
            <span class="fee-label">Class</span>
            <span class="fee-value"><?= htmlspecialchars($class_name) ?></span>
          </div>
          <div class="fee-row">
            <span class="fee-label">Roll Number</span>
            <span class="fee-value"><?= htmlspecialchars($student['roll_number']) ?></span>
          </div>
          <div class="fee-row">
            <span class="fee-label">Status</span>
            <span class="fee-value" style="color:#10b981;">Active</span>
          </div>
          <div style="margin-top:20px;">
            <span class="fee-tag"><i class="fa fa-user-check me-1"></i> Enrolled</span>
          </div>
          <a href="bursary.php" class="btn-enroll w-100 mt-4 justify-content-center"
             style="background:var(--gold-light);color:var(--navy) !important;">
            <i class="fa fa-hand-holding-heart"></i> Apply for bursary
          </a>
        </div>
      </div>
    </div>

    <!-- ── GRADES TABLE ───────────────────────────────────── -->
    <?php if ($test_grades || $eot_grades): ?>
    <div class="dropdown-card mb-5 fade-up">
      <div class="dropdown-header">
        <h4><i class="fa fa-graduation-cap me-2" style="color:var(--gold);"></i> My grades</h4>
        <a href="grades.php" style="font-size:13px;color:var(--gold);">View all →</a>
      </div>

      <table class="grade-table">
        <thead>
          <tr>
            <th>Subject</th>
            <th>Type</th>
            <th>CA1</th>
            <th>CA2</th>
            <th>CA3</th>
            <th>Exam</th>
            <th>Total</th>
            <th>Grade</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $display_grades = array_slice(array_merge($eot_grades, $test_grades), 0, 8);
          foreach ($display_grades as $g):
            $letter = $g['letter_grade'] ?? 'N/A';
            $badge  = match($letter) {
                'A'   => 'badge-a',
                'B'   => 'badge-b',
                'C'   => 'badge-c',
                'D'   => 'badge-d',
                default => 'badge-f'
            };
          ?>
          <tr>
            <td><strong><?= htmlspecialchars($g['subject_name']) ?></strong></td>
            <td style="font-size:12px;color:var(--muted);">
              <?= $g['grade_type'] === 'end_of_term' ? 'End of Term' : 'Test' ?>
            </td>
            <td><?= $g['ca1_score'] ?></td>
            <td><?= $g['ca2_score'] ?></td>
            <td><?= $g['ca3_score'] ?></td>
            <td><?= $g['exam_score'] ?></td>
            <td><strong><?= $g['total_score'] ?>%</strong></td>
            <td><span class="badge-grade <?= $badge ?>"><?= htmlspecialchars($letter) ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

    <!-- footer -->
    <div style="margin-top:40px;color:var(--muted);font-size:12px;text-align:center;border-top:1px dashed var(--border);padding-top:24px;">
      <i class="fa fa-lock me-1" style="color:var(--gold);"></i>
      secure student dashboard · Adaxy Academy · <?= date('Y') ?>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
  // sidebar toggle
  const btn     = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  if (btn) btn.addEventListener('click', () => sidebar.classList.toggle('show'));

  // fade observer
  const obs = new IntersectionObserver((entries) => {
    entries.forEach(en => {
      if (en.isIntersecting) { en.target.classList.add('visible'); obs.unobserve(en.target); }
    });
  }, { threshold: 0.1 });
  document.querySelectorAll('.fade-up').forEach(el => obs.observe(el));
})();
</script>
</body>
</html>