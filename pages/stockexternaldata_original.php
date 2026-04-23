<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Capture de la sortie pour éviter tout caractère non désiré
ob_start();

// S'assurer que le script renvoie du JSON
header('Content-Type: application/json');

// Récupération des données de la requête
$requestData = $_REQUEST;

$columns = array( 
    0 => 'Fld_Stock_externe_ID',
    1 => 'Fld_Part_ID', 
    2 => 'Fld_Part_SN', 
    3 => 'Fld_Supplier_ID', 
    4 => 'Fld_Entry_Date', 
    5 => 'Fld_Part_Price', 
    6 => 'Fld_Price_Currency_ID', 
    7 => 'Fld_BAX_PO_Nbr', 
    8 => 'Fld_Supplier_order_Date', 
    9 => 'Fld_Supplier_Payment_Date', 
    10 => 'Fld_Qty', 
    11 => 'Fld_Condition_ID', 
    12 => 'Fld_Release_ID', 
    13 => 'Fld_Tag_Info_ID', 
    14 => 'Fld_Tag_Date', 
    15 => 'Fld_Traceability_ID', 
    16 => 'Fld_Warehouse_Location', 
    17 => 'Fld_Physical_Stock', 
    18 => 'Fld_Owner_ID', 
    19 => 'Fld_Stock_Location_ID', 
    20 => 'Fld_Status_ID', 
    21 => 'Fld_Status_Ind', 
    22 => 'Fld_Status_Date', 
    23 => 'Fld_Stock_Remark', 
    24 => 'Fld_Shelf_Life_Limit', 
    25 => 'Fld_Valeur_Comptable', 
    26 => 'Fld_Valeur_Comptable_currency_Id', 
    27 => 'Fld_Sales_Remark', 
    28 => 'Fld_External_Location', 
    29 => 'Fld_Sales_Remark_ID', 
    30 => 'Fld_Warehouse_Location_ID', 
    31 => 'Fld_OriginalUnit_Stock_ID', 
    32 => 'Fld_Min_Qty', 
    33 => 'Fld_Publish',
    34 => 'status',
    35 => 'Fld_AC_ID',
    36 => 'Fld_Company_ID'
);

// Récupération du nombre total d'enregistrements sans filtre
$sql = "SELECT * FROM tbl_Stock_external";
$query = mysqli_query($conn, $sql);

if (!$query) {
    echo json_encode(["error" => "Erreur SQL: " . mysqli_error($conn)]);
    ob_end_clean();
    exit;
}

$totalData = mysqli_num_rows($query);
$totalFiltered = $totalData;

// Préparation de la requête SQL pour la recherche
$sql = "SELECT tbl_Stock_external.*, tbl_Parts.Fld_Part_Nbr, tbl_Parts.Fld_Part_Desc 
        FROM tbl_Stock_external 
        LEFT JOIN tbl_Parts ON tbl_Stock_external.Fld_Part_ID = tbl_Parts.Fld_Part_ID 
        WHERE 1 = 1";

if (!empty($requestData['search']['value'])) {
    $searchValue = mysqli_real_escape_string($conn, $requestData['search']['value']);
    $sql .= " AND (tbl_Stock_external.Fld_Stock_externe_ID LIKE '%" . $searchValue . "%' ";

    $sql2 = "SHOW COLUMNS FROM tbl_Stock_external";
    $query2 = mysqli_query($conn, $sql2);

    if (!$query2) {
        echo json_encode(["error" => "Erreur SQL: " . mysqli_error($conn)]);
        ob_end_clean();
        exit;
    }

    while ($row2 = mysqli_fetch_array($query2)) {
        $sql .= " OR tbl_Stock_external." . $row2["Field"] . " LIKE '%" . $searchValue . "%' ";
    }

    $sql .= ") OR (tbl_Parts.Fld_Part_Nbr LIKE '%" . $searchValue . "%' OR tbl_Parts.Fld_Part_Desc LIKE '%" . $searchValue . "%')";
}

$query = mysqli_query($conn, $sql);

if (!$query) {
    echo json_encode(["error" => "Erreur SQL: " . mysqli_error($conn)]);
    ob_end_clean();
    exit;
}

$totalFiltered = mysqli_num_rows($query); // Modification du total filtré en fonction des résultats de recherche
$sql .= " ORDER BY tbl_Stock_external." . $columns[$requestData['order'][0]['column']] . " " . mysqli_real_escape_string($conn, $requestData['order'][0]['dir']) . " LIMIT " . intval($requestData['start']) . ", " . intval($requestData['length']);

