<?php
session_start();
include_once "confphp7.php";

// Sécurité : redirige si l'utilisateur n'est pas connecté
if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
    header("Location: login.php?url=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Test Header</title>

  <!-- Bootstrap Core CSS -->
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

  <!-- MetisMenu CSS -->
  <link href="../vendor/metisMenu/metisMenu.min.css" rel="stylesheet">

  <!-- DataTables CSS -->
  <link href="../vendor/datatables-plugins/dataTables.bootstrap.css" rel="stylesheet">

  <!-- DataTables Responsive CSS -->
  <link href="../vendor/datatables-responsive/dataTables.responsive.css" rel="stylesheet">

  <!-- Custom CSS (corrigé) -->
   <link href="../dist/css/sb-admin-2.css" rel="stylesheet">
<link href="../dist/css/aci-overrides.css" rel="stylesheet"> <!-- <= impératif, et APRÈS sb-admin-2.css -->

  <!-- Custom Fonts -->
  <link href="../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
</head>
<body>
<div id="wrapper">
  <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
    <?php include "top_menu.php"; ?>                       <!-- barre rouge -->
    <?php if(isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
</nav>
<?php include "after_nav.php"; ?>

  <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">
    <!-- ton contenu -->
  </div>
</div>

  <div class="container">
    <h1 class="text-primary">✅ Ceci est un test de header</h1>
    <p class="lead">Si tout s'affiche avec une belle mise en page (polices, marges, boutons stylés), alors le CSS est bien chargé.</p>
  </div>

</div>
</body>
</html>
