<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
    header("Location: login.php?url=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

if (isset($sql_bdd) && $sql_bdd !== 'aerocanada_yoyamic') {
    die('Invalid database selected.');
}

$db = isset($connection) && $connection ? $connection : (isset($conn) ? $conn : null);
$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
if ($id <= 0 || !$db) {
    die('Invalid external stock row.');
}

function externalStockPost($key, $default = '') {
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}

$errors = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = array(
        'Fld_Qty' => (int)externalStockPost('Fld_Qty', '0'),
        'Fld_Condition_ID' => (int)externalStockPost('Fld_Condition_ID', '0'),
        'Fld_Physical_Stock' => externalStockPost('Fld_Physical_Stock'),
        'Fld_Entry_Date' => externalStockPost('Fld_Entry_Date'),
        'Fld_Stock_Remark' => externalStockPost('Fld_Stock_Remark'),
        'Fld_Sales_Remark' => externalStockPost('Fld_Sales_Remark')
    );

    $set = array();
    foreach ($fields as $col => $val) {
        $set[] = "`".$col."`='".mysqli_real_escape_string($db, (string)$val)."'";
    }

    $sql = "UPDATE tbl_Stock_external SET ".implode(',', $set)." WHERE Fld_Stock_externe_ID=".$id;
    if (mysql2_query($sql)) {
        header("Location: stock_external.php");
        exit();
    }
    $errors[] = 'Unable to update external stock: '.mysqli_error($db);
}

$query = mysql2_query("
    SELECT
      se.*,
      p.Fld_Part_Nbr,
      p.Fld_Part_Desc,
      company.Fld_Company_Name
    FROM tbl_Stock_external se
    LEFT JOIN tbl_Parts p ON se.Fld_Part_ID = p.Fld_Part_ID
    LEFT JOIN tb_company company ON se.Fld_Company_ID = company.Fld_Company_ID
    WHERE se.Fld_Stock_externe_ID = ".$id."
    LIMIT 1
");
$row = $query ? mysqli_fetch_assoc($query) : null;
if (!$row) {
    die('External stock row not found.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aerocanada - Edit External Stock</title>
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/metisMenu/metisMenu.min.css" rel="stylesheet">
    <link href="../dist/css/sb-admin-2.css" rel="stylesheet">
    <link href="../dist/css/aci-overrides.css" rel="stylesheet">
    <link href="../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
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
      <div class="col-lg-12"><h1 class="page-header">EDIT EXTERNAL STOCK</h1></div>
    </div>

    <?php if (!empty($errors)) { ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $error) echo "<div>".htmlspecialchars($error, ENT_QUOTES, 'UTF-8')."</div>"; ?>
      </div>
    <?php } ?>

    <div class="panel panel-default">
      <div class="panel-heading" style="background-color:#A7142A;color:#fff">External Stock</div>
      <div class="panel-body">
        <form method="post" action="modif_external_stock.php?id=<?php echo $id; ?>" role="form">
          <input type="hidden" name="id" value="<?php echo $id; ?>">

          <div class="row">
            <div class="col-sm-4">
              <div class="form-group">
                <label>PN</label>
                <input class="form-control" value="<?php echo htmlspecialchars($row['Fld_Part_Nbr'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly>
              </div>
            </div>
            <div class="col-sm-8">
              <div class="form-group">
                <label>DESCRIPTION</label>
                <input class="form-control" value="<?php echo htmlspecialchars($row['Fld_Part_Desc'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-4">
              <div class="form-group">
                <label>COMPANY</label>
                <input class="form-control" value="<?php echo htmlspecialchars($row['Fld_Company_Name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly>
              </div>
            </div>
            <div class="col-sm-2">
              <div class="form-group">
                <label>QTY</label>
                <input type="number" name="Fld_Qty" class="form-control" value="<?php echo htmlspecialchars($row['Fld_Qty'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
              </div>
            </div>
            <div class="col-sm-3">
              <div class="form-group">
                <label>CONDITION</label>
                <select name="Fld_Condition_ID" class="form-control">
                  <option value=""></option>
                  <?php
                  $q = mysql2_query("SELECT Fld_Condition_ID, Fld_Condition_Text FROM tbl_Condition ORDER BY Fld_Condition_Text");
                  while ($r = mysqli_fetch_assoc($q)) {
                      $selected = ((string)$r['Fld_Condition_ID'] === (string)$row['Fld_Condition_ID']) ? ' selected' : '';
                      echo "<option value='".$r['Fld_Condition_ID']."'".$selected.">".htmlspecialchars($r['Fld_Condition_Text'], ENT_QUOTES, 'UTF-8')."</option>";
                  }
                  ?>
                </select>
              </div>
            </div>
            <div class="col-sm-3">
              <div class="form-group">
                <label>PHYSICAL STOCK</label>
                <input name="Fld_Physical_Stock" class="form-control" value="<?php echo htmlspecialchars($row['Fld_Physical_Stock'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-3">
              <div class="form-group">
                <label>ENTRY DATE</label>
                <input name="Fld_Entry_Date" class="form-control" value="<?php echo htmlspecialchars($row['Fld_Entry_Date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label>STOCK REMARKS</label>
                <textarea name="Fld_Stock_Remark" class="form-control" rows="3"><?php echo htmlspecialchars($row['Fld_Stock_Remark'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>SALES REMARKS</label>
                <textarea name="Fld_Sales_Remark" class="form-control" rows="3"><?php echo htmlspecialchars($row['Fld_Sales_Remark'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
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
</body>
</html>
