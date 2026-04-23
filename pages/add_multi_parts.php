<?php
// add_multi_parts.php
session_start();
include_once "conf.php";
include_once "page_titles.php";
if($_SESSION['conectroy']=="parfait"){
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Aerocanada-industries.com</title>

  <!-- CSS vendors -->
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../vendor/metisMenu/metisMenu.min.css" rel="stylesheet">
  <link href="../vendor/datatables-plugins/dataTables.bootstrap.css" rel="stylesheet">
  <link href="../vendor/datatables-responsive/dataTables.responsive.css" rel="stylesheet">
  <link href="../dist/css/sb-admin-2.css" rel="stylesheet">
  <link href="../dist/css/aci-overrides.css" rel="stylesheet"><!-- DOIT rester après sb-admin-2.css -->
  <link href="../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

  <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body>

<div id="wrapper">
  <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
    <?php include "top_menu.php"; ?> <!-- barre rouge (NE PAS modifier) -->
    <?php if(isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
  </nav>

  <?php include "after_nav.php"; ?><!-- CSS d’affichage (pas de JS, pas de backdrop) -->

  <!-- ========= CONTENU DE LA PAGE : UN SEUL page-wrapper/page-wrapper2 ========= -->
  <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">

    <div class="row">
      <div class="col-lg-8">
        <div class="panel panel-default" style="background-color:#ddd;">
          <div class="panel-heading" style="background-color:#A7142A">
            <span style="color:white;">ADD MULTI PARTS</span>
            <a href="javascript:add_pn()" style="color:white;"> + Add A PN</a>
          </div>

          <!--
            tbl_Parts: Fld_Part_ID Fld_Part_Nbr Fld_Part_Desc Fld_Part_MFG Fld_Part_MFG_Old
            Fld_AC_ID Fld_Old_LP Fld_Part_List_Price Fld_Part_Price_Currency_ID
            Fld_Part_LP_Date Fld_Remark status alt_pn Fld_Add_PN_Date aci_contact_entry ata_chapter
          -->
          <div class="panel-body">
            <div class="table-responsive">
              <form id="formajoutpart" role="form" method="post" action="valid_ajout_multi_parts.php">
                <?php $today = date("Y-m-d"); $yeartoday = date("Y"); ?>
                <input type="hidden" name="Fld_Part_LP_Date" value="<?php echo $yeartoday; ?>">
                <input type="hidden" name="Fld_Add_PN_Date" value="<?php echo $today; ?>">
                <input type="hidden" name="aci_contact_entry" value="<?php echo $_SESSION['id_utilisateur']; ?>">
                <!-- compteur unique des lignes ajoutées -->
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
            </div><!-- /.table-responsive -->
          </div><!-- /.panel-body -->
        </div><!-- /.panel -->
      </div><!-- /.col-lg-8 -->
    </div><!-- /.row -->

  </div><!-- /#page-wrapper | #page-wrapper2 -->
</div><!-- /#wrapper -->

<!-- JS vendors (ordre strict) -->
<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="../vendor/metisMenu/metisMenu.min.js"></script>
<script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
<script src="../vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
<script src="../vendor/datatables-responsive/dataTables.responsive.js"></script>
<script src="../dist/js/sb-admin-2.js"></script>

<script>
// Ajout d'une ligne PN propre (pas d'eval, pas de doublon de hidden)
function add_pn(){
  var table = document.getElementById('mytable');
  var tbody = table.tBodies[0];
  var current = tbody.rows.length;      // lignes existantes dans le TBODY
  var next = current + 1;               // index de la nouvelle ligne

  var tr = tbody.insertRow(-1);
  tr.id = 'row_' + next;

  // Col 1: index
  var c1 = tr.insertCell(0);
  c1.textContent = next;

  // Col 2: PN
  var c2 = tr.insertCell(1);
  c2.innerHTML = "<input class='form-control' name='Fld_Part_Nbr"+next+"' id='Fld_Part_Nbr"+next+"' placeholder='PN'>";

  // Col 3: DESCRIPTION
  var c3 = tr.insertCell(2);
  c3.innerHTML = "<input class='form-control' name='Fld_Part_Desc"+next+"' id='Fld_Part_Desc"+next+"' placeholder='DESCRIPTION'>";

  // MAJ compteur unique
  var nb = document.getElementById('nbpnadd');
  if (nb) nb.value = next;
}
</script>
</body>
</html>
<?php
} else {
  echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=".$_SERVER['REQUEST_URI']."\">";
}
?>
