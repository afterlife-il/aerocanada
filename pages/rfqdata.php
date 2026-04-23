<?php
session_start();
include_once "conf.php"; // doit définir $conn (mysqli)

// Sécurité basique
if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
    http_response_code(403);
    echo json_encode(["error" => "Not authorized"]);
    exit;
}

// Paramètres DataTables
$draw   = isset($_GET['draw']) ? (int)$_GET['draw'] : 1;
$start  = isset($_GET['start']) ? (int)$_GET['start'] : 0;
$length = isset($_GET['length']) ? (int)$_GET['length'] : 10;

$searchValue = isset($_GET['search']['value']) ? trim($_GET['search']['value']) : "";

// Colonnes simples
$columns = [
    0 => 'tbl_RFQ_1.ID',
    1 => 'tbl_RFQ_1.Fld_RFQ_ID',
    2 => 'tbl_RFQ_1.date',
    3 => 'tbl_RFQ_1.Fld_Qty',
    4 => 'tbl_Parts.Fld_Part_Nbr',
    5 => 'tbl_Parts.Fld_Part_Desc',
    6 => 'tb_company.Fld_Company_Name'
];

// Tri
$orderColIndex = isset($_GET['order'][0]['column']) ? (int)$_GET['order'][0]['column'] : 0;
$orderDir      = isset($_GET['order'][0]['dir']) && $_GET['order'][0]['dir'] === 'desc' ? 'DESC' : 'ASC';
$orderColumn   = isset($columns[$orderColIndex]) ? $columns[$orderColIndex] : 'tbl_RFQ_1.ID';

// Filtre global
$where = " WHERE 1=1 ";

if ($searchValue !== "") {
    $sv = mysqli_real_escape_string($conn, $searchValue);
    $where .= " AND (
        tbl_RFQ_1.Fld_RFQ_ID LIKE '%$sv%' OR
        tbl_Parts.Fld_Part_Nbr LIKE '%$sv%' OR
        tbl_Parts.Fld_Part_Desc LIKE '%$sv%' OR
        tb_company.Fld_Company_Name LIKE '%$sv%'
    )";
}

// Compte total
$sqlTotal = "
    SELECT COUNT(*) AS total
    FROM tbl_RFQ_1
    LEFT JOIN tbl_Parts   ON tbl_RFQ_1.Fld_Part_ID     = tbl_Parts.Fld_Part_ID
    LEFT JOIN tb_company  ON tbl_RFQ_1.Fld_Customer_ID = tb_company.Fld_Company_ID
";
$resTotal = mysqli_query($conn, $sqlTotal);
$rowTotal = $resTotal ? mysqli_fetch_assoc($resTotal) : ['total' => 0];
$totalData = (int)$rowTotal['total'];

// Compte filtré
$sqlFiltered = $sqlTotal . $where;
$resFiltered = mysqli_query($conn, $sqlFiltered);
$rowFiltered = $resFiltered ? mysqli_fetch_assoc($resFiltered) : ['total' => 0];
$totalFiltered = (int)$rowFiltered['total'];

// Données
$sqlData = "
    SELECT
        tbl_RFQ_1.ID,
        tbl_RFQ_1.Fld_RFQ_ID,
        tbl_RFQ_1.date,
        tbl_RFQ_1.Fld_Qty,
        tbl_Parts.Fld_Part_Nbr,
        tbl_Parts.Fld_Part_Desc,
        tb_company.Fld_Company_Name
    FROM tbl_RFQ_1
    LEFT JOIN tbl_Parts   ON tbl_RFQ_1.Fld_Part_ID     = tbl_Parts.Fld_Part_ID
    LEFT JOIN tb_company  ON tbl_RFQ_1.Fld_Customer_ID = tb_company.Fld_Company_ID
    $where
    ORDER BY $orderColumn $orderDir
    LIMIT $start, $length
";

$resData = mysqli_query($conn, $sqlData);
$data    = [];

if ($resData) {
    while ($row = mysqli_fetch_assoc($resData)) {
        $data[] = [
            $row['ID'],
            $row['Fld_RFQ_ID'],
            $row['date'],
            $row['Fld_Qty'],
            $row['Fld_Part_Nbr'],
            $row['Fld_Part_Desc'],
            $row['Fld_Company_Name']
        ];
    }
}

// Réponse JSON DataTables
echo json_encode([
    "draw"            => $draw,
    "recordsTotal"    => $totalData,
    "recordsFiltered" => $totalFiltered,
    "data"            => $data
]);
