<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";


require('../classes/rfq.class.php');
$objet=new rfq();
$donnee = $objet->add_pn_rfq();

echo "<META http-equiv=\"refresh\" content=\"0;URL=details_rfq.php?Fld_RFQ_ID=".$_POST['Fld_RFQ_ID']."\">";

?>