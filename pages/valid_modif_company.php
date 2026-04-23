<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

// (Optionnel mais conseillé) contrôle d'accès
if (empty($_SESSION['conectroy']) || $_SESSION['conectroy'] !== 'parfait') {
    header('Location: login.php?url=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Exécuter l'update (IMPORTANT: pas de echo dans cette méthode)
require('../classes/company.class.php');
$objet = new company();
$ok = $objet->modif_company(); // doit retourner un bool/ok, sans output

$companyId = isset($_POST['Fld_Company_ID']) ? (int)$_POST['Fld_Company_ID'] : 0;

// Cible selon le bouton
$target = (isset($_POST['save_and_return']) && $_POST['save_and_return'] == '1')
    ? "company.php?Fld_Company_ID={$companyId}"
    : "modif_company.php?Fld_Company_ID={$companyId}&saved=1";

// Redirection HTTP si possible
if (!headers_sent()) {
    header("Location: {$target}");
    exit;
}

// Fallback JS si des sorties ont déjà été envoyées
echo '<script>location.href=' . json_encode($target) . ';</script>';
exit;
