<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";


require('../classes/rfq.class.php');
$objet=new rfq();
$donnee = $objet->modif_quote();

$id = (int)$_POST['ID'];
$sourceType = addslashes($_POST['source_type'] ?? '');
$sourceId = (int)($_POST['source_id'] ?? 0);
mysql2_query("UPDATE tbl_RFQ_3 SET source_type='".$sourceType."', source_id='".$sourceId."' WHERE ID='".$id."'");

$rfqLineId = (int)($_POST['id_tbl_rfq1'] ?? 0);
$customerContactId = (int)($_POST['customer_contact_id'] ?? 0);
if ($rfqLineId > 0 && $customerContactId > 0) {
    mysql2_query("UPDATE tbl_RFQ_1 SET id_company_contact='".$customerContactId."' WHERE ID='".$rfqLineId."'");
}

if (!empty($_POST['send_quotation'])) {
    echo "<META http-equiv=\"refresh\" content=\"0;URL=return_email_quote.php?ID=".$id."\">";
} elseif (!empty($_POST['clean_mode'])) {
    echo "<META http-equiv=\"refresh\" content=\"0;URL=modif_quotations.php?ID=".$id."&mode=clean\">";
} else {
    echo "<META http-equiv=\"refresh\" content=\"0;URL=modif_quotations.php?ID=".$id."\">";
}
?>
