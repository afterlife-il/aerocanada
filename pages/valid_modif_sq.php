<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

require('../classes/sq.class.php');
$objet=new sq();
$donnee = $objet->modif_sq();

// Redirect logic: RFQ page > Part page > SQ list
if (!empty($_POST['Fld_RFQ_ID'])) {
    header("Location: valid_add_multi_pn_rfq.php?Fld_RFQ_ID=" . urlencode($_POST['Fld_RFQ_ID']));
} elseif (!empty($_POST['part_id'])) {
    header("Location: Part-Nbr.php?part_id=" . urlencode($_POST['part_id']));
} else {
    header("Location: suppliers_quote.php");
}
exit;
?>
