<?php
// send-single-message.php

// Include the functions and constants from the provided PHP code
include 'sms-functions.php';

// Retrieve form data
$number = $_POST['singleNumber'];
$message = $_POST['singleMessage'];
$device = isset($_POST['singleDevice']) ? $_POST['singleDevice'] : 0;
$schedule = isset($_POST['singleSchedule']) ? strtotime($_POST['singleSchedule']) : null;
$isMMS = isset($_POST['singleIsMMS']);
$attachments = isset($_POST['singleAttachments']) ? $_POST['singleAttachments'] : null;
$prioritize = isset($_POST['singlePrioritize']);

try {
    // Call the sendSingleMessage function
    $result = sendSingleMessage($number, $message, $device, $schedule, $isMMS, $attachments, $prioritize);

    // Handle the result as needed
    print_r($result);

} catch (Exception $e) {
    echo $e->getMessage();
}
?>
