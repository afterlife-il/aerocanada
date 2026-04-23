<?php
// add_capa_list.php

// 1) PhpSpreadsheet (pour Excel)
require_once __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// 2) Session + includes ACI
session_start();
include_once "conf.php";
include_once "page_titles.php";

if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
    echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=".$_SERVER['REQUEST_URI']."\">";
    exit;
}

// (optionnel pour debug — à enlever si ça t’affiche trop d’infos)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// =======================================================
// 1. TEMPLATE CSV (ouvre-le avec Excel)
// =======================================================
if (isset($_GET['download']) && $_GET['download'] === "template") {

    $filename = "capa_list_template.csv";

    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"".$filename."\"");

    $output = fopen("php://output", "w");

    fputcsv($output, array(
        'PN',
        'DESCRIPTION',
        'AIRCRAFT',
        'ATA',
        'CAPABILITY',
        'PMA',
        'DOA',
        'DER',
        'CODE_OEM',
        'DESIGN_OEM'
    ));

    fclose($output);
    exit;
}

// =======================================================
// 2. LISTE DES PROVIDERS (tb_company)
// =======================================================
$providers = array();
if (isset($db_link) && $db_link) {
    $sql = "
        SELECT Fld_Company_ID, Fld_Company_Name
        FROM tb_company
        WHERE Fld_Company_Name <> ''
        ORDER BY Fld_Company_Name ASC
    ";
    if ($res = mysqli_query($db_link, $sql)) {
        while ($row = mysqli_fetch_assoc($res)) {
            $providers[] = $row;
        }
    }
}

// =======================================================
// 3. FONCTIONS DE MAPPING INTELLIGENT
// =======================================================

/**
 * Normalise un nom de colonne : majuscules + on enlève tout ce qui n’est pas A-Z0-9
 */
function normalize_header_name($name) {
    $upper = strtoupper(trim($name));
    $norm  = preg_replace('/[^A-Z0-9]/', '', $upper);
    return $norm;
}

/**
 * Construit la map logique -> index colonne à partir de la ligne de header.
 * Exemple : 'Vendor PN' → 'PN', 'Designation' → 'DESCRIPTION', etc.
 *
 * @param array $header  tableau brut des noms de colonnes
 * @return array [$map, $missingCritical]
 *         - $map: ['PN' => 0, 'DESCRIPTION' => 1, ...]
 *         - $missingCritical: liste de champs critiques non trouvés (aujourd’hui : PN seulement)
 */
