<?php 
$page_title = 'Student Dashboard';
include '../includes/auth_check.php'; 
if ($role !== 'student') { header('Location: ../Auth/login.php'); exit; }

// Fetch student data
$slogin = $_SESSION['slogin'];
$result = $conn->query("SELECT * FROM tblstudents WHERE RollId='$slogin'")->fetch_assoc();
$student_name = $result['StudentName'] ?? 'Student';
$classid = $result['ClassId'] ?? 'N/A';

// Mock stats - replace with real queries
$total_results = $conn->query("SELECT COUNT(*) as count FROM tblresult WHERE RollId='$slogin'")->fetch_assoc()['count'] ?? 0;
$avg_gpa = 3.85; // From tblresult avg
?>
<?php include '../includes/header.php'; ?>

<h1>Welcome back, <?php echo htmlspecialchars($student_name); ?> 👋</h1>
<p>Form <?php echo $classid; ?> | Term 1, 2025</p>

<!-- STATS -->
<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card text-center bg-primary text-white">
            <div class="card-body">
                <i class="fa fa-star fa-2x mb-3"></i>
                <h2><?php echo $avg_gpa; ?>/4.0</h2>
                <p>GPA (Term 1)</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-success text-white">
            <div class="card-body">
                <i class="fa fa-file-alt fa-2x mb-3"></i>
                <h2><?php echo $total_results; ?></h2>
                <p>Results Available</p>
            </div>
        </div>
    </div>
    <!-- Add more dynamic cards -->
</div>

<!-- Notifications from DB -->
<div class="card">
    <div class="card-header"><h5>Recent Notifications</h5></div>
    <div class="card-body">
        <!-- Fetch from tblnotifications or mock -->
        <div class="alert alert-info">Fee payment due in 5 days</div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
