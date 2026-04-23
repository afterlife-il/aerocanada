<?php
require_once 'bootstrap.php';
require_auth();

header('Content-Type: application/json; charset=utf-8');

try {
    // Compat GET/POST
    $companyname  = $_GET['companyname']  ?? $_POST['companyname']  ?? '';
    $contactname  = $_GET['contactname']  ?? $_POST['contactname']  ?? '';
    $contactemail = $_GET['contactemail'] ?? $_POST['contactemail'] ?? '';
    $aci          = isset($_SESSION['id_utilisateur']) ? (int)$_SESSION['id_utilisateur'] : 0;

    if ($companyname === '') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Missing companyname']);
        exit;
    }

    // Sécurité: échapper avec la connexion officielle de conf.php
    $esc = fn($s) => escape_data($s);

    // 1) Insert company
    $sql1 = "INSERT INTO tb_company (Fld_Company_Name, aci_contact)
             VALUES ('".$esc($companyname)."', '$aci')";
    mysql2_query($sql1);

    // Récupérer le nouvel ID via helper conf.php
    $lastid = mysql2_insert_id();

    // 2) Insert détails par défaut
    $sql2 = "INSERT INTO tbl_Company_Details
             (Fld_Linked_ID, Fld_Company_ID, Fld_Company_Type_ID, Fld_Company_Country, Fld_Company_City,
              Fld_Company_State, Fld_Company_Street, Fld_Company_ZipCode, Fld_Company_Fax, Fld_Company_Phone,
              Fld_Company_Email, Fld_Company_Score, Fld_Company_BAX_Contact, Fld_Remark, Fld_VAT_Nbr,
              Fld_Date_Of_First_Contact, Fld_Company_Address_Type, UTC_timezone, title_address)
             VALUES ('', '$lastid', '', 'NO COUNTRY', 'NO CITY', '', 'No address', '', '', '', '',
                     '', '$aci', 'No remark', '', '', '', '', '')";
    mysql2_query($sql2);

    // 3) Optionnel: créer un premier contact
    if ($contactname !== '') {
        $today = date("Y-m-d");
        $sql3 = "INSERT INTO tb_company_contact
                 (Fld_Linked_ID, Fld_Company_ID, Company_Old_Id, Fld_Contact_Name,
                  Fld_Contact_Phone, Fld_Contact_Phone2, Fld_Contact_Fax, Fld_Company_Mobile,
                  Fld_Contact_Division_ID, Fld_Contact_Email, Fld_Contact_Title, Fld_Contact_Remark,
                  status, aci_contact, entry_date)
                 VALUES
                 ('', '$lastid', '', '".$esc($contactname)."', '', '', '', '',
                  '', '".$esc($contactemail)."', '', '', 'available', '$aci', '$today')";
        mysql2_query($sql3);
    }

    echo json_encode(['ok' => true, 'company_id' => $lastid, 'company_name' => $companyname]);

} catch (Throwable $e) {
    log_error("add_company_from_popup FAILED: ".$e->getMessage(), [
        'user' => $_SESSION['nom_utilisateur'] ?? 'unknown',
        'get'  => $_GET,
        'post' => $_POST,
    ]);
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error']);
}