<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
    header("Location: login.php?url=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

if (isset($sql_bdd) && $sql_bdd !== 'aerocanada_yoyamic') {
    die('Invalid database selected.');
}

$ids = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ids']) && is_array($_POST['ids'])) {
    foreach ($_POST['ids'] as $id) {
        $id = (int)$id;
        if ($id > 0) $ids[$id] = $id;
    }
} elseif (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id > 0) $ids[$id] = $id;
}

if (!empty($ids)) {
    $idList = implode(',', $ids);
    mysql2_query("DELETE FROM tbl_Stock_external WHERE Fld_Stock_externe_ID IN ($idList)");
}

header("Location: stock_external.php");
exit();
?>
