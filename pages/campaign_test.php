<?php
// campaign_test.php — ENVOI D’UN SEUL EMAIL DE TEST

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "phpmailer/PHPMailerAutoload.php";

// =============================
// CONFIG DESTINATAIRE DU TEST
// =============================
$to = "yohan@aerocanada.aero";   // ← ton email de test

// =============================
// TEXTE DE LA CAMPAGNE
// =============================
$first_name = "Yohan";
$dear = "Dear $first_name,";

// =============================
// SIGNATURE (EXACTE VERSION GMAIL)
// =============================
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

// =============================
// TEXTE FINAL DE L’EMAIL
// =============================
$html = "
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

// =============================
// ENVOI DU MAIL
// =============================
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    // ⚠️ EXACTEMENT ton mot de passe d’application Gmail
    $mail->Username = 'sales@aerocanada-industries.com';
    $mail->Password = 'dpcuaertsbpmhfdy';

    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('sales@aerocanada-industries.com', 'AeroCanada Industries 770 Inc.');
    $mail->addAddress($to);

    $mail->isHTML(true);
    $mail->Subject = "TEST — AeroCanada Broadcast Preview";
    $mail->Body    = $html;

    $mail->send();
    echo "Email test envoyé à $to !";

} catch (Exception $e) {
    echo "Erreur d'envoi : " . $mail->ErrorInfo;
}
