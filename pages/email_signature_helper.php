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

function aci_email_render_setting($settings, $defaults, $key) {
    if (array_key_exists($key, $settings)) {
        return (string)$settings[$key];
    }
    return (string)($defaults[$key] ?? '');
}

function aci_email_signature_row($label, $value, $isHtml = false) {
    $value = (string)$value;
    if (trim($value) === '') return '';
    $display = $isHtml ? $value : htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    return '<tr><td style="font-weight:bold;padding:1px 8px 1px 0;white-space:nowrap;vertical-align:top;">'.$label.'</td><td style="padding:1px 0;vertical-align:top;">'.$display.'</td></tr>';
}

function aci_quote_email_header_html($settings = null) {
    if ($settings === null) $settings = aci_email_settings();
    $defaults = aci_email_default_settings();
    $logo = htmlspecialchars(aci_email_render_setting($settings, $defaults, 'company_logo_url'), ENT_QUOTES, 'UTF-8');
    $logoHtml = $logo !== '' ? '<img src="'.$logo.'" width="83" height="96">' : '';

    return '<html><head></head><body>
<div style="font-family:sans-serif;color:#000000;font-size:16px;margin:0px;padding:20px;">
<table id="" style="border-collapse:collapse;border:1px solid #BE0831;background:#e9e9e9;color:#000000;"><tbody><tr style="height:100px"><td id="" style="padding:25px;border:1px solid #BE0831">'.$logoHtml.'</td></tr><tr><td id="" valign="top" style="padding:25px;border:1px solid #BE0831;line-height:24px">';
}

function aci_quote_email_signature_html($employee = array(), $settings = null) {
    if ($settings === null) $settings = aci_email_settings();
    $defaults = aci_email_default_settings($employee);

    $override = trim((string)aci_email_setting($settings, $defaults, 'email_signature_html'));
    $signatureLogo = htmlspecialchars(aci_email_render_setting($settings, $defaults, 'signature_logo_url'), ENT_QUOTES, 'UTF-8');
    $signerName = aci_email_render_setting($settings, $defaults, 'signer_name');
    $signerTitle = aci_email_render_setting($settings, $defaults, 'signer_title');
    $phone = aci_email_render_setting($settings, $defaults, 'phone');
    $mobile = aci_email_render_setting($settings, $defaults, 'mobile');
    $fax = aci_email_render_setting($settings, $defaults, 'fax');
    $email = aci_email_render_setting($settings, $defaults, 'email');
    $website = aci_email_render_setting($settings, $defaults, 'website');
    $skype = aci_email_render_setting($settings, $defaults, 'skype');
    $addressHtml = aci_email_render_setting($settings, $defaults, 'address_html');
    $footerText = aci_email_render_setting($settings, $defaults, 'footer_text');
    $termsUrl = aci_email_render_setting($settings, $defaults, 'terms_url');

    $footer = aci_quote_email_footer_html($settings, $defaults);
    if ($override !== '') {
        return $override.$footer;
    }

    $logoHtml = $signatureLogo !== '' ? '<img src="'.$signatureLogo.'" width="83" height="96">' : '';
    $rows = '';
    $rows .= aci_email_signature_row('Name', $signerName);
    $rows .= aci_email_signature_row('Title', $signerTitle);
    $rows .= aci_email_signature_row('Phone', $phone);
    $rows .= aci_email_signature_row('Mobile', $mobile);
    $rows .= aci_email_signature_row('Fax', $fax);
    $rows .= aci_email_signature_row('Email', $email);
    $rows .= aci_email_signature_row('Website', $website);
    $rows .= aci_email_signature_row('Skype', $skype);
    $rows .= aci_email_signature_row('Address', $addressHtml, true);
    $rows .= aci_email_signature_row('Footer', $footerText, true);
    $rows .= aci_email_signature_row('Terms', $termsUrl);

    return '<div dir="ltr" style="font-size:small"><br><table border="0" cellspacing="0" cellpadding="0" width="560" style="width:420pt;border-collapse:collapse;border:none"><tbody><tr><td width="108" valign="top" style="width:81pt;border-top:none;border-bottom:none;border-left:none;border-right:1pt solid windowtext;padding:0in 5.4pt"><p align="center" style="margin-bottom:0.0001pt;text-align:center">'.$logoHtml.'</p></td><td width="452" valign="top" style="width:339pt;border:none;padding:0in 5.4pt"><table border="0" cellspacing="0" cellpadding="0" style="border-collapse:collapse;font-family:Arial,sans-serif;font-size:9pt;line-height:15px;">'.$rows.'</table></td></tr></tbody></table></div>

'.$footer;
}

function aci_quote_email_footer_html($settings, $defaults) {
    return '</td></tr></tbody></table></div>
</body></html>';
}
