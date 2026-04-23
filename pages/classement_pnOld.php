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

    <title>Classement des PN les plus demandees</title>

    <!-- Bootstrap Core CSS -->
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="../vendor/metisMenu/metisMenu.min.css" rel="stylesheet">

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
    <?php include "top_menu.php"; ?>  <!-- barre rouge avec SON burger -->
    <?php if(isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
  </nav>

  <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">
    <!-- ICI ton contenu de page (le panel ADDRESS TYPE, etc.) -->
    <div class="row"> 
  </div><!-- /page-wrapper|2 -->
</div><!-- /wrapper -->

    <!-- ton contenu -->
  </div>
</div>

        <!-- Page Content -->
        <div id="page-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <h1 class="page-header"></h1>
						<table>
						<thead>
                                    <tr>
										<th>PN</th>
                                        <th>DESCRIPTION</th>
										<th>NBR&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                        <th>DATE DERNIERE DEMANDE</th>
                                        
                                    </tr>
                                </thead>
								<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
						<?php
											//****tbl_RFQ_1****ID Fld_RFQ_ID  Fld_Qty  Fld_Part_ID  Fld_Observation  Fld_Customer_ID date  Fld_RFQ_Type_ID  Fld_Priority_ID  Employee_ID  id_company_contact  Fld_Payment_Term_ID  Fld_Condition_ID  pn_rfq description_rfq
											$sqldiv="SELECT pn_rfq,description_rfq,Fld_Part_ID,count(*) as compte FROM tbl_RFQ_1 where Fld_RFQ_ID>'2017-00-00-000000' GROUP BY pn_rfq ORDER BY compte DESC";
											
											$reqemp = mysql2_query($sqldiv);
											while($datadiv = mysqli_fetch_array($reqemp))
											{
												if((!empty($datadiv['pn_rfq']))&&($datadiv['pn_rfq']!='pntest23')){
													//recuperation de la description pn 
													$sqldesfrompn="SELECT Fld_Part_Desc FROM tbl_Parts where Fld_Part_Nbr like '%".$datadiv['pn_rfq']."%'";
													
													$reqdesfrompn = mysql2_query($sqldesfrompn);
													$datadesfrompn = mysqli_fetch_array($reqdesfrompn);
													//Fin recuperation de la description pn 
													//recuperation de la date derniere visite
													$sqldatelv="SELECT max(Fld_RFQ_ID) as datemax FROM tbl_RFQ_1 where pn_rfq like '%".$datadiv['pn_rfq']."%'";
													
													$reqdatelv = mysql2_query($sqldatelv);
													$datadatelv = mysqli_fetch_array($reqdatelv);
													$rest = substr($datadatelv['datemax'], 0, 10);
													//recuperation de la date derniere visite
												$pn = urlencode($datadiv['pn_rfq']);
echo "<tr>
        <td><a href='Part-Nbr.php?pn=$pn'>" . htmlspecialchars($datadiv['pn_rfq'], ENT_QUOTES, 'UTF-8') . "</a></td>
        <td>" . htmlspecialchars($datadesfrompn['Fld_Part_Desc'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>
        <td>" . htmlspecialchars($datadiv['compte'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>
        <td align='center'>" . htmlspecialchars($rest ?? '', ENT_QUOTES, 'UTF-8') . "</td>
      </tr>";

												}
											}
											?>
											<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
											</table>
                    </div>
                    <!-- /.col-lg-12 -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /.container-fluid -->
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

    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

</body>

</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php\">";
?>