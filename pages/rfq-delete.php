<?php
// rfq-delete.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] != "parfait") {
    echo json_encode(array(
        'success' => false,
        'error'   => 'not_logged'
    ));
    exit;
}

include_once "conf.php";

if (empty($_POST['rfq_id'])) {
    echo json_encode(array(
        'success' => false,
        'error'   => 'missing_rfq_id'
    ));
    exit;
}

$rfq_id = trim($_POST['rfq_id']);
$rfq_id_safe = addslashes($rfq_id);

// Suppression de TOUTES les lignes de cette RFQ dans tbl_RFQ_1
$sql_delete = "
    DELETE FROM tbl_RFQ_1
    WHERE Fld_RFQ_ID = '" . $rfq_id_safe . "'
";

$res = mysql2_query($sql_delete);

if ($res) {
    echo json_encode(array(
        'success' => true
    ));
} else {
    echo json_encode(array(
        'success' => false,
        'error'   => 'sql_error'
    ));
}
exit;
