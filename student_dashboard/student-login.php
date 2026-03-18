<?php
session_start();
include('includes/config.php'); // Include your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate credentials against the student table
    $sql = "SELECT * FROM tblstudents WHERE username = :username AND password = :password";
    $query = $dbh->prepare($sql);
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->bindParam(':password', $password, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Set student role in the session
        $_SESSION['user_role'] = 'student';

        // Redirect to student dashboard
        header("Location: student-dashboard.php");
        exit();
    } else {
        $error = "Invalid credentials";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Login</title>
    
    body{
        background-color: blueviolet;
    }
        
    
     
</head>

<body>
    <div class="login-container">
        <h2>Student Login</h2>
        <?php if (isset($error)) { ?>
            <div class="error"><?php echo $error; ?></div>
        <?php } ?>
        <form method="post" action="">
            <label for="username">Username:</label>
            <input type="text" name="username" required></br></br

            <label for="password">Password:</label>
            <input type="password" name="password" required></br></br>

            <button type="submit">Login</button>
        </form>
    </div>
</body>

</html>

