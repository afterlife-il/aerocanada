<?php
// add_pn_session_newsletter.php (version robuste, même contrat et même comportement)
session_start();
include_once "conf.php";
include_once "page_titles.php";

// Optionnel mais recommandé: refuser si non connecté (sans rien renvoyer)
if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
  // ne rien écho pour ne rien casser côté appelant
  exit;
}

// Rien à faire si pas d'ID
if (empty($_GET['Fld_Part_ID'])) {
  exit;
}

// ----- Parsing robuste de Fld_Part_ID (accepte "Nom,123" OU "123") -----
$raw = trim($_GET['Fld_Part_ID']);
$pos = strrpos($raw, ',');
$tail = ($pos !== false) ? trim(substr($raw, $pos + 1)) : $raw;
$partId = null;
if ($tail !== '' && ctype_digit($tail)) {
  $partId = (int)$tail;
}
if ($partId === null) {
  // format invalide -> on ne casse rien, on sort silencieusement
  exit;
}

// ----- Récupération/MAJ du compteur -----
$count = isset($_SESSION['countpnsessionnews']) ? (int)$_SESSION['countpnsessionnews'] : 0;
$count++;
$_SESSION['countpnsessionnews'] = $count;

// ----- Valeurs associées (on tolère l'absence) -----
$qty  = isset($_GET['Fld_Qty_RFQ']) ? (int)$_GET['Fld_Qty_RFQ'] : null;
$cond = isset($_GET['Fld_Condition_ID']) ? (int)$_GET['Fld_Condition_ID'] : null;

// ----- Stockage en session (mêmes clés que ton code d’origine) -----
$_SESSION['pnusedsessionnews' . $count] = $partId;
$_SESSION['pnqtysessionnews'  . $count] = $qty;
$_SESSION['pncondsessionnews' . $count] = $cond;

// Pas d'output, pour garder le contrat
