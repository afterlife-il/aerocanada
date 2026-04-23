<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
if($_SESSION['conectroy']=="parfait"){
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
	
	<!--fusioncharts-->
	<script type="text/javascript" src="fusioncharts/js/fusioncharts.js"></script>
	<script type="text/javascript" src="fusioncharts/js/themes/fusioncharts.theme.fint.js"></script>
	<?php
	//recuperation des dates du jour et du mois dernier
	$today = date("Y-m-d");
	$avant = date('Y-m-d', strtotime('-1 day'));
	?>
	<script type="text/javascript">
  FusionCharts.ready(function(){
    var revenueChart = new FusionCharts({
        "type": "column2d",
        "renderAt": "chartContainer",
        "width": "700",
        "height": "400",
        "dataFormat": "json",
        "dataSource":  {
          "chart": {
            "caption": "Visits of the software between <?php echo $avant;?> and <?php echo $today;?>",
            "subCaption": "Aerocanada",
            "xAxisName": "USERS",
            "yAxisName": "NUMBER OF VISITS",
            "theme": "fint"
         },
         "data": [
          <?php
							//recuperation des visites site
							//****tbl_connection******   id_connection	Employee_ID	ip_address	connection_date  session_id
						    $sqleid="SELECT DISTINCT Employee_ID FROM tbl_connection";
							
						    $reqeid = mysql2_query($sqleid);
						    while($dataeid = mysqli_fetch_array($reqeid))
							{
								//recuperation nom utilsateur
								$sql="SELECT Employee_Name FROM tbl_Employee where Employee_ID='".$dataeid['Employee_ID']."'";
								
								$req = mysql2_query($sql);
								$data = mysqli_fetch_array($req);
								//Fin recuperation nom utilsateur
								
								$sqlc="SELECT count(*) AS totale FROM tbl_connection where Employee_ID='".$dataeid['Employee_ID']."' and connection_date>='".$avant."' and connection_date<='".$today."'";
								//echo $sqlc;
								
								$reqc = mysql2_query($sqlc);
								$datac = mysqli_fetch_array($reqc);
								
							echo "{\"label\": \"".$data['Employee_Name']."\",\"value\": \"".$datac['totale']."\"},";
							}
							
							//Fin recuperation des visites site
		  ?>
          ]
      }

  });
revenueChart.render();
})
</script>
	<!--Fin fusioncharts-->
</head>

<body>
    <div id="wrapper">
  <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
    <?php include "top_menu.php"; ?>                       <!-- barre rouge -->
    <?php if(isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
</nav>
<?php include "after_nav.php"; ?>

 

       

          <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">

            <div class="row">
                <div class="col-lg-10">
                </div>
                <!-- /.col-lg-10 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-10">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            USERS OF THE DAY
                        </div>
						<br><br>
						<div id="chartContainer"></div>
						
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-10 -->
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

    <!-- Page-Level Demo Scripts - Tables - Use for reference -->
    <script type="text/javascript">
    $(document).ready(function() {
        $('#dataTables-example').DataTable({
            responsive: true
        });
    });
    </script>

</body>

</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=".$_SERVER['REQUEST_URI']."\">";
?>