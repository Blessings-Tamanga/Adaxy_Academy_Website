<?php
session_start();
// error_reporting(0);
include('includes/config.php');

if (!isset($_SESSION['alogin'])) {
    header("Location: index.php");
    exit;
}

$msg = $error = "";
$student = $examPeriod = $subjects = $existingResults = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $rollId = $_POST['student'];
    $examPeriodId = $_POST['exam_period'];

    // Fetch student information
    $sqlStudent = "SELECT * FROM tblstudents WHERE RollId = :rollId";
    $queryStudent = $dbh->prepare($sqlStudent);
    $queryStudent->bindParam(':rollId', $rollId, PDO::PARAM_STR);
    $queryStudent->execute();
    $student = $queryStudent->fetch(PDO::FETCH_ASSOC);

    // Fetch subjects based on SCCode
    $sqlSubjects = "SELECT tblsubjectcombination.SubjectCode, tblsubjects.SubjectName
                    FROM tblsubjectcombination 
                    JOIN tblsubjects ON tblsubjectcombination.SubjectCode = tblsubjects.SubjectCode
                    WHERE SCCode = :scCode";

    $querySubjects = $dbh->prepare($sqlSubjects);
    $querySubjects->bindParam(':scCode', $student['SCCode'], PDO::PARAM_STR);
    $querySubjects->execute();
    $subjects = $querySubjects->fetchAll(PDO::FETCH_ASSOC);

    // Fetch existing results for the selected student and exam period
    $sqlResults = "SELECT SubjectCode, marks FROM tblresult
                    WHERE RollId = :rollId AND ExamPeriodId = :examPeriodId";

    $queryResults = $dbh->prepare($sqlResults);
    $queryResults->bindParam(':rollId', $rollId, PDO::PARAM_STR);
    $queryResults->bindParam(':examPeriodId', $examPeriodId, PDO::PARAM_INT);
    $queryResults->execute();
    $existingResults = $queryResults->fetchAll(PDO::FETCH_ASSOC);

    // Fetch exam period information
    $sqlExamPeriod = "SELECT * FROM tblexamperiod WHERE id = :examPeriodId";
    $queryExamPeriod = $dbh->prepare($sqlExamPeriod);
    $queryExamPeriod->bindParam(':examPeriodId', $examPeriodId, PDO::PARAM_INT);
    $queryExamPeriod->execute();
    $examPeriod = $queryExamPeriod->fetch(PDO::FETCH_ASSOC);

    // Process form submission to update or add results
    foreach ($subjects as $subject) {
        $subjectCode = $subject['SubjectCode'];
        $marks = isset($_POST[$subjectCode]) ? $_POST[$subjectCode] : null;

        // Check if marks exist for the subject
        $existingMark = array_filter($existingResults, function ($result) use ($subjectCode) {
            return $result['SubjectCode'] === $subjectCode;
        });

        if ($marks !== null) {
            if ($existingMark) {
                // Update existing marks
                $sqlUpdateMarks = "UPDATE tblresult
                                   SET marks = :marks
                                   WHERE RollId = :rollId AND SubjectCode = :subjectCode AND ExamPeriodId = :examPeriodId";

                $queryUpdateMarks = $dbh->prepare($sqlUpdateMarks);
                $queryUpdateMarks->bindParam(':marks', $marks, PDO::PARAM_STR);
                $queryUpdateMarks->bindParam(':rollId', $rollId, PDO::PARAM_STR);
                $queryUpdateMarks->bindParam(':subjectCode', $subjectCode, PDO::PARAM_STR);
                $queryUpdateMarks->bindParam(':examPeriodId', $examPeriodId, PDO::PARAM_INT);

                if ($queryUpdateMarks->execute()) {
                    // Marks updated successfully
                    $msg = "Results updated successfully";
                } else {
                    // Error updating marks
                    $error = "Error updating marks";
                }
            } else {
                // Insert new marks
                $sqlInsertMarks = "INSERT INTO tblresult (RollId, SubjectCode, marks, ExamPeriodId)
                                   VALUES (:rollId, :subjectCode, :marks, :examPeriodId)";

                $queryInsertMarks = $dbh->prepare($sqlInsertMarks);
                $queryInsertMarks->bindParam(':rollId', $rollId, PDO::PARAM_STR);
                $queryInsertMarks->bindParam(':subjectCode', $subjectCode, PDO::PARAM_STR);
                $queryInsertMarks->bindParam(':marks', $marks, PDO::PARAM_STR);
                $queryInsertMarks->bindParam(':examPeriodId', $examPeriodId, PDO::PARAM_INT);

                if ($queryInsertMarks->execute()) {
                    // Marks inserted successfully
                    $msg = "Results updated successfully";
                } else {
                    // Error inserting marks
                    $error = "Error updating marks";
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
    <title>SMS Admin | View Results</title>
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
        <!-- ========== TOP NAVBAR ========== -->
        <?php include('includes/topbar.php'); ?>
        <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
        <div class="content-wrapper">
            <div class="content-container">
                <!-- ========== LEFT SIDEBAR ========== -->
                <?php include('includes/leftbar.php'); ?>
                <!-- /.left-sidebar -->
                <div class="main-page">
                    <div class="container-fluid">
                        <div class="row page-title-div">
                            <div class="col-md-6">
                                <h2 class="title">View / Edit Results</h2>
                            </div>
                        </div>
                        <!-- /.row -->
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li class="active"><?php echo $examPeriod['PeriodName']; ?> Results</li>
                                    <li><?php echo $student['StudentName']; ?> (<?php echo $student['RollId']; ?>)</li>
                                </ul>
                            </div>
                        </div>
                        <!-- /.row -->
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel">
                                    <div class="panel-body">

                                        <h4>Results</h4>

                                        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && !empty($msg)) : ?>
                                            <div class="alert alert-success"><?php echo $msg; ?></div>
                                        <?php endif; ?>

                                        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($error)) : ?>
                                            <div class="alert alert-danger"><?php echo $error; ?></div>
                                        <?php endif; ?>

                                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                            <input type="hidden" name="student" value="<?php echo $student['RollId']; ?>">
                                            <input type="hidden" name="exam_period" value="<?php echo $examPeriodId; ?>">

                                            <?php foreach ($subjects as $subject) {
                                                $subjectCode = $subject['SubjectCode'];
                                                $existingMarks = array_reduce($existingResults, function ($carry, $result) use ($subjectCode) {
                                                    if ($result['SubjectCode'] === $subjectCode) {
                                                        $carry = $result['marks'];
                                                    }
                                                    return $carry;
                                                });
                                            ?>
                                                <div class="form-group">
                                                    <label for="<?php echo $subjectCode; ?>" class="col-sm-2 control-label"><?php echo $subject['SubjectName']; ?></label>
                                                    <div class="col-sm-10">
                                                        <input type="text" name="<?php echo $subjectCode; ?>" id="<?php echo $subjectCode; ?>" value="<?php echo isset($_POST[$subjectCode]) ? $_POST[$subjectCode] : $existingMarks; ?>" placeholder="Enter marks out of 100" autocomplete="off">
                                                    </div>
                                                </div>
                                            <?php } ?>

                                            <div class="form-group">
                                                <div class="col-sm-offset-2 col-sm-10">
                                                    <button type="submit" name="submit" class="btn btn-primary">Update Results</button>
                                                </div>
                                            </div>
                                        </form>

                                        <a href="exam-results.php">Back to Manage results</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
