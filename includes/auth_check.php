<?php
// Require at top of protected pages
session_start();
if (!isset($_SESSION['alogin']) && !isset($_SESSION['tlogin']) && !isset($_SESSION['slogin'])) {
    header('Location: ../Auth/login.php');
    exit;
}
$role = '';
if (isset($_SESSION['alogin'])) $role = 'admin';
elseif (isset($_SESSION['tlogin'])) $role = 'teacher';
elseif (isset($_SESSION['slogin'])) $role = 'student';
?>