function buildHeaderMapSmart($header) {

    // Synonymes (normalisés) pour chaque champ logique
    $synonyms = array(
        'PN' => array(
            'PN','P/N','PARTNUMBER','PARTNBR','PARTNO','PARTNUM',
            'VENDORPN','VENDORPARTNUMBER','VENDORPARTNBR','VENDORPARTNO',
            'SUPPLIERPN','SUPPLIERP/N','SUPPLIERPARTNUMBER','SUPPLIERPARTNO',
            'ENGINEPN','ENGINEMANUFACTURERPNORALTERNATEPN','MANUFACTURERPN',
            'TYPEDEMATERIELPARTNUMBER','NUMERODEPIECE','ITEM','ITEMPN'
        ),
        'DESCRIPTION' => array(
            'DESCRIPTION','DESIGNATION','PARTDESCRIPTION','DESIGNATIONDESIGNATION',
            'DESIGNATIONDESIGNATION','ITEMDESCRIPTION','DESCRIP','DESC','DESIGNATIONFRANCAISE'
        ),
        'AIRCRAFT' => array(
            'AIRCRAFT','AIRCRAFTTYPE','AIRCRAFTMFG','AIRCRAFTMFGR',
            'AC_TYPE','ACTYPE','ACTYP','ACFT','AC','PLATFORM',
            'TYPEDEMAT','TYPEDEMATERIEL','TYPEDEMATERIELPARTNUMBER'
        ),
        'ATA' => array(
            'ATA','ATACHAPTER','CHAPTERATA','ATACHAP','ATA_CHAPTER','ATACODE'
        ),
        'CAPABILITY' => array(
            'CAPABILITY','CAPA','CAPABILITYLEVEL','TRAVAUXCAPABILITYLEVEL',
            'WORKSCOPE','LEVELFAA','LEVELEASA','LEVELDC','LEVELEMAR'
        ),
        'PMA' => array(
            'PMA'
        ),
        'DOA' => array(
            'DOA'
        ),
        'DER' => array(
            'DER'
        ),
        'CODE_OEM' => array(
            'CODEOEM','OEMCODE','OEMPN','OEMPART','OEMPNCODE','OEMPNUMBER','OEMREFERENCE','DOCUMENTATIONOWNER','CMM'
        ),
        'DESIGN_OEM' => array(
            'DESIGNOEM','DESIGNCOM','OEMDESIGN','DESIGNER','DESIGNCOMPANY','DESIGNORG','CONSTRUCTEURMANUFACTURER','OEM'
        )
    );

    // Normalise tous les headers
    $normalizedHeaders = array();
    foreach ($header as $idx => $colName) {
        $normalizedHeaders[$idx] = normalize_header_name($colName);
    }

    $map = array();

    // Pour chaque champ logique → on cherche un header qui match un des synonymes
    foreach ($synonyms as $logicalField => $synList) {

        $foundIndex = null;

        foreach ($synList as $syn) {
            $synNorm = normalize_header_name($syn);

            foreach ($normalizedHeaders as $idx => $normHeader) {
                if ($normHeader === $synNorm) {
                    $foundIndex = $idx;
                    break 2; // on sort des 2 boucles
                }
            }
        }

        if ($foundIndex !== null) {
            $map[$logicalField] = $foundIndex;
        }
    }

    // Champs critiques manquants (aujourd’hui : PN seulement)
    $missingCritical = array();
    if (!isset($map['PN'])) {
        $missingCritical[] = 'PN';
    }

    return array($map, $missingCritical);
}

/**
 * Détecte une "famille avion" (ATR, A320 FAMILY, BOEING, DASH 8, etc.) à partir du texte complet.
 */
function detect_aircraft_family($aircraftRaw) {
    $s = strtoupper(trim($aircraftRaw));
    if ($s === '') return '';

    // On remplace quelques séparateurs par des espaces
    $s = str_replace(array('-', '/', '\\'), ' ', $s);

    // Exemple 1 : ATR (ATR-42, ATR72-600, ATR 42/72, etc.)
    if (strpos($s, 'ATR') !== false) {
        return 'ATR';
    }

    // Exemple 2 : famille A320 (A318, A319, A320, A321)
    if (preg_match('/A3(18|19|20|21)/', $s)) {
        return 'A320 FAMILY';
    }

    // Exemple 3 : BOEING (737, 747, 757, 767, 777, 787)
    if (preg_match('/B7(27|37|47|57|67|77|87)/', $s) || strpos($s, 'BOEING') !== false) {
        return 'BOEING';
    }

    // Exemple 4 : DASH 8 / Q400 / DH8
    if (strpos($s, 'DASH 8') !== false || strpos($s, 'Q400') !== false || strpos($s, 'DH8') !== false) {
        return 'DASH 8';
    }

    // Ici on pourra ajouter plus tard : E-JET, EMBRAER, CRJ, CL215/415, etc.

    return '';
}

