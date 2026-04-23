<?php
// export_ready2go_odoo.php
// A placer dans /var/www/vhosts/aerocanada-industries.com/httpdocs/tools/

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

function export_table($mysqli, $table, $filename) {
    $export_dir = __DIR__ . "/exports";
    if (!is_dir($export_dir)) {
        mkdir($export_dir, 0777, true);
    }
    $filepath = "$export_dir/$filename";

    $query = "SELECT * FROM $table";
    $result = $mysqli->query($query);

    echo "\nDossier de destination : $export_dir";
    echo "\nPermission sur le dossier : " . decoct(fileperms($export_dir) & 0777);
    echo "\nPropriétaire : " . posix_getpwuid(fileowner($export_dir))['name'];
    echo "\nGroupe : " . posix_getgrgid(filegroup($export_dir))['name'];

    $f = fopen($filepath, "w");
    if (!$f) {
        die("Erreur : impossible d'écrire dans $filepath");
    }
    $header_written = false;

    while ($row = $result->fetch_assoc()) {
        if (!$header_written) {
            if ($table === 'tb_company') {
                $row['logo_base64'] = '';
            }
            fputcsv($f, array_keys($row));
            $header_written = true;
        }

        if ($table === 'tb_company') {
            $logo_file = trim($row['logocompany']);
            $logo_path = "/var/www/vhosts/aerocanada-industries.com/httpdocs/logo_company/" . $logo_file;
            $row['logo_base64'] = image_to_base64($logo_path);
        }

        fputcsv($f, $row);
    }
    fclose($f);
    echo "\n✔ Exporté : $filename";
}

$mysqli = connect_db();

$tables = [
    'tb_company' => 'export_companies.csv',
    'tbl_Employee' => 'export_employees.csv',
    'tbl_RFQ' => 'export_rfq.csv',
    'tbl_Payment' => 'export_payment.csv',
    'tbl_Fleet' => 'export_fleet.csv',
    'tbl_Competitor' => 'export_competitor.csv',
    'tbl_Company_Type' => 'export_company_type.csv',
    'tbl_Company_Details' => 'export_company_details.csv',
    'tbl_Company_Bank_Account' => 'export_company_bank_account.csv',
    'tbl_capa_list' => 'export_capa_list.csv',
    'tbl_Bank' => 'export_bank.csv',
    'tbl_Forwarder' => 'export_forwarder.csv',
    'tb_company_contact' => 'export_company_contact.csv',
    'tbl_Division' => 'export_division.csv'
];

foreach ($tables as $table => $file) {
    export_table($mysqli, $table, $file);
}

$mysqli->close();

echo "\n\n✅ Export terminé avec succès.\n";

// Création d'un ZIP de tous les fichiers CSV exportés
$zip = new ZipArchive();
$zip_path = __DIR__ . "/exports/export_ready2go.zip";

if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
    foreach ($tables as $table => $file) {
        $file_path = __DIR__ . "/exports/$file";
        if (file_exists($file_path)) {
            $zip->addFile($file_path, $file);
        }
    }
    $zip->close();
    echo "\n📦 Archive ZIP créée : exports/export_ready2go.zip\n";
} else {
    echo "\n❌ Erreur lors de la création du fichier ZIP.\n";
}
?>
