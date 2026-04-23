<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

// Storing request (ie, get/post) global array to a variable  
$requestData = $_REQUEST;

$columns = array( 
    // datatable column index  => database column name
    0 => 'id_tb_JPFleet', 
    1 => 'Country',
    2 => 'Airline',
    3 => 'registration',
    4 => 'type_of_aircraft',
    5 => 'cn_fn',
    6 => 'exreg',
    7 => 'mfd',
    8 => 'del',
    9 => 'QT',
    10 => 'Powered_by',
    11 => 'mtow_kg',
    12 => 'config',
    13 => 'remarks',
    14 => 'Head',
    15 => 'Email',
    16 => 'WEB',
    17 => 'TEL',
    18 => 'C20',
    19 => 'C30',
    20 => 'RR',
    21 => 'PT6A',
    22 => 'C20R',
    23 => 'ALL_jpf',
    24 => 'year'
);

// Getting total number of records without any search
$sql = "SELECT * FROM tb_JPFleet";
$query = mysqli_query($conn, $sql) or die("server_processing.php: get records");
$totalData = mysqli_num_rows($query);
$totalFiltered = $totalData;  // When there is no search parameter, total number rows = total number filtered rows

// If there is a search parameter
if (!empty($requestData['search']['value'])) {
    $sql .= " WHERE ( id_tb_JPFleet LIKE '%" . $requestData['search']['value'] . "%' ";
    
    $sql2 = "SHOW COLUMNS FROM tb_JPFleet";
    $query2 = mysqli_query($conn, $sql2) or die("server_processing.php: get columns");
    while ($row2 = mysqli_fetch_array($query2)) {
        $sql .= " OR " . $row2["Field"] . " LIKE '%" . $requestData['search']['value'] . "%' ";
    }
    
    $sql .= " )";
}

$query = mysqli_query($conn, $sql) or die("server_processing.php: get filtered records");
$totalFiltered = mysqli_num_rows($query); // When there is a search parameter, we need to modify total number of filtered rows as per search result 

$sql .= " ORDER BY " . $columns[$requestData['order'][0]['column']] . " " . $requestData['order'][0]['dir'] . " LIMIT " . $requestData['start'] . " ," . $requestData['length'] . " ";

$query = mysqli_query($conn, $sql) or die("server_processing.php: get ordered records");

$data = array();
while ($row = mysqli_fetch_array($query)) {  // Preparing an array
    $nestedData = array(); 

    $nestedData[] = $row["id_tb_JPFleet"];
    $nestedData[] = $row["Country"];
    $nestedData[] = $row["Airline"];
    $nestedData[] = $row["registration"];
    $nestedData[] = $row["type_of_aircraft"];
    $nestedData[] = $row["cn_fn"];
    $nestedData[] = $row["exreg"];
    $nestedData[] = $row["mfd"];
    $nestedData[] = $row["del"];
    $nestedData[] = $row["QT"];
    $nestedData[] = $row["Powered_by"];
    $nestedData[] = $row["mtow_kg"];
    $nestedData[] = $row["config"];
    $nestedData[] = $row["remarks"];
    $nestedData[] = $row["Head"];
    $nestedData[] = $row["Email"];
    $nestedData[] = $row["WEB"];
    $nestedData[] = $row["TEL"];
    $nestedData[] = $row["C20"];
    $nestedData[] = $row["C30"];
    $nestedData[] = $row["RR"];
    $nestedData[] = $row["PT6A"];
    $nestedData[] = $row["C20R"];
    $nestedData[] = $row["ALL_jpf"];
    $nestedData[] = $row["year"];

    $data[] = $nestedData;
}

$json_data = array(
    "draw"            => intval($requestData['draw']),   // For every request/draw by clientside, they send a number as a parameter, when they receive a response/data they first check the draw number, so we are sending the same number in draw. 
    "recordsTotal"    => intval($totalData),  // Total number of records
    "recordsFiltered" => intval($totalFiltered), // Total number of records after searching, if there is no searching then totalFiltered = totalData
    "data"            => $data   // Total data array
);

echo json_encode($json_data);  // Send data as JSON format

?>
