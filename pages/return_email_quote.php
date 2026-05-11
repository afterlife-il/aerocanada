<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
include_once "email_signature_helper.php";
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
<link href="../dist/css/aci-overrides.css" rel="stylesheet"> <!-- <= impÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©ratif, et APRÃƒÆ’Ã†â€™Ãƒâ€¹Ã¢â‚¬Â S sb-admin-2.css -->

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
                        <h1 class="page-header">Step 2: Review and send email</h1>
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
		<?php 
		//****tbl_RFQ_3**** ID  Fld_RFQ_ID  Fld_Quote_Date  Fld_Part_Id  Fld_Part_SN  Fld_Qty  Fld_Condition  Fld_Price  Fld_Price_Min  Fld_Price_Max  Fld_Currency_ID  Fld_Remark  Fld_Supply_Date  Fld_Traceability_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Release_ID  Fld_Linked_ID  Fld_Exch_Core_Value  Fld_Exch_Core_Value_Currency_ID  Fld_Exch_Cond  Fld_IsBeen_Chosen  Fld_Send_Mail  Fld_Exch_Core_RCVD  moq  lead_time  Fld_Priority_ID
		  
					$sql="SELECT * from tbl_RFQ_3 where ID='".$_GET['ID']."'";
					//echo $sqlrfq2;
					$req = mysql2_query($sql);
					$data = mysqli_fetch_array($req);
					
											//reuperation du pn et de la description
											//Table tbl_Parts :::: Fld_Part_ID  Fld_Part_Nbr  Fld_Part_Desc  Fld_Part_MFG  Fld_Part_MFG_Old  Fld_AC_ID  Fld_Old_LP  Fld_Part_List_Price  Fld_Part_Price_Currency_ID  Fld_Part_LP_Date  Fld_Remark status
											//recuperation du PN ********************
											$sqlpn="SELECT Fld_Part_Nbr,Fld_Part_Desc FROM tbl_Parts WHERE Fld_Part_ID='".$data['Fld_Part_Id']."'";
											// echo $sqlpn;
											$reqpn = mysql2_query($sqlpn);
											$datapn = mysqli_fetch_array($reqpn);
											//Fin recuperation du PN ********************
?>
	
						<form action="validation-send-email-quotation.php" method="post">
						<input type="hidden" name="RFQ_ID" value="<?php echo $data['Fld_RFQ_ID'];?>">
						<input type="hidden" name="Fld_RFQ_ID" value="<?php echo $data['Fld_RFQ_ID'];?>">
						<input type="hidden" name="quote_id" value="<?php echo (int)$data['ID'];?>">
						<input type="hidden" name="id_tbl_rfq1" value="<?php echo (int)$data['id_tbl_rfq1'];?>">
						<input type="hidden" name="idrfq1" value="<?php echo (int)$data['id_tbl_rfq1'];?>">
						<input type="hidden" name="selected_source_type" value="<?php echo htmlspecialchars($data['source_type'] ?? '', ENT_QUOTES, 'UTF-8');?>">
						<input type="hidden" name="selected_source_id" value="<?php echo (int)($data['source_id'] ?? 0);?>">
						<input type="hidden" name="Fld_Qty" value="<?php echo $data['Fld_Qty'];?>">
						<input type="hidden" name="Fld_Price" value="<?php echo $data['Fld_Price'];?>">
						<input type="hidden" name="Fld_Part_Nbr" value="<?php echo $datapn['Fld_Part_Nbr'];?>">
						<input type="hidden" name="Fld_Part_Desc" value="<?php echo $datapn['Fld_Part_Desc'];?>">
						<input type="hidden" name="Fld_Condition_ID" value="<?php echo $data['Fld_Condition'];?>">
						<input type="hidden" name="Fld_Release_ID" value="<?php echo $data['Fld_Release_ID'];?>">
						<input type="hidden" name="lead_time" value="<?php echo $data['lead_time'];?>">
						<input type="hidden" name="Fld_Remark" value="<?php echo $data['Fld_Remark'];?>">
						<input type="hidden" name="Fld_Tag_Info_ID" value="<?php echo $data['Fld_Tag_Info_ID'];?>">
						<input type="hidden" name="Fld_Tag_Date" value="<?php echo $data['Fld_Tag_Date'];?>">
						<input type="hidden" name="part_id" value="<?php echo $data['Fld_Part_Id'];?>">
						<input type="hidden" name="Fld_Part_SN" value="<?php echo $data['Fld_Part_SN'];?>">
						<input type="hidden" name="FldCurrencyID" value="<?php echo $data['Fld_Currency_ID'];?>">
						<input type="hidden" name="Fld_Priority_ID" value="<?php echo $data['Fld_Priority_ID'];?>">
						<input type="hidden" name="moq" value="<?php echo $data['moq'];?>">
						<input type="hidden" name="Fld_Traceability_ID" value="<?php echo $data['Fld_Traceability_ID'];?>">
						<?php
