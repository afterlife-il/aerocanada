<?php
/**************************************************************************/
include_once "conf.php";
include_once "page_titles.php";
/*SAISIE DU PO ACKNOWLEDGMENT dans la BDD*/
//1er etape table tbl_PO_Customer

				//recuperation Supplier ID
				/** tbl_RFQ_2 **/
				$sqlsupplierid="SELECT Fld_Supplier_ID FROM tbl_RFQ_2 where Fld_RFQ_ID='".$_POST['Fld_RFQ_ID']."'";
				
				$reqsupplierid = mysql2_query($sqlsupplierid);
				$datasupplierid = mysqli_fetch_array($reqsupplierid);
				//Fin recuperation Supplier ID
				
				//creation du po nb aci770
				//recuperation du dernier numero de PO
				$sqlrecupponb="SELECT MAX(nb_po) as maxpo FROM tbl_PO_Customer";
				$reqrecupponb = mysql2_query($sqlrecupponb);
				$datarecupponb = mysqli_fetch_array($reqrecupponb);
				$maxpo=$datarecupponb['maxpo'];
				//Fin recuperation du dernier numero de PO
				$newnb=$maxpo+181;
				$Fld_ACI_PO="AC".date('ymd')."-".$newnb;
				$newmaxpo=$maxpo+1;
				//Fin creation du po nb aci770
				
				//Recuperation de la date de paiement
				if(!empty($_POST['Fld_Payment_Term_ID'])){
				$sqlnbjour="SELECT nb_jour FROM tbl_Payment where Fld_Payment_Term_ID='".$_POST['Fld_Payment_Term_ID']."'";
				
				$reqnbjour = mysql2_query($sqlnbjour);
				$datanbjour = mysqli_fetch_array($reqnbjour);
				if($datanbjour['nb_jour']=='0') $Fld_Payment_Date=date("Y-m-d");
				else {
				$dt = date("Y-m-d");
				$Fld_Payment_Date=date( "Y-m-d", strtotime( $dt." +".$datanbjour['nb_jour']." day" ) );
				$jour_reminder=$datanbjour['nb_jour']-5;
				$Fld_Reminder_Date=date( "Y-m-d", strtotime( $dt." +".$jour_reminder." day" ) );
				}
				}
				//Fin Recuperation de la date de paiement
			
$req="INSERT INTO `tbl_PO_Customer` (`Fld_Row_ID`, `Fld_PO`, `Fld_Current_Date`, `Fld_Company_Supplier_ID`, `Fld_Company_Details_ID`, `Fld_Company_ShipTo_ID`, `Fld_Fwrd_Ship_Via`, `Fld_Vat_ID`, `Fld_Remark`, `Fld_ACI_PO`, `Fld_ACI_Location_ID`, `Fld_Supplier_Invoice_Nbr`, `Fld_Supplier_Payment_Method`, `Fld_Payment_Date`, `Fld_Shipping_Cost`, `id_company_address_accounting`, `id_company_address_delivery`, `id_Forwarder`, `nb_po`) VALUES (NULL, '".$_POST['customer_po_number']."', '".$_POST['po_date']."', '".$datasupplierid['Fld_Supplier_ID']."', NULL, NULL, NULL, NULL, '".$_POST['Fld_Remark']."', '".$Fld_ACI_PO."', NULL, NULL, NULL, '".$Fld_Payment_Date."', NULL, '".$_POST['id_company_address_accounting']."', '".$_POST['id_company_address_delivery']."', '".$_POST['id_Forwarder']."', '".$newmaxpo."');";
$requete = mysql2_query($req);
//Fin table tbl_PO_Customer

//2e etape table Tbl_Customer_PO_Follow_UP
//Fld_Phase id:12 -  Acknowledged
$req2="INSERT INTO `Tbl_Customer_PO_Follow_UP` (`id`, `Fld_RFQ_ID`, `Fld_Part_ID`, `Fld_Part_SN`, `Fld_Reminder_Date`, `Fld_Last_Info`, `Fld_Phase`, `Fld_Phase_Person_In_Charge`, `Fld_Date_in_Phase`, `Fld_Urgency_Dgree`, `Fld_PO`, `Fld_RFQ3_ID`, `Fld_Stock_ID`) VALUES (NULL, '".$_POST['Fld_RFQ_ID']."', '".$_POST['Fld_Part_Id']."', '".$_POST['Fld_Part_SN']."', '".$Fld_Reminder_Date."', NULL, '12', '".$_SESSION['id_utilisateur']."', '".$_POST['po_date']."', '".$Fld_Priority_ID."', '".$_POST['customer_po_number']."', '".$_POST['Fld_RFQ3_ID']."', NULL);";
$requete2 = mysql2_query($req2);
//FIN table Tbl_Customer_PO_Follow_UP

