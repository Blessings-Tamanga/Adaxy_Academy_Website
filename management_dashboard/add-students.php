<?php
session_start();
include('includes/config.php');

$msg = "";
$error = "";

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

if (isset($_POST['submit'])) {
        $studentname = $_POST['fullanme'];
        $rollid = $_POST['rollid'];
        $email = $_POST['emailid'];
        $gender = $_POST['gender'];
        $classid = $_POST['class'];
        $sccode = $_POST['sccode'];
        $dob = $_POST['dob'];
        $status = 1;

        // Fetch additional fields from $_POST
        $phonenumber = $_POST['phonenumber'];
        $parentname = $_POST['parentname'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $security_question = $_POST['security_question'];
        $security_answer = $_POST['security_answer'];

        // File upload configuration
        $target_directory = "uploads/";
        $target_file = $target_directory . basename($_FILES["profile_picture"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is an actual image or fake image
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            $error = "File is not an image.";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["profile_picture"]["size"] > 5000000) {
            $error = "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        if ($uploadOk == 1) {
            // Check if the student with the same rollid already exists
            $sql_check = "SELECT RollId FROM tblstudents WHERE RollId=:rollid";
            $query_check = $dbh->prepare($sql_check);
            $query_check->bindParam(':rollid', $rollid, PDO::PARAM_STR);
            $query_check->execute();
            $result_check = $query_check->fetch(PDO::FETCH_ASSOC);

            if ($result_check) {
                $error = "Student with Roll ID already exists.";
            } else {
                if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                    // File uploaded successfully, continue with database insertion
                    $sql = "INSERT INTO tblstudents (StudentName, RollId, Email, Gender, ClassId, SCCode, DOB, Status, ProfilePicture, PhoneNumber, ParentGuardianName, Password, SecurityQuestion, SecurityAnswer) 
                            VALUES (:studentname, :rollid, :email, :gender, :classid, :sccode, :dob, :status, :profile_picture, :phonenumber, :parentname, :password, :security_question, :security_answer)";
                    
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':studentname', $studentname, PDO::PARAM_STR);
                    $query->bindParam(':rollid', $rollid, PDO::PARAM_STR);
                    $query->bindParam(':email', $email, PDO::PARAM_STR);
                    $query->bindParam(':gender', $gender, PDO::PARAM_STR);
                    $query->bindParam(':classid', $classid, PDO::PARAM_STR);
                    $query->bindParam(':sccode', $sccode, PDO::PARAM_STR);
                    $query->bindParam(':dob', $dob, PDO::PARAM_STR);
                    $query->bindParam(':status', $status, PDO::PARAM_STR);
                    $query->bindParam(':profile_picture', $target_file, PDO::PARAM_STR);
                    $query->bindParam(':phonenumber', $phonenumber, PDO::PARAM_STR);
                    $query->bindParam(':parentname', $parentname, PDO::PARAM_STR);
                    $query->bindParam(':password', $password, PDO::PARAM_STR);
                    $query->bindParam(':security_question', $security_question, PDO::PARAM_STR);
                    $query->bindParam(':security_answer', $security_answer, PDO::PARAM_STR);

                    $query->execute();
                    $lastInsertId = $dbh->lastInsertId();

                    if ($lastInsertId) {
                        $msg = "Student info added successfully";
                    } else {
                        $error = "Something went wrong. Please try again";
                    }
                } else {
                    $error = "Sorry, there was an error uploading your file.";
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
    <title>DAXY ACADAMY Admin| Student Admission< </title>
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
                            <h2 class="title">Student Admission</h2>

                        </div>

                        <!-- /.col-md-6 text-right -->
                    </div>
                    <!-- /.row -->
                    <div class="row breadcrumb-div">
                        <div class="col-md-6">
                            <ul class="breadcrumb">
                                <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                
                                <li class="active">Student Admission</li>
                            </ul>
                        </div>

                    </div>
                    <!-- /.row -->
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

                                        <form class="form-horizontal" method="post" enctype="multipart/form-data">
                                            <?php if($msg){?>
                                                <div class="alert alert-success left-icon-alert" role="alert">
                                                 <strong>Well done!</strong><?php echo htmlentities($msg); ?>
                                                </div><?php } 
                                             else if($error){?>
                                                <div class="alert alert-danger left-icon-alert" role="alert">
                                                    <strong>Oh snap!</strong> <?php echo htmlentities($error); ?>
                                                </div>
                                            <?php } ?>
                                            <div class="form-group">
                                                <label for="default" class="col-sm-2 control-label">Full Name</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="fullanme" class="form-control" id="fullanme" required="required" autocomplete="off" value="<?php echo isset($_POST['fullanme']) ? htmlentities($_POST['fullanme']) : ''; ?>">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="profile_picture" class="col-sm-2 control-label">Profile Picture</label>
                                                <div class="col-sm-10">
                                                    <input type="file" name="profile_picture" id="profile_picture" accept="image/*" required>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="default" class="col-sm-2 control-label">Roll Id</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="rollid" class="form-control" id="rollid" maxlength="5" required="required" autocomplete="off" value="<?php echo isset($_POST['rollid']) ? htmlentities($_POST['rollid']) : ''; ?>">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="default" class="col-sm-2 control-label">Email id</label>
                                                <div class="col-sm-10">
                                                    <input type="email" name="emailid" class="form-control" id="email" autocomplete="off" value="<?php echo isset($_POST['emailid']) ? htmlentities($_POST['emailid']) : ''; ?>">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="default" class="col-sm-2 control-label">Phone Number</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="phonenumber" class="form-control" id="phonenumber" required="required" autocomplete="off" value="<?php echo isset($_POST['phonenumber']) ? htmlentities($_POST['phonenumber']) : ''; ?>">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="default" class="col-sm-2 control-label">Parent/Guardian Name</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="parentname" class="form-control" id="parentname" required="required" autocomplete="off" value="<?php echo isset($_POST['parentname']) ? htmlentities($_POST['parentname']) : ''; ?>">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="default" class="col-sm-2 control-label">Gender</label>
                                                <div class="col-sm-10">
                                                    <input type="radio" name="gender" value="Male" required="required" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Male') ? 'checked' : ''; ?>>Male 
                                                    <input type="radio" name="gender" value="Female" required="required" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Female') ? 'checked' : ''; ?>>Female
                                                </div>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="default" class="col-sm-2 control-label">Class</label>
                                                <div class="col-sm-10">
                                                    <select name="class" class="form-control" id="default" required="required">
                                                        <option value="">Select Class</option>
                                                        <?php 
                                                        $sql = "SELECT * from tblclasses";
                                                        $query = $dbh->prepare($sql);
                                                        $query->execute();
                                                        $results=$query->fetchAll(PDO::FETCH_OBJ);
                                                        if($query->rowCount() > 0)
                                                        {
                                                            foreach($results as $result)
                                                            {   ?>
                                                                <option value="<?php echo htmlentities($result->id); ?>" <?php echo (isset($_POST['class']) && $_POST['class'] == $result->id) ? 'selected' : ''; ?>>
                                                                    <?php echo htmlentities($result->ClassName); ?>&nbsp; Section-<?php echo htmlentities($result->Section); ?>
                                                                </option>
                                                            <?php }} ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <!-- Dropdown for Subject Combination Code -->
                                            <div class="form-group">
                                                <label for="sccode" class="col-sm-2">Subject Combination Code</label>
                                                <div class="col-sm-10">
                                                    <select name="sccode" class="form-control" id="sccode">
                                                        <option value="">Select Subject Combination Code</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <style>
                                                .subjects-wrapper {
                                                    display: flex;
                                                    flex-wrap: wrap;
                                                    gap: 10px;
                                                    align-items: center;
                                                }

                                                .subjects-wrapper label {
                                                    display: flex;
                                                    align-items: center;
                                                    margin-bottom: 0;
                                                    width: auto;
                                                }

                                                .subjects-wrapper input[type="checkbox"] {
                                                    margin-right: 5px;
                                                }
                                            </style>

                                            <div class="form-group">
                                                <label for="subjects" class="col-sm-2 control-label">Subjects Preview</label>
                                                <div class="col-sm-10">
                                                    <div id="subjects-container" class="subjects-wrapper">
                                                        <!-- Checkboxes will be populated here -->
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="date" class="col-sm-2 control-label">DOB</label>
                                                <div class="col-sm-10">
                                                    <input type="date"  name="dob" class="form-control" id="date" value="<?php echo isset($_POST['dob']) ? htmlentities($_POST['dob']) : ''; ?>">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="default" class="col-sm-2 control-label">Password</label>
                                                <div class="col-sm-10">
                                                    <input type="password" name="password" class="form-control" id="password" required="required">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="default" class="col-sm-2 control-label">Security Question</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="security_question" class="form-control" id="security_question" autocomplete="off" value="<?php echo isset($_POST['security_question']) ? htmlentities($_POST['security_question']) : ''; ?>">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="default" class="col-sm-2 control-label">Security Answer</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="security_answer" class="form-control" id="security_answer" autocomplete="off" value="<?php echo isset($_POST['security_answer']) ? htmlentities($_POST['security_answer']) : ''; ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-sm-offset-2 col-sm-10">
                                                    <button type="submit" name="submit" class="btn btn-primary">Add</button>
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
            checkbox.disabled = true; // Disable the checkbox
            label.appendChild(checkbox);
            label.appendChild(document.createTextNode(subject.name));
            container.appendChild(label);
            container.appendChild(document.createElement('br'));
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

    document.getElementById('sccode').addEventListener('change', syncCheckboxesWithDropdown);

    // Listen for checkbox changes to update the dropdown
    document.querySelectorAll('#subjects-container input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', updateDropdownFromCheckboxes);
    });

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
