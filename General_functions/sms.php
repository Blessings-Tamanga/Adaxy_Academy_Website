<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS Form</title>
</head>
<body>

<!-- Single Message Form -->
<form action="sms.php" method="post">
    <!-- Include fields similar to the Single Message Form -->
    <label for="singleNumber">Recipient Number:</label>
    <input type="text" name="singleNumber" required>
    <br>
    <label for="singleMessage">Message:</label>
    <textarea name="singleMessage" required></textarea>
    <br>
    <label for="singleDevice">Device ID (optional):</label>
    <input type="text" name="singleDevice">
    <br>
    <label for="singleSchedule">Schedule (optional):</label>
    <input type="text" name="singleSchedule" placeholder="YYYY-MM-DD HH:MM:SS">
    <br>
    <label for="singleIsMMS">Send MMS?</label>
    <input type="checkbox" name="singleIsMMS">
    <br>
    <label for="singleAttachments">Attachments (comma-separated URLs, optional for MMS):</label>
    <input type="text" name="singleAttachments">
    <br>
    <label for="singlePrioritize">Prioritize?</label>
    <input type="checkbox" name="singlePrioritize">
    <br>
    <input type="submit" name="submitSingle" value="Send Single Message">
</form>

<!-- Bulk Messages Form -->
<form action="sms.php" method="post">
    <!-- Include fields similar to the Bulk Messages Form -->
    <label for="bulkMessages">Bulk Messages (JSON format):</label>
    <textarea name="bulkMessages" required></textarea>
    <br>
    <label for="bulkOption">Bulk Option:</label>
    <select name="bulkOption">
        <option value="USE_SPECIFIED">Use Specified Devices/SIMs</option>
        <option value="USE_ALL_DEVICES">Use All Devices</option>
        <option value="USE_ALL_SIMS">Use All SIMs</option>
    </select>
    <br>
    <!-- Additional fields for bulk messages -->
    <label for="bulkDevices">Devices (comma-separated, optional):</label>
    <input type="text" name="bulkDevices">
    <br>
    <label for="bulkSchedule">Schedule (optional):</label>
    <input type="text" name="bulkSchedule" placeholder="YYYY-MM-DD HH:MM:SS">
    <br>
    <label for="bulkUseRandomDevice">Use Random Device?</label>
    <input type="checkbox" name="bulkUseRandomDevice">
    <br>
    <input type="submit" name="submitBulk" value="Send Bulk Messages">
</form>

<!-- Add more forms for other functionalities if needed -->

<?php
// Include the sms-functions.php file
require_once 'sms-functions.php';

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["submitSingle"])) {
        // Handle single message submission
        try {
            $number = $_POST["singleNumber"];
            $message = $_POST["singleMessage"];
            $device = $_POST["singleDevice"] ?? 0;
            $schedule = $_POST["singleSchedule"] ?? null;
            $isMMS = isset($_POST["singleIsMMS"]) ? true : false;
            $attachments = $_POST["singleAttachments"] ?? null;
            $prioritize = isset($_POST["singlePrioritize"]) ? true : false;

            $msg = sendSingleMessage($number, $message, $device, $schedule, $isMMS, $attachments, $prioritize);
            print_r($msg);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    } elseif (isset($_POST["submitBulk"])) {
        // Handle bulk message submission
        try {
            $bulkMessages = json_decode($_POST["bulkMessages"], true);
            $bulkOption = $_POST["bulkOption"];
            $bulkDevices = $_POST["bulkDevices"] ?? [];
            $bulkSchedule = $_POST["bulkSchedule"] ?? null;
            $useRandomDevice = isset($_POST["bulkUseRandomDevice"]) ? true : false;

            $msgs = sendMessages($bulkMessages, $bulkOption, $bulkDevices, $bulkSchedule, $useRandomDevice);
            print_r($msgs);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
?>

</body>
</html>
