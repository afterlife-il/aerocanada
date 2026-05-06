<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

require('../classes/sq.class.php');
$objet=new sq();
$donnee = $objet->add_sq();

$rfqId = !empty($_POST['Fld_RFQ_ID']) ? $_POST['Fld_RFQ_ID'] : '';

if ($rfqId !== '') {
    header("Location: valid_add_multi_pn_rfq.php?Fld_RFQ_ID=" . urlencode($rfqId));
} else {
    header("Location: suppliers_quote.php");
}
exit;
?>
