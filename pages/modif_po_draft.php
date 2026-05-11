<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
    echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php\">";
    exit;
}

$draftId = (int)($_GET['id'] ?? 0);
if ($draftId <= 0) {
    die("Missing PO draft ID.");
}

function pod_escape($value) {
    global $conn;
    return mysqli_real_escape_string($conn, trim((string)($value ?? '')));
}

function pod_h($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "UPDATE tbl_PO_Draft SET
        po_number='".pod_escape($_POST['po_number'] ?? '')."',
        qty='".pod_escape($_POST['qty'] ?? '')."',
        condition_id='".(int)($_POST['condition_id'] ?? 0)."',
        price='".pod_escape($_POST['price'] ?? '')."',
        currency_id='".(int)($_POST['currency_id'] ?? 0)."',
        release_id='".(int)($_POST['release_id'] ?? 0)."',
        delivery='".pod_escape($_POST['delivery'] ?? '')."',
        remarks='".pod_escape($_POST['remarks'] ?? '')."',
        status='".pod_escape($_POST['status'] ?? 'DRAFT')."'
        WHERE id='".$draftId."'";
    if (!mysql2_query($sql)) {
        $saveError = "Unable to save PO draft.";
    } else {
        header("Location: modif_po_draft.php?id=".$draftId."&saved=1");
        exit;
    }
}

$sql = "SELECT d.*,
        q.Fld_Quote_Date,
        p.Fld_Part_Nbr,
        p.Fld_Part_Desc,
        cust.Fld_Company_Name AS customer_name,
        contact.Fld_Contact_Name,
        contact.Fld_Contact_Email,
        sourceCompany.Fld_Company_Name AS source_company_name,
        cond.Fld_Condition_Text,
        cur.Fld_Currency_Text,
        rel.Fld_Release_Text
    FROM tbl_PO_Draft d
    LEFT JOIN tbl_RFQ_3 q ON d.quotation_id = q.ID
    LEFT JOIN tbl_Parts p ON d.part_id = p.Fld_Part_ID
    LEFT JOIN tb_company cust ON d.customer_company_id = cust.Fld_Company_ID
    LEFT JOIN tb_company_contact contact ON d.customer_contact_id = contact.id_company_contact
    LEFT JOIN tb_company sourceCompany ON d.source_company_id = sourceCompany.Fld_Company_ID
    LEFT JOIN tbl_Condition cond ON d.condition_id = cond.Fld_Condition_ID
    LEFT JOIN tbl_Currency cur ON d.currency_id = cur.Fld_Currency_ID
    LEFT JOIN tbl_Release rel ON d.release_id = rel.Fld_Release_ID
    WHERE d.id='".$draftId."'
    LIMIT 1";
