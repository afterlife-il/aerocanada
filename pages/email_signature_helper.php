<?php

function aci_email_default_settings($employee = array()) {
    return array(
        'company_logo_url' => 'https://www.aerocanada-industries.com/yoyamic/pages/images/logo-aei-email.png',
        'signature_logo_url' => 'https://www.aerocanada-industries.com/yoyamic/pages/images/logo-aei-email.png',
        'signer_name' => $employee['Employee_Name'] ?? '',
        'signer_name_show' => '1',
        'signer_name_label' => 'Name',
        'signer_title' => $employee['position'] ?? '',
        'signer_title_show' => '1',
        'signer_title_label' => 'Title',
        'phone' => !empty($employee['tel']) ? $employee['tel'] : '+1 514 800 6223',
        'phone_show' => '1',
        'phone_label' => 'Phone',
        'mobile' => $employee['mobile'] ?? '',
        'mobile_show' => '1',
        'mobile_label' => 'Mobile',
        'fax' => '+1 514 800 6224',
        'fax_show' => '1',
        'fax_label' => 'Fax',
        'email' => $employee['email'] ?? 'sales@aerocanada.org',
        'email_show' => '1',
        'email_label' => 'Email',
        'website' => 'http://www.aerocanada.aero',
        'website_show' => '1',
        'website_label' => 'Website',
        'skype' => $employee['skype'] ?? '',
        'skype_show' => '1',
        'skype_label' => 'Skype',
        'address_html' => '99, Prince Street, 7th Floor, Suite#701<br>Montreal QC H3C 2M7, Canada',
        'address_html_show' => '1',
        'address_html_label' => 'Address',
        'footer_text' => 'FAA AC00-56A |&nbsp;UNGM 256670 |&nbsp;NATO CAGE L06T4',
        'footer_text_show' => '1',
        'footer_text_label' => 'Footer',
        'terms_url' => 'http://www.aerocanada.org/index.php/en/aero-canada/terms-of-sale',
        'terms_url_show' => '1',
        'terms_url_label' => 'Terms',
        'email_signature_html' => ''
    );
}

function aci_email_current_user_id() {
    if (isset($_SESSION['id_utilisateur'])) return (int)$_SESSION['id_utilisateur'];
    if (isset($_SESSION['Employee_ID'])) return (int)$_SESSION['Employee_ID'];
    return 0;
}