//recuperation info RFQ 
//	ID Fld_RFQ_ID  Fld_Qty  Fld_Part_ID  Fld_Observation  Fld_Customer_ID  date  Fld_RFQ_Type_ID  Fld_Priority_ID  Employee_ID  id_company_contact  Fld_Payment_Term_ID  Fld_Condition_ID  pn_rfq  description_rfq
$sqlrfq="SELECT * from tbl_RFQ_1 where Fld_RFQ_ID='".$data['Fld_RFQ_ID']."'";
if (!empty($data['id_tbl_rfq1'])) {
    $sqlrfq .= " AND ID=".(int)$data['id_tbl_rfq1'];
}
$sqlrfq .= " ORDER BY ID DESC LIMIT 1";
// echo $sqlrfq;

$reqrfq = mysql2_query($sqlrfq);
$datarfq = mysqli_fetch_array($reqrfq);
$daterfq=$datarfq['date'];
$contactaci=$datarfq['Employee_ID'];
$currentSignatureUser = aci_email_current_user_id();
if ($currentSignatureUser > 0) {
    $contactaci = $currentSignatureUser;
}
// ** tb_company_contact ** id_company_contact Fld_Linked_ID  Fld_Company_ID  Company_Old_Id  Fld_Contact_Name  Fld_Contact_Phone  Fld_Contact_Phone2  Fld_Contact_Fax  Fld_Company_Mobile  Fld_Contact_Division_ID  Fld_Contact_Email  Fld_Contact_Title  Fld_Contact_Remark  status  aci_contact entry_date
$sqlr="SELECT * from tb_company_contact where id_company_contact=".$datarfq['id_company_contact'];
// echo $sqlr;

$reqr = mysql2_query($sqlr);
$datar = mysqli_fetch_array($reqr);

$Fld_Contact_Name=$datar['Fld_Contact_Name'];
//Liste des champs reucperer de la page precedente (quote)
// RFQ_ID  RFQ_DATE  Fld_RFQ_Type_ID   Fld_Part_Nbr  Fld_Part_Desc  Fld_Qty  Fld_Condition_ID  Fld_Part_SN  Fld_Min_Qty  Fld_Remark  Fld_Release_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Traceability_ID  lead_time  Fld_Price  FldCurrencyID
						


$Fld_Qty_recup=$data["Fld_Qty"];
$Fld_Price_recup=$data["Fld_Price"];									
										
/***********************************************************************************/
/***********************************************************************************/
/*Generation de l'email pour client*/
// $mail = "lamalol@gmail.com"; // DÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©claration de l'adresse de destination.
$mail = $datar['Fld_Contact_Email']; // DÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©claration de l'adresse de destination.
if (!preg_match("#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#", $mail)) // On filtre les serveurs qui rencontrent des bogues.
{
	$passage_ligne = "\r\n";
}
else
{
	$passage_ligne = "\n";
}
//=====DÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©claration des messages au format texte et au format HTML.
$message_txt = "Salut ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â  tous, voici un e-mail envoyÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â© par un script PHP.";
// $message_html = "<html><head></head><body><b>Salut ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â  tous</b>, voici un e-mail envoyÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â© par un <i>script PHP</i>.</body></html>";
											

$message_html = aci_quote_email_header_html();

											//reuperation priority
											// ** tbl_Priority ** Fld_Priority_ID  Fld_Priority_Text
					                        $sqlPriority="SELECT Fld_Priority_Text FROM tbl_Priority where Fld_Priority_ID=".$data['Fld_Priority_ID'];
											
											$reqPriority = mysql2_query($sqlPriority);
											$dataPriority = mysqli_fetch_array($reqPriority);
											//Fin reuperation priority
											
$message_html2= 'Dear '.$Fld_Contact_Name.',<br><br>
First of all, thank you very much for your RFQ.<br>
As per your request, we can propose you as follow:
<br><br>
<table border="0" cellpadding="1" cellspacing="1"><tbody>
<tr><td nowrap="" align="left" style="font-weight: bold;" colspan="2"><b>Priority</b> &nbsp;&nbsp;'.$dataPriority['Fld_Priority_Text'].' &nbsp;&nbsp;&nbsp;&nbsp;- &nbsp;&nbsp;<b>RFQ #</b> '.$data['Fld_RFQ_ID'].'</td></tr>
<tr><td align="left" style="font-weight: bold;"><b>Part Number</b></td><td nowrap="" align="left">&nbsp;&nbsp;'.$datapn['Fld_Part_Nbr'].'</td></tr>
<tr><td nowrap="" align="left" style="font-weight: bold;"><b>Description</b></td><td nowrap="" align="left">&nbsp;&nbsp;'.$datapn['Fld_Part_Desc'].'</td></tr>
<tr><td nowrap="" align="left" style="font-weight: bold;"><b>Condition</b></td><td nowrap="" align="left">&nbsp;&nbsp;';
											//recuperation condition 
											// ** tbl_Condition ** Fld_Condition_ID  Fld_Condition_Text
					                        $sqlc="SELECT Fld_Condition_Text FROM tbl_Condition where Fld_Condition_ID=".$data['Fld_Condition'];
											
											$reqc = mysql2_query($sqlc);
											$datac = mysqli_fetch_array($reqc);
											$Fld_Condition_Text_aff=$datac["Fld_Condition_Text"];
											//Fin recuperation condition 

