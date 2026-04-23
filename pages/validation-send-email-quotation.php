<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

if ($_SESSION['conectroy'] == "parfait") {

    // Débug temporaire (tu peux remettre en commentaire quand tout sera bon)
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Aerocanada-industries.com</title>

    <!-- Bootstrap Core CSS -->
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- MetisMenu CSS -->
    <link href="../vendor/metisMenu/metisMenu.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="../vendor/datatables-plugins/dataTables.bootstrap.css" rel="stylesheet">
    <!-- DataTables Responsive CSS -->
    <link href="../vendor/datatables-responsive/dataTables.responsive.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../dist/css/sb-admin-2.css" rel="stylesheet">
    <link href="../dist/css/aci-overrides.css" rel="stylesheet">
    <!-- Custom Fonts -->
    <link href="../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
<div id="wrapper">
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
        <?php include "top_menu.php"; ?>
        <?php if (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu'] == 'open') include "left_menu.php"; ?>
    </nav>

    <?php include "after_nav.php"; ?>

    <div id="page-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Validation Quote</h1>

<?php
// ======================================================================
// 0. TABLEAU DE DEBUG INTERNE
// ======================================================================
$debug_msgs = [];

// ======================================================================
// 1. RÉCUPÉRATION DES INFOS RFQ / CONTACT
// ======================================================================

// RFQ ID est une chaîne du type 2025-11-18-112319 → surtout pas de (int)
$Fld_RFQ_ID = isset($_POST['Fld_RFQ_ID']) ? trim($_POST['Fld_RFQ_ID']) : '';
$debug_msgs[] = "RFQ ID reçu : " . $Fld_RFQ_ID;

$datarfq          = null;
$daterfq          = "";
$contactaci       = 0;
$Fld_Contact_Name = "";
$datar            = null;

if ($Fld_RFQ_ID !== '') {
    // On prend la DERNIÈRE ligne saisie pour ce RFQ (ORDER BY ID DESC)
    $sqlrfq = "
        SELECT *
        FROM tbl_RFQ_1
        WHERE Fld_RFQ_ID = '" . mysqli_real_escape_string($conn, $Fld_RFQ_ID) . "'
        ORDER BY ID DESC
        LIMIT 1
    ";

    $reqrfq = mysql2_query($sqlrfq);
    if ($reqrfq) {
        $datarfq = mysqli_fetch_array($reqrfq);
    }

    if ($datarfq) {
        $daterfq    = $datarfq['date'];
        $contactaci = (int)$datarfq['Employee_ID'];

        $sqlr  = "SELECT * FROM tb_company_contact
                  WHERE id_company_contact = " . (int)$datarfq['id_company_contact'];
        $reqr  = mysql2_query($sqlr);
        $datar = $reqr ? mysqli_fetch_array($reqr) : null;

        if ($datar) {
            $Fld_Contact_Name = $datar['Fld_Contact_Name'];
        }
    } else {
        $debug_msgs[] = "Aucune ligne trouvée dans tbl_RFQ_1 pour ce RFQ.";
    }
} else {
    $debug_msgs[] = "Attention : Fld_RFQ_ID manquant ou égal à 0.";
}

// ======================================================================
// 1.b DONNÉES VENANT DU FORM QUOTE
// ======================================================================

$Fld_Qty_recup   = isset($_POST["Fld_Qty"])   ? $_POST["Fld_Qty"]   : '';
$Fld_Price_recup = isset($_POST["Fld_Price"]) ? $_POST["Fld_Price"] : '';
$Fld_Currency_ID = isset($_POST["FldCurrencyID"]) ? $_POST["FldCurrencyID"] : '1';

// --------------------------------------------------
// FORMATAGE PRIX POUR LE RÉSUMÉ
// --------------------------------------------------
$raw_price = str_replace([' ', "\xc2\xa0"], '', $Fld_Price_recup); // supprime espaces / nbsp
$raw_price = str_replace(',', '.', $raw_price);                    // au cas où
$price_number = (float)$raw_price;

$currencySymbol = '';
switch ($Fld_Currency_ID) {
    case '1': $currencySymbol = '$'; break;      // USD
    case '2': $currencySymbol = '€'; break;      // EUR
    case '3': $currencySymbol = '£'; break;      // GBP
    default:  $currencySymbol = '';  break;
}

if ($price_number > 0) {
    $price_display = trim($currencySymbol . ' ' . number_format($price_number, 2, '.', ','));
} else {
    // fallback : on affiche tel que saisi
    $price_display = $Fld_Price_recup;
}

// --------------------------------------------------
// EXTRACTION RFQ COMPLET + NOM CONTACT À PARTIR DU TEXTE EMAIL
// On essaie plusieurs noms de champs possibles pour le corps HTML
// --------------------------------------------------
$message_html2 = '';
$fields_possibles = ['message_html2', 'message_html', 'message2', 'message', 'message_txt'];

foreach ($fields_possibles as $fname) {
    if (isset($_POST[$fname]) && trim($_POST[$fname]) !== '') {
        $message_html2 = $_POST[$fname];
        $debug_msgs[] = "Contenu email récupéré depuis le champ : " . $fname;
        break;
    }
}

if ($message_html2 === '') {
    $debug_msgs[] = "Aucun contenu HTML d'email trouvé (tous les champs message_* sont vides).";
}

$rfq_display     = $Fld_RFQ_ID;       // fallback
$contact_display = $Fld_Contact_Name; // fallback

$plain_text = strip_tags($message_html2);
$plain_text = preg_replace('/\s+/', ' ', $plain_text);

// RFQ # 2025-11-17-110753
if (preg_match('/RFQ\s*#\s*([0-9\-]+)/i', $plain_text, $m)) {
    $rfq_display = $m[1];
}

// Dear Yohan Amsellem,
if (preg_match('/Dear\s+([^,\n]+)\s*,/i', $plain_text, $m2)) {
    $contact_display = trim($m2[1]);
}

// ======================================================================
// 2. CONSTRUCTION DU CONTENU HTML COMPLET DE L'EMAIL
// ======================================================================

$mail = ($datar && !empty($datar['Fld_Contact_Email'])) ? $datar['Fld_Contact_Email'] : '';
if ($mail == '') {
    $debug_msgs[] = "Email du contact client vide ou introuvable.";
}

// gestion du passage de ligne (gardé comme à l’origine même si on n’en a plus besoin)
if (!preg_match("#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#i", $mail)) {
    $passage_ligne = "\r\n";
} else {
    $passage_ligne = "\n";
}

// ***** ENVELOPPE HTML COMPATIBLE OUTLOOK *****
$message_html  = "<!DOCTYPE html>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
</head>
<body style='margin:0;padding:0;'>
<div style='font-family:sans-serif;color:#000000;font-size:16px;margin:0px;padding:20px;'>
<table style='border-collapse:collapse;border:1px solid #BE0831;background:#e9e9e9;color:#000000;'>
<tbody>
<tr style='height:100px'>
<td style='padding:25px;border:1px solid #BE0831'>
<img src='https://aerocanada-industries.com/pages/images/logo-aei-email.png' width='83' height='96'>
</td>
</tr>
<tr>
<td valign='top' style='padding:25px;border:1px solid #BE0831;line-height:24px'>";

// on reprend exactement le HTML saisi dans le formulaire
$message_html .= $message_html2;

// ======================================================================
// 2.b SIGNATURE EMPLOYÉ (tbl_Employee)
// ======================================================================
$contactaci = $contactaci > 0 ? $contactaci : (int)$_SESSION['id_utilisateur'];
$sqleaci    = "SELECT * FROM tbl_Employee WHERE Employee_ID = " . $contactaci;
$reqeaci    = mysql2_query($sqleaci);
$dataeaci   = $reqeaci ? mysqli_fetch_array($reqeaci) : null;

if (!$dataeaci) {
    $debug_msgs[] = "Impossible de récupérer les infos de l'employé ACI770 (signature).";
    $dataeaci = [
        'Employee_Name' => '',
        'position'      => '',
        'tel'           => '',
        'mobile'        => '',
        'email'         => '',
        'skype'         => ''
    ];
}

$message_html .= "
<div dir='ltr' style='font-size:small'><br>
<table border='0' cellspacing='0' cellpadding='0' width='479' style='width:359.55pt;border-collapse:collapse;border:none'>
<tbody>
<tr>
<td width='108' valign='top' style='width:81pt;border-top:none;border-bottom:none;border-left:none;border-right:1pt solid windowtext;padding:0in 5.4pt'>
<p align='center' style='margin-bottom:0.0001pt;text-align:center'>
<img src='https://aerocanada-industries.com/pages/images/logo-aei-email.png' width='83' height='96'>
</p>
</td>
<td width='371' valign='top' style='width:278.55pt;border:none;padding:0in 5.4pt'>
<p style='margin-bottom:0.0001pt;line-height: 15px;'>
<b style='font-size:12.8px'>
<span lang='EN-US' style='font-family:Arial,sans-serif'>" . $dataeaci['Employee_Name'] . "<br></span>
</b>
<span style='font-family:Arial,sans-serif;font-size:9pt'>" . $dataeaci['position'] . " | AeroCanada Industries 770 Inc.</span>
</p>
<p style='font-size:12.8px'>
<i><span style='font-family:Americana'><b><font size='2'>Your Perfect Choice For Aviation&nbsp;Solutions</font></b></span></i>
</p>
<p style='margin-bottom:0.0001pt;line-height: 15px;'>
<span style='font-family:Arial,sans-serif;font-size:9pt'>dir. " . $dataeaci['tel'] . " | mob.&nbsp;" . $dataeaci['mobile'] . "<br></span>
<span style='font-family:Arial,sans-serif;font-size:9pt'>tel. +1 514 80 06 223 | fax. +1 514 80 06 224<br></span>
<a href='mailto:" . $dataeaci['email'] . "' style='color:rgb(17,85,204);font-family:Arial,sans-serif;font-size:9pt' target='_blank'>" . $dataeaci['email'] . "</a>
<span style='font-family:Arial,sans-serif;font-size:9pt'>&nbsp;|&nbsp;</span>
<a href='http://www.aerocanada.aero/' style='color:rgb(17,85,204);font-family:Arial,sans-serif;font-size:9pt' target='_blank'>http://www.aerocanada.aero</a><br>
<span style='font-family:Arial,sans-serif;font-size:9pt'><b>Skype:</b>&nbsp;" . $dataeaci['skype'] . "</span>
</p>
<p style='margin-bottom:0.0001pt;line-height: 15px;'>
<b><u style='background-color:rgb(204,0,0)'><font color='#f3f3f3'>OUR ADDRESS CHANGED:<br></font></u></b>
<span style='font-size:12.8px'>99, Prince Street, 7th Floor, Suite#701<br></span>
<span style='font-size:12.8px'>Montreal QC H3C 2M7, Canada</span>
</p>
<p style='font-size:12.8px'>
<img src='http://www.aerocanada.org/images/asa-36.png'><span>&nbsp;|&nbsp;</span>
<img src='http://www.aerocanada.org/images/tac-36.png'><span>&nbsp;|&nbsp;</span>
<img src='http://www.aerocanada.org/images/logo-nato-36.png'><span>&nbsp;|&nbsp;</span>
<img src='http://www.aerocanada.org/images/logo-ungm-36.png'><span>&nbsp;|&nbsp;</span>
<a href='https://www.facebook.com/AeroCanada-Industries-770-Inc-967017943346764/' target='_blank'>
<img src='http://www.aerocanada.org/images/f_icon-36.png'></a><span>&nbsp;|&nbsp;</span>
<a href='https://www.linkedin.com/company/3155360' target='_blank'>
<img src='http://www.aerocanada.org/images/linkedin-36.png'></a><br>
</p>
<p style='margin-bottom:0.0001pt;line-height: 15px;'>
<font size='1'>FAA AC00-56A |&nbsp;UNGM 256670 |&nbsp;NATO CAGE L06T4<br></font>
<a href='http://www.aerocanada.org/index.php/fr/aero-canada-accueil/conditions-generales-de-vente' style='color:rgb(17,85,204);font-size:x-small' target='_blank'>Conditions Generales Vente</a>
<span style='font-size:x-small;color:rgb(0,0,0)'>&nbsp;/&nbsp;</span>
<a href='http://www.aerocanada.org/index.php/en/aero-canada/terms-of-sale' style='color:rgb(17,85,204);font-size:x-small' target='_blank'>Terms of Sale</a>
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
<td align='center' style='padding:25px;font-size:12px;border:1px solid #BE0831'>
<a href='http://www.aerocanada.aero' target='_blank'>www.aerocanada.aero</a> • phone:
<a href='tel:+15148006223' value='+15148006223' target='_blank'>+1 514 800 6223</a> • email
<a href='sales@aerocanada.org' value='sales@aerocanada.org' target='_blank'>sales@aerocanada.org</a>
</td>
</tr>
</tbody>
</table>
</div>
</body></html>";

$sujet = isset($_POST['sujet']) ? (string)$_POST['sujet'] : 'Quotation';

// ======================================================================
// 3. UPLOAD DES PIÈCES JOINTES
// ======================================================================

$uploaddir = __DIR__ . '/uploads/';   // /var/www/.../pages/uploads/

// si le dossier n'existe pas, on le crée
if (!is_dir($uploaddir)) {
    if (!mkdir($uploaddir, 0755, true)) {
        $debug_msgs[] = "Impossible de créer le dossier d'uploads : " . $uploaddir;
    }
}

function aci_upload_file($field_name, $uploaddir, &$debug_msgs)
{
    if (!isset($_FILES[$field_name]) || empty($_FILES[$field_name]['name'])) {
        return "no";
    }

    $uploadfile = $uploaddir . basename($_FILES[$field_name]['name']);

    if (move_uploaded_file($_FILES[$field_name]['tmp_name'], $uploadfile)) {
        $debug_msgs[] = "Upload OK pour " . $field_name . " -> " . basename($uploadfile);
        return $uploadfile;
    } else {
        $debug_msgs[] = "Échec upload pour " . $field_name;
        return "no";
    }
}

$uploadfile  = aci_upload_file('userfile',  $uploaddir, $debug_msgs);
$uploadfile2 = aci_upload_file('userfile2', $uploaddir, $debug_msgs);
$uploadfile3 = aci_upload_file('userfile3', $uploaddir, $debug_msgs);

// ======================================================================
// 4. RÉCUP COMPTE GMAIL EMPLOYÉ + PHPMailer (version moderne)
// ======================================================================

// PHPMailer 6.x (dans /pages/phpmailer6)
require_once __DIR__ . '/phpmailer6/PHPMailer.php';
require_once __DIR__ . '/phpmailer6/SMTP.php';
require_once __DIR__ . '/phpmailer6/Exception.php';

// Classe users existante
require('../classes/users.class.php');

// utilisateur connecté
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

/**
 * Envoie un email via Gmail / PHPMailer 6
 * Retourne true ou un message d'erreur
 */
function smtpMailer(
    $to,
    $subject,
    $body,
    $emailemplo,
    $pwgmaero,
    $employeeName,
    $ccList,       // tableau d'adresses CC
    $uploadfile,
    $uploadfile2,
    $uploadfile3,
    &$debug_msgs
) {
    // On utilise le namespace complet pour éviter les "use" en haut de fichier
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // =======================
        // CONFIG SMTP GMAIL
        // =======================
        $mail->isSMTP();
        $mail->SMTPDebug  = 0;
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;

        // Même réglage que ton ancien code : SSL sur 465
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS; // équivalent "ssl"
        $mail->Port       = 465;

        $mail->CharSet    = 'UTF-8';
        $mail->Encoding   = 'base64'; // PHPMailer 6 gère bien ça

        // Identifiants SMTP (Gmail)
        $mail->Username   = $emailemplo;
        $mail->Password   = $pwgmaero;

        // Expéditeur = employé
        $mail->setFrom($emailemplo, $employeeName);
        $mail->addReplyTo($emailemplo, $employeeName);

        // Destinataire principal
        $mail->addAddress($to);

        // =======================
        // CC (tableau $ccList)
        // =======================
        if (!empty($ccList) && is_array($ccList)) {
            foreach ($ccList as $ccAddr) {
                $ccAddr = trim($ccAddr);
                if ($ccAddr !== '' && filter_var($ccAddr, FILTER_VALIDATE_EMAIL)) {
                    $mail->addCC($ccAddr);
                } else {
                    if ($ccAddr !== '') {
                        $debug_msgs[] = "CC ignoré (email invalide) : " . htmlspecialchars($ccAddr);
                    }
                }
            }
        }

        // =======================
        // Sujet + contenu
        // =======================
        $mail->Subject = (string)$subject;
        $mail->isHTML(true);

        // Corps HTML
        $mail->Body = (string)$body;

        // Version texte (pour Outlook / mode "texte seul")
        $plain = (string)$body;
        // <br> -> retour à la ligne
        $plain = preg_replace('#<\s*br\s*/?\s*>#i', "\n", $plain);
        // </p> -> retour à la ligne
        $plain = preg_replace('#</\s*p\s*>#i', "\n", $plain);
        // On enlève le reste du HTML
        $plain = strip_tags($plain);
        // Compacte les espaces multiples
        $plain = preg_replace('/[ \t]+/', ' ', $plain);
        // Normalise les fins de lignes
        $plain = preg_replace("/\n{2,}/", "\n\n", $plain);
        $mail->AltBody = trim($plain);

        // =======================
        // Pièces jointes
        // =======================
        $files = [$uploadfile, $uploadfile2, $uploadfile3];

        foreach ($files as $path) {
            if ($path == "no" || !is_file($path)) {
                continue;
            }

            $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mime = '';

            if ($ext === 'png') {
                $mime = 'image/png';
            } elseif ($ext === 'jpg' || $ext === 'jpeg') {
                $mime = 'image/jpeg';
            } elseif ($ext === 'pdf') {
                $mime = 'application/pdf';
            }

            if ($mime) {
                $mail->addAttachment($path, basename($path), 'base64', $mime);
                $debug_msgs[] = "Pièce jointe envoyée : " . basename($path) . " (" . $mime . ")";
            } else {
                // Autres types → on laisse PHPMailer deviner
                $mail->addAttachment($path);
                $debug_msgs[] = "Pièce jointe envoyée (MIME auto) : " . basename($path);
            }
        }

        // =======================
        // Envoi
        // =======================
        $mail->send();
        return true;

    } catch (\PHPMailer\PHPMailer\Exception $e) {
        $err = 'Mail error: ' . $mail->ErrorInfo . ' (Exception: ' . $e->getMessage() . ')';
        $debug_msgs[] = $err;
        return $err;
    }
}

// ======================================================================
// 5. ENVOI DES EMAILS CLIENTS
// ======================================================================

// Emails destinataires (TO) saisis dans le formulaire
$clientemail_raw = isset($_POST['clientemail']) ? $_POST['clientemail'] : '';
$cc_raw = isset($_POST['emailcc']) ? $_POST['emailcc'] : '';

// Emails en copie (CC) – on essaie plusieurs noms possibles au cas où
$cc_raw = '';
if (isset($_POST['emailcc'])) {
    $cc_raw = $_POST['emailcc'];
} elseif (isset($_POST['email_cc'])) {
    $cc_raw = $_POST['email_cc'];
} elseif (isset($_POST['ccemail'])) {
    $cc_raw = $_POST['ccemail'];
}

/**
 * Parse une chaîne d'emails séparés par virgule/espace/point-virgule
 * et retourne un tableau d'adresses valides.
 */
function aci_parse_emails($raw, &$debug_msgs, $label = 'TO')
{
    $list = [];
    $raw  = trim($raw);

    if ($raw === '') {
        return $list;
    }

    // On coupe d'abord sur les virgules / points-virgules (séparation classique des adresses)
    $chunks = preg_split('/[;,]+/', $raw);

    foreach ($chunks as $chunk) {
        $chunk = trim($chunk);
        if ($chunk === '') {
            continue;
        }

        // Si on a un format "Nom <email@domaine.com>", on récupère juste ce qui est entre <>
        if (preg_match('/<([^>]+)>/', $chunk, $m)) {
            $addr = trim($m[1]);
        } else {
            $addr = $chunk;
        }

        if (filter_var($addr, FILTER_VALIDATE_EMAIL)) {
            $list[] = $addr;
        } else {
            $debug_msgs[] = "Adresse email invalide ignorée dans $label : " . htmlspecialchars($chunk);
        }
    }

    return $list;
}


// Tableau d'emails TO et CC
$listmails = aci_parse_emails($clientemail_raw, $debug_msgs, 'TO');
$listCC    = aci_parse_emails($cc_raw,        $debug_msgs, 'CC');

// Fallback : si aucun TO mais au moins un CC, on utilise le 1er CC comme destinataire principal
if (empty($listmails) && !empty($listCC)) {
    $listmails[] = array_shift($listCC); // retire le 1er CC et le met en TO
    $debug_msgs[] = "Aucun TO fourni, premier CC utilisé comme destinataire principal.";
}

$nbmails = count($listmails);
$send_results = [];


if ($nbmails > 0) {
    foreach ($listmails as $oneMail) {
        $res = smtpMailer(
            $oneMail,
            $sujet,
            $message_html,
            $emailemplo,
            $pwgmaero,
            $Employee_Name,
            $listCC,       // <- tableau de CC
            $uploadfile,
            $uploadfile2,
            $uploadfile3,
            $debug_msgs
        );
        $send_results[$oneMail] = $res;
    }
} else {
    $debug_msgs[] = "Aucune adresse email client valide fournie (clientemail vide ou invalide).";
}


// ======================================================================
// 6. AFFICHAGE : DEBUG + RÉCAP + APERÇU EMAIL
// ======================================================================
?>

<?php if (!empty($debug_msgs)) : ?>
    <div class="alert alert-info" style="margin-top:10px;">
        <strong>Info debug interne (temporaire) :</strong><br>
        <?php foreach ($debug_msgs as $msg) {
            echo htmlspecialchars($msg) . '<br>';
        } ?>
    </div>
<?php endif; ?>

<?php if (!empty($send_results)) : ?>
    <?php
    $onlyTrue = array_filter($send_results, function ($v) { return $v === true; });
    $allOk    = (count($onlyTrue) === count($send_results));
    ?>
    <div class="alert alert-<?php echo $allOk ? 'success' : 'warning'; ?>">
        <strong>Résultat envoi email :</strong><br>
        <?php
        foreach ($send_results as $email => $res) {
            if ($res === true) {
                echo htmlspecialchars($email) . " : envoyé avec succès.<br>";
            } else {
                echo htmlspecialchars($email) . " : " . htmlspecialchars($res) . "<br>";
            }
        }
        ?>
    </div>
<?php else : ?>
    <div class="alert alert-warning">
        Aucun email n'a été envoyé (voir section debug ci-dessus).
    </div>
<?php endif; ?>

<div class="panel panel-default">
    <div class="panel-heading">
        <strong>Résumé de la cotation envoyée</strong>
    </div>
    <div class="panel-body">
        <p><strong>RFQ :</strong> RFQ # <?php echo htmlspecialchars($rfq_display); ?></p>
        <p><strong>Contact :</strong> <?php echo htmlspecialchars($contact_display); ?></p>
        <p><strong>Email(s) client :</strong> <?php echo htmlspecialchars($clientemail_raw); ?></p>
        <p><strong>CC :</strong> <?php echo htmlspecialchars($cc_raw); ?></p>
        <p><strong>Sujet :</strong> <?php echo htmlspecialchars($sujet); ?></p>
        <p><strong>Qté :</strong> <?php echo htmlspecialchars($Fld_Qty_recup); ?> |
           <strong>Prix :</strong> <?php echo htmlspecialchars($price_display); ?></p>
        <p><strong>Pièces jointes :</strong>
            <?php
            $att = [];
            if ($uploadfile  != "no") $att[] = basename($uploadfile);
            if ($uploadfile2 != "no") $att[] = basename($uploadfile2);
            if ($uploadfile3 != "no") $att[] = basename($uploadfile3);
            echo empty($att) ? "aucune" : htmlspecialchars(implode(', ', $att));
            ?>
        </p>
    </div>
</div>

<?php
// Lien retour vers la fiche Part-Nbr correspondant au RFQ
// Priorité : PN texte (pn_rfq) → c’est ce qui apparaît dans l’URL ?pn=XXXX
$back_url = 'Part-Nbr.php';

if (!empty($_POST['pn_rfq'])) {
    // PN envoyé par le formulaire
    $back_url .= '?pn=' . urlencode($_POST['pn_rfq']);
} elseif (!empty($datarfq['pn_rfq'])) {
    // PN enregistré dans tbl_RFQ_1
    $back_url .= '?pn=' . urlencode($datarfq['pn_rfq']);
} elseif (!empty($datarfq['Fld_Part_ID'])) {
    // Dernier recours : ID interne de la pièce
    $back_url .= '?part_id=' . (int)$datarfq['Fld_Part_ID'];
}
?>
<div style="margin-bottom:15px;">
    <a href="<?php echo $back_url; ?>" class="btn btn-default">
        ← Back to Part-Nbr
    </a>
    <!-- Cancel renvoie aussi sur le même PN, mais sans forcer le rechargement de l’email -->
    <a href="<?php echo $back_url; ?>" class="btn btn-default">
        Cancel / Go back
    </a>
</div>

<h3>Aperçu de l'email envoyé</h3>
<div style="border:1px solid #ccc;padding:10px;background:#fff;margin-bottom:20px;">
    <?php echo $message_html; ?>
</div>


<?php
// ======================================================================
// 7. NETTOYAGE DES FICHIERS UPLOADÉS (dans le même dossier)
// ======================================================================

// On supprime uniquement les fichiers réellement uploadés pour cette quotation
if ($uploadfile != "no" && is_file($uploadfile)) {
    unlink($uploadfile);
}
if ($uploadfile2 != "no" && is_file($uploadfile2)) {
    unlink($uploadfile2);
}
if ($uploadfile3 != "no" && is_file($uploadfile3)) {
    unlink($uploadfile3);
}
?>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="../vendor/jquery/jquery.min.js"></script>
<!-- Metis Menu Plugin JavaScript -->
<script src="../vendor/metisMenu/metisMenu.min.js"></script>
<!-- DataTables JavaScript -->
<script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
<script src="../vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
<script src="../vendor/datatables-responsive/dataTables.responsive.js"></script>
<!-- Custom Theme JavaScript -->
<script src="../dist/js/sb-admin-2.js"></script>

</body>
</html>

<?php
} else {
    echo '<meta http-equiv="refresh" content="0; url=login.php">';
}
?>