//3e etape table tbl_Sales_Order
$req3="INSERT INTO `tbl_Sales_Order` (`Fld_Row_ID`, `Fld_PO`, `Fld_Current_Date`, `Fld_Company_Supplier_ID`, `Fld_Company_Details_ID`, `Fld_Company_Supplier_Contact`, `Fld_Company_ShipTo_ID`, `Fld_Company_ShipTo_Details_ID`, `Fld_Company_ShipTo_Contact`, `Fld_Fwrd_Ship_Via`, `Fld_Vat_ID`, `Fld_Remark`, `Fld_ACI_PO`, `Fld_ACI_Location_ID`, `Fld_Supplier_Invoice_Nbr`, `Fld_Supplier_Payment_Method`, `Fld_Payment_Date`, `Fld_Supplier_Bank_Account`, `Fld_Paying_Bank_Account`, `Fld_Payment_Cheque_No`, `Fld_Paid_in_full`, `Fld_Payment_Remark`, `Fld_PO_Still_Open`, `Fld_AWB`, `Fld_AWB_Out_RCVD_by_Supplier`, `Fld_Shipping_Porforma_Nbr`, `Fld_Country_Origin`, `Fld_No_Boxes`, `Fld_RFQ_ID`, `Fld_Priority_ID`, `Fld_AWB_Coming_IN`, `Fld_AWB_Coming_RCVD`, `Fld_Employee_ID`, `Fld_Order_Rating`, `Fld_Approved_for_Payment_by`) VALUES (NULL, '".$_POST['customer_po_number']."', '".$_POST['po_date']."', '".$datasupplierid['Fld_Supplier_ID']."', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '".$_POST['Fld_Remark']."', '".$Fld_ACI_PO."', NULL, NULL, NULL, '".$Fld_Payment_Date."', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '".$_POST['Fld_RFQ_ID']."', '".$Fld_Priority_ID."', NULL, NULL, '".$_SESSION['id_utilisateur']."', NULL, NULL);";
$requete3 = mysql2_query($req3);
//FIN table tbl_Sales_Order

//4e etape table tbl_RFQ

//je verifie si une rfq a ete faite pour ce rfqid dans tbl_RFQ_1
$resultrfq1 = mysql2_query("SELECT * FROM tbl_RFQ_1 where Fld_RFQ_ID='".$_POST['Fld_RFQ_ID']."'");
$num_rows_rfq1 = mysqli_num_rows($resultrfq1);
if(0<$num_rows_rfq1) $Fld_Step_1="TRUE"; else $Fld_Step_1="FALSE";
//Fin verification si une rfq a ete faite pour ce rfqid dans tbl_RFQ_1

//je verifie si une rfq a ete faite pour ce rfqid dans tbl_RFQ_2
$resultrfq2 = mysql2_query("SELECT * FROM tbl_RFQ_2 where Fld_RFQ_ID='".$_POST['Fld_RFQ_ID']."'");
$num_rows_rfq2 = mysqli_num_rows($resultrfq2);
if(0<$num_rows_rfq2) $Fld_Step_2="TRUE"; else $Fld_Step_2="FALSE";
//Fin verification si une rfq a ete faite pour ce rfqid dans tbl_RFQ_2

//je verifie si une rfq a ete faite pour ce rfqid dans tbl_RFQ_3
$resultrfq3 = mysql2_query("SELECT * FROM tbl_RFQ_3 where Fld_RFQ_ID='".$_POST['Fld_RFQ_ID']."'");
$num_rows_rfq3 = mysqli_num_rows($resultrfq3);
if(0<$num_rows_rfq3) $Fld_Step_3="TRUE"; else $Fld_Step_3="FALSE";
//Fin verification si une rfq a ete faite pour ce rfqid dans tbl_RFQ_3

