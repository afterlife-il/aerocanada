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

function pod_ensure_workflow_columns() {
    $columns = array(
        'customer_po_number' => "ALTER TABLE tbl_PO_Draft ADD COLUMN customer_po_number VARCHAR(100) DEFAULT NULL AFTER po_number",
        'customer_po_file' => "ALTER TABLE tbl_PO_Draft ADD COLUMN customer_po_file VARCHAR(255) DEFAULT NULL AFTER customer_po_number",
        'accepted_price' => "ALTER TABLE tbl_PO_Draft ADD COLUMN accepted_price VARCHAR(30) DEFAULT NULL AFTER customer_po_file",
        'accepted_qty' => "ALTER TABLE tbl_PO_Draft ADD COLUMN accepted_qty VARCHAR(20) DEFAULT NULL AFTER accepted_price",
        'accepted_condition_id' => "ALTER TABLE tbl_PO_Draft ADD COLUMN accepted_condition_id INT(11) DEFAULT NULL AFTER accepted_qty",
        'payment_terms' => "ALTER TABLE tbl_PO_Draft ADD COLUMN payment_terms VARCHAR(255) DEFAULT NULL AFTER accepted_condition_id",
        'shipping_terms' => "ALTER TABLE tbl_PO_Draft ADD COLUMN shipping_terms VARCHAR(255) DEFAULT NULL AFTER payment_terms",
        'shipping_address' => "ALTER TABLE tbl_PO_Draft ADD COLUMN shipping_address TEXT AFTER shipping_terms",
        'shipping_address_id' => "ALTER TABLE tbl_PO_Draft ADD COLUMN shipping_address_id INT(11) DEFAULT NULL AFTER shipping_address",
        'required_documents' => "ALTER TABLE tbl_PO_Draft ADD COLUMN required_documents TEXT AFTER shipping_address_id",
        'missing_information' => "ALTER TABLE tbl_PO_Draft ADD COLUMN missing_information TEXT AFTER required_documents",
        'acceptance_notes' => "ALTER TABLE tbl_PO_Draft ADD COLUMN acceptance_notes TEXT AFTER missing_information"
    );
    foreach ($columns as $column => $alterSql) {
        $exists = mysql2_query("SHOW COLUMNS FROM tbl_PO_Draft LIKE '".$column."'");
        if ($exists && mysqli_num_rows($exists) == 0) {
            mysql2_query($alterSql);
        }
    }
}

function pod_required_documents_from_post() {
    $allowed = array('PO Acknowledgment', 'Proforma Invoice', 'Shipping Proforma', 'Delivery Note', 'Packing List', 'ATA106', 'NIS', 'Certificate / Release');
    $selected = isset($_POST['required_documents']) && is_array($_POST['required_documents']) ? $_POST['required_documents'] : array();
    $clean = array();
    foreach ($selected as $doc) {
        if (in_array($doc, $allowed, true)) $clean[] = $doc;
    }
    return implode("\n", $clean);
}

function pod_missing_information($draft) {
    $missing = array();
    if (trim((string)($draft['customer_po_number'] ?? '')) === '' && trim((string)($draft['customer_po_file'] ?? '')) === '') $missing[] = 'customer PO number or scanned customer PO missing';
    $docs = array_filter(array_map('trim', explode("\n", (string)($draft['required_documents'] ?? ''))));
    if (in_array('PO Acknowledgment', $docs, true) && trim((string)($draft['customer_po_file'] ?? '')) === '') $missing[] = 'scanned customer PO file missing for PO acknowledgment';
    if ((int)($draft['release_id'] ?? 0) <= 0) $missing[] = 'release/certification missing';
    if (trim((string)($draft['shipping_address'] ?? '')) === '') $missing[] = 'shipping address missing';
    if (trim((string)($draft['shipping_terms'] ?? '')) === '') $missing[] = 'shipping terms missing';
    if (trim((string)($draft['payment_terms'] ?? '')) === '') $missing[] = 'payment terms missing';
    if ((int)($draft['source_company_id'] ?? 0) <= 0) $missing[] = 'source supplier missing';
    if (trim((string)($draft['source_type'] ?? '')) === '' || (int)($draft['source_id'] ?? 0) <= 0) $missing[] = 'stock/source not selected';
    if ((int)($draft['customer_company_id'] ?? 0) <= 0) $missing[] = 'customer missing';
    return $missing;
}

