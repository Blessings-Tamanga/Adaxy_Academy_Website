<?php
session_start();
error_reporting(0);
include('includes/config.php');

if (strlen($_SESSION['alogin']) == 0) {
    header("Location: index.php");
    exit();
} else {
    // Fetch active school period for the current date
    $currentDate = date('Y-m-d');
    $sql = "SELECT * FROM tblschoolperiods WHERE StartDate <= :currentDate AND EndDate >= :currentDate";
    $query = $dbh->prepare($sql);
    $query->bindParam(':currentDate', $currentDate, PDO::PARAM_STR);
    $query->execute();
    $periods = $query->fetchAll(PDO::FETCH_ASSOC);

    if (empty($periods)) {
        $error = "No active school period found for the current date.";
    } else {
        $schoolPeriod = $periods[0];
        $startDate = $schoolPeriod['StartDate'];
        $endDate = $schoolPeriod['EndDate'];

        if (isset($_POST['submit'])) {
            $attendanceDate = $_POST['attendance_date'];

            try {
                // Loop through posted student attendance
                foreach ($_POST['attendance'] as $rollId => $status) {
                    // Insert or update attendance record
                    $sql = "INSERT INTO tblattendance (RollId, AttendanceDate, IsPresent, ClassId)
                            VALUES (:rollId, :attendanceDate, :status, :classId)
                            ON DUPLICATE KEY UPDATE IsPresent = :status";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':rollId', $rollId, PDO::PARAM_STR);
                    $query->bindParam(':attendanceDate', $attendanceDate, PDO::PARAM_STR);
                    $query->bindParam(':status', $status, PDO::PARAM_INT);
                    $query->bindParam(':classId', $_POST['class_id'][$rollId], PDO::PARAM_INT);
                    $query->execute();
                }

                // Success message
                $msg = "Attendance marked successfully!";
            } catch (PDOException $e) {
                // PDO Exception handling
                $error = "Error marking attendance: " . $e->getMessage();
            } catch (Exception $e) {
                // General exception handling
                $error = "Error marking attendance: " . $e->getMessage();
            }
        }

        // Fetch all students
        $sql = "SELECT tblstudents.StudentName, tblstudents.RollId, tblstudents.ClassId, tblclasses.ClassName, tblclasses.Section
                FROM tblstudents
                JOIN tblclasses ON tblstudents.ClassId = tblclasses.id";
        $query = $dbh->prepare($sql);
        $query->execute();
        $students = $query->fetchAll(PDO::FETCH_ASSOC);

        // Fetch existing attendance for the selected date
        $attendanceDate = isset($_POST['attendance_date']) ? $_POST['attendance_date'] : $currentDate;
        $attendance = [];
        $sql = "SELECT RollId, IsPresent FROM tblattendance WHERE AttendanceDate = :attendanceDate";
        $query = $dbh->prepare($sql);
        $query->bindParam(':attendanceDate', $attendanceDate, PDO::PARAM_STR);
        $query->execute();
        $existingAttendance = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach ($existingAttendance as $record) {
            $attendance[$record['RollId']] = $record['IsPresent'];
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mark Attendance</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen">
    <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen">
    <link rel="stylesheet" href="css/prism/prism.css" media="screen">
    <link rel="stylesheet" type="text/css" href="js/DataTables/datatables.min.css"/>
    <link rel="stylesheet" href="css/main.css" media="screen">
    <style>
        .errorWrap {
            padding: 10px;
            margin: 0 0 20px 0;
            background: #fff;
            border-left: 4px solid #dd3d36;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
        }
        .succWrap {
            padding: 10px;
            margin: 0 0 20px 0;
            background: #fff;
            border-left: 4px solid #5cb85c;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
        }

            .attendance-status {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 10px;
        vertical-align: middle;
    }

    .present {
        background-color: #5cb85c; /* Green */
    }

    .absent {
        background-color: #d9534f; /* Red */
    }

    .not-marked {
        background-color: #d9d9d9; /* Grey */
    }
    </style>
</head>
<body class="top-navbar-fixed">
    <div class="main-wrapper">

        <!-- ========== TOP NAVBAR ========== -->
        <?php include('includes/topbar.php'); ?>
        <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
        <div class="content-wrapper">
            <div class="content-container">
                <?php include('includes/leftbar.php'); ?>

                <div class="main-page">
                    <div class="container-fluid">
                        <div class="row page-title-div">
                            <div class="col-md-6">
                                <h2 class="title">Mark Attendance</h2>
                            </div>
                        </div>
                        <!-- /.row -->
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li>Attendance</li>
                                    <li class="active">Mark Attendance</li>
                                </ul>
                            </div>
                        </div>
                        <!-- /.row -->
                    </div>
                    <!-- /.container-fluid -->

                    <section class="section">
                        <div class="container-fluid">

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel">
                                        <div class="panel-heading">
                                            <div class="panel-title">
                                                <h5>Mark Attendance</h5>
                                            </div>
                                        </div>
                                        <div class="panel-body p-20">
                                            <?php if ($msg) { ?>
                                                <div class="alert alert-success left-icon-alert" role="alert">
                                                    <strong>Well done!</strong> <?php echo htmlentities($msg); ?>
                                                </div>
                                            <?php } else if ($error) { ?>
                                                <div class="alert alert-danger left-icon-alert" role="alert">
                                                    <strong>Oh snap!</strong> <?php echo htmlentities($error); ?>
                                                </div>
                                            <?php } ?>
                                            <form method="post">
                                                <div class="form-group">
                                                    <label for="attendance_date">Attendance Date:</label>
                                                    <input type="date" name="attendance_date" id="attendance_date" class="form-control" value="<?php echo htmlentities($attendanceDate); ?>" min="<?php echo $startDate; ?>" max="<?php echo $endDate; ?>" required>
                                                    <button type="submit" name="load_attendance" class="btn btn-info">Load Attendance</button>
                                                </div>
                                                <div class="form-group">
                                                    <label>Mark Attendance:</label><br>
                                                    <table id="example" class="table table-striped table-bordered" style="width:100%">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Student Name</th>
                                                                <th>Roll Id</th>
                                                                <th>Class (Section)</th>
                                                                <th>Attendance Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tfoot>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Student Name</th>
                                                                <th>Roll Id</th>
                                                                <th>Class (Section)</th>
                                                                <th>Attendance Status</th>
                                                            </tr>
                                                        </tfoot>
                                                        <tbody>
                                                            <?php if (!empty($students)) {
                                                                $cnt = 1;
                                                                foreach ($students as $student) {
                                                                    // Check if attendance record exists for the student
                                                                    $isPresent = isset($attendance[$student['RollId']]) ? $attendance[$student['RollId']] : ''; 
                                                                    // Determine CSS class based on attendance status
                                                                    $statusClass = $isPresent == 1 ? 'present' : ($isPresent == 0 ? 'absent' : 'not-marked');
                                                                    ?>
                                                                    <tr>
                                                                        <td><?php echo $cnt; ?></td>
                                                                        <td><?php echo htmlentities($student['StudentName']); ?></td>
                                                                        <td><?php echo htmlentities($student['RollId']); ?></td>
                                                                        <td><?php echo htmlentities($student['ClassName']) . ' (' . htmlentities($student['Section']) . ')'; ?></td>
                                                                        <td>
                                                                            <div class="attendance-status <?php echo $statusClass; ?>"></div>
                                                                            <input type="hidden" name="class_id[<?php echo htmlentities($student['RollId']); ?>]" value="<?php echo htmlentities($student['ClassId']); ?>">
                                                                            <input type="radio" name="attendance[<?php echo htmlentities($student['RollId']); ?>]" value="1" <?php echo $isPresent == 1 ? 'checked' : ''; ?>> Present
                                                                            <input type="radio" name="attendance[<?php echo htmlentities($student['RollId']); ?>]" value="0" <?php echo $isPresent == 0 ? 'checked' : ''; ?>> Absent
                                                                        </td>
                                                                    </tr>
                                                                    <?php $cnt++; } } else { ?>
                                                                    <tr>
                                                                        <td colspan="5">No students found.</td>
                                                                    </tr>
                                                                    <?php } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <button type="submit" name="submit" class="btn btn-primary">Submit Attendance</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </section>

                </div>
                <!-- /.main-page -->

            </div>
            <!-- /.content-container -->
        </div>
        <!-- /.content-wrapper -->

    </div>
    <!-- /.main-wrapper -->

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
        $(function($) {
            $('#example').DataTable();
        });
    </script>
</body>
</html>

<?php } ?>
