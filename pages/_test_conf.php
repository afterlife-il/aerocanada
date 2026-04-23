<?php
require_once 'conf.php';

echo "<h1>Test conf.php</h1>";

echo "<p>\$db_link : " . (isset($db_link) ? '✅ OK' : '❌ FAILED') . "</p>";
echo "<p>\$conn : " . (isset($conn) ? '✅ OK' : '❌ FAILED') . "</p>";
echo "<p>\$conn === \$db_link : " . ((isset($conn, $db_link) && $conn === $db_link) ? '✅ OK' : '❌ FAILED') . "</p>";

// Test query (sans try/catch, car mysql2_query() fait die() en cas d'erreur)
$test_query = "SELECT COUNT(*) as cnt FROM tbl_Employee";
$result = mysql2_query($test_query);
$row = mysqli_fetch_assoc($result);

echo "<p>Users count : ✅ " . (int)$row['cnt'] . "</p>";