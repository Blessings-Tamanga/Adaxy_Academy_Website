<?php
include('includes/config.php');

// Fetching students from the database
$sql = "SELECT * FROM tblstudents";
$query = $dbh->prepare($sql);
$query->execute();
$students = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List</title>
    <!-- Add any additional CSS styles here -->
</head>
<body>

<h2>Student List</h2>

<table border="1">
    <thead>
        <tr>
            <th>Roll ID</th>
            <th>Student Name</th>
            <th>Subject Combination Code</th>
            <th>Edit Results</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($students as $student) { ?>
            <tr>
                <td><?php echo $student['RollId']; ?></td>
                <td><?php echo $student['StudentName']; ?></td>
                <td><?php echo $student['SCCode']; ?></td>
                <td><a href="add-result.php?rollid=<?php echo $student['RollId']; ?>">Edit</a></td>

            </tr>
        <?php } ?>
    </tbody>
</table>

</body>
</html>
