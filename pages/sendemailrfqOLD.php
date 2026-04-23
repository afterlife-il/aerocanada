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

		<!-- Page Content -->
        <div id="page-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <h1 class="page-header">Validation Quote</h1>
					</div>
				</div>	
				
				<div class="row">
					<div class="col-lg-12">
					    <div class="panel panel-default">
                             <div class="panel-heading">
                                 Basic Form Elements
                             </div>
								
								<div class="panel-body">
									<div class="row">
										<div class="col-lg-6">

						<form method="post" enctype="multipart/form-data" name="Form1">

						<input type="hidden" name="Fld_Qty_RFQ" value="<?php echo $_POST['Fld_Qty_RFQ'];?>">
						<input type="hidden" name="Fld_Part_Nbr" value="<?php echo $_POST['Fld_Part_Nbr'];?>">
						<input type="hidden" name="Fld_Part_Desc" value="<?php echo $_POST['Fld_Part_Desc'];?>">
						<input type="hidden" name="Fld_Condition_ID" value="<?php echo $_POST['Fld_Condition_ID'];?>">
						<input type="hidden" name="Fld_Part_ID" value="<?php echo $_POST['Fld_Part_ID'];?>">
						<input type="hidden" name="Fld_Priority_ID" value="<?php echo $_POST['Fld_Priority_ID'];?>">
						<input type="hidden" name="emailrfq" value="<?php echo $_POST['emailrfq'];?>">
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
<div style="font-family:sans-serif;color:#000000;font-size:16px;margin:0px;padding:20px;">
<table id="" style="border-collapse:collapse;border:1px solid #BE0831;background:#e9e9e9;color:#000000;"><tbody><tr style="height:100px"><td id="" style="padding:25px;border:1px solid #BE0831"><img src="http://aerocanada-industries.com/img/logo-aei-email.png" width="83" height="96"></td></tr><tr><td id="" valign="top" style="padding:25px;border:1px solid #BE0831;line-height:24px">';

$message_html_fast=$message_html;											
											
$message_html2= 'Hey,<br><br>
Can you please advise availability, Tag Info and price regarding the following PN: 
<br><br>
<table border="0" cellpadding="1" cellspacing="1"><tbody>
<tr><td align="left" style="font-weight: bold;"><b>Part Number</b></td><td nowrap="" align="left">&nbsp;&nbsp;'.$_POST['Fld_Part_Nbr'].'</td></tr>
<tr><td nowrap="" align="left" style="font-weight: bold;"><b>Description</b></td><td nowrap="" align="left">&nbsp;&nbsp;'.$_POST['Fld_Part_Desc'].'</td></tr>';


											//recuperation condition 
											if(!empty($_POST['Fld_Condition_ID']))
											{
											// ** tbl_Condition ** Fld_Condition_ID  Fld_Condition_Text
					                        $sqlc="SELECT Fld_Condition_Text FROM tbl_Condition where Fld_Condition_ID='".$_POST['Fld_Condition_ID']."'";
											
											$reqc = mysql2_query($sqlc);
											$datac = mysqli_fetch_array($reqc);
											$Fld_Condition_Text_aff=$datac["Fld_Condition_Text"];
											$message_html2.= '<tr><td nowrap="" align="left" style="font-weight: bold;"><b>Condition</b></td><td nowrap="" align="left">&nbsp;&nbsp;'.$Fld_Condition_Text_aff.'</td></tr>';
											}
											//Fin recuperation condition
																						
											//reuperation priority
											// ** tbl_Priority ** Fld_Priority_ID  Fld_Priority_Text
					                        $sqlPriority="SELECT Fld_Priority_Text FROM tbl_Priority where Fld_Priority_ID=".$_POST['Fld_Priority_ID'];
											
											$reqPriority = mysql2_query($sqlPriority);
											$dataPriority = mysqli_fetch_array($reqPriority);
											//Fin reuperation priority
											


$message_html2.= '<tr><td nowrap="" align="left" style="font-weight: bold;"><b>Quantity</b></td><td nowrap="" align="left">&nbsp;&nbsp;'.$_POST['Fld_Qty_RFQ'].'</td></tr>
<tr><td nowrap="" align="left" style="font-weight: bold;"><b>Priority</b></td><td nowrap="" align="left">&nbsp;&nbsp;'.$dataPriority['Fld_Priority_Text'].'</td></tr>';
if(!empty($_POST['commentsrfq']))
											{
$message_html2.= '<tr><td nowrap="" align="left" style="font-weight: bold;"><b>Comments</b></td><td nowrap="" align="left">&nbsp;&nbsp;'.$_POST['commentsrfq'].'</td></tr>';
											}
$message_html2.= '</tbody></table>
Many Thanks,<br>';
											
