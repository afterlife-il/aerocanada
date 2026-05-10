<?php

function aci_email_default_settings($employee = array()) {
    return array(
        'company_logo_url' => 'https://www.aerocanada-industries.com/yoyamic/pages/images/logo-aei-email.png',
        'signature_logo_url' => 'https://www.aerocanada-industries.com/yoyamic/pages/images/logo-aei-email.png',
        'signer_name' => $employee['Employee_Name'] ?? '',
        'signer_title' => $employee['position'] ?? '',
        'phone' => '+1 514 800 6223',
        'mobile' => $employee['mobile'] ?? '',
        'fax' => '+1 514 800 6224',
        'email' => $employee['email'] ?? 'sales@aerocanada.org',
        'website' => 'http://www.aerocanada.aero',
        'skype' => $employee['skype'] ?? '',
        'address_html' => '99, Prince Street, 7th Floor, Suite#701<br>Montreal QC H3C 2M7, Canada',
        'footer_text' => 'FAA AC00-56A |&nbsp;UNGM 256670 |&nbsp;NATO CAGE L06T4',
        'terms_url' => 'http://www.aerocanada.org/index.php/en/aero-canada/terms-of-sale',
        'email_signature_html' => ''
    );
}

function aci_email_current_user_id() {
    if (isset($_SESSION['id_utilisateur'])) return (int)$_SESSION['id_utilisateur'];
    if (isset($_SESSION['Employee_ID'])) return (int)$_SESSION['Employee_ID'];
    return 0;
}

