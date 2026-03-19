<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(!isset($_SESSION['alogin']))
    {   
    header("Location: index.php"); 
    }
    else{
if (isset($_GET['rollid'])) {
    $rollid = $_GET['rollid'];

    // Fetch student information
    $sqlStudent = "SELECT * FROM tblstudents WHERE RollId = :rollid";
    $queryStudent = $dbh->prepare($sqlStudent);
    $queryStudent->bindParam(':rollid', $rollid, PDO::PARAM_STR);
    $queryStudent->execute();
    $student = $queryStudent->fetch(PDO::FETCH_ASSOC);

    // Fetch subjects based on SCCode
    $sqlSubjects = "SELECT tblsubjectcombination.SubjectCode, tblsubjects.SubjectName
                FROM tblsubjectcombination 
                JOIN tblsubjects ON tblsubjectcombination.SubjectCode = tblsubjects.SubjectCode
                WHERE SCCode = :sccode";

    $querySubjects = $dbh->prepare($sqlSubjects);
    $querySubjects->bindParam(':sccode', $student['SCCode'], PDO::PARAM_STR);
    $querySubjects->execute();
    $subjects = $querySubjects->fetchAll(PDO::FETCH_ASSOC);

    // Fetch existing results for the selected student
    $sqlResults = "SELECT tblresult.SubjectCode, tblresult.marks, tblsubjects.SubjectName
                FROM tblresult
                JOIN tblsubjects ON tblresult.SubjectCode = tblsubjects.SubjectCode
                WHERE RollId = :rollid";
    $queryResults = $dbh->prepare($sqlResults);
    $queryResults->bindParam(':rollid', $rollid, PDO::PARAM_STR);
    $queryResults->execute();
    $results = $queryResults->fetchAll(PDO::FETCH_ASSOC);

    // Process form submission to update results
    if(isset($_POST['submit'])) {
        var_dump($_POST);
        $marks = $_POST['marks'];
        $rollid = $_POST['rollid']; 
        $subjectCombinationCode = $_POST['subjectCombinationCode'];

        // Fetch student information
        $sqlStudent = "SELECT * FROM tblstudents WHERE RollId = :rollid";
        $queryStudent = $dbh->prepare($sqlStudent);
        $queryStudent->bindParam(':rollid', $rollid, PDO::PARAM_STR);
        $queryStudent->execute();
        $student = $queryStudent->fetch(PDO::FETCH_ASSOC);

        // Fetch subjects associated with the subject combination code
        $stmt = $dbh->prepare("SELECT tblsubjects.SubjectCode, tblsubjects.SubjectName
            FROM tblsubjectcombination 
            JOIN tblsubjects ON tblsubjectcombination.SubjectCode = tblsubjects.SubjectCode
            WHERE tblsubjectcombination.SCCode = :sccode");

        $stmt->execute(array(':sccode' => $subjectCombinationCode));
        
        $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($subjects as $subject) {
            $subjectCode = $subject['SubjectCode'];

            // Insert or update results into the tblresult table
            if(isset($marks[$subjectCode])) {
                $marksValue = $marks[$subjectCode];

                $sql = "INSERT INTO tblresult(RollId, SCCode, SubjectCode, marks) 
                        VALUES(:rollid, :sccode, :subjectcode, :marks)
                        ON DUPLICATE KEY UPDATE marks = :marks";

                $query = $dbh->prepare($sql);
                $query->bindParam(':rollid', $rollid, PDO::PARAM_STR);
                $query->bindParam(':sccode', $subjectCombinationCode, PDO::PARAM_STR);
                $query->bindParam(':subjectcode', $subjectCode, PDO::PARAM_STR);
                $query->bindParam(':marks', $marksValue, PDO::PARAM_STR);
                $query->execute();

                $lastInsertId = $dbh->lastInsertId();

                if($lastInsertId) {
                    $msg = "Result info added successfully";
                } else {
                    $error = "Something went wrong. Please try again";
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
        <title>DAXY ACADAMY Admin| Add Result </title>
        <link rel="stylesheet" href="css/bootstrap.min.css" media="screen" >
        <link rel="stylesheet" href="css/font-awesome.min.css" media="screen" >
        <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen" >
        <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen" >
        <link rel="stylesheet" href="css/prism/prism.css" media="screen" >
        <link rel="stylesheet" href="css/select2/select2.min.css" >
        <link rel="stylesheet" href="css/main.css" media="screen" >
        <script src="js/modernizr/modernizr.min.js"></script>
        

    </head>
    <body class="top-navbar-fixed">
        <div class="main-wrapper">

            <!-- ========== TOP NAVBAR ========== -->
            <?php include('includes/topbar.php');?> 
            <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
            <div class="content-wrapper">
                <div class="content-container">

                    <!-- ========== LEFT SIDEBAR ========== -->
                   <?php include('includes/leftbar.php');?>  
                    <!-- /.left-sidebar -->

                    <div class="main-page">

                     <div class="container-fluid">
                            <div class="row page-title-div">
                                <div class="col-md-6">
                                    <h2 class="title">Declare Result</h2>
                                
                                </div>
                                
                                <!-- /.col-md-6 text-right -->
                            </div>
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                
                                        <li class="active">Student Result</li>
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
                                                <?php if($msg){?>
                                                <div class="alert alert-success left-icon-alert" role="alert">
                                                    <strong>Well done!</strong><?php echo htmlentities($msg); ?>
                                                 </div><?php } 
                                                else if($error){?>
                                                <div class="alert alert-danger left-icon-alert" role="alert">
                                                    <strong>Oh snap!</strong> <?php echo htmlentities($error); ?>
                                                </div>
                                                <?php } ?>
                                                <form class="form-horizontal" method="post">


                                                    <div class="form-group">

                                                        <label for="date" class="col-sm-2 control-label ">Existing Results</label>
                                                        <div class="col-sm-10">
                                                            <ul>
                                                                <?php foreach ($results as $result) { ?>
                                                                    <li style="border-bottom: 1px solid"><?php echo $result['SubjectName'] . ': ' . $result['marks']; ?></li>
                                                                <?php } ?>
                                                            </ul>
                                                        </div>
                                                        
                                                    </div>          
                                                    
                                                    <div class="form-group">
                                                        <label for="date" class="col-sm-2 control-label">Subjects</label>
                                                        <div class="col-sm-10">
                                                            <?php foreach ($subjects as $subject) { ?>
                                                                <div id="reslt">
                                                                    <?php echo $subject['SubjectName']; ?>:
                                                                    <input type="text" name="marks[<?php echo $subject['SubjectCode']; ?>]" 
                                                                           value="<?php echo isset($_POST['marks'][$subject['SubjectCode']]) ? htmlspecialchars($_POST['marks'][$subject['SubjectCode']]) : ''; ?>"
                                                                           placeholder="Enter marks out of 100" autocomplete="off">
                                                                </div>
                                                            <?php } ?>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <div class="col-sm-offset-2 col-sm-10">
                                                            <button type="submit" name="submit" id="submit" class="btn btn-primary">Declare Result</button>
                                                        </div>
                                                    </div>

                                                </form>

                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.col-md-12 -->
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
<?PHP } ?>