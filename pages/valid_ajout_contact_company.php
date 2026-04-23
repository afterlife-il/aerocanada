<?php
session_start();
include_once "conf.php";

require('../classes/company.class.php');

// Log avant
if (function_exists('log_error')) {
    log_error("TRACE before ajout_contact_company_unique()", [
        'user' => $_SESSION['nom_utilisateur'] ?? 'unknown',
        'post_data' => $_POST
    ]);
}

// UTILISER LA MÉTHODE _unique au lieu de la méthode normale
$objet = new company();
$donnee = $objet->ajout_contact_company_unique();

// Log après
if (function_exists('log_error')) {
    log_error("TRACE after ajout_contact_company_unique()", [
        'user' => $_SESSION['nom_utilisateur'] ?? 'unknown'
    ]);
}

// Redirection
if (function_exists('safe_redirect')) {
    $company_id = $_POST['Fld_Company_ID'] ?? '';
    if (!empty($company_id)) {
        safe_redirect("detailcompany.php?Fld_Company_ID=$company_id");
    } else {
        safe_redirect('company_contact.php');
    }
} else {
    $company_id = $_POST['Fld_Company_ID'] ?? '';
    if (!empty($company_id)) {
        echo "<META http-equiv=\"refresh\" content=\"0;URL=detailcompany.php?Fld_Company_ID=$company_id\">";
    } else {
        echo "<META http-equiv=\"refresh\" content=\"0;URL=company_contact.php\">";
    }
}
?>