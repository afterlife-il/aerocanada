<?php
session_start();
include_once "conf.php";

echo "<h1>Debug Méthode ajout_contact_company()</h1>";

// Simuler données POST réelles
$_POST = [
    'Fld_Contact_Name' => 'TEST DEBUG',
    'Fld_Contact_Email' => 'debug@test.com',
    'Fld_Company_ID' => '5263',
    'Fld_Contact_FirstName' => 'Debug',
    'Fld_Contact_Tel' => '+33111111111',
    'Fld_Contact_Mobile' => '+33222222222',
];

echo "<h2>POST Data:</h2>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

// Charger la classe
require('../classes/company.class.php');

// Voir le code source de la méthode
echo "<h2>Code de la méthode ajout_contact_company():</h2>";

$reflection = new ReflectionClass('company');
$method = $reflection->getMethod('ajout_contact_company');

echo "<pre>";
$filename = $method->getFileName();
$start_line = $method->getStartLine();
$end_line = $method->getEndLine();

$file = file($filename);
$code = implode('', array_slice($file, $start_line - 1, $end_line - $start_line + 1));

echo htmlspecialchars($code);
echo "</pre>";

// Appeler la méthode avec debug SQL
echo "<h2>Appel de la méthode:</h2>";

// Activer debug SQL temporairement
$GLOBALS['debug_mode'] = true;

try {
    $objet = new company();
    $result = $objet->ajout_contact_company();
    
    echo "<p style='color:green; font-size:20px;'>✅ SUCCÈS</p>";
    echo "<pre>";
    var_dump($result);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color:red; font-size:20px;'>❌ ERREUR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Vérifier dans la vraie table
echo "<h2>Contenu de tb_company_contact:</h2>";

$sql = "SELECT * FROM tb_company_contact ORDER BY Fld_Contact_Id DESC LIMIT 5";
$result = mysql2_query($sql);

if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
    
    // Headers
    $first_row = mysqli_fetch_assoc($result);
    echo "<tr style='background:#ddd;'>";
    foreach (array_keys($first_row) as $col) {
        echo "<th style='padding:5px;'>$col</th>";
    }
    echo "</tr>";
    
    // First row
    echo "<tr>";
    foreach ($first_row as $val) {
        echo "<td style='padding:5px;'>" . htmlspecialchars($val ?? '') . "</td>";
    }
    echo "</tr>";
    
    // Other rows
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        foreach ($row as $val) {
            echo "<td style='padding:5px;'>" . htmlspecialchars($val ?? '') . "</td>";
        }
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color:orange;'>⚠️ Table vide</p>";
}

// Chercher si le contact "TEST DEBUG" existe
echo "<h2>Recherche contact 'TEST DEBUG':</h2>";
$sql = "SELECT COUNT(*) as cnt FROM tb_company_contact WHERE Fld_Contact_Name LIKE '%TEST%'";
$result = mysql2_query($sql);
$row = mysqli_fetch_assoc($result);
echo "<p>Nombre de contacts avec 'TEST' dans le nom : <strong>" . $row['cnt'] . "</strong></p>";
?>