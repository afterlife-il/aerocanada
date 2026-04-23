<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

// Storing request (ie, get/post) global array to a variable  
$requestData = $_REQUEST;

$columns = array(
    0 => 'ID', 
    1 => 'Fld_RFQ_ID',
    2 => 'Fld_Quote_Date',
    3 => 'Fld_Part_Nbr',
    4 => 'Fld_Part_SN',
    5 => 'Fld_Qty',
    6 => 'Fld_Condition_Text',
    7 => 'Fld_Price',
    8 => 'Fld_Currency_Text',
    9 => 'Fld_Remark',
    10 => 'Fld_Traceability_ID',
    11 => 'Fld_Tag_Info_ID',
    12 => 'Fld_Tag_Date',
    13 => 'Fld_Release_Text',
    14 => 'moq',
    15 => 'lead_time',
    16 => 'Fld_Priority_Text',
    17 => 'Employee_Name',
    18 => 'ID'
);

// Getting total number records without any search
$sql = "SELECT * FROM tbl_RFQ_3 WHERE 1=1";
$query = mysqli_query($conn, $sql) or die("server_processing.php: get employees");
$totalData = mysqli_num_rows($query);
$totalFiltered = $totalData;

$sql = "SELECT tbl_RFQ_3.*, tbl_Parts.Fld_Part_Nbr, tbl_Parts.Fld_Part_Desc,
            tbl_Condition.Fld_Condition_Text, tbl_Currency.Fld_Currency_Text, 
            tbl_Release.Fld_Release_Text, tbl_Priority.Fld_Priority_Text, 
            tbl_Employee.Employee_Name
        FROM tbl_RFQ_3 
        LEFT JOIN tbl_Parts ON tbl_RFQ_3.Fld_Part_ID = tbl_Parts.Fld_Part_ID 
        LEFT JOIN tbl_Condition ON tbl_RFQ_3.Fld_Condition = tbl_Condition.Fld_Condition_ID 
        LEFT JOIN tbl_Currency ON tbl_RFQ_3.Fld_Currency_ID = tbl_Currency.Fld_Currency_ID 
        LEFT JOIN tbl_Release ON tbl_RFQ_3.Fld_Release_ID = tbl_Release.Fld_Release_ID 
        LEFT JOIN tbl_Priority ON tbl_RFQ_3.Fld_Priority_ID = tbl_Priority.Fld_Priority_ID 
        LEFT JOIN tbl_Employee ON tbl_RFQ_3.Fld_RFQ_ID = tbl_Employee.Employee_ID
        WHERE 1=1";

if( !empty($requestData['search']['value']) ) {   
    $sql .= " AND (tbl_RFQ_3.ID LIKE '%" . $requestData['search']['value'] . "%' ";  
    $sql .= " OR tbl_RFQ_3.Fld_RFQ_ID LIKE '%" . $requestData['search']['value'] . "%' ";
    $sql .= " OR tbl_RFQ_3.Fld_Quote_Date LIKE '%" . $requestData['search']['value'] . "%' ";
    $sql .= " OR tbl_RFQ_3.Fld_Part_Nbr LIKE '%" . $requestData['search']['value'] . "%' ";
    $sql .= " OR tbl_Parts.Fld_Part_Desc LIKE '%" . $requestData['search']['value'] . "%') ";
}

$query = mysqli_query($conn, $sql) or die("server_processing.php: get quotations");
$totalFiltered = mysqli_num_rows($query);

$sql .= " ORDER BY " . $columns[$requestData['order'][0]['column']] . " " . $requestData['order'][0]['dir'] . " 
          LIMIT " . $requestData['start'] . " ," . $requestData['length'] . " ";
$query = mysqli_query($conn, $sql) or die("server_processing.php: get quotations");

$data = array();
while( $row = mysqli_fetch_array($query) ) {  
    $nestedData = array(); 

    $nestedData[] = "<a href='modif_quotations.php?ID=".$row["ID"]."'>".$row["Fld_RFQ_ID"]."</a>";
    $nestedData[] = htmlspecialchars($row["Fld_Quote_Date"] ?? '');
    $nestedData[] = htmlspecialchars($row["Fld_Part_Nbr"] ?? '');
    $nestedData[] = htmlspecialchars($row["Fld_Part_SN"] ?? '');
    $nestedData[] = htmlspecialchars($row['Fld_Qty'] ?? '');
    $nestedData[] = htmlspecialchars($row["Fld_Condition_Text"] ?? '');
    $nestedData[] = htmlspecialchars($row["Fld_Price"] ?? '');
    $nestedData[] = htmlspecialchars($row["Fld_Currency_Text"] ?? '');
    $nestedData[] = htmlspecialchars($row['Fld_Remark'] ?? '');
    $nestedData[] = htmlspecialchars($row['Fld_Traceability_ID'] ?? '');
    $nestedData[] = htmlspecialchars($row['Fld_Tag_Info_ID'] ?? '');
    $nestedData[] = htmlspecialchars($row["Fld_Tag_Date"] ?? '');
    $nestedData[] = htmlspecialchars($row["Fld_Release_Text"] ?? '');
    $nestedData[] = htmlspecialchars($row["moq"] ?? '');
    $nestedData[] = htmlspecialchars($row["lead_time"] ?? '');
    $nestedData[] = htmlspecialchars($row["Fld_Priority_Text"] ?? '');
    $nestedData[] = htmlspecialchars($row["Employee_Name"] ?? '');
    $nestedData[] = $row["ID"];

    if ($_SESSION['statut'] == "SuperAdmin") {
        $nestedData[] = "<a href='del_quotation.php?ID=".$row['ID']."' onClick=\"return(confirm('Etes vous sur ?'));\">
                            <img src='images/bin-blue-full-icon.png' border='0' width='27'></a>";
    }

    $data[] = $nestedData;
}

$json_data = array(
    "draw"            => intval($requestData['draw']),   
    "recordsTotal"    => intval($totalData),
    "recordsFiltered" => intval($totalFiltered),
    "data"            => $data   
);

echo json_encode($json_data);

?>
