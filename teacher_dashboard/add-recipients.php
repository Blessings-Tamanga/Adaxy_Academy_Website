<?php
session_start();
//error_reporting(0);
include('includes/config.php');

if (!isset($_SESSION['alogin'])) {
    header("Location: index.php");
} else {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $selectedRecipients = processForm();

        $_SESSION['selectedRecipients'] = $selectedRecipients;

        // Debugging: Output the selected recipients' data
        echo '<pre>';
        print_r($_SESSION['selectedRecipients']);
        echo '</pre>';

        // Redirect to the same page after processing the form
        // header("Location: confirm-recipients.php");
        // exit();
    }

    function processForm() {
        // Implement the processForm() function based on your form structure
        // Example: Fetch and process the selected recipients' data
        $selectedClasses = $_POST['selectedClasses'] ?? [];
        $selectedStudents = $_POST['selectedStudents'] ?? [];

        // Add your processing logic here

        // Return the selected recipients data
        return [
            'selectedClasses' => $selectedClasses,
            'selectedStudents' => $selectedStudents,
            // Add any other selected recipients data if needed
        ];
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>DAXY ACADAMY Admin Select Recipients</title>
        <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
        <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
        <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen">
        <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen">
        <link rel="stylesheet" href="css/prism/prism.css" media="screen">
        <link rel="stylesheet" type="text/css" href="js/DataTables/datatables.min.css"/>
        <link rel="stylesheet" href="css/main.css" media="screen">
        <script src="js/modernizr/modernizr.min.js"></script>
        <style>
            
        </style>
    </head>
    <body class="top-navbar-fixed">
        <div class="main-wrapper">
            <!-- ========== TOP NAVBAR ========== -->
            <?php include('includes/topbar.php'); ?>
            <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
            <div class="content-wrapper">
                <div class="content-container">
                    <?php include('includes/leftbar.php'); ?>
                    <div class="main-page">
                        <div class="container-fluid">
                            <div class="row page-title-div">
                                <div class="col-md-6">
                                    <h2 class="title">Add Recipients</h2>
                                </div>
                            </div>
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                        <li> Students</li>
                                        <li class="active">Add Recipients</li>
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
                                                    <!-- Heading or instructions for the user -->
                                                    <h5>Select Recipients</h5>
                                                </div>
                                            </div>
                                            <div class="panel-body p-20">
                                                <!-- Tabs for recipient selection -->
                                                <ul class="nav nav-tabs">
                                                    <li class="active"><a data-toggle="tab" href="#selectClass">Select Class</a></li>
                                                    <li><a data-toggle="tab" href="#sendAll">Send to Entire School</a></li>
                                                    <li><a data-toggle="tab" href="#sendSpecific">Send to Specific</a></li>
                                                </ul>

                                                <!-- Tab content -->
                                                <div class="tab-content">
                                                    <!-- Tab: Select Class -->
                                                    <div id="selectClass" class="tab-pane fade in active">
                                                        <h4>Select Class</h4>
                                                        <!-- Add a form with a list of classes and checkboxes -->
                                                        <form id="selectClassForm" action="add-recipients.php" method="post">
                                                            <?php
                                                            // Fetch and display the list of classes
                                                            $sql = "SELECT id, ClassName, Section FROM tblclasses";
                                                            $query = $dbh->prepare($sql);
                                                            $query->execute();
                                                            $classes = $query->fetchAll(PDO::FETCH_OBJ);

                                                            foreach ($classes as $class) {
                                                                echo '<div class="checkbox">';
                                                                echo '<label><input type="checkbox" name="selectedClasses[]" value="' . $class->id . '"> ' . $class->ClassName . ' (' . $class->Section . ')</label>';
                                                                echo '</div>';
                                                            }
                                                            ?>
                                                            <!-- Display the fetched students here -->
                                                            <div id="studentsList"></div>
                                                            <button type="submit" class="btn btn-primary">Fetch Students</button>
                                                        </form>
                                                    </div>

                                                    <!-- Tab: Send to Entire School -->
                                                    <div id="sendAll" class="tab-pane fade">
                                                        <h4>Send to Entire School</h4>
                                                        <p>Are you sure you want to send the message to the entire school?</p>
                                                    </div>

                                                    <!-- Add Recipient Tab Content -->
                                                    <div class="tab-pane fade" id="sendSpecific">
                                                        <h3 class="tab-title">Send to Specific</h3>
                                                        <!-- Selected Students Count -->
                                                        <p id="selectedCount">0 students selected</p>

                                                        <!-- Search Box for Filtering Students -->
                                                        <div class="form-group">
                                                            <input type="text" id="searchStudent" class="form-control" placeholder="Search for a student">
                                                        </div>
                                                        <!-- Students Table -->
                                                        <table id="studentsTable" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                                                            <thead>
                                                                <tr>
                                                                    <th>Roll ID</th>
                                                                    <th>Student Name</th>
                                                                    <th>Class Name (Section)</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                $sql = "SELECT tblstudents.StudentName,tblstudents.RollId,tblstudents.SCCode,tblstudents.RegDate,tblstudents.StudentId,tblstudents.Status,tblclasses.ClassName,tblclasses.Section from tblstudents join tblclasses on tblclasses.id=tblstudents.ClassId";
                                                                $query = $dbh->prepare($sql);
                                                                $query->execute();
                                                                $students = $query->fetchAll(PDO::FETCH_OBJ);
                                                                $cnt = 1;
                                                                if ($query->rowCount() > 0) {
                                                                    foreach ($students as $student) {
                                                                        echo '<tr>';
                                                                        echo '<td>' . htmlentities($student->RollId) . '</td>';
                                                                        echo '<td>' . htmlentities($student->StudentName) . '</td>';
                                                                        echo '<td>' . htmlentities($student->ClassName . ' (' . $student->Section . ')') . '</td>';
                                                                        echo '<td><button class="btn btn-primary btn-sm addStudent" data-id="' . htmlentities($student->StudentId) . '">Add</button></td>';
                                                                        echo '</tr>';
                                                                    }
                                                                }
                                                                ?>
                                                            </tbody>
                                                        </table>
                                                        <!-- Selected Students List -->
                                                        <div class="selected-students">
                                                            <h4>Selected Students:</h4>
                                                            <ul id="selectedStudentsList"></ul>
                                                        </div>
                                                        <!-- Message Input and Send Button -->
<div class="form-group">
    <label for="message">Type your message:</label>
    <textarea class="form-control" id="message" name="message" rows="3"></textarea>
</div>
<button id="sendButton" class="btn btn-success" onclick="redirectToSendSMS()">Send Message</button>

                                                    </div>
                                                    <!-- End of Send to Specific Tab Content -->

                                                    <!-- Button to proceed to the next page -->
                                                    <!-- <button id="proceedBtn" class="btn btn-primary">Proceed to Confirm Recipients</button> -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>

            <!-- ========== COMMON JS FILES ========== -->
            <script src="js/jquery/jquery-2.2.4.min.js"></script>
            <script src="js/bootstrap/bootstrap.min.js"></script>
            <script src="js/pace/pace.min.js"></script>
            <script src="js/lobipanel/lobipanel.min.js"></script>
            <script src="js/iscroll/iscroll.js"></script>

            <!-- ========== PAGE JS FILES ========== -->
            <script src="js/prism/prism.js"></script>
            <script src="js/DataTables/datatables.min.js"></script>

            <!-- ========== THEME JS ========== -->
            <script src="js/main.js"></script>
            <script>
// Array to store selected students
var selectedStudents = [];

// DataTable initialization
var studentsTable = $('#studentsTable').DataTable();

// Function to update the selected students list display
function updateSelectedStudentsList() {
    var selectedList = $('#selectedStudentsList');
    selectedList.empty();

    // Display the selected students count
    $('#selectedCount').text(selectedStudents.length + ' students selected');

    // Display each selected student
    selectedStudents.forEach(function (studentId) {
        var student = getStudentById(studentId);
        if (student) {
            // Display the student info as "rollid name"
            var studentDisplay = student.RollId + ' ' + student.StudentName;

            // Create list item with remove button
            var listItem = $('<li>').text(studentDisplay);
            var removeButton = $('<button class="btn btn-danger btn-sm removeStudent">Remove</button>').data('id', studentId);
            listItem.append(removeButton);

            // Append the list item to the existing list
            selectedList.append(listItem);
        }
    });
}

// Function to get student details by ID
function getStudentById(studentId) {
    // Replace this with your actual logic to fetch student details by ID
    var students = <?php echo json_encode($students); ?>;
    return students.find(function (student) {
        return student.StudentId == studentId;
    });
}

// Add a click event listener to the "Remove" button in the selected students list
$('#selectedStudentsList').on('click', 'button.removeStudent', function () {
    // Get the selected student's ID
    var studentId = $(this).data('id');

    // Remove the student from the selected students array
    var indexToRemove = selectedStudents.indexOf(studentId);
    if (indexToRemove !== -1) {
        selectedStudents.splice(indexToRemove, 1);
    }

    // Update the selected students list display
    updateSelectedStudentsList();
});

// Add a click event listener to the "Add" button
$('#studentsTable tbody').on('click', 'button.addStudent', function () {
    // Get the selected student's ID
    var studentId = $(this).data('id');

    // Check if the student is not already in the list
    if (selectedStudents.indexOf(studentId) === -1) {
        // Add the student to the list
        selectedStudents.push(studentId);

        // Update the selected students list display
        updateSelectedStudentsList();
    }
});

// Add a keyup event listener to the search input for filtering students
$('#searchStudent').on('keyup', function () {
    studentsTable.search(this.value).draw();
});

            </script>

            <script>
// Function to encode selected students and message and redirect to send-sms.php
function redirectToSendSMS() {
    // Check if at least one student is selected
    if (selectedStudents.length === 0) {
        alert('Please select at least one student.');
        return;
    }

    // Get the message from the textarea
    var message = $('#message').val();

    // Check if the message is not empty
    if (message.trim() === '') {
        alert('Please enter a message.');
        return;
    }

    // Create an object to hold selected students and message
    var sendData = {
        selectedStudents: selectedStudents,
        message: message
    };

    // Encode the data as a JSON string
    var encodedData = encodeURIComponent(JSON.stringify(sendData));

    // Redirect to send-sms.php with the encoded data in the URL
    window.location.href = 'send-sms.php?data=' + encodedData;
}
</script>
        </body>
    </html>
    <?php
}
?>
