<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

//******tbl_RFQ_1******ID   Fld_RFQ_ID  Fld_Qty  Fld_Part_ID  Fld_Observation  Fld_Customer_ID  date  Fld_RFQ_Type_ID  Fld_Priority_ID  Employee_ID  id_company_contact  Fld_Payment_Term_ID  Fld_Condition_ID  pn_rfq  description_rfq

//recuperation du rfq ID
$sql="SELECT Fld_RFQ_ID FROM tbl_RFQ_1 where ID='".$_GET["ID"]."'";
$req = mysql2_query($sql);
$data = mysqli_fetch_array($req);
//Fin recuperation du rfq ID

$req2="DELETE FROM tbl_RFQ_1 where ID='".$_GET["ID"]."'";
$requete = mysql2_query($req2);

$req3="DELETE FROM tbl_RFQ_3 where Fld_RFQ_ID='".$data['Fld_RFQ_ID']."' and id_tbl_rfq1='".$_GET["ID"]."'";
$requete2 = mysql2_query($req3);

echo "<META http-equiv=\"refresh\" content=\"0;URL=details_rfq.php?Fld_RFQ_ID=".$data['Fld_RFQ_ID']."\">";

?>