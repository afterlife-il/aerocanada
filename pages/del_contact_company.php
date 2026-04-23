<?php
//del_contact_company.php
//👉 sert à archiver / désarchiver (changer la colonne status entre Available et none).
session_start();
include_once "conf.php";

if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] != "parfait") {
    header("Location: index.php");
    exit;
}

// Récupération des paramètres
$id_contact = isset($_GET['id_company_contact']) ? (int) $_GET['id_company_contact'] : 0;
$id_company = isset($_GET['Fld_Company_ID']) ? (int) $_GET['Fld_Company_ID'] : 0;
$page       = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$mode       = isset($_GET['mode']) ? $_GET['mode'] : 'archive';

// mode = archive  -> status = 'none'
// mode = restore  -> status = 'Available'
$status = ($mode == 'restore') ? "Available" : "none";

// On met à jour le statut
if ($id_contact > 0) {
    $sql = "UPDATE tb_company_contact 
            SET status = '".$status."' 
            WHERE id_company_contact = ".$id_contact." 
            LIMIT 1";
    mysql2_query($sql);
}

// Si on vient d'ARCHIVER -> ouvrir onglet CONTACT ARCHIVED
// Si on vient de RESTAURER -> ouvrir onglet CONTACT (bloc principal)
$anchor = ($status == 'none') ? "#collapseFour" : "#bloccompany";

$location = "company.php?companyrating=all&page=".$page.
            "&Fld_Company_ID=".$id_company.$anchor;

header("Location: " . $location);
exit;
