<?php
session_start();
include_once "conf.php";
if (!isset($_SESSION["conectroy"]) || $_SESSION["conectroy"] !== "parfait") { header("Location: login.php"); exit; }
include_once "page_titles.php";
require('../classes/currency.class.php');
$objet=new currency();
$donnee = $objet->del_currency($_GET['idsup']);

echo "<META http-equiv=\"refresh\" content=\"0;URL=currency.php\">";

?>