function aci_email_settings($userId = null) {
    static $settingsCache = array();
    if ($userId === null) $userId = aci_email_current_user_id();
    $userId = (int)$userId;
    if (isset($settingsCache[$userId])) return $settingsCache[$userId];

    $settings = array();
    $req = @mysql2_query("SELECT setting_key, setting_value, user_id FROM tbl_Email_Settings WHERE user_id IN (0, ".$userId.") ORDER BY user_id ASC");
    if ($req) {
        while ($row = mysqli_fetch_assoc($req)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    $settingsCache[$userId] = $settings;
    return $settings;
}

function aci_email_setting($settings, $defaults, $key) {
    if (isset($settings[$key]) && trim((string)$settings[$key]) !== '') {
        return $settings[$key];
    }
    return $defaults[$key] ?? '';
}

function aci_quote_email_header_html($settings = null) {
    if ($settings === null) $settings = aci_email_settings();
    $defaults = aci_email_default_settings();
    $logo = htmlspecialchars(aci_email_setting($settings, $defaults, 'company_logo_url'), ENT_QUOTES, 'UTF-8');

    return '<html><head></head><body>
<div style="font-family:sans-serif;color:#000000;font-size:16px;margin:0px;padding:20px;">
<table id="" style="border-collapse:collapse;border:1px solid #BE0831;background:#e9e9e9;color:#000000;"><tbody><tr style="height:100px"><td id="" style="padding:25px;border:1px solid #BE0831"><img src="'.$logo.'" width="83" height="96"></td></tr><tr><td id="" valign="top" style="padding:25px;border:1px solid #BE0831;line-height:24px">';
}

function aci_quote_email_signature_html($employee = array(), $settings = null) {
    if ($settings === null) $settings = aci_email_settings();
    $defaults = aci_email_default_settings($employee);

    $override = trim((string)aci_email_setting($settings, $defaults, 'email_signature_html'));
    $signatureLogo = htmlspecialchars(aci_email_setting($settings, $defaults, 'signature_logo_url'), ENT_QUOTES, 'UTF-8');
    $signerName = htmlspecialchars(aci_email_setting($settings, $defaults, 'signer_name'), ENT_QUOTES, 'UTF-8');
    $signerTitle = htmlspecialchars(aci_email_setting($settings, $defaults, 'signer_title'), ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars(aci_email_setting($settings, $defaults, 'phone'), ENT_QUOTES, 'UTF-8');
    $mobile = htmlspecialchars(aci_email_setting($settings, $defaults, 'mobile'), ENT_QUOTES, 'UTF-8');
    $fax = htmlspecialchars(aci_email_setting($settings, $defaults, 'fax'), ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars(aci_email_setting($settings, $defaults, 'email'), ENT_QUOTES, 'UTF-8');
    $website = htmlspecialchars(aci_email_setting($settings, $defaults, 'website'), ENT_QUOTES, 'UTF-8');
    $skype = htmlspecialchars(aci_email_setting($settings, $defaults, 'skype'), ENT_QUOTES, 'UTF-8');
    $addressHtml = aci_email_setting($settings, $defaults, 'address_html');
    $footerText = aci_email_setting($settings, $defaults, 'footer_text');
    $termsUrl = htmlspecialchars(aci_email_setting($settings, $defaults, 'terms_url'), ENT_QUOTES, 'UTF-8');

    $footer = aci_quote_email_footer_html($settings, $defaults);
    if ($override !== '') {
        return $override.$footer;
    }

    return '<div dir="ltr" style="font-size:small"><br><table border="0" cellspacing="0" cellpadding="0" width="479" style="width:359.55pt;border-collapse:collapse;border:none"><tbody><tr><td width="108" valign="top" style="width:81pt;border-top:none;border-bottom:none;border-left:none;border-right:1pt solid windowtext;padding:0in 5.4pt"><p align="center" style="margin-bottom:0.0001pt;text-align:center"><img src="'.$signatureLogo.'" width="83" height="96"></p></td><td width="371" valign="top" style="width:278.55pt;border:none;padding:0in 5.4pt"><p style="margin-bottom:0.0001pt;line-height:15px;"><b style="font-size:12.8px"><span lang="EN-US" style="font-family:Arial,sans-serif">'.$signerName.'<br></span></b><span style="font-family:Arial,sans-serif;font-size:9pt">'.$signerTitle.' | AeroCanada Industries 770 Inc.</span></p><p style="font-size:12.8px"><i><span style="font-family:Americana"><b><font size="2">Your Perfect Choice For Aviation&nbsp;Solutions</font></b></span></i></p><p style="margin-bottom:0.0001pt;line-height:15px;"><span style="font-family:Arial,sans-serif;font-size:9pt">dir. '.$phone.($mobile !== '' ? ' | mob.&nbsp;'.$mobile : '').'<br></span><span style="font-family:Arial,sans-serif;font-size:9pt">tel. '.$phone.' | fax. '.$fax.'<br></span><a href="mailto:'.$email.'" style="color:rgb(17,85,204);font-family:Arial,sans-serif;font-size:9pt" target="_blank">'.$email.'</a><span style="font-family:Arial,sans-serif;font-size:9pt">&nbsp;|&nbsp;</span><a href="'.$website.'" style="color:rgb(17,85,204);font-family:Arial,sans-serif;font-size:9pt" target="_blank">'.$website.'</a><br>'.($skype !== '' ? '<span style="font-family:Arial,sans-serif;font-size:9pt"><b>Skype:</b>&nbsp;'.$skype.'</span>' : '').'</p><p style="margin-bottom:0.0001pt;line-height:15px;">'.$addressHtml.'</p><p style="margin-bottom:0.0001pt;line-height:15px;"><font size="1">'.$footerText.'<br></font><a href="'.$termsUrl.'" style="color:rgb(17,85,204);font-size:x-small" target="_blank">Terms of Sale</a></p><div><br></div></td></tr></tbody></table></div>

'.$footer;
}

function aci_quote_email_footer_html($settings, $defaults) {
    $website = htmlspecialchars(aci_email_setting($settings, $defaults, 'website'), ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars(aci_email_setting($settings, $defaults, 'phone'), ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars(aci_email_setting($settings, $defaults, 'email'), ENT_QUOTES, 'UTF-8');

    return '</td></tr><tr><td id="" align="center" style="padding:25px;font-size:12px;border:1px solid #BE0831"><a href="'.$website.'" target="_blank">'.$website.'</a> &bull; phone: <a href="tel:'.preg_replace('/[^0-9+]/', '', $phone).'" target="_blank">'.$phone.'</a> &bull; email <a href="mailto:'.$email.'" target="_blank">'.$email.'</a></td></tr></tbody></table></div>
</body></html>';
}
