<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

if ($_SESSION['conectroy'] == "parfait") {

/***********************************************************************************/
/* Generation de l'email pour client */
/***********************************************************************************/

// $mail = "lamalol@gmail.com"; // Déclaration de l'adresse de destination.
$mail = $datar['Fld_Contact_Email']; // Déclaration de l'adresse de destination.

if (!preg_match("#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#", $mail)) {
    // On filtre les serveurs qui rencontrent des bogues.
    $passage_ligne = "\r\n";
} else {
    $passage_ligne = "\n";
}

//=====Déclaration du message HTML principal
$message_html = '<html><head></head><body>
<div style="font-family:sans-serif;color:#000000;font-size:16px;margin:0px;padding:20px;">
<table id="" style="border-collapse:collapse;border:1px solid #BE0831;background:#e9e9e9;color:#000000;">
<tbody>
<tr style="height:100px">
<td id="" style="padding:25px;border:1px solid #BE0831">
<img src="https://www.aerocanada-industries.com/pages/images/logo-aei-email.png" width="83" height="96">
</td>
</tr>
<tr>
<td id="" valign="top" style="padding:25px;border:1px solid #BE0831;line-height:24px">';

$message_html .= $_POST['message_html2'];

//======================================================================
// Récupération employee ACI 770 pour signature (tbl_Employee)
//======================================================================
// ** tbl_Employee **  Employee_ID | Employee_Name | Fld_Contact_Id | pw | email | statut
//                      position   | tel | mobile | skype | numformat | pwgmaero
$sqleaci  = "SELECT * FROM tbl_Employee WHERE Employee_ID='" . $_SESSION['id_utilisateur'] . "'";
$reqeaci  = mysql2_query($sqleaci);
$dataeaci = mysqli_fetch_array($reqeaci);
// Fin recuperation employee ACI 770

$message_html .= '
<div dir="ltr" style="font-size:small"><br>
<table border="0" cellspacing="0" cellpadding="0" width="479" style="width:359.55pt;border-collapse:collapse;border:none">
<tbody>
<tr>
<td width="108" valign="top" style="width:81pt;border-top:none;border-bottom:none;border-left:none;border-right:1pt solid windowtext;padding:0in 5.4pt">
<p align="center" style="margin-bottom:0.0001pt;text-align:center">
<img src="https://www.aerocanada-industries.com/pages/images/logo-aei-email.png" width="83" height="96">
</p>
</td>
<td width="371" valign="top" style="width:278.55pt;border:none;padding:0in 5.4pt">
<p style="margin-bottom:0.0001pt;line-height: 15px;">
<b style="font-size:12.8px">
<span lang="EN-US" style="font-family:Arial,sans-serif">' . $dataeaci['Employee_Name'] . '<br></span>
</b>
<span style="font-family:Arial,sans-serif;font-size:9pt">' . $dataeaci['position'] . ' | AeroCanada Industries 770 Inc.</span>
</p>
<p style="font-size:12.8px">
<i><span style="font-family:Americana"><b><font size="2">Your Perfect Choice For Aviation&nbsp;Solutions</font></b></span></i>
</p>
<p style="margin-bottom:0.0001pt;line-height: 15px;">
<span style="font-family:Arial,sans-serif;font-size:9pt">dir. ' . $dataeaci['tel'] . ' | mob.&nbsp;' . $dataeaci['mobile'] . '<br></span>
<span style="font-family:Arial,sans-serif;font-size:9pt">tel. +1 514 80 06 223 | fax. +1 514 80 06 224<br></span>
<a href="mailto:' . $dataeaci['email'] . '" style="color:rgb(17,85,204);font-family:Arial,sans-serif;font-size:9pt" target="_blank">' . $dataeaci['email'] . '</a>
<span style="font-family:Arial,sans-serif;font-size:9pt">&nbsp;|&nbsp;</span>
<a href="http://www.aerocanada.aero/" style="color:rgb(17,85,204);font-family:Arial,sans-serif;font-size:9pt" target="_blank">http://www.aerocanada.aero</a><br>
<span style="font-family:Arial,sans-serif;font-size:9pt"><b>Skype:</b>&nbsp;' . $dataeaci['skype'] . '</span>
</p>
<p style="margin-bottom:0.0001pt;line-height: 15px;">
<b><u style="background-color:rgb(204,0,0)"><font color="#f3f3f3">OUR ADDRESS CHANGED:<br></font></u></b>
<span style="font-size:12.8px">99, Prince Street, 7th Floor, Suite#701<br></span>
<span style="font-size:12.8px">Montreal QC H3C 2M7, Canada</span>
</p>
<p style="font-size:12.8px">
<img src="http://www.aerocanada.org/images/asa-36.png"><span>&nbsp;|&nbsp;</span>
<img src="http://www.aerocanada.org/images/tac-36.png"><span>&nbsp;|&nbsp;</span>
<img src="http://www.aerocanada.org/images/logo-nato-36.png"><span>&nbsp;|&nbsp;</span>
<img src="http://www.aerocanada.org/images/logo-ungm-36.png"><span>&nbsp;|&nbsp;</span>
<a href="https://www.facebook.com/AeroCanada-Industries-770-Inc-967017943346764/" style="color:rgb(17,85,204);" target="_blank">
<img src="http://www.aerocanada.org/images/f_icon-36.png"></a><span>&nbsp;|&nbsp;</span>
<a href="https://www.linkedin.com/company/3155360" style="color:rgb(17,85,204);" target="_blank">
<img src="http://www.aerocanada.org/images/linkedin-36.png"></a><br>
</p>
<p style="margin-bottom:0.0001pt;line-height: 15px;">
<font size="1">FAA AC00-56A |&nbsp;UNGM 256670 |&nbsp;NATO CAGE L06T4<br></font>
<a href="http://www.aerocanada.org/index.php/fr/aero-canada-accueil/conditions-generales-de-vente" style="color:rgb(17,85,204);font-size:x-small" target="_blank">Conditions Generales Vente</a>
<span style="font-size:x-small;color:rgb(0,0,0)">&nbsp;/&nbsp;</span>
<a href="http://www.aerocanada.org/index.php/en/aero-canada/terms-of-sale" style="color:rgb(17,85,204);font-size:x-small" target="_blank">Terms of Sale</a>
</p>
<div><br></div>
</td>
</tr>
</tbody>
</table>
</div>

</td>
</tr>
<tr>
<td id="" align="center" style="padding:25px;font-size:12px;border:1px solid #BE0831">
<a href="http://www.aerocanada.aero" target="_blank">www.aerocanada.aero</a> • phone:
<a href="tel:+15148006223" value="+15148006223" target="_blank">+1 514 800 6223</a> • email
<a href="sales@aerocanada.org" value="sales@aerocanada.org" target="_blank">sales@aerocanada.org</a>
</td>
</tr>
</tbody>
</table>
</div>
</body></html>';

//=====Définition du sujet.
$sujet = $_POST['sujet'];
//=========

//*******************************************************************************************************
// module envoi email par gmail
//*******************************************************************************************************

ini_set('display_errors', 1);

// --- PATCH compatibilité PHP 8 pour PHPMailer-FE ---
// certaines vieilles versions appellent encore get_magic_quotes_runtime()
if (!function_exists('get_magic_quotes_runtime')) {
    function get_magic_quotes_runtime() {
        return 0;
    }
}
if (!function_exists('set_magic_quotes_runtime')) {
    function set_magic_quotes_runtime($new_setting) {
        // ne fait rien, juste pour éviter l’erreur fatale
        return true;
    }
}
// --- FIN PATCH ---

require_once('PHPMailer-FE_v4.11/_lib/class.phpmailer.php');

// récupération information compte google employee
require('../classes/users.class.php');

// tbl_Employee : Employee_ID, Employee_Name, Fld_Contact_Id, pw, email, statut,
// position, tel, mobile, skype, numformat, pwgmaero
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

function smtpMailer($to, $from, $from_name, $subject, $body, $emailemplo, $pwgmaero) {
    $mail = new PHPMailer();  // Cree un nouvel objet PHPMailer
    $mail->IsSMTP();          // active SMTP
    $mail->SMTPDebug  = 0;    // 0 = pas de debug
    $mail->SMTPAuth   = true; // Authentification SMTP active
    $mail->SMTPSecure = 'ssl'; // Gmail REQUIERT le transfert securise
    $mail->Host       = 'smtp.gmail.com';
    $mail->Port       = 465;
    $mail->Username   = $emailemplo;
    $mail->Password   = $pwgmaero;

    $mail->From     = $from;
    $mail->FromName = $from_name;
    $mail->Subject  = $subject;
    $mail->isHTML(true);
    $mail->Body     = $body;

    $mail->AddAddress($to);

    if (!$mail->Send()) {
        return 'Mail error: ' . $mail->ErrorInfo;
    } else {
        return true;
    }
}

// Envoi à toutes les adresses de emailrfq
$tags  = explode(',', $_POST['emailrfq']);
$count = count($tags);

foreach ($tags as $key) {
    $key = trim($key);
    if ($key === '') continue;

    $result = smtpMailer(
        $key,
        $emailemplo,
        $Employee_Name,
        $sujet,
        $message_html,
        $emailemplo,
        $pwgmaero
    );
    // (éventuellement, tu pourras logguer $result plus tard)
}

//==========
// Fin Generation de l'email pour client
//==========

echo '<meta http-equiv="refresh" content="0; url=Part-Nbr.php?part_id=' . $_POST['Fld_Part_ID'] . '">';

} else {
    echo '<meta http-equiv="refresh" content="0; url=login.php">';
}
?>
