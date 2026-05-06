<?php
session_start();
// valid_add_multi_pn_rfq.php
include_once "conf.php";
include_once "page_titles.php";

// optionnel : à commenter une fois que tout marche
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

if ($_SESSION['conectroy'] == "parfait") {

    // ==========================================================
    // 1) TRAITEMENT DE L'AJOUT DE PN (POST)
    // ==========================================================
    if (
        !empty($_POST['action']) &&
        $_POST['action'] === 'add_multi_pn_rfq' &&
        !empty($_POST['pnid'])              // Ne crée une ligne que si un PN est saisi
    ) {
        require('../classes/rfq.class.php');
        $objet  = new rfq();
        $donnee = $objet->add_rfq();

        // On récupère l'ID de la RFQ pour la redirection
        $rfq_id = !empty($_POST['Fld_RFQ_ID'])
            ? $_POST['Fld_RFQ_ID']
            : date("Y-m-d-His");

        // *** POST → REDIRECT → GET ***
        // Pour éviter que F5 / Refresh ne ré-exécute add_rfq()
        header("Location: valid_add_multi_pn_rfq.php?Fld_RFQ_ID=" . urlencode($rfq_id));
        exit;
    }

    // ==========================================================
    // 2) DÉTERMINER LE Fld_RFQ_ID COURANT (GET ou POST ou nouveau)
    // ==========================================================
    if (!empty($_POST['Fld_RFQ_ID'])) {
        $rfq_id = $_POST['Fld_RFQ_ID'];
    } elseif (!empty($_GET['Fld_RFQ_ID'])) {
        $rfq_id = $_GET['Fld_RFQ_ID'];
    } else {
        // Cas très rare : nouvelle RFQ sans ID transmis
        $rfq_id = date("Y-m-d-His");
    }

    // ==========================================================
    // 3) RÉCUPÉRER LE CLIENT + CONTACT + DATE À PARTIR DE tbl_RFQ_1
    // ==========================================================
    $company_name   = '';
    $company_id     = 0;
    $contact_name   = '';
    $contact_id     = 0;
    $rfq_date       = '';

    $rfq_id_sql = addslashes($rfq_id);

    $sqlHeader = "
        SELECT
            r.Fld_Customer_ID,
            r.id_company_contact,
            r.date,
            r.Fld_RFQ_Type_ID,
            c.Fld_Company_Name,
            cc.Fld_Contact_Name
        FROM tbl_RFQ_1 r
        LEFT JOIN tb_company c 
               ON c.Fld_Company_ID = r.Fld_Customer_ID
        LEFT JOIN tb_company_contact cc
               ON cc.id_company_contact = r.id_company_contact
        WHERE r.Fld_RFQ_ID = '" . $rfq_id_sql . "'
        LIMIT 1
    ";
    $reqHeader = mysql2_query($sqlHeader);
    $rfq_type_id_selected = '';
    if ($reqHeader && $header = mysqli_fetch_array($reqHeader)) {
        $company_id   = (int)$header['Fld_Customer_ID'];
        $contact_id   = (int)$header['id_company_contact'];
        $company_name = $header['Fld_Company_Name'];
        $contact_name = $header['Fld_Contact_Name'];
        $rfq_date     = $header['date'];
        $rfq_type_id_selected = $header['Fld_RFQ_Type_ID'] ?? '';
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
    <link href="../dist/css/aci-overrides.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
	
    <!--CSS rating ajoute par roy-->
    <link href="rating.css" rel="stylesheet">
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
                <div class="col-lg-10">
                    <!-- header vide -->
                </div>
            </div>

            <div class="row">
                <div class="col-lg-10">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            ADD RFQ
                        </div>

                        <form method="post" name="Form1">
                            <input type="hidden" name="action" value="add_multi_pn_rfq">
                            <div class="panel-body">
                                <div class="row">
                                    <!-- RFQ ID -->
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>RFQ ID</label>
                                            <input 
    class="form-control"
    value="<?php echo htmlspecialchars($rfq_id); ?>" 
    disabled
    style="background:#eee; cursor:not-allowed;"
>
<input type="hidden" name="Fld_RFQ_ID" value="<?php echo htmlspecialchars($rfq_id); ?>">

                                        </div>
                                    </div>
                                    <!-- DATE -->
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>DATE</label><br>
                                            <?php echo htmlspecialchars($rfq_date); ?>
                                            <input type="hidden" class="form-control" name="RFQ_DATE" value="<?php echo htmlspecialchars($rfq_date); ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-6"></div>
                                </div>

                                <div class="row">
                                    <!-- RFQ TYPE -->
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>RFQ TYPE</label>
                                            <select class="form-control" name="Fld_RFQ_Type_ID">
                                                <option></option>
                                                <?php
                                                $sqlrfqt="SELECT * FROM tbl_RFQ_Type ORDER BY Fld_RFQ_Type_Text";
                                                $reqrfqt = mysql2_query($sqlrfqt);
                                                while($datarfqt = mysqli_fetch_array($reqrfqt)){
                                                    echo "<option value='".$datarfqt['Fld_RFQ_Type_ID']."'";
                                                    if ((string)$datarfqt['Fld_RFQ_Type_ID'] === (string)($_POST['Fld_RFQ_Type_ID'] ?? $rfq_type_id_selected)) echo " selected";
                                                    echo ">".$datarfqt['Fld_RFQ_Type_Text']."</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <!-- PRIORITY -->
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>PRIORITY</label>
                                            <select class="form-control" name="Fld_Priority_ID">
                                                <?php
                                                $sqlPriority="SELECT * FROM tbl_Priority";
                                                $reqPriority = mysql2_query($sqlPriority);
                                                while($dataPriority = mysqli_fetch_array($reqPriority)){
                                                    echo "<option value='".$dataPriority['Fld_Priority_ID']."'";
                                                    if (isset($_POST['Fld_Priority_ID']) && $dataPriority['Fld_Priority_ID']==$_POST['Fld_Priority_ID']) echo " selected";
                                                    echo ">".$dataPriority['Fld_Priority_Text']."</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <!-- SALES CONTACT -->
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>SALES CONTACT</label>
                                            <select class="form-control" name="Employee_ID">
                                                <?php
                                                $sqlemp="SELECT DISTINCT(Employee_Name),Employee_ID FROM tbl_Employee";
                                                $reqemp = mysql2_query($sqlemp);
                                                while($dataemp = mysqli_fetch_array($reqemp)){
                                                    echo "<option value='".$dataemp['Employee_ID']."'";
                                                    if ($dataemp['Employee_ID']==$_SESSION['id_utilisateur']) echo " selected";
                                                    echo ">".$dataemp['Employee_Name']."</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- CLIENT + CONTACT + TERMS -->
                                <div class="row">
                                   <!-- CUSTOMER'S NAME -->
									<div class="col-lg-3">
									    <div class="form-group">
									        <label>CUSTOMER'S NAME</label><br>
									        <?php
									        // on part de ce qui vient de la RFQ (lecture en haut du fichier)
									        $companyidrecup       = $company_id;
									        $company_name_to_show = $company_name;

									        // si POST, on écrase la valeur
									        if (!empty($_POST['companyid'])) {
									            $raw   = $_POST['companyid'];
									            $parts = explode(",", $raw);

									            // "Nom,1234"
									            if (count($parts) > 1 && is_numeric($parts[1])) {
									                $companyidrecup = (int)$parts[1];
									            }
									            // "1234"
									            elseif (is_numeric($raw)) {
									                $companyidrecup = (int)$raw;
									            }
									        }

									        // si on a un ID valide, on vérifie le nom en base
									        if ($companyidrecup > 0) {
									            $sqlrn = "SELECT Fld_Company_Name 
									                      FROM tb_company 
									                      WHERE Fld_Company_ID=".(int)$companyidrecup;
									            $reqrn = mysql2_query($sqlrn);
									            if ($reqrn && $datarn = mysqli_fetch_array($reqrn)) {
									                $company_name_to_show = $datarn['Fld_Company_Name'];
									            }
									        }

									        // valeur à afficher dans le champ texte (typeahead)
									        if (!empty($_POST['companyid'])) {
									            $input_company_value = $_POST['companyid'];
									        } elseif ($companyidrecup > 0 && $company_name_to_show !== '') {
									            // format "ID,Nom" — matches list-company.php typeahead
									            $input_company_value = $companyidrecup . ',' . $company_name_to_show;
									        } else {
									            $input_company_value = '';
									        }
									        ?>

									        <input
									            type="text"
									            class="form-control companyid"
									            id="companyid"
									            name="companyid"
									            value="<?php echo htmlspecialchars($input_company_value); ?>"
									            placeholder="Type company name"
									            onblur="majtarea('divcontactname');"
									        >
									    </div>
									</div>


                                    <!-- CONTACT NAME -->
                                    <div class="col-lg-3" id="bloccontactname">
                                        <div class="form-group" id="divcontactname">
                                            <label>CONTACT NAME</label><br>
                                            <?php
                                            $contact_name_to_show = $contact_name;

                                            if (!empty($_POST['id_company_contact'])) {
                                                $id_contact_post = (int)$_POST['id_company_contact'];
                                                $sqlcc = "SELECT Fld_Contact_Name 
                                                          FROM tb_company_contact 
                                                          WHERE id_company_contact=".$id_contact_post;
                                                $reqcc = mysql2_query($sqlcc);
                                                if ($reqcc && $datacc = mysqli_fetch_array($reqcc)) {
                                                    $contact_name_to_show = $datacc['Fld_Contact_Name'];
                                                    $contact_id           = $id_contact_post;
                                                }
                                            }

                                            echo htmlspecialchars($contact_name_to_show);
                                            ?>
                                            <input type="hidden" name="id_company_contact" value="<?php echo (int)$contact_id; ?>">
                                        </div>
                                    </div>

                                    <!-- TERMS -->
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>TERMS</label>
                                            <select class="form-control" name="Fld_Payment_Term_ID">
                                                <?php
                                                $sqlptid="SELECT * FROM tbl_Payment";
                                                $reqptid = mysql2_query($sqlptid);
                                                while($dataptid = mysqli_fetch_array($reqptid)){
                                                    echo "<option value='".$dataptid['Fld_Payment_Term_ID']."'";
                                                    if (isset($_POST['Fld_Payment_Term_ID']) && $dataptid['Fld_Payment_Term_ID']==$_POST['Fld_Payment_Term_ID']) echo " selected";
                                                    echo ">".$dataptid['Fld_Payment_Text']."</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-3"></div>
                                </div>

                                <!-- PN / DESC / QTY / CONDITION -->
                                <div class="row">
                                    <!-- PN -->
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>PN</label>
                                            <a data-toggle="modal" data-target="#myModal" style="cursor: pointer;">
                                                <i style="margin-left:10px; position:relative; top:4px; font-size:23px;" class="fa fa-plus-circle"></i>
                                            </a>
                                            <br>
                                            <input
                                                type="text"
                                                name="pnid"
                                                id="pnid"
                                                class="pnid form-control"
                                                placeholder="Please Enter P/N"
                                                required
                                            >
                                        </div>
                                    </div>

                                    <!-- DESCRIPTION -->
                                    <div class="col-lg-3" id="blocdescription">
                                        <div class="form-group" id="divdescription">
                                            <label>DESCRIPTION</label>
                                            <input
                                                class="form-control"
                                                name="description"
                                                id="description"
                                                onclick="javascript:descfrompn();"
                                            >
                                        </div>
                                    </div>

                                    <!-- QTY -->
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label>QTY</label>
                                            <input
                                                class="form-control"
                                                name="Fld_Qty"
                                                id="Fld_Qty"
                                                value="1"
                                            >
                                        </div>
                                    </div>

                                    <!-- CONDITION -->
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label>CONDITION</label>
                                            <select class="form-control" name="Fld_Condition_ID" required>
                                                <option value="">Select</option>
                                                <?php
                                                $sqlc = "SELECT * FROM tbl_Condition ORDER BY Fld_Condition_Text";
                                                $reqc = mysql2_query($sqlc);
                                                while($datac = mysqli_fetch_array($reqc)){
                                                    echo "<option value='".$datac['Fld_Condition_ID']."'>".$datac['Fld_Condition_Text']."</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- ADD PN BUTTON -->
                                    <div class="col-lg-2" style="padding-top:22px;">
                                        <button type="submit" class="btn btn-danger btn-block">
                                            ADD PN TO RFQ
                                        </button>
                                    </div>
                                </div>

                                <!-- REMARKS -->
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>REMARKS</label>
                                            <textarea class="form-control" rows="3" name="Fld_Remark_rfq" id="Fld_Remark_rfq"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-lg-6"></div>
                                </div>

                                <!-- BOUTONS -->
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group" align="right">
                                            <input type="button" value="OPEN RFQs" name="button1" onclick="return OnButton1();">
                                        </div>
                                    </div>	
                                    <div class="col-lg-4">
                                        <div class="form-group" align="right">
                                            <input type="button" value="ADD Multiples RFQS" name="button2" onclick="return OnButton2();">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group" align="right">
                                            <input type="button" value="FINISH THIS RFQ" name="button3" onclick="return OnButton3();">
                                        </div>
                                    </div>	
                                </div>						   
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php
            // liste des PN déjà dans cette RFQ
            $sqlPriority = "
                SELECT *
                FROM tbl_RFQ_1
                WHERE Fld_RFQ_ID = '" . $rfq_id_sql . "'
                ORDER BY ID ASC
            ";
            $reqPriority    = mysql2_query($sqlPriority);
            $num_rows_rfq1  = ($reqPriority ? mysqli_num_rows($reqPriority) : 0);

            if ($num_rows_rfq1 > 0) {
            ?>
            <div class="row">
                <div class="col-lg-10">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <span style="color:white;">P/N FOR RFQ ID <?php echo htmlspecialchars($rfq_id); ?></span>
                        </div>
                        <div class="panel-body">
                            <table width="100%" class="table table-striped table-bordered table-hover" id="dataTablespnrfq">
                                <thead>
                                    <tr>
                                        <th>PN</th>
                                        <th>DESCRIPTION</th>
                                        <th>QTY</th>
                                        <th>CONDITION</th>
                                        <th>REMARKS</th>
                                        <th style="min-width:320px">ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $z=0;
                                    while($dataPriority = mysqli_fetch_array($reqPriority)) {
                                        $z++;
                                        $lineId = (int)$dataPriority['ID'];
                                        $linePn = htmlspecialchars($dataPriority['pn_rfq'] ?? '');
                                        $lineDesc = htmlspecialchars($dataPriority['description_rfq'] ?? '');
                                        $lineQty = htmlspecialchars($dataPriority['Fld_Qty'] ?? '');
                                        $lineCondId = (int)$dataPriority['Fld_Condition_ID'];
                                        $linePartId = (int)$dataPriority['Fld_Part_ID'];
                                        $lineObs = htmlspecialchars($dataPriority['Fld_Observation'] ?? '');

                                        $sqlrc  = "SELECT Fld_Condition_Text FROM tbl_Condition WHERE Fld_Condition_ID='".$lineCondId."'";
                                        $reqrc  = mysql2_query($sqlrc);
                                        $datarc = ($reqrc ? mysqli_fetch_array($reqrc) : array('Fld_Condition_Text' => ''));
                                        $condText = htmlspecialchars($datarc['Fld_Condition_Text'] ?? '');

                                        // Count existing supplier quotes for this line
                                        $sqlSqCount = "SELECT COUNT(*) AS c FROM tbl_RFQ_2 WHERE Fld_RFQ_ID='".addslashes($rfq_id)."' AND Fld_Part_ID='".$linePartId."'";
                                        $reqSqCount = mysql2_query($sqlSqCount);
                                        $sqCount = ($reqSqCount && $rowSq = mysqli_fetch_assoc($reqSqCount)) ? (int)$rowSq['c'] : 0;

                                        echo "<tr id=\"row_".$lineId."\">";
                                        echo "<td><strong>".$linePn."</strong></td>";
                                        echo "<td>".$lineDesc."</td>";
                                        echo "<td>".$lineQty."</td>";
                                        echo "<td>".$condText."</td>";
                                        echo "<td>".$lineObs."</td>";
                                        echo "<td>";
                                        // Action buttons
                                        $addSqUrl = "add_suppliers_quote.php?Fld_RFQ_ID=".urlencode($rfq_id)
                                            ."&id_tbl_rfq1=".$lineId
                                            ."&Fld_Part_ID=".$linePartId
                                            ."&pn_rfq=".urlencode($dataPriority['pn_rfq'] ?? '')
                                            ."&description_rfq=".urlencode($dataPriority['description_rfq'] ?? '')
                                            ."&Fld_Qty=".urlencode($dataPriority['Fld_Qty'] ?? '')
                                            ."&Fld_Condition_ID=".$lineCondId;

                                        echo "<a href='".$addSqUrl."' class='btn btn-xs btn-primary' title='Add Supplier Quote'><i class='fa fa-plus'></i> Add SQ</a> ";
                                        echo "<button type='button' class='btn btn-xs btn-info js-view-sq' data-line-id='".$lineId."' data-rfq='".htmlspecialchars($rfq_id)."' data-part-id='".$linePartId."' data-pn='".$linePn."' title='View Supplier Quotes'><i class='fa fa-list'></i> SQ";
                                        if ($sqCount > 0) echo " <span class='badge'>".$sqCount."</span>";
                                        echo "</button> ";
                                        echo "<button type='button' class='btn btn-xs btn-default js-view-stock' data-line-id='".$lineId."' data-part-id='".$linePartId."' data-pn='".$linePn."' title='View Stock'><i class='fa fa-database'></i> Stock</button> ";
                                        echo "<a href='javascript:sup_pn_rfq(".$lineId.",".$z.")' onclick=\"return confirm('Delete this PN line?');\" class='btn btn-xs btn-danger' title='Delete'><i class='fa fa-trash'></i></a>";
                                        echo "<div style='display:none; margin-top:8px; min-width:720px;' id='sq_detail_".$lineId."'><div id='sq_content_".$lineId."'></div></div>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            } else {
                echo "<meta http-equiv=\"refresh\" content=\"0; url=add_rfq.php\">";
            }
            ?>
			
        </div> <!-- /#page-wrapper -->

    </div> <!-- /#wrapper -->

    <!-- POPUP ADD PN -->
    <script type="text/javascript">
    $('#myModal').on('hidden.bs.modal', function (e) {
        //get value from #myPopupInput and set the value to #myMainPageInput
    });
    </script>
	
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="myModalLabel">ADD PN</h4>
                </div>
                <div class="modal-body">
                    PN:
                    <input type="text" class="form-control" id="myPopupInputpn" />
                </div>
			
                <div class="modal-body">
                    DESCRIPTION:
                    <input type="text" class="form-control" id="myPopupInputdescription" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="../vendor/metisMenu/metisMenu.min.js"></script>
    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
    <script src="../vendor/datatables-responsive/dataTables.responsive.js"></script>
    <script src="../dist/js/sb-admin-2.js"></script>
    <script type="text/javascript" src="../js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
    <script type="text/javascript" src="../js/locales/bootstrap-datetimepicker.fr.js" charset="UTF-8"></script>

    <script type="text/javascript">
    $(document).ready(function() {
        $('#dataTablespnrfq').DataTable({
            responsive: true,
            paging: false,
            searching: false,
            info: false,
            ordering: false
        });

        // View Supplier Quotes for a PN line
        $(document).on('click', '.js-view-sq', function(){
          var btn = $(this);
          var rfq = btn.data('rfq');
          var partId = btn.data('part-id');
          var pn = btn.data('pn');
          var lineId = btn.data('line-id');
          var detailBox = $('#sq_detail_' + lineId);
          console.log('View SQ click', {rfq_id: rfq, line_id: lineId, part_id: partId, pn: pn, target: detailBox.length});

          if (detailBox.is(':visible') && detailBox.data('source') === 'sq') {
            detailBox.hide();
            return;
          }
          var contentDiv = $('#sq_content_' + lineId);
          contentDiv.html('<p><i class="fa fa-spinner fa-spin"></i> Loading...</p>');
          detailBox.data('source', 'sq').show();

          $.get('rfq_line_sq.php', {rfq_id: rfq, line_id: lineId, part_id: partId, pn: pn}, function(html){
            contentDiv.html(html);
          }).fail(function(xhr){ console.log('View SQ ajax failed', xhr.status, xhr.responseText); contentDiv.html('<p class="text-danger">Error loading data.</p>'); });
        });

        // View Stock for a PN
        $(document).on('click', '.js-view-stock', function(){
          var btn = $(this);
          var partId = btn.data('part-id');
          var pn = btn.data('pn');
          var lineId = btn.data('line-id');
          var detailBox = $('#sq_detail_' + lineId);
          console.log('View Stock click', {line_id: lineId, part_id: partId, pn: pn, target: detailBox.length});

          if (detailBox.is(':visible') && detailBox.data('source') === 'stock') {
            detailBox.hide();
            return;
          }
          var contentDiv = $('#sq_content_' + lineId);
          contentDiv.html('<p><i class="fa fa-spinner fa-spin"></i> Loading stock...</p>');
          detailBox.data('source', 'stock').show();

          $.get('rfq_line_stock.php', {line_id: lineId, part_id: partId, pn: pn}, function(html){
            contentDiv.html(html);
          }).fail(function(xhr){ console.log('View Stock ajax failed', xhr.status, xhr.responseText); contentDiv.html('<p class="text-danger">Error loading stock.</p>'); });
        });

        $(document).on('click', '.js-use-sq-source', function(){
          var btn = $(this);
          var msg = 'Selected SQ source: ' + (btn.data('supplier') || '') + ' ' + (btn.data('price') || '') + ' ' + (btn.data('currency') || '');
          btn.closest('div[id^="sq_content_"]').find('.js-source-choice').remove();
          btn.closest('div[id^="sq_content_"]').prepend('<div class="alert alert-success js-source-choice" style="margin:8px 0">'+msg+'</div>');
        });

        $(document).on('click', '.js-use-stock-source', function(){
          var btn = $(this);
          var msg = 'Selected stock source: ' + (btn.data('pn') || '') + ' ' + (btn.data('condition') || '') + ' ' + (btn.data('location') || '');
          btn.closest('div[id^="sq_content_"]').find('.js-source-choice').remove();
          btn.closest('div[id^="sq_content_"]').prepend('<div class="alert alert-success js-source-choice" style="margin:8px 0">'+msg+'</div>');
        });

        // POPUP ADD PN
        $('#myModal').on('click', '.btn-primary', function(){
            var value  = $('#myPopupInputpn').val();
            var value2 = $('#myPopupInputdescription').val();
            $.ajax({
                url: 'add_pn.php',
                data: {pn: value, desc:value2},
                type: 'get',
                success: function(output) {
                    // optionnel : console.log(output);
                }
            });
            $('#myModal').modal('hide');
        });
    });

    // Ajout nom contact à partir du nom de la société
    function majtarea(id)
    {
        var bloccontactname=document.getElementById('bloccontactname');
        var companyidval=document.getElementById('companyid').value;

        bloccontactname.style.display='inline';

        var xhr=null;
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        } else if (window.ActiveXObject) {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }

        xhr.open("POST", "contactnamefromcompany-rfq.php?id="+companyidval, true);
        xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() { up_contact_name(xhr,id); };
        xhr.send("id="+id);
    }

    function up_contact_name(xhr,id)
    {
        if (xhr.readyState==4)
        {
            document.getElementById('divcontactname').innerHTML='<div id="'+id+'" align="center">';
            var resp2 = xhr.responseText;
            document.getElementById('divcontactname').innerHTML+=resp2;
            document.getElementById('divcontactname').innerHTML+='</div>';
        }
    }

    // Récupération Description à partir du P/N
    function descfrompn(id)
    {
        var blocdescription=document.getElementById('blocdescription');
        var pnid=document.getElementById('pnid').value;

        blocdescription.style.display='inline';

        var xhr=null;

        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        } else if (window.ActiveXObject) {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }

        xhr.open("POST", "descriptionfrompn.php?id="+pnid, true);
        xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() { up_descfrompn(xhr,id); };
        xhr.send("id="+id);
    }

    function up_descfrompn(xhr,id)
    {
        if (xhr.readyState==4)
        {
            document.getElementById('divdescription').innerHTML='<div id="'+id+'" align="center">';
            var resp2 = xhr.responseText;
            document.getElementById('divdescription').innerHTML+=resp2;
            document.getElementById('divdescription').innerHTML+='</div>';
        }
    }

    // DIFFERENTES ACTIONS SUR BOUTONS
    function OnButton1()
    {
        document.Form1.action = "rfq-list.php";
        document.Form1.target = "_self";
        document.Form1.submit();
        return true;
    }

    function OnButton2()
    {
        document.Form1.action = "valid_add_multi_pn_rfq.php";
        document.Form1.target = "_self";
        document.Form1.submit();
        return true;
    }
    function OnButton3()
    {
        document.Form1.action = "rfq-list.php";
        document.Form1.target = "_self";
        document.Form1.submit();
        return true;
    }

    // DEL PN FROM RFQ
    function sup_pn_rfq(id,nbligne){
        if (id > 0) {
            $('#dataTablespnrfq tr[id="row_' + id + '"] td').css({
                'backgroundImage': 'none',
                'backgroundColor': 'white'
            });
            $('#dataTablespnrfq tr[id="row_' + id + '"] td').animate({
                'backgroundColor': '#ff8888',
                'color': '#941010'
            }, 1000);
            $.get('del_pn_rfq.php', {
                idsup:id
            }, function(data){
                $('#dataTablespnrfq tr[id="row_' + id + '"] td').fadeTo("slow", 0, function(){
                    $(this).hide();
                });
            });

            if (document.getElementById("Fld_Division_Text"+nbligne)) {
                document.getElementById("Fld_Division_Text"+nbligne).value = '0';
            }
        }
    }
    </script>

    <!-- Typeahead -->
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
            border-radius: 8px;
            font-size: 18px;
            color: #111;
            background-color: #F1F1F1;
        }
    </style>
    <script>
    $(function(){
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
            // Quand une société est choisie dans l'autocomplétion, on recharge la liste des contacts
	    $('input.companyid').on('blur', function () {
	        majtarea('divcontactname');
	    });
	        // Quand on quitte le champ PN (tab, clic ailleurs) → on va chercher la description
   		$('#pnid').on('blur', function () {
        	descfrompn('divdescription');
    });

    // Optionnel : si tu tapes le PN et fais "Enter" dans le champ PN
    	$('#pnid').on('keypress', function (e) {
        if (e.which === 13) {          // touche Enter
            e.preventDefault();        // évite d'envoyer tout le formulaire
            descfrompn('divdescription');
        }
    });

    });
    </script>

</body>
</html>
<?php
} else {
    echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php\">";
}
?>
