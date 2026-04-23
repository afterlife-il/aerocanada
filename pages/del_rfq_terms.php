<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
require('../classes/rfq.class.php');
$objet=new rfq();
$donnee = $objet->del_rfq_terms($_GET['idsup']);

echo "<META http-equiv=\"refresh\" content=\"0;URL=rfq_terms.php\">";

?>