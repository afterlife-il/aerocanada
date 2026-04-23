<?php
session_start();
		include_once "conf.php";
if (!isset($_SESSION["conectroy"]) || $_SESSION["conectroy"] !== "parfait") { header("Location: login.php"); exit; }
include_once "page_titles.php";
		require('../classes/rfq.class.php');
		$objet=new rfq();
		$donnee = $objet->del_rfq($_GET['idsup']);

?>