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
                    <h1 class="page-header">Modif Part</h1>
                </div>
                <!-- /.col-lg-10 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-10">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            
                        </div>
						<?php
						require('../classes/parts.class.php');
						$objet=new parts();
						$donnee = $objet->get_part($_GET['Fld_Part_ID']);
						foreach($donnee as $reponse)
									{
						?>
						<form id="formajoutpart" role="form" method="post" action="valid_modif_part.php">
						<!--
						*****tbl_Parts*************
Fld_Part_ID
Fld_Part_Nbr
Fld_Part_Desc
Fld_Part_MFG
Fld_Part_MFG_Old
Fld_AC_ID
Fld_Old_LP
Fld_Part_List_Price
Fld_Part_Price_Currency_ID
Fld_Part_LP_Date
Fld_Remark
						-->
									
									
						<div class="panel-body">
							<div class="row">
							
								<div class="col-lg-5">
								
									<div class="form-group">
                                            <label>PN</label>
                                            <input class="form-control" name="Fld_Part_Nbr" value="<?php echo $reponse['Fld_Part_Nbr'];?>">
                                        </div>
									 
									<div class="form-group">
                                            <label>Part description</label>
                                            <input class="form-control" name="Fld_Part_Desc" value="<?php echo $reponse['Fld_Part_Desc'];?>">
                                        </div>
									
									<div class="form-group">
                                            <label>MFG/OIM</label>
                                            <select class="form-control" name="Fld_Part_MFG">
											<?php

											$sqldiv="SELECT distinct(Fld_Company_Name),Fld_Company_ID FROM tb_company";
											
											//echo $sqldiv;
											$reqemp = mysql2_query($sqldiv);
											while($datadiv = mysqli_fetch_array($reqemp))
											{
												echo "<option value='".$datadiv['Fld_Company_ID']."'";
												if ($datadiv['Fld_Company_ID']==$reponse['Fld_Part_MFG']) echo " selected";
												echo ">".$datadiv['Fld_Company_Name']."</option>";
											}
					                        
											?>
                                                
                                            </select>
                                        </div>
										
										<div class="form-group">
                                            <label>Aircraft</label>
                                            <select class="form-control" name="Fld_AC_ID">
											<?php
											// Fld_AC_ID  Fld_AC_Model  Fld_AC_Series  Fld_AC_Manufacturer  Fld_AC_Engine_Model  Fld_AC_Engine_Series
											$sqldiv="SELECT Distinct(Fld_AC_Model),Fld_AC_ID FROM tbl_Aircraft";
											
											//echo $sqldiv;
											$reqemp = mysql2_query($sqldiv);
											while($datadiv = mysqli_fetch_array($reqemp))
											{
												echo "<option value='".$datadiv ['Fld_AC_ID']."'";
												if ($datadiv['Fld_AC_ID']==$reponse['Fld_AC_ID'])echo " selected";
												echo ">".$datadiv ['Fld_AC_Model']."</option>";
											}
											?>
                                                
                                            </select>
                                        </div>
									
									<div class="form-group">
                                            <label>List Price</label>
                                            <input class="form-control" name="Fld_Part_List_Price" value="<?php echo $reponse['Fld_Part_List_Price'];?>">
                                        </div>
									
									<div class="form-group">
                                            <label>Currency</label>
                                            <select class="form-control" name="Fld_Part_Price_Currency_ID">
											<?php
											//recuperation du nom de la currency	
											// Fld_Currency_ID    Fld_Currency_Text
											$sqldiv="SELECT * FROM tbl_Currency";
											
											//echo $sqldiv;
											$reqemp = mysql2_query($sqldiv);
											while($datadiv = mysqli_fetch_array($reqemp))
											{
												echo "<option value='".$datadiv ['Fld_Currency_ID']."'";
												if ($datadiv['Fld_Currency_ID']==$reponse['Fld_Part_Price_Currency_ID'])echo " selected";
												echo ">".$datadiv ['Fld_Currency_Text']."</option>";
											}
											?>
                                                
                                            </select>
                                        </div>
									
									
									
								</div>
								<div class="col-lg-5">

										
										<div class="form-group">
                                            <label>Remark</label>
                                            
											<textarea class="form-control" rows="3" name="Fld_Remark"> <?php echo $reponse['Fld_Remark'];?></textarea>
                                        </div>
										
									
                                </div>
										
                                <!-- /.col-lg-5 (nested) -->
                            </div>
							<input type="hidden" name="Fld_Part_LP_Date" value="<?php echo $reponse['Fld_Part_LP_Date'];?>">
							<input type="hidden" name="Fld_Part_ID" value="<?php echo $reponse['Fld_Part_ID'];?>">
										
									<button type="submit" class="btn btn-default">Validate</button>
                            </div>

									
									</div>

                        <!-- /.panel-body -->
									
						</form>
									<?php }?>
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