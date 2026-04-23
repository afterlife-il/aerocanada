<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

// Optionnel: limiter à certains rôles
// if (!isset($_SESSION['statut']) || $_SESSION['statut'] !== 'SuperAdmin') { die('Not allowed'); }

if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
  header("Location: login.php"); exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { die('Invalid ID'); }

// Sécurité: LIMIT 1, et on cible bien la table des SQ
$sql = "DELETE FROM tbl_RFQ_2 WHERE ID = $id LIMIT 1";
if (!mysql2_query($sql)) {
  die('DB error while deleting');
}

// Retour à la liste
header("Location: suppliers_quote.php?deleted=1");
exit;
