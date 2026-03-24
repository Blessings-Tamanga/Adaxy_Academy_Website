<?php
include('../config/db_connect.php');

// Fix admin passwords
$new_hash = password_hash('password', PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE admin SET password = ?");
$stmt->bind_param("s", $new_hash);
$stmt->execute();
$admin_count = $stmt->affected_rows;
$stmt->close();

// Fix management passwords
$stmt = $conn->prepare("UPDATE management SET password = ?");
$stmt->bind_param("s", $new_hash);
$stmt->execute();
$mgmt_count = $stmt->affected_rows;
$stmt->close();

echo "<h2>Password Fix Complete</h2>";
echo "<p>✅ Updated $admin_count admin record(s)</p>";
echo "<p>✅ Updated $mgmt_count management record(s)</p>";
echo "<p>New hash for 'password': <code>$new_hash</code></p>";
echo "<hr>";
echo "<h3>Test Login Now:</h3>";
echo "<p><strong>Admin:</strong> username: admin, password: password</p>";
echo "<p><strong>Management:</strong> username: headmaster, password: password</p>";

$conn->close();
?>