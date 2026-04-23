<?php
session_start();

/* Database connection start */
include_once "conf.php";
include_once "page_titles.php";
/* Database connection end */

// Réponse JSON par défaut
header('Content-Type: application/json; charset=utf-8');

if (!isset($_REQUEST['query'])) {
    echo json_encode([]);
    exit;
}

$recherche = trim((string)$_REQUEST['query']);
$recherche = str_replace('%20', ' ', $recherche);
$recherche = escape_data($recherche); // échappement SQL

// IMPORTANT : 1 seule ligne par compagnie grâce à DISTINCT
$sql = mysql2_query("
    SELECT DISTINCT c.Fld_Company_ID, c.Fld_Company_Name
    FROM tb_company c
    WHERE c.Fld_Company_Name LIKE '%{$recherche}%'
       OR c.Fld_Company_ID LIKE '%{$recherche}%'
    ORDER BY c.Fld_Company_Name ASC
    LIMIT 50
");

$array = [];
$seen  = []; // anti-doublons JSON par sécurité

while ($row = mysqli_fetch_assoc($sql)) {
    $cid  = $row['Fld_Company_ID'];
    $name = $row['Fld_Company_Name'];

    if (isset($seen[$cid])) {
        continue; // évite doublon au cas où
    }
    $seen[$cid] = true;

    $array[] = [
        // 'value' = ce que ton autocomplete envoie ensuite (ID + nom)
        'value' => $cid . ',' . addslashes($name),
        // 'label' = ce qui s’affiche visuellement dans la liste
        'label' => addslashes($name),
    ];
}

echo json_encode($array, JSON_UNESCAPED_UNICODE);
