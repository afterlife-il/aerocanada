<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";


require('../classes/rfq.class.php');
$objet=new rfq();
$donnee = $objet->modif_quote();

$id = (int)$_POST['ID'];
if (!empty($_POST['send_quotation'])) {
    echo "<META http-equiv=\"refresh\" content=\"0;URL=return_email_quote.php?ID=".$id."\">";
} else {
    echo "<META http-equiv=\"refresh\" content=\"0;URL=modif_quotations.php?ID=".$id."\">";
}
?>