$message_html_fast.=$message_html2;									
											//recuperation employee ACI 770 
											// ** tbl_Employee ** 	Employee_ID Employee_Name  Fld_Contact_Id  pw  email  statut  position  tel  mobile  skype
					                        $sqleaci="SELECT * FROM tbl_Employee where Employee_ID='".$_SESSION['id_utilisateur']."'";
											
											$reqeaci = mysql2_query($sqleaci);
											$dataeaci = mysqli_fetch_array($reqeaci);
											//Fin recuperation employee ACI 770

											
$message_html3= '<div dir="ltr" style="font-size:small"><br><table border="0" cellspacing="0" cellpadding="0" width="479" style="width:359.55pt;border-collapse:collapse;border:none"><tbody><tr><td width="108" valign="top" style="width:81pt;border-top:none;border-bottom:none;border-left:none;border-right:1pt solid windowtext;padding:0in 5.4pt"><p align="center" style="margin-bottom:0.0001pt;text-align:center"><img src="http://aerocanada-industries.com/img/logo-aei-email.png" width="83" height="96"></p></td><td width="371" valign="top" style="width:278.55pt;border:none;padding:0in 5.4pt"><p style="margin-bottom:0.0001pt;line-height: 15px;"><b style="font-size:12.8px"><span lang="EN-US" style="font-family:Arial,sans-serif">'.$dataeaci['Employee_Name'].'<br></span></b><span style="font-family:Arial,sans-serif;font-size:9pt">'.$dataeaci['position'].' | AeroCanada Industries 770 Inc.</span></p><p style="font-size:12.8px"><i><span style="font-family:Americana"><b><font size="2">Your Perfect Choice For Aviation&nbsp;Solutions</font></b></span></i></p><p style="margin-bottom:0.0001pt;line-height: 15px;"><span style="font-family:Arial,sans-serif;font-size:9pt">dir. '.$dataeaci['tel'].' | mob.&nbsp;'.$dataeaci['mobile'].'<br></span><span style="font-family:Arial,sans-serif;font-size:9pt">tel. +1 514 80 06 223 | fax. +1 514 80 06 224<br></span><a href="mailto:roy@aerocanada.aero" style="color:rgb(17,85,204);font-family:Arial,sans-serif;font-size:9pt" target="_blank">'.$dataeaci['email'].'</a><span style="font-family:Arial,sans-serif;font-size:9pt">&nbsp;|&nbsp;</span><a href="http://www.aerocanada.aero/" style="color:rgb(17,85,204);font-family:Arial,sans-serif;font-size:9pt" target="_blank">http://www.aerocanada.aero</a><br><span style="font-family:Arial,sans-serif;font-size:9pt"><b>Skype:</b>&nbsp;'.$dataeaci['skype'].'</span></p><p style="margin-bottom:0.0001pt;line-height: 15px;"><b><u style="background-color:rgb(204,0,0)"><font color="#f3f3f3">OUR ADDRESS CHANGED:<br></font></u></b><span style="font-size:12.8px">99, Prince Street, 7th Floor, Suite#701<br></span><span style="font-size:12.8px">Montreal QC H3C 2M7, Canada</span></p><p style="font-size:12.8px"><img src="http://www.aerocanada.org/images/asa-36.png" style="font-size:12.8px"><span style="font-size:12.8px">&nbsp;|&nbsp;</span><img src="http://www.aerocanada.org/images/tac-36.png" style="font-size:12.8px"><span style="font-size:12.8px">&nbsp;|&nbsp;</span><img src="http://www.aerocanada.org/images/logo-nato-36.png" style="font-size:12.8px"><span style="font-size:12.8px">&nbsp;|&nbsp;</span><img src="http://www.aerocanada.org/images/logo-ungm-36.png" style="font-size:12.8px"><span style="font-size:12.8px">&nbsp;|&nbsp;</span><a href="https://www.facebook.com/AeroCanada-Industries-770-Inc-967017943346764/" style="color:rgb(17,85,204);font-size:12.8px" target="_blank"><img src="http://www.aerocanada.org/images/f_icon-36.png" style="font-size:12.8px"></a><span style="font-size:12.8px">&nbsp;|&nbsp;</span><a href="https://www.linkedin.com/company/3155360?trk=tyah&amp;trkInfo=clickedVertical%3Acompany%2CclickedEntityId%3A3155360%2Cidx%3A2-1-2%2CtarId%3A1467015396545%2Ctas%3Aaerocana" style="color:rgb(17,85,204);font-size:12.8px" target="_blank"><img src="http://www.aerocanada.org/images/linkedin-36.png" style="font-size:12.8px"></a><span style="font-size:12.8px"></span><span style="font-size:12.8px"></span><a style="color:rgb(17,85,204);font-size:12.8px"></a><span style="font-size:12.8px">&nbsp;</span><br></p><p style="margin-bottom:0.0001pt;line-height: 15px;"><font size="1">FAA AC00-56A |&nbsp;UNGM 256670 |&nbsp;NATO CAGE L06T4<br></font><a href="http://www.aerocanada.org/index.php/fr/aero-canada-accueil/conditions-generales-de-vente" style="color:rgb(17,85,204);font-size:x-small" target="_blank">Conditions Generales Vente</a><span style="font-size:x-small;color:rgb(0,0,0)">&nbsp;</span><span style="font-size:x-small;color:rgb(0,0,0)">/</span><span style="font-size:x-small;color:rgb(0,0,0)">&nbsp;</span><a href="http://www.aerocanada.org/index.php/en/aero-canada/terms-of-sale" style="color:rgb(17,85,204);font-size:x-small" target="_blank">Terms of Sale</a></p><div><br></div></td></tr></tbody></table></div>


