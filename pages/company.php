<?php
//company.php
session_start();
include_once "conf.php";

// sécurité session
if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
  header("Location: login.php?url=" . urlencode($_SERVER['REQUEST_URI']));
  exit;
}

// paramètres GET nettoyés
$page       = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$companyID  = isset($_GET['Fld_Company_ID']) ? trim($_GET['Fld_Company_ID']) : '';
$details2   = isset($_GET['details2']) ? trim($_GET['details2']) : '';
$ratingGet  = isset($_GET['companyrating']) ? $_GET['companyrating'] : 'all';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AeroCanada-Industries.com</title>

  <!-- CSS vendors -->
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../vendor/metisMenu/metisMenu.min.css" rel="stylesheet">
  <link href="../vendor/datatables-plugins/dataTables.bootstrap.css" rel="stylesheet">
  <link href="../vendor/datatables-responsive/dataTables.responsive.css" rel="stylesheet">
  <link href="../dist/css/sb-admin-2.css" rel="stylesheet">
  <link href="../dist/css/aci-overrides.css?v=9" rel="stylesheet"> <!-- doit être APRÈS -->
  <link href="../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

  <!-- Styles spécifiques Company -->
  <style id="company-table-css">
    /* Desktop : on fige les largeurs, pas de "+" */
    #dataTables-example.is-desktop.dataTable { table-layout: fixed; }

    #dataTables-example.is-desktop th:nth-child(1),
    #dataTables-example.is-desktop td:nth-child(1){ width:7ch!important; text-align:center; white-space:nowrap; } /* ID */
    #dataTables-example.is-desktop th:nth-child(2),
    #dataTables-example.is-desktop td:nth-child(2){ width:200px!important; } /* Logo */
    #dataTables-example.is-desktop th:nth-child(3),
    #dataTables-example.is-desktop td:nth-child(3){ width:30ch!important; } /* Company */
    #dataTables-example.is-desktop th:nth-child(4),
    #dataTables-example.is-desktop td:nth-child(4){ width:8ch!important; text-align:center; white-space:nowrap; } /* CAGE */
    #dataTables-example.is-desktop th:nth-child(5),
    #dataTables-example.is-desktop td:nth-child(5){ width:18ch!important; text-align:center; white-space:nowrap; } /* Local time */
    #dataTables-example.is-desktop th:nth-child(6),
    #dataTables-example.is-desktop td:nth-child(6){ width:26ch!important; } /* Contact */
    #dataTables-example.is-desktop td:nth-child(7) a{ margin-right:8px; display:inline-block; } /* actions */

    #dataTables-example td img{ max-width:200px; height:auto; display:block; margin:0 auto; }
    #dataTables-example td .dt-clip{ overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:block; width:100%; }
    #dataTables-example td.dt-nowrap, #dataTables-example th.dt-nowrap{ white-space:nowrap; }
    #dataTables-example td.dt-center, #dataTables-example th.dt-center{ text-align:center; }

    /* Téléphone : DataTables responsive (child rows), on masque la colonne logo */
    #dataTables-example.is-phone.dataTable { table-layout: auto; }
    #dataTables-example.is-phone td:nth-child(7) a{ margin-right:6px; } /* actions plus serrées */
    #dataTables-example.is-phone td:nth-child(7) i{ font-size:16px; }

    @media (max-width:767.98px){
      #dataTables-example.is-phone th:nth-child(2),
      #dataTables-example.is-phone td:nth-child(2){ display:none!important; } /* logo */
    }
  </style>
</head>
<body>