//je verifie si le RFQ ID se trouve dans la table tbl_RFQ 
$result = mysql2_query("SELECT * FROM tbl_RFQ where Fld_RFQ_ID='".$_POST['Fld_RFQ_ID']."'");
$num_rows = mysqli_num_rows($result);
if(0<$num_rows)
{
// Textes complets	   Fld_Customer_ID
//ID  Fld_RFQ_ID  Fld_Date  Fld_Step_1  Fld_Step_2  Fld_Step_3  Fld_Priority_ID  Fld_Observation  Fld_RFQ_Type_ID  Fld_RFQ_ACI_Employee_Id  Fld_Customer_ID  Fld_Contact_ID  Fld_Customer_Detail_ID  Fld_PO  Fld_PO_Date  Fld_Requested_Date  Fld_Payment_Term_ID  Fld_Customer_Forwarder_ID  Fld_ShipTo_Company  Fld_Customer_ShippingDetail_ID  Fld_Customer_ShippingContact_ID  Fld_PO_IsOpen  RowIndex  Fld_Order_Rating
$sql4="update tbl_RFQ set Fld_Step_1='".$Fld_Step_1."',Fld_Step_2='".$Fld_Step_2."',Fld_Step_3='".$Fld_Step_3."',Fld_Priority_ID='".$Fld_Priority_ID."',Fld_PO='".$_POST['customer_po_number']."',Fld_PO_Date='".$_POST['po_date']."' where Fld_RFQ_ID='".$_POST['Fld_RFQ_ID']."'";
$query4=mysql2_query($sql4);	
}
else {
$req5="INSERT INTO `tbl_RFQ` (`ID`, `Fld_RFQ_ID`, `Fld_Date`, `Fld_Step_1`, `Fld_Step_2`, `Fld_Step_3`, `Fld_Priority_ID`, `Fld_Observation`, `Fld_RFQ_Type_ID`, `Fld_RFQ_ACI_Employee_Id`, `Fld_Customer_ID`, `Fld_Contact_ID`, `Fld_Customer_Detail_ID`, `Fld_PO`, `Fld_PO_Date`, `Fld_Requested_Date`, `Fld_Payment_Term_ID`, `Fld_Customer_Forwarder_ID`, `Fld_ShipTo_Company`, `Fld_Customer_ShippingDetail_ID`, `Fld_Customer_ShippingContact_ID`, `Fld_PO_IsOpen`, `RowIndex`, `Fld_Order_Rating`) VALUES (NULL, '".$_POST['Fld_RFQ_ID']."', '".$_POST['date_rfq']."', '".$Fld_Step_1."', '".$Fld_Step_2."', '".$Fld_Step_3."', '".$Fld_Priority_ID."', NULL, NULL, '".$_SESSION['id_utilisateur']."', '".$_POST['Fld_Customer_ID']."', NULL, NULL, '".$_POST['customer_po_number']."', '".$_POST['po_date']."', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);";
$requete5 = mysql2_query($req5);
}
//FIN table tbl_RFQ


//Mise a jour de la quotation (table tbl_RFQ_3)
/****tbl_RFQ_3**** ID  Fld_RFQ_ID  Fld_Quote_Date  Fld_Part_Id  Fld_Part_SN  Fld_Qty  Fld_Condition  Fld_Price  Fld_Price_Min  Fld_Price_Max  Fld_Currency_ID  Fld_Remark  Fld_Supply_Date  Fld_Traceability_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Release_ID  Fld_Linked_ID  Fld_Exch_Core_Value  Fld_Exch_Core_Value_Currency_ID  Fld_Exch_Cond  Fld_IsBeen_Chosen  Fld_Send_Mail  Fld_Exch_Core_RCVD  moq  lead_time  Fld_Priority_ID */
$sql6="update tbl_RFQ_3 set Fld_Part_SN='".$_POST['Fld_Part_SN']."',Fld_Price='".$_POST['Fld_Price']."',Fld_Currency_ID='".$_POST['FldCurrencyID']."',Fld_Remark='".$_POST['Fld_Remark']."' where ID='".$_POST['Fld_RFQ3_ID']."'";
$query6=mysql2_query($sql6);
//Fin  mise a jour de la quotation (table tbl_RFQ_3)


//Mise a jour du core value dans la table part
/*****tbl_Parts*************  Fld_Part_ID  Fld_Part_Nbr  Fld_Part_Desc  Fld_Part_MFG  Fld_Part_MFG_Old  Fld_AC_ID  Fld_Old_LP  Fld_Part_List_Price  Fld_Part_Price_Currency_ID  Fld_Part_LP_Date  Fld_Remark status alt_pn Fld_Add_PN_Date aci_contact_entry ata_chapter   cage_code   essentiality_category_id  nha  moq  	oem_lead_time  core_value  id_currency_core_value*/
$sql7="update tbl_Parts set core_value='".$_POST['core_value']."',id_currency_core_value='".$_POST['id_currency_core_value']."' where Fld_Part_ID='".$_POST['Fld_Part_Id']."'";
$query7=mysql2_query($sql7);
//Fin  Mise a jour du core value dans la table part

