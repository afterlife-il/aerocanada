<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
include_once "email_signature_helper.php";

if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
    echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php\">";
    exit;
}

if (!isset($_SESSION['statut']) || $_SESSION['statut'] !== "SuperAdmin") {
    echo "Access denied.";
    exit;
}

$keys = array(
    'company_logo_url',
    'signature_logo_url',
    'signer_name',
    'signer_name_show',
    'signer_name_label',
    'signer_title',
    'signer_title_show',
    'signer_title_label',
    'phone',
    'phone_show',
    'phone_label',
    'mobile',
    'mobile_show',
    'mobile_label',
    'fax',
    'fax_show',
    'fax_label',
    'email',
    'email_show',
    'email_label',
    'website',
    'website_show',
    'website_label',
    'skype',
    'skype_show',
    'skype_label',
    'address_html',
    'address_html_show',
    'address_html_label',
    'footer_text',
    'footer_text_show',
    'footer_text_label',
    'terms_url',
    'terms_url_show',
    'terms_url_label',
    'email_signature_html'
);

$saved = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updatedBy = aci_email_current_user_id();
    $companyId = aci_email_current_company_id($updatedBy);
    foreach ($keys as $key) {
        if (substr($key, -5) === '_show') {
            $value = isset($_POST[$key]) ? '1' : '0';
        } else {
            $value = $_POST[$key] ?? '';
        }
        $safeKey = escape_data($key);
        $safeValue = mysqli_real_escape_string($db_link, (string)$value);
        mysql2_query("INSERT INTO tbl_Email_Settings (user_id, company_id, setting_key, setting_value, updated_by, updated_at)
            VALUES ('".$updatedBy."', '".$companyId."', '".$safeKey."', '".$safeValue."', '".$updatedBy."', NOW())
            ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), updated_by=VALUES(updated_by), updated_at=VALUES(updated_at)");
    }
    $saved = true;
}

