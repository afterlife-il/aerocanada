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
    <meta name="viewport" content="widtd=device-widtd, initial-scale=1">
    <meta name="description" content="">
    <meta name="autdor" content="">

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
    <!-- WARNING: Respond.js doesn't work if you view tde page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
	
	<!---->
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
	<!---->
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
		<!-- Page Content -->
        <div id="page-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <h1 class="page-header">VALIDATION NEWSLETTER</h1>
					</div>
				</div>	
				
				<div class="row">
					<div class="col-lg-12">
					    <div class="panel panel-default">
                             <div class="panel-heading">
                                
                             </div>
								
								<div class="panel-body">
									<div class="row">
										<div class="col-lg-6">

						<form method="post" enctype="multipart/form-data" name="Form1">

						<?php								
										
/***********************************************************************************/
/***********************************************************************************/
/*Generation de l'email pour client*/
// $mail = "lamalol@gmail.com"; // Déclaration de l'adresse de destination.
$mail = $datar['Fld_Contact_Email']; // Déclaration de l'adresse de destination.
if (!preg_match("#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#", $mail)) // On filtre les serveurs qui rencontrent des bogues.
{
	$passage_ligne = "\r\n";
}
else
{
	$passage_ligne = "\n";
}
//=====Déclaration des messages au format texte et au format HTML.
$message_html = '<html><head></head><body>
<div style="font-family:sans-serif;color:#000000;font-size:16px;margin:0px;padding:20px;" align="center">
<table border="0" cellspacing="0" cellpadding="0" align="center">
        <tbody><tr><td nowrap="" style="background:;border:1px solid #2a2a2a;color:#ffffff;padding:24px" width="100px">
		<table width="100%"><tbody><tr><td align="center" nowrap=""><img src="http://aerocanada-industries.com/pages/images/logo-aerocanada.png" alt="Aerocanada" ></td>
				</tr></tbody></table></td></tr>
        <tr><td align="center" style="background:#ffffff;color:#000000;border-top:10px solid #BD0831;border-bottom:10px solid #000000" height="200px">
		
		<table width="100%" cellspacing="0" style="background:white;color:black;border-collapse:collapse">
<tbody><tr>
<td nowrap="" align="center" style="border:1px solid #acacac;padding:3px;font-family:sans-serif;font-weight:bold;font-size:16px;padding-left:8px;padding-right:8px">Part Number</td>
<td nowrap="" align="center" style="border:1px solid #acacac;padding:3px;font-family:sans-serif;font-weight:bold;font-size:16px;padding-left:8px;padding-right:8px">Description</td>
<td nowrap="" align="center" style="border:1px solid #acacac;padding:3px;font-family:sans-serif;font-weight:bold;font-size:16px;padding-left:8px;padding-right:8px">Qty</td>
<td nowrap="" align="center" style="border:1px solid #acacac;padding:3px;font-family:sans-serif;font-weight:bold;font-size:16px;padding-left:8px;padding-right:8px">Cond</td>
</tr>';

for ($i=1; $i<=$_SESSION['countpnsessionnews']; $i++) {

//recuperation info PN	
$sql="SELECT * from tbl_Parts where Fld_Part_ID='".$_SESSION['pnusedsessionnews'.$i]."'";
$req = mysql2_query($sql);
$data = mysqli_fetch_array($req);
//Fin recuperation info PN	

//recuperation CONDITION	
// ** tbl_Condition ** Fld_Condition_ID  Fld_Condition_Text
$sql2="SELECT * from tbl_Condition where Fld_Condition_ID='".$_SESSION['pncondsessionnews'.$i]."'";
$req2 = mysql2_query($sql2);
$data2 = mysqli_fetch_array($req2);
//Fin recuperation CONDITION	

$message_html .='<tr>
<td nowrap="" align="center" style="border:1px solid #acacac;padding:3px;font-family:sans-serif;font-size:15px;padding-left:8px;padding-right:8px;font-weight:bold;padding-top:12px;padding-bottom:12px">'.$data['Fld_Part_Nbr'].'</td>
<td nowrap="" align="center" style="border:1px solid #acacac;padding:3px;font-family:sans-serif;font-size:15px;padding-left:8px;padding-right:8px">'.$data['Fld_Part_Desc'].'</td>
<td nowrap="" align="center" style="border:1px solid #acacac;padding:3px;font-family:sans-serif;font-size:15px;padding-left:8px;padding-right:8px">'.$_SESSION['pnqtysessionnews'.$i].'</td>
<td nowrap="" align="center" style="border:1px solid #acacac;padding:3px;font-family:sans-serif;font-size:15px;padding-left:8px;padding-right:8px">'.$data2['Fld_Condition_Text'].'</td>
</tr>';
}



											//recuperation employee ACI 770 
											// ** tbl_Employee ** 	Employee_ID Employee_Name  Fld_Contact_Id  pw  email  statut  position  tel  mobile  skype
					                        $sqleaci="SELECT * FROM tbl_Employee where Employee_ID='".$_SESSION['id_utilisateur']."'";
											
											$reqeaci = mysql2_query($sqleaci);
											$dataeaci = mysqli_fetch_array($reqeaci);
											//Fin recuperation employee ACI 770
											
