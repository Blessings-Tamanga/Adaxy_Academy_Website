<?php
session_start();
error_reporting(0);
include('includes/config.php');

if (!isset($_SESSION['alogin'])) {
    header("Location: index.php");
    exit;
}

$msg = ''; // Initialize a variable to store messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process the form submission
    $activityName = $_POST['activityName'];
    $activityDate = $_POST['activityDate'];
    $activityTime = $_POST['activityTime'];
    $eventDescription = $_POST['eventDescription'];

    // Validate and sanitize input (you should implement proper validation)
    $activityName = htmlspecialchars($activityName, ENT_QUOTES, 'UTF-8');
    $activityDate = htmlspecialchars($activityDate, ENT_QUOTES, 'UTF-8');
    $activityTime = htmlspecialchars($activityTime, ENT_QUOTES, 'UTF-8');
    $eventDescription = htmlspecialchars($eventDescription, ENT_QUOTES, 'UTF-8');

    // Insert into the database
    try {
        $sql = "INSERT INTO tblupcomingevents (ActivityName, EventDate, EventTime, EventDescription) 
                VALUES (:activityName, :activityDate, :activityTime, :eventDescription)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':activityName', $activityName, PDO::PARAM_STR);
        $query->bindParam(':activityDate', $activityDate, PDO::PARAM_STR);
        $query->bindParam(':activityTime', $activityTime, PDO::PARAM_STR);
        $query->bindParam(':eventDescription', $eventDescription, PDO::PARAM_STR);
        $query->execute();

        $msg = "Event added successfully!";
    } catch (PDOException $e) {
        $msg = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Event</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h2>Add Event</h2>
        <hr>

        <!-- Display success or error message -->
        <?php if ($msg): ?>
            <div class="alert <?php echo (strpos($msg, 'Error') !== false) ? 'alert-danger' : 'alert-success'; ?>" role="alert">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <!-- Form to add event -->
        <form method="post" action="">
            <div class="form-group">
                <label for="activityName">Activity Name:</label>
                <input type="text" class="form-control" id="activityName" name="activityName" required>
            </div>
            <div class="form-group">
                <label for="activityDate">Date:</label>
                <input type="date" class="form-control" id="activityDate" name="activityDate" required>
            </div>
            <div class="form-group">
                <label for="activityTime">Time:</label>
                <input type="time" class="form-control" id="activityTime" name="activityTime" required>
            </div>
            <div class="form-group">
                <label for="eventDescription">Description:</label>
                <textarea class="form-control" id="eventDescription" name="eventDescription" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Event</button>
        </form>

        <!-- Back to dashboard link -->
        <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>

    <script src="js/jquery/jquery-2.2.4.min.js"></script>
    <script src="js/bootstrap/bootstrap.min.js"></script>
</body>

</html>
