<?php
//company_by_group.php
session_start();
include_once "conf.php";
include_once "page_titles.php";

if ($_SESSION['conectroy'] == "parfait") {

    require('../classes/company.class.php');

    // (optionnel pour debug)
    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);
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

    <!-- CSS rating ajouté par Roy -->
    <link href="rating.css" rel="stylesheet">
    <!-- Fin CSS rating -->

</head>

<body>

<div id="wrapper">

    <!-- NAVBAR FIXE STANDARD (une seule fois !) -->
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
        <?php include "top_menu.php"; ?>  <!-- barre rouge -->
        <?php
        if (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu'] == 'open') {
            include "left_menu.php";
        }
        ?>
    </nav>

    <?php include "after_nav.php"; ?>

    <!-- CONTENU PAGE -->
    <div id="page-wrapper">
        <div class="container-fluid">

            <!-- Titre de page -->
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Companies by Group</h1>
                </div>
            </div>

            <?php
            if ($_SESSION['statut'] == "SuperAdmin") {
            ?>
            <div class="row">
                <div class="col-lg-10">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            COMPANY BY GROUPS
                            <a data-toggle="modal" data-target="#myModaladdCompany" style="cursor: pointer;">
                                <i style='margin-left:10px;top: 4px;font-size:23px;' class='fa fa-plus-circle '></i>
                            </a>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">

                            <table width="100%" class="table table-striped table-bordered table-hover" id="Tablescompanygroup">
                                <thead>
                                    <tr>
                                        <th>COMPANY</th>
                                        <th>NEWSLETTER GROUP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                // tbl_company_group_newsletter : id_company_group_newsletter, id_company, id_groupe_newsletter, valid
                                // tbl_groupe_newsletter : id_groupe_newsletter, group_name
                                $sqldiv = "SELECT tbl_company_group_newsletter.*, tb_company.Fld_Company_Name, tbl_groupe_newsletter.group_name 
                                           FROM tbl_company_group_newsletter 
                                           LEFT JOIN tb_company 
                                                ON tb_company.Fld_Company_ID = tbl_company_group_newsletter.id_company
                                           LEFT JOIN tbl_groupe_newsletter 
                                                ON tbl_company_group_newsletter.id_groupe_newsletter = tbl_groupe_newsletter.id_groupe_newsletter";

                                $reqemp = mysql2_query($sqldiv);
                                while ($datadiv = mysqli_fetch_array($reqemp)) {
                                    echo "<tr class=\"gradeA\">
                                            <td>" . $datadiv["Fld_Company_Name"] . "</td>
                                            <td>" . $datadiv["group_name"] . "</td>
                                          </tr>";
                                }
                                ?>
                                </tbody>
                            </table>
                            <!-- /.table-responsive -->

                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-10 -->
            </div>
            <!-- /.row -->
            <?php } // fin if SuperAdmin ?>

        </div>
        <!-- /.container-fluid -->
    </div>
    <!-- /#page-wrapper -->

</div>
<!-- /#wrapper -->

<!--*************************************************************************************************************************************-->
<!-- POPUP ADD A COMPANY -->

<script type="text/javascript">
    $('#myModaladdCompany').on('hidden.bs.modal', function (e) {
        // placeholder : si un jour tu veux récupérer des valeurs à la fermeture
    });
</script>

<div class="modal fade" id="myModaladdCompany" tabindex="-1" role="dialog" aria-labelledby="myModaladdCompanyLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModaladdCompanyLabel">ADD A COMPANY</h4>
            </div>
            <div class="modal-body">
                COMPANY:<br>
                <input type="text" name="companyid" id="companyid" class="companyid" placeholder="Please Enter company">
            </div>

            <div class="modal-body">
                NEWSLETTER GROUP:<br>
                <select class="form-control" name="id_groupe_newsletter" id="id_groupe_newsletter">
                <?php
                // tbl_groupe_newsletter : id_groupe_newsletter, group_name
                $sqldiv = "SELECT * FROM tbl_groupe_newsletter";
                $reqemp = mysql2_query($sqldiv);
                while ($datadiv = mysqli_fetch_array($reqemp)) {
                    echo "<option value='" . $datadiv["id_groupe_newsletter"] . "'>" . $datadiv["group_name"] . "</option>";
                }
                ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>
<!-- END POPUP ADD A COMPANY -->
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
<script>
    $(document).ready(function() {
        $('#Tablescompanygroup').DataTable({
            responsive: true
        });
    });

    //**************************************** ADD COMPANY FROM POPUP ****************************************//
    $('#myModaladdCompany').on('click', '.btn-primary', function() {
        var value  = $('#companyid').val();
        var value2 = $('#id_groupe_newsletter').val();

        // enregistrer la nouvelle ligne dans la base
        $.ajax({
            url: 'add_company_by_group.php',
            data: { companyid: value, id_groupe_newsletter: value2 },
            type: 'get',
            success: function(output) {
                // alert(output); // si tu veux debugger
            }
        });

        $('#myModaladdCompany').modal('hide');
        window.location.reload(true);
    });
    //*****************************************************************************************************//
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
    .companyid {
        display: block;
        width: 100%;
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
        background-color: #F1F1F1;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 18px;
        color: #111;
    }
</style>

<script>
    $(document).ready(function() {
        $('input.companyid').typeahead({
            name: 'Fld_Company_Name',
            id: 'Fld_Company_ID',
            remote: 'list-company.php?query=%QUERY'
        });
    });
</script>
<!-- Fin Ajout pour autocompletion Roy -->

</body>

</html>
<?php
} else {
    echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=" . $_SERVER['REQUEST_URI'] . "\">";
}
?>
