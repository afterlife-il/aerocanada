<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

include_once "conf.php";

// Simulation de requête basique sans filtre
$sql = "
SELECT 
    Fld_Company_ID,
    Fld_Company_Name,
    companyrating,
    aci_contact,
    logocompany,
    internet,
    cage_code,
    (SELECT Employee_Name FROM tbl_Employee WHERE Employee_ID = tb_company.aci_contact LIMIT 1) AS Employee_Name
FROM tb_company
WHERE status = 'Available'
LIMIT 10";

$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode(["error" => "SQL Error", "message" => mysqli_error($conn)]);
    exit;
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "total" => count($data),
    "data" => $data
], JSON_PRETTY_PRINT);
