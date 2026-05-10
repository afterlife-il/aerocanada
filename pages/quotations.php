<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
if ($_SESSION['conectroy'] == "parfait") {
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Aerocanada-industries.com</title>

    <!-- Bootstrap Core CSS -->
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="../vendor/metisMenu/metisMenu.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="../vendor/datatables-plugins/dataTables.bootstrap.css" rel="stylesheet">

    <!-- DataTables Responsive CSS -->
    <link href="../vendor/datatables-responsive/dataTables.responsive.css" rel="stylesheet">

    <!-- Custom CSS -->
     <link href="../dist/css/sb-admin-2.css" rel="stylesheet">
<link href="../dist/css/aci-overrides.css" rel="stylesheet"> <!-- <= impératif, et APRÈS sb-admin-2.css -->

    <!-- Custom Fonts -->
    <link href="../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
    <div id="wrapper">
  <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
    <?php include "top_menu.php"; ?>                       <!-- barre rouge -->
    <?php if(isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
</nav>
<?php include "after_nav.php"; ?>

        <div id="<?php echo $_SESSION['leftmenu'] == 'open' ? 'page-wrapper' : 'page-wrapper2'; ?>">
            <div class="row">
                <div class="col-lg-12">
                </div>
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            QUOTATIONS
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <table width="100%" class="table table-striped table-bordered table-hover" id="dataTables-example">
                                <thead>
                                    <tr>
                                        <th>RFQ ID</th>
                                        <th>QUOTE DATE</th>
                                        <th>SENT</th>
                                        <th>SENT DATE</th>
                                        <th>CUSTOMER</th>
                                        <th>CONTACT</th>
                                        <th>EMAIL</th>
                                        <th>PN</th>
                                        <th>DESCRIPTION</th>
                                        <th>SN</th>
                                        <th>QTY</th>
                                        <th>CONDITION</th>
                                        <th>PRICE</th>
                                        <th>$/€</th>
                                        <th>REMARK</th>
                                        <th>SOURCE</th>
                                        <th>LEAD TIME</th>
                                        <th>PRIORITY</th>
                                        <th>ACI770</th>
                                        <th>ID</th>
                                        <?php if ($_SESSION['statut'] == "SuperAdmin") { ?>
                                        <th></th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                            </table>
                            <div style="display:none" id="bloccompany"><div id="divcompany" align="center"></div></div>   
                            <!-- /.table-responsive -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->
    </div>
    <!-- /#wrapper -->

    <!-- jQuery -->
    <script src="../vendor/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="../vendor/metisMenu/metisMenu.min.js"></script>

    <!-- DataTables JavaScript -->
    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
    <script src="../vendor/datatables-responsive/dataTables.responsive.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <script>
    $(document).ready(function() {
        $('#dataTables-example').DataTable({
            "responsive": true,
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "quotedata.php",
                "type": "POST",
                "error": function(xhr, error, thrown) {
                    console.log(xhr.responseText);
                }
            }
        });
    });

    function detailcompany(id) {
        var bloc = document.getElementById('bloccompany');
        bloc.style.display = 'table-row';
        document.getElementById("divcompany").innerHTML = '<div id="divcompany" align="center"><img src="../images/loader.gif" border="0"></div>';

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "detailcompany.php?id=" + id, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() { 
            if (xhr.readyState == 4) {
                document.getElementById('divcompany').innerHTML = xhr.responseText;
                document.location.href = "#bloccompany";
            }
        };
        xhr.send("id=" + id);
    }

    function fermeturedetailcompany() {
        var bloc = document.getElementById('bloccompany');
        bloc.style.display = 'none';
    }
    </script>
</body>

</html>
<?php
} else {
    echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=" . $_SERVER['REQUEST_URI'] . "\">";
}
?>
