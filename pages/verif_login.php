<?php
session_start();

include_once "conf.php";
include_once "page_titles.php";
require('../classes/users.class.php');

if ((!empty($_POST['email_form'])) && (!empty($_POST['pw_form']))) {
    $email_form = htmlentities($_POST['email_form']);
    $pw_form = htmlentities($_POST['pw_form']);

    $objet = new users();
    $donnee = $objet->verif_login($email_form, $pw_form);
    
    if (!empty($donnee)) {
        foreach ($donnee as $reponse) {
            $Employee_ID_bdd = $reponse['Employee_ID'];
            $Employee_Name_bdd = $reponse['Employee_Name'];
            $email_bdd = $reponse['email'];
            $pw_bdd = $reponse['pw'];
            $statut_bdd = $reponse['statut'];
        }

        $pw_match = false;
        if (substr($pw_bdd, 0, 4) === '$2y$' || substr($pw_bdd, 0, 4) === '$2a$') {
            $pw_match = password_verify($pw_form, $pw_bdd);
        } else {
            $pw_match = ($pw_form === $pw_bdd);
        }

        if (($email_form == $email_bdd) && $pw_match) {
            $_SESSION['conectroy'] = "parfait";
            $_SESSION['nom_utilisateur'] = $Employee_Name_bdd;
            $_SESSION['id_utilisateur'] = $Employee_ID_bdd;
            $_SESSION['user_id'] = $_SESSION['id_utilisateur']; // ✅ Assure la cohérence
            $_SESSION['statut'] = $statut_bdd;
            $_SESSION['leftmenu'] = "open";

            // Récupération de l'adresse IP
            if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip_address = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip_address = $_SERVER['REMOTE_ADDR'];
            }
            $_SESSION['ipconn'] = $ip_address;

            // Enregistrement connexion
            $objet->connection_user($Employee_ID_bdd, $ip_address, $sessionid);

    		// ✅ Forcer PHP à sauvegarder la session immédiatement
            session_write_close();				
			
            // ✅ Vérification après connexion
            echo json_encode([
                "message" => "Connexion réussie"
            ]);
            exit;
        }
    }

    // ❌ Échec de connexion
    $_SESSION['conectroy'] = "";
    echo json_encode(["error" => "Identifiants incorrects"]);
    exit;
}

// ❌ Aucun email/mot de passe envoyé
echo json_encode(["error" => "Email ou mot de passe manquant"]);
exit;
?>