// =======================================================
// 4. Petite fonction utilitaire pour préparer l’INSERT
// =======================================================
function buildInsertForRow($db_link, $row, $map, $company_id, $today) {
    // PN obligatoire
    if (!isset($map['PN'])) return false;
    $pnIndex = $map['PN'];
    if (!isset($row[$pnIndex])) return false;
    $pn = trim($row[$pnIndex]);
    if ($pn === "") return false;

    $get = function($key) use ($row, $map) {
        if (!isset($map[$key])) return '';
        $idx = $map[$key];
        return isset($row[$idx]) ? trim($row[$idx]) : '';
    };

    $description = $get('DESCRIPTION');
    $aircraft    = $get('AIRCRAFT');

    // Normalisation "famille avion" sans perdre l'info d'origine
    $aircraftRaw = $aircraft;
    $family      = detect_aircraft_family($aircraftRaw);
    if ($family !== '') {
        // On stocke : "ATR | ATR-72-600" par exemple
        if ($aircraftRaw !== '' && stripos($aircraftRaw, $family) === false) {
            $aircraft = $family.' | '.$aircraftRaw;
        } else {
            $aircraft = $aircraftRaw;
        }
    }

    $ata         = $get('ATA');
    $capability  = $get('CAPABILITY');
    $pma         = $get('PMA');
    $doa         = $get('DOA');
    $der         = $get('DER');
    $code_oem    = $get('CODE_OEM');
    $design_oem  = $get('DESIGN_OEM');

    // Sécurisation
    $pn          = mysqli_real_escape_string($db_link, $pn);
    $description = mysqli_real_escape_string($db_link, $description);
    $aircraft    = mysqli_real_escape_string($db_link, $aircraft);
    $ata         = mysqli_real_escape_string($db_link, $ata);
    $capability  = mysqli_real_escape_string($db_link, $capability);
    $pma         = mysqli_real_escape_string($db_link, $pma);
    $doa         = mysqli_real_escape_string($db_link, $doa);
    $der         = mysqli_real_escape_string($db_link, $der);
    $code_oem    = mysqli_real_escape_string($db_link, $code_oem);
    $design_oem  = mysqli_real_escape_string($db_link, $design_oem);

    // IMPORTANT : dans la DB, c’est "descriptioin"
    $sqlInsert = "
        INSERT INTO tbl_capa_list
        (
            pn,
            descriptioin,
            aircraft,
            ata,
            capability,
            pma,
            doa,
            der,
            code_oem,
            design_oem,
            id_company,
            status,
            entry_date,
            comments
        )
        VALUES
        (
            '".$pn."',
            '".$description."',
            '".$aircraft."',
            '".$ata."',
            '".$capability."',
            '".$pma."',
            '".$doa."',
            '".$der."',
            '".$code_oem."',
            '".$design_oem."',
            ".$company_id.",
            '',
            '".$today."',
            ''
        )
    ";

    return $sqlInsert;
}

