<?php
include('../config/db_connect.php');

$username = 'bmwale';
$test_password = 'password';

$stmt = $conn->prepare("SELECT * FROM teachers WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();

echo "<h2>Teacher Login Test</h2>";
echo "Username: " . $teacher['username'] . "<br>";
echo "Name: " . $teacher['first_name'] . " " . $teacher['last_name'] . "<br>";
echo "Password hash in DB: " . $teacher['password'] . "<br>";
echo "Hash length: " . strlen($teacher['password']) . "<br>";

if (password_verify($test_password, $teacher['password'])) {
    echo "<h3 style='color:green'>✅ SUCCESS! Password 'password' matches!</h3>";
    echo "You can now login with:<br>";
    echo "Username: <strong>bmwale</strong><br>";
    echo "Password: <strong>password</strong>";
} else {
    echo "<h3 style='color:red'>❌ FAILED! Password doesn't match!</h3>";
    echo "The correct hash for 'password' is:<br>";
    echo "<code>" . password_hash('password', PASSWORD_DEFAULT) . "</code>";
}

$conn->close();
?>