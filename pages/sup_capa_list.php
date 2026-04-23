<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
    header("Location: login.php?url=capa-list.php");
    exit;
}

if (isset($_GET['ID'])) {
    $id = intval($_GET['ID']);
    if ($id > 0) {
        // Suppression de la ligne CAPA
        $sql = "DELETE FROM tbl_capa_list WHERE id_capa_list = " . $id . " LIMIT 1";
        mysqli_query($db_link, $sql);
    }
}

// Retour à la liste CAPA
header("Location: capa-list.php");
exit;
?>
