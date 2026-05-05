<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

$rfqId = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';
if ($rfqId === '') {
  echo "<div class='alert alert-warning'>No RFQ ID.</div>";
  exit;
}

$sql = "SELECT ID, Fld_RFQ_ID, Fld_Part_ID, Fld_Qty, Fld_Condition_ID, pn_rfq, description_rfq
        FROM tbl_RFQ_1
        WHERE Fld_RFQ_ID='".addslashes($rfqId)."'
        ORDER BY ID";
$req = mysql2_query($sql);

if (!$req || mysqli_num_rows($req) == 0) {
  echo "<div class='alert alert-info' style='margin:10px 0'>No PN for this RFQ.</div>";
  exit;
}

echo "<table class='table table-condensed' style='margin-bottom:0'>";
echo "<thead><tr><th>PN</th><th>Description</th><th></th></tr></thead><tbody>";

while ($row = mysqli_fetch_assoc($req)) {
  $id    = (int)$row['ID'];
  $partId = (int)$row['Fld_Part_ID'];
  $qty   = $row['Fld_Qty'];
  $cond  = $row['Fld_Condition_ID'];
  $pn    = $row['pn_rfq'];
  $desc  = $row['description_rfq'];
  $rfq   = $row['Fld_RFQ_ID'];

  $pnAttr   = htmlspecialchars($pn ?? '',   ENT_QUOTES, 'UTF-8');
  $descAttr = htmlspecialchars($desc ?? '', ENT_QUOTES, 'UTF-8');
  $rfqAttr  = htmlspecialchars($rfq ?? '',  ENT_QUOTES, 'UTF-8');
  $qtyAttr  = htmlspecialchars($qty ?? '',  ENT_QUOTES, 'UTF-8');
  $condAttr = htmlspecialchars($cond ?? '', ENT_QUOTES, 'UTF-8');

  echo "<tr>";
  echo '<td><a href="#" class="js-add-sq"'
     .' data-rfq="'.$rfqAttr.'"'
     .' data-id="'.$id.'"'
     .' data-part-id="'.$partId.'"'
     .' data-qty="'.$qtyAttr.'"'
     .' data-condition-id="'.$condAttr.'"'
     .' data-pn="'.$pnAttr.'"'
     .' data-desc="'.$descAttr.'">'.$pnAttr.'</a></td>';
  echo '<td>'.$descAttr.'</td>';
  echo '<td><button type="button" class="btn btn-xs btn-default js-add-sq"'
     .' data-rfq="'.$rfqAttr.'"'
     .' data-id="'.$id.'"'
     .' data-part-id="'.$partId.'"'
     .' data-qty="'.$qtyAttr.'"'
     .' data-condition-id="'.$condAttr.'"'
     .' data-pn="'.$pnAttr.'"'
     .' data-desc="'.$descAttr.'">Use in form</button></td>';
  echo "</tr>";
}

echo "</tbody></table>";
