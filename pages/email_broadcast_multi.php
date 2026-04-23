<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
if($_SESSION['conectroy']=="parfait"){

//*************************************************************************************
//*************************************************************************************
//Sisie des informations ou mise a jour dans la BDD

//validation des modification de la rfq
require('../classes/rfq.class.php');
$objet=new rfq();
$donnee = $objet->valid_modif_rfq();
//End validation des modification de la rfq

//Enregistrement de la quotation ou des modification de la quotation dans la BDD

if (0<$_POST['num_rows_rfq3']){
	$objet=new rfq();
	$donnee = $objet->modif_multi_quote();
}
else
{
$objet=new rfq();
$donnee = $objet->add_quote_RFQ3_multi();						
}
//Fin Enregistrement de la quotation ou des modification de la quotation dans la BDD
//************************************************************************************
//************************************************************************************
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
                        <h1 class="page-header">Validation Quote</h1>
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
						<form enctype="multipart/form-data" name="Form1" method="post">
						<?php $nbline=$_POST['nbline'];
						// echo $_POST['nbline'];
						?>
						<input type="hidden" name="nbline" value="<?php echo $_POST['nbline'];?>">
						<input type="hidden" name="Fld_RFQ_ID" value="<?php echo $_POST['Fld_RFQ_ID'];?>">
						<input type="hidden" name="Fld_Priority_ID" value="<?php echo $_POST['Fld_Priority_ID'];?>">
						<?php for($i=1;$i<=$nbline;$i++)
						{	
						?>
						<input type="hidden" name="Fld_Qty<?php echo $i;?>" value="<?php echo $_POST['Fld_Qty'.$i];?>">
						<input type="hidden" name="Fld_Price<?php echo $i;?>" value="<?php echo $_POST['Fld_Price'.$i];?>">
						<input type="hidden" name="Fld_Part_Nbr<?php echo $i;?>" value="<?php echo $_POST['Fld_Part_Nbr'.$i];?>">
						<input type="hidden" name="Fld_Part_Desc<?php echo $i;?>" value="<?php echo $_POST['Fld_Part_Desc'.$i];?>">
						<input type="hidden" name="Fld_Condition_ID<?php echo $i;?>" value="<?php echo $_POST['Fld_Condition_ID'.$i];?>">
						<input type="hidden" name="Fld_Release_ID<?php echo $i;?>" value="<?php echo $_POST['Fld_Release_ID'.$i];?>">
						<input type="hidden" name="lead_time<?php echo $i;?>" value="<?php echo $_POST['lead_time'.$i];?>">
						<input type="hidden" name="Fld_Remark<?php echo $i;?>" value="<?php echo $_POST['Fld_Remark'.$i];?>">
						<input type="hidden" name="Fld_Tag_Info_ID<?php echo $i;?>" value="<?php echo $_POST['Fld_Tag_Info_ID'.$i];?>">
						<input type="hidden" name="Fld_Tag_Date<?php echo $i;?>" value="<?php echo $_POST['Fld_Tag_Date'.$i];?>">
						<input type="hidden" name="Fld_Part_ID<?php echo $i;?>" value="<?php echo $_POST['Fld_Part_ID'.$i];?>">
						<input type="hidden" name="Fld_Part_SN<?php echo $i;?>" value="<?php echo $_POST['Fld_Part_SN'.$i];?>">
						<input type="hidden" name="FldCurrencyID<?php echo $i;?>" value="<?php echo $_POST['FldCurrencyID'.$i];?>">
						<input type="hidden" name="moq<?php echo $i;?>" value="<?php echo $_POST['moq'.$i];?>">
						<input type="hidden" name="Fld_Traceability_ID<?php echo $i;?>" value="<?php echo $_POST['Fld_Traceability_ID'.$i];?>">
						<input type="hidden" name="rfqvalid<?php echo $i;?>" value="<?php echo $_POST['rfqvalid'.$i];?>">
						
<?php
// $Fld_Qty_recup.$i=$_POST["Fld_Qty".$i];
// $Fld_Price_recup.$i=$_POST["Fld_Price".$i];
						}
//recuperation info RFQ 
//	ID Fld_RFQ_ID  Fld_Qty  Fld_Part_ID  Fld_Observation  Fld_Customer_ID  date  Fld_RFQ_Type_ID  Fld_Priority_ID  Employee_ID  id_company_contact  Fld_Payment_Term_ID  Fld_Condition_ID  pn_rfq  description_rfq
$sqlrfq="SELECT * from tbl_RFQ_1 where Fld_RFQ_ID='".$_POST['Fld_RFQ_ID']."'";
// echo $sqlrfq;

$reqrfq = mysql2_query($sqlrfq);
$datarfq = mysqli_fetch_array($reqrfq);
$daterfq=$datarfq['date'];
$contactaci=$datarfq['Employee_ID'];
// ** tb_company_contact ** id_company_contact Fld_Linked_ID  Fld_Company_ID  Company_Old_Id  Fld_Contact_Name  Fld_Contact_Phone  Fld_Contact_Phone2  Fld_Contact_Fax  Fld_Company_Mobile  Fld_Contact_Division_ID  Fld_Contact_Email  Fld_Contact_Title  Fld_Contact_Remark  status  aci_contact entry_date
$sqlr="SELECT * from tb_company_contact where id_company_contact=".$_POST["id_company_contact"];
// echo $sqlr;

$reqr = mysql2_query($sqlr);
$datar = mysqli_fetch_array($reqr);

$Fld_Contact_Name=$datar['Fld_Contact_Name'];
//Liste des champs reucperer de la page precedente (quote)
// RFQ_ID  RFQ_DATE  Fld_RFQ_Type_ID   Fld_Part_Nbr  Fld_Part_Desc  Fld_Qty  Fld_Condition_ID  Fld_Part_SN  Fld_Min_Qty  Fld_Remark  Fld_Release_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Traceability_ID  lead_time  Fld_Price  FldCurrencyID
															
										
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
$message_txt = "Salut à tous, voici un e-mail envoyé par un script PHP.";
// $message_html = "<html><head></head><body><b>Salut à tous</b>, voici un e-mail envoyé par un <i>script PHP</i>.</body></html>";
											
//#e9e9e9
$message_html = '<html><head></head><body>
<div style="font-family:sans-serif;color:#000000;font-size:16px;margin:0px;padding:20px;">
<table id="" style="border-collapse:collapse;border:1px solid #BE0831;background:#FFFFFF;color:#000000;width:800px;"><tbody><tr style="height:100px"><td id="" style="padding:25px;border:1px solid #BE0831"><img src="http://aerocanada-industries.com/img/logo-aei-email.png" width="83" height="96"></td></tr><tr><td id="" valign="top" style="padding:25px;border:1px solid #BE0831;line-height:24px">';

											//reuperation priority
											// ** tbl_Priority ** Fld_Priority_ID  Fld_Priority_Text
					                        $sqlPriority="SELECT Fld_Priority_Text FROM tbl_Priority where Fld_Priority_ID=".$_POST['Fld_Priority_ID'];
											
											$reqPriority = mysql2_query($sqlPriority);
											$dataPriority = mysqli_fetch_array($reqPriority);
											//Fin reuperation priority
											
$message_html2= 'Dear '.$Fld_Contact_Name.',<br><br>
First of all, thank you very much for your RFQ recorded as <b>'.$dataPriority['Fld_Priority_Text'].'</b> Priority &nbsp;&nbsp;- &nbsp;&nbsp; <b>RFQ #</b> '.$_POST['Fld_RFQ_ID'].'<br>
Per your request, we can propose you as follow:
<br>
<div style="border: 1px solid black;">
<table width="800" border="0" cellpadding="1" cellspacing="1"><tbody>
					 	
<tr><td></td><td style="text-align:center;font-weight: bold;"><b>P/N</b></td><td style="text-align:center;font-weight: bold;"><b>Description</b></td><td style="text-align:center;font-weight: bold;"><b>Qty (EA)</b></td><td style="text-align:center;font-weight: bold;"><b>Condition</b></td><td style="text-align:center;font-weight: bold;"><b>Delivery</b></td><td style="text-align:center;font-weight: bold;"><b>Min Qty</b></td><td style="text-align:center;font-weight: bold;"><b>Price (EA)</b></td><td style="text-align:center;font-weight: bold;"><b>Total</b></td></tr>';

// $colorvar=0;
$varligne=0;
for($i=1;$i<=$_POST["nbline"];$i++){
	
	if($_POST['rfqvalid'.$i]=='ok')
	{
		$varligne++;
											 // $colorvar++;
											 // if($colorvar=="2") 
											 // {
											 // $couleuraff="background-color:#E6E6E6;padding:5px;";
											 // $colorvar="0"; 
											 // } 
											 // else 
												 
											 $couleuraff="background-color:white;padding:5px;";
$message_html2.='<tr><td width="30" style="text-align:center;'.$couleuraff.'">&nbsp;'.$varligne.'&nbsp;</td><td style="text-align:left;'.$couleuraff.'">'.$_POST['pn_rfq'.$i].'</td><td style="text-align:left;'.$couleuraff.'">'.$_POST['description'.$i].'</td><td style="text-align:center;'.$couleuraff.'">'.$_POST['Fld_Qty'.$i].'</td>';

											//recuperation condition 
											// ** tbl_Condition ** Fld_Condition_ID  Fld_Condition_Text
					                        $sqlc="SELECT Fld_Condition_Text FROM tbl_Condition where Fld_Condition_ID=".$_POST['Fld_Condition_ID'.$i];
											
											$reqc = mysql2_query($sqlc);
											$datac = mysqli_fetch_array($reqc);
											$Fld_Condition_Text_aff=$datac["Fld_Condition_Text"];
											//Fin recuperation condition
											
											//currency recuperation du logo de la monnaie comme $
											//tbl_Currency---- Fld_Currency_ID Fld_Currency_Text htmlcode
					                        $sqlcid="SELECT htmlcode FROM tbl_Currency where Fld_Currency_ID='".$_POST['FldCurrencyID'.$i]."'";
											
											$reqcid = mysql2_query($sqlcid);
											$datacid = mysqli_fetch_array($reqcid);
											//End currency
											//formatage prix
											setlocale(LC_MONETARY, 'en_US.UTF-8');
											$nombre_format_francais = money_format('%.2n', $_POST['Fld_Price'.$i]);
											//Fin formatage prix
											
$message_html2.='<td style="text-align:center;'.$couleuraff.'">'.$Fld_Condition_Text_aff.'</td><td style="text-align:left;'.$couleuraff.'">'.$_POST['lead_time'.$i].'</td><td style="text-align:left;'.$couleuraff.'">'.$_POST['moq'.$i].'</td><td style="text-align:right;'.$couleuraff.'">'.$nombre_format_francais.'</td><td style="text-align:right;'.$couleuraff.'">';
$total_price=$_POST['Fld_Price'.$i]*$_POST['Fld_Qty'.$i];
$nombre_format_total_francais = money_format('%.2n', $total_price);
$message_html2.= $nombre_format_total_francais.'</td>';
$message_html2.= "</tr>";

//recuperation release
											// ** tbl_Release ** Fld_Release_ID  Fld_Release_Text
					                        $sqlrel="SELECT Fld_Release_Text FROM tbl_Release where Fld_Release_ID=".$_POST['Fld_Release_ID'.$i];
											
											$reqrel = mysql2_query($sqlrel);
											$datarel = mysqli_fetch_array($reqrel);
											//Fin recuperation release
							
											//reucperation du nom compagnie tag info
											$companyname = explode(",", $_POST['Fld_Tag_Info_ID'.$i]);
											$companyname=$companyname[0]; 
											//Fin reucperation du nom compagnie tag info

$message_html2.="<tr><td align='left' style='".$couleuraff."'></td><td colspan='7' align='left' style='".$couleuraff."'><b>Certification</b>&nbsp;&nbsp;".$datarel['Fld_Release_Text']." By ".$companyname." ".$_POST['Fld_Tag_Date'.$i]."</td></tr><tr><td align='left' style='".$couleuraff."'></td><td colspan='7' align='left' style='".$couleuraff."'><b>Comments :</b> ".$_POST['Fld_Remark'.$i]."</td></tr>";
$message_html2.="<tr><td colspan='8' align='center' style='background-color:white;'>&nbsp;</td></tr>";
	}
}
$message_html2.="</tbody></table></div>
<span style='font-size:11px;'>Price in USD / Prices & Availability are subject to change / AOG fees may apply.</span>
<br>
In case of any requirement, please remember to contact AeroCanada Industries 770 Inc.
<br>
It will always be a pleasure to support you!
<br><br>
Best Regards,<br>
";
											 

// $message_htmlOLD= $Fld_Condition_Text_aff.'</td></tr><tr><td nowrap="" align="left" style="font-weight: bold;"><b>Quantity</b></td><td nowrap="" align="left">&nbsp;&nbsp;'.$Fld_Qty_recup.'</td></tr>
// <tr><td nowrap="" align="left" style="font-weight: bold;"><b>Price in USD EA</b></td><td nowrap="" align="left">&nbsp;&nbsp;'.$Fld_Price_recup.'</td></tr>';

											//recuperation employee ACI 770 
											// ** tbl_Employee ** 	Employee_ID Employee_Name  Fld_Contact_Id  pw  email  statut  position  tel  mobile  skype
					                        $sqleaci="SELECT * FROM tbl_Employee where Employee_ID=".$contactaci;
											
											$reqeaci = mysql2_query($sqleaci);
											$dataeaci = mysqli_fetch_array($reqeaci);
											//Fin recuperation employee ACI 770

											
$message_html3= '<div dir="ltr" style="font-size:small"><br><table border="0" cellspacing="0" cellpadding="0" width="479" style="width:359.55pt;border-collapse:collapse;border:none"><tbody><tr><td width="108" valign="top" style="width:81pt;border-top:none;border-bottom:none;border-left:none;border-right:1pt solid windowtext;padding:0in 5.4pt"><p align="center" style="margin-bottom:0.0001pt;text-align:center"><img src="http://aerocanada-industries.com/img/logo-aei-email.png" width="83" height="96"></p></td><td width="371" valign="top" style="width:278.55pt;border:none;padding:0in 5.4pt"><p style="margin-bottom:0.0001pt;line-height: 15px;"><b style="font-size:12.8px"><span lang="EN-US" style="font-family:Arial,sans-serif">'.$dataeaci['Employee_Name'].'<br></span></b><span style="font-family:Arial,sans-serif;font-size:9pt">'.$dataeaci['position'].' | AeroCanada Industries 770 Inc.</span></p><p style="font-size:12.8px"><i><span style="font-family:Americana"><b><font size="2">Your Perfect Choice For Aviation&nbsp;Solutions</font></b></span></i></p><p style="margin-bottom:0.0001pt;line-height: 15px;"><span style="font-family:Arial,sans-serif;font-size:9pt">dir. '.$dataeaci['tel'].' | mob.&nbsp;'.$dataeaci['mobile'].'<br></span><span style="font-family:Arial,sans-serif;font-size:9pt">tel. +1 514 80 06 223 | fax. +1 514 80 06 224<br></span><a href="mailto:roy@aerocanada.aero" style="color:rgb(17,85,204);font-family:Arial,sans-serif;font-size:9pt" target="_blank">'.$dataeaci['email'].'</a><span style="font-family:Arial,sans-serif;font-size:9pt">&nbsp;|&nbsp;</span><a href="http://www.aerocanada.aero/" style="color:rgb(17,85,204);font-family:Arial,sans-serif;font-size:9pt" target="_blank">http://www.aerocanada.aero</a><br><span style="font-family:Arial,sans-serif;font-size:9pt"><b>Skype:</b>&nbsp;'.$dataeaci['skype'].'</span></p><p style="margin-bottom:0.0001pt;line-height: 15px;"><b><u style="background-color:rgb(204,0,0)"><font color="#f3f3f3">OUR ADDRESS CHANGED:<br></font></u></b><span style="font-size:12.8px">99, Prince Street, 7th Floor, Suite#701<br></span><span style="font-size:12.8px">Montreal QC H3C 2M7, Canada</span></p><p style="font-size:12.8px"><img src="http://www.aerocanada.org/images/asa-36.png" style="font-size:12.8px"><span style="font-size:12.8px">&nbsp;|&nbsp;</span><img src="http://www.aerocanada.org/images/tac-36.png" style="font-size:12.8px"><span style="font-size:12.8px">&nbsp;|&nbsp;</span><img src="http://www.aerocanada.org/images/logo-nato-36.png" style="font-size:12.8px"><span style="font-size:12.8px">&nbsp;|&nbsp;</span><img src="http://www.aerocanada.org/images/logo-ungm-36.png" style="font-size:12.8px"><span style="font-size:12.8px">&nbsp;|&nbsp;</span><a href="https://www.facebook.com/AeroCanada-Industries-770-Inc-967017943346764/" style="color:rgb(17,85,204);font-size:12.8px" target="_blank"><img src="http://www.aerocanada.org/images/f_icon-36.png" style="font-size:12.8px"></a><span style="font-size:12.8px">&nbsp;|&nbsp;</span><a href="https://www.linkedin.com/company/3155360?trk=tyah&amp;trkInfo=clickedVertical%3Acompany%2CclickedEntityId%3A3155360%2Cidx%3A2-1-2%2CtarId%3A1467015396545%2Ctas%3Aaerocana" style="color:rgb(17,85,204);font-size:12.8px" target="_blank"><img src="http://www.aerocanada.org/images/linkedin-36.png" style="font-size:12.8px"></a><span style="font-size:12.8px"></span><span style="font-size:12.8px"></span><a style="color:rgb(17,85,204);font-size:12.8px"></a><span style="font-size:12.8px">&nbsp;</span><br></p><p style="margin-bottom:0.0001pt;line-height: 15px;"><font size="1">FAA AC00-56A |&nbsp;UNGM 256670 |&nbsp;NATO CAGE L06T4<br></font><a href="http://www.aerocanada.org/index.php/fr/aero-canada-accueil/conditions-generales-de-vente" style="color:rgb(17,85,204);font-size:x-small" target="_blank">Conditions Generales Vente</a><span style="font-size:x-small;color:rgb(0,0,0)">&nbsp;</span><span style="font-size:x-small;color:rgb(0,0,0)">/</span><span style="font-size:x-small;color:rgb(0,0,0)">&nbsp;</span><a href="http://www.aerocanada.org/index.php/en/aero-canada/terms-of-sale" style="color:rgb(17,85,204);font-size:x-small" target="_blank">Terms of Sale</a><br>Phone: <a href="tel:+15148006223" value="+15148006223" target="_blank">+1 514 800 6223</a> <br>E-mail <a href="sales@aerocanada.org" value="sales@aerocanada.org" target="_blank">sales@aerocanada.org</a></p><div><br></div></td></tr></tbody></table></div>


</td></tr><tr><td id="" align="center" style="padding:25px;font-size:12px;border:1px solid #BE0831;background-color:#BE0831;color:#FFFFFF;"><a href="http://www.aerocanada.aero" target="_blank" style="color:#FFFFFF;font-size:20px">www.aerocanada.aero</a></td></tr></tbody></table></div>
</body></html>';
//==========
 
//=====Création de la boundary
$boundary = "-----=".md5(rand());
//==========
 
//=====Définition du sujet.
$sujet = "ACI770 QUOTE - Multi P/N - ".$_POST['Fld_RFQ_ID'];
//=========
 
//=====Création du header de l'e-mail.
$header = "From: \"".$dataeaci['Employee_Name']."\"<".$dataeaci['email'].">".$passage_ligne;
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
$message.= $passage_ligne.$message_html.$passage_ligne;
//==========
$message.= $passage_ligne."--".$boundary."--".$passage_ligne;
$message.= $passage_ligne."--".$boundary."--".$passage_ligne;
//==========
 
//=====Envoi de l'e-mail.
// mail($mail,$sujet,$message,$header);
// mail("lamalol@gmail.com",$sujet,$message,$header);
//==========

/*Fin Generation de l'email pour client*/

//affichage information pour validation 
echo '<b>E-mail</b> : <input class="form-control" type="text" name="clientemail" value="'.$mail.'"><br><br>';
echo '<b>E-mail CC</b> : <input class="form-control" name="emailcc"><br>';

echo '<div class="form-group"><label>Subject</label><input class="form-control" name="sujet" value="'.$sujet.'"></div>';
echo $message_html;


//affichage de tout les resultats envoyoer par un formulaire en POST
// foreach($_POST as $key => $val) echo '$_POST["'.$key.'"]='.$val.'<br />';
?>	
<input type="hidden" name="Fld_Contact_Name" value="<?php echo $Fld_Contact_Name;?>">


<textarea name="message_html2" style="height: 450px;" rows="10" height="450"><?php echo $message_html2;?></textarea>
		<script>
			CKEDITOR.replace( 'message_html2' );
		</script>	
	<?php echo $message_html3;

	?>	
</div>
									</div>
<div class="row">
								<div class="col-lg-4">
										<!--<div class="form-group" align="right">
										<INPUT type="button" value="Save only" name=button1 onclick="return OnButton1();">
										</div>
										-->
								</div>	
								<div class="col-lg-4">
										<div class="form-group" align="right">
										<INPUT type="button" value="Send email" name=button2 onclick="return OnButton2();">
										</div>
								</div>									
								

						   </div>

						</form>
										
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
    document.Form1.action = "validation-quotation-without-email-multi.php"
    document.Form1.target = "_self";    // Open in a new window
    document.Form1.submit();             // Submit the page
    return true;
}

function OnButton2()
{
    document.Form1.action = "validation-send-email-quotation-multi.php"
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