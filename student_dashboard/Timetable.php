<?php
session_start();
include('../config/db_connect.php');

if (empty($_SESSION['slogin'])) { header('Location: ../Auth/login.php?role=student'); exit; }

$username = $_SESSION['slogin'];

$stmt = $conn->prepare("
    SELECT s.*, c.class_name, c.form_level, c.programme, c.class_id
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

$full_name  = $student['first_name'] . ' ' . $student['last_name'];
$first_name = $student['first_name'];
$class_name = $student['class_name'] ?? 'N/A';
$class_id   = (int)$student['class_id'];
$initials   = strtoupper(substr($student['first_name'],0,1) . substr($student['last_name'],0,1));

// fetch full timetable for this class
$stmt = $conn->prepare("
    SELECT t.*, sub.subject_name, sub.subject_code,
           CONCAT(te.first_name, ' ', te.last_name) AS teacher_name
    FROM   timetable t
    JOIN   subjects  sub ON sub.subject_id = t.subject_id
    JOIN   teachers  te  ON te.teacher_id  = t.teacher_id
    WHERE  t.class_id = ?
    ORDER  BY FIELD(t.day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday'), t.period_no
");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$all_periods = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// group by day
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday'];
$timetable = [];
foreach ($days as $day) {
    $timetable[$day] = array_filter($all_periods, fn($p) => $p['day_of_week'] === $day);
}

$today = date('l'); // e.g. Monday

$day_colors = [
    'Monday'    => '#dbeafe',
    'Tuesday'   => '#dcfce7',
    'Wednesday' => '#fef9c3',
    'Thursday'  => '#ede9fe',
    'Friday'    => '#ffedd5',
];

$page_title = 'Timetable';
$conn->close();

include 'includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
  <div>
    <div class="section-tag">Schedule</div>
    <h2 style="font-size:26px;margin-bottom:4px;">My Timetable</h2>
    <p style="color:var(--muted);"><?= htmlspecialchars($class_name) ?> · Academic Year <?= date('Y') ?></p>
  </div>
  <div style="background:var(--white);border:1px solid var(--border);border-radius:50px;padding:8px 20px;font-size:13px;font-weight:600;color:var(--navy);">
    <i class="fa fa-calendar-day me-2" style="color:var(--gold);"></i>Today: <?= $today ?>
  </div>
</div>

<?php if (!$all_periods): ?>
  <div class="card-box text-center" style="padding:50px;">
    <i class="fa fa-calendar-xmark" style="font-size:40px;color:var(--border);display:block;margin-bottom:12px;"></i>
    <p style="color:var(--muted);">No timetable has been set for your class yet.</p>
  </div>
<?php else: ?>

  <?php foreach ($days as $day):
    $periods = array_values($timetable[$day]);
    $is_today = ($day === $today);
    $color    = $day_colors[$day];
  ?>
  <div class="card-box mb-4 fade-up" style="<?= $is_today ? 'border-color:var(--gold);border-width:2px;' : '' ?>">
    <div class="card-box-header">
      <h4>
        <span style="display:inline-block;width:12px;height:12px;background:<?= $color ?>;border-radius:50%;margin-right:10px;border:2px solid <?= $is_today ? 'var(--gold)' : 'var(--border)' ?>;"></span>
        <?= $day ?>
        <?php if ($is_today): ?>
          <span style="background:var(--gold);color:var(--navy);font-size:11px;font-weight:700;padding:2px 10px;border-radius:50px;margin-left:8px;">TODAY</span>
        <?php endif; ?>
      </h4>
      <span style="font-size:13px;color:var(--muted);"><?= count($periods) ?> period<?= count($periods)!==1?'s':'' ?></span>
    </div>

    <?php if ($periods): ?>
    <div style="overflow-x:auto;">
      <table style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="border-bottom:1px solid var(--border);">
            <th style="padding:8px 12px;font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;text-align:left;">Period</th>
            <th style="padding:8px 12px;font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;text-align:left;">Subject</th>
            <th style="padding:8px 12px;font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;text-align:left;">Teacher</th>
            <th style="padding:8px 12px;font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;text-align:left;">Time</th>
            <th style="padding:8px 12px;font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;text-align:left;">Room</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($periods as $p):
            $now     = date('H:i');
            $is_now  = $is_today && $now >= date('H:i', strtotime($p['start_time'])) && $now <= date('H:i', strtotime($p['end_time']));
          ?>
          <tr style="border-bottom:1px solid var(--border);<?= $is_now ? 'background:#f0f9ff;' : '' ?>">
            <td style="padding:12px;">
              <span style="background:var(--navy);color:var(--gold);font-size:11px;font-weight:700;padding:3px 10px;border-radius:50px;">P<?= $p['period_no'] ?></span>
            </td>
            <td style="padding:12px;font-weight:600;color:var(--navy);">
              <?= htmlspecialchars($p['subject_name']) ?>
              <?php if ($is_now): ?>
                <span style="background:#dcfce7;color:#15803d;font-size:10px;font-weight:700;padding:2px 8px;border-radius:50px;margin-left:6px;">NOW</span>
              <?php endif; ?>
            </td>
            <td style="padding:12px;color:var(--muted);font-size:14px;"><?= htmlspecialchars($p['teacher_name']) ?></td>
            <td style="padding:12px;color:var(--muted);font-size:13.5px;">
              <?= date('H:i', strtotime($p['start_time'])) ?> – <?= date('H:i', strtotime($p['end_time'])) ?>
            </td>
            <td style="padding:12px;color:var(--muted);font-size:13.5px;"><?= htmlspecialchars($p['room_no'] ?? '—') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
      <p style="color:var(--muted);font-size:14px;padding:12px 0;">No classes on <?= $day ?>.</p>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>

<?php endif; ?>
