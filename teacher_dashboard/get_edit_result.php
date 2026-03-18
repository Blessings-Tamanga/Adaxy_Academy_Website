<?php
// Assuming you have a database connection in your config.php
include('includes/config.php');

// Check if the rollid parameter is set in the URL
if(isset($_GET['rollid'])) {
    $rollid = $_GET['rollid'];

    // Fetch student information
    $sqlStudent = "SELECT * FROM tblstudents WHERE RollId = :rollid";
    $queryStudent = $dbh->prepare($sqlStudent);
    $queryStudent->bindParam(':rollid', $rollid, PDO::PARAM_INT);
    $queryStudent->execute();
    $student = $queryStudent->fetch(PDO::FETCH_ASSOC);

    // Fetch subject combination code for the student
    $subjectCombinationCode = $student['SCCode'];

    // Fetch subjects associated with the subject combination code
    $sqlSubjects = "SELECT s.* FROM tblsubjects s
                    JOIN tblsubjectcombination sc ON s.SCCode = sc.SCCode
                    WHERE sc.SCCode = :subjectCombinationCode";
    $querySubjects = $dbh->prepare($sqlSubjects);
    $querySubjects->bindParam(':subjectCombinationCode', $subjectCombinationCode, PDO::PARAM_STR);
    $querySubjects->execute();
    $subjects = $querySubjects->fetchAll(PDO::FETCH_ASSOC);

    // Fetch existing results for the student
    $sqlResults = "SELECT * FROM tblresult WHERE RollId = :rollid";
    $queryResults = $dbh->prepare($sqlResults);
    $queryResults->bindParam(':rollid', $rollid, PDO::PARAM_INT);
    $queryResults->execute();
    $results = $queryResults->fetchAll(PDO::FETCH_ASSOC);

    // Process form submission to update results
    if(isset($_POST['submit'])) {
        // Validate and sanitize user inputs, and perform database update/insert operations here
        // ...

        // Redirect back to the student list page after updating results
        header("Location: student_results.php");
        exit;
    }
} else {
    // If rollid is not provided, redirect to the student list page
    header("Location: student_results.php");
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

<h2>Edit Results for <?php echo $student['studentname']; ?></h2>

<form method="post" action="">
    <h3>Subject Combination Code: <?php echo $subjectCombinationCode; ?></h3>
    <!-- Display subjects for editing -->
    <?php foreach ($subjects as $subject) { ?>
        <label for="<?php echo $subject['subject_code']; ?>"><?php echo $subject['subject_name']; ?>:</label>
        <input type="text" name="new_results[<?php echo $subject['subject_code']; ?>]" placeholder="Enter new results" required>
        <!-- Add other form fields for subjects, marks, etc., based on your database structure -->
        <br>
    <?php } ?>

    <input type="submit" name="submit" value="Update Results">
</form>

<!-- Display existing results for reference -->
<h3>Existing Results:</h3>
<ul>
    <?php foreach ($results as $result) { ?>
        <li><?php echo $result['subject_name'] . ': ' . $result['marks']; ?></li>
    <?php } ?>
</ul>

</body>
</html>
