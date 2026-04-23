<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
require('../classes/currency.class.php');
$objet=new currency();

$donnee = $objet->modif_currency();

//echo "<META http-equiv=\"refresh\" content=\"0;URL=currency.php\">";

?>