/*END SAISIE DU PO ACKNOWLEDGMENT dans la BDD*/
/*********************************************/
/* include autoloader */
require_once 'dompdf/autoload.inc.php';

/* reference the Dompdf namespace */
use Dompdf\Dompdf;

/* instantiate and use the dompdf class */
$dompdf = new Dompdf();

//newsletter html
$html = '<body style="height: 100%;margin: 0;padding: 0;width: 100%;font-size: 12px;">


		<table style="width: 100%;">
		<tr><td style="width: 16%;"><img src="'.$_SERVER["DOCUMENT_ROOT"].'/pages/images/logo-aerocanada.png" style="padding:5px;width:100px;"></td>
		<td align="left" style="width: 42%;">
		<h3>AEROCANADA INDUSTRIES 770 INC.</h3>
		99, Prince Street, 7th Floor, Suite#701<br>
		Montreal QC H3C 2M7, Canada<br>
		tel. +1 514 80 06 223 | fax. +1 514 80 06 224
		</td>
		<td style="width: 42%;">
		<span style="font-size: 13px;">ORDER ACKNOWLEDGEMENT</span>
		<hr>
		<table style="width: 100%;">
		<tr><td style="width: 50%;">Sales Order</td><td style="width: 50%;font-weight: bold;">'.$Fld_ACI_PO.'</td></tr>
		<tr><td colspan="2"><hr></td></tr>
		<tr><td>Date</td><td style="font-weight: bold;">'.$_POST['date_rfq'].'</td></tr>
		<tr><td colspan="2"><hr></td></tr>
		<tr><td>Taken by</td><td style="font-weight: bold;">'.$_POST['date_rfq'].'</td></tr>
		<tr><td colspan="2"><hr></td></tr>
		<tr><td>Your PO</td><td style="font-weight: bold;">'.$_POST['customer_po_number'].'</td></tr>
		<tr><td colspan="2"><hr></td></tr>
		<tr><td>Ordered by</td><td style="font-weight: bold;">'.$_POST['customer_po_number'].'<td></td></tr>
		<tr><td colspan="2"><hr></td></tr>
		</td></tr></table>
		
		</td></tr></table>
		<br>
		<table  style="width: 100%;">
		<tr>
		<td width: 50%;>Invoice Address<br>
		<hr>';
		
		//**tb_company**  Fld_Company_ID        Company_Old_Id       Fld_Company_Name      Fld_Company_Rating_ID    delete   companyrating	aci_contact   logocompany status internet cage_code  customer_payment_term_id   customer_payment_term_amount   customer_payment_term_currencyid  aci_payment_term_id  aci_payment_term_amount   aci_payment_term_currencyid
		
		//**tbl_Company_Details**   id_tbl_company_Details Fld_Linked_ID Fld_Company_ID Company_Old_Id Fld_Company_Type_ID Fld_Company_Country Fld_Company_City Fld_Company_State Fld_Company_Street Fld_Company_ZipCode Fld_Company_Fax Fld_Company_Phone Fld_Company_Email Fld_Company_Score Fld_Company_BAX_Contact Fld_Remark Fld_VAT_Nbr Fld_Date_Of_First_Contact Fld_Company_Address_Type  UTC_timezone   title_address
		
				//recuperation accounting address
				$sqlaccountadd="SELECT * FROM tb_company,tbl_Company_Details where tb_company.Fld_Company_ID=tbl_Company_Details.Fld_Company_ID AND tbl_Company_Details.id_tbl_company_Details='".$_POST['id_company_address_accounting']."' AND tb_company.Fld_Company_ID='".$_POST['Fld_Customer_ID']."'";
				
				$reqaccountadd = mysql2_query($sqlaccountadd);
				$dataaccountadd = mysqli_fetch_array($reqaccountadd);
				//Fin recuperation accounting address
		
		$html.= $dataaccountadd['Fld_Company_Name'].'<br>'.
		$dataaccountadd['Fld_Company_Street'].'<br>'.
		$dataaccountadd['Fld_Company_City'].'<br>'.
		$dataaccountadd['Fld_Company_ZipCode'].'<br>'.
		$dataaccountadd['Fld_Company_Country'].'<br>';
		
		$html.= '</td>
		<td width: 50%;>Deliver to<br>
		<hr>';
		
				//recuperation delivery address
				$sqldeliveradd="SELECT * FROM tb_company,tbl_Company_Details where tb_company.Fld_Company_ID=tbl_Company_Details.Fld_Company_ID AND tbl_Company_Details.id_tbl_company_Details='".$_POST['id_company_address_delivery']."' AND tb_company.Fld_Company_ID='".$_POST['Fld_Customer_ID']."'";
				// echo $sqldeliveradd;
				
				$reqdeliveradd = mysql2_query($sqldeliveradd);
				$datadeliveradd = mysqli_fetch_array($reqdeliveradd);
				//Fin recuperation delivery address
				
				
		$html.= $datadeliveradd['Fld_Company_Name'].'<br>'.
		$datadeliveradd['Fld_Company_Street'].'<br>'.
		$datadeliveradd['Fld_Company_City'].'<br>'.
		$datadeliveradd['Fld_Company_ZipCode'].'<br>'.
		$datadeliveradd['Fld_Company_Country'].'<br>';
		
		$html.= '</td>
		</tr>
		</table>
		<br>
		<table  style="width: 100%;">
		<thead>
		 	 														
                                    <tr>
										<th>ITEM</th>
                                        <th>PART NO</th>
                                        <th>DESCRIPTION</th>
                                        <th>Date Req</th>
                                        <th>Date Due</th>
                                        <th>REL</th>
                                        <th>QTY</th>
                                        <th>PRICE/UOM</th>
                                        <th>VALUE</th>
                                    </tr>
									<tr><td colspan="9"><hr></td></tr>
                                </thead>';
		
		//recuperation information de la commande
		$sqlinfcom="SELECT * FROM tbl_RFQ_1,tbl_RFQ_3 where tbl_RFQ_1.Fld_RFQ_ID=tbl_RFQ_3.Fld_RFQ_ID AND tbl_RFQ_1.Fld_RFQ_ID='".$_POST['Fld_RFQ_ID']."'";
		// echo $sqlinfcom;
		
		$reqsqlinfcom = mysql2_query($sqlinfcom);
		$datasqlinfcom = mysqli_fetch_array($reqsqlinfcom);
		//Fin recuperation information de la commande
		
											//recuperation release
											// ** tbl_Release ** Fld_Release_ID  Fld_Release_Text
					                        $sqlrelease="SELECT Fld_Release_Text from tbl_Release where Fld_Release_ID='".$datasqlinfcom['Fld_Release_ID']."'";
											
											$reqrelease = mysql2_query($sqlrelease);
											$datarelease = mysqli_fetch_array($reqrelease);
											//Fin recuperation release
											
											//formatage du prix
											setlocale(LC_MONETARY, 'en_US.UTF-8');
											$nombre_format_francais = money_format('%.2n', $datasqlinfcom['Fld_Price']);
											//Fin formatage du prix
		$html.= '<tr>
		
		<td>1.0</td>
		<td>'.$datasqlinfcom['pn_rfq'].'</td>
		<td>'.$datasqlinfcom['description_rfq'].'</td>
		<td>'.$datasqlinfcom['date'].'</td>
		<td>'.$datasqlinfcom['date'].'</td>
		<td>'.$datarelease['Fld_Release_Text'].'</td>
		<td>'.$datasqlinfcom['Fld_Qty'].'</td>
		<td>'.$nombre_format_francais.'</td>
		<td>'.$nombre_format_francais.'</td>
		</tr>
		<tr><td colspan="9"><hr></td></tr>
		<tr>
		<td colspan="6">
		Order type : <b>Standard sales order</b><br>
		Urgency<br>
		Shipping method : <b>B & H - COLLECTION</b>
		</td>
		<td colspan="3">
		Freight $0.00<BR>
		<hr>
		Sub Total $40,000.00<BR>
		<hr>
		VAT 20.0% $0.00<BR>
		<hr>
		<b>Total Due</b> : $40,000.00<BR>
		<hr>
		Currency : Dollars (USD)
		</td>
		</tr>';
		
		$html.= '</table>
		
		<br>	
		<div style="padding:5px;font-size: 10px;">
		Comments:<br>
		All goods supplied or services given are subject to the standard terms and conditions of Saywell International Limited, a copy of which canbe seen on our web site at www.saywell.co.uk/tc.pdf
		<br><br>
		Please note: There may be additional Customs import duty charges applicable which have not been reflected in the total shown. If any dutycharges are applicable, you will be notified at time of dispatch and the amount will be reflected in the final invoice.
		</div>
		</body>';
		
		echo $html;
		
