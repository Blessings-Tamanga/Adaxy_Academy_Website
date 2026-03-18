<?php
session_start();
error_reporting(0);
include('includes/config.php');

if (strlen($_SESSION['alogin']) == 0) {
    header("Location: index.php");
    exit();
} else {
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

        // Fetch all students and their classes
        $sqlStudents = "SELECT tblstudents.StudentName, tblstudents.RollId, tblstudents.ClassId, tblclasses.ClassName, tblclasses.Section
                        FROM tblstudents
                        JOIN tblclasses ON tblstudents.ClassId = tblclasses.id";
        $queryStudents = $dbh->prepare($sqlStudents);
        $queryStudents->execute();
        $students = $queryStudents->fetchAll(PDO::FETCH_ASSOC);

        // Attendance date selection
        $attendanceDate = isset($_POST['attendance_date']) ? $_POST['attendance_date'] : $currentDate;

        // Query to get latest attendance for selected date
        $sqlAttendance = "SELECT a.RollId, a.IsPresent, s.ClassId
                          FROM tblattendance a
                          JOIN (SELECT RollId, MAX(timestamp) AS MaxTimestamp
                                FROM tblattendance
                                WHERE AttendanceDate = :attendanceDate
                                GROUP BY RollId) latest_attendance ON a.RollId = latest_attendance.RollId AND a.timestamp = latest_attendance.MaxTimestamp
                          JOIN tblstudents s ON a.RollId = s.RollId";
        $queryAttendance = $dbh->prepare($sqlAttendance);
        $queryAttendance->bindParam(':attendanceDate', $attendanceDate, PDO::PARAM_STR);
        $queryAttendance->execute();
        $existingAttendance = $queryAttendance->fetchAll(PDO::FETCH_ASSOC);

        // Calculate attendance statistics
        $attendanceStatus = [];
        foreach ($existingAttendance as $record) {
            $attendanceStatus[$record['RollId']] = $record['IsPresent'];
        }

        // Initialize variables for overall summary
        $totalStudents = count($students);
        $presentCount = 0;
        $absentCount = 0;
        $notSubmittedCount = 0;

        // Calculate individual attendance counts
        foreach ($students as $student) {
            $rollId = $student['RollId'];
            if (isset($attendanceStatus[$rollId])) {
                if ((int)$attendanceStatus[$rollId] === 1) {
                    $presentCount++;
                } elseif ((int)$attendanceStatus[$rollId] === 0) {
                    $absentCount++;
                }
            } else {
                $notSubmittedCount++;
            }
        }

        // Overall attendance percentage
        $attendancePercentage = ($totalStudents > 0) ? ($presentCount / $totalStudents) * 100 : 0;

        // Group students by classes for summary cards
        $classSummary = [];
        foreach ($students as $student) {
            $classId = $student['ClassId'];
            $rollId = $student['RollId'];
            $className = $student['ClassName'];
            $section = $student['Section'];
            $isPresent = isset($attendanceStatus[$rollId]) ? $attendanceStatus[$rollId] : -1;

            if (!isset($classSummary[$classId])) {
                $classSummary[$classId] = [
                    'ClassName' => $className,
                    'Section' => $section,
                    'TotalStudents' => 0,
                    'PresentCount' => 0,
                    'AbsentCount' => 0,
                    'NotSubmittedCount' => 0,
                    'AttendancePercentage' => 0,
                ];
            }

            $classSummary[$classId]['TotalStudents']++;
            if ((int)$isPresent === 1) {
                $classSummary[$classId]['PresentCount']++;
            } elseif ((int)$isPresent === 0) {
                $classSummary[$classId]['AbsentCount']++;
            } else {
                $classSummary[$classId]['NotSubmittedCount']++;
            }

            // Calculate the percentage for each class
            $classSummary[$classId]['AttendancePercentage'] = ($classSummary[$classId]['TotalStudents'] > 0) ?
                ($classSummary[$classId]['PresentCount'] / $classSummary[$classId]['TotalStudents']) * 100 : 0;
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
    <title>View Attendance</title>
    <!-- ========== CSS ========== -->
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" type="text/css" href="js/DataTables/datatables.min.css"/>
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen">
    <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen">
    <link rel="stylesheet" href="css/toastr/toastr.min.css" media="screen">
    <link rel="stylesheet" href="css/icheck/skins/line/blue.css">
    <link rel="stylesheet" href="css/icheck/skins/line/red.css">
    <link rel="stylesheet" href="css/icheck/skins/line/green.css">
    <link rel="stylesheet" href="css/main.css" media="screen">
    <!-- ========== JS ========== -->
    <script src="js/modernizr/modernizr.min.js"></script>
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

        .class-summary-card {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .class-summary-card h4 {
            margin-top: 0;
        }

        .class-summary-card .summary {
            font-size: 18px;
        }

        .chart-container {
            height: 300px;
            width: 100%;
        }

        /* Hide the table on small screens and show it on medium and larger screens */
        @media (max-width: 768px) {
            .attendance-table {
                display: none;
            }
            .attendance-cards {
                display: block;
            }
        }

        @media (min-width: 769px) {
            .attendance-table {
                display: block;
            }
            .attendance-cards {
                display: none;
            }
        }


    </style>
    <!-- AmCharts CSS -->
    <link rel="stylesheet" href="https://www.amcharts.com/lib/4/core.css">
    <link rel="stylesheet" href="https://www.amcharts.com/lib/4/charts.css">
    <link rel="stylesheet" href="https://www.amcharts.com/lib/4/themes/animated.css">
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
                            <h2 class="title">View Attendance</h2>
                        </div>
                    </div>
                    <!-- /.row -->
                    <div class="row breadcrumb-div">
                        <div class="col-md-6">
                            <ul class="breadcrumb">
                                <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                <li>Attendance</li>
                                <li class="active">View Attendance</li>
                            </ul>
                        </div>
                    </div>
                    <!-- /.row -->
                </div>

                <section class="section">
                    <div class="container-fluid">

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="panel">
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                            <h5>Attendance Overview</h5>
                                        </div>
                                    </div>
                                    <div class="panel-body p-20">
                                        <!-- Date Selection Form -->
                                        <form method="POST" action="">
                                            <div class="form-group">
                                                <label for="attendance_date">Select Date:</label>
                                                <input type="date" name="attendance_date" id="attendance_date" class="form-control" value="<?php echo htmlentities($attendanceDate); ?>" min="<?php echo $startDate; ?>" max="<?php echo $endDate; ?>" required>
                                            </div>
                                            <button type="submit" class="btn btn-primary">View Attendance</button>
                                        </form>

                                        <div class="col-lg-12">
                                            <!-- Overall Attendance Summary -->
                                            <div class="alert alert-info" style="margin-top: 20px;">
                                                <strong>Overall Attendance Summary:</strong><br>
                                                <span class="attendance-status present"></span> Present: <?php echo $presentCount; ?><br>
                                                <span class="attendance-status absent"></span> Absent: <?php echo $absentCount; ?><br>
                                                <span class="attendance-status not-marked"></span> Not Marked: <?php echo $notSubmittedCount; ?><br>
                                                Attendance Percentage: <?php echo number_format($attendancePercentage, 2); ?>%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="panel">
                                    <div class="panel-body" style="background-color: #f1f1f1">
                                        <!-- Class-wise Attendance Summary Cards -->
                                        <div class="row">
                                            <?php foreach ($classSummary as $classId => $summary) { ?>
                                                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 mb-4">
                                                    <h5 class="card-title"><?php echo htmlentities($summary['ClassName'] . " (" . $summary['Section'] . ")"); ?></h5>
                                                    <!-- ApexCharts Pie Chart -->
                                                    <div id="chart-<?php echo $classId; ?>" style="background-color: #ffffff;"></div>
                                                    <script>
                                                        document.addEventListener("DOMContentLoaded", function() {
                                                            var options<?php echo $classId; ?> = {
                                                                series: [<?php echo $summary['PresentCount']; ?>, <?php echo $summary['AbsentCount']; ?>, <?php echo $summary['NotSubmittedCount']; ?>],
                                                                chart: {
                                                                    height: 350,
                                                                    type: 'pie',
                                                                },
                                                                labels: ['Present', 'Absent', 'Not Marked'],
                                                                colors: ['#5cb85c', '#d9534f', '#d9d9d9'],
                                                                responsive: [{
                                                                    breakpoint: 480,
                                                                    options: {
                                                                        chart: {
                                                                            width: 200
                                                                        },
                                                                        legend: {
                                                                            position: 'bottom'
                                                                        }
                                                                    }
                                                                }]
                                                            };

                                                            var chart<?php echo $classId; ?> = new ApexCharts(document.querySelector("#chart-<?php echo $classId; ?>"), options<?php echo $classId; ?>);
                                                            chart<?php echo $classId; ?>.render();
                                                        });
                                                    </script>
                                                    <!-- End ApexCharts Pie Chart --
                                                    <div class="summary">
                                                        <span class="attendance-status present"></span> Present: <?php echo $summary['PresentCount']; ?><br>
                                                        <span class="attendance-status absent"></span> Absent: <?php echo $summary['AbsentCount']; ?><br>
                                                        <span class="attendance-status not-marked"></span> Not Marked: <?php echo $summary['NotSubmittedCount']; ?><br>
                                                        Attendance Percentage: <?php echo number_format($summary['AttendancePercentage'], 2); ?>%
                                                    </div -->
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>


                                <div class="panel attendance-table">
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                            <h5>Attendance Table</h5>
                                        </div>
                                    </div>
                                    <div class="panel-body">

                                        <div class="col-lg-12">
                                            <!-- Attendance Table -->
                                            <table id="example" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                                                <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Roll Id</th>
                                                    <th>Student Name</th>
                                                    <th>Class</th>
                                                    <th>Section</th>
                                                    <th>Attendance Status</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php
                                                $cnt = 1;
                                                foreach ($students as $student) {
                                                    $rollId = $student['RollId'];
                                                    $studentName = $student['StudentName'];
                                                    $className = $student['ClassName'];
                                                    $section = $student['Section'];
                                                    $isPresent = isset($attendanceStatus[$rollId]) ? $attendanceStatus[$rollId] : -1;
                                                    $statusText = ($isPresent == 1) ? 'Present' : (($isPresent == 0) ? 'Absent' : 'Not Marked');
                                                    $statusClass = ($isPresent == 1) ? 'present' : (($isPresent == 0) ? 'absent' : 'not-marked');
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlentities($cnt); ?></td>
                                                        <td><?php echo htmlentities($rollId); ?></td>
                                                        <td><?php echo htmlentities($studentName); ?></td>
                                                        <td><?php echo htmlentities($className); ?></td>
                                                        <td><?php echo htmlentities($section); ?></td>
                                                        <td><span class="attendance-status <?php echo $statusClass; ?>"></span> <?php echo htmlentities($statusText); ?></td>
                                                    </tr>
                                                    <?php $cnt++;
                                                } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="panel attendance-cards">
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                            <h5>Attendance Cards</h5>
                                        </div>
                                    </div>

                                    <div class="panel-body">
                                        <div class="row">
                                            <!-- Attendance Cards section (visible on all screens) -->
                                            <?php foreach ($students as $student) {
                                                $rollId = $student['RollId'];
                                                $studentName = $student['StudentName'];
                                                $className = $student['ClassName'];
                                                $section = $student['Section'];
                                                $isPresent = isset($attendanceStatus[$rollId]) ? $attendanceStatus[$rollId] : -1;
                                                $statusText = ($isPresent == 1) ? 'Present' : (($isPresent == 0) ? 'Absent' : 'Not Marked');
                                                $statusClass = ($isPresent == 1) ? 'present' : (($isPresent == 0) ? 'absent' : 'not-marked');
                                                ?>
                                                <div class="col-lg-3 col-sm-3 mb-4">
                                                    <div class="alert alert-info" style="margin-top: 20px;">
                                                        <strong><?php echo htmlentities($studentName); ?> (<?php echo htmlentities($rollId); ?>)</strong><br>
                                                        <span class="text-muted"></span><?php echo htmlentities($className . ' - ' . $section); ?><br>
                                                        <span class="attendance-status <?php echo $statusClass; ?>"></span> <?php echo htmlentities($statusText); ?>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>
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
<script src="js/prism/prism.js"></script>
<script src="js/DataTables/datatables.min.js"></script>
<script src="js/apexcharts/apexcharts.min.js"></script>
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


</body>
</html>
