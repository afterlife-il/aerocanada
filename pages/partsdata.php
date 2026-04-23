<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
session_start();

if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
    echo json_encode(["draw"=>0,"recordsTotal"=>0,"recordsFiltered"=>0,"data"=>[]]);
    exit;
}

include "conf.php";

// Stocker les requêtes globales (GET/POST) dans une variable
$requestData = $_REQUEST;

// Définir les colonnes
$columns = array(
    0 => 'Fld_Part_ID',
    1 => 'Fld_Part_Nbr',
    2 => 'Fld_Part_Desc',
    3 => 'Fld_Part_Desc',
    4 => 'Fld_Part_MFG',
    5 => 'Fld_Part_MFG_Old',
    6 => 'Fld_AC_ID',
    7 => 'Fld_Old_LP',
    8 => 'Fld_Part_List_Price',
    9 => 'Fld_Part_Price_Currency_ID',
    10 => 'Fld_Part_LP_Date',
    11 => 'Fld_Remark',
    12 => 'status'
);

// Initialisation des paramètres pour éviter les erreurs
$start = isset($requestData['start']) && is_numeric($requestData['start']) ? intval($requestData['start']) : 0;
$length = isset($requestData['length']) && is_numeric($requestData['length']) ? intval($requestData['length']) : 25;
$orderColumnIndex = isset($requestData['order'][0]['column']) ? intval($requestData['order'][0]['column']) : 0;
$orderDirection = isset($requestData['order'][0]['dir']) && in_array($requestData['order'][0]['dir'], ['asc', 'desc']) ? $requestData['order'][0]['dir'] : 'ASC';

// Obtenir le nombre total d'enregistrements sans filtre de recherche
$sql = "SELECT * FROM tbl_Parts WHERE status='Available'";
$query = mysqli_query($db_link, $sql) or die(json_encode(["error" => "Erreur SQL : " . mysqli_error($db_link)]));
$totalData = mysqli_num_rows($query);
$totalFiltered = $totalData;

// Appliquer le filtre de recherche si nécessaire
if (!empty($requestData['search']['value'])) {
    $searchValue = mysqli_real_escape_string($db_link, $requestData['search']['value']);
    $sql .= " AND (tbl_Parts.Fld_Part_ID LIKE '%" . $searchValue . "%'";

    $sql2 = "SHOW COLUMNS FROM tbl_Parts";
    $query2 = mysqli_query($db_link, $sql2) or die(json_encode(["error" => "Erreur SQL : " . mysqli_error($db_link)]));
    while ($row2 = mysqli_fetch_array($query2)) {
        $sql .= " OR tbl_Parts." . $row2["Field"] . " LIKE '%" . $searchValue . "%'";
    }
    $sql .= ")";
}

// Obtenir le nombre total de résultats après filtrage
$query = mysqli_query($db_link, $sql) or die(json_encode(["error" => "Erreur SQL : " . mysqli_error($db_link)]));
$totalFiltered = mysqli_num_rows($query);

// Ajouter le tri et la pagination
$sql .= " ORDER BY " . $columns[$orderColumnIndex] . " $orderDirection LIMIT $start, $length";

// Exécuter la requête finale
$query = mysqli_query($db_link, $sql) or die(json_encode(["error" => "Erreur SQL : " . mysqli_error($db_link)]));

$data = array();
while ($row = mysqli_fetch_array($query)) {
    $nestedData = array();

    // Récupération du nom de la currency
    $currency = "";
    if (!empty($row["Fld_Part_Price_Currency_ID"])) {
        $sqlCurrency = "SELECT htmlcode FROM tbl_Currency WHERE Fld_Currency_ID=" . $row["Fld_Part_Price_Currency_ID"];
        $reqCurrency = mysqli_query($db_link, $sqlCurrency) or die(json_encode(["error" => "Erreur SQL : " . mysqli_error($db_link)]));
        $dataCurrency = mysqli_fetch_array($reqCurrency);
        $currency = $dataCurrency['htmlcode'] ?? "";
    }

    // Récupération du nom du aircraft
    $Aircraft_model = "";
    if (!empty($row["Fld_AC_ID"])) {
        $sqlac = "SELECT Fld_AC_Model FROM tbl_Aircraft WHERE Fld_AC_ID=" . $row["Fld_AC_ID"];
        $reqac = mysqli_query($db_link, $sqlac) or die(json_encode(["error" => "Erreur SQL : " . mysqli_error($db_link)]));
        $dataac = mysqli_fetch_array($reqac);
        $Aircraft_model = $dataac['Fld_AC_Model'] ?? "";
    }

    // Récupération du nom de la compagnie
    $companyname = "";
    if (!empty($row["Fld_Part_MFG"])) {
        $sqlcn = "SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID=" . $row["Fld_Part_MFG"];
        $reqcn = mysqli_query($db_link, $sqlcn) or die(json_encode(["error" => "Erreur SQL : " . mysqli_error($db_link)]));
        $datacn = mysqli_fetch_array($reqcn);
        $companyname = $datacn['Fld_Company_Name'] ?? "";
    }

    // Construction des données
    $pn = urlencode($row["Fld_Part_Nbr"]);
    $nestedData[] = "<a href='Part-Nbr.php?pn=" . $pn . "'>" . htmlspecialchars($row["Fld_Part_Nbr"] ?? '', ENT_QUOTES, 'UTF-8') . "</a>";
    $nestedData[] = htmlspecialchars($row["alt_pn"] ?? '', ENT_QUOTES, 'UTF-8');
    $nestedData[] = htmlspecialchars($row["Fld_Part_Desc"] ?? '', ENT_QUOTES, 'UTF-8');
    $nestedData[] = htmlspecialchars($companyname, ENT_QUOTES, 'UTF-8');
    $nestedData[] = htmlspecialchars($Aircraft_model, ENT_QUOTES, 'UTF-8');
    $nestedData[] = $row["Fld_Part_List_Price"] ?? "";
    $nestedData[] = $currency ? html_entity_decode($currency) : '';
    $nestedData[] = htmlspecialchars($row["Fld_Part_LP_Date"] ?? '', ENT_QUOTES, 'UTF-8');
    $nestedData[] = htmlspecialchars($row["Fld_Remark"] ?? '', ENT_QUOTES, 'UTF-8');
    if ($_SESSION['statut'] === "SuperAdmin") {
        $nestedData[] = "<a href='del_part.php?part_id=" . $row['Fld_Part_ID'] . "' onClick=\"return(confirm('Etes vous sur ?'));\"><img src='images/bin-blue-full-icon.png' border='0' width='27'></a>";
    }

    $data[] = $nestedData;
}

// Préparation de la réponse JSON
$json_data = array(
    "draw" => intval($requestData['draw'] ?? 0),
    "recordsTotal" => intval($totalData),
    "recordsFiltered" => intval($totalFiltered),
    "data" => $data
);

echo json_encode($json_data, JSON_UNESCAPED_UNICODE);
?>
