<?php
session_start();
include_once "conf.php";
if (!isset($_SESSION["conectroy"]) || $_SESSION["conectroy"] !== "parfait") { header("Location: login.php"); exit; }
include_once "page_titles.php";
require('../classes/users.class.php');

$objet=new users();
$donnee = $objet->del_user($_GET['idsup']);

echo "<META http-equiv=\"refresh\" content=\"0;URL=users.php\">";

?>