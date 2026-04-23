<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
require('../classes/rfq.class.php');
$objet=new rfq();
$donnee = $objet->del_quote($_GET['ID']);

echo "<META http-equiv=\"refresh\" content=\"0;URL=quotations.php\">";

?>