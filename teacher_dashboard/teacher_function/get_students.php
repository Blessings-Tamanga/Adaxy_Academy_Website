<?php
// Assume you have a function to fetch students based on the classid
// Replace this with your actual database interaction code

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['class'])) {
    $classId = $_GET['class'];
    $students = fetch_students_for_class($classId);

    // Simulate the data for this example
    $students = [
        ['rollid' => 1, 'studentname' => 'Student 1'],
        ['rollid' => 2, 'studentname' => 'Student 2'],
        // Add more students as needed
    ];

    // Return the data as JSON
    header('Content-Type: application/json');
    echo json_encode($students);
    exit;
}
?>
