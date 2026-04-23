<?php
session_start();
include_once "conf.php";

require('../classes/company.class.php');

echo "<h1>Code complet de ajout_contact_company()</h1>";

$reflection = new ReflectionClass('company');
$method = $reflection->getMethod('ajout_contact_company');

$filename = $method->getFileName();
$start_line = $method->getStartLine();
$end_line = $method->getEndLine();

$file = file($filename);
$code = implode('', array_slice($file, $start_line - 1, $end_line - $start_line + 1));

echo "<pre style='background:#f4f4f4; padding:20px; border:1px solid #ccc;'>";
echo htmlspecialchars($code);
echo "</pre>";

// Copier dans presse-papier
echo "<h2>Code brut (pour copier/coller) :</h2>";
echo "<textarea style='width:100%; height:400px; font-family:monospace;'>";
echo $code;
echo "</textarea>";
?>