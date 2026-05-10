<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
if($_SESSION['conectroy']=="parfait"){
if(isset($_GET['mode']) && $_GET['mode'] === 'clean'){
    $quoteId = (int)($_GET['ID'] ?? 0);

    function qh($value) {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    function renderQuoteOptions($sql, $valueField, $labelField, $selected) {
        $req = mysql2_query($sql);
        while($row = mysqli_fetch_array($req)) {
            $value = $row[$valueField];
            echo "<option value='".qh($value)."'";
            if((string)$value === (string)$selected) echo " selected";
            echo ">".qh($row[$labelField])."</option>";
        }
    }

    function sourceInfoForQuote($sourceType, $sourceId) {
        $sourceType = trim((string)$sourceType);
        $sourceId = (int)$sourceId;
        if($sourceType === '' || $sourceId <= 0) {
            return array('type' => '', 'supplier' => '', 'price' => '', 'currency' => '');
        }

        if(stripos($sourceType, 'SQ') !== false) {
            $sql = "SELECT r2.Fld_Price AS price, cur.Fld_Currency_Text AS currency, s.Fld_Company_Name AS supplier
                FROM tbl_RFQ_2 r2
                LEFT JOIN tb_company s ON r2.Fld_Supplier_ID = s.Fld_Company_ID
                LEFT JOIN tbl_Currency cur ON r2.Fld_Currency_ID = cur.Fld_Currency_ID
                WHERE r2.ID='".$sourceId."'
                LIMIT 1";
        } elseif(stripos($sourceType, 'External') !== false) {
            $sql = "SELECT se.Fld_Part_Price AS price, cur.Fld_Currency_Text AS currency, COALESCE(company.Fld_Company_Name, supplier.Fld_Company_Name) AS supplier
                FROM tbl_Stock_external se
                LEFT JOIN tb_company supplier ON se.Fld_Supplier_ID = supplier.Fld_Company_ID
                LEFT JOIN tb_company company ON se.Fld_Company_ID = company.Fld_Company_ID
                LEFT JOIN tbl_Currency cur ON se.Fld_Price_Currency_ID = cur.Fld_Currency_ID
                WHERE se.Fld_Stock_externe_ID='".$sourceId."'
                LIMIT 1";
        } else {
            $sql = "SELECT s.Fld_Part_Price AS price, cur.Fld_Currency_Text AS currency, supplier.Fld_Company_Name AS supplier
                FROM tbl_Stock s
                LEFT JOIN tb_company supplier ON s.Fld_Supplier_ID = supplier.Fld_Company_ID
                LEFT JOIN tbl_Currency cur ON s.Fld_Price_Currency_ID = cur.Fld_Currency_ID
                WHERE s.Fld_Stock_ID='".$sourceId."'
                LIMIT 1";
        }

        $req = mysql2_query($sql);
        $row = $req ? mysqli_fetch_array($req) : null;
        return array(
            'type' => $sourceType,
            'supplier' => $row['supplier'] ?? '',
            'price' => $row['price'] ?? '',
            'currency' => $row['currency'] ?? ''
        );
    }

    function sourceButton($label, $sourceType, $sourceId, $partId, $pn, $description, $qty, $conditionId, $conditionText, $price, $currencyId, $currencyText, $releaseId, $releaseText, $leadTime, $remarks, $supplier) {
        return "<button type='button' class='btn btn-xs btn-success js-use-source'"
            ." data-source-type='".qh($sourceType)."'"
            ." data-source-id='".(int)$sourceId."'"
            ." data-part-id='".(int)$partId."'"
            ." data-pn='".qh($pn)."'"
            ." data-description='".qh($description)."'"
            ." data-qty='".qh($qty)."'"
            ." data-condition-id='".qh($conditionId)."'"
            ." data-condition='".qh($conditionText)."'"
            ." data-price='".qh($price)."'"
            ." data-currency-id='".qh($currencyId)."'"
            ." data-currency='".qh($currencyText)."'"
            ." data-release-id='".qh($releaseId)."'"
            ." data-release='".qh($releaseText)."'"
            ." data-lead-time='".qh($leadTime)."'"
            ." data-remarks='".qh($remarks)."'"
            ." data-supplier='".qh($supplier)."'>".qh($label)."</button>";
    }

    function renderSourceTable($title, $emptyText, $req, $sourceType, $partId, $pn, $description, $isSupplierQuote) {
        echo "<h5>".qh($title)."</h5>";
        if(!$req || mysqli_num_rows($req) === 0) {
            echo "<div class='text-muted source-empty'>".qh($emptyText)."</div>";
            return;
        }

        echo "<table class='table table-condensed table-bordered source-table'>";
        echo "<thead><tr><th>Action</th><th>Supplier / Company</th><th>Qty</th><th>Condition</th><th>Price</th><th>Currency</th><th>Release</th><th>Delivery</th><th>Remarks</th></tr></thead><tbody>";
        while($row = mysqli_fetch_array($req)) {
            if($isSupplierQuote) {
                $sourceId = $row['ID'];
                $supplier = $row['supplier_name'];
                $qty = $row['Fld_Qty'];
                $conditionId = $row['Fld_Condition_ID'];
                $conditionText = $row['Fld_Condition_Text'];
                $price = $row['Fld_Price'];
                $currencyId = $row['Fld_Currency_ID'];
                $currencyText = $row['Fld_Currency_Text'];
                $releaseId = $row['Fld_Release_ID'];
                $releaseText = $row['Fld_Release_Text'];
                $leadTime = $row['lead_time'];
                $remarks = $row['Fld_Remark'];
            } else {
                $sourceId = $row['stock_id'];
                $supplier = $row['supplier_name'];
                $qty = $row['Fld_Qty'];
                $conditionId = $row['Fld_Condition_ID'];
                $conditionText = $row['Fld_Condition_Text'];
                $price = $row['Fld_Part_Price'];
                $currencyId = $row['Fld_Price_Currency_ID'];
                $currencyText = $row['Fld_Currency_Text'];
                $releaseId = $row['Fld_Release_ID'];
                $releaseText = $row['Fld_Release_Text'];
                $leadTime = trim(($row['Fld_Warehouse_Location'] ?? '').' '.($row['Fld_External_Location'] ?? ''));
                $remarks = trim(($row['Fld_Stock_Remark'] ?? '').' '.($row['Fld_Sales_Remark'] ?? ''));
            }

            echo "<tr>";
            echo "<td>".sourceButton('Use this source', $sourceType, $sourceId, $partId, $pn, $description, $qty, $conditionId, $conditionText, $price, $currencyId, $currencyText, $releaseId, $releaseText, $leadTime, $remarks, $supplier)."</td>";
            echo "<td>".qh($supplier)."</td>";
            echo "<td>".qh($qty)."</td>";
            echo "<td>".qh($conditionText)."</td>";
            echo "<td>".qh($price)."</td>";
            echo "<td>".qh($currencyText)."</td>";
            echo "<td>".qh($releaseText)."</td>";
            echo "<td>".qh($leadTime)."</td>";
            echo "<td>".qh($remarks)."</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    }

    $sql = "SELECT q.*,
            p.Fld_Part_Nbr,
            p.Fld_Part_Desc,
            r.Fld_Customer_ID,
            r.id_company_contact,
            cust.Fld_Company_Name AS customer_name,
            contact.Fld_Contact_Name,
            contact.Fld_Contact_Email,
            cond.Fld_Condition_Text,
            cur.Fld_Currency_Text,
            rel.Fld_Release_Text,
            tag.Fld_Company_Name AS tag_info_name,
            trace.Fld_Company_Name AS traceability_name
        FROM tbl_RFQ_3 q
        LEFT JOIN tbl_Parts p ON q.Fld_Part_Id = p.Fld_Part_ID
        LEFT JOIN tbl_RFQ_1 r ON q.id_tbl_rfq1 = r.ID
        LEFT JOIN tb_company cust ON r.Fld_Customer_ID = cust.Fld_Company_ID
        LEFT JOIN tb_company_contact contact ON r.id_company_contact = contact.id_company_contact
        LEFT JOIN tbl_Condition cond ON q.Fld_Condition = cond.Fld_Condition_ID
        LEFT JOIN tbl_Currency cur ON q.Fld_Currency_ID = cur.Fld_Currency_ID
        LEFT JOIN tbl_Release rel ON q.Fld_Release_ID = rel.Fld_Release_ID
        LEFT JOIN tb_company tag ON q.Fld_Tag_Info_ID = tag.Fld_Company_ID
        LEFT JOIN tb_company trace ON q.Fld_Traceability_ID = trace.Fld_Company_ID
        WHERE q.ID='".$quoteId."'
        LIMIT 1";
    $req = mysql2_query($sql);
    $data = $req ? mysqli_fetch_array($req) : null;

    if(!$data) {
        echo "<!DOCTYPE html><html><head><meta charset='utf-8'><link href='../vendor/bootstrap/css/bootstrap.min.css' rel='stylesheet'></head><body><div class='container' style='margin-top:30px'><div class='alert alert-danger'>Quotation not found.</div></div></body></html>";
        exit;
    }

    if((empty($data['customer_name']) || empty($data['Fld_Contact_Name'])) && !empty($data['Fld_RFQ_ID'])) {
        $fallbackSql = "SELECT cust.Fld_Company_Name AS customer_name, contact.Fld_Contact_Name, contact.Fld_Contact_Email
            FROM tbl_RFQ_1 r
            LEFT JOIN tb_company cust ON r.Fld_Customer_ID = cust.Fld_Company_ID
            LEFT JOIN tb_company_contact contact ON r.id_company_contact = contact.id_company_contact
            WHERE r.Fld_RFQ_ID='".addslashes($data['Fld_RFQ_ID'])."'
            ORDER BY r.ID DESC
            LIMIT 1";
        $fallbackReq = mysql2_query($fallbackSql);
        $fallback = $fallbackReq ? mysqli_fetch_array($fallbackReq) : null;
        if($fallback) {
            if(empty($data['customer_name'])) $data['customer_name'] = $fallback['customer_name'];
            if(empty($data['Fld_Contact_Name'])) $data['Fld_Contact_Name'] = $fallback['Fld_Contact_Name'];
            if(empty($data['Fld_Contact_Email'])) $data['Fld_Contact_Email'] = $fallback['Fld_Contact_Email'];
        }
    }

    $source = sourceInfoForQuote($data['source_type'] ?? '', $data['source_id'] ?? 0);
    $partId = (int)$data['Fld_Part_Id'];
    $pn = $data['Fld_Part_Nbr'];
    $description = $data['Fld_Part_Desc'];

    $sqlAciStock = "SELECT s.Fld_Stock_ID AS stock_id,
            s.Fld_Qty, s.Fld_Condition_ID, cond.Fld_Condition_Text,
            s.Fld_Part_Price, s.Fld_Price_Currency_ID, cur.Fld_Currency_Text,
            s.Fld_Release_ID, rel.Fld_Release_Text,
            supplier.Fld_Company_Name AS supplier_name,
            s.Fld_Warehouse_Location, '' AS Fld_External_Location,
            s.Fld_Stock_Remark, s.Fld_Sales_Remark
        FROM tbl_Stock s
        LEFT JOIN tbl_Condition cond ON s.Fld_Condition_ID = cond.Fld_Condition_ID
        LEFT JOIN tbl_Currency cur ON s.Fld_Price_Currency_ID = cur.Fld_Currency_ID
        LEFT JOIN tbl_Release rel ON s.Fld_Release_ID = rel.Fld_Release_ID
        LEFT JOIN tb_company supplier ON s.Fld_Supplier_ID = supplier.Fld_Company_ID
        WHERE s.Fld_Part_ID='".$partId."'
        ORDER BY s.Fld_Stock_ID DESC
        LIMIT 50";
    $reqAciStock = mysql2_query($sqlAciStock);

    $sqlExternalStock = "SELECT se.Fld_Stock_externe_ID AS stock_id,
            se.Fld_Qty, se.Fld_Condition_ID, cond.Fld_Condition_Text,
            se.Fld_Part_Price, se.Fld_Price_Currency_ID, cur.Fld_Currency_Text,
            se.Fld_Release_ID, rel.Fld_Release_Text,
            COALESCE(company.Fld_Company_Name, supplier.Fld_Company_Name) AS supplier_name,
            se.Fld_Warehouse_Location, se.Fld_External_Location,
            se.Fld_Stock_Remark, se.Fld_Sales_Remark
        FROM tbl_Stock_external se
        LEFT JOIN tbl_Condition cond ON se.Fld_Condition_ID = cond.Fld_Condition_ID
        LEFT JOIN tbl_Currency cur ON se.Fld_Price_Currency_ID = cur.Fld_Currency_ID
        LEFT JOIN tbl_Release rel ON se.Fld_Release_ID = rel.Fld_Release_ID
        LEFT JOIN tb_company supplier ON se.Fld_Supplier_ID = supplier.Fld_Company_ID
        LEFT JOIN tb_company company ON se.Fld_Company_ID = company.Fld_Company_ID
        WHERE se.Fld_Part_ID='".$partId."'
        ORDER BY se.Fld_Stock_externe_ID DESC
        LIMIT 50";
    $reqExternalStock = mysql2_query($sqlExternalStock);

    $sqlSupplierQuotes = "SELECT r2.ID,
            r2.Fld_Qty, r2.Fld_Condition_ID, cond.Fld_Condition_Text,
            r2.Fld_Price, r2.Fld_Currency_ID, cur.Fld_Currency_Text,
            r2.Fld_Release_ID, rel.Fld_Release_Text,
            supplier.Fld_Company_Name AS supplier_name,
            r2.lead_time, r2.Fld_Remark
        FROM tbl_RFQ_2 r2
        LEFT JOIN tbl_Condition cond ON r2.Fld_Condition_ID = cond.Fld_Condition_ID
        LEFT JOIN tbl_Currency cur ON r2.Fld_Currency_ID = cur.Fld_Currency_ID
        LEFT JOIN tbl_Release rel ON r2.Fld_Release_ID = rel.Fld_Release_ID
        LEFT JOIN tb_company supplier ON r2.Fld_Supplier_ID = supplier.Fld_Company_ID
        WHERE r2.Fld_Part_ID='".$partId."'
            AND r2.Fld_Supplier_ID IS NOT NULL
            AND r2.Fld_Supplier_ID <> ''
            AND r2.Fld_Supplier_ID <> '0'
        ORDER BY r2.ID DESC
        LIMIT 50";
    $reqSupplierQuotes = mysql2_query($sqlSupplierQuotes);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quotation Preparation</title>
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <style>
        body { background:#f5f5f5; }
        .quote-shell { max-width:1180px; margin:22px auto; }
        .quote-header { background:#fff; border:1px solid #ddd; padding:16px 18px; margin-bottom:14px; }
        .quote-title { margin:0; font-size:22px; font-weight:600; }
        .quote-card { background:#fff; border:1px solid #ddd; padding:16px; margin-bottom:14px; }
        .quote-card h4 { margin-top:0; font-size:16px; font-weight:600; }
        .quote-context dt { width:120px; }
        .quote-context dd { margin-left:135px; margin-bottom:6px; }
        .form-actions { text-align:right; padding:14px 0 4px; }
        textarea.form-control { min-height:92px; }
        .source-table { font-size:12px; background:#fff; }
        .source-table th { background:#eee; }
        .source-empty { margin:0 0 10px; }
        .source-summary { margin-top:8px; }
    </style>
</head>
<body>
<div class="quote-shell">
    <div class="quote-header">
        <h1 class="quote-title">Quotation Preparation</h1>
        <div class="text-muted">Review and adjust the quote before preparing the customer email.</div>
    </div>

    <div class="row">
        <div class="col-sm-7">
            <div class="quote-card">
                <h4>Context</h4>
                <dl class="dl-horizontal quote-context">
                    <dt>RFQ ID</dt><dd><?php echo qh($data['Fld_RFQ_ID']); ?></dd>
                    <dt>PN</dt><dd><?php echo qh($data['Fld_Part_Nbr']); ?></dd>
                    <dt>Description</dt><dd><?php echo qh($data['Fld_Part_Desc']); ?></dd>
                    <dt>Customer</dt><dd><?php echo qh($data['customer_name']); ?></dd>
                    <dt>Contact</dt><dd><?php echo qh($data['Fld_Contact_Name']); ?> <?php if(!empty($data['Fld_Contact_Email'])) echo '&lt;'.qh($data['Fld_Contact_Email']).'&gt;'; ?></dd>
                </dl>
            </div>
        </div>
        <div class="col-sm-5">
            <div class="quote-card">
                <h4>Source</h4>
                <?php if(!empty($source['type'])) { ?>
                    <dl class="dl-horizontal quote-context">
                        <dt>Type</dt><dd><?php echo qh($source['type']); ?> #<?php echo (int)$data['source_id']; ?></dd>
                        <dt>Supplier</dt><dd><?php echo qh($source['supplier']); ?></dd>
                        <dt>Source Price</dt><dd><?php echo qh($source['price']); ?> <?php echo qh($source['currency']); ?></dd>
                    </dl>
                <?php } else { ?>
                    <div class="text-muted">No stock or supplier quote source is linked to this customer quote.</div>
                <?php } ?>
            </div>
        </div>
    </div>

    <form method="post" action="valid_modif_quotation.php">
        <input type="hidden" name="ID" value="<?php echo (int)$data['ID']; ?>">
        <input type="hidden" name="clean_mode" value="1">
        <input type="hidden" name="id_tbl_rfq1" value="<?php echo (int)$data['id_tbl_rfq1']; ?>">
        <input type="hidden" name="source_type" id="source_type" value="<?php echo qh($data['source_type'] ?? ''); ?>">
        <input type="hidden" name="source_id" id="source_id" value="<?php echo (int)($data['source_id'] ?? 0); ?>">
        <input type="hidden" name="Fld_RFQ_ID" value="<?php echo qh($data['Fld_RFQ_ID']); ?>">
        <input type="hidden" name="Fld_Quote_Date" value="<?php echo qh($data['Fld_Quote_Date']); ?>">
        <input type="hidden" name="Fld_Part_Id" value="<?php echo (int)$data['Fld_Part_Id']; ?>">
        <input type="hidden" name="Fld_Traceability_ID" value="<?php echo qh($data['Fld_Traceability_ID']); ?>">
        <input type="hidden" name="Fld_Tag_Info_ID" value="<?php echo qh($data['Fld_Tag_Info_ID']); ?>">
        <input type="hidden" name="Fld_Priority_ID" value="<?php echo qh($data['Fld_Priority_ID']); ?>">
        <input type="hidden" name="moq" value="<?php echo qh($data['moq']); ?>">

        <div class="quote-card">
            <h4>Customer Contact</h4>
            <div class="row">
                <div class="col-sm-5">
                    <div class="form-group">
                        <label>Contact</label>
                        <select class="form-control" name="customer_contact_id">
                            <?php
                            $contactSql = "SELECT id_company_contact, Fld_Contact_Name, Fld_Contact_Email
                                FROM tb_company_contact
                                WHERE Fld_Company_ID='".(int)$data['Fld_Customer_ID']."'
                                ORDER BY Fld_Contact_Name";
                            $contactReq = mysql2_query($contactSql);
                            while($contactRow = mysqli_fetch_array($contactReq)) {
                                echo "<option value='".(int)$contactRow['id_company_contact']."'";
                                if((int)$contactRow['id_company_contact'] === (int)$data['id_company_contact']) echo " selected";
                                echo ">".qh($contactRow['Fld_Contact_Name'])." - ".qh($contactRow['Fld_Contact_Email'])."</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="quote-card">
            <h4>Available Sources</h4>
            <div id="source-current" class="alert alert-info source-summary">
                Current source:
                <strong><?php echo qh($source['type'] ?: 'None'); ?></strong>
                <?php if(!empty($data['source_id'])) echo '#'.(int)$data['source_id']; ?>
                <?php if(!empty($source['supplier'])) echo ' | '.qh($source['supplier']); ?>
                <?php if(!empty($source['price'])) echo ' | '.qh($source['price']).' '.qh($source['currency']); ?>
            </div>
            <?php
                renderSourceTable('ACI770 Stock', 'No ACI770 stock found.', $reqAciStock, 'STOCK-ACI770', $partId, $pn, $description, false);
                renderSourceTable('External Stock', 'No External stock found.', $reqExternalStock, 'STOCK-External', $partId, $pn, $description, false);
                renderSourceTable('Supplier Quotes', 'No supplier quotes found.', $reqSupplierQuotes, 'SQ', $partId, $pn, $description, true);
            ?>
        </div>

        <div class="quote-card">
            <h4>Quote Data</h4>
            <div class="row">
                <div class="col-sm-2">
                    <div class="form-group">
                        <label>Qty</label>
                        <input class="form-control" name="Fld_Qty" value="<?php echo qh($data['Fld_Qty']); ?>">
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label>Condition</label>
                        <select class="form-control" name="Fld_Condition">
                            <?php renderQuoteOptions("SELECT Fld_Condition_ID, Fld_Condition_Text FROM tbl_Condition ORDER BY Fld_Condition_Text", 'Fld_Condition_ID', 'Fld_Condition_Text', $data['Fld_Condition']); ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <label>Price</label>
                        <input class="form-control" name="Fld_Price" value="<?php echo qh($data['Fld_Price']); ?>">
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <label>Currency</label>
                        <select class="form-control" name="Fld_Currency_ID">
                            <?php renderQuoteOptions("SELECT Fld_Currency_ID, Fld_Currency_Text FROM tbl_Currency ORDER BY Fld_Currency_Text", 'Fld_Currency_ID', 'Fld_Currency_Text', $data['Fld_Currency_ID']); ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label>Certification / Release</label>
                        <select class="form-control" name="Fld_Release_ID">
                            <?php renderQuoteOptions("SELECT Fld_Release_ID, Fld_Release_Text FROM tbl_Release ORDER BY Fld_Release_Text", 'Fld_Release_ID', 'Fld_Release_Text', $data['Fld_Release_ID']); ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-3">
                    <div class="form-group">
                        <label>Delivery</label>
                        <input class="form-control" name="lead_time" value="<?php echo qh($data['lead_time']); ?>">
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label>Part SN</label>
                        <input class="form-control" name="Fld_Part_SN" value="<?php echo qh($data['Fld_Part_SN']); ?>">
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label>Tag Info</label>
                        <input class="form-control" value="<?php echo qh($data['tag_info_name']); ?>">
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label>Tag Date</label>
                        <input class="form-control" name="Fld_Tag_Date" value="<?php echo qh($data['Fld_Tag_Date']); ?>">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Remarks</label>
                <textarea class="form-control" name="Fld_Remark"><?php echo qh($data['Fld_Remark']); ?></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" name="send_quotation" value="1" class="btn btn-danger btn-lg">
                    <i class="fa fa-paper-plane"></i> Send Quotation
                </button>
            </div>
        </div>
    </form>
</div>
<script src="../vendor/jquery/jquery.min.js"></script>
<script>
$(document).on('click', '.js-use-source', function(){
    var btn = $(this);
    var qty = btn.data('qty');
    var conditionId = btn.data('condition-id');
    var price = btn.data('price');
    var currencyId = btn.data('currency-id');
    var releaseId = btn.data('release-id');
    var leadTime = btn.data('lead-time');
    var remarks = btn.data('remarks');
    var sourceType = btn.data('source-type');
    var sourceId = btn.data('source-id');
    var supplier = btn.data('supplier') || '';
    var currency = btn.data('currency') || '';

    $('#source_type').val(sourceType);
    $('#source_id').val(sourceId);
    $('input[name="Fld_Qty"]').val(qty);
    $('select[name="Fld_Condition"]').val(String(conditionId));
    $('input[name="Fld_Price"]').val(price);
    $('select[name="Fld_Currency_ID"]').val(String(currencyId));
    $('select[name="Fld_Release_ID"]').val(String(releaseId));
    $('input[name="lead_time"]').val(leadTime);
    $('textarea[name="Fld_Remark"]').val(remarks);

    $('#source-current').removeClass('alert-info').addClass('alert-success')
        .html('Selected source: <strong>' + $('<div>').text(sourceType).html() + '</strong> #' + sourceId
            + (supplier ? ' | ' + $('<div>').text(supplier).html() : '')
            + (price ? ' | ' + $('<div>').text(price + ' ' + currency).html() : ''));
});
</script>
</body>
</html>
<?php
    exit;
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
</head>

<body>
    <div id="wrapper">
  <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
    <?php include "top_menu.php"; ?>                       <!-- barre rouge -->
    <?php if(isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
</nav>
<?php include "after_nav.php"; ?>

 

        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.html"></a>
            </div>
            <!-- /.navbar-header -->

            <?php
		//ajout le menu du haut
		include "top_menu.php";
	   ?>
            <!-- /.navbar-top-links -->

        <?php
		//ajout le menu de gauche
		include "left_menu.php";
	   ?>
            <!-- /.navbar-static-side -->
        </nav>
		<?php 
		//****tbl_RFQ_3**** ID  Fld_RFQ_ID  Fld_Quote_Date  Fld_Part_Id  Fld_Part_SN  Fld_Qty  Fld_Condition  Fld_Price  Fld_Price_Min  Fld_Price_Max  Fld_Currency_ID  Fld_Remark  Fld_Supply_Date  Fld_Traceability_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Release_ID  Fld_Linked_ID  Fld_Exch_Core_Value  Fld_Exch_Core_Value_Currency_ID  Fld_Exch_Cond  Fld_IsBeen_Chosen  Fld_Send_Mail  Fld_Exch_Core_RCVD  moq  lead_time  Fld_Priority_ID
		  
					$sql="SELECT * from tbl_RFQ_3 where ID='".$_GET['ID']."'";
					//echo $sqlrfq2;
					$req = mysql2_query($sql);
					$data = mysqli_fetch_array($req);
		?>
         <div id="page-wrapper">
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
                            MODIF QUOTATION
                        </div>
						<form id="formajoutsq" role="form" method="post" action="valid_modif_quotation.php" enctype="multipart/form-data">
						<input type="hidden" name="ID" value="<?php echo $_GET['ID'];?>">
                        <div class="panel-body">
                            <div class="row">
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>RFQ ID</label>
                                            <input class="form-control" name="Fld_RFQ_ID" value="<?php echo $data['Fld_RFQ_ID'];?>">
                                        </div>
								</div>
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>QUOTATION DATE</label>
                                            <input class="form-control" name="Fld_Quote_Date" value="<?php echo $data['Fld_Quote_Date'];?>">
                                        </div>
								</div>
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>PART SN</label>
											<input class="form-control" name="Fld_Part_SN" value="<?php echo $data['Fld_Part_SN'];?>">
                                        </div>
								</div>
								<div class="col-lg-1">
										<div class="form-group">
                                            <label>QTY</label>
                                            <input class="form-control" name="Fld_Qty" value="<?php echo $data['Fld_Qty'];?>">
                                        </div>
								</div>
								
								<div class="col-lg-2">
										<label>CONDITION</label>
                                            <select class="form-control" name="Fld_Condition">
											<?php

											$sqldiv="SELECT distinct(Fld_Condition_Text),Fld_Condition_ID FROM tbl_Condition order by Fld_Condition_Text";
											
											//echo $sqldiv;
											$reqemp = mysql2_query($sqldiv);
											while($datadiv = mysqli_fetch_array($reqemp))
											{
												echo "<option value='".$datadiv ['Fld_Condition_ID']."'";
												if($datadiv['Fld_Condition_ID']==$data['Fld_Condition']) echo "selected";
												echo ">".$datadiv ['Fld_Condition_Text']."</option>";
											}
											?>
                                                
                                            </select>
								</div>
								<div class="col-lg-3">
								</div>
						</div>	
						<div class="row">
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>PN</label><br>
											<input type="hidden" name="Fld_Part_Id" value="<?php echo $data['Fld_Part_Id'];?>">
											<?php
											//Table tbl_Parts :::: Fld_Part_ID  Fld_Part_Nbr  Fld_Part_Desc  Fld_Part_MFG  Fld_Part_MFG_Old  Fld_AC_ID  Fld_Old_LP  Fld_Part_List_Price  Fld_Part_Price_Currency_ID  Fld_Part_LP_Date  Fld_Remark status
											//recuperation du PN ********************
											$sqlpn="SELECT Fld_Part_Nbr,Fld_Part_Desc FROM tbl_Parts WHERE Fld_Part_ID='".$data['Fld_Part_Id']."'";
											// echo $sqlpn;
											$reqpn = mysql2_query($sqlpn);
											$datapn = mysqli_fetch_array($reqpn);
											//Fin recuperation du PN ********************
											?>
											<input type="text" name="pnid" id="pnid" class="pnid" placeholder="<?php echo $datapn['Fld_Part_Nbr'];?>">
       
                                        </div>
								</div>
								<div class="col-lg-2" id='blocdescription'>
										<div class="form-group" id='divdescription'>
                                            <label>DESCRIPTION</label>
                                            <input class="form-control" name="description"  onclick="javascript:descfrompn();" placeholder="<?php echo $datapn['Fld_Part_Desc'];?>">
                                        </div>
								</div>
								<div class="col-lg-1">
										<div class="form-group">
                                            <label>PRICE</label>
                                            <input class="form-control" name="Fld_Price" value="<?php echo $data['Fld_Price'];?>">
                                        </div>
								</div>
								<div class="col-lg-1">
										<div class="form-group">
                                            <label>$/€</label>
                                            <select class="form-control" name="Fld_Currency_ID">
											<?php
											//recuperation du nom de la currency	
											// Fld_Currency_ID    Fld_Currency_Text
											$sqlcurr="SELECT * FROM tbl_Currency";
											
											//echo $sqldiv;
											$reqcurr = mysql2_query($sqlcurr);
											while($datacurr = mysqli_fetch_array($reqcurr))
											{
												echo "<option value='".$datacurr['Fld_Currency_ID']."'";
												if($datacurr['Fld_Currency_ID']==$data['Fld_Currency_ID']) echo "selected";
												echo ">".$datacurr['Fld_Currency_Text']."</option>";
											}
											?>
                                                
                                            </select>
                                        </div>
									</div>
							</div>
							
							<div class="row">
								
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>TRACEABILITY</label><br>
											<input type="hidden" name="Fld_Traceability_ID" value="<?php echo $data['Fld_Traceability_ID'];?>">
											<!--Traceability sont les noms de compagnie-->
											<?php
											//recuperation du nom de compagnie TRACABILITY ********************
											$sqltrac="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID=".$data['Fld_Traceability_ID'];
											$reqtrac = mysql2_query($sqltrac);
											$datatrac = mysqli_fetch_array($reqtrac);
											//Fin recuperation du nom de compagnie TRACABILITY ********************
											?>
                                            <input type="text" name="companyidtreacability" id="companyidtreacability" class="companyidtreacability" placeholder="<?php echo $datatrac['Fld_Company_Name'];?>" >
                                        </div>
								</div>
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>TAG INFO</label><br>
											<input type="hidden" name="Fld_Tag_Info_ID" value="<?php echo $data['Fld_Tag_Info_ID'];?>">
											<?php
											//recuperation du nom de compagnie TAG INFO ********************
											$sqltiid="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID=".$data['Fld_Tag_Info_ID'];
											$reqtiid = mysql2_query($sqltiid);
											$datatiid = mysqli_fetch_array($reqtiid);
											//Fin recuperation du nom de compagnie TAG INFO ********************
											?>
											<input type="text" name="companyidtaginfo" id="companyidtaginfo" class="companyidtaginfo" placeholder="<?php echo $datatiid['Fld_Company_Name'];?>" >
                                        </div>
								</div>
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>TAG DATE (JJ-MM-AA)</label>
                                            <input class="form-control" name="Fld_Tag_Date" value="<?php echo $data['Fld_Tag_Date'];?>">
                                        </div>
								</div>
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>RELEASE</label>
                                            <select class="form-control" name="Fld_Release_ID">
											<?php
											$sqlrel="SELECT * FROM tbl_Release order by Fld_Release_Text";
											
											//echo $sqldiv;
											$reqrel = mysql2_query($sqlrel);
											while($datarel = mysqli_fetch_array($reqrel))
											{
												echo "<option value='".$datarel['Fld_Release_ID']."'";
												if($datarel['Fld_Release_ID']==$data['Fld_Release_ID']) echo "selected";
												echo ">".$datarel['Fld_Release_Text']."</option>";
											}
											?>
                                            </select>
                                        </div>
								</div>

							</div>

    
							<div class="row">
							
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>MOQ</label>
											<input class="form-control" name="moq" value="<?php echo $data['moq'];?>">
                                    </div>
                                </div>
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>LEAD TIME</label>
											<input class="form-control" name="lead_time" value="<?php echo $data['lead_time'];?>">
                                    </div>
                                </div>
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>PRIORITY</label>
											<select class="form-control" name="Fld_Priority_ID">
											<?php
											//recuperation Priority
											// ** tbl_Priority ** Fld_Priority_ID  Fld_Priority_Text
					                        $sqlPriority="SELECT * FROM tbl_Priority";
											
											$reqPriority = mysql2_query($sqlPriority);
											while($dataPriority = mysqli_fetch_array($reqPriority)){
												echo "<option value='".$dataPriority['Fld_Priority_ID']."'";
												if($dataPriority['Fld_Priority_ID']==$data['Fld_Priority_ID']) echo "selected";
												echo ">".$dataPriority['Fld_Priority_Text']."</option>";
											}
					                        //Fin recuperation Priority
											?>
                                                
                                            </select>
                                        </div>
								</div>

							</div>
							<div class="row">
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>REMARK</label>
                                            
											<textarea class="form-control" rows="3" name="Fld_Remark"><?php echo $data['Fld_Remark'];?></textarea>
                                        </div>
								</div>
							</div>
							          

                        <!-- /.panel-body -->
									<div align="center">
										<button type="submit" class="btn btn-default">Validate</button>
										&nbsp;&nbsp;&nbsp;
										<button type="submit" name="send_quotation" value="1" class="btn btn-primary">SEND QUOTATION</button>
									</div>
						</form>
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
	
	<script type="text/javascript" src="../js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
	<script type="text/javascript" src="../js/locales/bootstrap-datetimepicker.fr.js" charset="UTF-8"></script>

    <!-- Page-Level Demo Scripts - Tables - Use for reference -->
    <script type="text/javascript">
    $(document).ready(function() {
        $('#dataTables-example').DataTable({
            responsive: true
        });
    });
    </script>
<!--*****************************************************************************************************-->
<!--*****************************************************************************************************-->
<!--*****************************************************************************************************-->
<!--Ajout pour autocompression Roy-->
 <!--<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>-->
    <script src="js/typeahead.js"></script>
    <style>
       
		.tt-hint,
        .companyid,.companyidtaginfo,.companyidtreacability,.pnid {
            display: block;
    width: 190px;
    height: 34px;
    padding: 6px 12px;
    font-size: 14px;
    line-height: 1.42857143;
    color: #555;
    background-color: #fff;
    background-image: none;
    border: 1px solid #ccc;
    border-radius: 4px;
        }

        .tt-dropdown-menu {
            width: 400px;
            margin-top: 5px;
            padding: 8px 12px;
            background-color: #fff;
            border: 1px solid #ccc;
            border: 1px solid rgba(0, 0, 0, 0.2);
            border-radius: 8px 8px 8px 8px;
            font-size: 18px;
            color: #111;
            background-color: #F1F1F1;
        }
    </style>
    <script>
        $(document).ready(function() {

            $('input.companyid').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
			$('input.companyidtaginfo').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
			$('input.companyidtreacability').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
			$('input.pnid').typeahead({
                name: 'Fld_Part_Nbr',
				id: 'Fld_Part_ID',
                remote: 'list-pn-select.php?query=%QUERY'
            });
        })
<!--Fin Ajout pour autocompression Roy-->
<!--*****************************************************************************************************-->
<!--*****************************************************************************************************-->
<!--*****************************************************************************************************-->

<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!--Ajout nom contact a partir du nom de la societe-->
<!--*******************************************************************************-->
<!--*******************************************************************************-->
	function majtarea(id)
{
var bloccontactname=document.getElementById('bloccontactname');
var companyidval=document.getElementById('companyid').value;

bloccontactname.style.display='inline';

//document.getElementById("divcontactname").innerHTML='<div id="divcontactname" align="center"><img src="../images_design/Spin.gif" border="0"></div>';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "contactnamefromcompany-sq.php?id="+companyidval, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { up_contact_name(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
    
}
function up_contact_name(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divcontactname').innerHTML='<div id="'+id+'" align="center">';
         var resp2;
        resp2 = xhr.responseText;
        document.getElementById('divcontactname').innerHTML+=resp2;
    document.getElementById('divcontactname').innerHTML+='</div>';
    }
}

<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!-- Fin Ajout nom contact a partir du nom de la societe-->
<!--*******************************************************************************-->
<!--*******************************************************************************-->

<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!--Recuperation Description a partir du P/N-->
<!--*******************************************************************************--> 
<!--*******************************************************************************-->
	function descfrompn(id)
{
var blocdescription=document.getElementById('blocdescription');
var pnid=document.getElementById('pnid').value;

blocdescription.style.display='inline';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "descriptionfrompn.php?id="+pnid, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { up_descfrompn(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
    
}
function up_descfrompn(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divdescription').innerHTML='<div id="'+id+'" align="center">';
         var resp2;
        resp2 = xhr.responseText;
        document.getElementById('divdescription').innerHTML+=resp2;
    document.getElementById('divdescription').innerHTML+='</div>';
    }
}

<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!-- Fin Recuperation Description a partir du P/N-->
<!--*******************************************************************************-->
<!--*******************************************************************************-->
</script>
</body>

</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php\">";
?>
