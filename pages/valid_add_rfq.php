<?php
// valid_add_rfq.php
session_start();
include_once "conf.php";
include_once "page_titles.php";

if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] != "parfait") {
    echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=" . $_SERVER['REQUEST_URI'] . "\">";
    exit;
}

require('../classes/rfq.class.php');

// 1) Création / enregistrement de la RFQ via la classe
$objet  = new rfq();
$donnee = $objet->add_rfq();

// 2) On essaie de déterminer le PART ID lié à cette RFQ
$part_id = 0;

// a) Cas classique : on vient d'une fiche PN avec un hidden
if (!empty($_POST['Fld_Part_ID_hidden'])) {
    $part_id = (int) $_POST['Fld_Part_ID_hidden'];
} elseif (!empty($_POST['part_id'])) {
    $part_id = (int) $_POST['part_id'];
} elseif (!empty($_POST['Fld_Part_ID'])) {
    $part_id = (int) $_POST['Fld_Part_ID'];
}

// b) On récupère aussi le RFQ ID utilisé
$rfq_id = '';
if (!empty($_POST['Fld_RFQ_ID'])) {
    $rfq_id = trim($_POST['Fld_RFQ_ID']);
} elseif (!empty($_POST['RFQ_ID'])) {
    $rfq_id = trim($_POST['RFQ_ID']);
}

// c) Si on n'a toujours pas de part_id, on le cherche dans tbl_RFQ_1 via le RFQ ID
if ($part_id <= 0 && $rfq_id !== '') {
    $sql_rfq_part = "
        SELECT Fld_Part_ID 
        FROM tbl_RFQ_1 
        WHERE Fld_RFQ_ID = '" . $rfq_id . "'
        ORDER BY ID DESC
        LIMIT 1
    ";
    $req_rfq_part = mysql2_query($sql_rfq_part);
    if ($req_rfq_part && $row_rfq_part = mysqli_fetch_array($req_rfq_part)) {
        $part_id = (int) $row_rfq_part['Fld_Part_ID'];
    }
}

// d) En dernier recours, on tente de retrouver le PART ID via le PN
if ($part_id <= 0 && !empty($_POST['Fld_Part_Nbr'])) {
    $pn = trim($_POST['Fld_Part_Nbr']);
    $sql_pn = "
        SELECT Fld_Part_ID 
        FROM tbl_Parts 
        WHERE Fld_Part_Nbr = '" . $pn . "'
        ORDER BY Fld_Part_ID DESC
        LIMIT 1
    ";
    $req_pn = mysql2_query($sql_pn);
    if ($req_pn && $row_pn = mysqli_fetch_array($req_pn)) {
        $part_id = (int) $row_pn['Fld_Part_ID'];
    }
}

// Si malgré tout on n'a pas de PART ID, on évite la redirection cassée
if ($part_id <= 0) {
    // Sécurité : on te renvoie sur la liste des parts plutôt que sur Part-Nbr.php?part_id=0
    echo "Erreur : PART ID introuvable après création de la RFQ.";
    exit;
}

// 3) Enregistrement de l'activité (maakav) comme avant
$today = date("Y-m-d");
$heure = date("H:i:s");

mysql2_query("
    INSERT INTO tbl_maakav_pn (id_maakav_pn, id_part, datepn, heurevisitepn, id_Employee)
    VALUES (NULL, '" . $part_id . "', '" . $today . "', '" . $heure . "', '" . (int) $_SESSION['id_utilisateur'] . "')
");

// 4) Redirection vers la fiche PART correspondante
//    On garde le RFQ_ID dans l'URL si on l'a, pour continuer facilement sur la même RFQ
$url = "Part-Nbr.php?part_id=" . $part_id;
if ($rfq_id !== '') {
    $url .= "&RFQ_ID=" . urlencode($rfq_id);
}

header("Location: " . $url);
exit;
?>
