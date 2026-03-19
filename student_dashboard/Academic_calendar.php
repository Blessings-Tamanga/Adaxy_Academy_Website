<?php
$page_title = "Academic Calendar";
include '../includes/auth_check.php';
if ($role !== 'student') { header('Location: ../Auth/login.php'); exit; }

$events = $conn->query("SELECT * FROM tblcalendar ORDER BY Date ASC");

include '../includes/header.php';
?>

<h1>Academic Calendar</h1>
<table class="table table-bordered table-hover">
  <thead>
    <tr>
      <th>Date</th>
      <th>Event</th>
      <th>Type</th>
    </tr>
  </thead>
  <tbody>
    <?php if($events->num_rows):
      while($row = $events->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['Date']) ?></td>
        <td><?= htmlspecialchars($row['Event']) ?></td>
        <td><?= htmlspecialchars($row['Type']) ?></td>
      </tr>
    <?php endwhile; else: ?>
      <tr><td colspan="3" class="text-center">No events scheduled yet.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php include '../includes/footer.php'; ?>