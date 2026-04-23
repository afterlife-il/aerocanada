<?php
require_once('../conf.php'); // même include que sur tes autres ajax
header('Content-Type: application/json; charset=utf-8');

$term = isset($_GET['term']) ? trim($_GET['term']) : '';
$out  = [];

if ($term !== '') {
    $esc = mysqli_real_escape_string($conn, $term);
    // Adapter le WHERE à ton schéma/état (j’exclus 'Deleted' si tu as ce champ)
    $sql = "
        SELECT Fld_Company_ID AS id, Fld_Company_Name AS label
        FROM tb_company
        WHERE Fld_Company_Name LIKE '%$esc%'
        ORDER BY Fld_Company_Name
        LIMIT 20
    ";
    $rs = mysql2_query($sql);
    if ($rs) {
        while ($row = mysqli_fetch_assoc($rs)) {
            $out[] = ['id' => (int)$row['id'], 'label' => $row['label']];
        }
    }
}

echo json_encode($out);
