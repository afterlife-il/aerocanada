<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// SERVEUR SQL
$sql_serveur = "localhost:3306";

// LOGIN SQL
$sql_user = "aerocanada-indus";

// MOT DE PASSE SQL
$sql_passwd = "F66x%jo4!";

// NOM DE LA BASE DE DONNEES
$sql_bdd = "aerocanada";

// REDIRECTION VERS UNE PAGE ERREUR AU CAS OU LE LOGIN ET MOT DE PASSE SONT INVALIDES
$url_erreur = "erreur.htm";

// CONNEXION MYSQL
$db_link = @mysqli_connect($sql_serveur, $sql_user, $sql_passwd, $sql_bdd);
$conn = $db_link;

if (!$db_link) {
    echo "Connexion impossible à la base de données <b>$sql_bdd</b> sur le serveur <b>$sql_serveur</b><br>Vérifiez les paramètres du fichier conf.php";
    exit;
}
mysqli_set_charset($db_link, "utf8mb4");

// Fonction pour exécuter une requête SQL et gérer les erreurs
function mysql2_query($sql) {
    global $db_link;

    // Vérification que la requête n'est pas vide
    if (empty($sql)) {
        die("Erreur : La requête SQL est vide.");
    }

    // Afficher la requête pour déboguer - désactiver en production
    echo "Requête SQL : " . $sql . "<br>"; // DEBUGGING ONLY

    $result = mysqli_query($db_link, $sql);

    if (!$result) {
        // Afficher l'erreur SQL et la requête qui a échoué
        die("Erreur SQL : " . mysqli_error($db_link) . "<br>Requête : " . $sql);
    }

    return $result;
}

// Fonction pour obtenir l'ID de la dernière insertion
function mysql2_insert_id() {
    global $db_link;
    return mysqli_insert_id($db_link);
}

// Fonction temporaire pour format monétaire
function money_format($fmt, $str) {
    return $str;
}

?>