</td></tr><tr><td id="" align="center" style="padding:25px;font-size:12px;border:1px solid #BE0831"><a href="http://www.aerocanada.aero" target="_blank">www.aerocanada.aero</a> • phone: <a href="tel:+15148006223" value="+15148006223" target="_blank">+1 514 800 6223</a> • email <a href="sales@aerocanada.org" value="sales@aerocanada.org" target="_blank">sales@aerocanada.org</a> </td></tr></tbody></table></div>
</body></html>';

$message_html_fast.=$message_html3;
//==========
 
//=====Création de la boundary
$boundary = "-----=".md5(rand());
//==========
 
//=====Définition du sujet.
$sujet = "QUOTE / AEROCANADA / PN# ".$_POST['Fld_Part_Nbr'];
//=========
 
//=====Création du header de l'e-mail.
$header = "From: \"".$dataeaci['Employee_Name']."\"<".$dataeaci['email'].">".$passage_ligne;
$header.= "Cc: ".$dataeaci['email']."".$passage_ligne;
$header.= "Reply-to: \"".$dataeaci['Employee_Name']."\" <".$dataeaci['email'].">".$passage_ligne;
$header.= "MIME-Version: 1.0".$passage_ligne;
$header.= "Content-Type: multipart/alternative;".$passage_ligne." boundary=\"$boundary\"".$passage_ligne;
//==========
 
//=====Création du message.
$message = $passage_ligne."--".$boundary.$passage_ligne;
//=====Ajout du message au format texte.
$message.= "Content-Type: text/plain; charset=\"ISO-8859-1\"".$passage_ligne;
$message.= "Content-Transfer-Encoding: 8bit".$passage_ligne;
$message.= $passage_ligne.$message_txt.$passage_ligne;
//==========
$message.= $passage_ligne."--".$boundary.$passage_ligne;
//=====Ajout du message au format HTML
$message.= "Content-Type: text/html; charset=\"ISO-8859-1\"".$passage_ligne;
$message.= "Content-Transfer-Encoding: 8bit".$passage_ligne;
$message.= $passage_ligne.$message_html_fast.$passage_ligne;
//==========
$message.= $passage_ligne."--".$boundary."--".$passage_ligne;
$message.= $passage_ligne."--".$boundary."--".$passage_ligne;
//==========
 
//=====Envoi de l'e-mail.
if($_POST['fast_send']=='ok'){
$tags = explode(',' , $_POST['emailrfq']);
$count =count($tags);
  // echo 'Count is: '.$count .'</br>';
foreach($tags as $key) {
	mail($key,$sujet,$message,$header);
}
echo "<meta http-equiv=\"refresh\" content=\"0; url=Part-Nbr.php?part_id=".$_POST['Fld_Part_ID']."\">";
EXIT;
}
//==========

/*Fin Generation de l'email pour client*/

//affichage information pour validation 
echo '<b>E-mail</b> : <input class="form-control" name="emailrfq" value="'.$_POST['emailrfq'].'"><br>';

echo '<div class="form-group"><label>Subject</label><input class="form-control" name="sujet" value="'.$sujet.'"></div>';
echo $message_html;
?>	
<input type="hidden" name="Fld_Contact_Name" value="<?php echo $Fld_Contact_Name;?>">
<input type="hidden" name="clientemail" value="<?php echo $mail;?>">
<textarea name="message_html2" style="height: 450px;" rows="10" height="450"><?php echo $message_html2;?></textarea>
		<script>
			CKEDITOR.replace( 'message_html2' );
		</script>	
	<?php echo $message_html3;?>
	

	
						<div class="row">
								
								<div class="col-lg-4">
										<div class="form-group" align="right">
										<INPUT type="button" value="SEND RFQ" name=button2 onclick="return OnButton2();">
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
    $(document).ready(function() {
        $('#dataTables-example').DataTable({
            responsive: true
        });
    });
	
	
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
    document.Form1.action = "validation-send-email-rfq.php"
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