<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

// Sécurisation basique des entrées
$company_id    = isset($_POST['company_id'])    ? trim($_POST['company_id'])    : '';
$contact_name  = isset($_POST['contact_name'])  ? trim($_POST['contact_name'])  : '';
$contact_email = isset($_POST['contact_email']) ? trim($_POST['contact_email']) : '';
$contact_phone = isset($_POST['contact_phone']) ? trim($_POST['contact_phone']) : '';

if ($company_id === '' || $contact_name === '') {
    http_response_code(400);
    echo "Missing required fields.";
    exit;
}

$company_id_esc    = mysqli_real_escape_string($conn, $company_id);
$contact_name_esc  = mysqli_real_escape_string($conn, $contact_name);
$contact_email_esc = mysqli_real_escape_string($conn, $contact_email);
$contact_phone_esc = mysqli_real_escape_string($conn, $contact_phone);

$today = date("Y-m-d");
$aci   = isset($_SESSION['id_utilisateur']) ? (int)$_SESSION['id_utilisateur'] : 0;

// Harmoniser la casse à 'available'
$sql = "
INSERT INTO tb_company_contact
  (Fld_Linked_ID, Fld_Company_ID, Company_Old_Id,
   Fld_Contact_Name, Fld_Contact_Phone, Fld_Contact_Phone2, Fld_Contact_Fax,
   Fld_Company_Mobile, Fld_Contact_Division_ID, Fld_Contact_Email, Fld_Contact_Title,
   Fld_Contact_Remark, status, aci_contact, entry_date)
VALUES
  ('', '$company_id_esc', '',
   '$contact_name_esc', '$contact_phone_esc', '', '',
   '', '', '$contact_email_esc', '',
   '', 'available', '$aci', '$today')
";

$ok = mysql2_query($sql);

if (!$ok) {
    http_response_code(500);
    echo "DB error";
    exit;
}

echo "OK";
