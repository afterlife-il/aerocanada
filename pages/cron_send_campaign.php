<?php
// cron_send_campaign.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once "conf.php"; // connexion DB + PHPMailer config

// Nom de la campagne
$campaign_name = 'teardown_campaign_nov2025';

// Détection connexion DB
$pdo = null;
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
    echo "Erreur : aucune connexion DB.";
    exit;
}

// Nombre d’emails par exécution
$limit = 10;

// ==============================
// FONCTION D’ENVOI PAR PHPMailer
// ==============================
function send_email_campaign($to, $subject, $html) {
    // Reprend EXACTEMENT la config d’envoi de tes fichiers RFQ
    require_once "phpmailer/PHPMailerAutoload.php";

    $mail = new PHPMailer(true);

    try {
        // SMTP (comme dans validation-send-email-rfq.php)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'yohan@aerocanada.aero'; 
        $mail->Password = 'dpcuaertsbpmhfdy'; // ⚠️ mot de passe d’application Gmail
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('sales@aerocanada-industries.com', 'AeroCanada Industries 770 Inc.');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html;

        return $mail->send();
    }
    catch (Exception $e) {
        return false;
    }
}

// ==============================
// TEXTE DE LA CAMPAGNE EMAIL
// ==============================
function build_email_body($first_name) {

    // Si pas de prénom → Dear Sir or Madam
    if (!$first_name || trim($first_name) == "") {
        $dear = "Dear Sir or Madam,";
    } else {
        $dear = "Dear " . htmlspecialchars($first_name) . ",";
    }

    // Signature pro (à personnaliser si tu veux)
    $signature = '
<br><br>
Best Regards,<br>
---<br><br>

<table cellspacing="0" cellpadding="0" border="0" style="font-family:Arial, sans-serif; font-size:14px;">
    <tr>
        <td valign="top" style="padding-right:12px;">
            <img src="https://ci3.googleusercontent.com/mail-sig/AIorK4zjXKBjL4yZ8C3wf1YiSuNj-WBLa9bBZp2PNM6k7z6lQYWD-zmSnwZ-TIbB8pcUMoB0KJkT2htwN1mf" 
                 width="95" 
                 height="95" 
                 alt="AeroCanada Logo" 
                 style="display:block;">
        </td>

        <td valign="top" style="font-family:Arial, sans-serif; font-size:14px; color:#000;">
            <strong>Yohan | AeroCanada Team</strong><br>
            Business Development Manager<br><br>

            Your Perfect Choice For Aviation Solutions<br><br>

            <strong>v. dir.</strong> +33 1 84 16 07 49 | 
            <strong>mob.</strong> +33 6 52 54 36 80<br>

            📧 <a href="mailto:yohan@aerocanada.aero">yohan@aerocanada.aero</a><br>
            📧 <a href="mailto:repairs@aerocanada.aero">repairs@aerocanada.aero</a><br>
            📧 <a href="mailto:rfq@aerocanada.aero">rfq@aerocanada.aero</a><br><br>

            📞 Teams : <a href="mailto:Team@AeroCanadaSales">AeroCanadaSales</a><br><br>

            AeroCanada Industries 770 Inc.<br>
            100, Alexis-Nihon Boulevard | 9th Floor, Suite 971<br>
            Montreal, QC, H4M 2P5 🇨🇦<br><br>

            FAA AC00-56B | UNGM 256670 | NATO CAGE L06T4<br>
            <a href="https://www.aerocanada.aero/conditions-generales">Conditions Générales Vente</a> |
            <a href="https://www.aerocanada.aero/terms-of-sale">Terms of Sale</a>
        </td>
    </tr>
</table>
';

    return "
        <html><body style='font-family:Arial;font-size:14px;color:#333;'>

        <p>$dear</p>

        <p>
        I am reaching out on behalf of <strong>AeroCanada Industries 770 Inc.</strong>, 
        a Canadian company founded in 2010 and specialized in the supply, management, and repair of 
        aircraft components for regional and commercial platforms.
        </p>

        <p>
        Over the past year, in partnership with <strong>ClearSky</strong>, we have dismantled 
        around ten complete aircraft — including Airbus A320 family, Boeing B737-700/-800, 
        Embraer E170/E190, CASA CN235, and Bombardier CRJ900.
        These operations allow us to provide <strong>any interior or structural component</strong> 
        with high-resolution photos, full traceability, and complete technical documentation.
        </p>

        <p>
        In parallel, AeroCanada manages component repair services through a network of partner stations 
        in France (Domusa Group), covering:
        <strong>ACMs, heat exchangers, environmental & pneumatic systems, avionics, radar, APU, hydraulic 
        and electromechanical components.</strong>  
        We offer reliable turnaround times and highly competitive pricing — your one-stop shop solution.
        </p>

        <p>
        If you are currently sourcing components or planning maintenance activities, 
        I would be pleased to provide a quotation or availability.
        </p>

        $signature

        <br><br>
        <hr>
        <small>If you are not the correct contact or wish to unsubscribe, please reply to this email.</small>

        </body></html>

    ";
}

// ==============================
// 1. RÉCUPÉRER 10 EMAILS EN ATTENTE
// ==============================
if ($mysqli) {
    $mysqli->set_charset("utf8mb4");

    $sql = "
        SELECT id, email, first_name
        FROM tbl_email_campaign_queue
        WHERE status = 'pending'
        AND campaign_name = ?
        ORDER BY id ASC
        LIMIT ?
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("si", $campaign_name, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
}
else {
    $stmt = $pdo->prepare("
        SELECT id, email, first_name
        FROM tbl_email_campaign_queue
        WHERE status = 'pending'
        AND campaign_name = :c
        ORDER BY id ASC
        LIMIT $limit
    ");
    $stmt->execute([':c' => $campaign_name]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ==============================
// 2. ENVOYER CHAQUE EMAIL
// ==============================
$count = 0;

foreach ($result as $row) {

    $id        = $row['id'];
    $email     = $row['email'];
    $firstname = $row['first_name'];

    $subject = "Aircraft Parts Support - AeroCanada Industries 770 Inc.";
    $body    = build_email_body($firstname);

    $sent = send_email_campaign($email, $subject, $body);

    if ($mysqli) {
        if ($sent) {
            $mysqli->query("UPDATE tbl_email_campaign_queue SET status='sent', sent_at=NOW() WHERE id=$id");
        } else {
            $mysqli->query("UPDATE tbl_email_campaign_queue SET status='error', last_error='SMTP error' WHERE id=$id");
        }
    }
    else {
        if ($sent) {
            $pdo->query("UPDATE tbl_email_campaign_queue SET status='sent', sent_at=NOW() WHERE id=$id");
        } else {
            $pdo->query("UPDATE tbl_email_campaign_queue SET status='error', last_error='SMTP error' WHERE id=$id");
        }
    }

    $count++;
}

echo "Cron terminé : $count emails traités.";
