<?php
session_start();
include('includes/config.php');

// Redirect to login page if not logged in
if (!isset($_SESSION['alogin'])) {
    header("Location: index.php");
    exit;
}

// Initialize variables
$msg = $error = "";
$student = $examPeriod = $subjects = $existingResults = [];
$readonly = '';
$disabled = '';

// Fetch student information if RollId and exam_period are set
if (isset($_POST['student']) && isset($_POST['exam_period'])) {
    $rollId = $_POST['student'];
    $examPeriodId = $_POST['exam_period'];

    // Check if the exam period is active before allowing edits
    $sqlCheckExamPeriod = "SELECT Status FROM tblexamperiod WHERE id = :examPeriodId";
    $queryCheckExamPeriod = $dbh->prepare($sqlCheckExamPeriod);
    $queryCheckExamPeriod->bindParam(':examPeriodId', $examPeriodId, PDO::PARAM_INT);
    $queryCheckExamPeriod->execute();
    $examPeriodStatus = $queryCheckExamPeriod->fetchColumn();

    if ((int)$examPeriodStatus !== 1) {
        // Exam period is not active, set a flag to disable editing
        $readonly = 'readonly';
        $disabled = 'disabled';
        $error = "You can only view results for inactive exam periods.";
    }

    // Fetch student information
    $sqlStudent = "SELECT * FROM tblstudents WHERE RollId = :rollId";
    $queryStudent = $dbh->prepare($sqlStudent);
    $queryStudent->bindParam(':rollId', $rollId, PDO::PARAM_STR);
    $queryStudent->execute();
    $student = $queryStudent->fetch(PDO::FETCH_ASSOC);

    if ($student) {
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
        $existingResultsArray = $queryResults->fetchAll(PDO::FETCH_ASSOC);

        // Convert existing results to an associative array for easier access
        foreach ($existingResultsArray as $result) {
            $existingResults[$result['SubjectCode']] = $result['marks'];
        }
    } else {
        $error = "Student not found.";
    }

    // Fetch exam period information
    $sqlExamPeriod = "SELECT * FROM tblexamperiod WHERE id = :examPeriodId";
    $queryExamPeriod = $dbh->prepare($sqlExamPeriod);
    $queryExamPeriod->bindParam(':examPeriodId', $examPeriodId, PDO::PARAM_INT);
    $queryExamPeriod->execute();
    $examPeriod = $queryExamPeriod->fetch(PDO::FETCH_ASSOC);

    if (!$examPeriod) {
        $error = "Exam period not found.";
    }
}

// Process form submission to update or add results
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $rollId = $_POST['student'];
    $examPeriodId = $_POST['exam_period'];

    foreach ($subjects as $subject) {
        $subjectCode = $subject['SubjectCode'];
        $marks = isset($_POST[$subjectCode]) && $_POST[$subjectCode] !== 'Not Marked' ? $_POST[$subjectCode] : null;

        // Check if marks exist for the subject
        $existingMark = array_key_exists($subjectCode, $existingResults);

        if ($marks !== null) {
            if ($existingMark) {
                // Update existing marks
                $sqlUpdateMarks = "UPDATE tblresult
                                   SET marks = :marks
                                   WHERE RollId = :rollId AND SubjectCode = :subjectCode AND ExamPeriodId = :examPeriodId";

                $queryUpdateMarks = $dbh->prepare($sqlUpdateMarks);
                $queryUpdateMarks->bindParam(':marks', $marks, PDO::PARAM_INT);
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
                $queryInsertMarks->bindParam(':marks', $marks, PDO::PARAM_INT);
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
                                    <li class="active"><?php echo isset($examPeriod['PeriodName']) ? $examPeriod['PeriodName'] : 'Exam Period'; ?> Results</li>
                                    <li><?php echo isset($student['StudentName']) ? $student['StudentName'] : 'Student'; ?> (<?php echo isset($student['RollId']) ? $student['RollId'] : 'Roll ID'; ?>)</li>
                                </ul>
                            </div>
                        </div>
                        <!-- /.row -->
                    </div>
                    <section class="section">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-8 col-md-offset-2">
                                    <div class="panel">
                                        <div class="panel-heading">
                                            <div class="panel-title">
                                                <h5>View / Edit Results</h5>
                                            </div>
                                        </div>
                                        <div class="p-20" style="margin-bottom: -20px">
                                            <?php if ($msg) : ?>
                                                <div class="alert alert-success" role="alert">
                                                    <?php echo htmlentities($msg); ?>
                                                </div>
                                            <?php elseif ($error) : ?>
                                                <div class="alert alert-danger" role="alert">
                                                    <?php echo htmlentities($error); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="panel-body">

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <input type="hidden" name="student" value="<?php echo isset($student['RollId']) ? $student['RollId'] : ''; ?>">
    <input type="hidden" name="exam_period" value="<?php echo isset($examPeriod['id']) ? $examPeriod['id'] : ''; ?>">

    <?php foreach ($subjects as $subject) : ?>
        <div class="form-group">
            <label for="<?php echo $subject['SubjectCode']; ?>"><?php echo $subject['SubjectName']; ?></label>
            <input type="number" id="<?php echo $subject['SubjectCode']; ?>" name="<?php echo $subject['SubjectCode']; ?>" class="form-control" <?php echo $readonly; ?> 
                   value="<?php echo isset($_POST[$subject['SubjectCode']]) ? htmlspecialchars($_POST[$subject['SubjectCode']]) : (isset($existingResults[$subject['SubjectCode']]) ? htmlspecialchars($existingResults[$subject['SubjectCode']]) : 'Not Marked'); ?>">
        </div>
    <?php endforeach; ?>

    <div class="form-group">
        <button type="submit" name="submit" class="btn btn-primary" <?php echo $disabled; ?>>Update Results</button>
    </div>
</form>



                                        </div>
                                    </div>
                                </div>
                                <!-- /.col-md-12 -->
                            </div>
                            <!-- /.row -->
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
            $(".js-example-basic-single").select2();
        });
    </script>
</body>

</html>
