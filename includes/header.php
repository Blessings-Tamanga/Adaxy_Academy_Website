<?php
session_start();
require_once '../config/db_connect.php';

function getRole() {
    if (isset($_SESSION['alogin'])) return 'admin';
    if (isset($_SESSION['tlogin'])) return 'teacher';
    if (isset($_SESSION['slogin'])) return 'student';
    return null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adaxy Academy | <?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Design system from public index.php */
        :root { --navy: #0F172A; --gold: #2563EB; --cream: #F8FAFC; /* ... rest preserved */ }
        /* Sidebar & dashboard styles here - abbreviated for brevity */
        .dashboard-wrapper { display: flex; min-height: 100vh; }
        /* Full styles from analysis will be inline/ linked */
    </style>
</head>
<body>
<?php if (getRole()): ?>
<nav class="navbar navbar-expand-lg" style="background: var(--navy); color: white;">
    <div class="container">
        <a class="navbar-brand" href="#"><i class="fa fa-graduation-cap"></i> Adaxy Academy</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="?logout=1"><i class="fa fa-sign-out"></i> Logout</a>
        </div>
    </div>
</nav>
<?php endif; ?>
<div class="dashboard-wrapper">
<?php if (getRole()): ?>
    <!-- Sidebar based on role -->
    <div class="sidebar bg-dark text-white p-3" style="width: 250px;">
        <?php 
        $role = getRole();
        if ($role == 'student'): ?>
            <a href="../student_dashboard/" class="d-block p-2">Dashboard</a>
            <a href="#" class="d-block p-2">Results</a>
        <?php elseif ($role == 'teacher'): ?>
            <a href="../teacher_dashboard/" class="d-block p-2">Dashboard</a>
            <a href="#" class="d-block p-2">Students</a>
        <?php else: ?>
            <a href="../management_dashboard/" class="d-block p-2">Dashboard</a>
            <a href="#" class="d-block p-2">Students</a>
            <a href="#" class="d-block p-2">Teachers</a>
        <?php endif; ?>
    </div>
<?php endif; ?>
<div class="flex-grow-1 p-4">