//fin newsletter html
$dompdf->loadHtml($html);

/* Render the HTML as PDF */
$dompdf->render();

/* Output the generated PDF to Browser */
// $dompdf->stream();

$output = $dompdf->output();
file_put_contents('pdf/filename.pdf', $output);


//**********************************************************Envoie Email

$mail = 'lamalol@gmail.com'; // Déclaration de l'adresse de destination.
if (!preg_match("#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#", $mail)) // On filtre les serveurs qui présentent des bogues.
{
	$passage_ligne = "\r\n";
}
else
{
	$passage_ligne = "\n";
}
//=====Déclaration des messages au format texte et au format HTML.
$message_txt = "ORDER ACKNOWLEDGEMENT";
$message_html = "<html><head></head><body><b>ORDER ACKNOWLEDGEMENT</b></body></html>";
//==========
 
//=====Lecture et mise en forme de la pièce jointe.
$fichier   = fopen("pdf/filename.pdf", "r");
$attachement = fread($fichier, filesize("pdf/filename.pdf"));
$attachement = chunk_split(base64_encode($attachement));
fclose($fichier);
//==========
 
//=====Création de la boundary.
$boundary = "-----=".md5(rand());
$boundary_alt = "-----=".md5(rand());
//==========
 
//=====Définition du sujet.
$sujet = "ORDER ACKNOWLEDGEMENT";
//=========
 