function pod_format_company_address($address) {
    if (!$address) return '';
    $parts = array();
    foreach (array('title_address', 'Fld_Company_Street', 'Fld_Company_City', 'Fld_Company_State', 'Fld_Company_ZipCode', 'Fld_Company_Country') as $field) {
        if (trim((string)($address[$field] ?? '')) !== '') $parts[] = trim((string)$address[$field]);
    }
    return implode("\n", $parts);
}

function pod_fulfillment_info($draft) {
    $type = strtoupper((string)($draft['source_type'] ?? ''));
    if (strpos($type, 'SQ') !== false) {
        return array('required' => 'YES', 'action' => 'Create Supplier PO Draft', 'message' => 'Supplier PO is required. Supplier, price, condition, delivery and release must come from the selected Supplier Quote.');
    }
    if (strpos($type, 'ACI770') !== false || $type === 'ACI770') {
        return array('required' => 'NO', 'action' => 'Internal fulfilment / shipping docs', 'message' => 'No supplier PO required. Proceed with internal fulfilment and shipping documents.');
    }
    if (strpos($type, 'EXTERNAL') !== false || strpos($type, 'CONSIGN') !== false) {
        return array('required' => 'MAYBE', 'action' => 'Confirm source availability / documents', 'message' => 'Supplier or consignment confirmation may be required before document generation.');
    }
    return array('required' => 'UNKNOWN', 'action' => 'Review source selection', 'message' => 'Source type is not clear. Review selected stock/SQ source before proceeding.');
}

function pod_customer_po_upload_dir() {
    return __DIR__.'/uploads/customer_po';
}

function pod_customer_po_upload_error($code) {
    $errors = array(
        UPLOAD_ERR_INI_SIZE => 'Uploaded file exceeds the server upload limit.',
        UPLOAD_ERR_FORM_SIZE => 'Uploaded file exceeds the form upload limit.',
        UPLOAD_ERR_PARTIAL => 'Uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => '',
        UPLOAD_ERR_NO_TMP_DIR => 'Server temporary upload directory is missing.',
        UPLOAD_ERR_CANT_WRITE => 'Server could not write the uploaded file.',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
    );
    return $errors[$code] ?? 'Unknown upload error.';
}

