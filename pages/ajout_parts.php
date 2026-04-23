<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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

    <title>AeroCanada-Industries.com</title>

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

</head>

<body>

<div id="wrapper">
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
        <?php include "top_menu.php"; ?>  <!-- barre rouge avec SON burger -->
        <?php if(isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
    </nav>
    <?php include "after_nav.php"; ?>

    <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">

        <!-- TOUT LE CONTENU DE LA PAGE DOIT ÊTRE À L’INTÉRIEUR DE CE DIV -->

        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">ADD A PART</h1>
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <!-- /.row -->

        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default" style="background-color: #ddd;">
                    <div class="panel-heading" style="background-color:#A7142A">
                    </div>

                    <form id="formajoutpart" role="form" method="post" action="valid_ajout_part.php">
                        <!--
                        //*****tbl_Parts*************  
                        // Fld_Part_ID  Fld_Part_Nbr  Fld_Part_Desc  Fld_Part_MFG  Fld_Part_MFG_Old  
                        // Fld_AC_ID  Fld_Old_LP  Fld_Part_List_Price  Fld_Part_Price_Currency_ID  
                        // Fld_Part_LP_Date  Fld_Remark status alt_pn Fld_Add_PN_Date aci_contact_entry ata_chapter
                        -->

                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label>PN</label>
                                        <input class="form-control" name="Fld_Part_Nbr">
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label>Part description</label>
                                        <input class="form-control" name="Fld_Part_Desc">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>ALT PN</label>
                                        <textarea class="form-control" rows="3" name="alt_pn"></textarea>
                                    </div>
                                </div>
                            </div>
                            <!-- /.row -->

                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label>MFG/OEM</label>
                                        <a data-toggle="modal" data-target="#myModal" style="cursor: pointer;">
                                            <i style="margin-left:10px;top: 4px;font-size:23px;" class="fa fa-plus-circle "></i>
                                        </a><br>
                                        <input type="text" name="Fld_Part_MFG" size="30" class="Fld_Part_MFG" placeholder="Please Enter company">
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label>OEM LEAD TIME</label><br>
                                        <input type="text" name="oem_lead_time" class="form-control">
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label>List Price</label>
                                        <input class="form-control" name="Fld_Part_List_Price">
                                    </div>
                                </div>
                                <div class="col-lg-1">
                                    <div class="form-group">
                                        <label>Currency</label>
                                        <select class="form-control" name="Fld_Part_Price_Currency_ID">
                                            <?php
                                            //recuperation du nom de la currency    
                                            // Fld_Currency_ID    Fld_Currency_Text
                                            $sqldiv="SELECT * FROM tbl_Currency";
                                            $reqemp = mysql2_query($sqldiv);
                                            while($datadiv = mysqli_fetch_array($reqemp))
                                            {
                                                echo "<option value='".$datadiv['Fld_Currency_ID']."'>".$datadiv['Fld_Currency_Text']."</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label>LP DATE</label>
                                        <input class="form-control" name="Fld_Part_LP_Date">
                                    </div>
                                </div>
                            </div>
                            <!-- /.row -->

                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label>Aircraft</label>
                                        <select class="form-control" name="Fld_AC_ID">
                                            <option value=""></option>
                                            <?php
                                            // Fld_AC_ID  Fld_AC_Model  Fld_AC_Series  Fld_AC_Manufacturer  Fld_AC_Engine_Model  Fld_AC_Engine_Series
                                            $sqldiv="SELECT Distinct(Fld_AC_Model),Fld_AC_ID FROM tbl_Aircraft order by Fld_AC_Model";
                                            $reqemp = mysql2_query($sqldiv);
                                            while($datadiv = mysqli_fetch_array($reqemp))
                                            {
                                                echo "<option value='".$datadiv['Fld_AC_ID']."'>".$datadiv['Fld_AC_Model']."</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label>ATA CHAPTER</label>
                                        <input class="form-control" name="ata_chapter" id="ata_chapter">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>Remark</label>
                                        <textarea class="form-control" rows="3" name="Fld_Remark"></textarea>
                                    </div>
                                </div>
                            </div>
                            <!-- /.row -->

                            <?php $today = date("Y-m-d"); ?>
                            <?php $yeartoday = date("Y"); ?>
                            <input type="hidden" name="Fld_Part_LP_Date" value="<?php echo $yeartoday;?>">
                            <input type="hidden" name="Fld_Add_PN_Date" value="<?php echo $today;?>">
                            <input type="hidden" name="aci_contact_entry" value="<?php echo $_SESSION['id_utilisateur'];?>">
                            
                            <button type="submit" class="btn btn-default">Validate</button>

                        </div>
                        <!-- /.panel-body -->

                    </form>
                </div>
                <!-- /.panel -->
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <!-- /.row -->

    </div>
    <!-- /#page-wrapper ou page-wrapper2 -->

</div>
<!-- /#wrapper -->

<!--*************************************************************************************************************************************-->
<!--*********************************************** POPUP ADD A COMPANY *****************************************************************-->

<script type="text/javascript">
    $('#myModal').on('hidden.bs.modal', function (e) {
        //get value from #myPopupInput and set the value to #myMainPageInput
    });
</script>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #A7142A;">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel" style="color:#FFFFFF;font-weight: bold;">ADD A COMPANY</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="myPopupInputcompanyname" class="col-form-label">COMPANY NAME:</label>
                            <input type="text" class="form-control" id="myPopupInputcompanyname" />
                        </div>
                        <div class="col-md-4">
                        </div>
                    </div>  
                </div>
            </div>

            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="myPopupInputcompanycontactname" class="col-form-label">CONTACT NAME:</label>
                            <input type="text" class="form-control" id="myPopupInputcompanycontactname" />
                        </div>
                        <div class="col-md-4">
                            <label for="myPopupInputcompanycontactemail" class="col-form-label">E-MAIL:</label>
                            <input type="text" class="form-control" id="myPopupInputcompanycontactemail" />
                        </div>
                    </div>  
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>

<!--******************************************* FIN POPUP ADD A COMPANY ******************************************************************-->
<!--*************************************************************************************************************************************-->

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

<!-- Page-Level Demo Scripts - Tables - Use for reference -->
<script type="text/javascript">
    $(document).ready(function() {
        $('#dataTables-example').DataTable({
            responsive: true
        });
    });
</script>

<!-- Ajout pour autocompletion Roy -->
<script src="js/typeahead.js"></script>

<style>
    h1 {
        font-size: 20px;
        color: #111;
    }

    .content {
        width: 80%;
        margin: 0 auto;
        margin-top: 50px;
    }

    .tt-hint,
    .Fld_Part_MFG {
        border: 1px solid #CCCCCC;
        /* border-radius: 8px 8px 8px 8px; */
        font-size: 24px;
        /* height: 45px; */
        line-height: 30px;
        outline: medium none;
        padding: 8px 12px;
        width: 100%;
    }

    .tt-dropdown-menu {
        width: 400px;
        margin-top: 5px;
        padding: 8px 12px;
        background-color: #fff;
        border: 1px solid #ccc;
        border-radius: 8px 8px 8px 8px;
        font-size: 18px;
        color: #111;
        background-color: #F1F1F1;
    }
</style>

<script>
    $(document).ready(function() {

        $('input.Fld_Part_MFG').typeahead({
            name: 'Fld_Company_Name',
            id: 'Fld_Company_ID',
            remote: 'list-company.php?query=%QUERY'
        });

        //****************************************ADD A COMPANY POPUP******************************************
        $('#myModal').on('click', '.btn-primary', function(){
            var value  = $('#myPopupInputcompanyname').val();
            var value2 = $('#myPopupInputcompanycontactname').val();  
            var value3 = $('#myPopupInputcompanycontactemail').val();

            // enregistrer la nouvelle company dans la base 
            $.ajax({
                url: 'add_company_from_popup.php',
                data: {companyname: value, contactname: value2, contactemail: value3},
                type: 'get',
                success: function(output) {
                    // alert(output);
                }
            });

            $('#myModal').modal('hide');
        });
        //************************************END ADD A COMPANY POPUP*****************************************
    });
</script>

</body>
</html>

<?php
}
else {
    echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=".$_SERVER['REQUEST_URI']."\">";
}
?>
