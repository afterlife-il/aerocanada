<?php
$today = date("Y-m-d");


//Entrez ici les informations de votre base de données et le nom du fichier de sauvegarde.
$mysqlDatabaseName ='aerocanada';//nom de la base de données
$mysqlUserName ='aerocanada-indus';//Nom dutilisateur
$mysqlPassword ='Gmm5s3~3';//Mot de passe
$mysqlHostName ='localhost:3306';//dbxxx.hosting-data.io
$mysqlExportPath ='sauvegarde-bdd-aerocanada'.$today.'.sql';//nom-du-fichier-dexport.sql

//Veuillez ne pas modifier les points suivants
//Exportation de la base de données et résultat
$command='mysqldump --opt -h' .$mysqlHostName .' -u' .$mysqlUserName .' -p' .$mysqlPassword .' ' .$mysqlDatabaseName .' > ' .$mysqlExportPath;
exec($command,$output=array(),$worked);
switch($worked){
case 0:
echo 'La base de données <b>' .$mysqlDatabaseName .'</b> a été stockée avec succès dans le chemin suivant '.getcwd().'/' .$mysqlExportPath .'</b>';
break;
case 1:
echo 'Une erreur s est produite lors de la exportation de <b>' .$mysqlDatabaseName .'</b> vers'.getcwd().'/' .$mysqlExportPath .'</b>';
break;
case 2:
echo 'Une erreur d exportation s est produite, veuillez vérifier les informations suivantes : <br/><br/><table><tr><td>MySQL Database Name:</td><td><b>' .$mysqlDatabaseName .'</b></td></tr><tr><td>MySQL User Name:</td><td><b>' .$mysqlUserName .'</b></td></tr><tr><td>MySQL Password:</td><td><b>NOTSHOWN</b></td></tr><tr><td>MySQL Host Name:</td><td><b>' .$mysqlHostName .'</b></td></tr></table>';
break;
}

phpinfo();
?>