<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
    echo json_encode(["draw"=>0,"recordsTotal"=>0,"recordsFiltered"=>0,"data"=>[]]);
    exit;
}

include_once "conf.php";
include_once "page_titles.php";

// Storing request (ie, get/post) global array to a variable
$requestData = $_REQUEST;

$columns = array(
    0 => 'q.Fld_RFQ_ID',
    1 => 'q.Fld_Quote_Date',
    2 => 'q.Fld_Send_Mail',
    3 => 'q.sent_datetime',
    4 => 'customer.Fld_Company_Name',
    5 => 'contact.Fld_Contact_Name',
    6 => 'q.sent_to_email',
    7 => 'p.Fld_Part_Nbr',
    8 => 'p.Fld_Part_Desc',
    9 => 'q.Fld_Part_SN',
    10 => 'q.Fld_Qty',
    11 => 'cond.Fld_Condition_Text',
    12 => 'q.Fld_Price',
    13 => 'cur.Fld_Currency_Text',
    14 => 'q.Fld_Remark',
    15 => 'q.source_type',
    16 => 'q.lead_time',
    17 => 'prio.Fld_Priority_Text',
    18 => 'sender.Employee_Name',
    19 => 'q.ID'
);

// Getting total number records without any search
$sql = "SELECT COUNT(*) AS c FROM tbl_RFQ_3";
$query = mysqli_query($conn, $sql) or die("server_processing.php: get employees");
$totalData = ($row = mysqli_fetch_assoc($query)) ? (int)$row['c'] : 0;
$totalFiltered = $totalData;

$baseSql = "FROM tbl_RFQ_3 q
        LEFT JOIN tbl_RFQ_1 r1 ON q.id_tbl_rfq1 = r1.ID
        LEFT JOIN tb_company customer ON r1.Fld_Customer_ID = customer.Fld_Company_ID
        LEFT JOIN tb_company_contact contact ON r1.id_company_contact = contact.id_company_contact
        LEFT JOIN tbl_Parts p ON q.Fld_Part_Id = p.Fld_Part_ID
        LEFT JOIN tbl_Condition cond ON q.Fld_Condition = cond.Fld_Condition_ID
        LEFT JOIN tbl_Currency cur ON q.Fld_Currency_ID = cur.Fld_Currency_ID
        LEFT JOIN tbl_Priority prio ON q.Fld_Priority_ID = prio.Fld_Priority_ID
        LEFT JOIN tbl_Employee sender ON COALESCE(q.sender_user_id, r1.Employee_ID) = sender.Employee_ID
        WHERE 1=1";

if( !empty($requestData['search']['value']) ) {   
    $s = mysqli_real_escape_string($conn, $requestData['search']['value']);
    $baseSql .= " AND (
        q.ID LIKE '%$s%' OR
        q.Fld_RFQ_ID LIKE '%$s%' OR
        q.Fld_Quote_Date LIKE '%$s%' OR
        q.sent_datetime LIKE '%$s%' OR
        q.sent_to_email LIKE '%$s%' OR
        q.sent_subject LIKE '%$s%' OR
        q.source_type LIKE '%$s%' OR
        p.Fld_Part_Nbr LIKE '%$s%' OR
        p.Fld_Part_Desc LIKE '%$s%' OR
        customer.Fld_Company_Name LIKE '%$s%' OR
        contact.Fld_Contact_Name LIKE '%$s%'
    )";
}

$query = mysqli_query($conn, "SELECT COUNT(*) AS c $baseSql") or die("server_processing.php: get quotations");
$totalFiltered = ($row = mysqli_fetch_assoc($query)) ? (int)$row['c'] : 0;

$orderIdx = intval($requestData['order'][0]['column'] ?? 0);
$orderCol = $columns[$orderIdx] ?? 'q.ID';
$orderDir = strtolower($requestData['order'][0]['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
$start = max(0, intval($requestData['start'] ?? 0));
$length = max(10, intval($requestData['length'] ?? 25));

$sql = "SELECT q.*, p.Fld_Part_Nbr, p.Fld_Part_Desc,
            cond.Fld_Condition_Text, cur.Fld_Currency_Text,
            prio.Fld_Priority_Text, sender.Employee_Name,
            customer.Fld_Company_Name AS customer_name,
            contact.Fld_Contact_Name AS contact_name,
            contact.Fld_Contact_Email AS contact_email
        $baseSql
        ORDER BY $orderCol $orderDir, q.ID DESC
        LIMIT $start, $length";
$query = mysqli_query($conn, $sql) or die("server_processing.php: get quotations");

$data = array();
while( $row = mysqli_fetch_array($query) ) {  
    $nestedData = array(); 

    $sentText = trim((string)($row['send_status'] ?? ''));
    if ($sentText === '') $sentText = (($row['Fld_Send_Mail'] ?? '') === 'YES') ? 'SENT' : '';

    $nestedData[] = "<a href='modif_quotations.php?ID=".$row["ID"]."'>".htmlspecialchars($row["Fld_RFQ_ID"] ?? '')."</a>";
    $nestedData[] = htmlspecialchars($row["Fld_Quote_Date"] ?? '');
    $nestedData[] = htmlspecialchars($sentText);
    $nestedData[] = htmlspecialchars($row["sent_datetime"] ?? '');
    $nestedData[] = htmlspecialchars($row["customer_name"] ?? '');
    $nestedData[] = htmlspecialchars($row["contact_name"] ?? '');
    $nestedData[] = htmlspecialchars(($row["sent_to_email"] ?? '') !== '' ? $row["sent_to_email"] : ($row["contact_email"] ?? ''));
    $nestedData[] = htmlspecialchars($row["Fld_Part_Nbr"] ?? '');
    $nestedData[] = htmlspecialchars($row["Fld_Part_Desc"] ?? '');
    $nestedData[] = htmlspecialchars($row["Fld_Part_SN"] ?? '');
    $nestedData[] = htmlspecialchars($row['Fld_Qty'] ?? '');
    $nestedData[] = htmlspecialchars($row["Fld_Condition_Text"] ?? '');
    $nestedData[] = htmlspecialchars($row["Fld_Price"] ?? '');
    $nestedData[] = htmlspecialchars($row["Fld_Currency_Text"] ?? '');
    $nestedData[] = htmlspecialchars($row['Fld_Remark'] ?? '');
    $nestedData[] = htmlspecialchars(trim(($row['source_type'] ?? '') . (($row['source_id'] ?? '') !== '' ? ' #' . $row['source_id'] : '')));
    $nestedData[] = htmlspecialchars($row["lead_time"] ?? '');
    $nestedData[] = htmlspecialchars($row["Fld_Priority_Text"] ?? '');
    $nestedData[] = htmlspecialchars($row["Employee_Name"] ?? '');
    $nestedData[] = $row["ID"]." <a href='download_quote_pdf.php?ID=".(int)$row["ID"]."' target='_blank' class='btn btn-xs btn-default'>PDF</a> <a href='create_po_from_quote.php?quote_id=".(int)$row["ID"]."' class='btn btn-xs btn-default'>Create PO</a>";

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
