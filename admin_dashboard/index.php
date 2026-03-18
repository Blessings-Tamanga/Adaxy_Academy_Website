<?php 
$page_title = 'Admin Dashboard';
include '../includes/auth_check.php'; 
if ($role !== 'admin') { header('Location: ../Auth/login.php'); exit; }
?>
<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <h1>Admin Dashboard</h1>
    <p>Advanced admin tools coming soon...</p>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5>Quick Links</h5>
                    <a href="../management_dashboard/" class="btn btn-primary d-block mb-2">Management View</a>
                    <a href="../teacher_dashboard/manage-students.php" class="btn btn-success d-block mb-2">Students</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

