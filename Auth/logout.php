<?php
// ============================================================
//  Adaxy Academy · Logout Handler
//  Destroys session and redirects to landing page
// ============================================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to landing page (index.html)
header("Location: ../index.php");
exit;
?>