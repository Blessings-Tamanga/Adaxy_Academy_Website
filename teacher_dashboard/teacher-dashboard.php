<?php
session_start();
//error_reporting(0);
include('includes/config.php');

if (!isset($_SESSION['alogin'])) {
    header("Location: index.php");
} else {

    $sqlMaleCount = "SELECT COUNT(*) as maleCount FROM tblstudents WHERE Gender = 'Male'";
    $queryMaleCount = $dbh->prepare($sqlMaleCount);
    $queryMaleCount->execute();
    $resultMaleCount = $queryMaleCount->fetch(PDO::FETCH_ASSOC);
    $maleCount = $resultMaleCount['maleCount'];

    $sqlFemaleCount = "SELECT COUNT(*) as femaleCount FROM tblstudents WHERE Gender = 'Female'";
    $queryFemaleCount = $dbh->prepare($sqlFemaleCount);
    $queryFemaleCount->execute();
    $resultFemaleCount = $queryFemaleCount->fetch(PDO::FETCH_ASSOC);
    $femaleCount = $resultFemaleCount['femaleCount'];

?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SRMS System | Dashboard</title>
        <!-- ========== CSS ========== -->
        <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
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
    </head>

    <body class="top-navbar-fixed">
        <div class="main-wrapper">
            <?php include('includes/t-topbar.php'); ?>
            <div class="content-wrapper">
                <div class="content-container">
                    <?php include('includes/t-leftbar.php'); ?>
                    <div class="main-page">
                        <div class="container-fluid">
                            <div class="row page-title-div">
                                <div class="col-sm-6">
                                    <h2 class="title">Dashboard</h2>
                                </div>
                                <!-- /.col-sm-6 -->
                            </div>
                            <!-- /.row -->
                        </div>
                        <!-- /.container-fluid -->

                        <section class="section">
                            <div class="container-fluid">
                                <div class="row">
                                    <!-- Card 1: Registered Users -->
                                    <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12 mb-4">
                                        <a class="dashboard-stat bg-primary" href="manage-students.php">
                                            <?php
                                            $sql1 = "SELECT StudentId from tblstudents ";
                                            $query1 = $dbh->prepare($sql1);
                                            $query1->execute();
                                            $totalstudents = $query1->rowCount();
                                            ?>
                                            <span class="number counter"><?php echo htmlentities($totalstudents); ?></span>
                                            <span class="name">Regd Students</span>
                                            <span class="bg-icon"><i class="fa fa-users"></i></span>
                                        </a>
                                        <!-- /.dashboard-stat -->
                                    </div>

                                    <!-- Card 2: Subjects Listed -->
                                    <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12 mb-4">
                                        <a class="dashboard-stat bg-danger" href="manage-subjects.php">
                                            <?php
                                            $sql = "SELECT id from tblsubjects ";
                                            $query = $dbh->prepare($sql);
                                            $query->execute();
                                            $totalsubjects = $query->rowCount();
                                            ?>
                                            <span class="number counter"><?php echo htmlentities($totalsubjects); ?></span>
                                            <span class="name">Subjects Listed</span>
                                            <span class="bg-icon"><i class="fa fa-ticket"></i></span>
                                        </a>
                                        <!-- /.dashboard-stat -->
                                    </div>

                                    <!-- Card 3: Total Classes Listed -->
                                    <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12 mb-4">
                                        <a class="dashboard-stat bg-warning" href="manage-classes.php">
                                            <?php
                                            $sql2 = "SELECT id from tblclasses ";
                                            $query2 = $dbh->prepare($sql2);
                                            $query2->execute();
                                            $totalclasses = $query2->rowCount();
                                            ?>
                                            <span class="number counter"><?php echo htmlentities($totalclasses); ?></span>
                                            <span class="name">Total classes listed</span>
                                            <span class="bg-icon"><i class="fa fa-bank"></i></span>
                                        </a>
                                        <!-- /.dashboard-stat -->
                                    </div>

                                    <!-- Card 4: Total Teachers -->
                                    <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12 mb-4">
                                        <a class="dashboard-stat bg-info" href="manage-teachers.php">
                                            <?php
                                            $sqlTeachers = "SELECT id from tblteachers";
                                            $queryTeachers = $dbh->prepare($sqlTeachers);
                                            $queryTeachers->execute();
                                            $totalTeachers = $queryTeachers->rowCount();
                                            ?>
                                            <span class="number counter"><?php echo htmlentities($totalTeachers); ?></span>
                                            <span class="name">Total Teachers</span>
                                            <span class="bg-icon"><i class="fa fa-user"></i></span>
                                        </a>
                                        <!-- /.dashboard-stat -->
                                    </div>


                                    <!-- Card 4: Total Teachers -->
                                    <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12 mb-4">
                                        <a class="dashboard-stat bg-success" href="manage-teachers.php">
                                            <span class="number counter">0</span>
                                            <span class="name">Inbox</span>
                                            <span class="bg-icon"><i class="fa fa-inbox"></i></span>
                                        </a>
                                        <!-- /.dashboard-stat -->
                                    </div>
                                    <!-- Card 4: Total Teachers -->
                                    <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12 mb-4">
                                        <a class="dashboard-stat bg-success" href="manage-teachers.php">
                                            <span class="number counter">0</span>
                                            <span class="name">Inbox</span>
                                            <span class="bg-icon"><i class="fa fa-inbox"></i></span>
                                        </a>
                                        <!-- /.dashboard-stat -->
                                    </div>
                                </div>
                                <!-- /.row -->

                                <!-- row -->
                                <div class="row">
                                    <!-- Pie Chart: Gender Distribution -->
                                    <div class="col-lg-6 col-md-8 col-sm-6 col-xs-12 mb-4">
                                        <div id="gender-pie-chart" style="width: 100%; height: 300px;"></div>
                                    </div>

                                    <div class="col-lg-6 col-md-8 col-sm-6 col-xs-12 mb-4">
                                        <!-- Card: Upcoming Events -->
                                        <div class="card shadow text-center" style="background-color: #ffffff;">
                                            <div class="card-header">
                                                <h5 class="card-title">Upcoming Events</h5>
                                            </div>
                                            <div class="card-body">
                                                <!-- Display Upcoming Events from Database -->
                                                <table class="table table-bordered text-left"  style="background-color: #f8f9fa;">
                                                    <thead>
                                                        <tr>
                                                            <th>Activity Name</th>
                                                            <th>Date</th>
                                                            <th>Time</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                    // Fetch data from tblupcomingevents table
                                                        $sqlUpcomingEvents = "SELECT * FROM tblupcomingevents WHERE EventDate >= CURDATE() ORDER BY EventDate, EventTime LIMIT 5";
                                                        $queryUpcomingEvents = $dbh->prepare($sqlUpcomingEvents);
                                                        $queryUpcomingEvents->execute();
                                                        $upcomingEvents = $queryUpcomingEvents->fetchAll(PDO::FETCH_ASSOC);

                                                        foreach ($upcomingEvents as $event) {
                                                        // Determine the display value for the date
                                                            $eventDate = date('Y-m-d', strtotime($event['EventDate']));
                                                            $formattedDate = '';

                                                            if ($eventDate === date('Y-m-d')) {
                                                                $formattedDate = 'Today';
                                                            } elseif ($eventDate === date('Y-m-d', strtotime('tomorrow'))) {
                                                                $formattedDate = 'Tomorrow';
                                                            } else {
                                                                $formattedDate = date('d-M-Y', strtotime($event['EventDate']));
                                                            }

                                                            echo "<tr>";
                                                            echo "<td>{$event['ActivityName']}</td>";
                                                            echo "<td>{$formattedDate}</td>";
                                                            echo "<td>{$event['EventTime']}</td>";
                                                            echo "<td><a href='edit-event.php?id={$event['id']}'>View/Edit</a></td>";
                                                            echo "</tr>";
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                                <p class="text-left" style="margin-left:10px">Click <strong><a href="add-event.php">Here</a></strong> to add an event</p>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <!--- /.row --->


                                <!--- row --->
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 mb-4">
                                        <div class="card text-center shadow" style="background-color: #ffffff">
                                            <div class="card-body">
                                                <?php
                                            // Retrieve the average results for the latest exam period
                                                $sqlAvgResults = "SELECT AVG(marks) as avgMarks FROM tblresult WHERE ExamPeriodId = (SELECT id FROM tblexamperiod ORDER BY StartDate DESC LIMIT 1)";
                                                $queryAvgResults = $dbh->prepare($sqlAvgResults);
                                                $queryAvgResults->execute();
                                                $resultAvgResults = $queryAvgResults->fetch(PDO::FETCH_ASSOC);
                                                $averageResults = round($resultAvgResults['avgMarks'], 2);

                                            // Retrieve the name of the latest exam period
                                                $sqlLatestExamPeriod = "SELECT PeriodName FROM tblexamperiod ORDER BY StartDate DESC LIMIT 1";
                                                $queryLatestExamPeriod = $dbh->prepare($sqlLatestExamPeriod);
                                                $queryLatestExamPeriod->execute();
                                                $resultLatestExamPeriod = $queryLatestExamPeriod->fetch(PDO::FETCH_ASSOC);
                                                $latestExamPeriodName = $resultLatestExamPeriod['PeriodName'];
                                                ?>
                                                <h5 class="card-title"><?php echo htmlentities($latestExamPeriodName); ?></h5>
                                                <div id="average-results-gauge"></div>
                                            </div>
                                        </div>
                                    </div>
<!--
<div class="col-lg-6 col-md-4 col-sm-6 col-xs-12 mb-4">
    <div class="card text-center shadow" style="background-color: #ffffff">
        <div class="card-body">


            <h5 class="card-title">Average Results by Class</h5>

            <div id="average-results-by-class-chart"></div>
        </div>
    </div>
</div>

-->


<?php
// Fetch average results for individual classes
$sqlAvgResultsByClass = "SELECT CONCAT(c.ClassName, ' (', c.Section, ')') AS ClassNameSection, AVG(r.marks) as avgMarks
                          FROM tblresult r
                          INNER JOIN tblstudents s ON r.RollId = s.RollId
                          INNER JOIN tblclasses c ON s.ClassId = c.id
                          WHERE r.ExamPeriodId = (SELECT id FROM tblexamperiod ORDER BY StartDate DESC LIMIT 1)
                          GROUP BY c.id";

$queryAvgResultsByClass = $dbh->prepare($sqlAvgResultsByClass);
$queryAvgResultsByClass->execute();
$resultAvgResultsByClass = $queryAvgResultsByClass->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 mb-4">
    <div class="card text-center shadow" style="background-color: #ffffff">
        <div class="card-body">
            <h5 class="card-title">Class Averages</h5>
            <ul class="list-group">
                <?php foreach ($resultAvgResultsByClass as $classData): ?>
                    <li class="list-group-item">
                        <?php echo $classData['ClassNameSection']; ?>: <?php echo round($classData['avgMarks'], 2); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>


<div id="classAverageChart" style="width: 100%; height: 400px;"></div>

                                </div>
                                <!--- /.row --->

                            </div>
                            <!-- /.container-fluid -->
                        </section>
                        <!-- /.section -->
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
        <script src="js/jquery-ui/jquery-ui.min.js"></script>
        <script src="js/bootstrap/bootstrap.min.js"></script>
        <script src="js/pace/pace.min.js"></script>
        <script src="js/lobipanel/lobipanel.min.js"></script>
        <script src="js/iscroll/iscroll.js"></script>

        <!-- ========== PAGE JS FILES ========== -->
        <script src="js/prism/prism.js"></script>
        <script src="js/waypoint/waypoints.min.js"></script>
        <script src="js/counterUp/jquery.counterup.min.js"></script>
        <script src="js/amcharts/amcharts.js"></script>
        <script src="js/amcharts/serial.js"></script>
        <script src="js/amcharts/plugins/export/export.min.js"></script>
        <link rel="stylesheet" href="js/amcharts/plugins/export/export.css" type="text/css" media="all" />
        <script src="js/amcharts/themes/light.js"></script>
        <script src="js/toastr/toastr.min.js"></script>
        <script src="js/icheck/icheck.min.js"></script>

        <!-- ========== THEME JS ========== -->
        <script src="js/main.js"></script>
        <script src="js/production-chart.js"></script>
        <script src="js/traffic-chart.js"></script>
        <script src="js/task-list.js"></script>

        <!-- ========== JUST GAGE ========== -->
        <script src="js/justgage.js"></script>
        <script src="js/raphael-2.1.4.min.js"></script>

        <!-- ========== AMCHARTS ========== -->
        <script src="js/amcharts/amcharts.js"></script>
        <script src="js/amcharts/pie.js"></script>
        <script src="js/amcharts/themes/light.js"></script>

        <script>
            $(function() {
            // Counter for dashboard stats
                $('.counter').counterUp({
                    delay: 10,
                    time: 1000
                });

            // Welcome notification
                toastr.options = {
                    "closeButton": true,
                    "debug": false,
                    "newestOnTop": false,
                    "progressBar": false,
                    "positionClass": "toast-top-right",
                    "preventDuplicates": false,
                    "onclick": null,
                    "showDuration": "300",
                    "hideDuration": "1000",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                }
                toastr["success"]("Welcome to the School's Management Sytem!");
            });
        </script>

        <script>
        // Initialize JustGage.js
            var averageResultsGauge = new JustGage({
                id: "average-results-gauge",
                value: <?php echo $averageResults; ?>,
                min: 0,
                max: 100,
                title: "Average Results",
                label: "Percentage",
                gaugeWidthScale: 0.5,
                counter: true,
                pointer: true,
                pointerOptions: {
                    toplength: -15,
                    bottomlength: 10,
                    bottomwidth: 12,
                    color: '#8e8e93',
                    stroke: '#ffffff',
                    stroke_width: 2,
                    stroke_linecap: 'round'
                },
                gaugeColor: "#E9ECEF",
                levelColors: ["#F5365C", "#FFD600", "#2DCE89"],
                relativeGaugeSize: true
            });
        </script>
        <script>
        // Create chart instance
          var chart = AmCharts.makeChart("gender-pie-chart", {
            "type": "pie",
            "theme": "light",
            "dataProvider": [
              { "gender": "Male", "count": <?php echo $maleCount; ?> },
              { "gender": "Female", "count": <?php echo $femaleCount; ?> }
              ],
            "valueField": "count",
            "titleField": "gender",
            "balloon": {
              "fixedPosition": true
          },
          "export": {
              "enabled": false
          }
      });
  </script>

<script>
    // Replace the following comments with the actual PHP data from the card
    var classAverageData = <?php echo json_encode($resultAvgResultsByClass); ?>;

    // Extract class names and average marks from the PHP result
    var classNames = classAverageData.map(function (data) {
        return data.ClassName;
    });

    var averageMarks = classAverageData.map(function (data) {
        return parseFloat(data.avgMarks.toFixed(2));
    });

    // Create chart for Average Results by Class
    var chart = AmCharts.makeChart("classAverageChart", {
        "type": "serial",
        "theme": "light",
        "dataProvider": classAverageData,
        "valueAxes": [{
            "gridColor": "#ddd",
            "gridAlpha": 1,
            "dashLength": 0
        }],
        "gridAboveGraphs": true,
        "startDuration": 1,
        "graphs": [{
            "balloonText": "[[category]]: <b>[[value]]</b>",
            "fillAlphas": 0.8,
            "lineAlpha": 0.2,
            "type": "column",
            "valueField": "avgMarks",
            "labelText": "[[value]]"
        }],
        "chartCursor": {
            "categoryBalloonEnabled": false,
            "cursorAlpha": 0,
            "zoomable": false
        },
        "categoryField": "ClassName",
        "categoryAxis": {
            "gridPosition": "start",
            "gridAlpha": 0
        },
        "export": {
            "enabled": false
        }
    });
</script>
</script>


</body>

<div class="foot">
    <footer></footer>
</div>

<style>
    .foot {
        text-align: center;
    }

    */ /* This closing style comment was incomplete, removed it */
</style>

</html>
<?php } ?>
