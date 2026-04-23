<?php
session_start();

echo "Session ID : " . session_id();

if (!isset($_SESSION['test'])) {
    $_SESSION['test'] = "Session active";
} else {
    $_SESSION['test'] = "Session mise à jour : " . time();
}
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "Chemin de stockage des sessions : " . session_save_path() . "<br>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

?>
