<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
require('../classes/company.class.php');

$objet = new company();
$donnee = $objet->modif_info_general_company();

// Récupérer le numéro de page envoyé par le formulaire
$current_page = isset($_POST['current_page']) ? (int)$_POST['current_page'] : 1;

// Rediriger vers la bonne page
header("Location: company.php?companyrating=all&page=$current_page&Fld_Company_ID=".$_POST['Fld_Company_ID']);
exit;
?>