$message_html .='</tbody></table></td></tr>
        <tr><td style="background:#BD0831;padding:0px" height="40">&nbsp;</td></tr>
        
        <tr><td align="center"><br>
                <table border="0">
                  <tbody><tr> 
                    <td><div align="right" style="font-size:12.5px;color:#5a5a5a;font-family:Arial,sans-serif;margin:7px"><div align="right" style="color:black;font-size:14px;font-weight:bold">'.$dataeaci['Employee_Name'].'</div>
                <div style="margin-top:4px"><span style="font-family:Arial,sans-serif;font-size:9pt">'.$dataeaci['position'].' | AeroCanada Industries 770 Inc.</span><br>Direct: '.$dataeaci['tel'].' | mob.&nbsp;'.$dataeaci['mobile'].'</div>
                <a href="http://www.aerocanada.aero" style="color:#5a5a5a" target="_blank" data-saferedirecturl="">http://www.aerocanada.aero</a><br>
                <a href="mailto:'.$dataeaci['email'].' style="color:#5a5a5a" target="_blank">'.$dataeaci['email'].'</a>
                </div></td>
                    <td width="1" style="border-right:1px solid black">&nbsp;</td>
                    <td align="left"><div style="margin:7px;width:340px;height:68px;text-align:center;" width="340" height="68"><img src="http://aerocanada-industries.com/pages/images/logo-aerocanada.png" height="68"></div></td>
                  </tr>
                </tbody></table>

            </td></tr>
        </tbody></table>
</div>
</body></html>';


//==========
 
//=====Définition du sujet.
$sujet = "NEWSLETTER / AEROCANADA ";
//=========
 


//==========

/*Fin Generation de l'email pour client*/

//affichage information pour validation 
echo '<b>SENDING GROUP</b> : 
<select class="form-control" name="id_groupe_newsletter" id="id_groupe_newsletter">';
				
									//** tbl_groupe_newsletter ** id_groupe_newsletter group_name
									$sqldiv="SELECT * FROM tbl_groupe_newsletter";
									
									$reqemp = mysql2_query($sqldiv);
									while($datadiv = mysqli_fetch_array($reqemp))
									{
										echo "<option value='".$datadiv["id_groupe_newsletter"]."'";
										if ($datadiv["id_groupe_newsletter"]==$_POST["id_groupe_newsletter"]) echo " selected";
										echo ">".$datadiv["group_name"]."</option>";
									}
				
                echo '</select><br>';

echo '<div class="form-group"><label>SUBJECT</label><input class="form-control" name="sujet" value="'.$sujet.'"></div>';
echo $message_html;
?>	


<!--<textarea name="message_html" style="height: 450px;" rows="10" height="450"><?php //echo $message_html;?></textarea>-->
		<script>
			//CKEDITOR.replace( 'message_html' );
		</script>	

						<div class="row">
								
								<div class="col-lg-4">
										<div class="form-group" align="right">
										<INPUT type="button" value="SEND NEWSLETTER" name=button2 onclick="return OnButton2();">
										</div>
								</div>									
						</div>	
						</form>
										</div>
									</div>
								</div>
						</div>
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

    <!-- jQuery --
    <script src="../vendor/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <!--<script src="../vendor/bootstrap/js/bootstrap.min.js"></script>-->

    <!-- Metis Menu Plugin JavaScript -->
    <script src="../vendor/metisMenu/metisMenu.min.js"></script>

    <!-- DataTables JavaScript -->
    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
    <script src="../vendor/datatables-responsive/dataTables.responsive.js"></script>

    <!-- Custom tdeme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!-- Page-Level Demo Scripts - Tables - Use for reference -->
    <script type="text/javascript">
	<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!-- ****************** DIFFERENTS ACTION SUR BOUTON SUBMIT************************-->
<!--*******************************************************************************-->
<!--*******************************************************************************-->
function OnButton1()
{
    document.Form1.action = ""
    document.Form1.target = "_self";    // Open in a new window
    document.Form1.submit();             // Submit the page
    return true;
}

function OnButton2()
{
    document.Form1.action = "validation-send-email-newsletter.php"
    document.Form1.target = "_self";    // Open in a new window
    document.Form1.submit();             // Submit the page
    return true;
}

-->
<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!-- ****************** FIN DIFFERENTS ACTION SUR BOUTON SUBMIT********************-->
<!--*******************************************************************************-->
<!--*******************************************************************************-->
    </script>



</body>

</html>

<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php\">";
?>