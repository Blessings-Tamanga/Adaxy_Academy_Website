<?php 
$page_title = 'Admin Dashboard';
include '../includes/auth_check.php'; 
if ($role !== 'admin') { header('Location: ../Auth/login.php'); exit; }

// Dynamic stats from DB
$total_students = $conn->query("SELECT COUNT(*) as count FROM tblstudents")->fetch_assoc()['count'] ?? 0;
$total_teachers = $conn->query("SELECT COUNT(*) as count FROM tblteachers")->fetch_assoc()['count'] ?? 0;
$total_classes = $conn->query("SELECT COUNT(*) as count FROM tblclasses")->fetch_assoc()['count'] ?? 0;
$total_subjects = $conn->query("SELECT COUNT(*) as count FROM tblsubjects")->fetch_assoc()['count'] ?? 0;
?>
<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <h1>Admin Dashboard</h1>
    
    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white text-center">
                <div class="card-body">
                    <i class="fa fa-users fa-3x mb-3"></i>
                    <h2><?php echo $total_students; ?></h2>
                    <p>Total Students</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white text-center">
                <div class="card-body">
                    <i class="fa fa-chalkboard-teacher fa-3x mb-3"></i>
                    <h2><?php echo $total_teachers; ?></h2>
                    <p>Total Teachers</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white text-center">
                <div class="card-body">
                    <i class="fa fa-book fa-3x mb-3"></i>
                    <h2><?php echo $total_classes; ?></h2>
                    <p>Total Classes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white text-center">
                <div class="card-body">
                    <i class="fa fa-list fa-3x mb-3"></i>
                    <h2><?php echo $total_subjects; ?></h2>
                    <p>Total Subjects</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Management Actions -->
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="manage-students.php" class="btn btn-primary w-100 mb-2">Manage Students</a>
                    <a href="manage-teachers.php" class="btn btn-success w-100 mb-2">Manage Teachers</a>
                    <a href="manage-classes.php" class="btn btn-info w-100">Manage Classes</a>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Recent Activity</h5>
                </div>
                <div class="card-body">
                    <p>System ready. Login flow working. Dashboards coordinated.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

