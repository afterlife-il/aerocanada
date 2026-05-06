<?php
session_start();
ob_start();
include_once "conf.php";
include_once "page_titles.php";

if (empty($_SESSION['conectroy']) || $_SESSION['conectroy'] !== 'parfait') {
    header('Location: login.php');
    exit;
}

require('../classes/company.class.php');
$objet = new company();
$donnee = $objet->ajout_company();

ob_end_clean();
header('Location: company.php?companyrating=all');
exit;
?>