function aci_email_current_company_id($userId = null) {
    if (isset($_SESSION['email_signature_company_id'])) return (int)$_SESSION['email_signature_company_id'];
    if (isset($_SESSION['company_id'])) return (int)$_SESSION['company_id'];
    if (isset($_SESSION['Fld_Company_ID'])) return (int)$_SESSION['Fld_Company_ID'];
    if ($userId === null) $userId = aci_email_current_user_id();
    $userId = (int)$userId;
    if ($userId <= 0) return 0;

    $req = @mysql2_query("SELECT c.Fld_Company_ID
        FROM tbl_Employee e
        LEFT JOIN tb_company_contact c ON c.id_company_contact = e.Fld_Contact_Id
        WHERE e.Employee_ID = ".$userId."
        LIMIT 1");
    if ($req && ($row = mysqli_fetch_assoc($req))) {
        return (int)$row['Fld_Company_ID'];
    }
    return 0;
}

function aci_email_current_employee_profile($userId = null) {
    if ($userId === null) $userId = aci_email_current_user_id();
    $userId = (int)$userId;
    if ($userId <= 0) return array();
    $req = @mysql2_query("SELECT * FROM tbl_Employee WHERE Employee_ID = ".$userId." LIMIT 1");
    if ($req && ($row = mysqli_fetch_assoc($req))) {
        return $row;
    }
    return array();
}

function aci_email_profile_setting_keys() {
    return array('signer_name', 'signer_title', 'phone', 'mobile', 'email', 'skype');
}

function aci_email_settings($userId = null) {
    static $settingsCache = array();
    if ($userId === null) $userId = aci_email_current_user_id();
    $userId = (int)$userId;
    $companyId = aci_email_current_company_id($userId);
    $cacheKey = $userId.'-'.$companyId;
    if (isset($settingsCache[$cacheKey])) return $settingsCache[$cacheKey];

    $settings = array();
    $fallbackSettings = array();
    $req = @mysql2_query("SELECT setting_key, setting_value, user_id, company_id, is_company_default, is_global_default
        FROM tbl_Email_Settings
        WHERE (user_id = ".$userId." AND company_id = ".$companyId.")
           OR (user_id = ".$userId." AND company_id = 0)
           OR (company_id = ".$companyId." AND is_company_default = 1)
           OR (company_id = 0 AND is_global_default = 1)
        ORDER BY
            CASE
                WHEN company_id = 0 AND is_global_default = 1 THEN 1
                WHEN company_id = ".$companyId." AND is_company_default = 1 THEN 2
                WHEN user_id = ".$userId." AND company_id = 0 THEN 3
                WHEN user_id = ".$userId." AND company_id = ".$companyId." THEN 4
                ELSE 5
            END ASC");
    if ($req) {
        while ($row = mysqli_fetch_assoc($req)) {
            if ((int)$row['user_id'] !== $userId
                && (int)$row['is_company_default'] !== 1
                && (int)$row['is_global_default'] !== 1) {
                continue;
            }
            if ((int)$row['user_id'] === $userId) {
                $settings[$row['setting_key']] = $row['setting_value'];
            } else {
                $fallbackSettings[$row['setting_key']] = $row['setting_value'];
            }
        }
    }
    $settings['__fallback_settings'] = $fallbackSettings;
    $settingsCache[$cacheKey] = $settings;
    return $settings;
}

function aci_email_setting($settings, $defaults, $key) {
    if (isset($settings[$key]) && trim((string)$settings[$key]) !== '') {
        return $settings[$key];
    }
    if (in_array($key, aci_email_profile_setting_keys(), true) && trim((string)($defaults[$key] ?? '')) !== '') {
        return $defaults[$key];
    }
    if (isset($settings['__fallback_settings'][$key]) && trim((string)$settings['__fallback_settings'][$key]) !== '') {
        return $settings['__fallback_settings'][$key];
    }
    return $defaults[$key] ?? '';
}

function aci_email_render_setting($settings, $defaults, $key) {
    if (array_key_exists($key, $settings)) {
        return (string)$settings[$key];
    }
    if (in_array($key, aci_email_profile_setting_keys(), true) && trim((string)($defaults[$key] ?? '')) !== '') {
        return (string)$defaults[$key];
    }
    if (array_key_exists('__fallback_settings', $settings) && array_key_exists($key, $settings['__fallback_settings'])) {
        return (string)$settings['__fallback_settings'][$key];
    }
    return (string)($defaults[$key] ?? '');
}

function aci_email_setting_enabled($settings, $defaults, $key) {
    $value = strtolower(trim(aci_email_render_setting($settings, $defaults, $key)));
    return !in_array($value, array('', '0', 'false', 'no', 'off'), true);
}

function aci_email_signature_row($settings, $defaults, $key, $value, $isHtml = false) {
    if (!aci_email_setting_enabled($settings, $defaults, $key.'_show')) return '';
    $value = (string)$value;
    if (trim($value) === '') return '';
    $label = trim(aci_email_render_setting($settings, $defaults, $key.'_label'));
    $display = $isHtml ? $value : htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    if ($label === '') {
        return '<tr><td colspan="2" style="padding:1px 0;vertical-align:top;">'.$display.'</td></tr>';
    }
    $labelDisplay = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    return '<tr><td style="font-weight:bold;padding:1px 8px 1px 0;white-space:nowrap;vertical-align:top;">'.$labelDisplay.':</td><td style="padding:1px 0;vertical-align:top;">'.$display.'</td></tr>';
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
    $currentEmployee = aci_email_current_employee_profile();
    if (empty($currentEmployee)) $currentEmployee = $employee;
    $defaults = aci_email_default_settings($currentEmployee);

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
    $rows .= aci_email_signature_row($settings, $defaults, 'signer_name', $signerName);
    $rows .= aci_email_signature_row($settings, $defaults, 'signer_title', $signerTitle);
    $rows .= aci_email_signature_row($settings, $defaults, 'phone', $phone);
    $rows .= aci_email_signature_row($settings, $defaults, 'mobile', $mobile);
    $rows .= aci_email_signature_row($settings, $defaults, 'fax', $fax);
    $rows .= aci_email_signature_row($settings, $defaults, 'email', $email);
    $rows .= aci_email_signature_row($settings, $defaults, 'website', $website);
    $rows .= aci_email_signature_row($settings, $defaults, 'skype', $skype);
    $rows .= aci_email_signature_row($settings, $defaults, 'address_html', $addressHtml, true);
    $rows .= aci_email_signature_row($settings, $defaults, 'footer_text', $footerText, true);
    $rows .= aci_email_signature_row($settings, $defaults, 'terms_url', $termsUrl);

    return '<div dir="ltr" style="font-size:small"><br><table border="0" cellspacing="0" cellpadding="0" width="560" style="width:420pt;border-collapse:collapse;border:none"><tbody><tr><td width="108" valign="top" style="width:81pt;border-top:none;border-bottom:none;border-left:none;border-right:1pt solid windowtext;padding:0in 5.4pt"><p align="center" style="margin-bottom:0.0001pt;text-align:center">'.$logoHtml.'</p></td><td width="452" valign="top" style="width:339pt;border:none;padding:0in 5.4pt"><table border="0" cellspacing="0" cellpadding="0" style="border-collapse:collapse;font-family:Arial,sans-serif;font-size:9pt;line-height:15px;">'.$rows.'</table></td></tr></tbody></table></div>

'.$footer;
}

function aci_quote_email_footer_html($settings, $defaults) {
    return '</td></tr></tbody></table></div>
</body></html>';
}
