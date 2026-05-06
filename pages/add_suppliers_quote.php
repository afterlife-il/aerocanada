<?php
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

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
	
			<!--CSS rating ajoute par roy-->
			<link href="rating.css" rel="stylesheet">
			<!--Fin CSS rating ajoute par roy-->


<style>
/* s'assure que la liste des suggestions passe au-dessus des panels/modals */
.tt-dropdown-menu, .tt-menu, .typeahead.dropdown-menu { z-index: 3000; }
</style>


</head>

<body>


<!-- Modal global: Add supplier contact -->
<div class="modal fade" id="modalAddContactGlobal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header" style="background:#A7142A;">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color:#fff;font-weight:bold;">×</button>
      <h4 class="modal-title" style="color:#fff;font-weight:bold;">ADD SUPPLIER CONTACT</h4>
    </div>
    <div class="modal-body">
      <div class="container-fluid">
        <input type="hidden" id="ac_company_id">
        <div class="row">
          <div class="col-md-6">
            <label>Contact Name</label>
            <input type="text" class="form-control" id="ac_contact_name">
          </div>
          <div class="col-md-6">
            <label>Contact Email</label>
            <input type="email" class="form-control" id="ac_contact_email">
          </div>
        </div>
        <div class="row" style="margin-top:10px;">
          <div class="col-md-6">
            <label>Contact Phone</label>
            <input type="text" class="form-control" id="ac_contact_phone">
          </div>
        </div>
        <div class="row" id="ac_alert" style="display:none;margin-top:10px;">
          <div class="col-md-12"><div class="alert alert-danger"></div></div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      <button type="button" class="btn btn-primary" id="ac_save_btn">Save</button>
    </div>
  </div></div>
