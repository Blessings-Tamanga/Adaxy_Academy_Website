<?php
// send-bulk-messages.php

// Include the functions and constants from the provided PHP code
include 'sms-functions.php';

// Retrieve form data
$bulkMessages = json_decode($_POST['bulkMessages'], true);
$bulkOption = $_POST['bulkOption'];
$bulkDevices = isset($_POST['bulkDevices']) ? json_decode($_POST['bulkDevices'], true) : [];
$bulkSchedule = isset($_POST['bulkSchedule']) ? strtotime($_POST['bulkSchedule']) : null;
$bulkUseRandomDevice = isset($_POST['bulkUseRandomDevice']);

try {
    // Call the sendMessages function
    $result = sendMessages($bulkMessages, $bulkOption, $bulkDevices, $bulkSchedule, $bulkUseRandomDevice);

    // Handle the result as needed
    print_r($result);

} catch (Exception $e) {
    echo $e->getMessage();
}
?>