<div id="wrapper">
  <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
    <?php include "top_menu.php"; ?> <!-- barre rouge -->
    <?php if (!empty($_SESSION['leftmenu']) && $_SESSION['leftmenu']==='open') include "left_menu.php"; ?>
  </nav>

  <?php include "after_nav.php"; ?> <!-- backdrop + sync mobile -->

  <!-- ********** CONTENU DE LA PAGE (UN SEUL wrapper !) ********** -->
  <div id="<?php echo (!empty($_SESSION['leftmenu']) && $_SESSION['leftmenu']==='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">
    <div class="row"><div class="col-lg-12"><h1 class="page-header">COMPANY</h1></div></div>

    <div class="row">
      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-heading">COMPANY</div>
          <div class="panel-body">

            <?php
            // Liste des commerciaux (utilise la même connexion $link)
            $aciEmployees = mysql2_query(
              "SELECT Employee_ID, Employee_Name
               FROM tbl_Employee
               WHERE Employee_Name <> ''
               ORDER BY Employee_Name ASC"
            );
            ?>
            <div class="row" style="margin-bottom:10px;">
  <div class="col-sm-6">
    <label style="margin-right:6px;">ACI 770 CONTACT :</label>
    <select id="filter_contact" class="form-control" style="display:inline-block; width:auto; min-width:260px;">
      <option value="">-- Tous les commerciaux --</option>
      <?php while($emp = mysqli_fetch_array($aciEmployees, MYSQLI_ASSOC)): ?>
        <option value="<?= (int)$emp['Employee_ID'] ?>">
          <?= htmlspecialchars($emp['Employee_Name'], ENT_QUOTES, 'UTF-8') ?>
        </option>
      <?php endwhile; ?>
    </select>
  </div>
</div>

            <table width="100%" class="table table-striped table-bordered table-hover" id="dataTables-example">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Logo COMPANY</th>
                  <th>COMPANY</th>
                  <th>CAGE CODE #</th>
                  <th>LOCAL TIME</th>
                  <th>ACI 770 CONTACT</th>
                  <th></th>
                </tr>
              </thead>
            </table>

            <div id="bloccompany" style="display:none;" width="100%">
              <div id="divcompany" align="center" width="100%"></div>
            </div>

          </div><!-- /.panel-body -->
        </div><!-- /.panel -->
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /#page-wrapper or #page-wrapper2 -->
</div><!-- /#wrapper -->

<?php
/* ========= blocs utilitaires générés (inchangés) ========= */
$varselaircraft = "<select class='form-control' name='Fld_AC_ID'><option></option>";
$sqlairc = "SELECT DISTINCT(Fld_AC_Model), Fld_AC_ID FROM tbl_Aircraft ORDER BY Fld_AC_Model";
$reqairc = mysql2_query($sqlairc);
while ($dataairc = mysqli_fetch_array($reqairc)) {
  $varselaircraft .= "<option value='".$dataairc['Fld_AC_ID']."'>".$dataairc['Fld_AC_Model']."</option>";
}
$varselaircraft .= "</select>";

$varshipperselect = "<select class='form-control' name='Fld_Shipper_ID'>";
$requete = mysql2_query("SELECT * FROM tbl_Shipper ORDER BY Fld_Shipper_Text");
while ($reponse = mysqli_fetch_array($requete)) {
  $varshipperselect .= "<option value='".$reponse["Fld_Shipper_ID"]."'>".$reponse["Fld_Shipper_Text"]."</option>";
}
$varshipperselect .= "</select>";

$varaddresstypeselect = "<select class='form-control' name='Fld_Company_Address_Type'>";
$reqtypec = mysql2_query("SELECT * FROM tbl_Division");
while ($datatypec = mysqli_fetch_array($reqtypec)) {
  $varaddresstypeselect .= "<option value='".$datatypec["Fld_Division_ID"]."'>".$datatypec["Fld_Division_Text"]."</option>";
}
$varaddresstypeselect .= "</select>";
?>

<!-- JS vendors (ordre strict) -->
<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="../vendor/jquery-ui/jquery-ui.min.css">
<style>
  /* Pour que la liste passe AU-DESSUS des panels/collapse */
  .ui-autocomplete { z-index: 9999 !important; }
</style>
<script src="../vendor/jquery-ui/jquery-ui.min.js"></script>
<script src="../vendor/metisMenu/metisMenu.min.js"></script>
<script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
<script src="../vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
<script src="../vendor/datatables-responsive/dataTables.responsive.js"></script>
<script src="../dist/js/sb-admin-2.js"></script>

<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<style>.ui-autocomplete{z-index:20000!important;}</style>



