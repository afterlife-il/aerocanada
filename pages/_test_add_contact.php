<?php
session_start();
include_once "conf.php";

echo "<h1>Test Add Contact Debug V2</h1>";

// 1. Lister toutes les tables qui contiennent "contact"
echo "<h2>Tables contenant 'contact':</h2>";
$sql = "SHOW TABLES LIKE '%contact%'";
$result = mysql2_query($sql);

echo "<ul>";
while ($row = mysqli_fetch_array($result)) {
    echo "<li><strong>" . $row[0] . "</strong></li>";
}
echo "</ul>";

// 2. Simuler un ajout
$_POST = [
    'Fld_Contact_Name' => 'TEST CONTACT YOHAN',
    'Fld_Contact_Email' => 'yohan@test.com',
    'Fld_Company_ID' => '5263',
    'Fld_Contact_FirstName' => 'Yohan',
    'Fld_Contact_Tel' => '+33123456789',
    'Fld_Contact_Mobile' => '+33987654321',
];

echo "<h2>POST Data envoyé:</h2>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

require('../classes/company.class.php');

echo "<h2>Appel ajout_contact_company():</h2>";

try {
    $objet = new company();
    $donnee = $objet->ajout_contact_company();
    
    echo "<p style='color:green; font-size:20px;'>✅ SUCCÈS</p>";
    
    if (!empty($donnee)) {
        echo "<pre>";
        print_r($donnee);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red; font-size:20px;'>❌ ERREUR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// 3. Tester plusieurs noms de tables possibles
$possible_tables = [
    'tbl_Company_Contact',
    'tbl_company_contact', 
    'tb_Company_Contact',
    'tbl_Contact',
    'tbl_Contacts',
    'tb_contact'
];

echo "<h2>Vérification des contacts dans différentes tables:</h2>";

foreach ($possible_tables as $table) {
    echo "<h3>Table: $table</h3>";
    
    $sql = "SELECT * FROM $table ORDER BY Fld_Contact_Id DESC LIMIT 3";
    
    try {
        $result = mysql2_query($sql);
        
        if (mysqli_num_rows($result) > 0) {
            echo "<table border='1' style='border-collapse:collapse;'>";
            echo "<tr style='background:#ddd;'>";
            
            // Headers
            $first_row = mysqli_fetch_assoc($result);
            foreach (array_keys($first_row) as $col) {
                echo "<th style='padding:5px;'>$col</th>";
            }
            echo "</tr>";
            
            // First row
            echo "<tr>";
            foreach ($first_row as $val) {
                echo "<td style='padding:5px;'>" . htmlspecialchars($val) . "</td>";
            }
            echo "</tr>";
            
            // Other rows
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                foreach ($row as $val) {
                    echo "<td style='padding:5px;'>" . htmlspecialchars($val) . "</td>";
                }
                echo "</tr>";
            }
            
            echo "</table>";
            echo "<p style='color:green;'>✅ Cette table existe et contient des données</p>";
            
        } else {
            echo "<p style='color:orange;'>⚠️ Table existe mais vide</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color:red;'>❌ Table n'existe pas ou erreur: " . $e->getMessage() . "</p>";
    }
}

// 4. Chercher le dernier ID inséré
echo "<h2>Dernier INSERT ID:</h2>";
echo "<p>Last insert ID: " . mysqli_insert_id($db_link) . "</p>";
?>