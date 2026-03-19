<?php
// config/db_connect.php  — Adaxy Academy database connection
define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // ← change to your MySQL user
define('DB_PASS', '');              // ← change to your MySQL password
define('DB_NAME', 'adaxy_academy');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

$conn->set_charset('utf8mb4');

// ── Session helpers ───────────────────────────────────────────
function requireLogin(string $role): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    $map = [
        'student'    => 'slogin',
        'teacher'    => 'tlogin',
        'admin'      => 'alogin',
        'management' => 'mlogin',
    ];

    $key = $map[$role] ?? null;
    if (!$key || empty($_SESSION[$key])) {
        header('Location: /Auth/login.php?role=' . $role);
        exit;
    }
}

function currentUser(): array
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    return [
        'role'  => $_SESSION['role']  ?? null,
        'login' => $_SESSION['slogin'] ?? $_SESSION['tlogin'] ?? $_SESSION['alogin'] ?? $_SESSION['mlogin'] ?? null,
    ];
}