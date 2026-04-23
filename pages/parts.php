<?php
session_start();
include_once "confphp7.php";

// Redirection sécurisée si la session n'est pas valide
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
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Aerocanada-industries.com</title>

  <!-- Cookie largeur une seule fois -->
  <script>
  (function(){
    if (!document.cookie.includes("largeur=")) {
      document.cookie = "largeur=" + window.innerWidth + "; path=/";
      location.reload();
    }
  })();
  </script>

  <!-- CSS vendor -->
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../vendor/metisMenu/metisMenu.min.css" rel="stylesheet">
  <link href="../vendor/datatables-plugins/dataTables.bootstrap.css" rel="stylesheet">
  <link href="../vendor/datatables-responsive/dataTables.responsive.css" rel="stylesheet">

  <!-- Thème + overrides (dans cet ordre) -->
  <link href="../dist/css/sb-admin-2.css" rel="stylesheet">
  <link href="../dist/css/aci-overrides.css" rel="stylesheet"> <!-- <= impératif, APRÈS sb-admin-2.css -->

  <!-- Fonts -->
  <link href="../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

  <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>

<body>

<div id="wrapper">
  <!-- ===== Barre rouge + (optionnel) menu gauche ===== -->
  <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
    <?php include "top_menu.php"; ?>   <!-- barre rouge avec SON burger -->
    <?php if (!empty($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
</nav>
<?php include "after_nav.php"; ?>

  <!-- ===== UNIQUE page-wrapper ===== -->
  <div id="<?php echo (!empty($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">

    <div class="row">
      <div class="col-lg-12" align="center">
        <!-- (vide, si tu veux un header de page tu peux le mettre ici) -->
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-heading">
            PARTS
            <a data-toggle="modal" data-target="#myModal" style="cursor:pointer;">
              <img src="images/add.png" width="30"> +ADD A PN
            </a>
          </div>
          <div class="panel-body">
            <table width="100%" class="table table-striped table-bordered table-hover" id="dataTablespars">
              <thead>
                <tr>
                  <th>P/N</th>
                  <th>ALT PN</th>
                  <th>DESC</th>
                  <th>MFG/OEM</th>
                  <th>A/C</th>
                  <th>LP</th>
                  <th>$/€</th>
                  <th>LP DATE</th>
                  <th>REMARK</th>
                  <?php if (!empty($_SESSION['statut']) && $_SESSION['statut']=="SuperAdmin"){ ?>
                    <th></th>
                  <?php } ?>
                </tr>
              </thead>
            </table>

            <div style="display:none" id="bloccompany">
              <div id="divcompany" align="center"></div>
            </div>
          </div><!-- /.panel-body -->
        </div><!-- /.panel -->
      </div><!-- /.col -->
    </div><!-- /.row -->

  </div><!-- /#page-wrapper|2 -->
</div><!-- /#wrapper -->

<!-- ===== Modal ADD PN ===== -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background-color:#A7142A;">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color:#FFFFFF;font-weight:bold;">×</button>
        <h4 class="modal-title" id="myModalLabel" style="color:#FFFFFF;font-weight:bold;">ADD A PART</h4>
      </div>

      <div class="modal-body">
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-4">
              <label for="Fld_Part_Nbr" class="col-form-label">PN:</label>
              <input type="text" class="form-control" id="Fld_Part_Nbr" />
            </div>
            <div class="col-md-4">
              <label for="Fld_Part_Desc" class="col-form-label">DESCRIPTION:</label>
              <input type="text" class="form-control" id="Fld_Part_Desc" />
            </div>
          </div>
        </div>
      </div>

      <div class="modal-body">
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-8">
              <label for="alt_pn" class="col-form-label">ALT PN:</label>
              <textarea class="form-control" rows="3" id="alt_pn"></textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-body">
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-4">
              <label for="Fld_Part_MFG" class="col-form-label">MFG/OEM:</label>
              <input type="text" id="Fld_Part_MFG" size="30" class="Fld_Part_MFG" placeholder="Please Enter company">
            </div>
            <div class="col-md-4">
              <label for="oem_lead_time" class="col-form-label">OEM LEAD TIME:</label>
              <input type="text" class="form-control" id="oem_lead_time" />
            </div>
          </div>
        </div>
      </div>

      <div class="modal-body">
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-4">
              <label for="Fld_Part_List_Price" class="col-form-label">LIST PRICE:</label>
              <input type="text" class="form-control" id="Fld_Part_List_Price">
            </div>
            <div class="col-md-4">
              <label for="Fld_Part_Price_Currency_ID" class="col-form-label">CURRENCY:</label>
              <select class="form-control" id="Fld_Part_Price_Currency_ID">
                <?php
                $sqldiv="SELECT * FROM tbl_Currency";
                $result = mysqli_query($link, $sqldiv);
                while ($data = mysqli_fetch_array($result, MYSQLI_BOTH)) {
                  echo "<option value='".$data['Fld_Currency_ID']."'>".$data['Fld_Currency_Text']."</option>";
                }
                ?>
              </select>
            </div>
            <div class="col-md-4">
              <label for="Fld_Part_LP_Date" class="col-form-label">LP DATE:</label>
              <input type="text" class="form-control" id="Fld_Part_LP_Date">
            </div>
          </div>
        </div>
      </div>

      <div class="modal-body">
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-4">
              <label for="Fld_AC_ID" class="col-form-label">AIRCRAFT:</label>
              <select class="form-control" id="Fld_AC_ID">
                <option value=""></option>
                <?php
                $sqldiv2="SELECT Distinct(Fld_AC_Model),Fld_AC_ID FROM tbl_Aircraft ORDER BY Fld_AC_Model";
                $result2 = mysqli_query($link, $sqldiv2);
                while ($data2 = mysqli_fetch_array($result2, MYSQLI_BOTH)) {
                  echo "<option value='".$data2['Fld_AC_ID']."'>".$data2['Fld_AC_Model']."</option>";
                }
                ?>
              </select>
            </div>
            <div class="col-md-4">
              <label for="ata_chapter" class="col-form-label">ATA CHAPTER:</label>
              <input type="text" class="form-control" id="ata_chapter" />
            </div>
          </div>
        </div>
      </div>

      <div class="modal-body">
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-8">
              <label for="Fld_Remark" class="col-form-label">REMARK:</label>
              <textarea class="form-control" rows="3" id="Fld_Remark"></textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save</button>
      </div>
    </div>
  </div>
</div>
<!-- /Modal -->

<!-- JS vendor (ordre strict) -->
<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="../vendor/metisMenu/metisMenu.min.js"></script>
<script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
<script src="../vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
<script src="../vendor/datatables-responsive/dataTables.responsive.js"></script>
<script src="../dist/js/sb-admin-2.js"></script>

<!-- Typeahead -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.10.3/typeahead.bundle.min.js"></script>

<!-- Init page -->
<script>
$(function(){
  // DataTable
  var oTable = $('#dataTablespars').DataTable({
    responsive: true,
    processing: true,
    serverSide: true,
    ajax: "partsdata.php",
    pageLength: 25
  });

  // Typeahead MFG
  $('input.Fld_Part_MFG').typeahead({
    name: 'Fld_Company_Name',
    id: 'Fld_Company_ID',
    remote: 'list-company.php?query=%QUERY'
  });

  // Save from modal
  $('#myModal').on('click', '.btn-primary', function(){
    $.get('add_pn_from_popup.php', {
      Fld_Part_Nbr: $('#Fld_Part_Nbr').val(),
      Fld_Part_Desc: $('#Fld_Part_Desc').val(),
      alt_pn: $('#alt_pn').val(),
      Fld_Part_MFG: $('#Fld_Part_MFG').val(),
      oem_lead_time: $('#oem_lead_time').val(),
      Fld_Part_List_Price: $('#Fld_Part_List_Price').val(),
      Fld_Part_Price_Currency_ID: $('#Fld_Part_Price_Currency_ID').val(),
      Fld_Part_LP_Date: $('#Fld_Part_LP_Date').val(),
      Fld_AC_ID: $('#Fld_AC_ID').val(),
      ata_chapter: $('#ata_chapter').val(),
      Fld_Remark: $('#Fld_Remark').val()
    }, function(){ /* ok */ });
    $('#myModal').modal('hide');
  });

  // Détails company (inchangé)
});
</script>

<!-- Tes fonctions détail company (inchangées) -->
<script>
function detailcompany(id){
  var bloc=document.getElementById('bloccompany');
  bloc.style.display='table-row';
  document.getElementById("divcompany").innerHTML='<div align="center"><img src="../images/loader.gif"></div>';
  var xhr=new XMLHttpRequest();
  xhr.open("POST","detailcompany.php?id="+id,true);
  xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
  xhr.onreadystatechange=function(){ up_donnee_courrier(xhr,id); };
  xhr.send("id="+id);
}
function up_donnee_courrier(xhr,id){
  if(xhr.readyState==4){
    var resp=xhr.responseText;
    document.getElementById('divcompany').innerHTML='<div id="'+id+'" align="center">'+resp+'</div>';
    document.location.href="#bloccompany";
  }
}
function fermeturedetailcompany(){
  var bloc=document.getElementById('bloccompany');
  if(bloc.style.display=='table-row') bloc.style.display='none';
}
</script>

<style>
/* tes styles typeahead, inchangés */
.tt-hint, .Fld_Part_MFG{
  border:1px solid #CCC;
  font-size:24px;
  line-height:30px;
  outline:none;
  padding:8px 12px;
  width:100%;
}
.tt-dropdown-menu{
  width:400px; margin-top:5px; padding:8px 12px;
  background:#F1F1F1; border:1px solid rgba(0,0,0,.2); border-radius:8px;
  font-size:18px; color:#111;
}
</style>

</body>
</html>
