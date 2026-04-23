<?php
session_start();
include_once "conf.php";

echo "<h1>Test Final Add Contact</h1>";

// Simuler EXACTEMENT ce que le formulaire envoie
$_POST = [
    'nbcontactcompanyajout' => '1',
    'companyid' => '6082,ACI770 - ESSAI2',
    'Fld_Company_ID' => '6082',
    'Fld_Contact_Name1' => 'TEST FINAL YOHAN',
    'Fld_Contact_Phone1' => '1234567890',
    'Fld_Contact_Phone21' => '0987654321',
    'Fld_Contact_Fax1' => '',
    'Fld_Company_Mobile1' => '+33612345678',
    'Fld_Contact_Division_ID1' => '1', // Sales (première option)
    'Fld_Contact_Email1' => 'test.final@yohan.com',
    'Fld_Contact_Title1' => 'Manager',
    'Fld_Contact_Remark1' => 'Test final contact',
];

echo "<h2>POST Data:</h2>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

require('../classes/company.class.php');

echo "<h2>Appel ajout_contact_company():</h2>";

try {
    $objet = new company();
    $result = $objet->ajout_contact_company();
    
    echo "<p style='color:green; font-size:24px;'>✅ SUCCÈS</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red; font-size:24px;'>❌ ERREUR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Vérifier dans la DB
echo "<h2>Contacts dans tb_company_contact pour company 6082:</h2>";

$sql = "SELECT * FROM tb_company_contact WHERE Fld_Company_ID=6082 ORDER BY Fld_Contact_Id DESC LIMIT 5";
$result = mysql2_query($sql);

if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
    
    $first_row = mysqli_fetch_assoc($result);
    echo "<tr style='background:#ddd;'>";
    foreach (array_keys($first_row) as $col) {
        echo "<th style='padding:5px;'>$col</th>";
    }
    echo "</tr>";
    
    echo "<tr>";
    foreach ($first_row as $val) {
        echo "<td style='padding:5px;'>" . htmlspecialchars($val ?? '') . "</td>";
    }
    echo "</tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        foreach ($row as $val) {
            echo "<td style='padding:5px;'>" . htmlspecialchars($val ?? '') . "</td>";
        }
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<p style='color:green; font-size:20px;'>✅ " . mysqli_num_rows($result) . " contact(s) trouvé(s)</p>";
    
} else {
    echo "<p style='color:red; font-size:20px;'>❌ Aucun contact trouvé pour company 6082</p>";
}

// Last insert ID
echo "<h2>Last Insert ID:</h2>";
echo "<p style='font-size:20px;'>" . mysqli_insert_id($db_link) . "</p>";
?>