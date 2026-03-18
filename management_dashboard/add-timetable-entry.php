<?php
session_start();
error_reporting(0);
include('includes/config.php');

if (!isset($_SESSION['alogin'])) {
    header("Location: index.php");
} else {
    if (isset($_POST['submit'])) {
        $classId = $_POST['class'];
        $day = $_POST['day'];
        $startTime = $_POST['starttime'];
        $endTime = $_POST['endtime'];
        $subjectId = $_POST['subject'];
        $teacherId = $_POST['teacher'];
        $venue = $_POST['venue'] == '-1' ? -1 : intval($_POST['venue']);// Handle 'In Class'

        // Additional Validations
        if (strtotime($startTime) >= strtotime($endTime)) {
            $error = "Start time must be earlier than end time.";
        } else {
            // Check if the teacher is already assigned to another class during that period
            $teacherConflictQuery = "SELECT * FROM tbltimetable
                                     WHERE TeacherTNumber = :teacherId
                                     AND Day = :day
                                     AND ((StartTime <= :startTime AND EndTime >= :startTime)
                                          OR (StartTime <= :endTime AND EndTime >= :endTime))";

            $teacherConflictStmt = $dbh->prepare($teacherConflictQuery);
            $teacherConflictStmt->bindParam(':teacherId', $teacherId, PDO::PARAM_STR);
            $teacherConflictStmt->bindParam(':day', $day, PDO::PARAM_STR);
            $teacherConflictStmt->bindParam(':startTime', $startTime, PDO::PARAM_STR);
            $teacherConflictStmt->bindParam(':endTime', $endTime, PDO::PARAM_STR);
            $teacherConflictStmt->execute();

            if ($teacherConflictStmt->rowCount() > 0) {
                $error = "Teacher is already assigned to another class during this period.";
            } else {
                // Check if there are no time conflicts within the same class
                $classConflictQuery = "SELECT * FROM tbltimetable
                                       WHERE ClassId = :classId
                                       AND Day = :day
                                       AND ((StartTime <= :startTime AND EndTime >= :startTime)
                                            OR (StartTime <= :endTime AND EndTime >= :endTime))";

                $classConflictStmt = $dbh->prepare($classConflictQuery);
                $classConflictStmt->bindParam(':classId', $classId, PDO::PARAM_INT);
                $classConflictStmt->bindParam(':day', $day, PDO::PARAM_STR);
                $classConflictStmt->bindParam(':startTime', $startTime, PDO::PARAM_STR);
                $classConflictStmt->bindParam(':endTime', $endTime, PDO::PARAM_STR);
                $classConflictStmt->execute();

                if ($classConflictStmt->rowCount() > 0) {
                    $error = "This class has another session around this time.";
                } else {
                    // Check for venue conflicts
                    if ($venue !== -1) { // Only check if a specific venue is selected
                        $venueConflictQuery = "SELECT * FROM tbltimetable
                                               WHERE Venue = :venue
                                               AND Day = :day
                                               AND ((StartTime <= :startTime AND EndTime >= :startTime)
                                                    OR (StartTime <= :endTime AND EndTime >= :endTime))";

                        $venueConflictStmt = $dbh->prepare($venueConflictQuery);
                        $venueConflictStmt->bindParam(':venue', $venue, PDO::PARAM_INT);
                        $venueConflictStmt->bindParam(':day', $day, PDO::PARAM_STR);
                        $venueConflictStmt->bindParam(':startTime', $startTime, PDO::PARAM_STR);
                        $venueConflictStmt->bindParam(':endTime', $endTime, PDO::PARAM_STR);
                        $venueConflictStmt->execute();

                        if ($venueConflictStmt->rowCount() > 0) {
                            $error = "The selected venue is already booked during this time.";
                        }
                    }

                    if (!isset($error)) {
                        // Insert the timetable entry into the database
                        $insertQuery = "INSERT INTO tbltimetable (ClassId, Day, StartTime, EndTime, SubjectId, TeacherTNumber, Venue) 
                                        VALUES (:classId, :day, :startTime, :endTime, :subjectId, :teacherId, :venue)";
                        $insertStmt = $dbh->prepare($insertQuery);
                        $insertStmt->bindParam(':classId', $classId, PDO::PARAM_INT);
                        $insertStmt->bindParam(':day', $day, PDO::PARAM_STR);
                        $insertStmt->bindParam(':startTime', $startTime, PDO::PARAM_STR);
                        $insertStmt->bindParam(':endTime', $endTime, PDO::PARAM_STR);
                        $insertStmt->bindParam(':subjectId', $subjectId, PDO::PARAM_INT);
                        $insertStmt->bindParam(':teacherId', $teacherId, PDO::PARAM_STR);
                        $insertStmt->bindParam(':venue', $venue, PDO::PARAM_INT); // Handle NULL for 'In Class'
                        
                        if ($insertStmt->execute()) {
                            $msg = "Timetable entry added successfully";
                        } else {
                            $error = "Something went wrong. Please try again";
                        }
                    }
                }
            }
        }
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DAXY ACADAMY Admin | Student Admission</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen">
    <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen">
    <link rel="stylesheet" href="css/prism/prism.css" media="screen">
    <link rel="stylesheet" href="css/select2/select2.min.css">
    <link rel="stylesheet" href="css/main.css" media="screen">
    <link rel="stylesheet" href="css/bootstrap-timepicker.min.css">
    <script src="js/modernizr/modernizr.min.js"></script>
</head>
<body class="top-navbar-fixed">
<div class="main-wrapper">
    <?php include('includes/topbar.php');?>
    <div class="content-wrapper">
        <div class="content-container">
            <?php include('includes/leftbar.php');?>
            <div class="main-page">
                <div class="container-fluid">
                    <div class="row page-title-div">
                        <div class="col-md-6">
                            <h2 class="title">Add Timetable Entry</h2>
                        </div>
                    </div>
                    <div class="row breadcrumb-div">
                        <div class="col-md-6">
                            <ul class="breadcrumb">
                                <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                <li class="active">Add Timetable Entry</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <section class="section">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel">
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                            <h5>Fill the Timetable Entry</h5>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <?php if ($msg) { ?>
                                            <div class="alert alert-success left-icon-alert" role="alert">
                                                <strong>Well done!</strong><?php echo htmlentities($msg); ?>
                                            </div>
                                        <?php } else if ($error) { ?>
                                            <div class="alert alert-danger left-icon-alert" role="alert">
                                                <strong>Oh snap!</strong> <?php echo htmlentities($error); ?>
                                            </div>
                                        <?php } ?>

                                        <form class="form-horizontal" method="post" enctype="multipart/form-data">
                                            <div class="form-group">
                                                <label for="class" class="col-sm-2 control-label">Class</label>
                                                <div class="col-sm-10">
                                                    <select name="class" class="form-control" id="class" required="required">
                                                        <option value="">Select Class</option>
                                                        <?php
                                                        $sql = "SELECT * FROM tblclasses";
                                                        $query = $dbh->prepare($sql);
                                                        $query->execute();
                                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                        if ($query->rowCount() > 0) {
                                                            foreach ($results as $result) {
                                                                echo '<option value="' . htmlentities($result->id) . '">' . htmlentities($result->ClassName) . '&nbsp; Section-' . htmlentities($result->Section) . '</option>';
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="day" class="col-sm-2 control-label">Day</label>
                                                <div class="col-sm-10">
                                                    <select name="day" class="form-control" id="day" required="required">
                                                        <option value="">Select Day</option>
                                                        <option value="Monday">Monday</option>
                                                        <option value="Tuesday">Tuesday</option>
                                                        <option value="Wednesday">Wednesday</option>
                                                        <option value="Thursday">Thursday</option>
                                                        <option value="Friday">Friday</option>
                                                        <option value="Saturday">Saturday</option>
                                                        <option value="Sunday">Sunday</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="starttime" class="col-sm-2 control-label">Start Time</label>
                                                <div class="col-sm-10">
                                                    <input type="time" class="form-control" name="starttime" id="starttime" required="required">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="endtime" class="col-sm-2 control-label">End Time</label>
                                                <div class="col-sm-10">
                                                    <input type="time" class="form-control" name="endtime" id="endtime" required="required">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="subject" class="col-sm-2 control-label">Subject</label>
                                                <div class="col-sm-10">
                                                    <select name="subject" class="form-control" id="subject" required="required">
                                                        <option value="">Select Subject</option>
                                                        <?php
                                                        $sqlSubject = "SELECT * FROM tblsubjects";
                                                        $querySubject = $dbh->prepare($sqlSubject);
                                                        $querySubject->execute();
                                                        $resultsSubject = $querySubject->fetchAll(PDO::FETCH_OBJ);
                                                        if ($querySubject->rowCount() > 0) {
                                                            foreach ($resultsSubject as $resultSubject) {
                                                                echo '<option value="' . htmlentities($resultSubject->id) . '">' . htmlentities($resultSubject->SubjectName) . '</option>';
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="teacher" class="col-sm-2 control-label">Teacher</label>
                                                <div class="col-sm-10">
                                                    <select name="teacher" class="form-control" id="teacher" required="required">
                                                        <option value="">Select Teacher</option>
                                                        <?php
                                                        $sqlTeacher = "SELECT * FROM tblteachers";
                                                        $queryTeacher = $dbh->prepare($sqlTeacher);
                                                        $queryTeacher->execute();
                                                        $resultsTeacher = $queryTeacher->fetchAll(PDO::FETCH_OBJ);
                                                        if ($queryTeacher->rowCount() > 0) {
                                                            foreach ($resultsTeacher as $resultTeacher) {
                                                                echo '<option value="' . htmlentities($resultTeacher->TNumber) . '">' . htmlentities($resultTeacher->Name) . '</option>';
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group">
    <label for="venue" class="col-sm-2 control-label">Venue</label>
    <div class="col-sm-10">
        <select name="venue" class="form-control" id="venue" required="required">
            <?php
            $sqlVenue = "SELECT * FROM tblvenues";
            $queryVenue = $dbh->prepare($sqlVenue);
            $queryVenue->execute();
            $resultsVenue = $queryVenue->fetchAll(PDO::FETCH_OBJ);
            if ($queryVenue->rowCount() > 0) {
                foreach ($resultsVenue as $resultVenue) {
                    echo '<option value="' . htmlentities($resultVenue->id) . '">' . htmlentities($resultVenue->VenueName) . '</option>';
                }
            }
            ?>
        </select>
    </div>
</div>


                                            <div class="form-group">
                                                <div class="col-sm-offset-2 col-sm-10">
                                                    <button type="submit" name="submit" class="btn btn-primary">Add Timetable Entry</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
    <script src="js/jquery/jquery-2.2.4.min.js"></script>
    <script src="js/bootstrap/bootstrap.min.js"></script>
    <script src="js/pace/pace.min.js"></script>
    <script src="js/lobipanel/lobipanel.min.js"></script>
    <script src="js/iscroll/iscroll.js"></script>
    <script src="js/prism/prism.js"></script>
    <script src="js/select2/select2.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/bootstrap-timepicker.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.timepicker').timepicker({
                showMeridian: false,
                format: 'HH:mm',
                defaultTime: false
            });
        });
    </script>
</body>
</html>
<?PHP } ?>
