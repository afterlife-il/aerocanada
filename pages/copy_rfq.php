<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";


//gestion de la duplication de la ligne dans la table RFQ
//ID   Fld_RFQ_ID  Fld_Qty  Fld_Part_ID  Fld_Observation  Fld_Customer_ID  date  Fld_RFQ_Type_ID  Fld_Priority_ID  Employee_ID  id_company_contact  Fld_Payment_Term_ID  Fld_Condition_ID  pn_rfq  description_rfq

//requete permetant la duplication d'une ligne dans une table
//INSERT INTO ma_table (colonne1, colonne2, colonne3, colonne4) SELECT colonne1, colonne2, colonne3, colonne4 FROM ma_table2 where id = 2;//exemple de duplication de lignes

$req="INSERT INTO tbl_RFQ_1 (ID, Fld_RFQ_ID, Fld_Qty, Fld_Part_ID, Fld_Observation, Fld_Customer_ID, date, Fld_RFQ_Type_ID, Fld_Priority_ID, Employee_ID, id_company_contact, Fld_Payment_Term_ID, Fld_Condition_ID, pn_rfq, description_rfq) SELECT '', Fld_RFQ_ID, Fld_Qty, Fld_Part_ID, Fld_Observation, Fld_Customer_ID, date, Fld_RFQ_Type_ID, Fld_Priority_ID, Employee_ID, id_company_contact, Fld_Payment_Term_ID, Fld_Condition_ID, pn_rfq, description_rfq FROM tbl_RFQ_1 where ID='".$_GET["ID"]."'";
// echo $req;
$requete = mysql2_query($req);
$lastid=mysqli_insert_id($connection);

//verification si une quotation a ete faite avec rfq id et si oui duplication de la quote
//****tbl_RFQ_3**** ID  Fld_RFQ_ID  Fld_Quote_Date  Fld_Part_Id  Fld_Part_SN  Fld_Qty  Fld_Condition  Fld_Price  Fld_Price_Min  Fld_Price_Max  Fld_Currency_ID  Fld_Remark  Fld_Supply_Date  Fld_Traceability_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Release_ID  Fld_Linked_ID  Fld_Exch_Core_Value  Fld_Exch_Core_Value_Currency_ID  Fld_Exch_Cond  Fld_IsBeen_Chosen  Fld_Send_Mail  Fld_Exch_Core_RCVD  moq  lead_time  Fld_Priority_ID  id_tbl_rfq1
$sqlverifquote="SELECT * FROM tbl_RFQ_3 WHERE Fld_RFQ_ID='".$_GET['Fld_RFQ_ID']."'";
// echo $sqlverifquote;
$resultverifquote = mysql2_query($sqlverifquote);
$num_rows_rfq3 = mysqli_num_rows($resultverifquote);
if (0<$num_rows_rfq3)
{
$datequote=date("d-m-y");
	
$req2="SELECT * FROM tbl_RFQ_1 WHERE ID='".$lastid."'";
$requete2 = mysql2_query($req2);
$data2 = mysqli_fetch_array($requete2);
	
	$sql3="INSERT INTO `tbl_RFQ_3` (`ID`, `Fld_RFQ_ID`, `Fld_Quote_Date`, `Fld_Part_Id`, `Fld_Part_SN`, `Fld_Qty`, `Fld_Condition`, `Fld_Price`, `Fld_Price_Min`, `Fld_Price_Max`, `Fld_Currency_ID`, `Fld_Remark`, `Fld_Supply_Date`, `Fld_Traceability_ID`, `Fld_Tag_Info_ID`, `Fld_Tag_Date`, `Fld_Release_ID`, `Fld_Linked_ID`, `Fld_Exch_Core_Value`, `Fld_Exch_Core_Value_Currency_ID`, `Fld_Exch_Cond`, `Fld_IsBeen_Chosen`, `Fld_Send_Mail`, `Fld_Exch_Core_RCVD`, `moq`, `lead_time`, `Fld_Priority_ID`, `id_tbl_rfq1`) 
	VALUES (NULL, '".$data2['Fld_RFQ_ID']."', '".$datequote."', '".$data2['Fld_Part_ID']."', '', '".$data2['Fld_Qty']."', '".$data2['Fld_Condition_ID']."', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '".$data2['Fld_Priority_ID']."', '".$data2['ID']."');";
		// echo $sql;
		 $requete3 = mysql2_query($sql3);
	
}
//Fin verification si une quotation a ete faite avec rfq id et si oui duplication de cette ligne de quotation

echo "<META http-equiv=\"refresh\" content=\"0;URL=details_rfq.php?Fld_RFQ_ID=".$_GET["Fld_RFQ_ID"]."\">";

?>