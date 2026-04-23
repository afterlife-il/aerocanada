<?php
session_start();
include_once "conf.php";
if (!isset($_SESSION["conectroy"]) || $_SESSION["conectroy"] !== "parfait") { header("Location: login.php"); exit; }
include_once "page_titles.php";
require('../classes/parts.class.php');
$objet=new parts();
$donnee = $objet->del_part($_GET['part_id']);

echo "<META http-equiv=\"refresh\" content=\"0;URL=parts.php\">";

?>