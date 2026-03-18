<?php
session_start();
include('includes/config.php');

// Fetch subject combinations
$sql = "SELECT SCCode FROM tblsccode";
$query = $dbh->prepare($sql);
$query->execute();
$subjectCombinations = $query->fetchAll(PDO::FETCH_OBJ);

// Fetch all subjects with their combinations
$sql = "SELECT s.SubjectName, s.SubjectCode, sc.SCCode 
        FROM tblsubjects s 
        LEFT JOIN tblsubjectcombination sc ON s.SubjectCode = sc.SubjectCode";
$query = $dbh->prepare($sql);
$query->execute();
$subjects = $query->fetchAll(PDO::FETCH_ASSOC);

// Determine selected subjects
$selectedSubjects = isset($_SESSION['selected_subjects']) ? $_SESSION['selected_subjects'] : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Arimi's ERP Admin| Student Admission</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen" >
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen" >
    <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen" >
    <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen" >
    <link rel="stylesheet" href="css/prism/prism.css" media="screen" >
    <link rel="stylesheet" href="css/select2/select2.min.css" >
    <link rel="stylesheet" href="css/main.css" media="screen" >
    <script src="js/modernizr/modernizr.min.js"></script>
    <script>
        // Embed PHP data into JavaScript
        const subjectCombinations = <?php echo json_encode(array_column($subjectCombinations, 'SCCode')); ?>;
        const allSubjects = <?php echo json_encode($subjects); ?>;
        const selectedSubjects = <?php echo json_encode($selectedSubjects); ?>;

        // Create a map of subject codes to names to avoid duplicates
        const subjectMap = new Map();
        allSubjects.forEach(subject => {
            if (!subjectMap.has(subject.SubjectCode)) {
                subjectMap.set(subject.SubjectCode, { name: subject.SubjectName, codes: [] });
            }
            subjectMap.get(subject.SubjectCode).codes.push(subject.SCCode);
        });
    </script>
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
                            <h2 class="title">Student Admission</h2>
                        </div>
                    </div>
                    <div class="row breadcrumb-div">
                        <div class="col-md-6">
                            <ul class="breadcrumb">
                                <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                <li class="active">Student Admission</li>
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
                                            <h5>Fill the Student info</h5>
                                        </div>
                                    </div>
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

<form id="subject-form">
    <!-- Dropdown for Subject Combination Code -->
    <div class="form-group">
        <label for="sccode" class="col-sm-2">Subject Combination Code</label>
        <div class="col-sm-10">
	        <select name="sccode" class="form-control" id="sccode">
	            <option value="">Select Subject Combination Code</option>
	        </select>
	    </div>
    </div>

    <!-- Checkboxes for Subjects -->
    <div class="form-group" id="subjects-container">
        <label class="col-sm-2">Subjects:</label>
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
            </section>
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
                <script src="js/bootstrap-timepicker.min.js"></script>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        populateDropdown();
        populateCheckboxes();
        syncCheckboxesWithDropdown();
    });

    function populateDropdown() {
        const dropdown = document.getElementById('sccode');
        subjectCombinations.forEach(code => {
            const option = document.createElement('option');
            option.value = code;
            option.textContent = code;
            dropdown.appendChild(option);
        });
    }

    function populateCheckboxes() {
        const container = document.getElementById('subjects-container');
        subjectMap.forEach((subject, code) => {
            const label = document.createElement('label');
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.value = code;
            checkbox.id = `subject-${code}`;
            checkbox.checked = selectedSubjects.includes(code);
            label.appendChild(checkbox);
            label.appendChild(document.createTextNode(subject.name));
            container.appendChild(label);
            container.appendChild(document.createElement('br'));
        });

        // Attach event listeners after checkboxes are populated
        document.querySelectorAll('#subjects-container input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', updateDropdownFromCheckboxes);
        });
    }

    function syncCheckboxesWithDropdown() {
        const selectedCode = document.getElementById('sccode').value;
        const checkboxes = document.querySelectorAll('#subjects-container input[type="checkbox"]');
        checkboxes.forEach(checkbox => checkbox.checked = false); // Uncheck all
        subjectMap.forEach((subject, code) => {
            if (subject.codes.includes(selectedCode)) {
                document.getElementById(`subject-${code}`).checked = true;
            }
        });
    }

    function updateSelection() {
        const selectedCode = document.getElementById('sccode').value;
        const checkboxes = document.querySelectorAll('#subjects-container input[type="checkbox"]');
        const checkedSubjects = Array.from(checkboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => checkbox.value);

        // Update session or handle form submission here
        console.log("Selected Code:", selectedCode);
        console.log("Selected Subjects:", checkedSubjects);

        // For demonstration: reload the page to simulate form submission
        document.getElementById('subject-form').submit();
    }

    function updateDropdownFromCheckboxes() {
        const checkedSubjects = Array.from(document.querySelectorAll('#subjects-container input[type="checkbox"]:checked'))
            .map(checkbox => checkbox.value);
        let matchedCode = '';
        subjectCombinations.forEach(code => {
            const combinationSubjects = Array.from(subjectMap.values()).filter(subject => subject.codes.includes(code)).map(subject => subject.SubjectCode);
            if (checkedSubjects.length === combinationSubjects.length &&
                checkedSubjects.every(subject => combinationSubjects.includes(subject))) {
                matchedCode = code;
            }
        });
        document.getElementById('sccode').value = matchedCode;
    }

</script>

</body>
</html>
