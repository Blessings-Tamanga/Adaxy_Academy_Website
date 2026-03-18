<?php
session_start();
include('includes/config.php');

if (!isset($_SESSION['alogin'])) {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['class_id'])) {
    header("Location: dashboard.php");
    exit;
}

$classSql = "SELECT * FROM tblclasses WHERE id = :classId";
$classQuery = $dbh->prepare($classSql);
$classQuery->bindParam(':classId', $_GET['class_id'], PDO::PARAM_INT);
$classQuery->execute();
$classInfo = $classQuery->fetch(PDO::FETCH_OBJ);

if (!$classInfo) {
    echo "Class not found!";
    exit;
}

$studentSql = "SELECT * FROM tblstudents WHERE ClassId = :classId";
$studentQuery = $dbh->prepare($studentSql);
$studentQuery->bindParam(':classId', $_GET['class_id'], PDO::PARAM_INT);
$studentQuery->execute();
$students = $studentQuery->fetchAll(PDO::FETCH_OBJ);

if (!$students) {
    echo "No students found for this class!";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $attendanceData = $_POST['attendance'];

    if (empty($attendanceData)) {
        $error = "Please select at least one student for attendance.";
    } else {
        if (updateAttendance($classInfo->id, $attendanceData)) {
            $msg = "Attendance submitted successfully!";
        } else {
            $error = "Error submitting attendance. Please try again.";
        }
    }
}

// Function to update attendance records in the database
function updateAttendance($classId, $attendanceData)
{
    global $dbh;

    try {
        // Start a transaction
        $dbh->beginTransaction();

        // Example: Insert attendance data into a hypothetical tblattendance table
        $insertSql = "INSERT INTO tblattendance (ClassId, RollId) VALUES (:classId, :rollId)";
        $insertQuery = $dbh->prepare($insertSql);

        // Loop through each selected student's RollId and insert into tblattendance
        foreach ($attendanceData as $rollId) {
            // Bind parameters inside the loop
            $insertQuery->bindParam(':classId', $classId, PDO::PARAM_INT);
            $insertQuery->bindParam(':rollId', $rollId, PDO::PARAM_INT);

            // Execute the query for each student
            $insertQuery->execute();
        }

        // Commit the transaction
        $dbh->commit();

        // Return true if the update is successful
        return true;
    } catch (PDOException $e) {
        // An error occurred, rollback the transaction
        $dbh->rollBack();

        // Print the error message for debugging (you can remove this in a production environment)
        echo "Error: " . $e->getMessage();

        // Return false to indicate failure
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DAXY ACADAMY Admin Manage Students</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen">
    <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen">
    <link rel="stylesheet" href="css/prism/prism.css" media="screen">
    <link rel="stylesheet" type="text/css" href="js/DataTables/datatables.min.css"/>
    <link rel="stylesheet" href="css/main.css" media="screen">
    <script src="js/modernizr/modernizr.min.js"></script>
    <style>
        /* Add your custom styles here */
        .class-card {
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 20px;
            padding: 15px;
            text-align: center;
        }
    </style>
</head>
<body class="top-navbar-fixed">
    <div class="main-wrapper">
        <!-- ========== TOP NAVBAR ========== -->
        <?php include('includes/topbar.php');?>
        <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
        <div class="content-wrapper">
            <div class="content-container">
                <?php include('includes/leftbar.php');?>
                <div class="main-page">
                    <div class="container-fluid">
                        <div class="row page-title-div">
                            <div class="col-md-6">
                                <h2 class="title">Attendance Overview</h2>
                            </div>
                        </div>
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li> Students</li>
                                    <li class="active">Attendance Overview</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <section class="section">
                        <div class="container-fluid">
                            <!-- Add the datepicker input field and default to today's date -->
                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <form method="post">
                                        <div class="form-group">
                                            <label for="datepicker">Select Date:</label>
                                            <input type="date" class="form-control" id="datepicker" name="selected_date" value="<?= date('Y-m-d'); ?>" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </form>
                                </div>
                            </div>

                            <div class="row">
                                <?php
                                // Fetch class information from the database
                                $classSql = "SELECT * FROM tblclasses";
                                $classQuery = $dbh->prepare($classSql);
                                $classQuery->execute();
                                $classes = $classQuery->fetchAll(PDO::FETCH_OBJ);
                                ?>
                                <?php foreach ($classes as $class): ?>
                                <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12 mb-4">
                                    <a href="class-attendance.php?class_id=<?php echo htmlentities($class->id); ?>">
                                        <div class="class-card text-center shadow" style="background-color: #ffffff" data-class-id="<?php echo htmlentities($class->id); ?>">
                                            <h5><?php echo htmlentities($class->ClassName . ' (' . $class->Section . ')'); ?></h5>
                                            <!-- Add logic to calculate and display attendance percentage -->
                                            <p>Attendance: <?php echo htmlentities(calculateAttendancePercentage($class->id, $_POST['selected_date'])); ?>%</p>

                                            <span class="bg-icon"><i class="fa fa-percentage"></i></span>
                                        </div>
                                    </a>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>


        <!-- ========== COMMON JS FILES ========== -->
    <script src="js/jquery/jquery-2.2.4.min.js"></script>
    <script src="js/bootstrap/bootstrap.min.js"></script>
    <script src="js/pace/pace.min.js"></script>
    <script src="js/lobipanel/lobipanel.min.js"></script>
    <script src="js/iscroll/iscroll.js"></script>

    <!-- ========== PAGE JS FILES ========== -->
    <script src="js/prism/prism.js"></script>
    <script src="js/DataTables/datatables.min.js"></script>

    <!-- ========== THEME JS ========== -->
    <script src="js/main.js"></script>
    <script>
        $(function ($) {
            $('#example').DataTable();

            $('#example2').DataTable({
                "scrollY": "300px",
                "scrollCollapse": true,
                "paging": false
            });

            $('#example3').DataTable();
        });
    </script>

    <script>
        // Initialize the datepicker
        $(document).ready(function () {
            $('#datepicker').datepicker({
                format: 'yyyy-mm-dd',
                todayHighlight: true,
                autoclose: true
            });
        });

        // Function to get attendance heading
        function getAttendanceHeading(selectedDate) {
            var today = new Date();
            var currentDate = new Date(selectedDate);
            if (currentDate.toDateString() === today.toDateString()) {
                return "Today's Attendance";
            } else if (currentDate.toDateString() === new Date(today.setDate(today.getDate() - 1)).toDateString()) {
                return "Yesterday's Attendance";
            } else if (currentDate.toDateString() === new Date(today.setDate(today.getDate() + 2)).toDateString()) {
                return "Tomorrow's Attendance";
            } else {
                return "Attendance for " + selectedDate;
            }
        }
    </script>


</body>
</html>
