<?php
// prepare_campaign_queue.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include_once "conf.php"; // connexion DB uniquement

// Sécuriser l'accès
if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] != "parfait") {
    echo "Access denied (no valid session).";
    exit;
}

// Nom de la campagne
$campaign_name = 'teardown_campaign_nov2025';

// =============================
// 1. DÉTECTION DE LA CONNEXION
// =============================
$pdo    = null;
$mysqli = null;

$candidates = ['bdd','DB_con','cnx','conn','connection','link','mysqli','db'];

foreach ($candidates as $name) {
    if (!isset($$name)) continue;
    $var = $$name;

    if ($var instanceof PDO) {
        $pdo = $var;
        break;
    }
    if ($var instanceof mysqli) {
        $mysqli = $var;
        break;
    }
}

if (!$pdo && !$mysqli) {
    echo "Erreur : aucune connexion DB trouvée.";
    exit;
}

// ========================================
// 2. LOGIQUE AVEC PDO OU MYSQLI
// ========================================

try {

    // ---------------- PDO ----------------
    if ($pdo) {

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Supprimer l'ancienne campagne
        $pdo->prepare("DELETE FROM tbl_email_campaign_queue WHERE campaign_name = :c")
            ->execute([':c' => $campaign_name]);

        // SELECT contacts (on utilise Fld_Contact_Name)
        $sqlContacts = "
            SELECT 
                id_company_contact,
                Fld_Contact_Name AS first_name,
                Fld_Contact_Email
            FROM tb_company_contact
            WHERE 
                Fld_Contact_Email IS NOT NULL
                AND Fld_Contact_Email <> ''
        ";
        $stmtContacts = $pdo->query($sqlContacts);

        // INSERT dans la queue
        $stmtInsert = $pdo->prepare("
            INSERT INTO tbl_email_campaign_queue (contact_id, email, first_name, campaign_name)
            VALUES (:id, :email, :fn, :camp)
        ");

        $count = 0;

        while ($r = $stmtContacts->fetch(PDO::FETCH_ASSOC)) {

            $email = trim($r['Fld_Contact_Email']);
            if ($email === '') continue;

            $stmtInsert->execute([
                ':id'   => $r['id_company_contact'],
                ':email'=> $email,
                ':fn'   => $r['first_name'],
                ':camp' => $campaign_name
            ]);

            $count++;
        }

        echo "Queue prête (PDO) : $count contacts ajoutés.";

    }

    // ---------------- MYSQLI ----------------
    else if ($mysqli) {

        if (method_exists($mysqli, 'set_charset')) {
            $mysqli->set_charset('utf8mb4');
        }

        // DELETE
        $stmtDelete = $mysqli->prepare("DELETE FROM tbl_email_campaign_queue WHERE campaign_name = ?");
        $stmtDelete->bind_param("s", $campaign_name);
        $stmtDelete->execute();
        $stmtDelete->close();

        // SELECT contacts (Fld_Contact_Name)
        $sqlContacts = "
            SELECT 
                id_company_contact,
                Fld_Contact_Name AS first_name,
                Fld_Contact_Email
            FROM tb_company_contact
            WHERE 
                Fld_Contact_Email IS NOT NULL
                AND Fld_Contact_Email <> ''
        ";
        $result = $mysqli->query($sqlContacts);

        if (!$result) {
            throw new Exception("Erreur SELECT : " . $mysqli->error);
        }

        // INSERT
        $stmtInsert = $mysqli->prepare("
            INSERT INTO tbl_email_campaign_queue (contact_id, email, first_name, campaign_name)
            VALUES (?, ?, ?, ?)
        ");

        $count = 0;

        while ($r = $result->fetch_assoc()) {

            $email = trim($r['Fld_Contact_Email']);
            if ($email === '') continue;

            $contact_id = $r['id_company_contact'];
            $first_name = $r['first_name'];

            $stmtInsert->bind_param(
                "isss",
                $contact_id,
                $email,
                $first_name,
                $campaign_name
            );

            $stmtInsert->execute();
            $count++;
        }

        $stmtInsert->close();
        $result->free();

        echo "Queue prête (mysqli) : $count contacts ajoutés.";
    }

}
catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
