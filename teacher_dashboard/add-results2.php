<?php
include('includes/config.php');

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
    if (isset($_POST['submit'])) {
        $sccode=$_POST['sccode'];
        $sql="INSERT INTO  tblsccode(SCCode) VALUES(:sccode)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':sccode',$sccode,PDO::PARAM_STR);
        $query->execute();
        $lastInsertId = $dbh->lastInsertId();
        if($lastInsertId)
            {
            $msg="Code Created Successfully";
            }
            else 
            {
            $error="Something went wrong. Please try again";
            }
        }
    } else {
    // If rollid is not provided, redirect to the student list page
    header("Location: add-results.php");
    exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student Results</title>
    <!-- Add any additional CSS styles here -->
</head>
<body>

<h2>Edit Results for <?php echo $student['StudentName']; ?> (<?php echo $student['RollId']; ?>)</h2>

<form method="post" action="">
    <!-- Display existing results for reference -->
    <h3>Existing Results:</h3>
    <ul>
        <?php foreach ($results as $result) { ?>
            <li><?php echo $result['SubjectName'] . ': ' . $result['marks']; ?></li>
        <?php } ?>
    </ul>

    <!-- Add input fields for new results based on subjects -->
    <h3>Add/Update Results:</h3>
    <?php foreach ($subjects as $subject) { ?>
        <p>
            <?php echo $subject['SubjectName']; ?>:
            <input type="text" name="marks[<?php echo $subject['SubjectCode']; ?>]" 
                   value="<?php // Retrieve and display existing marks if needed ?>" 
                   placeholder="Enter marks out of 100" autocomplete="off">
        </p>
    <?php } ?>

    <input type="submit" name="submit" value="Update Results">
</form>

</body>
</html>