<?php
$page_title = "My Results";
include '../includes/auth_check.php';
if ($role !== 'student') { header('Location: ../Auth/login.php'); exit; }

$slogin = $_SESSION['slogin'];
$stmt = $conn->prepare("SELECT Subject, Score, GPA, Term, Year FROM tblresult WHERE RollId=? ORDER BY Year DESC, Term DESC");
$stmt->bind_param("s", $slogin);
$stmt->execute();
$results = $stmt->get_result();

include '../includes/header.php';
?>

<h1>My Results</h1>
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>Subject</th>
            <th>Score</th>
            <th>GPA</th>
            <th>Term</th>
            <th>Year</th>
        </tr>
    </thead>
    <tbody>
        <?php if($results->num_rows):
            while($row = $results->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['Subject']) ?></td>
                <td><?= htmlspecialchars($row['Score']) ?></td>
                <td><?= htmlspecialchars($row['GPA']) ?></td>
                <td><?= htmlspecialchars($row['Term']) ?></td>
                <td><?= htmlspecialchars($row['Year']) ?></td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="5" class="text-center">No results available yet.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>