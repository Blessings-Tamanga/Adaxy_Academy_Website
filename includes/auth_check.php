<?php
session_start();

// 🔥 DEV MODE (REMOVE IN PRODUCTION)
if (!isset($_SESSION['slogin'])) {
    $_SESSION['slogin'] = 'STD001'; // use real RollId from DB
}
/*session_start();
require_once '../config/db_connect.php';

// Determine role
$role = null;
if (isset($_SESSION['alogin'])) $role = 'admin';
elseif (isset($_SESSION['tlogin'])) $role = 'teacher';
elseif (isset($_SESSION['slogin'])) $role = 'student';

if (!$role) {
    header('Location: ../Auth/login.php');
    exit;
}*/
?>