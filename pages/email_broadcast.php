<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

if ($_SESSION['conectroy'] == "parfait") {
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

    <!-- CKEditor -->
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>

</head>

<body>
    <div id="wrapper">
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
            <?php include "top_menu.php"; ?>
            <?php if (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
        </nav>
        <?php include "after_nav.php"; ?>

        <!-- Page Content -->
        <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">

            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <!-- <h1 class="page-header"></h1> -->
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                SEND E-MAIL QUOTE
                            </div>

                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-lg-6">

<?php
// ==================== MISE A JOUR RFQ / QUOTATION ====================
require('../classes/rfq.class.php');
if (!empty($_POST['actonrfq']) && $_POST['actonrfq'] == 'addrfqft') {
    $objet  = new rfq();
    $donnee = $objet->add_rfq();
} else {
    $objet  = new rfq();
    $donnee = $objet->modif_rfq_quote();
}
// =====================================================================
?>

<form method="post" enctype="multipart/form-data" name="Form1">
    <!-- HIDDEN FIELDS (sécurisés avec isset) -->
    <input type="hidden" name="RFQ_ID"            value="<?php echo isset($_POST['Fld_RFQ_ID']) ? $_POST['Fld_RFQ_ID'] : ''; ?>">
    <input type="hidden" name="Fld_RFQ_ID"        value="<?php echo isset($_POST['Fld_RFQ_ID']) ? $_POST['Fld_RFQ_ID'] : ''; ?>">
    <input type="hidden" name="Fld_Qty"           value="<?php echo isset($_POST['Fld_Qty']) ? $_POST['Fld_Qty'] : ''; ?>">
    <input type="hidden" name="Fld_Price"         value="<?php echo isset($_POST['Fld_Price']) ? $_POST['Fld_Price'] : ''; ?>">
    <input type="hidden" name="Fld_Part_Nbr"      value="<?php echo isset($_POST['Fld_Part_Nbr']) ? $_POST['Fld_Part_Nbr'] : ''; ?>">
    <input type="hidden" name="Fld_Part_Desc"     value="<?php echo isset($_POST['Fld_Part_Desc']) ? $_POST['Fld_Part_Desc'] : ''; ?>">
    <input type="hidden" name="Fld_Condition_ID"  value="<?php echo isset($_POST['Fld_Condition_ID']) ? $_POST['Fld_Condition_ID'] : ''; ?>">
    <input type="hidden" name="Fld_Release_ID"    value="<?php echo isset($_POST['Fld_Release_ID']) ? $_POST['Fld_Release_ID'] : ''; ?>">
    <input type="hidden" name="lead_time"         value="<?php echo isset($_POST['lead_time']) ? $_POST['lead_time'] : ''; ?>">
    <input type="hidden" name="Fld_Remark"        value="<?php echo isset($_POST['Fld_Remark']) ? $_POST['Fld_Remark'] : ''; ?>">
    <input type="hidden" name="Fld_Tag_Info_ID"   value="<?php echo isset($_POST['Fld_Tag_Info_ID']) ? $_POST['Fld_Tag_Info_ID'] : ''; ?>">
    <input type="hidden" name="Fld_Tag_Date"      value="<?php echo isset($_POST['Fld_Tag_Date']) ? $_POST['Fld_Tag_Date'] : ''; ?>">
    <input type="hidden" name="part_id"           value="<?php echo isset($_POST['part_id']) ? $_POST['part_id'] : ''; ?>">
    <input type="hidden" name="Fld_Part_SN"       value="<?php echo isset($_POST['Fld_Part_SN']) ? $_POST['Fld_Part_SN'] : ''; ?>">
    <input type="hidden" name="FldCurrencyID"     value="<?php echo isset($_POST['FldCurrencyID']) ? $_POST['FldCurrencyID'] : ''; ?>">
    <input type="hidden" name="Fld_Priority_ID"   value="<?php echo isset($_POST['Fld_Priority_ID']) ? $_POST['Fld_Priority_ID'] : ''; ?>">
    <input type="hidden" name="moq"               value="<?php echo isset($_POST['moq']) ? $_POST['moq'] : ''; ?>">
    <input type="hidden" name="Fld_Traceability_ID" value="<?php echo isset($_POST['Fld_Traceability_ID']) ? $_POST['Fld_Traceability_ID'] : ''; ?>">
    <input type="hidden" name="idrfq1"            value="<?php echo isset($_POST['idrfq1']) ? (int)$_POST['idrfq1'] : ''; ?>">
    <input type="hidden" name="selected_source_type" value="<?php echo isset($_POST['selected_source_type']) ? htmlspecialchars($_POST['selected_source_type'], ENT_QUOTES, 'UTF-8') : ''; ?>">
    <input type="hidden" name="selected_source_id" value="<?php echo isset($_POST['selected_source_id']) ? (int)$_POST['selected_source_id'] : ''; ?>">

<?php
// Si pas de RFQ_ID => on affiche un message au lieu de casser la page
if (empty($_POST['Fld_RFQ_ID'])) {
    echo '<div class="alert alert-danger">No RFQ data received. Please go back to the Part page and click "SEND QUOTATION" again.</div>';
} else {

    $rfq_id = $_POST['Fld_RFQ_ID'];
    $posted_idrfq1 = isset($_POST['idrfq1']) ? (int)$_POST['idrfq1'] : 0;

    // ==================== RECUPERATION INFO RFQ ====================
    if ($posted_idrfq1 > 0) {
        $sqlrfq  = "SELECT * FROM tbl_RFQ_1 WHERE ID=" . $posted_idrfq1 . " AND Fld_RFQ_ID='" . addslashes($rfq_id) . "' LIMIT 1";
    } else {
        $sqlrfq  = "SELECT * FROM tbl_RFQ_1 WHERE Fld_RFQ_ID='" . addslashes($rfq_id) . "' ORDER BY ID DESC LIMIT 1";
    }
    $reqrfq  = mysql2_query($sqlrfq);
    $datarfq = $reqrfq ? mysqli_fetch_array($reqrfq) : null;

    if (!$datarfq) {
        echo '<div class="alert alert-danger">RFQ not found in database (ID: '.htmlspecialchars($rfq_id).').</div>';
    } else {

        $id_tbl_rfq1 = $datarfq['ID'];
        $daterfq     = $datarfq['date'];
        // Employee ACI utilisé pour la signature
// 1) Si l'ID de l'employé connecté est en session -> on l'utilise
// 2) Sinon on garde celui de la RFQ (comportement historique)
if (isset($_SESSION['Employee_ID']) && (int)$_SESSION['Employee_ID'] > 0) {
    $contactaci = (int) $_SESSION['Employee_ID'];
} else {
    $contactaci = (int) $datarfq['Employee_ID'];
}


        // ===== CONTACT CLIENT =====
        $sqlr  = "SELECT * FROM tb_company_contact WHERE id_company_contact=".(int)$datarfq['id_company_contact'];
        $reqr  = mysql2_query($sqlr);
        $datar = $reqr ? mysqli_fetch_array($reqr) : null;

        if (!$datar) {
            echo '<div class="alert alert-danger">Customer contact not found for this RFQ.</div>';
        } else {

            $Fld_Contact_Name = $datar['Fld_Contact_Name'];

            // Données récupérées de la page précédente
            $Fld_Qty_recup   = isset($_POST["Fld_Qty"])        ? $_POST["Fld_Qty"]        : 0;
            $Fld_Price_recup = isset($_POST["Fld_Price"])      ? $_POST["Fld_Price"]      : 0;
            $Fld_Currency_ID = isset($_POST["FldCurrencyID"])  ? $_POST["FldCurrencyID"]  : 1;

            /***************************************************************
             * GENERATION DE L'EMAIL POUR CLIENT
             ***************************************************************/
            $mail = $datar['Fld_Contact_Email']; // Adresse de destination

            if (!preg_match("#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#", $mail)) {
                $passage_ligne = "\r\n";
            } else {
                $passage_ligne = "\n";
            }

            $message_txt = "Salut à tous, voici un e-mail envoyé par un script PHP.";

            $message_html = '<html><head></head><body>
<div style="font-family:sans-serif;color:#000000;font-size:16px;margin:0px;padding:20px;">
<table id="" style="border-collapse:collapse;border:1px solid #BE0831;background:#e9e9e9;color:#000000;"><tbody><tr style="height:100px"><td id="" style="padding:25px;border:1px solid #BE0831"><img src="http://aerocanada-industries.com/pages/images/logo-aei-email.png" width="83" height="96"></td></tr><tr><td id="" valign="top" style="padding:25px;border:1px solid #BE0831;line-height:24px">';

            // Priority
            $sqlPriority  = "SELECT Fld_Priority_Text FROM tbl_Priority WHERE Fld_Priority_ID=".(int)$_POST['Fld_Priority_ID'];
            $reqPriority  = mysql2_query($sqlPriority);
            $dataPriority = $reqPriority ? mysqli_fetch_array($reqPriority) : ['Fld_Priority_Text'=>''];

            // Condition
            $sqlc  = "SELECT Fld_Condition_Text FROM tbl_Condition WHERE Fld_Condition_ID=".(int)$_POST['Fld_Condition_ID'];
            $reqc  = mysql2_query($sqlc);
            $datac = $reqc ? mysqli_fetch_array($reqc) : ['Fld_Condition_Text'=>''];
            $Fld_Condition_Text_aff = $datac["Fld_Condition_Text"];

            // ===== FORMATAGE PRIX COMPATIBLE PHP 8 =====
            $Fld_Price_recup = (float)$Fld_Price_recup;

            $currencySymbol = '';
            if ($Fld_Currency_ID == '1') {
                $currencySymbol = '$';        // USD
            } elseif ($Fld_Currency_ID == '2') {
                $currencySymbol = '€';        // EUR
            } elseif ($Fld_Currency_ID == '3') {
                $currencySymbol = '£';        // GBP
            }

            $nombre_format_francais = $currencySymbol . ' ' . number_format($Fld_Price_recup, 2, '.', ',');

            // Release
            $sqlrel  = "SELECT Fld_Release_Text FROM tbl_Release WHERE Fld_Release_ID=".(int)$_POST['Fld_Release_ID'];
            $reqrel  = mysql2_query($sqlrel);
            $datarel = $reqrel ? mysqli_fetch_array($reqrel) : ['Fld_Release_Text'=>''];

            // Nom compagnie Tag Info
            $companyname = isset($_POST['Fld_Tag_Info_ID']) ? $_POST['Fld_Tag_Info_ID'] : '';
            $companyname = explode(",", $companyname);
            $companyname = $companyname[0];

            $message_html2 = 'Dear '.$Fld_Contact_Name.',<br><br>
First of all, thank you very much for your RFQ.<br>
Per your request, we can propose you as follow:
<br>
<table border="0" cellpadding="1" cellspacing="1"><tbody>
<tr><td nowrap="" align="left" style="font-weight: bold;" colspan="2"><b>Priority</b> &nbsp;&nbsp;'.$dataPriority['Fld_Priority_Text'].' &nbsp;&nbsp;&nbsp;&nbsp;- &nbsp;&nbsp;<b>RFQ #</b> '.$_POST['Fld_RFQ_ID'].'</td></tr>
<tr><td align="left" style="font-weight: bold;"><b>Part Number</b></td><td nowrap="" align="left">&nbsp;&nbsp;'.$_POST['Fld_Part_Nbr'].'</td></tr>
<tr><td nowrap="" align="left" style="font-weight: bold;"><b>Description</b></td><td nowrap="" align="left">&nbsp;&nbsp;'.$_POST['Fld_Part_Desc'].'</td></tr>
<tr><td nowrap="" align="left" style="font-weight: bold;"><b>Condition</b></td><td nowrap="" align="left">&nbsp;&nbsp;'.$Fld_Condition_Text_aff.'</td></tr>
<tr><td nowrap="" align="left" style="font-weight: bold;"><b>Quantity</b></td><td nowrap="" align="left">&nbsp;&nbsp;'.$Fld_Qty_recup.'</td></tr>
<tr><td nowrap="" align="left" style="font-weight: bold;"><b>Price </b></td><td nowrap="" align="left">&nbsp;&nbsp;'.$nombre_format_francais.'&nbsp;EA</td></tr>';

            if (!empty($_POST['moq'])) {
                $message_html2 .= '<tr><td nowrap="" align="left" style="font-weight: bold;"><b>Minimum Qty</b></td><td nowrap="" align="left">&nbsp;&nbsp;'.$_POST['moq'].'</td></tr>';
            }

            $message_html2 .= '<tr><td nowrap="" align="left" style="font-weight: bold;"><b>Certification</b></td><td nowrap="" align="left">&nbsp;&nbsp;'.$datarel['Fld_Release_Text'].' By '.$companyname.' '.$_POST['Fld_Tag_Date'].'</td></tr>
<tr><td nowrap="" align="left" style="font-weight: bold;"><b>Delivery</b></td><td nowrap="" align="left">&nbsp;&nbsp;'.$_POST['lead_time'].'</td></tr>
<tr><td nowrap="" align="left" style="font-weight: bold;"><b>Comments</b></td><td nowrap="" align="left">&nbsp;&nbsp;'.$_POST['Fld_Remark'].'</td></tr>
</tbody></table>
<span style="font-size:11px;">Prices & Availability are subject to change / AOG fees may apply.</span>
<br>
In case of any requirement, please remember to contact AeroCanada Industries 770 Inc.
<br>
It will always be a pleasure to support you!
<br><br>
Best Regards,<br>
';

            // ===== EMPLOYEE ACI 770 =====
            $sqleaci = "SELECT * FROM tbl_Employee WHERE Employee_ID=".(int)$contactaci;
            $reqeaci = mysql2_query($sqleaci);
            $dataeaci = $reqeaci ? mysqli_fetch_array($reqeaci) : [
                'Employee_Name'=>'',
                'position'=>'',
                'tel'=>'',
                'mobile'=>'',
                'email'=>'',
                'skype'=>''
            ];

            // Signature + footer (TA GROS BLOC ORIGINAL)
            $message_html3 = '<div dir="ltr" style="font-size:small"><br><table border="0" cellspacing="0" cellpadding="0" width="479" style="width:359.55pt;border-collapse:collapse;border:none"><tbody><tr><td width="108" valign="top" style="width:81pt;border-top:none;border-bottom:none;border-left:none;border-right:1pt solid windowtext;padding:0in 5.4pt"><p align="center" style="margin-bottom:0.0001pt;text-align:center"><img src="http://aerocanada-industries.com/pages/images/logo-aei-email.png" width="83" height="96"></p></td><td width="371" valign="top" style="width:278.55pt;border:none;padding:0in 5.4pt"><p style="margin-bottom:0.0001pt;line-height: 15px;"><b style="font-size:12.8px"><span lang="EN-US" style="font-family:Arial,sans-serif">'.$dataeaci['Employee_Name'].'<br></span></b><span style="font-family:Arial,sans-serif;font-size:9pt">'.$dataeaci['position'].' | AeroCanada Industries 770 Inc.</span></p><p style="font-size:12.8px"><i><span style="font-family:Americana"><b><font size="2">Your Perfect Choice For Aviation&nbsp;Solutions</font></b></span></i></p><p style="margin-bottom:0.0001pt;line-height: 15px;"><span style="font-family:Arial,sans-serif;font-size:9pt">dir. '.$dataeaci['tel'].' | mob.&nbsp;'.$dataeaci['mobile'].'<br></span><span style="font-family:Arial,sans-serif;font-size:9pt">tel. +1 514 80 06 223 | fax. +1 514 80 06 224<br></span><a href="mailto:roy@aerocanada.aero" style="color:rgb(17,85,204);font-family:Arial,sans-serif;font-size:9pt" target="_blank">'.$dataeaci['email'].'</a><span style="font-family:Arial,sans-serif;font-size:9pt">&nbsp;|&nbsp;</span><a href="http://www.aerocanada.aero/" style="color:rgb(17,85,204);font-family:Arial,sans-serif;font-size:9pt" target="_blank">http://www.aerocanada.aero</a><br><span style="font-family:Arial,sans-serif;font-size:9pt"><b>Skype:</b>&nbsp;'.$dataeaci['skype'].'</span></p><p style="margin-bottom:0.0001pt;line-height: 15px;"><b><u style="background-color:rgb(204,0,0)"><font color="#f3f3f3">OUR ADDRESS CHANGED:<br></font></u></b><span style="font-size:12.8px">99, Prince Street, 7th Floor, Suite#701<br></span><span style="font-size:12.8px">Montreal QC H3C 2M7, Canada</span></p><p style="font-size:12.8px"><img src="http://www.aerocanada.org/images/asa-36.png" style="font-size:12.8px"><span style="font-size:12.8px">&nbsp;|&nbsp;</span><img src="http://www.aerocanada.org/images/tac-36.png" style="font-size:12.8px"><span style="font-size:12.8px">&nbsp;|&nbsp;</span><img src="http://www.aerocanada.org/images/logo-nato-36.png" style="font-size:12.8px"><span style="font-size:12.8px">&nbsp;|&nbsp;</span><img src="http://www.aerocanada.org/images/logo-ungm-36.png" style="font-size:12.8px"><span style="font-size:12.8px">&nbsp;|&nbsp;</span><a href="https://www.facebook.com/AeroCanada-Industries-770-Inc-967017943346764/" style="color:rgb(17,85,204);font-size:12.8px" target="_blank"><img src="http://www.aerocanada.org/images/f_icon-36.png" style="font-size:12.8px"></a><span style="font-size:12.8px">&nbsp;|&nbsp;</span><a href="https://www.linkedin.com/company/3155360?trk=tyah&amp;trkInfo=clickedVertical%3Acompany%2CclickedEntityId%3A3155360%2Cidx%3A2-1-2%2CtarId%3A1467015396545%2Ctas%3Aaerocana" style="color:rgb(17,85,204);font-size:12.8px" target="_blank"><img src="http://www.aerocanada.org/images/linkedin-36.png" style="font-size:12.8px"></a><span style="font-size:12.8px"></span><span style="font-size:12.8px"></span><a style="color:rgb(17,85,204);font-size:12.8px"></a><span style="font-size:12.8px">&nbsp;</span><br></p><p style="margin-bottom:0.0001pt;line-height: 15px;"><font size="1">FAA AC00-56A |&nbsp;UNGM 256670 |&nbsp;NATO CAGE L06T4<br></font><a href="http://www.aerocanada.org/index.php/fr/aero-canada-accueil/conditions-generales-de-vente" style="color:rgb(17,85,204);font-size:x-small" target="_blank">Conditions Generales Vente</a><span style="font-size:x-small;color:rgb(0,0,0)">&nbsp;</span><span style="font-size:x-small;color:rgb(0,0,0)">/</span><span style="font-size:x-small;color:rgb(0,0,0)">&nbsp;</span><a href="http://www.aerocanada.org/index.php/en/aero-canada/terms-of-sale" style="color:rgb(17,85,204);font-size:x-small" target="_blank">Terms of Sale</a></p><div><br></div></td></tr></tbody></table></div>


</td></tr><tr><td id="" align="center" style="padding:25px;font-size:12px;border:1px solid #BE0831"><a href="http://www.aerocanada.aero" target="_blank">www.aerocanada.aero</a> • phone: <a href="tel:+15148006223" value="+15148006223" target="_blank">+1 514 800 6223</a> • email <a href="sales@aerocanada.org" value="sales@aerocanada.org" target="_blank">sales@aerocanada.org</a> </td></tr></tbody></table></div>
</body></html>';

            // ===== BOUNDARY / SUJET / HEADERS (comme avant) =====
            $boundary = "-----=".md5(rand());

            $sujet = "ACI770 QUOTE - PN ".$_POST['Fld_Part_Nbr']." - ".$Fld_Condition_Text_aff." - ".$_POST['Fld_Part_Desc'];

            $header  = "From: \"".$dataeaci['Employee_Name']."\"<".$dataeaci['email'].">".$passage_ligne;
            $header .= "Reply-to: \"".$dataeaci['Employee_Name']."\" <".$dataeaci['email'].">".$passage_ligne;
            $header .= "MIME-Version: 1.0".$passage_ligne;
            $header .= "Content-Type: multipart/alternative;".$passage_ligne." boundary=\"$boundary\"".$passage_ligne;

            $message  = $passage_ligne."--".$boundary.$passage_ligne;
            $message .= "Content-Type: text/plain; charset=\"ISO-8859-1\"".$passage_ligne;
            $message .= "Content-Transfer-Encoding: 8bit".$passage_ligne;
            $message .= $passage_ligne.$message_txt.$passage_ligne;
            $message .= $passage_ligne."--".$boundary.$passage_ligne;
            $message .= "Content-Type: text/html; charset=\"ISO-8859-1\"".$passage_ligne;
            $message .= "Content-Transfer-Encoding: 8bit".$passage_ligne;
            $message .= $passage_ligne.$message_html.$passage_ligne;
            $message .= $passage_ligne."--".$boundary."--".$passage_ligne;
            $message .= $passage_ligne."--".$boundary."--".$passage_ligne;

            // Envoi mail désactivé comme avant
            // mail($mail,$sujet,$message,$header);
            // mail("lamalol@gmail.com",$sujet,$message,$header);

            // ===== AFFICHAGE POUR VALIDATION =====
            echo '<b>E-mail</b> : <input class="form-control" name="clientemail" value="'.$mail.'"><br><br>';
            echo '<b>E-mail CC</b> : <input class="form-control" name="emailcc"><br>';
            echo '<div class="form-group"><label>Subject</label><input class="form-control" name="sujet" value="'.$sujet.'"></div>';
            echo $message_html;
?>
    <input type="hidden" name="id_tbl_rfq1" value="<?php echo $id_tbl_rfq1; ?>">
    <input type="hidden" name="idrfq1" value="<?php echo $id_tbl_rfq1; ?>">
    <input type="hidden" name="Fld_Contact_Name" value="<?php echo $Fld_Contact_Name; ?>">
    <input type="hidden" name="selected_source_type" value="<?php echo isset($_POST['selected_source_type']) ? htmlspecialchars($_POST['selected_source_type'], ENT_QUOTES, 'UTF-8') : ''; ?>">
    <input type="hidden" name="selected_source_id" value="<?php echo isset($_POST['selected_source_id']) ? (int)$_POST['selected_source_id'] : ''; ?>">

    <textarea name="message_html2" style="height: 450px;" rows="10"><?php echo $message_html2; ?></textarea>
    <script>
        CKEDITOR.replace('message_html2');
    </script>

    <?php echo $message_html3; ?>

    <div class="form-group">
        <label>File input 1</label>
        <input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
        <input type="file" name="userfile" />
    </div>

    <div class="form-group">
        <label>File input 2</label>
        <input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
        <input type="file" name="userfile2" />
    </div>

    <div class="form-group">
        <label>File input 3</label>
        <input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
        <input type="file" name="userfile3" />
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="form-group" align="right">
                <!-- <input type="button" value="Save only" name="button1" onclick="return OnButton1();"> -->
            </div>
        </div>
        <div class="col-lg-4">
            <div class="form-group" align="right">
                <input type="button" value="Send email" name="button2" onclick="return OnButton2();">
            </div>
        </div>
    </div>

<?php
        } // end if $datar
    }     // end if $datarfq
}         // end else (RFQ_ID ok)
?>
</form>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.col-lg-12 -->
                </div>
            </div>
        </div>
    </div>

       <!-- JS vendors (copié d'une page qui marche) -->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="../vendor/metisMenu/metisMenu.min.js"></script>
    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
    <script src="../vendor/datatables-responsive/dataTables.responsive.js"></script>
    <script src="../dist/js/sb-admin-2.js"></script>

    <script type="text/javascript">
    // init DataTables uniquement si la table existe sur la page
    $(document).ready(function() {
        if ($('#dataTables-example').length) {
            $('#dataTables-example').DataTable({
                responsive: true
            });
        }
    });

    /************************************************************************
     *      DIFFERENTES ACTIONS SUR BOUTONS SUBMIT
     ************************************************************************/
    function OnButton1() {
        document.Form1.action = "validation-quotation-without-email.php";
        document.Form1.target = "_self";
        document.Form1.submit();
        return true;
    }

    function OnButton2() {
        document.Form1.action = "validation-send-email-quotation.php";
        document.Form1.target = "_self";
        document.Form1.submit();
        return true;
    }
    </script>


</body>
</html>

<?php
} else {
    echo '<meta http-equiv="refresh" content="0; url=login.php">';
}
?>