</div>


    <div id="wrapper">
  <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
    <?php include "top_menu.php"; ?>                       <!-- barre rouge -->
    <?php if(isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
  </nav>
<?php include "after_nav.php"; ?>

        <!-- Navigation -->
        
		<?php 
		//****tbl_RFQ_2******ID  Fld_RFQ_ID  Fld_Supplier_ID  Fld_Qty  Fld_Condition_ID  Fld_Payment_Term_ID  Fld_Delivery  Fld_Price  Fld_Price_Max  Fld_Price_Min  Fld_Currency_ID  Fld_Traceability_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Release_ID  Fld_Part_ID  Fld_Remark  Fld_IsBeen_Chosen  Fld_Current_Date  Fld_Qty_Received  Fld_Part_SN  Fld_Supplier_Contact_ID  Fld_Date_RecevdEnd_REP
		$ctxRfqId = $_GET['Fld_RFQ_ID'] ?? '';
		$ctxRfqLineId = (int)($_GET['id_tbl_rfq1'] ?? 0);
		$ctxPartId = (int)($_GET['Fld_Part_ID'] ?? 0);
		$ctxPn = $_GET['pn_rfq'] ?? '';
		$ctxDesc = $_GET['description_rfq'] ?? '';
		$ctxQty = $_GET['Fld_Qty'] ?? '';
		$ctxConditionId = (int)($_GET['Fld_Condition_ID'] ?? 0);
		?>
          <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">

            <div class="row">
                <div class="col-lg-10">
                   
                </div>
                <!-- /.col-lg-10 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-10">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            ADD SUPPLIERS QUOTE
                        </div>
						<form id="formajoutsq" role="form" method="post" action="valid_add_sq.php" enctype="multipart/form-data">
						<?php $today = date("Y-m-d");?>
						<input type="hidden" name="Fld_Current_Date" value="<?php echo $today;?>">
						<input type="hidden" name="aci_contact" value="<?php echo $_SESSION['id_utilisateur'];?>">
						<input type="hidden" name="Fld_Part_ID" id="Fld_Part_ID" value="<?php echo $ctxPartId;?>">
						<input type="hidden" name="id_tbl_rfq1" id="id_tbl_rfq1" value="<?php echo $ctxRfqLineId;?>">
<div class="panel-body">
    <!-- zone pour le contenu AJAX du pré-remplissage -->
    <div id="blocaddsq" style="display:none">
        <div id="divaddsq"></div>
    </div>


  <!-- Ligne 1 : RFQ + PN + DESCRIPTION -->
<div class="row">
  <div class="col-sm-3">
    <div class="form-group">
      <label>RFQ ID</label>
      <input class="form-control" name="Fld_RFQ_ID" value="<?php echo htmlspecialchars($ctxRfqId !== '' ? $ctxRfqId : date("Y-m-d-His"), ENT_QUOTES, 'UTF-8'); ?>">
    </div>
  </div>

  <div class="col-sm-3">
    <div class="form-group">
      <label>PN</label>
      <input class="form-control pnid" name="pn_rfq" id="pn_rfq" placeholder="Enter PN" value="<?php echo htmlspecialchars($ctxPn, ENT_QUOTES, 'UTF-8'); ?>">
    </div>
  </div>

  <div class="col-sm-6">
    <div class="form-group">
      <label>DESCRIPTION</label>
      <input class="form-control" name="description_rfq" id="description_rfq" placeholder="Auto / manual" value="<?php echo htmlspecialchars($ctxDesc, ENT_QUOTES, 'UTF-8'); ?>">
    </div>
  </div>
</div>

<!-- Ligne 2 : SUPPLIERS + CONTACT -->
<div class="row">
  <div class="col-sm-4">
    <div class="form-group">
      <label>SUPPLIERS</label>
      <input type="text" name="companyid" id="companyid" class="form-control companyid" placeholder="Please Enter company">
    </div>
  </div>

  <div class="col-sm-5" id="bloccontactname">
    <div class="form-group" id="divcontactname">
      <label>SUPPLIERS CONTACT NAME</label>
      <select class="form-control" name="Fld_Supplier_Contact_ID" id="Fld_Supplier_Contact_ID">
        <option value="">-- Select contact --</option>
      </select>
    </div>
  </div>
</div>

  <!-- Ligne 2 : Part / Qty / Condition / Release -->
  <div class="row">
    <div class="col-sm-4">
      <div class="form-group">
        <label>PART SN</label>
        <input class="form-control" name="Fld_Part_SN">
      </div>
    </div>

    <div class="col-sm-2">
      <div class="form-group">
        <label>QTY</label>
        <input class="form-control" name="Fld_Qty" value="<?php echo htmlspecialchars($ctxQty, ENT_QUOTES, 'UTF-8'); ?>">
      </div>
    </div>

    <div class="col-sm-3">
      <div class="form-group">
        <label>CONDITION</label>
        <select class="form-control" name="Fld_Condition_ID">
          <?php
          $sqldiv="SELECT DISTINCT(Fld_Condition_Text), Fld_Condition_ID FROM tbl_Condition ORDER BY Fld_Condition_Text";
          $reqemp = mysql2_query($sqldiv);
          while($datadiv = mysqli_fetch_array($reqemp)){
            echo "<option value='".$datadiv['Fld_Condition_ID']."'";
            if($ctxConditionId > 0 && $ctxConditionId == $datadiv['Fld_Condition_ID']) echo " selected";
            echo ">".$datadiv['Fld_Condition_Text']."</option>";
          }
          ?>
        </select>
      </div>
    </div>

    <div class="col-sm-3">
      <div class="form-group">
        <label>RELEASE</label>
        <select class="form-control" name="Fld_Release_ID">
          <?php
          $sqldiv="SELECT * FROM tbl_Release ORDER BY Fld_Release_Text";
          $reqemp = mysql2_query($sqldiv);
          while($datadiv = mysqli_fetch_array($reqemp)){
            echo "<option value='".$datadiv['Fld_Release_ID']."'>".$datadiv['Fld_Release_Text']."</option>";
          }
          ?>
        </select>
      </div>
    </div>
  </div>

  <!-- Ligne 3 : Tag Info / Tag Date / Traceability -->
  <div class="row">
    <div class="col-sm-4">
      <div class="form-group">
        <label>TAG INFO</label>
        <input type="text" name="companyidtaginfo" id="companyidtaginfo" class="form-control companyidtaginfo" placeholder="Please Enter company">
      </div>
    </div>

    <div class="col-sm-2">
      <div class="form-group">
        <label>TAG DATE (JJ/MM/AAAA)</label>
        <input class="form-control" name="Fld_Tag_Date" placeholder="JJ/MM/AAAA">
      </div>
    </div>

    <div class="col-sm-6">
      <div class="form-group">
        <label>TRACEABILITY</label>
        <input type="text" name="companyidtreacability" id="companyidtreacability" class="form-control companyidtreacability" placeholder="Please Enter company">
      </div>
    </div>
  </div>

  <!-- Ligne 4 : Lead Time / Delivery / Price + Currency / Payment Term -->
  <div class="row">
    <div class="col-sm-3">
      <div class="form-group">
        <label>LEAD TIME</label>
        <input class="form-control" name="lead_time" placeholder="e.g. 2 weeks">
      </div>
    </div>

    <div class="col-sm-2">
      <div class="form-group">
        <label>DELIVERY</label>
        <input class="form-control" name="Fld_Delivery" placeholder="days">
      </div>
    </div>

    <div class="col-sm-2">
      <div class="form-group">
        <label>PRICE</label>
        <input class="form-control" name="Fld_Price">
      </div>
    </div>

    <div class="col-sm-2">
      <div class="form-group">
        <label>$ / €</label>
        <select class="form-control" name="Fld_Price_Currency_ID">
          <?php
          $sqldiv="SELECT * FROM tbl_Currency";
          $reqemp = mysql2_query($sqldiv);
          while($datadiv = mysqli_fetch_array($reqemp)){
            echo "<option value='".$datadiv['Fld_Currency_ID']."'>".$datadiv['Fld_Currency_Text']."</option>";
          }
          ?>
        </select>
      </div>
    </div>

    <div class="col-sm-3">
      <div class="form-group">
        <label>PAYMENT TERM</label>
        <select class="form-control" name="Fld_Payment_Term_ID">
          <?php
          $sqlpt="SELECT * FROM tbl_Payment ORDER BY Fld_Payment_Text";
          $reqpt = mysql2_query($sqlpt);
          while($datapt = mysqli_fetch_array($reqpt)){
            echo "<option value='".$datapt['Fld_Payment_Term_ID']."'>".$datapt['Fld_Payment_Text']."</option>";
          }
          ?>
        </select>
      </div>
    </div>
  </div>

  <!-- Ligne 5 : Remark (pleine largeur) -->
  <div class="row">
    <div class="col-sm-12">
      <div class="form-group">
        <label>REMARK</label>
        <textarea class="form-control" rows="3" name="Fld_Remark" placeholder="Notes…"></textarea>
      </div>
    </div>
  </div>

  <!-- Ligne 6 : Qty received / Date received -->
  <div class="row">
    <div class="col-sm-4">
      <div class="form-group">
        <label>QTY RECEIVED (REPAIR ONLY - OPTIONAL)</label>
        <input class="form-control" name="Fld_Qty_Received">
      </div>
    </div>

    <div class="col-sm-4">
      <div class="form-group">
        <label>DATE RECEIVED END REP (REPAIR ONLY - OPTIONAL)</label>
        <input class="form-control" name="Fld_Date_RecevdEnd_REP" placeholder="JJ/MM/AAAA">
      </div>
    </div>
  </div>

    <!-- Actions -->
  <div class="row">
    <div class="col-sm-12 text-right">
      <button type="submit" class="btn btn-primary">Validate</button>
    </div>
  </div>

</div>
</form>

<div class="table-responsive" style="min-height:500px;height:500px;overflow:auto;">

  <table class="table table-striped table-bordered table-hover" id="mytable">
    <thead>
      <tr>
        <th width="200">RFQ ID</th>
        <th>PN</th>
        <th width="300">CUSTOMER</th>
        <th width="200">ACI 770</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $sql="SELECT Fld_RFQ_ID, MAX(Fld_Customer_ID) AS Fld_Customer_ID, MAX(Employee_ID) AS Employee_ID, MAX(ID) AS last_id
            FROM tbl_RFQ_1
            GROUP BY Fld_RFQ_ID
            ORDER BY last_id DESC
            LIMIT 0,100";
      $req = mysql2_query($sql);
      while ($data = mysqli_fetch_array($req)) {
        $sqlrn="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID=".$data['Fld_Customer_ID'];
        $reqrn = mysql2_query($sqlrn);
        $datarn = mysqli_fetch_array($reqrn);

        $sqlemp="SELECT Employee_Name FROM tbl_Employee where Employee_ID=".$data['Employee_ID'];
        $reqemp = mysql2_query($sqlemp);
        $dataemp = mysqli_fetch_array($reqemp);

        echo '<tr class="odd gradeX">';
        // icône +
        echo '<td><a href="javascript:pluspn(\''.$data['Fld_RFQ_ID'].'\')" title="company details" style="text-decoration:none;"><i class="fa fa-plus-circle" style="margin-left:10px;position:relative;top:4px;font-size:23px;"></i></a> ';
        // lien sur RFQ ID (clic = même action)
        echo '<a href="#" onclick="pluspn(\''.$data['Fld_RFQ_ID'].'\');return false;">'.$data['Fld_RFQ_ID'].'</a></td>';

        // PN (liste courte à plat)
        echo '<td>';
        $sqlpns="SELECT pn_rfq FROM tbl_RFQ_1 where Fld_RFQ_ID='".$data['Fld_RFQ_ID']."'";
        $reqpns = mysql2_query($sqlpns);
        while ($datapns = mysqli_fetch_array($reqpns)) {
        echo htmlspecialchars($datapns['pn_rfq'] ?? '', ENT_QUOTES, 'UTF-8').'&nbsp;&nbsp;';
        }
        echo '</td>';

 		echo '<td>'.htmlspecialchars($datarn['Fld_Company_Name'] ?? '', ENT_QUOTES, 'UTF-8').'</td>';
		echo '<td>'.htmlspecialchars($dataemp['Employee_Name'] ?? '', ENT_QUOTES, 'UTF-8').'</td>';

        echo '</tr>';

        // ligne “drawer” masquée
        echo '<tr style="display:none" id="blocpluspn'.$data['Fld_RFQ_ID'].'"><td colspan="4"><div id="divpluspn'.$data['Fld_RFQ_ID'].'" align="left"></div></td></tr>';
      }
      ?>
    </tbody>
  </table>
</div>

						
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-10 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

    <!-- jQuery -->
    <script src="../vendor/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="../vendor/metisMenu/metisMenu.min.js"></script>

    <!-- DataTables JavaScript -->
    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
    <script src="../vendor/datatables-responsive/dataTables.responsive.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>
	<script src="../js/typeahead.js"></script>

	<script type="text/javascript" src="../js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
	<script type="text/javascript" src="../js/locales/bootstrap-datetimepicker.fr.js" charset="UTF-8"></script>

 
<script>
(function(){
  /* Définir les fonctions GLOBALES en premier (quoi qu'il arrive) */
  window.addsqfromrfqid = function(Fld_RFQ_ID, id, pn_rfq, descOpt, partId, qty, conditionId){
    // Set RFQ line context
    var rfqInput = document.querySelector('input[name="Fld_RFQ_ID"]');
    if (rfqInput) rfqInput.value = Fld_RFQ_ID;

    var rfqLineInput = document.getElementById('id_tbl_rfq1');
    if (rfqLineInput) rfqLineInput.value = id || '';

    var partInput = document.getElementById('Fld_Part_ID');
    if (partInput) partInput.value = partId || '';

    var pnInput = document.getElementById('pn_rfq');
    if (pnInput) pnInput.value = pn_rfq || '';

    var descInput = document.getElementById('description_rfq');
    if (descInput) descInput.value = descOpt || '';

    var qtyInput = document.querySelector('input[name="Fld_Qty"]');
    if (qtyInput) qtyInput.value = qty || '';

    var conditionInput = document.querySelector('select[name="Fld_Condition_ID"]');
    if (conditionInput && conditionId) conditionInput.value = conditionId;

    // Clear stale supplier/pricing fields from previous selection
    $('#companyid').val('');
    $('#Fld_Supplier_Contact_ID').val('');
    $('input[name="Fld_Part_SN"]').val('');
    $('input[name="lead_time"]').val('');
    $('input[name="Fld_Delivery"]').val('');
    $('input[name="Fld_Price"]').val('');
    $('select[name="Fld_Price_Currency_ID"]').prop('selectedIndex', 0);
    $('select[name="Fld_Payment_Term_ID"]').prop('selectedIndex', 0);
    $('select[name="Fld_Release_ID"]').prop('selectedIndex', 0);
    $('#companyidtaginfo').val('');
    $('input[name="Fld_Tag_Date"]').val('');
    $('#companyidtreacability').val('');
    $('textarea[name="Fld_Remark"]').val('');
    $('input[name="Fld_Qty_Received"]').val('');
    $('input[name="Fld_Date_RecevdEnd_REP"]').val('');

    // Reset contact dropdown
    var contactSel = document.getElementById('Fld_Supplier_Contact_ID');
    if (contactSel) {
      contactSel.innerHTML = '<option value="">-- Select contact --</option>';
    }

    try { document.getElementById('wrapper').scrollIntoView({behavior:'smooth'}); } catch(e){}
    return false;
  };

// Click délégué sur les liens/boutons injectés par pluspn.php
$(document).on('click', '.js-add-sq', function (e) {
  e.preventDefault();
  // dataset fonctionne sur <a> et <button>
  var d = this.dataset;
  return window.addsqfromrfqid(d.rfq || '', d.id || '', d.pn || '', d.desc || '', d.partId || '', d.qty || '', d.conditionId || '');
});

  window.pluspn = function(id){
    // Fermer les autres drawers
    document.querySelectorAll('tr[id^="blocpluspn"]').forEach(function(tr){
      if (tr.id !== 'blocpluspn'+id) tr.style.display = 'none';
    });

    var row = document.getElementById('blocpluspn'+id);
    if (!row) return;
    // Toggle celui demandé
    row.style.display = (row.style.display === 'table-row') ? 'none' : 'table-row';

    // Charger le contenu s'il est vide
    var box = document.getElementById('divpluspn'+id);
    if (!box) return;
    if (!box.dataset.loaded){ // évite re-fetch à chaque click
      box.innerHTML = "<div style='padding:10px'><img src=\"../images/loader.gif\"> Loading…</div>";
      var xhr = new XMLHttpRequest();
      xhr.open("POST", "pluspn.php?id="+encodeURIComponent(id), true);
      xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
      xhr.onreadystatechange = function(){
        if (xhr.readyState === 4){
          box.innerHTML = xhr.responseText || '';
          box.dataset.loaded = '1';
        }
      };
      xhr.send("id="+encodeURIComponent(id));
    }
  };

  /* Initialisations protégées pour ne pas casser si un plugin manque */
  $(function(){
    // DataTables
    if ($.fn.DataTable){ $('#mytable').DataTable({ responsive:true }); }

// --- Auto-complétion (nom OU ID) ---
if (!$.fn.typeahead) {
  console.warn('typeahead.js introuvable: vérifie le chemin ../js/typeahead.js');
} else {
  function bindCompanyTA(sel){
    $(sel).typeahead({
      name: 'Fld_Company_Name',
      id:   'Fld_Company_ID',
      remote: 'list-company.php?query=%QUERY'
    });
  }

  // SUPPLIERS / TAG INFO / TRACEABILITY
  bindCompanyTA('#companyid');
  bindCompanyTA('#companyidtaginfo');
  bindCompanyTA('#companyidtreacability');

  // PN (comme avant)
  $('input.pnid').typeahead({
    name: 'Fld_Part_Nbr',
    id:   'Fld_Part_ID',
    remote: 'list-pn-select.php?query=%QUERY'
  });

  $('input.pnid').on('typeahead:selected typeahead:autocompleted typeahead:select', function(ev, suggestion) {
    var value = (suggestion && (suggestion.value || suggestion.Fld_Part_Nbr)) ? (suggestion.value || suggestion.Fld_Part_Nbr) : this.value;
    var pieces = (value || '').split(',');
    if (pieces.length > 1) {
      $('#Fld_Part_ID').val($.trim(pieces[1]));
    } else if (suggestion && (suggestion.id || suggestion.Fld_Part_ID || suggestion.label)) {
      $('#Fld_Part_ID').val(suggestion.id || suggestion.Fld_Part_ID || suggestion.label);
    }
  });
}

// Rafraîchir la liste des contacts quand le fournisseur change
$('#companyid').on('blur typeahead:selected typeahead:autocompleted', majtarea);

// (facultatif) petit log pour vérifier que les requêtes partent bien
$(document).ajaxSend(function(e, xhr, opts){
  if (opts.url.indexOf('list-company.php') !== -1 || opts.url.indexOf('list-pn-select.php') !== -1) {
    console.log('typeahead ->', opts.url);
  }
});


    // Modal "Add contact"
    function extractCompanyId(val){ var m=(val||'').match(/^\s*(\d+)/); return m?m[1]:''; }
    $(document).on('click', '#btnAddContact, #btnAddContact2', function(e){
      e.preventDefault();
      var cid = extractCompanyId($('#companyid').val());
      if(!cid){ alert('Please choose a supplier first.'); return; }
      $('#ac_company_id').val(cid);
      $('#ac_contact_name, #ac_contact_email, #ac_contact_phone').val('');
      $('#ac_alert').hide();
      $('#modalAddContactGlobal').modal('show');
    });
    $('#ac_save_btn').on('click', function(){
      var company_id = $('#ac_company_id').val();
      var name  = $('#ac_contact_name').val().trim();
      var email = $('#ac_contact_email').val().trim();
      var phone = $('#ac_contact_phone').val().trim();
      if(!name){ $('#ac_alert .alert').text('Contact name is required.'); $('#ac_alert').show(); return; }
      $.post('add_contact_from_popup.php', { company_id, contact_name:name, contact_email:email, contact_phone:phone })
        .done(function(){ $('#modalAddContactGlobal').modal('hide'); majtarea(); })
        .fail(function(){ $('#ac_alert .alert').text('Server error while saving contact.'); $('#ac_alert').show(); });
    });
  });

  /* Charge contacts fournisseur */
  window.majtarea = function(){
    var bloc = document.getElementById('bloccontactname');
    if (bloc) bloc.style.display = 'inline';
    var company = (document.getElementById('companyid')||{}).value || '';
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "contactnamefromcompany-sq.php?id="+encodeURIComponent(company), true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.onreadystatechange = function(){
      if (xhr.readyState === 4){
        var tgt = document.getElementById('divcontactname');
        if (tgt) tgt.innerHTML = xhr.responseText || '';
      }
    };
    xhr.send("company="+encodeURIComponent(company));
  };
})();
</script>



</body>

</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=".$_SERVER['REQUEST_URI']."\">";
?>
