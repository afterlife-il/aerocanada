<?php
// Server-side processing for CAPA LIST (DataTables)
session_start();
include_once "conf.php";
include_once "page_titles.php";

if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
    // Not logged in – return empty result to DataTables
    echo json_encode([
        "draw" => isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 0,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => []
    ]);
    exit;
}

// Connexion DB (compatibilité ancienne avec $conn)
$db = isset($db_link) ? $db_link : $conn;

// Paramètres envoyés par DataTables
$requestData = $_REQUEST;

// Mapping colonnes DataTables -> SQL
$columns = array(
    0  => 'cl.id_capa_list',
    1  => 'cl.pn',
    2  => 'cl.descriptioin',     // (nom de colonne existant dans la DB)
    3  => 'cl.aircraft',
    4  => 'cl.ata',
    5  => 'cl.capability',
    6  => 'cl.pma',
    7  => 'cl.doa',
    8  => 'cl.der',
    9  => 'cl.code_oem',
    10 => 'cl.design_oem',
    11 => 'c.Fld_Company_Name',
    12 => ''                     // colonne actions (SUP)
);

$baseSql = " FROM tbl_capa_list cl
             LEFT JOIN tb_company c ON cl.id_company = c.Fld_Company_ID ";

$where = " WHERE 1=1 ";

// ------------------------
// Filtres personnalisés
// ------------------------

// Filtre fournisseur (id_company)
if (!empty($requestData['filter_company'])) {
    $idCompany = intval($requestData['filter_company']);
    if ($idCompany > 0) {
        $where .= " AND cl.id_company = " . $idCompany . " ";
    }
}

// Filtre aircraft
if (!empty($requestData['filter_aircraft'])) {
    $aircraft = mysqli_real_escape_string($db, $requestData['filter_aircraft']);
    $where .= " AND cl.aircraft = '" . $aircraft . "' ";
}

// Filtre ATA
if (!empty($requestData['filter_ata'])) {
    $ata = mysqli_real_escape_string($db, $requestData['filter_ata']);
    $where .= " AND cl.ata = '" . $ata . "' ";
}

// Filtre capability
if (!empty($requestData['filter_capability'])) {
    $capability = mysqli_real_escape_string($db, $requestData['filter_capability']);
    $where .= " AND cl.capability = '" . $capability . "' ";
}

// ------------------------
// Recherche globale
// ------------------------
if (!empty($requestData['search']['value'])) {
    $search = mysqli_real_escape_string($db, $requestData['search']['value']);
    $where .= " AND (
        cl.id_capa_list   LIKE '%" . $search . "%' OR
        cl.pn             LIKE '%" . $search . "%' OR
        cl.descriptioin   LIKE '%" . $search . "%' OR
        cl.aircraft       LIKE '%" . $search . "%' OR
        cl.ata            LIKE '%" . $search . "%' OR
        cl.capability     LIKE '%" . $search . "%' OR
        cl.pma            LIKE '%" . $search . "%' OR
        cl.doa            LIKE '%" . $search . "%' OR
        cl.der            LIKE '%" . $search . "%' OR
        cl.code_oem       LIKE '%" . $search . "%' OR
        cl.design_oem     LIKE '%" . $search . "%' OR
        c.Fld_Company_Name LIKE '%" . $search . "%'
    ) ";
}

// ------------------------
// Total sans filtre
// ------------------------
$sql = "SELECT COUNT(*) AS total " . $baseSql;
$res = mysqli_query($db, $sql);
$row = mysqli_fetch_assoc($res);
$totalData = intval($row['total']);

// ------------------------
// Total avec filtre
// ------------------------
$sql = "SELECT COUNT(*) AS total " . $baseSql . $where;
$res = mysqli_query($db, $sql);
$row = mysqli_fetch_assoc($res);
$totalFiltered = intval($row['total']);

// ------------------------
// Tri / Order
// ------------------------
$orderColumnIndex = isset($requestData['order'][0]['column']) ? intval($requestData['order'][0]['column']) : 0;
$orderDir = (isset($requestData['order'][0]['dir']) && strtolower($requestData['order'][0]['dir']) === 'desc') ? 'DESC' : 'ASC';
$orderColumn = isset($columns[$orderColumnIndex]) && $columns[$orderColumnIndex] !== '' ? $columns[$orderColumnIndex] : 'cl.id_capa_list';

// ------------------------
// Pagination
// ------------------------
$start  = isset($requestData['start']) ? intval($requestData['start']) : 0;
$length = isset($requestData['length']) ? intval($requestData['length']) : 10;
if ($length < 0) {
    $length = 10;
}

// ------------------------
// Requête principale
// ------------------------
$sql = "SELECT 
            cl.*,
            c.Fld_Company_Name
        " . $baseSql . $where . "
        ORDER BY " . $orderColumn . " " . $orderDir . "
        LIMIT " . $start . ", " . $length;

$query = mysqli_query($db, $sql);

$data = array();

while ($row = mysqli_fetch_assoc($query)) {
    $nestedData = array();

    $nestedData[] = $row["id_capa_list"];                         // 0 - ID
    $nestedData[] = htmlspecialchars($row["pn"]);                 // 1 - PN
    $nestedData[] = htmlspecialchars($row["descriptioin"]);       // 2 - DESCRIPTION
    $nestedData[] = htmlspecialchars($row["aircraft"]);           // 3 - AIRCRAFT
    $nestedData[] = htmlspecialchars($row["ata"]);                // 4 - ATA
    $nestedData[] = htmlspecialchars($row["capability"]);         // 5 - CAPABILITY
    $nestedData[] = htmlspecialchars($row["pma"]);                // 6 - PMA
    $nestedData[] = htmlspecialchars($row["doa"]);                // 7 - DOA
    $nestedData[] = htmlspecialchars($row["der"]);                // 8 - DER
    $nestedData[] = htmlspecialchars($row["code_oem"]);           // 9 - CODE OEM
    $nestedData[] = htmlspecialchars($row["design_oem"]);         // 10 - DESIGN OEM

    $companyName = (isset($row["Fld_Company_Name"]) && $row["Fld_Company_Name"] !== "")
        ? $row["Fld_Company_Name"]
        : "N/A";

    $nestedData[] = htmlspecialchars($companyName);               // 11 - COMPANY

    // 12 - ACTION (SUPPRESSION)
    $nestedData[] = "<a href='sup_capa_list.php?ID=" . intval($row['id_capa_list']) . "' onclick=\"return confirm('Confirm delete of this CAPA line?');\" style='text-decoration:none;' title='Delete CAPA'>SUP</a>";

    $data[] = $nestedData;
}

// ------------------------
// Réponse JSON à DataTables
// ------------------------
$json_data = array(
    "draw"            => isset($requestData['draw']) ? intval($requestData['draw']) : 0,
    "recordsTotal"    => $totalData,
    "recordsFiltered" => $totalFiltered,
    "data"            => $data
);

echo json_encode($json_data);
?>
