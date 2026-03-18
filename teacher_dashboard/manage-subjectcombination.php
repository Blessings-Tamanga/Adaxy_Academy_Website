
<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(!isset($_SESSION['alogin'])) 
{   
    header("Location: index.php"); 
}
else{
    if (isset($_GET['delete_scid'])) {
        $scid = intval($_GET['delete_scid']);

        // Perform the deletion
        $sql = "DELETE FROM tblsccode WHERE id = :scid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':scid', $scid, PDO::PARAM_INT);
        $query->execute();

        if ($query) {
            $msg = "Subject combination deleted successfully";
        } else {
            $error = "Error deleting subject combination";
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SRMS Admin Manage Subjects Combination</title>
        <link rel="stylesheet" href="css/bootstrap.min.css" media="screen" >
        <link rel="stylesheet" href="css/font-awesome.min.css" media="screen" >
        <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen" >
        <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen" >
        <link rel="stylesheet" href="css/prism/prism.css" media="screen" > <!-- USED FOR DEMO HELP - YOU CAN REMOVE IT -->
        <link rel="stylesheet" type="text/css" href="js/DataTables/datatables.min.css"/>
        <link rel="stylesheet" href="css/main.css" media="screen" >
        <script src="js/modernizr/modernizr.min.js"></script>
        <style>
            .errorWrap {
                padding: 10px;
                margin: 0 0 20px 0;
                background: #fff;
                border-left: 4px solid #dd3d36;
                -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
                box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
            }
            .succWrap{
                padding: 10px;
                margin: 0 0 20px 0;
                background: #fff;
                border-left: 4px solid #5cb85c;
                -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
                box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
            }
        </style>
    </head>
    <body class="top-navbar-fixed">
        <div class="main-wrapper">

            <!-- ========== TOP NAVBAR ========== -->
            <?php include('includes/topbar.php');?> 
            <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
            <div class="content-wrapper">
                <div class="content-container">
                    <?php include('includes/leftbar.php');?>  

                    <div class="main-page">
                        <div class="container-fluid">
                            <div class="row page-title-div">
                                <div class="col-md-6">
                                    <h2 class="title">Manage Subjects Combination</h2>
                                    
                                </div>
                                
                                <!-- /.col-md-6 text-right -->
                            </div>
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                     <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                     <li> Subjects</li>
                                     <li class="active">Manage Subjects Combination</li>
                                 </ul>
                             </div>
                             
                         </div>
                         <!-- /.row -->
                     </div>
                     <!-- /.container-fluid -->

                     <section class="section">
                        <div class="container-fluid">

                           

                            <div class="row">
                                <div class="col-md-12">

                                    <div class="panel">
                                        <div class="panel-heading">
                                            <div class="panel-title">
                                                <h5>View Subjects Combination Info</h5>
                                            </div>
                                        </div>
                                        <?php if($msg){?>
                                            <div class="alert alert-success left-icon-alert" role="alert">
                                               <strong>Well done!</strong><?php echo htmlentities($msg); ?>
                                               </div><?php } 
                                               else if($error){?>
                                                <div class="alert alert-danger left-icon-alert" role="alert">
                                                    <strong>Oh snap!</strong> <?php echo htmlentities($error); ?>
                                                </div>
                                            <?php } ?>
                                            <div class="panel-body p-20">

                                                <!-- ... Other HTML and PHP code above ... -->


                                                <table id="example" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Subject Combination Code</th>
                                                            <th>Number of Subjects</th>
                                                            <th>Number of Students</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tfoot>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Subject Combination Code</th>
                                                            <th>Number of Subjects</th>
                                                            <th>Number of Students</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </tfoot>
                                                    <tbody>
                                                        <?php
                                                        $sql = "SELECT
                                                        tblsccode.id as scid,
                                                        tblsccode.SCCode,
                                                        COUNT(DISTINCT tblsubjectcombination.SubjectCode) as numSubjects,
                                                        COUNT(DISTINCT tblstudents.StudentId) as numStudents
                                                        FROM
                                                        tblsccode
                                                        LEFT JOIN tblsubjectcombination ON tblsccode.SCCode = tblsubjectcombination.SCCode
                                                        LEFT JOIN tblstudents ON tblsccode.SCCode = tblstudents.SCCode
                                                        GROUP BY
                                                        tblsccode.id, tblsccode.SCCode";
                                                        $query = $dbh->prepare($sql);
                                                        $query->execute();
                                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                        $cnt = 1;
                                                        if ($query->rowCount() > 0) {
                                                            foreach ($results as $result) {
                                                                ?>
                                                                <tr>
                                                                    <td><?php echo htmlentities($cnt); ?></td>
                                                                    <td><?php echo htmlentities($result->SCCode); ?></td>
                                                                    <td><?php echo htmlentities($result->numSubjects); ?></td>
                                                                    <td><?php echo htmlentities($result->numStudents); ?></td>
                                                                    <td>
                                                                        <a href="edit-subjectcombination.php?scid=<?php echo htmlentities($result->scid); ?>"><i class="fa fa-edit" title="Edit Record"></i> </a>
                                                                        <a href="?delete_scid=<?php echo htmlentities($result->scid); ?>" onclick="return confirm('Do you really want to delete this subject combination?');"><i class="fa fa-trash" title="Delete Record"></i> </a>
                                                                    </td>
                                                                </tr>
                                                                <?php $cnt = $cnt + 1;
                                                            }
                                                        } ?>
                                                    </tbody>
                                                </table>

                                                
                                                <!-- /.col-md-12 -->
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->

                                    
                                </div>
                                <!-- /.col-md-12 -->
                            </div>
                        </div>
                        <!-- /.panel -->
                    </div>
                    <!-- /.col-md-6 -->

                </div>
                <!-- /.row -->

            </div>
            <!-- /.container-fluid -->
        </section>
        <!-- /.section -->

    </div>
    <!-- /.main-page -->

    

</div>
<!-- /.content-container -->
</div>
<!-- /.content-wrapper -->

</div>
<script src="js/jquery/jquery-2.2.4.min.js"></script>
<script src="js/bootstrap/bootstrap.min.js"></script>
<script src="js/pace/pace.min.js"></script>
<script src="js/lobipanel/lobipanel.min.js"></script>
<script src="js/iscroll/iscroll.js"></script>
<script src="js/prism/prism.js"></script>
<script src="js/DataTables/datatables.min.js"></script>
<script src="js/main.js"></script>
<script>
    $(function($) {
        $('#example').DataTable();

        $('#example2').DataTable( {
            "scrollY":        "300px",
            "scrollCollapse": true,
            "paging":         false
        } );

        $('#example3').DataTable();
    });
</script>

<script>
            // JavaScript function to handle the delete operation
    function deleteSubjectCombination(scid) {
                // Confirm deletion
        if (confirm('Do you really want to delete this subject combination?')) {
                    // Create a new XMLHttpRequest object
            var xhr = new XMLHttpRequest();

                    // Specify the request method, URL, and asynchronous flag
            xhr.open('GET', 'delete-subjectcombination.php?scid=' + scid, true);

                    // Set up the onload and onerror event handlers
            xhr.onload = function () {
                if (xhr.status >= 200 && xhr.status < 300) {
                            // If the request was successful, display a success message
                    alert('Subject combination deleted successfully');
                            // Reload the page or update the table if needed
                    location.reload();
                } else {
                            // If there was an error, display an error message
                    alert('Error deleting subject combination');
                }
            };

            xhr.onerror = function () {
                        // If there was a network error, display an error message
                alert('Network error occurred');
            };

                    // Send the request
            xhr.send();
        }
    }
</script>
</body>
</html>
<?php } ?>

