<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
if($_SESSION['conectroy']=="parfait"){
require('../classes/users.class.php');
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
                            USER PROFILE
                        </div>
						<?php
						//tbl_Employee---- Employee_ID Employee_Name Fld_Contact_Id pw email statut  position  tel  mobile  skype numformat  pwgmaero
									$objet=new users();
									$donnee = $objet->display_employee($_SESSION['id_utilisateur']);
									foreach($donnee as $dataemp)
									{
						?>
						<form id="formajoutcompany" role="form" method="post" action="validation_modif_users.php" >
						<input type="hidden" name="Employee_ID" value='<?php echo $_SESSION['id_utilisateur'];?>'>
						<input type="hidden" name="statut" value='<?php echo $dataemp['statut'];?>'>
						<input type="hidden" name="act" value='modifinfoperso'>
                        <div class="panel-body">

                            <div class="row">
                                <div class="col-lg-5">
                                        <div class="form-group">
                                            <label>EMPLYEE NAME</label>
                                            <input class="form-control" name="Employee_Name" value="<?php echo $dataemp['Employee_Name'];?>">
                                        </div>
										<div class="form-group">
                                            <label>E-MAIL</label>
                                            <input class="form-control" name="email" value="<?php echo $dataemp['email'];?>">
                                        </div>
										<div class="form-group">
                                            <label>PASSEWORD GMAIL</label>
                                            <input class="form-control" name="pwgmaero" value="<?php echo $dataemp['pwgmaero'];?>">
                                        </div>
										
										<div class="form-group">
                                            <label>TEL</label>
                                            <input class="form-control" name="tel" value="<?php echo $dataemp['tel'];?>">
                                        </div>
										
										<div class="form-group">
                                            <label>MOBILE</label>
                                            <input class="form-control" name="mobile" value="<?php echo $dataemp['mobile'];?>">
                                        </div>
										
										<div class="form-group">
                                            <label>SKYPE</label>
                                            <input class="form-control" name="skype" value="<?php echo $dataemp['skype'];?>">
                                        </div>
										
										<div class="form-group">
                                            <label>PASSWORD ADMIN</label>
                                            <input class="form-control" name="pw" value="<?php echo $dataemp['pw'];?>">
                                        </div>
										
										<div class="form-group">
                                            <label>TITLE</label>
                                            <input class="form-control" name="position" value="<?php echo $dataemp['position'];?>">
                                        </div>
										
								</div>	
								<div class="col-lg-2">
								</div>	
								<div class="col-lg-5">
										<div class="form-group">
                                            <label>YOUR SCREEN RESOLUTION</label>
											<br>
                                            <!--recuperation resolution ecran-->
<script>document.cookie = "largeur=" + window.innerWidth + "; expires=0"</script>
<script>document.cookie = "hauteur=" + window.innerHeight + "; expires=0"</script>

<?php
    if (isset ($_COOKIE ['largeur']))
    {
        echo "    Largeur = ".$_COOKIE ['largeur']." pixels<br/>\n";
        if (isset ($_COOKIE ['hauteur']))
            echo "    Hauteur = ".$_COOKIE ['hauteur']." pixels<br/>\n";
        else
            echo "    Hauteur non disponible, réafficher la page<br/>\n";
    }
    else
    {
        if (isset ($_COOKIE ['hauteur']))
        {
            echo "    Hauteur = ".$_COOKIE ['hauteur']." pixels<br/>\n";
            echo "    Largeur non disponible, réafficher la page<br/>\n";
        }
        else
        {
            echo "    Largeur et hauteur non disponibles, réafficher la page<br/><br/>\n";
            echo "    Si ça ne marche toujours pas vérifiez les points suivants :\n";
            echo "    <ul>\n";
            echo "        <li>votre navigateur web doit accepter les cookies</li>\n";
            echo "        <li>votre navigateur web doit utiliser javascript</li>\n";
            echo "        <li>votre navigateur web doit être compatible</li>\n";
            echo "    </ul>\n";
        }
    }
?>
<!--Fin recuperation resolution ecran-->
                                        </div>
								</div>	
								
							</div>
								<?php }?>
                        <!-- /.panel-body -->
									<button type="submit" class="btn btn-default">Submit Button</button>
                                   
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
	
	<script type="text/javascript" src="../js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
	<script type="text/javascript" src="../js/locales/bootstrap-datetimepicker.fr.js" charset="UTF-8"></script>

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