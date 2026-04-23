<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifiez que la session est valide
if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
    echo json_encode(["error" => "Session non valide"]);
    exit;
}

// S'assurer que le script renvoie du JSON
header('Content-Type: application/json');

// Récupération des données de la requête
$requestData = $_REQUEST;

$columns = array(
    0 => 'ID',
    1 => 'Fld_RFQ_ID',
    2 => 'Fld_Qty',
    3 => 'Fld_Part_ID',
    4 => 'Fld_Observation',
    5 => 'Fld_Customer_ID',
    6 => 'date',
    7 => 'Fld_RFQ_Type_ID',
    8 => 'Fld_Priority_ID',
    9 => 'Employee_ID',
    10 => 'id_company_contact',
    11 => 'Fld_Payment_Term_ID',
    12 => 'Fld_Condition_ID',
    13 => 'pn_rfq',
    14 => 'description_rfq'
);

// Récupération du nombre total d'enregistrements sans filtre
$sql = "SELECT pn_rfq, description_rfq, Fld_Part_ID, count(*) as compte FROM tbl_RFQ_1 WHERE Fld_RFQ_ID > '2017-00-00-000000' GROUP BY pn_rfq ORDER BY compte DESC";
$query = mysqli_query($conn, $sql);

if (!$query) {
    echo json_encode(["error" => "Erreur SQL: " . mysqli_error($conn)]);
    exit;
}

$totalData = mysqli_num_rows($query);
$totalFiltered = $totalData;

// Préparation de la requête SQL pour la recherche
$sql = "SELECT pn_rfq, description_rfq, Fld_Part_ID, count(*) as compte FROM tbl_RFQ_1 WHERE Fld_RFQ_ID > '2017-00-00-000000'";

if (!empty($requestData['search']['value'])) {
    $sql .= " AND (tbl_RFQ_1.ID LIKE '%" . $requestData['search']['value'] . "%'";

    $sql2 = "SHOW COLUMNS FROM tbl_RFQ_1";
    $query2 = mysqli_query($conn, $sql2);

    if (!$query2) {
        echo json_encode(["error" => "Erreur SQL: " . mysqli_error($conn)]);
        exit;
    }

    while ($row2 = mysqli_fetch_array($query2)) {
        $sql .= " OR tbl_RFQ_1." . $row2["Field"] . " LIKE '%" . $requestData['search']['value'] . "%'";
    }

    $sql .= ")";
}

$sql .= " GROUP BY pn_rfq ORDER BY count(*) DESC LIMIT " . intval($requestData['start']) . " ," . intval($requestData['length']);
$query = mysqli_query($conn, $sql);

if (!$query) {
    echo json_encode(["error" => "Erreur SQL: " . mysqli_error($conn)]);
    exit;
}

$totalFiltered = mysqli_num_rows($query);

$data = array();

while ($row = mysqli_fetch_array($query)) {
    $nestedData = array();

    if (!empty($row["pn_rfq"]) && $row["pn_rfq"] != 'pntest23') {
        // Récupération de la description du PN
        $sqldesfrompn = "SELECT Fld_Part_Desc FROM tbl_Parts WHERE Fld_Part_Nbr LIKE '%" . $row['pn_rfq'] . "%'";
        $reqdesfrompn = mysqli_query($conn, $sqldesfrompn);

        if ($reqdesfrompn && $datadesfrompn = mysqli_fetch_array($reqdesfrompn)) {
            $partDesc = htmlspecialchars($datadesfrompn['Fld_Part_Desc']);
        } else {
            $partDesc = "Description non disponible";
        }

        // Récupération de la date de dernière visite
        $sqldatelv = "SELECT max(Fld_RFQ_ID) as datemax FROM tbl_RFQ_1 WHERE pn_rfq LIKE '%" . $row['pn_rfq'] . "%'";
        $reqdatelv = mysqli_query($conn, $sqldatelv);
        $rest = '';

        if ($reqdatelv && $datadatelv = mysqli_fetch_array($reqdatelv)) {
            $rest = substr($datadatelv['datemax'], 0, 10);
        }

        // Préparation des données à envoyer
    $pn = urlencode($row['pn_rfq']);
$nestedData[] = "<a href='Part-Nbr.php?pn={$pn}'>" . htmlspecialchars($row["pn_rfq"], ENT_QUOTES, 'UTF-8') . "</a>";
$nestedData[] = htmlspecialchars($partDesc ?? '', ENT_QUOTES, 'UTF-8');
$nestedData[] = htmlspecialchars($row["compte"] ?? '', ENT_QUOTES, 'UTF-8');
$nestedData[] = htmlspecialchars($rest ?? '', ENT_QUOTES, 'UTF-8');

$data[] = $nestedData;

    }
}

// Préparation des données en JSON
$json_data = array(
    "draw" => intval($requestData['draw']),
    "recordsTotal" => intval($totalData),
    "recordsFiltered" => intval($totalFiltered),
    "data" => $data
);

// Envoi de la réponse en JSON avec vérification des erreurs JSON
echo json_encode($json_data, JSON_PRETTY_PRINT);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["error" => "Erreur JSON: " . json_last_error_msg()]);
}

?>