$query = mysqli_query($conn, $sql);

if (!$query) {
    echo json_encode(["error" => "Erreur SQL: " . mysqli_error($conn)]);
    ob_end_clean();
    exit;
}

$data = array();
while ($row = mysqli_fetch_array($query)) {
    $nestedData = array();
    $nestedData[] = htmlspecialchars($row['Fld_Stock_externe_ID']);
    $nestedData[] = htmlspecialchars($row['Fld_Part_Nbr']);
    $nestedData[] = htmlspecialchars($row['Fld_Part_Desc']);
    $nestedData[] = htmlspecialchars($row['Fld_Part_SN']);
    $nestedData[] = htmlspecialchars($row['Fld_Supplier_ID']);
    $nestedData[] = htmlspecialchars($row['Fld_Entry_Date']);
    $nestedData[] = htmlspecialchars($row['Fld_Part_Price']);
    $nestedData[] = htmlspecialchars($row['Fld_Price_Currency_ID']);
    $nestedData[] = htmlspecialchars($row['Fld_BAX_PO_Nbr']);
    $nestedData[] = htmlspecialchars($row['Fld_Supplier_order_Date']);
    $nestedData[] = htmlspecialchars($row['Fld_Supplier_Payment_Date']);
    $nestedData[] = htmlspecialchars($row['Fld_Qty']);
    $nestedData[] = htmlspecialchars($row['Fld_Condition_ID']);
    $nestedData[] = htmlspecialchars($row['Fld_Release_ID']);
    $nestedData[] = htmlspecialchars($row['Fld_Tag_Info_ID']);
    $nestedData[] = htmlspecialchars($row['Fld_Tag_Date']);
    $nestedData[] = htmlspecialchars($row['Fld_Traceability_ID']);
    $nestedData[] = htmlspecialchars($row['Fld_Warehouse_Location']);
    $nestedData[] = htmlspecialchars($row['Fld_Physical_Stock']);
    $nestedData[] = htmlspecialchars($row['Fld_Owner_ID']);
    $nestedData[] = htmlspecialchars($row['Fld_Stock_Location_ID']);
    $nestedData[] = htmlspecialchars($row['Fld_Status_ID']);
    $nestedData[] = htmlspecialchars($row['Fld_Status_Ind']);
    $nestedData[] = htmlspecialchars($row['Fld_Status_Date']);
    $nestedData[] = htmlspecialchars($row['Fld_Stock_Remark']);
    $nestedData[] = htmlspecialchars($row['Fld_Shelf_Life_Limit']);
    $nestedData[] = htmlspecialchars($row['Fld_Valeur_Comptable']);
    $nestedData[] = htmlspecialchars($row['Fld_Valeur_Comptable_currency_Id']);
    $nestedData[] = htmlspecialchars($row['Fld_Sales_Remark']);
    $nestedData[] = htmlspecialchars($row['Fld_External_Location']);
    $nestedData[] = htmlspecialchars($row['Fld_Sales_Remark_ID']);
    $nestedData[] = htmlspecialchars($row['Fld_Warehouse_Location_ID']);
    $nestedData[] = htmlspecialchars($row['Fld_OriginalUnit_Stock_ID']);
    $nestedData[] = htmlspecialchars($row['Fld_Min_Qty']);
    $nestedData[] = htmlspecialchars($row['Fld_Publish']);

    // Récupération du nom de la compagnie
    $sqlrn = "SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID='" . mysqli_real_escape_string($conn, $row['Fld_Company_ID']) . "'";
    $reqrn = mysqli_query($conn, $sqlrn);

    if ($reqrn && $datarn = mysqli_fetch_array($reqrn)) {
        $nestedData[] = htmlspecialchars($datarn["Fld_Company_Name"]);
    } else {
        $nestedData[] = "Nom de la compagnie non disponible";
    }

    $data[] = $nestedData;
}

// Nettoyer le tampon de sortie
ob_end_clean();

// Préparation des données en JSON
$json_data = array(
    "draw" => intval($requestData['draw']),
    "recordsTotal" => intval($totalData),
    "recordsFiltered" => intval($totalFiltered),
    "data" => $data
);

// Envoi de la réponse en JSON avec vérification des erreurs JSON
echo json_encode($json_data);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["error" => "Erreur JSON: " . json_last_error_msg()]);
}

?>
