<?php
session_start();

// Bascule entre open / closed
if (!isset($_SESSION['leftmenu']) || $_SESSION['leftmenu'] !== 'open') {
    $_SESSION['leftmenu'] = 'open';
} else {
    $_SESSION['leftmenu'] = 'closed';
}

// Redirige vers la page précédente (sécurisé)
if (isset($_GET['REQUEST_URI'])) {
    $url = filter_var($_GET['REQUEST_URI'], FILTER_SANITIZE_URL);
    header("Location: $url");
    exit;
} else {
    // Redirige par défaut vers la page d’accueil
    header("Location: index.php");
    exit;
}
?>