$settings = aci_email_settings();
$defaults = aci_email_default_settings();
if (!function_exists('aci_signature_setting_value')) {
    function aci_signature_setting_value($settings, $defaults, $key) {
        return htmlspecialchars(aci_email_render_setting($settings, $defaults, $key), ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('aci_signature_setting_checked')) {
    function aci_signature_setting_checked($settings, $defaults, $key) {
        return aci_email_setting_enabled($settings, $defaults, $key) ? ' checked' : '';
    }
}
if (!function_exists('aci_signature_field_options')) {
    function aci_signature_field_options($settings, $defaults, $fieldKey) {
        return '<div class="row" style="margin-bottom:5px;"><div class="col-xs-4"><label class="checkbox-inline"><input type="checkbox" name="'.$fieldKey.'_show" value="1"'.aci_signature_setting_checked($settings, $defaults, $fieldKey.'_show').'> Display</label></div><div class="col-xs-8"><input class="form-control input-sm" name="'.$fieldKey.'_label" placeholder="Label" value="'.aci_signature_setting_value($settings, $defaults, $fieldKey.'_label').'"></div></div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aerocanada-industries.com</title>
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/metisMenu/metisMenu.min.css" rel="stylesheet">
    <link href="../dist/css/sb-admin-2.css" rel="stylesheet">
    <link href="../dist/css/aci-overrides.css" rel="stylesheet">
    <link href="../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
</head>
<body>
<div id="wrapper">
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
        <?php include "top_menu.php"; ?>
        <?php if(isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
    </nav>
    <?php include "after_nav.php"; ?>
    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Email Signature Settings</h1>
                <?php if ($saved): ?>
                    <div class="alert alert-success">Email signature settings saved.</div>
                <?php endif; ?>
                <form method="post">
                    <div class="panel panel-default">
                        <div class="panel-heading">Quotation Email Signature</div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Logo URL</label>
                                        <input class="form-control" name="company_logo_url" value="<?php echo aci_signature_setting_value($settings, $defaults, 'company_logo_url'); ?>">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Signature Logo URL</label>
                                        <input class="form-control" name="signature_logo_url" value="<?php echo aci_signature_setting_value($settings, $defaults, 'signature_logo_url'); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-4"><div class="form-group"><label>Signer Name</label><?php echo aci_signature_field_options($settings, $defaults, 'signer_name'); ?><input class="form-control" name="signer_name" value="<?php echo aci_signature_setting_value($settings, $defaults, 'signer_name'); ?>"></div></div>
                                <div class="col-lg-4"><div class="form-group"><label>Signer Title</label><?php echo aci_signature_field_options($settings, $defaults, 'signer_title'); ?><input class="form-control" name="signer_title" value="<?php echo aci_signature_setting_value($settings, $defaults, 'signer_title'); ?>"></div></div>
                                <div class="col-lg-4"><div class="form-group"><label>Email</label><?php echo aci_signature_field_options($settings, $defaults, 'email'); ?><input class="form-control" name="email" value="<?php echo aci_signature_setting_value($settings, $defaults, 'email'); ?>"></div></div>
                            </div>
                            <div class="row">
                                <div class="col-lg-3"><div class="form-group"><label>Phone</label><?php echo aci_signature_field_options($settings, $defaults, 'phone'); ?><input class="form-control" name="phone" value="<?php echo aci_signature_setting_value($settings, $defaults, 'phone'); ?>"></div></div>
                                <div class="col-lg-3"><div class="form-group"><label>Mobile</label><?php echo aci_signature_field_options($settings, $defaults, 'mobile'); ?><input class="form-control" name="mobile" value="<?php echo aci_signature_setting_value($settings, $defaults, 'mobile'); ?>"></div></div>
                                <div class="col-lg-3"><div class="form-group"><label>Fax</label><?php echo aci_signature_field_options($settings, $defaults, 'fax'); ?><input class="form-control" name="fax" value="<?php echo aci_signature_setting_value($settings, $defaults, 'fax'); ?>"></div></div>
                                <div class="col-lg-3"><div class="form-group"><label>Skype</label><?php echo aci_signature_field_options($settings, $defaults, 'skype'); ?><input class="form-control" name="skype" value="<?php echo aci_signature_setting_value($settings, $defaults, 'skype'); ?>"></div></div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6"><div class="form-group"><label>Website</label><?php echo aci_signature_field_options($settings, $defaults, 'website'); ?><input class="form-control" name="website" value="<?php echo aci_signature_setting_value($settings, $defaults, 'website'); ?>"></div></div>
                                <div class="col-lg-6"><div class="form-group"><label>Terms Link</label><?php echo aci_signature_field_options($settings, $defaults, 'terms_url'); ?><input class="form-control" name="terms_url" value="<?php echo aci_signature_setting_value($settings, $defaults, 'terms_url'); ?>"></div></div>
                            </div>
                            <div class="form-group">
                                <label>Address HTML</label>
                                <?php echo aci_signature_field_options($settings, $defaults, 'address_html'); ?>
                                <textarea class="form-control" rows="4" name="address_html"><?php echo aci_signature_setting_value($settings, $defaults, 'address_html'); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Footer Text</label>
                                <?php echo aci_signature_field_options($settings, $defaults, 'footer_text'); ?>
                                <textarea class="form-control" rows="3" name="footer_text"><?php echo aci_signature_setting_value($settings, $defaults, 'footer_text'); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Full HTML Signature Override</label>
                                <textarea class="form-control" rows="10" name="email_signature_html" id="email_signature_html"><?php echo aci_signature_setting_value($settings, $defaults, 'email_signature_html'); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger">Save Email Signature Settings</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="../vendor/metisMenu/metisMenu.min.js"></script>
<script src="../dist/js/sb-admin-2.js"></script>
<script>CKEDITOR.replace('email_signature_html');</script>
</body>
</html>
