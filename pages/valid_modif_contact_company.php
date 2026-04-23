<?php
//valid_modif_contact_company.php
session_start();
include_once "conf.php";
include_once "page_titles.php";
require('../classes/company.class.php');

$objet = new company();
$donnee = $objet->valid_modif_contact_company();

// ✅ Redirection vers la fiche de la compagnie après modification
$id_company = intval($_POST['Fld_Company_ID']);
header("Location: detailcompany.php?id=$id_company");
exit;
?>
