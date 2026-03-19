<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(!isset($_SESSION['alogin']))
    {   
    header("Location: index.php"); 
    }
    else{

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classId = $_POST['class'];
    $studentId = $_POST['student'];

    // Fetch subjects based on the selected student's subject combination code
    $subjects = fetch_subjects_for_student($studentId);
    // Loop through $subjects to generate form fields

    // Handle form submission and update the 'results' table
    update_results_in_database($classId, $studentId, $_POST['subject1'], $_POST['subject2']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Results</title>
</head>
<body>
    <form method="post" action="process_results.php">
        <label for="class">Select Class:</label>
        <select name="class" id="class" onchange="getStudents(this.value)">
            <option value="">Select Class</option>
            <?php
                // Replace the following with your actual code to fetch classes from the database
                $classes = fetch_classes_from_database();
                foreach ($classes as $class) {
                    echo "<option value='{$class['classid']}'>{$class['classname']} - {$class['section']}</option>";
                }
            ?>
        </select>

        <label for="student">Select Student:</label>
        <select name="student" id="student">
            <option value="">Select Student</option>
            <!-- This will be populated dynamically based on the selected class -->
        </select>

        <!-- Here you will dynamically generate the form fields based on the selected student and subject combination -->
        <div id="subjectForm"></div>

        <input type="submit" value="Submit">
    </form>

    <script>
        function getStudents(classId) {
            var studentDropdown = document.getElementById("student");
            // Clear existing options
            studentDropdown.innerHTML = "<option value=''>Select Student</option>";

            // Make an AJAX request to fetch students based on the selected class
            fetch('get_students.php?class=' + classId)
                .then(response => response.json())
                .then(data => {
                    data.forEach(student => {
                        var option = document.createElement("option");
                        option.value = student.rollid;
                        option.text = student.studentname;
                        studentDropdown.add(option);
                    });
                })
                .catch(error => console.error('Error fetching students:', error));
        }

        // You'll need additional JavaScript functions to handle dynamic subject form generation
    </script>
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
