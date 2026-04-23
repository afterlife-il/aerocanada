<?php
require_once 'bootstrap.php';
require_auth();

require('../classes/parts.class.php');

try {
    $objet = new parts();
    $donnee = $objet->add_part();
    
    log_error("Part added successfully by " . $_SESSION['nom_utilisateur']);
    
    safe_redirect('parts.php');
    
} catch (Exception $e) {
    log_error("Error adding part: " . $e->getMessage(), [
        'user' => $_SESSION['nom_utilisateur'],
        'post_data' => $_POST
    ]);
    
    safe_redirect('ajout_parts.php?error=1&msg=' . urlencode($e->getMessage()));
}
?>