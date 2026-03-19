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

$full_name  = $student['first_name'] . ' ' . $student['last_name'];
$first_name = $student['first_name'];
$class_name = $student['class_name'] ?? 'N/A';
$initials   = strtoupper(substr($student['first_name'],0,1) . substr($student['last_name'],0,1));

// fetch notices for students
$notices = $conn->query("
    SELECT * FROM notices
    WHERE  is_published = 1
      AND  (audience = 'students' OR audience = 'all')
    ORDER  BY created_at DESC
")->fetch_all(MYSQLI_ASSOC);

function time_ago(string $dt): string {
    $diff = time() - strtotime($dt);
    if ($diff < 3600)  return round($diff/60)  . 'm ago';
    if ($diff < 86400) return round($diff/3600) . 'h ago';
    return round($diff/86400) . 'd ago';
}

$audience_colors = [
    'all'      => ['bg'=>'#dbeafe','color'=>'#1d4ed8','label'=>'All'],
    'students' => ['bg'=>'#dcfce7','color'=>'#15803d','label'=>'Students'],
    'teachers' => ['bg'=>'#ede9fe','color'=>'#6d28d9','label'=>'Teachers'],
    'parents'  => ['bg'=>'#ffedd5','color'=>'#c2410c','label'=>'Parents'],
];

$page_title = 'Notices';
$conn->close();

include 'includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
  <div>
    <div class="section-tag">Announcements</div>
    <h2 style="font-size:26px;margin-bottom:4px;">Notices</h2>
    <p style="color:var(--muted);"><?= count($notices) ?> notice<?= count($notices)!==1?'s':'' ?> available</p>
  </div>
</div>

<?php if ($notices): ?>
  <div class="row g-4">
    <?php foreach ($notices as $i => $n):
      $aud = $audience_colors[$n['audience']] ?? $audience_colors['all'];
    ?>
    <div class="col-12 fade-up" style="transition-delay:<?= $i * 0.05 ?>s">
      <div class="card-box" style="padding:24px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
          <div style="flex:1;min-width:0;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;flex-wrap:wrap;">
              <h5 style="font-size:16px;font-weight:700;color:var(--navy);margin:0;"><?= htmlspecialchars($n['title']) ?></h5>
              <span style="background:<?= $aud['bg'] ?>;color:<?= $aud['color'] ?>;font-size:11px;font-weight:700;padding:2px 10px;border-radius:50px;">
                <?= $aud['label'] ?>
              </span>
            </div>
            <p style="color:var(--text);font-size:14px;line-height:1.7;margin-bottom:12px;"><?= nl2br(htmlspecialchars($n['content'])) ?></p>
            <div style="display:flex;gap:16px;font-size:12.5px;color:var(--muted);flex-wrap:wrap;">
              <span><i class="fa fa-user me-1" style="color:var(--gold);"></i><?= htmlspecialchars($n['posted_by']) ?></span>
              <span><i class="fa fa-clock me-1" style="color:var(--gold);"></i><?= time_ago($n['created_at']) ?></span>
              <span><i class="fa fa-calendar me-1" style="color:var(--gold);"></i><?= date('d M Y', strtotime($n['created_at'])) ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="card-box text-center" style="padding:50px;">
    <i class="fa fa-bell-slash" style="font-size:40px;color:var(--border);display:block;margin-bottom:12px;"></i>
    <p style="color:var(--muted);">No notices at the moment. Check back later.</p>
  </div>
<?php endif; ?>

