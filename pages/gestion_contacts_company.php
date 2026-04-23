<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
if($_SESSION['conectroy']=="parfait"){
?>
<!DOCTYPE html>
<html lang="en">
<?php
include_once "conf.php";
include_once "page_titles.php";
?>
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
			<!--CSS rating ajoute par roy-->
			<link href="rating.css" rel="stylesheet">
			<!--Fin CSS rating ajoute par roy-->
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
                    <h1 class="page-header">Edit Contact company</h1>
                </div>
                <!-- /.col-lg-10 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-10">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            
                        </div>
						
						<form id="formajoutcontactcompany" role="form" method="post" action="valid_modif_contact_company.php">
				
					<?php
								//*****************************************************************CONTACT COMPANY
								/*Table tb_company_contact
								*************************************
								id_company_contact
								Fld_Linked_ID
								Fld_Company_ID
								Company_Old_Id
								Fld_Contact_Name
								Fld_Contact_Phone
								Fld_Contact_Phone2
								Fld_Contact_Fax
								Fld_Company_Mobile
								Fld_Contact_Division_ID
								Fld_Contact_Email
								Fld_Contact_Title
								Fld_Contact_Remark
								*/
								// getting total number records without any search
								$sql="SELECT * FROM tb_company_contact where id_company_contact=".$_GET['id_company_contact'];
								
								$req = mysql2_query($sql);
								$data = mysqli_fetch_array($req);
								
								?>
									
									
						<div class="panel-body">
							<div class="row">
							
								<div class="col-lg-5">
								
									<div class="form-group">
                                            <label>Contact Name</label>
                                            <input class="form-control" name="Fld_Contact_Name" value="<?php echo $data['Fld_Contact_Name']?>">
                                        </div>
									
									<div class="form-group">
                                            <label>Contact Phone</label>
                                            <input class="form-control" name="Fld_Contact_Phone" value="<?php echo $data['Fld_Contact_Phone']?>">
                                        </div>
									
									<div class="form-group">
                                            <label>Contact Phone 2</label>
                                            <input class="form-control" name="Fld_Contact_Phone2" value="<?php echo $data['Fld_Contact_Phone2']?>">
                                        </div>
									
									<div class="form-group">
                                            <label>Contact Fax</label>
                                            <input class="form-control" name="Fld_Contact_Fax" value="<?php echo $data['Fld_Contact_Fax']?>">
                                        </div>
									
									<div class="form-group">
                                            <label>Company Mobile</label>
                                            <input class="form-control" name="Fld_Company_Mobile" value="<?php echo $data['Fld_Company_Mobile']?>">
                                    </div>
									
								</div>
								<div class="col-lg-5">

										<div class="form-group">
                                            <label>Contact Division</label>
                                            <select class="form-control" name="Fld_Contact_Division_ID">
											<?php

											
											//recuperation du nom de la division	
											 //*******tbl_Division*********Fld_Division_ID    Fld_Division_Text
											$sqldiv="SELECT * FROM tbl_Division";
											
											//echo $sqldiv;
											$reqemp = mysql2_query($sqldiv);
											while($datadiv = mysqli_fetch_array($reqemp))
											{
												echo "<option value='".$datadiv ['Fld_Division_ID']."'";
												if ($data['Fld_Contact_Division_ID']==$datadiv['Fld_Division_ID']) echo " selected";
												echo ">".$datadiv ['Fld_Division_Text']."</option>";
											}
					                        //Fin recuperation des type de compagnie
											?>
                                                
                                            </select>
                                        </div>
									
									<div class="form-group">
                                            <label>Contact Email</label>
                                            <input class="form-control" name="Fld_Contact_Email" value="<?php echo $data['Fld_Contact_Email']?>">
                                        </div>
									<div class="form-group">
                                            <label>Contact Title</label>
                                            <input class="form-control" name="Fld_Contact_Title" value="<?php echo $data['Fld_Contact_Title']?>">
                                        </div>
										<div class="form-group">
                                            <label>Contact Remark</label>
                                            
											<textarea class="form-control" rows="3" name="Fld_Contact_Remark"><?php echo $data['Fld_Contact_Remark']?></textarea>
                                        </div>
										
									
                                </div>
										<input type="hidden" name="id_company_contact" value="<?php echo $data['id_company_contact']?>">
                                <!-- /.col-lg-5 (nested) -->
                            </div>
							
									<button type="submit" class="btn btn-default">Submit Button</button>
                                    <button type="reset" class="btn btn-default">Reset Button</button>
                            </div>

									<br>
									
									</div>

                        <!-- /.panel-body -->
									
						</form>
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
    <script>
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