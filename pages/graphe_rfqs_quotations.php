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

    <title>CHART RFQ/QUOTATION</title>

    <!-- Bootstrap Core CSS -->
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="../vendor/metisMenu/metisMenu.min.css" rel="stylesheet">

    <!-- Custom CSS -->
     <link href="../dist/css/sb-admin-2.css" rel="stylesheet">
<link href="../dist/css/aci-overrides.css" rel="stylesheet"> <!-- <= impératif, et APRÈS sb-admin-2.css -->

    <!-- Morris Charts CSS -->
    <link href="../vendor/morrisjs/morris.css" rel="stylesheet">

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

 

        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href=""></a>
            </div>
            <!-- /.navbar-header -->

            <?php
		//ajout le menu du haut
		include "top_menu.php";
	   ?>
            <!-- /.navbar-top-links -->

           <?php
		//ajout le menu de gauche
		if($_SESSION['leftmenu']=='open') include "left_menu.php";
	   ?>
            <!-- /.navbar-static-side -->
        </nav>

         <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">

            <div class="row">
                <div class="col-lg-12">
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-bar-chart-o fa-fw"></i> CHART RFQ/QUOTATION
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div id="morris-bar-chart"></div>
							<div style="height: 15px;width: 15px;background: #0b62a4;"></div>RFQ
							<div style="height: 15px;width: 15px;background: #7a92a3;"></div>QUOTATION
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

    <!-- Morris Charts JavaScript -->
    <script src="../vendor/raphael/raphael.min.js"></script>
    <script src="../vendor/morrisjs/morris.min.js"></script>
    <script src="../data/morris-data.js"></script>
	<!--donnees du graphe-->
	<?php
	//recuperation des dates du jour et du mois dernier
	$today = date("d/m/Y");
	$today2 = date("d-m-y");
	$avant = date('d-m-y', strtotime('-1 day'));
	?>
	<script type="text/javascript">
	$(function() {

    Morris.Bar({
        element: 'morris-bar-chart',
        data: [<?php
							
							
						    $sqleid="SELECT DISTINCT Employee_ID FROM tbl_connection"; 
							
						    $reqeid = mysql2_query($sqleid);
						    while($dataeid = mysqli_fetch_array($reqeid))
							{	
								
								//recuperation nom utilsateur
								$sql="SELECT Employee_Name FROM tbl_Employee where Employee_ID='".$dataeid['Employee_ID']."'";
								
								$req = mysql2_query($sql);
								$datauser = mysqli_fetch_array($req);
								//Fin recuperation nom utilsateur
								
								//recuperation nbr rfq
								//****tbl_RFQ_1****ID Fld_RFQ_ID  Fld_Qty  Fld_Part_ID  Fld_Observation  Fld_Customer_ID date  Fld_RFQ_Type_ID  Fld_Priority_ID  Employee_ID  id_company_contact  Fld_Payment_Term_ID  Fld_Condition_ID pn_rfq description_rfq	
								$sqlc="SELECT count(distinct Fld_RFQ_ID) AS totale FROM tbl_RFQ_1 where Employee_ID='".$dataeid['Employee_ID']."' and date='".$today."'";
								// echo $sqlc;
								
								$reqc = mysql2_query($sqlc);
								$datac = mysqli_fetch_array($reqc);
								
								//recuperation nbr quotation
								$sqlq="SELECT count(distinct tbl_RFQ_3.Fld_RFQ_ID) AS totale FROM tbl_RFQ_3 left join tbl_RFQ_1 on tbl_RFQ_3.Fld_RFQ_ID=tbl_RFQ_1.Fld_RFQ_ID where  tbl_RFQ_1.Employee_ID='".$dataeid['Employee_ID']."' and tbl_RFQ_3.Fld_Quote_Date='".$today2."'";
								// echo $sqlc;
								
								$reqq = mysql2_query($sqlq);
								$dataq = mysqli_fetch_array($reqq);
								
							echo "{label: '".$datauser['Employee_Name']."',rfq: '".$datac['totale']."',quotation: '".$dataq['totale']."'},";
							}
							
							//Fin recuperation des visites site
		  ?>],
        xkey: 'label',
        ykeys: ['rfq', 'quotation'],
        labels: ['rfq', 'quotation'],
        hideHover: 'auto',
        resize: true
    });
    
});

	</script>

    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

</body>

</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=".$_SERVER['REQUEST_URI']."\">";
?>