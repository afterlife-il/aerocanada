<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

if ($_SESSION['conectroy'] !== "parfait") {
    header("Location: login.php?url=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

function firstCsvValue($value) {
    $parts = explode(',', (string)$value);
    return trim($parts[0] ?? '');
}

function postValue($key, $default = '') {
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}

$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($sql_bdd) && $sql_bdd !== 'aerocanada_yoyamic') {
        $errors[] = 'Invalid database selected.';
    }

    $partId = (int)postValue('Fld_Part_ID', '0');
    if ($partId <= 0 && postValue('pnid') !== '') {
        $pnParts = explode(',', postValue('pnid'));
        $partId = (int)trim($pnParts[1] ?? '0');
    }
    if ($partId <= 0) {
        $errors[] = 'Please select a PN from autocomplete.';
    }

    $companyId = (int)firstCsvValue(postValue('companyid'));
    if ($companyId <= 0) {
        $errors[] = 'Please select a company from autocomplete.';
    }

    $supplierId = (int)firstCsvValue(postValue('supplierid'));
    $tagInfoId = (int)firstCsvValue(postValue('taginfoid'));
    $traceabilityId = (int)firstCsvValue(postValue('traceabilityid'));

    if (empty($errors)) {
        $fields = array(
            'Fld_Part_ID' => $partId,
            'Fld_Part_SN' => postValue('Fld_Part_SN'),
            'Fld_Supplier_ID' => $supplierId,
            'Fld_Entry_Date' => postValue('Fld_Entry_Date', date('Y-m-d')),
            'Fld_Part_Price' => (float)postValue('Fld_Part_Price', '0'),
            'Fld_Price_Currency_ID' => (int)postValue('Fld_Price_Currency_ID', '0'),
            'Fld_Qty' => (int)postValue('Fld_Qty', '0'),
            'Fld_Condition_ID' => (int)postValue('Fld_Condition_ID', '0'),
            'Fld_Release_ID' => (int)postValue('Fld_Release_ID', '0'),
            'Fld_Tag_Info_ID' => $tagInfoId,
            'Fld_Tag_Date' => postValue('Fld_Tag_Date'),
            'Fld_Traceability_ID' => $traceabilityId,
            'Fld_Warehouse_Location' => postValue('Fld_Warehouse_Location'),
            'Fld_Stock_Remark' => postValue('Fld_Stock_Remark'),
            'Fld_Sales_Remark' => postValue('Fld_Sales_Remark'),
            'Fld_Publish' => postValue('Fld_Publish', 'YES'),
            'status' => postValue('status', 'available'),
            'Fld_Company_ID' => $companyId
        );

        $cols = array();
        $vals = array();
        foreach ($fields as $col => $val) {
            $cols[] = "`".$col."`";
            $vals[] = "'".mysqli_real_escape_string($connection, (string)$val)."'";
        }

        $sql = "INSERT INTO tbl_Stock_external (".implode(',', $cols).") VALUES (".implode(',', $vals).")";
        if (mysql2_query($sql)) {
            header("Location: stock_external.php");
            exit();
        }
        $errors[] = 'Unable to save external stock.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aerocanada - Add External Stock</title>
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/metisMenu/metisMenu.min.css" rel="stylesheet">
    <link href="../vendor/datatables-plugins/dataTables.bootstrap.css" rel="stylesheet">
    <link href="../vendor/datatables-responsive/dataTables.responsive.css" rel="stylesheet">
    <link href="../dist/css/sb-admin-2.css" rel="stylesheet">
    <link href="../dist/css/aci-overrides.css" rel="stylesheet">
    <link href="../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <style>
        .twitter-typeahead { display:block !important; width:100%; }
        .twitter-typeahead .tt-input, .twitter-typeahead .tt-hint { width:100%; }
        .tt-dropdown-menu, .tt-menu, .typeahead.dropdown-menu {
            z-index:3000; background:#fff; border:1px solid #ccc; border-radius:4px;
            box-shadow:0 4px 10px rgba(0,0,0,.15); width:100%; max-height:260px; overflow-y:auto;
        }
        .tt-suggestion { color:#333; padding:6px 10px; cursor:pointer; }
        .tt-suggestion p { margin:0; }
        .tt-suggestion.tt-is-under-cursor, .tt-suggestion.tt-cursor, .tt-suggestion:hover {
            background:#337ab7; color:#fff;
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
        <h1 class="page-header">ADD EXTERNAL STOCK</h1>
      </div>
    </div>

    <?php if (!empty($errors)) { ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $error) echo "<div>".htmlspecialchars($error)."</div>"; ?>
      </div>
    <?php } ?>

    <div class="panel panel-default">
      <div class="panel-heading" style="background-color:#A7142A;color:#fff">Manual External Stock Entry</div>
      <div class="panel-body">
        <form method="post" action="add_external_stock.php" role="form">
          <input type="hidden" name="Fld_Part_ID" id="Fld_Part_ID" value="">

          <div class="row">
            <div class="col-sm-4">
              <div class="form-group">
                <label>PN</label>
                <input type="text" name="pnid" id="pnid" class="form-control pnid" placeholder="Please Enter P/N" required>
              </div>
            </div>
            <div class="col-sm-8">
              <div class="form-group">
                <label>DESCRIPTION</label>
                <input class="form-control" name="description" id="description" readonly>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-4">
              <div class="form-group">
                <label>COMPANY / OWNER</label>
                <input type="text" name="companyid" id="companyid" class="form-control companyid" placeholder="Please Enter company" required>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label>SUPPLIER</label>
                <input type="text" name="supplierid" class="form-control supplierid" placeholder="Please Enter supplier">
              </div>
            </div>
            <div class="col-sm-2">
              <div class="form-group">
                <label>QTY</label>
                <input type="number" name="Fld_Qty" class="form-control" value="1" min="0">
              </div>
            </div>
            <div class="col-sm-2">
              <div class="form-group">
                <label>SN</label>
                <input type="text" name="Fld_Part_SN" class="form-control">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-3">
              <div class="form-group">
                <label>CONDITION</label>
                <select name="Fld_Condition_ID" class="form-control">
                  <option value=""></option>
                  <?php
                  $q = mysql2_query("SELECT Fld_Condition_ID, Fld_Condition_Text FROM tbl_Condition ORDER BY Fld_Condition_Text");
                  while ($r = mysqli_fetch_assoc($q)) echo "<option value='".$r['Fld_Condition_ID']."'>".htmlspecialchars($r['Fld_Condition_Text'])."</option>";
                  ?>
                </select>
              </div>
            </div>
            <div class="col-sm-3">
              <div class="form-group">
                <label>RELEASE / CERTIFICATION</label>
                <select name="Fld_Release_ID" class="form-control">
                  <option value=""></option>
                  <?php
                  $q = mysql2_query("SELECT Fld_Release_ID, Fld_Release_Text FROM tbl_Release ORDER BY Fld_Release_Text");
                  while ($r = mysqli_fetch_assoc($q)) echo "<option value='".$r['Fld_Release_ID']."'>".htmlspecialchars($r['Fld_Release_Text'])."</option>";
                  ?>
                </select>
              </div>
            </div>
            <div class="col-sm-3">
              <div class="form-group">
                <label>PRICE / COST</label>
                <input type="number" step="0.01" name="Fld_Part_Price" class="form-control">
              </div>
            </div>
            <div class="col-sm-3">
              <div class="form-group">
                <label>CURRENCY</label>
                <select name="Fld_Price_Currency_ID" class="form-control">
                  <option value=""></option>
                  <?php
                  $q = mysql2_query("SELECT Fld_Currency_ID, Fld_Currency_Text FROM tbl_Currency ORDER BY Fld_Currency_Text");
                  while ($r = mysqli_fetch_assoc($q)) echo "<option value='".$r['Fld_Currency_ID']."'>".htmlspecialchars($r['Fld_Currency_Text'])."</option>";
                  ?>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-4">
              <div class="form-group">
                <label>TAG INFO</label>
                <input type="text" name="taginfoid" class="form-control taginfoid" placeholder="Please Enter company">
              </div>
            </div>
            <div class="col-sm-2">
              <div class="form-group">
                <label>TAG DATE</label>
                <input type="text" name="Fld_Tag_Date" class="form-control" placeholder="YYYY-MM-DD">
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label>TRACEABILITY</label>
                <input type="text" name="traceabilityid" class="form-control traceabilityid" placeholder="Please Enter company">
              </div>
            </div>
            <div class="col-sm-2">
              <div class="form-group">
                <label>ENTRY DATE</label>
                <input type="text" name="Fld_Entry_Date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-4">
              <div class="form-group">
                <label>LOCATION</label>
                <input type="text" name="Fld_Warehouse_Location" class="form-control">
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label>STATUS</label>
                <select name="status" class="form-control">
                  <option value="available">available</option>
                  <option value="Available">Available</option>
                </select>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label>PUBLISH</label>
                <select name="Fld_Publish" class="form-control">
                  <option value="YES">YES</option>
                  <option value="NO">NO</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label>STOCK REMARKS</label>
                <textarea name="Fld_Stock_Remark" class="form-control" rows="3"></textarea>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>SALES REMARKS</label>
                <textarea name="Fld_Sales_Remark" class="form-control" rows="3"></textarea>
              </div>
            </div>
          </div>

          <div class="text-right">
            <a href="stock_external.php" class="btn btn-default">Cancel</a>
            <button type="submit" class="btn btn-primary">Validate</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="../vendor/metisMenu/metisMenu.min.js"></script>
<script src="../dist/js/sb-admin-2.js"></script>
<script src="js/typeahead.js"></script>
<script>
$(function(){
    $('input.companyid').typeahead({ name: 'Fld_Company_Name', id: 'Fld_Company_ID', remote: 'list-company.php?query=%QUERY' });
    $('input.supplierid').typeahead({ name: 'Fld_Company_Name', id: 'Fld_Company_ID', remote: 'list-company.php?query=%QUERY' });
    $('input.taginfoid').typeahead({ name: 'Fld_Company_Name', id: 'Fld_Company_ID', remote: 'list-company.php?query=%QUERY' });
    $('input.traceabilityid').typeahead({ name: 'Fld_Company_Name', id: 'Fld_Company_ID', remote: 'list-company.php?query=%QUERY' });
    $('input.pnid').typeahead({ name: 'Fld_Part_Nbr', id: 'Fld_Part_ID', remote: 'list-pn-select.php?query=%QUERY' });

    function loadPn(value) {
        var parts = (value || '').split(',');
        if (parts.length > 1) {
            $('#Fld_Part_ID').val($.trim(parts[1]));
            $('#pnid').val($.trim(parts[0]));
        }
        if (!value) return;
        $.get('descriptionfrompn.php', {id: value}, function(html){
            var desc = $('<div>').html(html).find('input[name^="description"]').val();
            if (typeof desc !== 'undefined') $('#description').val(desc);
        });
    }

    $('input.pnid').on('typeahead:selected typeahead:autocompleted', function(ev, suggestion){
        loadPn((suggestion && suggestion.value) ? suggestion.value : this.value);
    });
    $('#pnid').on('blur', function(){ loadPn(this.value); });
});
</script>
</body>
</html>
