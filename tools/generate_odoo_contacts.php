<?php
// generate_odoo_contacts.php — Fusionne les données en un seul fichier Odoo-compatible avec enrichissements (contacts, employés, concurrents, paiements...)

ini_set('memory_limit', '1024M');
set_time_limit(0);

function connect_db() {
    $mysqli = new mysqli('localhost', 'odoo', 'Yoyamic@26', 'aerocanada', 3306, '/var/run/mysqld/mysqld.sock');
    if ($mysqli->connect_error) {
        die('Erreur de connexion MySQL: ' . $mysqli->connect_error);
    }
    $mysqli->set_charset("utf8mb4");
    return $mysqli;
}

function image_to_base64($filename) {
    if (!file_exists($filename) || is_dir($filename)) return '';
    $image_data = @file_get_contents($filename);
    if ($image_data === false) return '';
    $mime = mime_content_type($filename);
    return 'data:' . $mime . ';base64,' . base64_encode($image_data);
}

function export_odoo_contacts($mysqli) {
    $export_dir = __DIR__ . "/exports";
    if (!is_dir($export_dir)) {
        mkdir($export_dir, 0777, true);
    }
    $filepath = "$export_dir/odoo_contacts.csv";

    $query = "
        SELECT
            c.Fld_Company_Name AS name,
            cd.Fld_Company_Street AS street,
            cd.Fld_Company_City AS city,
            cd.Fld_Company_Country AS country,
            cd.Fld_Company_Phone AS phone,
            cd.Fld_Company_Fax AS mobile,
            cd.Fld_Company_Email AS email,
            '' AS website,
            ct.Fld_Company_Type_Text AS company_type,
            cb.Fld_Bank_Name AS bank_name,
            cb.Fld_Bank_Acct_Nbr AS iban,
            cc.Fld_Contact_Name AS contact_name,
            cc.Fld_Contact_Phone AS contact_phone,
            cc.Fld_Contact_Email AS contact_email,
            pm.Fld_Payment_Text AS payment_method,
            e.Employee_Name AS employee_name,
            e.email AS employee_email,
            comp.Fld_Company_Name AS competitor_name,
            c.logocompany,
            'Company' AS type,
            TRUE AS is_company,
            '' AS parent_id,
            cc.Fld_Contact_Title AS function,
            cc.Fld_Contact_Title AS title
        FROM tb_company c
        LEFT JOIN tbl_Company_Details cd ON c.Fld_Company_ID = cd.Fld_Company_ID
        LEFT JOIN tbl_Company_Type ct ON cd.Fld_Company_Type_ID = ct.Fld_Company_Type_ID
        LEFT JOIN tbl_Company_Bank_Account cb ON c.Fld_Company_ID = cb.Fld_Company_ID
        LEFT JOIN tb_company_contact cc ON c.Fld_Company_ID = cc.Fld_Company_ID
        LEFT JOIN tbl_Payment pm ON c.aci_payment_term_id = pm.Fld_Payment_Term_ID
        LEFT JOIN tbl_Employee e ON cc.id_company_contact = e.Fld_Contact_Id
        LEFT JOIN tbl_Competitor cp ON c.Fld_Company_ID = cp.Fld_Company_ID
        LEFT JOIN tb_company comp ON cp.Fld_Competitor_ID = comp.Fld_Company_ID
    ";

    $result = $mysqli->query($query);

    $f = fopen($filepath, "w");
    if (!$f) {
        die("Erreur : impossible d'écrire dans $filepath");
    }

    $header = [
        'Name', 'Street', 'City', 'Country', 'Phone', 'Mobile', 'Email', 'Website',
        'Company Type', 'IBAN', 'Bank Name', 'Contact Person', 'Contact Phone', 'Contact Email',
        'Payment Method', 'Employee Name', 'Employee Email', 'Competitor', 'Logo (base64)',
        'Type', 'Is Company', 'Parent ID', 'Function', 'Job Title'
    ];
    fputcsv($f, $header);

    while ($row = $result->fetch_assoc()) {
        $logo_path = "/var/www/vhosts/aerocanada-industries.com/httpdocs/logo_company/" . trim($row['logocompany']);
        $row_data = [
            $row['name'],
            $row['street'],
            $row['city'],
            $row['country'],
            $row['phone'],
            $row['mobile'],
            $row['email'],
            $row['website'],
            $row['company_type'],
            $row['iban'],
            $row['bank_name'],
            $row['contact_name'],
            $row['contact_phone'],
            $row['contact_email'],
            $row['payment_method'],
            $row['employee_name'],
            $row['employee_email'],
            $row['competitor_name'],
            image_to_base64($logo_path),
            $row['type'],
            $row['is_company'],
            $row['parent_id'],
            $row['function'],
            $row['title']
        ];
        fputcsv($f, $row_data);
    }

    fclose($f);
    echo "\n✅ Fichier Odoo enrichi prêt : odoo_contacts.csv\n";
}

$mysqli = connect_db();
export_odoo_contacts($mysqli);
$mysqli->close();
