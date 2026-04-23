<?php
// delete_contact_company.php
// 👉 efface définitivement un contact

session_start();
include_once "conf.php"; // même dossier que company.php

// Sécurité : vérifier la session
if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
    header("Location: index.php");
    exit;
}

// Récupération des paramètres
$id_contact = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_company = isset($_GET['Fld_Company_ID']) ? (int)$_GET['Fld_Company_ID'] : 0;
$page       = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Si paramètres invalides → retour à la liste
if ($id_contact <= 0 || $id_company <= 0) {
    header("Location: company.php?companyrating=all");
    exit;
}

// Suppression définitive
$sql = "DELETE FROM tb_company_contact
        WHERE id_company_contact = ".$id_contact."
        LIMIT 1";
mysql2_query($sql);

// Retour sur la fiche compagnie, onglet CONTACT ARCHIVED
header(
    "Location: company.php?companyrating=all"
    . "&page=" . $page
    . "&Fld_Company_ID=" . $id_company
    . "#collapseFour"
);
exit;