pod_ensure_workflow_columns();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $basicDraftRes = mysql2_query("SELECT customer_company_id, customer_po_file FROM tbl_PO_Draft WHERE id='".$draftId."' LIMIT 1");
    $basicDraft = $basicDraftRes ? mysqli_fetch_assoc($basicDraftRes) : array();
    $customerCompanyId = (int)($basicDraft['customer_company_id'] ?? 0);

    $uploadedFile = trim((string)($basicDraft['customer_po_file'] ?? ''));
    if (trim((string)($_POST['existing_customer_po_file'] ?? '')) !== '') {
        $uploadedFile = trim((string)$_POST['existing_customer_po_file']);
    }

    if (!empty($_FILES['customer_po_file']['name'])) {
        if (!empty($_FILES['customer_po_file']['error'])) {
            $saveError = pod_customer_po_upload_error((int)$_FILES['customer_po_file']['error']);
        }
        $uploadDir = pod_customer_po_upload_dir();
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }
        if (!is_writable($uploadDir)) {
            @chmod($uploadDir, 0775);
        }
        if (empty($saveError) && (!is_dir($uploadDir) || !is_writable($uploadDir))) {
            $saveError = "Customer PO upload directory is not writable.";
        }
        if (empty($saveError) && is_uploaded_file($_FILES['customer_po_file']['tmp_name'])) {
            $original = basename($_FILES['customer_po_file']['name']);
            $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $original);
            $targetName = 'po_draft_'.$draftId.'_'.date('YmdHis').'_'.$safeName;
            $targetPath = $uploadDir.'/'.$targetName;
            if (move_uploaded_file($_FILES['customer_po_file']['tmp_name'], $targetPath)) {
                $uploadedFile = 'uploads/customer_po/'.$targetName;
            } else {
                $saveError = "Unable to upload customer PO file.";
            }
        }
    }

    $nextStatus = $_POST['status'] ?? 'DRAFT';
    if (isset($_POST['validate_customer_po'])) {
        $hasCustomerPo = trim((string)($_POST['customer_po_number'] ?? '')) !== '' || trim((string)$uploadedFile) !== '';
        $hasAcceptedValues = trim((string)($_POST['accepted_price'] ?? '')) !== '' && trim((string)($_POST['accepted_qty'] ?? '')) !== '';
        $nextStatus = ($hasCustomerPo && $hasAcceptedValues) ? 'ACCEPTED_ORDER' : 'CUSTOMER_PO_RECEIVED';
    }

    $shippingAddress = trim((string)($_POST['shipping_address'] ?? ''));
    $shippingAddressId = (int)($_POST['shipping_address_id'] ?? 0);
    if ($shippingAddressId > 0 && empty($_POST['save_shipping_address'])) {
        $addressRes = mysql2_query("SELECT * FROM tbl_Company_Details WHERE id_tbl_company_Details='".$shippingAddressId."' AND Fld_Company_ID='".$customerCompanyId."' LIMIT 1");
        $addressRow = $addressRes ? mysqli_fetch_assoc($addressRes) : null;
        $shippingAddress = pod_format_company_address($addressRow);
    }
    if ($shippingAddress !== '' && !empty($_POST['save_shipping_address']) && $customerCompanyId > 0) {
        $title = 'PO Draft '.$draftId.' Shipping';
        $insertAddress = "INSERT INTO tbl_Company_Details (
                Fld_Linked_ID, Fld_Company_ID, Company_Old_Id, Fld_Company_Type_ID, Fld_Company_Country,
                Fld_Company_City, Fld_Company_State, Fld_Company_Street, Fld_Company_ZipCode, Fld_Company_Fax,
                Fld_Company_Phone, Fld_Company_Email, Fld_Company_Score, Fld_Company_BAX_Contact, Fld_Remark,
                Fld_VAT_Nbr, Fld_Date_Of_First_Contact, Fld_Company_Address_Type, UTC_timezone, title_address
            ) VALUES (
                0, '".$customerCompanyId."', 0, 0, '', '', '', '".pod_escape($shippingAddress)."', '', '',
                '', '', '', '', 'Saved from PO draft ".$draftId."', '', '".date('Y-m-d')."', 2, '', '".pod_escape($title)."'
            )";
        if (mysql2_query($insertAddress)) {
            $shippingAddressId = mysqli_insert_id($conn);
        }
    }

    $sql = "UPDATE tbl_PO_Draft SET
        po_number='".pod_escape($_POST['po_number'] ?? '')."',
        customer_po_number='".pod_escape($_POST['customer_po_number'] ?? '')."',
        customer_po_file='".pod_escape($uploadedFile)."',
        accepted_price='".pod_escape($_POST['accepted_price'] ?? '')."',
        accepted_qty='".pod_escape($_POST['accepted_qty'] ?? '')."',
        accepted_condition_id='".(int)($_POST['accepted_condition_id'] ?? 0)."',
        payment_terms='".pod_escape($_POST['payment_terms'] ?? '')."',
        shipping_terms='".pod_escape($_POST['shipping_terms'] ?? '')."',
        shipping_address='".pod_escape($shippingAddress)."',
        shipping_address_id=".($shippingAddressId > 0 ? "'".$shippingAddressId."'" : "NULL").",
        required_documents='".pod_escape(pod_required_documents_from_post())."',
        acceptance_notes='".pod_escape($_POST['acceptance_notes'] ?? '')."',
        qty='".pod_escape($_POST['qty'] ?? '')."',
        condition_id='".(int)($_POST['condition_id'] ?? 0)."',
        price='".pod_escape($_POST['price'] ?? '')."',
        currency_id='".(int)($_POST['currency_id'] ?? 0)."',
        release_id='".(int)($_POST['release_id'] ?? 0)."',
        delivery='".pod_escape($_POST['delivery'] ?? '')."',
        remarks='".pod_escape($_POST['remarks'] ?? '')."',
        status='".pod_escape($nextStatus)."'
        WHERE id='".$draftId."'";
    if (empty($saveError) && !mysql2_query($sql)) {
        $saveError = "Unable to save PO draft.";
    } elseif (empty($saveError)) {
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
$missingInfo = pod_missing_information($draft);
$missingText = implode("\n", $missingInfo);
if ((string)($draft['missing_information'] ?? '') !== $missingText) {
    mysql2_query("UPDATE tbl_PO_Draft SET missing_information='".pod_escape($missingText)."' WHERE id='".$draftId."'");
    $draft['missing_information'] = $missingText;
}
$savedDocs = array_filter(array_map('trim', explode("\n", (string)($draft['required_documents'] ?? ''))));
$allDocs = array('PO Acknowledgment', 'Proforma Invoice', 'Shipping Proforma', 'Delivery Note', 'Packing List', 'ATA106', 'NIS', 'Certificate / Release');
$fulfillmentInfo = pod_fulfillment_info($draft);
$addressRes = mysql2_query("SELECT * FROM tbl_Company_Details WHERE Fld_Company_ID='".(int)$draft['customer_company_id']."' ORDER BY id_tbl_company_Details DESC");
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
                <div class="alert alert-info">
                    <p><b>Editable PO Draft</b> is the internal accepted order data prepared from the customer quotation.</p>
                    <p><b>Customer PO</b> is the customer's official purchase order received by email, upload, or manual entry.</p>
                    <p><b>Validate Customer PO</b> confirms the customer order and prepares the document workflow.</p>
                    <p><b>Required Documents</b> is the checklist of documents to prepare after acceptance.</p>
                </div>
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
                    <div class="panel-heading">Fulfillment Source</div>
                    <div class="panel-body">
                        <p><b>Type:</b> <?php echo pod_h($draft['source_type']); ?></p>
                        <p><b>Source ID:</b> <?php echo (int)$draft['source_id']; ?></p>
                        <p><b>Supplier / Source:</b> <?php echo pod_h($draft['source_company_name']); ?></p>
                        <p><b>Supplier PO required:</b> <?php echo pod_h($fulfillmentInfo['required']); ?></p>
                        <p><b>Next recommended action:</b> <?php echo pod_h($fulfillmentInfo['action']); ?></p>
                        <p class="text-muted"><?php echo pod_h($fulfillmentInfo['message']); ?></p>
                        <?php if ($fulfillmentInfo['required'] === 'YES') { ?>
                            <button type="button" class="btn btn-default btn-sm" disabled>Create Supplier PO Draft</button>
                        <?php } elseif ($fulfillmentInfo['required'] === 'NO') { ?>
                            <span class="label label-success">No supplier PO required</span>
                        <?php } elseif ($fulfillmentInfo['required'] === 'MAYBE') { ?>
                            <span class="label label-warning">Supplier/consignment confirmation may be required</span>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <form method="post" enctype="multipart/form-data" class="panel panel-default">
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
                                <?php foreach (array('DRAFT','CUSTOMER_PO_RECEIVED','ACCEPTED_ORDER','DOCS_PENDING','READY_TO_SHIP','SHIPPED','CLOSED','CANCELLED') as $status) { ?>
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

                <hr>
                <h4>Customer PO</h4>
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Customer PO Number</label>
                            <input class="form-control" name="customer_po_number" value="<?php echo pod_h($draft['customer_po_number']); ?>">
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Upload Scanned Customer PO</label>
                            <input type="file" name="customer_po_file" class="form-control">
                            <input type="hidden" name="existing_customer_po_file" value="<?php echo pod_h($draft['customer_po_file']); ?>">
                            <?php if (!empty($draft['customer_po_file'])) { ?>
                                <p class="help-block">
                                    Current attachment:
                                    <a href="<?php echo pod_h($draft['customer_po_file']); ?>" target="_blank"><?php echo pod_h(basename($draft['customer_po_file'])); ?></a>
                                </p>
                            <?php } else { ?>
                                <p class="help-block">No customer PO attachment saved yet.</p>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label>Accepted Price</label>
                            <input class="form-control" name="accepted_price" value="<?php echo pod_h($draft['accepted_price'] ?: $draft['price']); ?>">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label>Accepted Qty</label>
                            <input class="form-control" name="accepted_qty" value="<?php echo pod_h($draft['accepted_qty'] ?: $draft['qty']); ?>">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label>Accepted Condition</label>
                            <select class="form-control" name="accepted_condition_id">
                                <?php
                                $acceptedConditionId = (int)($draft['accepted_condition_id'] ?: $draft['condition_id']);
                                $conditions = mysql2_query("SELECT Fld_Condition_ID, Fld_Condition_Text FROM tbl_Condition ORDER BY Fld_Condition_Text");
                                while ($condition = mysqli_fetch_assoc($conditions)) {
                                    echo "<option value='".(int)$condition['Fld_Condition_ID']."'";
                                    if ($acceptedConditionId === (int)$condition['Fld_Condition_ID']) echo " selected";
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
                            <label>Payment Terms</label>
                            <input class="form-control" name="payment_terms" value="<?php echo pod_h($draft['payment_terms']); ?>">
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Shipping Terms</label>
                            <input class="form-control" name="shipping_terms" value="<?php echo pod_h($draft['shipping_terms']); ?>">
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Shipping Address</label>
                            <select class="form-control" name="shipping_address_id">
                                <option value="">Choose saved address</option>
                                <?php while ($address = mysqli_fetch_assoc($addressRes)) {
                                    $addressText = pod_format_company_address($address);
                                    $selected = ((int)($draft['shipping_address_id'] ?? 0) === (int)$address['id_tbl_company_Details']) ? ' selected' : '';
                                    echo "<option value='".(int)$address['id_tbl_company_Details']."' data-address='".pod_h($addressText)."'".$selected.">".pod_h(str_replace("\n", ' - ', $addressText))."</option>";
                                } ?>
                            </select>
                            <p class="help-block">Choose a saved customer address, or enter a manual address below.</p>
                            <textarea class="form-control" name="shipping_address" rows="3"><?php echo pod_h($draft['shipping_address']); ?></textarea>
                            <label class="checkbox-inline" style="margin-top:6px">
                                <input type="checkbox" name="save_shipping_address" value="1"> Save this address to customer profile
                            </label>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea class="form-control" name="acceptance_notes" rows="3"><?php echo pod_h($draft['acceptance_notes']); ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Required Documents</label><br>
                    <?php foreach ($allDocs as $doc) { ?>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="required_documents[]" value="<?php echo pod_h($doc); ?>" <?php if (in_array($doc, $savedDocs, true)) echo 'checked'; ?>>
                            <?php echo pod_h($doc); ?>
                        </label>
                    <?php } ?>
                </div>

                <div class="panel panel-warning">
                    <div class="panel-heading">Required information before documents</div>
                    <div class="panel-body">
                        <?php if (empty($missingInfo)) { ?>
                            <p class="text-success">No blocking information is currently missing for document preparation.</p>
                        <?php } else { ?>
                            <ul>
                                <?php foreach ($missingInfo as $missing) { ?>
                                    <li><?php echo pod_h($missing); ?></li>
                                <?php } ?>
                            </ul>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <button type="submit" class="btn btn-danger"><i class="fa fa-save"></i> Save PO Draft</button>
                <button type="submit" name="validate_customer_po" value="1" class="btn btn-primary"><i class="fa fa-check"></i> Validate Customer PO</button>
                <a class="btn btn-default" href="modif_quotations.php?ID=<?php echo (int)$draft['quotation_id']; ?>&mode=clean">Back to Quote</a>
            </div>
        </form>
    </div>
</div>
<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="../vendor/metisMenu/metisMenu.min.js"></script>
<script src="../dist/js/sb-admin-2.js"></script>
<script>
$(function(){
    $('select[name="shipping_address_id"]').on('change', function(){
        var address = $(this).find('option:selected').data('address') || '';
        if (address) {
            $('textarea[name="shipping_address"]').val(address);
        }
    });
});
</script>
</body>
</html>
