<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
if($_SESSION['conectroy']=="parfait"){

  $sql="SELECT * from tbl_RFQ_2 where ID='".addslashes($_GET['ID'])."'";
  $req = mysql2_query($sql);
  $data = mysqli_fetch_array($req);

  // Resolve supplier name
  $supplierName = '';
  if (!empty($data['Fld_Supplier_ID'])) {
    $sqlncs="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID=".(int)$data['Fld_Supplier_ID'];
    $reqncs = mysql2_query($sqlncs);
    $datancs = mysqli_fetch_array($reqncs);
    $supplierName = $datancs['Fld_Company_Name'] ?? '';
  }

  // Resolve supplier contact
  $contactName = '';
  if (!empty($data['Fld_Supplier_Contact_ID'])) {
    $sqlscid="SELECT Fld_Contact_Name FROM tb_company_contact WHERE id_company_contact=".(int)$data['Fld_Supplier_Contact_ID'];
    $reqscid = mysql2_query($sqlscid);
    $datascid = mysqli_fetch_array($reqscid);
    $contactName = $datascid['Fld_Contact_Name'] ?? '';
  }

  // Resolve PN
  $pnNbr = '';
  $pnDesc = '';
  if (!empty($data['Fld_Part_ID'])) {
    $sqlpn="SELECT Fld_Part_Nbr, Fld_Part_Desc FROM tbl_Parts WHERE Fld_Part_ID=".(int)$data['Fld_Part_ID'];
    $reqpn = mysql2_query($sqlpn);
    $datapn = mysqli_fetch_array($reqpn);
    $pnNbr = $datapn['Fld_Part_Nbr'] ?? '';
    $pnDesc = $datapn['Fld_Part_Desc'] ?? '';
  }

  // Resolve tag info company
  $tagInfoName = '';
  if (!empty($data['Fld_Tag_Info_ID'])) {
    $sqltiid="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID=".(int)$data['Fld_Tag_Info_ID'];
    $reqtiid = mysql2_query($sqltiid);
    $datatiid = mysqli_fetch_array($reqtiid);
    $tagInfoName = $datatiid['Fld_Company_Name'] ?? '';
  }

  // Resolve traceability company
  $traceName = '';
  if (!empty($data['Fld_Traceability_ID'])) {
    $sqltrac="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID=".(int)$data['Fld_Traceability_ID'];
    $reqtrac = mysql2_query($sqltrac);
    $datatrac = mysqli_fetch_array($reqtrac);
    $traceName = $datatrac['Fld_Company_Name'] ?? '';
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aerocanada - Edit Supplier Quote</title>
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/metisMenu/metisMenu.min.css" rel="stylesheet">
    <link href="../dist/css/sb-admin-2.css" rel="stylesheet">
    <link href="../dist/css/aci-overrides.css" rel="stylesheet">
    <link href="../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <style>
      .tt-dropdown-menu, .tt-menu, .typeahead.dropdown-menu {
        z-index: 3000;
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-shadow: 0 4px 10px rgba(0,0,0,.15);
        width: 100%;
      }
      .tt-suggestion {
        color: #333;
        padding: 6px 10px;
        cursor: pointer;
      }
      .tt-suggestion.tt-is-under-cursor,
      .tt-suggestion.tt-cursor,
      .tt-suggestion:hover {
        background: #337ab7;
        color: #fff;
      }
    </style>
</head>
<body>
    <div id="wrapper">
  <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
    <?php include "top_menu.php"; ?>
    <?php if(isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
  </nav>
  <?php include "after_nav.php"; ?>

  <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">

    <div class="row">
      <div class="col-lg-12">
        <h1 class="page-header">EDIT SUPPLIER QUOTE</h1>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-10">
        <div class="panel panel-default">
          <div class="panel-heading">MODIF SUPPLIERS QUOTE</div>
          <form id="formajoutsq" role="form" method="post" action="valid_modif_sq.php">
            <input type="hidden" name="id_table_rfq2" value="<?php echo (int)$data['ID'];?>">
            <input type="hidden" name="part_id" value="<?php echo htmlspecialchars($_GET['part_id'] ?? '');?>">
            <div class="panel-body">

<!-- Row 1: RFQ ID + Supplier + Contact -->
<div class="row">
  <div class="col-sm-3">
    <div class="form-group">
      <label>RFQ ID</label>
      <input class="form-control" name="Fld_RFQ_ID" value="<?php echo htmlspecialchars($data['Fld_RFQ_ID']);?>">
    </div>
  </div>
  <div class="col-sm-4">
    <div class="form-group">
      <label>SUPPLIERS</label>
      <input type="text" name="companyid" id="companyid" class="form-control companyid"
             value="<?php echo htmlspecialchars($data['Fld_Supplier_ID'].','.$supplierName);?>">
    </div>
  </div>
  <div class="col-sm-5" id="bloccontactname">
    <div class="form-group" id="divcontactname">
      <label>SUPPLIER CONTACT</label>
      <select class="form-control" name="Fld_Supplier_Contact_ID" id="Fld_Supplier_Contact_ID">
        <?php
        if (!empty($data['Fld_Supplier_ID'])) {
          $sqlcc = "SELECT id_company_contact, Fld_Contact_Name FROM tb_company_contact
                    WHERE Fld_Company_ID = '".(int)$data['Fld_Supplier_ID']."' AND Fld_Contact_Name <> '' AND LOWER(status)='available'
                    ORDER BY Fld_Contact_Name";
          $reqcc = mysql2_query($sqlcc);
          while ($datacc = mysqli_fetch_array($reqcc)) {
            $sel = ($datacc['id_company_contact'] == $data['Fld_Supplier_Contact_ID']) ? ' selected' : '';
            echo "<option value='".$datacc['id_company_contact']."'".$sel.">".htmlspecialchars($datacc['Fld_Contact_Name'])."</option>";
          }
        }
        ?>
      </select>
    </div>
  </div>
</div>

<!-- Row 2: PN + Description + SN + Qty -->
<div class="row">
  <div class="col-sm-3">
    <div class="form-group">
      <label>PN</label>
      <input type="text" name="pnid" id="pnid" class="form-control pnid"
             value="<?php echo htmlspecialchars($pnNbr.','.$data['Fld_Part_ID']);?>">
    </div>
  </div>
  <div class="col-sm-3">
    <div class="form-group">
      <label>DESCRIPTION</label>
      <input class="form-control" name="description" value="<?php echo htmlspecialchars($pnDesc);?>">
    </div>
  </div>
  <div class="col-sm-3">
    <div class="form-group">
      <label>PART SN</label>
      <input class="form-control" name="Fld_Part_SN" value="<?php echo htmlspecialchars($data['Fld_Part_SN'] ?? '');?>">
    </div>
  </div>
  <div class="col-sm-1">
    <div class="form-group">
      <label>QTY</label>
      <input class="form-control" name="Fld_Qty" value="<?php echo htmlspecialchars($data['Fld_Qty'] ?? '');?>">
    </div>
  </div>
  <div class="col-sm-2">
    <div class="form-group">
      <label>CONDITION</label>
      <select class="form-control" name="Fld_Condition_ID">
        <?php
        $sqldiv="SELECT DISTINCT(Fld_Condition_Text), Fld_Condition_ID FROM tbl_Condition ORDER BY Fld_Condition_Text";
        $reqemp = mysql2_query($sqldiv);
        while($datadiv = mysqli_fetch_array($reqemp)){
          $sel = ($datadiv['Fld_Condition_ID'] == $data['Fld_Condition_ID']) ? ' selected' : '';
          echo "<option value='".$datadiv['Fld_Condition_ID']."'".$sel.">".$datadiv['Fld_Condition_Text']."</option>";
        }
        ?>
      </select>
    </div>
  </div>
</div>

<!-- Row 3: Release + Tag Info + Tag Date + Traceability -->
<div class="row">
  <div class="col-sm-2">
    <div class="form-group">
      <label>RELEASE</label>
      <select class="form-control" name="Fld_Release_ID">
        <?php
        $sqldiv="SELECT * FROM tbl_Release ORDER BY Fld_Release_Text";
        $reqemp = mysql2_query($sqldiv);
        while($datadiv = mysqli_fetch_array($reqemp)){
          $sel = ($datadiv['Fld_Release_ID'] == $data['Fld_Release_ID']) ? ' selected' : '';
          echo "<option value='".$datadiv['Fld_Release_ID']."'".$sel.">".$datadiv['Fld_Release_Text']."</option>";
        }
        ?>
      </select>
    </div>
  </div>
  <div class="col-sm-3">
    <div class="form-group">
      <label>TAG INFO</label>
      <input type="text" name="companyidtaginfo" id="companyidtaginfo" class="form-control companyidtaginfo"
             value="<?php echo htmlspecialchars($data['Fld_Tag_Info_ID'].','.$tagInfoName);?>">
    </div>
  </div>
  <div class="col-sm-2">
    <div class="form-group">
      <label>TAG DATE (JJ/MM/AAAA)</label>
      <input class="form-control" name="Fld_Tag_Date" value="<?php echo htmlspecialchars($data['Fld_Tag_Date'] ?? '');?>">
    </div>
  </div>
  <div class="col-sm-3">
    <div class="form-group">
      <label>TRACEABILITY</label>
      <input type="text" name="companyidtreacability" id="companyidtreacability" class="form-control companyidtreacability"
             value="<?php echo htmlspecialchars($data['Fld_Traceability_ID'].','.$traceName);?>">
    </div>
  </div>
</div>

<!-- Row 4: Lead Time + Delivery + Price + Currency + Payment Term -->
<div class="row">
  <div class="col-sm-2">
    <div class="form-group">
      <label>LEAD TIME</label>
      <input class="form-control" name="lead_time" value="<?php echo htmlspecialchars($data['lead_time'] ?? '');?>">
    </div>
  </div>
  <div class="col-sm-2">
    <div class="form-group">
      <label>DELIVERY</label>
      <input class="form-control" name="Fld_Delivery" value="<?php echo htmlspecialchars($data['Fld_Delivery'] ?? '');?>">
    </div>
  </div>
  <div class="col-sm-2">
    <div class="form-group">
      <label>PRICE</label>
      <input class="form-control" name="Fld_Price" value="<?php echo htmlspecialchars($data['Fld_Price'] ?? '');?>">
    </div>
  </div>
  <div class="col-sm-2">
    <div class="form-group">
      <label>$/€</label>
      <select class="form-control" name="Fld_Currency_ID">
        <?php
        $sqldiv="SELECT * FROM tbl_Currency";
        $reqemp = mysql2_query($sqldiv);
        while($datadiv = mysqli_fetch_array($reqemp)){
          $sel = ($datadiv['Fld_Currency_ID'] == $data['Fld_Currency_ID']) ? ' selected' : '';
          echo "<option value='".$datadiv['Fld_Currency_ID']."'".$sel.">".$datadiv['Fld_Currency_Text']."</option>";
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
          $sel = ($datapt['Fld_Payment_Term_ID'] == $data['Fld_Payment_Term_ID']) ? ' selected' : '';
          echo "<option value='".$datapt['Fld_Payment_Term_ID']."'".$sel.">".$datapt['Fld_Payment_Text']."</option>";
        }
        ?>
      </select>
    </div>
  </div>
</div>

<!-- Row 5: Remark -->
<div class="row">
  <div class="col-sm-12">
    <div class="form-group">
      <label>REMARK</label>
      <textarea class="form-control" rows="3" name="Fld_Remark"><?php echo htmlspecialchars($data['Fld_Remark'] ?? '');?></textarea>
    </div>
  </div>
</div>

<!-- Row 6: Qty Received + Date Received -->
<div class="row">
  <div class="col-sm-3">
    <div class="form-group">
      <label>QTY RECEIVED</label>
      <input class="form-control" name="Fld_Qty_Received" value="<?php echo htmlspecialchars($data['Fld_Qty_Received'] ?? '');?>">
    </div>
  </div>
  <div class="col-sm-3">
    <div class="form-group">
      <label>DATE RECEIVED END REP</label>
      <input class="form-control" name="Fld_Date_RecevdEnd_REP" value="<?php echo htmlspecialchars($data['Fld_Date_RecevdEnd_REP'] ?? '');?>">
    </div>
  </div>
</div>

<!-- Submit -->
<div class="row">
  <div class="col-sm-12 text-right">
    <a href="suppliers_quote.php" class="btn btn-default">Cancel</a>
    <button type="submit" class="btn btn-primary">Validate</button>
  </div>
</div>

            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
</div>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="../vendor/metisMenu/metisMenu.min.js"></script>
    <script src="../dist/js/sb-admin-2.js"></script>
    <script src="../js/typeahead.js"></script>
<script>
$(function(){
  $('input.companyid').typeahead({
    name: 'Fld_Company_Name',
    remote: 'list-company.php?query=%QUERY'
  });
  $('input.companyidtaginfo').typeahead({
    name: 'Fld_Company_Name',
    remote: 'list-company.php?query=%QUERY'
  });
  $('input.companyidtreacability').typeahead({
    name: 'Fld_Company_Name',
    remote: 'list-company.php?query=%QUERY'
  });
  $('input.pnid').typeahead({
    name: 'Fld_Part_Nbr',
    remote: 'list-pn-select.php?query=%QUERY'
  });

  // Reload contacts when supplier changes
  $('#companyid').on('blur typeahead:selected typeahead:autocompleted', function(){
    var company = $(this).val();
    if (!company) return;
    $.get('contactnamefromcompany-sq.php', {id: company}, function(html){
      $('#divcontactname').html(html);
    });
  });
});
</script>
</body>
</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=".$_SERVER['REQUEST_URI']."\">";
?>