//=====Création du header de l'e-mail.
$header = "From: \"Aerocanada\"<roy@aerocanada.aero>".$passage_ligne;
$header.= "Reply-to: \"Aerocanada\" <roy@aerocanada.aero>".$passage_ligne;
$header.= "MIME-Version: 1.0".$passage_ligne;
$header.= "Content-Type: multipart/mixed;".$passage_ligne." boundary=\"$boundary\"".$passage_ligne;
//==========
 
//=====Création du message.
$message = $passage_ligne."--".$boundary.$passage_ligne;
$message.= "Content-Type: multipart/alternative;".$passage_ligne." boundary=\"$boundary_alt\"".$passage_ligne;
$message.= $passage_ligne."--".$boundary_alt.$passage_ligne;
//=====Ajout du message au format texte.
$message.= "Content-Type: text/plain; charset=\"ISO-8859-1\"".$passage_ligne;
$message.= "Content-Transfer-Encoding: 8bit".$passage_ligne;
$message.= $passage_ligne.$message_txt.$passage_ligne;
//==========
 
$message.= $passage_ligne."--".$boundary_alt.$passage_ligne;
 
//=====Ajout du message au format HTML.
$message.= "Content-Type: text/html; charset=\"ISO-8859-1\"".$passage_ligne;
$message.= "Content-Transfer-Encoding: 8bit".$passage_ligne;
$message.= $passage_ligne.$message_html.$passage_ligne;
//==========
 
//=====On ferme la boundary alternative.
$message.= $passage_ligne."--".$boundary_alt."--".$passage_ligne;
//==========
 
 
 
$message.= $passage_ligne."--".$boundary.$passage_ligne;
 
//=====Ajout de la pièce jointe.
// $message.= "Content-Type: image/jpeg; name=\"image.jpg\"".$passage_ligne;
$message.= "Content-Type: application/pdf; name=\"pdf/filename.pdf\"".$passage_ligne;
$message.= "Content-Transfer-Encoding: base64".$passage_ligne;
$message.= "Content-Disposition: attachment; filename=\"pdf/filename.pdf\"".$passage_ligne;
$message.= $passage_ligne.$attachement.$passage_ligne.$passage_ligne;
$message.= $passage_ligne."--".$boundary."--".$passage_ligne; 
//========== 
//=====Envoi de l'e-mail.
mail($mail,$sujet,$message,$header);
// mail("yohan@aerocanada.aero",$sujet,$message,$header);
 
//==========

//**********************************************************Fin Envoie Email
// echo "<META http-equiv=\"refresh\" content=\"0;URL=po_validation.php?Fld_RFQ_ID=".$_POST['Fld_RFQ_ID']."\">";
?>