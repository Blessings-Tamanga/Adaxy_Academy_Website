<?php
session_start();
include('includes/config.php');

if (!isset($_SESSION['alogin'])) {
    header("Location: index.php");
} else {
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SRMS Admin Manage Timetable</title>
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
                                <h2 class="title">Manage Timetable</h2>
                            </div>
                        </div>
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li> Timetable</li>
                                    <li class="active">Manage Timetable</li>
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
                                                <h5>View Timetable Info</h5>
                                            </div>
                                        </div>
                                        <div class="panel-body p-20">
                                            <table id="example" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Class</th>
                                                        <th>Day</th>
                                                        <th>Start Time</th>
                                                        <th>End Time</th>
                                                        <th>Subject</th>
                                                        <th>Teacher</th>
                                                        <th>Venue</th> <!-- New column for Venue -->
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tfoot>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Class</th>
                                                        <th>Day</th>
                                                        <th>Start Time</th>
                                                        <th>End Time</th>
                                                        <th>Subject</th>
                                                        <th>Teacher</th>
                                                        <th>Venue</th> <!-- New column for Venue -->
                                                        <th>Action</th>
                                                    </tr>
                                                </tfoot>
                                                <tbody>
                                                    <?php
                                                    $sql = "SELECT tt.id, CONCAT(cl.ClassName, ' (', cl.Section, ')') AS ClassName, tt.Day, tt.StartTime, tt.EndTime, sb.SubjectName, t.Name AS TeacherName, 
                COALESCE(v.VenueName, 'In Class') AS VenueName
                FROM tbltimetable tt
                LEFT JOIN tblclasses cl ON tt.ClassId = cl.id
                LEFT JOIN tblsubjects sb ON tt.SubjectId = sb.id
                LEFT JOIN tblteachers t ON tt.TeacherTNumber = t.TNumber
                LEFT JOIN tblvenues v ON tt.Venue = v.id";

                                                    $query = $dbh->prepare($sql);
                                                    $query->execute();
                                                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                    $cnt = 1;
                                                    if ($query->rowCount() > 0) {
                                                        foreach ($results as $result) { ?>
                                                            <tr>
                                                                <td><?php echo htmlentities($cnt); ?></td>
                                                                <td><?php echo htmlentities($result->ClassName); ?></td>
                                                                <td><?php echo htmlentities($result->Day); ?></td>
                                                                <td><?php echo htmlentities($result->StartTime); ?></td>
                                                                <td><?php echo htmlentities($result->EndTime); ?></td>
                                                                <td><?php echo htmlentities($result->SubjectName); ?></td>
                                                                <td><?php echo htmlentities($result->TeacherName); ?></td>
                                                                <td><?php echo htmlentities($result->VenueName); ?></td> <!-- Venue column -->
                                                                <td>
                                                                    <!-- Add your action buttons (Edit and Delete) here -->
                                                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#timetableModal<?php echo htmlentities($result->id); ?>">
                                                                        Edit
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                            <!-- Modal -->
                                                            <div class="modal fade" id="timetableModal<?php echo htmlentities($result->id); ?>" tabindex="-1" role="dialog" aria-labelledby="timetableModalLabel" aria-hidden="true">
                                                                <div class="modal-dialog" role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title" id="timetableModalLabel">Edit Timetable Entry</h5>
                                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <!-- Include the form or edit-timetable-entry.php with appropriate parameters -->
                                                                            <iframe src="edit-timetable-entry.php?entryId=<?php echo htmlentities($result->id); ?>" width="100%" height="520px" frameborder="0"></iframe>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                                            <!-- Add additional buttons if needed -->
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <?php $cnt = $cnt + 1;
                                                        }
                                                    } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
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
        $(function ($) {
            $('#example').DataTable();
        });
    </script>
</body>

</html>
<?php } ?>
