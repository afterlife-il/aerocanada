<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
  header("Location: login.php?url=" . urlencode($_SERVER['REQUEST_URI']));
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Aerocanada-industries.com</title>

  <!-- CSS -->
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../vendor/metisMenu/metisMenu.min.css" rel="stylesheet">
  <link href="../vendor/datatables-plugins/dataTables.bootstrap.css" rel="stylesheet">
  <link href="../vendor/datatables-responsive/dataTables.responsive.css" rel="stylesheet">
  <link href="../dist/css/sb-admin-2.css" rel="stylesheet">
  <link href="../dist/css/aci-overrides.css?v=8" rel="stylesheet"> <!-- APRES sb-admin-2.css -->
  <link href="../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

  <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body>

<div id="wrapper">
  <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
    <?php include "top_menu.php"; ?>           <!-- barre rouge -->
    <?php if (!empty($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
  </nav>

  <?php include "after_nav.php"; ?>            <!-- backdrop + sync état mobile -->

  <!-- CONTENU DE PAGE — NE PAS OUVRIR UN 2e page-wrapper -->
  <div id="<?php echo (!empty($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-12">
          <h1 class="page-header">Validation Quote</h1>

<?php
// --------- VOTRE PHP ---------
require('../classes/rfq.class.php');
$objet  = new rfq();
$donnee = $objet->add_quote_RFQ3();

// RFQ
$sqlrfq  = "SELECT * FROM tbl_RFQ_1 WHERE Fld_RFQ_ID='".mysqli_real_escape_string($link, $_POST['RFQ_ID'])."'";
$reqrfq  = mysql2_query($sqlrfq);
$datarfq = mysqli_fetch_array($reqrfq);
$contactaci = $datarfq['Employee_ID'];

// Contact
$sqlr = "SELECT * FROM tb_company_contact WHERE id_company_contact=".$datarfq['id_company_contact'];
$reqr = mysql2_query($sqlr);
$datar = mysqli_fetch_array($reqr);

// … construction de $message_html (inchangée) …

echo $message_html;
// --------- FIN VOTRE PHP ---------
?>

        </div>
      </div>
    </div>
  </div><!-- /#page-wrapper | #page-wrapper2 -->
</div><!-- /#wrapper -->

<!-- JS (ordre strict) -->
<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="../vendor/metisMenu/metisMenu.min.js"></script>
<script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
<script src="../vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
<script src="../vendor/datatables-responsive/dataTables.responsive.js"></script>
<script src="../dist/js/sb-admin-2.js"></script>

<script>
// Initialisation DataTable uniquement si la table existe sur la page
$(function(){
  var $t = $('#dataTables-example');
  if ($t.length) $t.DataTable({ responsive:true });
});
</script>

</body>
</html>
