<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
if($_SESSION['conectroy']=="parfait"){
require('../classes/company.class.php');
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

    <!-- Bootstrap Core CSS -->
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- MetisMenu CSS -->
    <link href="../vendor/metisMenu/metisMenu.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="../vendor/datatables-plugins/dataTables.bootstrap.css" rel="stylesheet">
    <!-- DataTables Responsive CSS -->
    <link href="../vendor/datatables-responsive/dataTables.responsive.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../dist/css/sb-admin-2.css" rel="stylesheet">
    <link href="../dist/css/aci-overrides.css" rel="stylesheet"> <!-- <= impératif, et APRÈS sb-admin-2.css -->
    <!-- Custom Fonts -->
    <link href="../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!--CSS rating ajoute par roy-->
    <link href="rating.css" rel="stylesheet">
    <!--Fin CSS rating ajoute par roy-->
</head>

<body>
<div id="wrapper">
  <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
    <?php include "top_menu.php"; ?>                       <!-- barre rouge -->
    <?php if(isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
  </nav>
  <?php include "after_nav.php"; ?>

  <div id="page-wrapper">
    <div class="row">
      <!-- /.row -->
      <?php if($_SESSION['statut']=="SuperAdmin"){ ?>
      <div class="row">
        <div class="col-lg-4">
          <div class="panel panel-default">
            <div class="panel-heading">
              SHIPPERS <a href="javascript:add_shipper()"> + Add A SHIPPER</a>
            </div>
            <div class="panel-body">
              <div class="table-responsive">
                <form action='validation_shipper.php' method="post">
                  <?php
                  /* ========= TRI A->Z / Z->A côté serveur, sûr et sans casser le reste ========= */
                  $objet = new company();
                  $donnee = $objet->affichage_shippers();   // tableau associatif attendu

                  // Paramètres de tri GET
                  $sort = isset($_GET['sort']) && in_array($_GET['sort'], ['id','name'], true) ? $_GET['sort'] : 'name';
                  $dir  = isset($_GET['dir'])  && in_array(strtolower($_GET['dir']), ['asc','desc'], true) ? strtolower($_GET['dir']) : 'asc';

                  // Tri de l'array $donnee (on ne touche pas au SQL ni à la classe)
                  if (is_array($donnee)) {
                    usort($donnee, function($a, $b) use ($sort, $dir) {
                      if ($sort === 'id') {
                        $res = ((int)$a['Fld_Shipper_ID']) <=> ((int)$b['Fld_Shipper_ID']);
                      } else {
                        $A = isset($a['Fld_Shipper_Text']) ? mb_strtoupper($a['Fld_Shipper_Text'], 'UTF-8') : '';
                        $B = isset($b['Fld_Shipper_Text']) ? mb_strtoupper($b['Fld_Shipper_Text'], 'UTF-8') : '';
                        $res = strcoll($A, $B); // respecte accents si locale UTF-8
                        if ($res === 0) { // fallback si locale absente
                          $res = strcasecmp($A, $B);
                        }
                      }
                      return ($dir === 'asc') ? $res : -$res;
                    });
                  }

                  // Prépare les toggles et les flèches
                  $idDirToggle   = ($sort === 'id'   && $dir === 'asc') ? 'desc' : 'asc';
                  $nameDirToggle = ($sort === 'name' && $dir === 'asc') ? 'desc' : 'asc';
                  $arrow = function($col) use ($sort, $dir) {
                    if ($sort === $col) return $dir === 'asc' ? ' ▲' : ' ▼';
                    return '';
                  };
                  ?>
                  <table class="table table-striped table-bordered table-hover" id="mytable">
                    <thead>
                      <tr>
                        <th style="white-space:nowrap;">
                          <a href="Shippers.php?sort=id&dir=<?php echo $idDirToggle; ?>">#<?php echo $arrow('id'); ?></a>
                        </th>
                        <th>
                          <a href="Shippers.php?sort=name&dir=<?php echo $nameDirToggle; ?>">Shipper Name<?php echo $arrow('name'); ?></a>
                        </th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php
                    $z = 0;
                    if (is_array($donnee)) {
                      foreach($donnee as $dataemp){
                        $z++;
                        $id   = (int)$dataemp["Fld_Shipper_ID"];
                        $name = strtoupper($dataemp['Fld_Shipper_Text'] ?? '');
                        echo "<tr class=\"odd gradeX\" id=\"row_{$id}\">";
                        echo "  <td>{$id}</td>";
                        echo "  <td>{$name}</td>";
                        echo "  <td>";
                        echo "    <input type=\"hidden\" name=\"nbshipper\" id=\"nbshipper\" value=\"{$z}\">";
                        echo "    <a href=\"javascript:modif_shipper({$id})\"><i style=\"margin-left:10px;position: relative;top: 4px;font-size:23px;\" class=\"fa fa-pencil-square-o\"></i></a>";
                        echo "    &nbsp;&nbsp;&nbsp;&nbsp;";
                        echo "    <a href='javascript:sup_shipper({$id},{$z})' onClick=\"return(confirm('Are you sure ?'));\"><img src='images/bin-blue-full-icon.png' border='0' width='27' alt='delete'></a>";
                        echo "  </td>";
                        echo "</tr>";
                      }
                    }
                    ?>
                    </tbody>
                  </table>
                </form>
              </div>

              <div style="display:none" id="blocshipper"><div id="divshipper" align="center"></div></div>
            </div>
          </div>
        </div><!-- /.col-lg-4 -->
      </div>
      <?php } ?>
      <!-- /.row -->
    </div><!-- /#page-wrapper -->
  </div><!-- /#wrapper -->

  <!-- jQuery -->
  <script src="../vendor/jquery/jquery.min.js"></script>
  <!-- Bootstrap Core JavaScript -->
  <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
  <!-- Metis Menu Plugin JavaScript -->
  <script src="../vendor/metisMenu/metisMenu.min.js"></script>
  <!-- DataTables (chargé mais non obligatoire pour le tri serveur) -->
  <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
  <script src="../vendor/datatables-responsive/dataTables.responsive.js"></script>
  <!-- Custom Theme JavaScript -->
  <script src="../dist/js/sb-admin-2.js"></script>
  <script type="text/javascript" src="../js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
  <script type="text/javascript" src="../js/locales/bootstrap-datetimepicker.fr.js" charset="UTF-8"></script>

  <script type="text/javascript">
  /* On ne lance pas DataTables ici pour ne pas interférer avec la ligne d’ajout.
     Le tri est géré côté serveur avec ?sort= & ?dir= */

  // ===== Add shipper =====
  function add_shipper(){
    var tableau = document.getElementById("mytable");
    var nbLignes = tableau.rows.length;       // inclut thead -> la nouvelle ligne sera numérotée à la fin
    var ligne = tableau.insertRow(-1);
    ligne.id='row_'+(nbLignes+1);

    var cell0 = ligne.insertCell(0);
    cell0.innerHTML = (nbLignes+1);

    var cell1 = ligne.insertCell(1);
    cell1.innerHTML = "<input class=\"form-control\" name=\"Fld_Shipper_Text\" id=\"Fld_Shipper_Text\" placeholder=\"Shipper Name\">";

    var cell2 = ligne.insertCell(2);
    cell2.innerHTML = "<input type='submit' value='submit' class=\"btn btn-primary btn-block\">";
  }

  // ===== Supp shipper =====
  function sup_shipper(id,nbligne){
    if (id > 0) {
      $('#mytable tr[id="row_' + id + '"] td').css({'backgroundImage':'none','backgroundColor':'white'});
      $('#mytable tr[id="row_' + id + '"] td').animate({'backgroundColor':'#ff8888','color':'#941010'}, 1000);
      $.get('del_shipper.php', { idsup:id }, function(data){
        $('#mytable tr[id="row_' + id + '"] td').fadeTo("slow", 0, function(){ $(this).hide(); });
      });
    }
  }

  // ===== Modification shipper (chargement AJAX du formulaire) =====
  function modif_shipper(id){
    var bloc=document.getElementById('blocshipper');
    bloc.style.display='table-row';
    document.getElementById("divshipper").innerHTML='<div id="divshipper" align="center"><img src="../images/loader.gif" border="0"></div>';

    var xhr=null;
    if (window.XMLHttpRequest) { xhr = new XMLHttpRequest(); }
    else if (window.ActiveXObject) { xhr = new ActiveXObject("Microsoft.XMLHTTP"); }

    xhr.open("POST", "modif_shipper.php?id="+id, true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
      if (xhr.readyState==4){
        document.getElementById('divshipper').innerHTML='<div id="'+id+'" align="center">'+xhr.responseText+'</div>';
        document.location.href="#blocshipper";
      }
    };
    xhr.send("id="+id);
  }
  </script>
</body>
</html>
<?php
} else {
  echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=".$_SERVER['REQUEST_URI']."\">";
}
?>
