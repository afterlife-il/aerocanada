<?php
// Version HYBRIDE avec logs détaillés
session_start();
include_once "conf.php";

require('../classes/stock.class.php');

// Log TRÈS détaillé pour comprendre
if (function_exists('log_error')) {
    log_error("TRACE stock START", [
        'user' => $_SESSION['nom_utilisateur'] ?? 'unknown',
        'post_keys' => array_keys($_POST),
        'post_data' => $_POST // ATTENTION : peut contenir données sensibles
    ]);
}

// Appel méthode
$objet = new stock();

try {
    $donnee = $objet->add_stock();
    
    // Log succès
    if (function_exists('log_error')) {
        log_error("TRACE stock SUCCESS", [
            'user' => $_SESSION['nom_utilisateur'] ?? 'unknown',
            'result' => $donnee
        ]);
    }
    
} catch (Exception $e) {
    // Log erreur
    if (function_exists('log_error')) {
        log_error("TRACE stock ERROR: " . $e->getMessage(), [
            'user' => $_SESSION['nom_utilisateur'] ?? 'unknown',
            'trace' => $e->getTraceAsString()
        ]);
    }
}

// Redirection
if (function_exists('safe_redirect')) {
    safe_redirect('stock.php');
} else {
    echo "<META http-equiv=\"refresh\" content=\"0;URL=stock.php\">";
}
?>