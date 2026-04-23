<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include_once "conf.php";

if (!isset($conn)) {
    die("Connexion échouée - \$conn non défini");
}

$res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tb_company");
$row = mysqli_fetch_assoc($res);

echo "Nombre de compagnies : " . $row['total'];