// =======================================================
// 5. TRAITEMENT FORMULAIRE (IMPORT / DELETE PROVIDER)
// =======================================================
$message = "";
$message_type = ""; // success | danger
$mapping_info = ""; // info sur les colonnes détectées

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = isset($_POST['action_type']) ? $_POST['action_type'] : 'import';

    // ---------- SUPPRESSION TOTALE D'UN PROVIDER ----------
    if ($action === 'delete_provider') {

        $companyDel = isset($_POST['id_company_delete']) ? intval($_POST['id_company_delete']) : 0;

        if ($companyDel <= 0) {
            $message = "Please select a Provider / MRO to delete.";
            $message_type = "danger";
        } else {
            $sqlDel = "DELETE FROM tbl_capa_list WHERE id_company = ".$companyDel;
            if (mysqli_query($db_link, $sqlDel)) {
                $nb = mysqli_affected_rows($db_link);
                $message = "All CAPA rows for this provider have been deleted (".$nb." row(s)).";
                $message_type = "success";
            } else {
                $message = "Error while deleting CAPA rows for this provider.";
                $message_type = "danger";
            }
        }

    } else {
        // ---------- IMPORT CAPA LIST ----------
        $company_id = isset($_POST['id_company']) ? intval($_POST['id_company']) : 0;

        if ($company_id <= 0) {
            $message = "Please select a Provider / MRO.";
            $message_type = "danger";
        } elseif (!isset($_FILES['capa_file']) || $_FILES['capa_file']['error'] != UPLOAD_ERR_OK) {
            $message = "Please select a valid file to upload (CSV, XLS, XLSX).";
            $message_type = "danger";
        } else {

            $tmpName  = $_FILES['capa_file']['tmp_name'];
            $fileName = $_FILES['capa_file']['name'];
            $ext      = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $today    = date("Y-m-d");
            $inserted = 0;
            $skipped  = 0;

            // ---------- CSV ----------
            if ($ext === 'csv') {

                if (($handle = fopen($tmpName, 'r')) !== false) {

                    $header = fgetcsv($handle, 0, ',');
                    if ($header === false) {
                        $message = "CSV file seems empty or unreadable.";
                        $message_type = "danger";
                    } else {
                        list($map, $missingCritical) = buildHeaderMapSmart($header);

                        if (!empty($missingCritical)) {
                            $message = "Cannot import: PN column not found (even with synonyms).";
                            $message_type = "danger";
                        } else {
                            // Info pour toi sur le mapping
                            $mapping_info = "Mapping detected: ";
                            foreach ($map as $field => $idx) {
                                $mapping_info .= $field . " ← '" . $header[$idx] . "'; ";
                            }

                            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                                $sqlInsert = buildInsertForRow($db_link, $row, $map, $company_id, $today);
                                if ($sqlInsert === false) {
                                    $skipped++;
                                    continue;
                                }
                                if (mysqli_query($db_link, $sqlInsert)) {
                                    $inserted++;
                                } else {
                                    $skipped++;
                                }
                            }
                            fclose($handle);
                            $message = "Import completed (CSV). Inserted: ".$inserted." row(s), skipped: ".$skipped." row(s).";
                            $message_type = "success";
                        }
                    }

                } else {
                    $message = "Unable to open uploaded CSV file.";
                    $message_type = "danger";
                }

            // ---------- EXCEL (XLS / XLSX) ----------
            } elseif ($ext === 'xls' || $ext === 'xlsx') {

                try {
                    $spreadsheet = IOFactory::load($tmpName);
                    $sheet = $spreadsheet->getActiveSheet();
                    $highestRow = $sheet->getHighestRow();
                    $highestColumn = $sheet->getHighestColumn();
                    $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

                    // Ligne 1 = header
                    $header = array();
                    for ($col = 1; $col <= $highestColumnIndex; $col++) {
                        $colLetter = Coordinate::stringFromColumnIndex($col); // A, B, C...
                        $header[] = trim((string)$sheet->getCell($colLetter . '1')->getValue());
                    }

                    list($map, $missingCritical) = buildHeaderMapSmart($header);

                    if (!empty($missingCritical)) {
                        $message = "Cannot import: PN column not found in Excel file (even with synonyms).";
                        $message_type = "danger";
                    } else {

                        // Info mapping
                        $mapping_info = "Mapping detected: ";
                        foreach ($map as $field => $idx) {
                            $mapping_info .= $field . " ← '" . $header[$idx] . "'; ";
                        }

                        for ($rowNum = 2; $rowNum <= $highestRow; $rowNum++) {
                            $row = array();
                            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                                $colLetter = Coordinate::stringFromColumnIndex($col); // A, B, C...
                                $cellValue = $sheet->getCell($colLetter . $rowNum)->getValue();
                                $row[] = trim((string)$cellValue);
                            }

                            $sqlInsert = buildInsertForRow($db_link, $row, $map, $company_id, $today);
                            if ($sqlInsert === false) {
                                $skipped++;
                                continue;
                            }
                            if (mysqli_query($db_link, $sqlInsert)) {
                                $inserted++;
                            } else {
                                $skipped++;
                            }
                        }

                        $message = "Import completed (Excel). Inserted: ".$inserted." row(s), skipped: ".$skipped." row(s).";
                        $message_type = "success";
                    }

                } catch (Exception $e) {
                    $message = "Error reading Excel file: ".$e->getMessage();
                    $message_type = "danger";
                }

            } else {
                $message = "Unsupported file type: please upload CSV, XLS or XLSX.";
                $message_type = "danger";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aerocanada-industries.com - Import CAPA List</title>

    <!-- Bootstrap Core CSS -->
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- MetisMenu CSS -->
    <link href="../vendor/metisMenu/metisMenu.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../dist/css/sb-admin-2.css" rel="stylesheet">
    <link href="../dist/css/aci-overrides.css" rel="stylesheet">
    <!-- Custom Fonts -->
    <link href="../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
</head>
<body>

<div id="wrapper">
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
        <?php include "top_menu.php"; ?>
        <?php if(isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
    </nav>

    <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">

        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Import CAPA List</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">

                <?php if ($message !== ""): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?><br>
                        <?php if ($mapping_info !== "" && $message_type === "success"): ?>
                            <small><?php echo htmlspecialchars($mapping_info); ?></small>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- PANEL IMPORT -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Import CAPA from file (CSV / XLS / XLSX)
                    </div>
                    <div class="panel-body">
                        <p>
                            1. Télécharge le template CSV :
                            <a href="add_capa_list.php?download=template" class="btn btn-xs btn-info">
                                Download Template
                            </a><br>
                            2. Remplis le fichier (une ligne par PN) ou adapte ton fichier existant<br>
                            3. Choisis le Provider / MRO correspondant à cette CAPA List.<br>
                            4. Charge le fichier ci-dessous (CSV / Excel).
                        </p>

                        <form method="post" enctype="multipart/form-data" class="form-horizontal">
                            <input type="hidden" name="action_type" value="import">

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Provider / MRO</label>
                                <div class="col-sm-9">
                                    <select name="id_company" class="form-control" required>
                                        <option value="">-- Select Provider --</option>
                                        <?php foreach ($providers as $p): ?>
                                            <option value="<?php echo (int)$p['Fld_Company_ID']; ?>">
                                                <?php echo htmlspecialchars($p['Fld_Company_Name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">CAPA File</label>
                                <div class="col-sm-9">
                                    <input type="file" name="capa_file" class="form-control" accept=".csv,.xls,.xlsx" required>
                                    <p class="help-block">
                                        Accepted formats: CSV, XLS, XLSX.
                                    </p>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-offset-3 col-sm-9">
                                    <button type="submit" class="btn btn-primary">
                                        Import CAPA List
                                    </button>
                                    <a href="capa-list.php" class="btn btn-default">Back to CAPA List</a>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>

                <!-- PANEL DELETE PROVIDER -->
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        Delete ALL CAPA rows for a Provider
                    </div>
                    <div class="panel-body">
                        <p>
                            Attention : cette action supprime <strong>toutes les lignes CAPA</strong> associées au provider sélectionné.
                        </p>

                        <form method="post" class="form-horizontal" onsubmit="return confirm('ARE YOU SURE you want to delete ALL CAPA rows for this provider?');">
                            <input type="hidden" name="action_type" value="delete_provider">

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Provider / MRO</label>
                                <div class="col-sm-9">
                                    <select name="id_company_delete" class="form-control" required>
                                        <option value="">-- Select Provider --</option>
                                        <?php foreach ($providers as $p): ?>
                                            <option value="<?php echo (int)$p['Fld_Company_ID']; ?>">
                                                <?php echo htmlspecialchars($p['Fld_Company_Name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-offset-3 col-sm-9">
                                    <button type="submit" class="btn btn-danger">
                                        Delete ALL CAPA for this provider
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>

            </div><!-- /.col-lg-8 -->
        </div><!-- /.row -->

    </div><!-- /#page-wrapper -->
</div><!-- /#wrapper -->

<!-- jQuery -->
<script src="../vendor/jquery/jquery.min.js"></script>
<!-- Bootstrap Core JavaScript -->
<script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
<!-- Metis Menu Plugin JavaScript -->
<script src="../vendor/metisMenu/metisMenu.min.js"></script>
<!-- Custom Theme JavaScript -->
<script src="../dist/js/sb-admin-2.js"></script>

</body>
</html>
