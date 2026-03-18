<?php
session_start();
//error_reporting(0);
include('includes/config.php');


if (!isset($_SESSION['alogin'])) {
    header("Location: index.php");
} else {
    if (isset($_GET['data'])) {
        $selectedRecipientsData = json_decode(urldecode($_GET['data']), true);
        $selectedStudents = $selectedRecipientsData['selectedStudents'] ?? [];
        $message = $selectedRecipientsData['message'] ?? '';
        $phoneNumbers = fetchPhoneNumbers($dbh, $selectedStudents);
    } else {
        echo "Error: Data not provided.";
        exit();
    }
}


function fetchPhoneNumbers($dbh, $selectedStudents)
{
    $studentIds = implode(',', $selectedStudents);
    $sql = "SELECT StudentId, PhoneNumber FROM tblstudents WHERE StudentId IN ($studentIds)";
    $query = $dbh->prepare($sql);
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);
    return array_column($result, 'PhoneNumber', 'StudentId');
}

function authenticate($domain, $username, $secret)
{
    $apiUrl = "https://$domain/api/services/auth/";
    $postData = [
        'username' => $username,
        'secret' => $secret,
        'pass_type' => 'plain',
    ];

    return makeApiRequest($apiUrl, 'POST', $postData);
}

function sendSmsToStudent($domain, $apiKey, $partnerID, $phoneNumber, $message)
{
    $apiUrl = "https://$domain/api/services/sendsms/";
    $postData = [
        'apikey' => $apiKey,
        'partnerID' => $partnerID,
        'mobile' => $phoneNumber,
        'message' => $message,
        'shortcode' => '{{shortcode}}', // Replace with your shortcode if needed
        'pass_type' => 'plain',
    ];

    return makeApiRequest($apiUrl, 'POST', $postData);
}

function makeApiRequest($url, $method, $postData)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if ($method == 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['httpCode' => $httpCode, 'response' => $response];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $authResult = authenticate('{{domain}}', 'Arimi', 'ITNxuQhKGkBSTZAhuTLDhJBaO6jNIPAJ');

    if ($authResult['httpCode'] == 200) {
        foreach ($selectedStudents as $studentId) {
            if (isset($phoneNumbers[$studentId])) {
                $phoneNumber = $phoneNumbers[$studentId];
                $smsResult = sendSmsToStudent('https://sms.textsms.co.ke', 'b29c20bc-b372-4754-aadc-28d2e021d678', '9663', $phoneNumber, $message);

                if ($smsResult['httpCode'] == 200) {
                    // SMS sent successfully
                    // Handle success
                    echo "SMS sent successfully to $phoneNumber<br>";
                } else {
                    // SMS sending failed
                    // Handle failure
                    echo "Failed to send SMS to $phoneNumber. HTTP Code: {$smsResult['httpCode']}, Response: {$smsResult['response']}<br>";
                }
            }
        }
    } else {
        // Authentication failed
        // Handle authentication failure
        echo "Authentication failed. HTTP Code: {$authResult['httpCode']}, Response: {$authResult['response']}<br>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Send SMS</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen">
    <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen">
    <link rel="stylesheet" href="css/prism/prism.css" media="screen">
    <link rel="stylesheet" type="text/css" href="js/DataTables/datatables.min.css" />
    <link rel="stylesheet" href="css/main.css" media="screen">
    <script src="js/modernizr/modernizr.min.js"></script>
    <style>
        /* Add your custom styles here */
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
                                <h2 class="title">Send SMS</h2>
                            </div>
                        </div>
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li><a href="add-recipients.php">Add Recipients</a></li>
                                    <li class="active">Send SMS</li>
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
                                                <h5>Send SMS to Selected Students</h5>
                                            </div>
                                        </div>
                                        <div class="panel-body p-20">

                                            <!-- Form for sending SMS (if needed) -->
                                            <form method="post" action="send-sms.php">
                                                <!-- Add any additional form fields if required -->
    <label for="mobile">Selected Students' Phone Numbers:</label>
    <input type="text" id="mobile" name="mobile" value="<?php echo implode(', ', $phoneNumbers); ?>" readonly>

    <label for="message">Message:</label>
    <textarea id="message" name="message" rows="4" readonly><?php echo $message; ?></textarea>

                                                <!-- Button to send SMS -->
                                                <button type="submit" class="btn btn-success">Send SMS</button>
                                            </form>
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
            // Add your custom scripts here
        </script>
    </body>

    </html>
