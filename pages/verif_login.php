<?php
session_start();

//if (!isset($_SESSION['user_id'])) {
//    $_SESSION['user_id'] = $user['id'];  // Vérifie bien que cette ligne existe
//}
file_put_contents("/tmp/session_login_test.txt", json_encode($_SESSION) . "\n", FILE_APPEND);

$_SESSION['user_id'] = $user['id']; // Vérifie que cette ligne est bien exécutée
echo json_encode(["message" => "Connexion réussie", "session_data" => $_SESSION]);

include_once "conf.php";
include_once "page_titles.php";
// include_once "confphp7.php";
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

        if (($email_form == $email_bdd) && ($pw_form == $pw_bdd)) {
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
                "message" => "Connexion réussie",
                "session_id" => session_id(),
                "session_data" => $_SESSION
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
