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
	$today = date("d-m-y");
	$avant = date('d-m-y', strtotime('-1 day'));
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
            "caption": "Number of quotations of the day <?php echo $today;?>",
            "subCaption": "Aerocanada",
            "xAxisName": "USERS",
            "yAxisName": "QUOTATIONS",
            "theme": "fint"
         },
         "data": [
          <?php
							
							
						    $sqleid="SELECT DISTINCT Employee_ID FROM tbl_connection"; 
							
						    $reqeid = mysql2_query($sqleid);
						    while($dataeid = mysqli_fetch_array($reqeid))
							{	
								
								//recuperation nom utilsateur
								$sql="SELECT Employee_Name FROM tbl_Employee where Employee_ID='".$dataeid['Employee_ID']."'";
								
								$req = mysql2_query($sql);
								$data = mysqli_fetch_array($req);
								//Fin recuperation nom utilsateur
								
								// $sqlqj="SELECT DISTINCT Fld_RFQ_ID FROM tbl_RFQ_3 where Fld_Quote_Date BETWEEN '".$avant."' and '".$today."'"; 
								// 
								// $reqqj = mysql2_query($sqlqj);
								// while($dataqj = mysqli_fetch_array($reqqj))
								// {}
								
								// $sqlc="SELECT count(distinct tbl_RFQ_3.Fld_RFQ_ID) AS totale FROM tbl_RFQ_3 left join tbl_RFQ_1 on tbl_RFQ_3.Fld_RFQ_ID=tbl_RFQ_1.Fld_RFQ_ID where  tbl_RFQ_1.Employee_ID='".$dataeid['Employee_ID']."' and tbl_RFQ_3.Fld_Quote_Date BETWEEN '".$avant."' and '".$today."'";
								$sqlc="SELECT count(distinct tbl_RFQ_3.Fld_RFQ_ID) AS totale FROM tbl_RFQ_3 left join tbl_RFQ_1 on tbl_RFQ_3.Fld_RFQ_ID=tbl_RFQ_1.Fld_RFQ_ID where  tbl_RFQ_1.Employee_ID='".$dataeid['Employee_ID']."' and tbl_RFQ_3.Fld_Quote_Date='".$today."'";
								// echo $sqlc;
								
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

 

        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.html"></a>
            </div>
            <!-- /.navbar-header -->

            <?php
		//ajout le menu du haut
		include "top_menu.php";
	   ?>
            <!-- /.navbar-top-links -->

        <?php
		//ajout le menu de gauche
		include "left_menu.php";
	   ?>
            <!-- /.navbar-static-side -->
        </nav>
         <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-10">
                    <h1 class="page-header"></h1>
                </div>
                <!-- /.col-lg-10 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-10">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            
                        </div>
						<br><br>
						<div id="chartContainer">FusionCharts XT will load here!</div>
						<?php //echo $sqlc;?>
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
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php\">";
?>