<!-- Page script -->
<script>
(function () {
  var companyrating = <?= json_encode($ratingGet) ?>;
  var pageParam     = <?= json_encode((string)$page) ?>;
  var companyIDPhp  = <?= json_encode($companyID) ?>;
  var details2Php   = <?= json_encode($details2) ?>;

  var table = null;
  var currentIsPhone = null;

  function buildDataTable(isPhone){
    if ($.fn.DataTable.isDataTable('#dataTables-example')) {
      $('#dataTables-example').DataTable().destroy();
    }

    var $tbl = $('#dataTables-example');
    $tbl.toggleClass('is-phone',   isPhone);
    $tbl.toggleClass('is-desktop', !isPhone);

    table = $tbl.DataTable({
      responsive: isPhone ? { details: { type: 'inline' } } : false,
      autoWidth: false,
      scrollX: isPhone ? false : true,
      processing: true,
      serverSide: true,
      order: [[2, 'asc']],
      ajax: {
        url: 'company22.php?companyrating=' + encodeURIComponent(companyrating),
        type: 'POST',
        data: function (d) {
          d.contact_id = $('#filter_contact').val() || '';
        },
        dataSrc: function (json) {
          return (json && json.data) ? json.data : [];
        },
        error: function (xhr) {
          console.error('AJAX ERROR', xhr.status, xhr.responseText);
          alert('Erreur AJAX (' + xhr.status + '). Consulte la console.');
        }
      },
      columnDefs: [
        { targets: 0, className: 'dt-center dt-nowrap',            responsivePriority: 3 }, // ID
        { targets: 1,                                            responsivePriority: 7 },   // Logo
        { targets: 2, className: 'dt-clip',                      responsivePriority: 1 },   // Company
        { targets: 3, className: 'dt-center',                    responsivePriority: 6 },   // CAGE
        { targets: 4, className: 'dt-center dt-nowrap',          responsivePriority: 2 },   // Local time
        { targets: 5,                                            responsivePriority: 5 },   // Contact
        { targets: 6, orderable: false, className: 'dt-nowrap',  responsivePriority: 4 }    // Actions
      ]
    });

    $('#filter_contact').off('change.aci').on('change.aci', function () {
      table.ajax.reload(null, true);
    });
  }

  function decideAndBuild(){
    var isPhone = window.innerWidth < 768;
    if (isPhone !== currentIsPhone) {
      currentIsPhone = isPhone;
      buildDataTable(isPhone);
    } else {
      if (table) table.columns.adjust().draw(false);
    }
  }

  decideAndBuild();
  var resizeTimer = null;
  $(window).on('resize.company', function(){
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(decideAndBuild, 150);
  });

  // ===== DÉTAILS SOCIÉTÉ =====
  window.detailcompany = function (id) {
    var bloc = document.getElementById('bloccompany');
    var div  = document.getElementById('divcompany');
    if (bloc) bloc.style.display = 'inline';
    if (div)  div.innerHTML = '<div align="center"><img src="../images/loader.gif" border="0" /></div>';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'detailcompany.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4) {
        if (xhr.status === 200) {
          if (div) div.innerHTML = '<div id="' + id + '" align="center">' + xhr.responseText + '</div>';
          var newUrl = 'company.php?companyrating=' + encodeURIComponent(companyrating)
                     + '&page=' + encodeURIComponent(pageParam)
                     + '&Fld_Company_ID=' + encodeURIComponent(id)
                     + '#bloccompany';
          window.history.replaceState({}, '', newUrl);
        } else {
          console.error('Erreur AJAX : ' + xhr.status);
        }
      }
    };
    xhr.send('id=' + encodeURIComponent(id) + '&page=' + encodeURIComponent(pageParam));
  };

  window.detailcompany2 = function (id) {
    var bloc = document.getElementById('bloccompany');
    var div  = document.getElementById('divcompany');
    if (bloc) bloc.style.display = 'inline';
    if (div)  div.innerHTML = '<div align="center"><img src="../images/loader.gif" border="0" /></div>';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'detailcompany2.php?id=' + encodeURIComponent(id), true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4) {
        if (xhr.status === 200) {
          if (div) div.innerHTML = '<div id="' + id + '" align="center">' + xhr.responseText + '</div>';
          location.hash = '#bloccompany';
        } else {
          console.error('Erreur AJAX : ' + xhr.status);
        }
      }
    };
    xhr.send('id=' + encodeURIComponent(id));
  };

  window.fermeturedetailcompany = function () {
    var bloc = document.getElementById('bloccompany');
    if (bloc) bloc.style.display = 'none';
  };

  // Ouverture automatique via URL
  if (companyIDPhp) {
    if (details2Php) { detailcompany2(companyIDPhp); }
    else             { detailcompany(companyIDPhp); }
  }
    /* =======================
     FONCTIONS REMISES EN PLACE
     ======================= */

  // --- Statut contact (archiver / désarchiver) + maj remark ---
  window.statutcontact = function(id){
    if (id > 0) {
      $('#mytable tr[id="row_' + id + '"] td').css({
        backgroundImage: 'none',
        backgroundColor: '#be0831',
        color: '#ffffff'
      });
      $.get('archiver_contact.php', { idsup:id }, function(){});
      document.getElementById("case"+ id).innerHTML=" <a href=javascript:desarchivercontact("+id+")>annuler</a>";
    }
  };

  window.desarchivercontact = function(id){
    if (id > 0) {
      $('#mytable tr[id="row_' + id + '"] td').css({
        backgroundImage: 'none',
        backgroundColor: '#ffffff',
        color: '#333333'
      });
      $.get('desarchivercontact.php', { idsup:id }, function(){});
      document.getElementById("case"+ id).innerHTML=" <a href=javascript:statutcontact("+id+")><i style=\"margin-left:10px;position: relative;top: 4px;font-size:23px;\" class=\"fa  fa-archive\"></i></a>";
    }
  };

  window.majtarea = function(id){
    var selection = document.getElementById("recupmessageremark"+id).value;
    $.get('majremarkcontact.php', { id_company_contact:id, Fld_Contact_Remark:selection }, function(){});
  };

  // --- AJOUT Address Company (ligne inline) ---
  window.addaddresscompany = function(){
    var tableau = document.getElementById("tableaddressecompany");
    var nbLignes = tableau.rows.length;
    var ligne = tableau.insertRow(-1);
    ligne.id='row_'+(nbLignes+1);

    var cell;

    cell = ligne.insertCell(0);
    cell.innerHTML = "<?php echo $varaddresstypeselect;?>";

    cell = ligne.insertCell(1);
    cell.innerHTML = "<input class=\"form-control\" name=\"title_address\" id=\"title_address\" placeholder=\"Address Title\">";

    cell = ligne.insertCell(2);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Company_Street\" id=\"Fld_Company_Street\" placeholder=\"Street\">";

    cell = ligne.insertCell(3);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Company_City\" id=\"Fld_Company_City\" placeholder=\"City\">";

    cell = ligne.insertCell(4);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Company_ZipCode\" id=\"Fld_Company_ZipCode\" placeholder=\"Zip Code\">";

    cell = ligne.insertCell(5);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Company_State\" id=\"Fld_Company_State\" placeholder=\"State\">";

    cell = ligne.insertCell(6);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Company_Country\" id=\"Fld_Company_Country\" placeholder=\"Country\">";

    cell = ligne.insertCell(7);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Company_Phone\" id=\"Fld_Company_Phone\" placeholder=\"PHONE\">";

    cell = ligne.insertCell(8);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Company_Email\" id=\"Fld_Company_Email\" placeholder=\"E-MAIL\">";

    cell = ligne.insertCell(9);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Remark\" id=\"Fld_Remark\" placeholder=\"Remark\">";

    cell = ligne.insertCell(10);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_VAT_Nbr\" id=\"Fld_VAT_Nbr\" placeholder=\"VAT Nbr\"><input type='hidden' name='act' value='addaddresscompany'>";

    cell = ligne.insertCell(11);
    cell.innerHTML = "<input type='submit' value='submit' class=\"form-control\">";
  };

  // --- AJOUT Company Contact (une ligne en tête du tableau) ---
  window.addcontactcompany = function(){
    var tableau = document.getElementById("tableaddcontactcompany");
    var nbLignes = tableau.rows.length;
    var ligne = tableau.insertRow(1); // sous l’en-tête
    ligne.id='row_'+(nbLignes+1);

    var cell;

    cell = ligne.insertCell(0);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Contact_Name\" id=\"Fld_Contact_Name\" placeholder=\"\">";

    cell = ligne.insertCell(1);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Contact_Phone\" id=\"Fld_Contact_Phone\" placeholder=\"\">";

    cell = ligne.insertCell(2);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Contact_Phone2\" id=\"Fld_Contact_Phone2\" placeholder=\"\">";

    cell = ligne.insertCell(3);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Contact_Fax\" id=\"Fld_Contact_Fax\" placeholder=\"\">";

    cell = ligne.insertCell(4);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Company_Mobile\" id=\"Fld_Company_Mobile\" placeholder=\"\">";

    cell = ligne.insertCell(5);
    cell.innerHTML = "<select class=\"form-control\" name=\"Fld_Contact_Division_ID\"><option value=\"1\" selected=\"\">Sales</option><option value=\"2\">Account</option><option value=\"3\">Logistics / Shipping1</option><option value=\"5\">Technical</option><option value=\"6\">Purchasing</option><option value=\"7\">AOG</option><option value=\"8\">Customer Service Administrator</option><option value=\"9\">Management</option><option value=\"10\">Quality</option><option value=\"11\">Sales Technical</option><option value=\"12\">Shipping2</option><option value=\"13\">***No Longer Valid***</option><option value=\"14\">DROP SHIPMENT</option></select>";

    cell = ligne.insertCell(6);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Contact_Email\" id=\"Fld_Contact_Email\" placeholder=\"\">";

    cell = ligne.insertCell(7);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Contact_Title\" id=\"Fld_Contact_Title\" placeholder=\"\">";

    cell = ligne.insertCell(8);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Contact_Remark\" id=\"Fld_Contact_Remark\" placeholder=\"\"><input type='hidden' name='nbcontact' value='"+nbLignes+"'><input type='hidden' name='act' value='addcontact'>";

    cell = ligne.insertCell(9);
    cell.innerHTML = "<input type='submit' value='submit' class=\"form-control\">";
  };

  // --- AJOUT Aircraft (Fleet) ---
  window.addaircraft = function(){
    var tableau = document.getElementById("dataTablefleet");
    var nbLignes = tableau.rows.length;
    var ligne = tableau.insertRow(-1);
    ligne.id='row_'+(nbLignes+1);

    var cell;

    cell = ligne.insertCell(0);
    cell.innerHTML = "<select class=\"form-control\" name=\"Fld_Region\"><option>Choose Region</option><option value=\"1\" >Africa</option><option value=\"2\">Asia & Pacific</option><option value=\"3\">Canada</option><option value=\"4\">Europe</option><option value=\"5\">Latin America</option><option value=\"6\">Middle East</option><option value=\"7\">USA</option></select>";

    cell = ligne.insertCell(1);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Engine\" id=\"Fld_Engine\" placeholder=\"\">";

    cell = ligne.insertCell(2);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Unit\" id=\"Fld_Unit\" placeholder=\"\">";

    cell = ligne.insertCell(3);
    cell.innerHTML ="<?php echo $varselaircraft;?>";

    cell = ligne.insertCell(4);
    cell.innerHTML = "<input class=\"form-control\" name=\"msn\" id=\"msn\" placeholder=\"\">";

    cell = ligne.insertCell(5);
    cell.innerHTML = "<input class=\"form-control\" name=\"immat\" id=\"immat\" placeholder=\"\">";

    cell = ligne.insertCell(6);
    cell.innerHTML = "<input type='hidden' name='nbaircraft' value='"+nbLignes+"'><input type='hidden' name='act' value='addaircraft'><input type='submit' value='submit' class=\"form-control\">";
  };

  // --- AJOUT Bank Account ---
  window.addaba = function(){
    var tableau = document.getElementById("dataTableba");
    var nbLignesba = tableau.rows.length;
    var ligne = tableau.insertRow(-1);
    ligne.id='row_'+(nbLignesba+1);

    var cell;

    cell = ligne.insertCell(0);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Bank_Name\" id=\"Fld_Bank_Name\" placeholder=\"BANK NAME\">";

    cell = ligne.insertCell(1);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Bank_Address\" id=\"Fld_Bank_Address\" placeholder=\"BANK ADDRESS\">";

    cell = ligne.insertCell(2);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Bank_Acct_Nbr\" id=\"Fld_Bank_Acct_Nbr\" placeholder=\"ACCOUNT #\">";

    cell = ligne.insertCell(3);
    cell.innerHTML = "<input class=\"form-control\" name=\"branch_nbr\" id=\"branch_nbr\" placeholder=\"BRANCH #\">";

    cell = ligne.insertCell(4);
    cell.innerHTML = "<input class=\"form-control\" name=\"bank_nbr\" id=\"bank_nbr\" placeholder=\"BANK #\">";

    cell = ligne.insertCell(5);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Swift_Nbr\" id=\"Fld_Swift_Nbr\" placeholder=\"SWIFT #\">";

    cell = ligne.insertCell(6);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_ABA_Routing_Nbr\" id=\"Fld_ABA_Routing_Nbr\" placeholder=\"ABA ROUTING #\">";

    cell = ligne.insertCell(7);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Reference\" id=\"Fld_Reference\" placeholder=\"REFERENCE\">";

    cell = ligne.insertCell(8);
    cell.innerHTML = "<input class=\"form-control\" name=\"comments\" id=\"comments\" placeholder=\"COMMENTS\">";

    cell = ligne.insertCell(9);
    cell.innerHTML = "<input type='hidden' name='nbbankaccount' value='"+nbLignesba+"'><input type='hidden' name='act' value='addbankaccount'><input type='submit' value='submit' class=\"form-control\">";
  };

  // --- AJOUT Forwarder ---
  window.addforwarder = function(){
    var tableau = document.getElementById("dataTableforw");
    var nbLignesfo = tableau.rows.length;
    var ligne = tableau.insertRow(-1);
    ligne.id='row_'+(nbLignesfo+1);

    var cell;

    cell = ligne.insertCell(0);
    cell.innerHTML = "<?php echo $varshipperselect;?>";

    cell = ligne.insertCell(1);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Shipper_Contact_Name_Forw\" id=\"Fld_Shipper_Contact_Name_Forw\" placeholder=\"CONTACT NAME\">";

    cell = ligne.insertCell(2);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Shipper_Contact_Phone_Forw\" id=\"Fld_Shipper_Contact_Phone_Forw\" placeholder=\"CONTACT PHONE\">";

    cell = ligne.insertCell(3);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Account_Nbr\" id=\"Fld_Account_Nbr\" placeholder=\"ACCOUNT #\">";

    cell = ligne.insertCell(4);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Remark\" id=\"Fld_Remark\" placeholder=\"REMARK\">";

    cell = ligne.insertCell(5);
    cell.innerHTML = "<input type='hidden' name='nbforwarder' value='"+nbLignesfo+"'><input type='hidden' name='act' value='addforwarder'><input type='submit' value='submit' class=\"form-control\">";
  };

  // --- Modif address (ouvre le sous-formulaire via AJAX) ---
  window.modif_address_company = function(id){
    var bloc=document.getElementById('blocdetailscompany');
    if (bloc) bloc.style.display='inline';

    var div = document.getElementById("divdetailscompany");
    if (div) div.innerHTML='<div id="divdetailscompany" align="center"><img src="../images/loader.gif" border="0"></div>';

    var xhr=new XMLHttpRequest();
    xhr.open("POST", "modif_address_company.php?id="+encodeURIComponent(id), true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.onreadystatechange=function(){ window.up_donnee_address_company(xhr,id); };
    xhr.send("id="+encodeURIComponent(id));
  };

  window.up_donnee_address_company = function(xhr,id){
    if (xhr.readyState===4){
      var div = document.getElementById('divdetailscompany');
      if (div){
        div.innerHTML='<div id="'+id+'" align="center">'+xhr.responseText+'</div>';
        location.hash="#blocdetailscompany";
      }
    }
  };

  // --- Modif contact (ouvre le sous-formulaire via AJAX) ---
  window.modif_contact_company = function(id){
    var bloc=document.getElementById('bloccontactcompany');
    if (bloc) bloc.style.display='inline';

    var div = document.getElementById("divcontactcompany");
    if (div) div.innerHTML='<div id="divcontactcompany" align="center"><img src="../images/loader.gif" border="0"></div>';

    var xhr=new XMLHttpRequest();
    xhr.open("POST", "modif_contact_company.php?id="+encodeURIComponent(id)+"&Fld_Company_ID="+encodeURIComponent(companyIDPhp || '')+"&page="+encodeURIComponent(pageParam || '1'), true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.onreadystatechange=function(){ window.up_donnee_contact_company(xhr,id); };
    xhr.send("id="+encodeURIComponent(id));
  };

  window.up_donnee_contact_company = function(xhr,id){
    if (xhr.readyState===4){
      var div = document.getElementById('divcontactcompany');
      if (div){
        div.innerHTML='<div id="'+id+'" align="center">'+xhr.responseText+'</div>';
        location.hash="#bloccontactcompany";
      }
    }
  };

  // --- Click-to-call ---
  window.callclient = function(FldContactPhone,telsource){
    $.ajax({
      type: "GET",
      url: "clear.php",
      data: { FldContactPhone: FldContactPhone, telsource: telsource },
      success : function(){ location.reload(); }
    });
  };
// === COMPETITOR =====

// Toggle du mini-formulaire
$(document).on('click', '#add_competitor_button', function (e) {
  e.preventDefault();
  $('#add_competitor_form').toggle();
  if ($('#add_competitor_form').is(':visible')) {
    setTimeout(function(){ $('#new_competitor').trigger('focus'); }, 100);
  }
});

// Installe l'autocomplete sur #new_competitor (et #competitor_name si présent)
$(document).on('focus', '#new_competitor, #competitor_name', function () {
  var $input = $(this);
  if ($input.data('ui-autocomplete')) return; // déjà instancié

  $input.autocomplete({
    appendTo: 'body',          // évite les soucis de z-index/collapse
    minLength: 2,
    delay: 150,
    source: function (request, response) {
      $.ajax({
        url: 'search_competitor.php',   // chemin relatif à /pages/company.php
        type: 'GET',
        dataType: 'json',
        data: {
          term:   request.term,                         // conv. jQuery UI
          query:  request.term,                         // compat si le PHP attend "query"
          exclude: $('#companyid_parent').val() || 0    // exclure la société courante
        }
      })
      .done(function (list) {
        response($.map(list || [], function (it) {
          return {
            label: it.Fld_Company_Name + '  (ID ' + it.Fld_Company_ID + ')',
            value: it.Fld_Company_Name,
            id:    it.Fld_Company_ID
          };
        }));
      })
      .fail(function () { response([]); });
    },
    select: function (e, ui) {
      // alimente le hidden (peu importe lequel tu as dans le DOM)
      $('#new_competitor_id, #competitor_id').val(ui.item.id);
      // affiche le libellé dans l’input
      $input.val(ui.item.value);
      // focus sur le bouton (qualité UX)
      setTimeout(function(){ $('#submit_new_competitor, #btn_add_competitor').trigger('focus'); }, 0);
      return false;
    },
    open: function(){
      $('.ui-autocomplete').css({'max-height':'260px','overflow-y':'auto'});
    }
  });
});


// Ajout via AJAX
$(document).on('click', '#submit_new_competitor', function () {
  var parentId = $('#companyid_parent').val();
  var compId   = parseInt($('#new_competitor_id').val(), 10);
  var compName = ($('#new_competitor').val() || '').trim();

  if (!parentId) { alert('Company parent ID manquant.'); return; }

  function go(finalId){
    if (!finalId || isNaN(finalId)) { alert("Sélectionne une compagnie valide."); return; }
    $.ajax({
      url: 'add_competitor.php',
      type: 'POST',
      dataType: 'json',
      data: { companyid_parent: parentId, companyid1: finalId }
    }).done(function (json) {
      // Recharge proprement la fiche + rouvre COMPETITOR
      detailcompany2(parentId);
      setTimeout(function(){ $('#collapseEight').collapse('show'); }, 400);
    }).fail(function (xhr) {
      alert('Erreur ' + xhr.status + ' lors de l’ajout.');
      console.error(xhr.responseText);
    });
  }

  if (compId) {
    go(compId);
  } else if (compName.length >= 2) {
    // Si l’utilisateur n’a pas “sélectionné”, on tente de trouver le 1er match
    $.getJSON('search_competitor.php', { query: compName, exclude: parentId }, function (list) {
      if (list && list.length) go(list[0].Fld_Company_ID);
      else alert('Aucun résultat pour "' + compName + '".');
    });
  } else {
    alert("Tape au moins 2 caractères et choisis un résultat.");
  }
});

})();
</script>

<script type="text/javascript">
function delete_contact(idContact) {
    if (!confirm('Are you sure you want to DELETE this contact permanently ?')) {
        return false;
    }

    // On garde la même société + la page actuelle
    var companyId = <?php echo (int)$id_company; ?>;
    var page      = <?php echo isset($_GET['page']) ? (int)$_GET['page'] : 1; ?>;

    var url = 'delete_contact_company.php'
            + '?id=' + idContact
            + '&Fld_Company_ID=' + companyId
            + '&page=' + page;

    window.location.href = url;
}
</script>

</body>
</html>
