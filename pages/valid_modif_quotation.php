<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";


require('../classes/rfq.class.php');
$objet=new rfq();
$donnee = $objet->modif_quote();

echo "<META http-equiv=\"refresh\" content=\"0;URL=modif_quotations.php?ID=".$_POST['ID']."\">";
?>