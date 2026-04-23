<?php
// rfq-list-data.php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
    echo json_encode([
        "draw"            => 0,
        "recordsTotal"    => 0,
        "recordsFiltered" => 0,
        "data"            => []
    ]);
    exit;
}

include_once "conf.php"; // doit définir $conn (mysqli)

/*
 * Paramètres envoyés par DataTables
 */
$draw   = isset($_GET['draw'])   ? (int)$_GET['draw']   : 0;
$start  = isset($_GET['start'])  ? (int)$_GET['start']  : 0;
$length = isset($_GET['length']) ? (int)$_GET['length'] : 50;

if ($length <= 0 || $length > 200) {
    $length = 50;
}

$searchValue = "";
if (isset($_GET['search']['value'])) {
    $searchValue = trim($_GET['search']['value']);
}

/*
 * 1) Nombre total de RFQ (sans filtre)
 */
$sql_total = "SELECT COUNT(DISTINCT Fld_RFQ_ID) AS cnt FROM tbl_RFQ_1";
$res_total = mysqli_query($conn, $sql_total);
$row_total = $res_total ? mysqli_fetch_assoc($res_total) : ['cnt' => 0];
$recordsTotal = (int)$row_total['cnt'];

/*
 * 2) WHERE pour le filtre global (barre de recherche DataTables)
 */
$where = " WHERE 1=1 ";

if ($searchValue !== '') {
    $sv = mysqli_real_escape_string($conn, $searchValue);
    $like = "'%" . $sv . "%'";

    $where .= " AND (
        r.Fld_RFQ_ID          LIKE $like
        OR c.Fld_Company_Name LIKE $like
        OR cc.Fld_Contact_Name LIKE $like
        OR t.Fld_RFQ_Type_Text LIKE $like
        OR p.Fld_Priority_Text LIKE $like
        OR e.Employee_Name     LIKE $like
    )";
}

/*
 * 3) Nombre filtré
 */
$recordsFiltered = $recordsTotal;

if ($searchValue !== '') {
    $sql_filtered = "
        SELECT COUNT(DISTINCT r.Fld_RFQ_ID) AS cnt
        FROM tbl_RFQ_1 r
        LEFT JOIN tb_company c
               ON c.Fld_Company_ID = r.Fld_Customer_ID
        LEFT JOIN tb_company_contact cc
               ON cc.id_company_contact = r.id_company_contact
        LEFT JOIN tbl_RFQ_Type t
               ON t.Fld_RFQ_Type_ID = r.Fld_RFQ_Type_ID
        LEFT JOIN tbl_Priority p
               ON p.Fld_Priority_ID = r.Fld_Priority_ID
        LEFT JOIN tbl_Employee e
               ON e.Employee_ID = r.Employee_ID
        $where
    ";
    $res_filtered = mysqli_query($conn, $sql_filtered);
    $row_filtered = $res_filtered ? mysqli_fetch_assoc($res_filtered) : ['cnt' => 0];
    $recordsFiltered = (int)$row_filtered['cnt'];
}

/*
 * 4) Récupération des données paginées
 *    1 RFQ = 1 ligne (GROUP BY r.Fld_RFQ_ID)
 */
$sql_data = "
    SELECT
        r.Fld_RFQ_ID,
        MAX(r.date)               AS rfq_date,
        c.Fld_Company_Name        AS company_name,
        cc.Fld_Contact_Name       AS contact_name,
        t.Fld_RFQ_Type_Text       AS rfq_type,
        p.Fld_Priority_Text       AS priority_text,
        e.Employee_Name           AS sales_contact,
        COUNT(*)                  AS pn_count
    FROM tbl_RFQ_1 r
    LEFT JOIN tb_company c
           ON c.Fld_Company_ID = r.Fld_Customer_ID
    LEFT JOIN tb_company_contact cc
           ON cc.id_company_contact = r.id_company_contact
    LEFT JOIN tbl_RFQ_Type t
           ON t.Fld_RFQ_Type_ID = r.Fld_RFQ_Type_ID
    LEFT JOIN tbl_Priority p
           ON p.Fld_Priority_ID = r.Fld_Priority_ID
    LEFT JOIN tbl_Employee e
           ON e.Employee_ID = r.Employee_ID
    $where
    GROUP BY r.Fld_RFQ_ID
    ORDER BY r.Fld_RFQ_ID DESC
    LIMIT $start, $length
";

$res_data = mysqli_query($conn, $sql_data);

$data = [];

if ($res_data && mysqli_num_rows($res_data) > 0) {
    while ($row = mysqli_fetch_assoc($res_data)) {

        $rfq_id       = $row['Fld_RFQ_ID'];
        $rfq_date_raw = $row['rfq_date'];

        $company_name  = $row['company_name']  ?: '';
        $contact_name  = $row['contact_name']  ?: '';
        $rfq_type      = $row['rfq_type']      ?: '';
        $priority_text = $row['priority_text'] ?: '';
        $sales_contact = $row['sales_contact'] ?: '';
        $pn_count      = (int)$row['pn_count'];

        // Format date
        $rfq_date_disp = $rfq_date_raw;
        if (!empty($rfq_date_raw) && $rfq_date_raw != '0000-00-00') {
            $ts = strtotime($rfq_date_raw);
            if ($ts) {
                $rfq_date_disp = date('d/m/Y', $ts);
            }
        }

        // RFQ ID cliquable
        $rfq_id_html = '<a href="valid_add_multi_pn_rfq.php?Fld_RFQ_ID='
                     . htmlspecialchars($rfq_id, ENT_QUOTES, 'UTF-8') . '">'
                     . htmlspecialchars($rfq_id, ENT_QUOTES, 'UTF-8')
                     . '</a>';

        // Bouton poubelle (même classe que dans rfq-list.php)
        $actions_html = '<a href="#" class="btn btn-xs btn-danger btn-delete-rfq" '
                      . 'data-rfq-id="' . htmlspecialchars($rfq_id, ENT_QUOTES, 'UTF-8') . '" '
                      . 'title="Delete RFQ">'
                      . '<i class="fa fa-trash"></i>'
                      . '</a>';

        $data[] = [
            $rfq_id_html,
            htmlspecialchars($rfq_date_disp, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($company_name,  ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($contact_name,  ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($rfq_type,      ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($priority_text, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($sales_contact, ENT_QUOTES, 'UTF-8'),
            $pn_count,
            $actions_html
        ];
    }
}

/*
 * 5) Réponse JSON
 */
echo json_encode([
    "draw"            => $draw,
    "recordsTotal"    => $recordsTotal,
    "recordsFiltered" => $recordsFiltered,
    "data"            => $data
]);
exit;