$message_html2.= $Fld_Condition_Text_aff.'</td></tr><tr><td nowrap="" align="left" style="font-weight: bold;"><b>Quantity</b></td><td nowrap="" align="left">&nbsp;&nbsp;'.$Fld_Qty_recup.'</td></tr>
<tr><td nowrap="" align="left" style="font-weight: bold;"><b>Price in USD </b></td><td nowrap="" align="left">&nbsp;$&nbsp;'.$Fld_Price_recup.'&nbsp;EA</td></tr>';

											//recuperation release
											// ** tbl_Release ** Fld_Release_ID  Fld_Release_Text
					                        $sqlrel="SELECT Fld_Release_Text FROM tbl_Release where Fld_Release_ID=".$data['Fld_Release_ID'];
											
											$reqrel = mysql2_query($sqlrel);
											$datarel = mysqli_fetch_array($reqrel);
											//Fin recuperation release
							
											

$message_html2.= '<tr><td nowrap="" align="left" style="font-weight: bold;"><b>Certification</b></td><td nowrap="" align="left">&nbsp;&nbsp;'.$datarel['Fld_Release_Text'].' By '.$data['Fld_Tag_Info_ID'].' '.$data['Fld_Tag_Date'].'</td></tr>
<tr><td nowrap="" align="left" style="font-weight: bold;"><b>Delivery</b></td><td nowrap="" align="left">&nbsp;&nbsp;'.$data['lead_time'].'</td></tr>
<tr><td nowrap="" align="left" style="font-weight: bold;"><b>Comments</b></td><td nowrap="" align="left">&nbsp;&nbsp;'.$data['Fld_Remark'].'</td></tr>
</tbody></table>
<span style="font-size:11px;">Price in USD / Prices & Availability are subject to change / AOG fees may be apply.</span>

<br><br>
In case of any requirement, please remember to contact AeroCanada Industries 770 Inc.
<br><br>
It will always be a pleasure to support you !';
											
											
											//recuperation employee ACI 770 
											// ** tbl_Employee ** 	Employee_ID Employee_Name  Fld_Contact_Id  pw  email  statut  position  tel  mobile  skype
					                        $sqleaci="SELECT * FROM tbl_Employee where Employee_ID=".$contactaci;
											
											$reqeaci = mysql2_query($sqleaci);
											$dataeaci = mysqli_fetch_array($reqeaci);
											//Fin recuperation employee ACI 770

											
$message_html3 = aci_quote_email_signature_html($dataeaci);
//==========
 
//=====CrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©ation de la boundary
$boundary = "-----=".md5(rand());
//==========
 
//=====DÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©finition du sujet.
$sujet = "ACI770 QUOTE - PN ".$datapn['Fld_Part_Nbr']." - ".$Fld_Condition_Text_aff." - ".$datapn['Fld_Part_Desc'];
//=========
 
//=====CrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©ation du header de l'e-mail.
$header = "From: \"".$dataeaci['Employee_Name']."\"<".$dataeaci['email'].">".$passage_ligne;
$header.= "Reply-to: \"".$dataeaci['Employee_Name']."\" <".$dataeaci['email'].">".$passage_ligne;
$header.= "MIME-Version: 1.0".$passage_ligne;
$header.= "Content-Type: multipart/alternative;".$passage_ligne." boundary=\"$boundary\"".$passage_ligne;
//==========
 
//=====CrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©ation du message.
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
// mail("roy@aerocanada.aero",$sujet,$message,$header);
//==========

/*Fin Generation de l'email pour client*/

//affichage information pour validation 
echo '<b>E-mail</b> : <input class="form-control" name="clientemail" value="'.htmlspecialchars($mail, ENT_QUOTES, 'UTF-8').'"><br><br>';
echo '<b>E-mail CC</b> : <input class="form-control" name="emailcc"><br>';

echo '<div class="form-group"><label>Subject</label><input class="form-control" name="sujet" value="'.$sujet.'"></div>';
echo $message_html;
?>	
<input type="hidden" name="Fld_Contact_Name" value="<?php echo $Fld_Contact_Name;?>">
<textarea name="message_html2" style="height: 450px;" rows="10" height="450"><?php echo $message_html2;?></textarea>
		<script>
			CKEDITOR.replace( 'message_html2' );
		</script>	
	<?php 
	echo $message_html3;
	?>	
<div align="center"><button type="submit" class="btn btn-danger btn-lg">Send Email to Customer</button></div>

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
    </script>



</body>

</html>

<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php\">";
?>