$res = mysql2_query($sql);
$draft = $res ? mysqli_fetch_assoc($res) : null;
if (!$draft) {
    die("PO draft not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aerocanada-industries.com</title>
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
    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">PO Draft</h1>
                <?php if (!empty($_GET['saved'])) { ?><div class="alert alert-success">PO draft saved.</div><?php } ?>
                <?php if (!empty($saveError)) { ?><div class="alert alert-danger"><?php echo pod_h($saveError); ?></div><?php } ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="panel panel-default">
                    <div class="panel-heading">Context</div>
                    <div class="panel-body">
                        <p><b>Quote ID:</b> <a href="modif_quotations.php?ID=<?php echo (int)$draft['quotation_id']; ?>&mode=clean"><?php echo (int)$draft['quotation_id']; ?></a></p>
                        <p><b>RFQ ID:</b> <?php echo pod_h($draft['rfq_id']); ?></p>
                        <p><b>RFQ Line ID:</b> <?php echo (int)$draft['rfq_line_id']; ?></p>
                        <p><b>PN:</b> <a href="Part-Nbr.php?pn=<?php echo urlencode($draft['Fld_Part_Nbr']); ?>"><?php echo pod_h($draft['Fld_Part_Nbr']); ?></a></p>
                        <p><b>Description:</b> <?php echo pod_h($draft['Fld_Part_Desc']); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="panel panel-default">
                    <div class="panel-heading">Customer</div>
                    <div class="panel-body">
                        <p><b>Company:</b> <?php echo pod_h($draft['customer_name']); ?></p>
                        <p><b>Contact:</b> <?php echo pod_h($draft['Fld_Contact_Name']); ?></p>
                        <p><b>Email:</b> <?php echo pod_h($draft['Fld_Contact_Email']); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="panel panel-default">
                    <div class="panel-heading">Source</div>
                    <div class="panel-body">
                        <p><b>Type:</b> <?php echo pod_h($draft['source_type']); ?></p>
                        <p><b>Source ID:</b> <?php echo (int)$draft['source_id']; ?></p>
                        <p><b>Supplier / Source:</b> <?php echo pod_h($draft['source_company_name']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <form method="post" class="panel panel-default">
            <div class="panel-heading">Editable PO Draft</div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>PO Draft #</label>
                            <input class="form-control" name="po_number" value="<?php echo pod_h($draft['po_number']); ?>">
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="status">
                                <?php foreach (array('DRAFT','READY','ON HOLD') as $status) { ?>
                                    <option value="<?php echo pod_h($status); ?>" <?php if ($draft['status'] === $status) echo 'selected'; ?>><?php echo pod_h($status); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Qty</label>
                            <input class="form-control" name="qty" value="<?php echo pod_h($draft['qty']); ?>">
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Condition</label>
                            <select class="form-control" name="condition_id">
                                <?php
                                $conditions = mysql2_query("SELECT Fld_Condition_ID, Fld_Condition_Text FROM tbl_Condition ORDER BY Fld_Condition_Text");
                                while ($condition = mysqli_fetch_assoc($conditions)) {
                                    echo "<option value='".(int)$condition['Fld_Condition_ID']."'";
                                    if ((int)$draft['condition_id'] === (int)$condition['Fld_Condition_ID']) echo " selected";
                                    echo ">".pod_h($condition['Fld_Condition_Text'])."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Price</label>
                            <input class="form-control" name="price" value="<?php echo pod_h($draft['price']); ?>">
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Currency</label>
                            <select class="form-control" name="currency_id">
                                <?php
                                $currencies = mysql2_query("SELECT Fld_Currency_ID, Fld_Currency_Text FROM tbl_Currency ORDER BY Fld_Currency_Text");
                                while ($currency = mysqli_fetch_assoc($currencies)) {
                                    echo "<option value='".(int)$currency['Fld_Currency_ID']."'";
                                    if ((int)$draft['currency_id'] === (int)$currency['Fld_Currency_ID']) echo " selected";
                                    echo ">".pod_h($currency['Fld_Currency_Text'])."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Certification / Release</label>
                            <select class="form-control" name="release_id">
                                <?php
                                $releases = mysql2_query("SELECT Fld_Release_ID, Fld_Release_Text FROM tbl_Release ORDER BY Fld_Release_Text");
                                while ($release = mysqli_fetch_assoc($releases)) {
                                    echo "<option value='".(int)$release['Fld_Release_ID']."'";
                                    if ((int)$draft['release_id'] === (int)$release['Fld_Release_ID']) echo " selected";
                                    echo ">".pod_h($release['Fld_Release_Text'])."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Delivery</label>
                            <input class="form-control" name="delivery" value="<?php echo pod_h($draft['delivery']); ?>">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Remarks</label>
                    <textarea class="form-control" name="remarks" rows="4"><?php echo pod_h($draft['remarks']); ?></textarea>
                </div>
            </div>
            <div class="panel-footer">
                <button type="submit" class="btn btn-danger"><i class="fa fa-save"></i> Save PO Draft</button>
                <a class="btn btn-default" href="modif_quotations.php?ID=<?php echo (int)$draft['quotation_id']; ?>&mode=clean">Back to Quote</a>
            </div>
        </form>
    </div>
</div>
<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="../vendor/metisMenu/metisMenu.min.js"></script>
<script src="../dist/js/sb-admin-2.js"></script>
</body>
</html>
