<?php
include('includes/config.php');

$msg = '';
$error = '';

if (isset($_POST['submit'])) {
    $periodname = $_POST['periodname'];
    $startdate = $_POST['startdate'];
    $enddate = $_POST['enddate'];
    $status = isset($_POST['status']) ? 1 : 0;

    // Check if start date is not later than end date
    if ($startdate > $enddate) {
        $error = "Error: Start date cannot be later than end date.";
    } else {
        // Check for overlapping periods
        $sql = "SELECT * FROM tblschoolperiods WHERE (:startdate <= EndDate AND :enddate >= StartDate)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':startdate', $startdate, PDO::PARAM_STR);
        $query->bindParam(':enddate', $enddate, PDO::PARAM_STR);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        if (count($results) > 0) {
            $error = "Error: School periods cannot overlap.";
        } else {
            if ($status == 1) {
                // Set all other periods to inactive
                $sqlInactive = "UPDATE tblschoolperiods SET IsActive = 0";
                $dbh->query($sqlInactive);
            }

            // Insert the new school period
            $sqlInsert = "INSERT INTO tblschoolperiods (PeriodName, StartDate, EndDate, IsHoliday, IsActive) VALUES (:periodname, :startdate, :enddate, 0, :status)";
            $queryInsert = $dbh->prepare($sqlInsert);
            $queryInsert->bindParam(':periodname', $periodname, PDO::PARAM_STR);
            $queryInsert->bindParam(':startdate', $startdate, PDO::PARAM_STR);
            $queryInsert->bindParam(':enddate', $enddate, PDO::PARAM_STR);
            $queryInsert->bindParam(':status', $status, PDO::PARAM_INT);
            $queryInsert->execute();

            $msg = "School period added successfully.";
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
    <title>DAXY ACADAMY Admin | Add School Period</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen">
    <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen">
    <link rel="stylesheet" href="css/prism/prism.css" media="screen">
    <link rel="stylesheet" href="css/select2/select2.min.css">
    <link rel="stylesheet" href="css/main.css" media="screen">
    <script src="js/modernizr/modernizr.min.js"></script>
</head>
<body class="top-navbar-fixed">
    <div class="main-wrapper">
        <?php include('includes/topbar.php'); ?>
        <div class="content-wrapper">
            <div class="content-container">
                <?php include('includes/leftbar.php'); ?>
                <div class="main-page">
                    <div class="container-fluid">
                        <div class="row page-title-div">
                            <div class="col-md-6">
                                <h2 class="title">Add School Period</h2>
                            </div>
                        </div>
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li> School Period</li>
                                    <li class="active">Add School Period</li>
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
                                                <h5>Add School Period</h5>
                                            </div>
                                        </div>
                                        <div class="panel-body">
                                            <?php if ($msg) { ?>
                                                <div class="alert alert-success left-icon-alert" role="alert">
                                                    <strong>Well done!</strong> <?php echo htmlentities($msg); ?>
                                                </div>
                                            <?php } else if ($error) { ?>
                                                <div class="alert alert-danger left-icon-alert" role="alert">
                                                    <strong>Oh snap!</strong> <?php echo htmlentities($error); ?>
                                                </div>
                                            <?php } ?>
                                            <form class="form-horizontal" method="post">
                                                <div class="form-group">
                                                    <label for="periodname" class="col-sm-2 control-label">Period Name</label>
                                                    <div class="col-sm-10">
                                                        <input type="text" name="periodname" class="form-control" id="periodname" required="required" autocomplete="off">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="startdate" class="col-sm-2 control-label">Start Date</label>
                                                    <div class="col-sm-10">
                                                        <input type="date" name="startdate" class="form-control" id="startdate" required="required">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="enddate" class="col-sm-2 control-label">End Date</label>
                                                    <div class="col-sm-10">
                                                        <input type="date" name="enddate" class="form-control" id="enddate" required="required">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="status" class="col-sm-2 control-label">Status</label>
                                                    <div class="col-sm-10">
                                                        <input type="checkbox" name="status" value="1"> Active
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-sm-offset-2 col-sm-10">
                                                        <button type="submit" name="submit" class="btn btn-success">Add</button>
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
            <!-- /.content-container -->
        </div>
        <!-- /.content-wrapper -->
    </div>
    <!-- /.main-wrapper -->
    <script src="js/jquery/jquery-2.2.4.min.js"></script>
    <script src="js/bootstrap/bootstrap.min.js"></script>
    <script src="js/pace/pace.min.js"></script>
    <script src="js/lobipanel/lobipanel.min.js"></script>
    <script src="js/iscroll/iscroll.js"></script>
    <script src="js/prism/prism.js"></script>
    <script src="js/select2/select2.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        $(function($) {
            $(".js-states").select2();
            $(".js-states-limit").select2({
                maximumSelectionLength: 2
            });
            $(".js-states-hide").select2({
                minimumResultsForSearch: Infinity
            });
        });
    </script>
</body>
</html>
