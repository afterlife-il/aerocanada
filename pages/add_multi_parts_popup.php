<?php
// add_multi_parts_popup.php
session_start();
include_once "conf.php";
include_once "page_titles.php";
if ($_SESSION['conectroy'] == "parfait") {
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Aerocanada-industries.com</title>

  <!-- CSS -->
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../vendor/metisMenu/metisMenu.min.css" rel="stylesheet">
  <link href="../vendor/datatables-plugins/dataTables.bootstrap.css" rel="stylesheet">
  <link href="../vendor/datatables-responsive/dataTables.responsive.css" rel="stylesheet">
  <link href="../dist/css/sb-admin-2.css" rel="stylesheet">
  <link href="../dist/css/aci-overrides.css" rel="stylesheet"> <!-- APRES sb-admin-2.css -->
  <link href="../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

  <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body>

<div id="wrapper">
  <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
    <?php include "top_menu.php"; ?>                       <!-- barre rouge -->
    <?php if (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
  </nav>

  <?php include "after_nav.php"; ?>

  <!-- ========== UN SEUL page-wrapper / page-wrapper2 ========== -->
  <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">

    <div class="row">
      <div class="col-lg-8"></div>
    </div>

    <div class="row">
      <div class="col-lg-8">
        <div class="panel panel-default" style="background-color:#ddd;">
          <div class="panel-heading" style="background-color:#A7142A">
            <span style="color:#fff;">ADD MULTI PARTS</span>
            <a href="#" onclick="add_pn();return false;" style="color:#fff;"> + Add A PN</a>
          </div>

          <div class="panel-body">
            <div class="table-responsive">
              <form id="formajoutpart" role="form" method="post" action="valid_ajout_multi_parts.php">
                <input type="hidden" name="origine" value="popup">
                <?php $today = date("Y-m-d"); $yeartoday = date("Y"); ?>
                <input type="hidden" name="Fld_Part_LP_Date" value="<?php echo $yeartoday; ?>">
                <input type="hidden" name="Fld_Add_PN_Date" value="<?php echo $today; ?>">
                <input type="hidden" name="aci_contact_entry" value="<?php echo $_SESSION['id_utilisateur']; ?>">
                <input type="hidden" name="nbpnadd" id="nbpnadd" value="1">

                <table class="table table-striped table-bordered table-hover" id="mytable">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>PN</th>
                      <th>DESCRIPTION</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr class="odd gradeX" id="row_1">
                      <td>1</td>
                      <td><input class="form-control" name="Fld_Part_Nbr1" id="Fld_Part_Nbr1" placeholder="PN"></td>
                      <td><input class="form-control" name="Fld_Part_Desc1" id="Fld_Part_Desc1" placeholder="DESCRIPTION"></td>
                    </tr>
                  </tbody>
                </table>

                <input type="submit" value="submit" class="form-control">
              </form>
            </div>
          </div><!-- /.panel-body -->
        </div><!-- /.panel -->
      </div><!-- /.col-lg-8 -->
    </div><!-- /.row -->

  </div><!-- /#page-wrapper OR #page-wrapper2 -->
</div><!-- /#wrapper -->

<!-- JS -->
<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="../vendor/metisMenu/metisMenu.min.js"></script>
<script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
<script src="../vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
<script src="../vendor/datatables-responsive/dataTables.responsive.js"></script>
<script src="../dist/js/sb-admin-2.js"></script>

<script>
// Initialisation DataTables (seulement si un tableau cible existe sur la page)
$(function(){
  if ($('#dataTables-example').length) {
    $('#dataTables-example').DataTable({ responsive:true });
  }
});

// Ajout d'une ligne PN
function add_pn(){
  var table = document.getElementById('mytable');
  var tbody = table.tBodies[0];
  var next  = 
  tbody.rows.length + 1; // 1ère ligne existe déjà => +1

  var tr = tbody.insertRow(-1);
  tr.id = 'row_' + next;

  var td0 = tr.insertCell(0);
  td0.textContent = next;

  var td1 = tr.insertCell(1);
  td1.innerHTML = "<input class='form-control' name='Fld_Part_Nbr"+next+"' id='Fld_Part_Nbr"+next+"' placeholder='PN'>";

  var td2 = tr.insertCell(2);
  td2.innerHTML = "<input class='form-control' name='Fld_Part_Desc"+next+"' id='Fld_Part_Desc"+next+"' placeholder='DESCRIPTION'>";

  // on met à jour l’unique hidden nbpnadd
  var nb = document.getElementById('nbpnadd');
  if (nb) nb.value = next;
}
</script>

</body>
</html>
<?php
} else {
  echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php\">";
}
?>
