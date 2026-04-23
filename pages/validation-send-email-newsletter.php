<?php
// validation-send-email-newsletter.php

session_start();
include_once "conf.php";
include_once "page_titles.php";

if ($_SESSION['conectroy'] == "parfait") {

    /***********************************************************************************/
    /* Génération de l'email newsletter pour client                                    */
    /***********************************************************************************/

    // Tableau pour debug interne (si besoin)
    $debug_msgs = [];

    // ======================================================================
    // 1. CONSTRUCTION DU HTML DE LA NEWSLETTER
    // ======================================================================

    $message_html = '<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head><body>
<div style="font-family:sans-serif;color:#000000;font-size:16px;margin:0px;padding:20px;" align="center">
<table border="0" cellspacing="0" cellpadding="0" align="center">
    <tbody>
    <tr>
        <td nowrap="" style="border:1px solid #2a2a2a;color:#ffffff;padding:24px" width="100px">
            <table width="100%">
                <tbody>
                <tr>
                    <td align="center" nowrap="">
                        <img src="http://aerocanada-industries.com/pages/images/logo-aerocanada.png" alt="Aerocanada">
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td align="center" style="background:#ffffff;color:#000000;border-top:10px solid #BD0831;border-bottom:10px solid #000000" height="200px">
            <table width="100%" cellspacing="0" style="background:white;color:black;border-collapse:collapse">
                <tbody>
                <tr>
                    <td nowrap="" align="center" style="border:1px solid #acacac;padding:3px;font-family:sans-serif;font-weight:bold;font-size:16px;padding-left:8px;padding-right:8px">
                        Part Number
                    </td>
                    <td nowrap="" align="center" style="border:1px solid #acacac;padding:3px;font-family:sans-serif;font-weight:bold;font-size:16px;padding-left:8px;padding-right:8px">
                        Description
                    </td>
                    <td nowrap="" align="center" style="border:1px solid #acacac;padding:3px;font-family:sans-serif;font-weight:bold;font-size:16px;padding-left:8px;padding-right:8px">
                        Qty
                    </td>
                    <td nowrap="" align="center" style="border:1px solid #acacac;padding:3px;font-family:sans-serif;font-weight:bold;font-size:16px;padding-left:8px;padding-right:8px">
                        Cond
                    </td>
                </tr>';

    // Boucle sur les PNs en session
    for ($i = 1; $i <= $_SESSION['countpnsessionnews']; $i++) {

        // Récupération info PN
        $sql  = "SELECT * FROM tbl_Parts WHERE Fld_Part_ID='" . mysqli_real_escape_string($conn, $_SESSION['pnusedsessionnews' . $i]) . "'";
        $req  = mysql2_query($sql);
        $data = mysqli_fetch_array($req);

        // Récupération CONDITION
        $sql2  = "SELECT * FROM tbl_Condition WHERE Fld_Condition_ID='" . mysqli_real_escape_string($conn, $_SESSION['pncondsessionnews' . $i]) . "'";
        $req2  = mysql2_query($sql2);
        $data2 = mysqli_fetch_array($req2);

        $message_html .= '
                <tr>
                    <td nowrap="" align="center" style="border:1px solid #acacac;padding:3px;font-family:sans-serif;font-size:15px;padding-left:8px;padding-right:8px;font-weight:bold;padding-top:12px;padding-bottom:12px">'
                        . htmlspecialchars($data['Fld_Part_Nbr']) .
                    '</td>
                    <td nowrap="" align="center" style="border:1px solid #acacac;padding:3px;font-family:sans-serif;font-size:15px;padding-left:8px;padding-right:8px">'
                        . htmlspecialchars($data['Fld_Part_Desc']) .
                    '</td>
                    <td nowrap="" align="center" style="border:1px solid #acacac;padding:3px;font-family:sans-serif;font-size:15px;padding-left:8px;padding-right:8px">'
                        . htmlspecialchars($_SESSION['pnqtysessionnews' . $i]) .
                    '</td>
                    <td nowrap="" align="center" style="border:1px solid #acacac;padding:3px;font-family:sans-serif;font-size:15px;padding-left:8px;padding-right:8px">'
                        . htmlspecialchars($data2['Fld_Condition_Text']) .
                    '</td>
                </tr>';
    }

    // Récupération employé ACI 770 (signature)
    // tbl_Employee : Employee_ID, Employee_Name, Fld_Contact_Id, pw, email, statut, position, tel, mobile, skype, numformat, pwgmaero
    $sqleaci  = "SELECT * FROM tbl_Employee WHERE Employee_ID='" . (int)$_SESSION['id_utilisateur'] . "'";
    $reqeaci  = mysql2_query($sqleaci);
    $dataeaci = mysqli_fetch_array($reqeaci);

    if (!$dataeaci) {
        $dataeaci = [
            'Employee_Name' => '',
            'position'      => '',
            'tel'           => '',
            'mobile'        => '',
            'email'         => '',
        ];
    }

    $message_html .= '
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td style="background:#BD0831;padding:0px" height="40">&nbsp;</td>
    </tr>
    <tr>
        <td align="center"><br>
            <table border="0">
                <tbody>
                <tr>
                    <td>
                        <div align="right" style="font-size:12.5px;color:#5a5a5a;font-family:Arial,sans-serif;margin:7px">
                            <div align="right" style="color:black;font-size:14px;font-weight:bold">'
                                . htmlspecialchars($dataeaci['Employee_Name']) .
                            '</div>
                            <div style="margin-top:4px">
                                <span style="font-family:Arial,sans-serif;font-size:9pt">'
                                    . htmlspecialchars($dataeaci['position']) .
                                    ' | AeroCanada Industries 770 Inc.</span><br>
                                Direct: ' . htmlspecialchars($dataeaci['tel']) . ' | mob.&nbsp;' . htmlspecialchars($dataeaci['mobile']) . '
                            </div>
                            <a href="http://www.aerocanada.aero" style="color:#5a5a5a" target="_blank">http://www.aerocanada.aero</a><br>
                            <a href="mailto:' . htmlspecialchars($dataeaci['email']) . '" style="color:#5a5a5a" target="_blank">'
                                . htmlspecialchars($dataeaci['email']) .
                            '</a>
                        </div>
                    </td>
                    <td width="1" style="border-right:1px solid black">&nbsp;</td>
                    <td align="left">
                        <div style="margin:7px;width:340px;height:68px;text-align:center;" width="340" height="68">
                            <img src="http://aerocanada-industries.com/pages/images/logo-aerocanada.png" height="68">
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
</div>
</body></html>';

    // ======================================================================
    // 2. SUJET DE LA NEWSLETTER
    // ======================================================================
    $sujet = isset($_POST['sujet']) && trim($_POST['sujet']) !== ''
        ? trim($_POST['sujet'])
        : 'AeroCanada Newsletter';

    // ======================================================================
    // 3. RECUP INFO COMPTE GMAIL EMPLOYÉ
    // ======================================================================
    require('../classes/users.class.php');

    $objet  = new users();
    $donnee = $objet->display_employee($_SESSION['id_utilisateur']);

    $Employee_Name = '';
    $emailemplo    = '';
    $pwgmaero      = '';

    foreach ($donnee as $dataemp) {
        $Employee_Name = $dataemp['Employee_Name'];
        $emailemplo    = $dataemp['email'];
        $pwgmaero      = $dataemp['pwgmaero'];
    }

    if ($emailemplo == '' || $pwgmaero == '') {
        $debug_msgs[] = "Attention : email ou mot de passe Gmail (pwgmaero) de l'employé non défini.";
    }

    // ======================================================================
    // 4. CHARGEMENT PHPMailer 6.x
    // ======================================================================
    require_once __DIR__ . '/phpmailer6/PHPMailer.php';
    require_once __DIR__ . '/phpmailer6/SMTP.php';
    require_once __DIR__ . '/phpmailer6/Exception.php';

    /**
     * Envoie un email via Gmail / PHPMailer 6
     * Retourne true ou un message d'erreur
     */
    function smtpMailer_newsletter(
        $to,
        $subject,
        $body,
        $emailemplo,
        $pwgmaero,
        $employeeName,
        &$debug_msgs
    ) {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // CONFIG SMTP GMAIL
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS; // SSL
            $mail->Port       = 465;
            $mail->CharSet    = 'UTF-8';
            $mail->Encoding   = 'base64';

            // Identifiants
            $mail->Username   = $emailemplo;
            $mail->Password   = $pwgmaero;

            // Expéditeur
            $mail->setFrom($emailemplo, $employeeName);
            $mail->addReplyTo($emailemplo, $employeeName);

            // Destinataire principal
            $mail->addAddress($to);

            // Sujet + contenu
            $mail->Subject = (string)$subject;
            $mail->isHTML(true);
            $mail->Body    = (string)$body;

            // Version texte (compatibilité clients texte)
            $plain = (string)$body;
            $plain = preg_replace('#<\s*br\s*/?\s*>#i', "\n", $plain);
            $plain = preg_replace('#</\s*p\s*>#i', "\n", $plain);
            $plain = strip_tags($plain);
            $plain = preg_replace('/[ \t]+/', ' ', $plain);
            $plain = preg_replace("/\n{2,}/", "\n\n", $plain);
            $mail->AltBody = trim($plain);

            $mail->send();
            return true;

        } catch (\PHPMailer\PHPMailer\Exception $e) {
            $err = 'Mail error: ' . $mail->ErrorInfo . ' (Exception: ' . $e->getMessage() . ')';
            $debug_msgs[] = $err;
            return $err;
        }
    }

    // ======================================================================
    // 5. ENVOI DE TEST (POUR VÉRIFIER SUR TOUS LES LOGICIELS)
    // ======================================================================

    // Pour l’instant, on envoie sur ton adresse AeroCanada.
    // Ensuite on branchera ça sur les groupes.
    $to_test = 'yohan@aerocanada.aero';

    $result = smtpMailer_newsletter(
        $to_test,
        $sujet,
        $message_html,
        $emailemplo,
        $pwgmaero,
        $Employee_Name,
        $debug_msgs
    );

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Newsletter send result</title>
        <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body style="padding:20px;">
    <h3>Newsletter – Résultat envoi</h3>

    <?php if ($result === true): ?>
        <div class="alert alert-success">
            Newsletter envoyée avec succès à :
            <?php echo htmlspecialchars($to_test); ?>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            Problème lors de l&#39;envoi à
            <?php echo htmlspecialchars($to_test); ?><br>
            Détail :
            <?php echo htmlspecialchars($result); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($debug_msgs)): ?>
        <div class="alert alert-info">
            <strong>Debug interne :</strong><br>
            <?php foreach ($debug_msgs as $msg) {
                echo htmlspecialchars($msg) . '<br>';
            } ?>
        </div>
    <?php endif; ?>

    <h4>Aperçu HTML envoyé</h4>
    <div style="border:1px solid #ccc;background:#fff;padding:10px;">
        <?php echo $message_html; ?>
    </div>

    </body>
    </html>
    <?php

} else {
    echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php\">";
}
?>
