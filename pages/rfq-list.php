<?php
// rfq-list.php
session_start();
include_once "conf.php";
include_once "page_titles.php";

if ($_SESSION['conectroy'] == "parfait") {
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

</head>

<body>
    <div id="wrapper">
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
            <?php include "top_menu.php"; ?>  <!-- barre rouge -->
            <?php if (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
        </nav>
        <?php include "after_nav.php"; ?>

        <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">

            <div class="row">
                <div class="col-lg-12">
                    <h3 class="page-header">RFQ LIST</h3>
                </div>
            </div>

            <!-- Zone message pour la suppression -->
            <div class="row" id="rfq-delete-message-container" style="display:none;">
                <div class="col-lg-12">
                    <div class="alert" id="rfq-delete-message"></div>
                </div>
            </div>

            <!-- Tableau SANS données : rempli en AJAX par DataTables -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            RFQs (1 ligne = 1 RFQ, les plus récents en premier)
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table width="100%" class="table table-striped table-bordered table-hover" id="dataTables-rfq">
                                    <thead>
                                        <tr>
                                            <th>RFQ ID</th>
                                            <th>Date</th>
                                            <th>Customer</th>
                                            <th>Contact</th>
                                            <th>Type</th>
                                            <th>Priority</th>
                                            <th>Sales contact</th>
                                            <th># P/N</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- rempli dynamiquement -->
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.table-responsive -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>

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

    <script>
    $(document).ready(function() {
        // DataTables en mode server-side
        var table = $('#dataTables-rfq').DataTable({
            processing: true,
            serverSide: true,
            ajax: 'rfq-list-data.php', // NOUVEAU FICHIER
            responsive: true,
            pageLength: 50,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            order: [[0, 'desc']],
            stateSave: true
        });

        // Suppression RFQ via AJAX
        $('#dataTables-rfq').on('click', '.btn-delete-rfq', function(e) {
            e.preventDefault();

            var $btn   = $(this);
            var rfq_id = $btn.data('rfq-id');

            if (!rfq_id) {
                alert('RFQ ID manquant.');
                return false;
            }

            if (!confirm("Supprimer totalement la RFQ #" + rfq_id + " ?\nToutes les lignes P/N associées seront supprimées.")) {
                return false;
            }

            $.ajax({
                url: 'rfq-delete.php',
                type: 'POST',
                dataType: 'json',
                data: { rfq_id: rfq_id },
                success: function(response) {
                    if (response && response.success) {
                        $('#rfq-delete-message')
                            .removeClass('alert-danger')
                            .addClass('alert-success')
                            .text('RFQ #' + rfq_id + ' supprimée avec succès.');
                        $('#rfq-delete-message-container').show();

                        // recharge uniquement les données du tableau (page actuelle)
                        table.ajax.reload(null, false);
                    } else {
                        $('#rfq-delete-message')
                            .removeClass('alert-success')
                            .addClass('alert-danger')
                            .text('Erreur lors de la suppression de la RFQ #' + rfq_id + '.');
                        $('#rfq-delete-message-container').show();
                    }
                },
                error: function() {
                    $('#rfq-delete-message')
                        .removeClass('alert-success')
                        .addClass('alert-danger')
                        .text('Erreur technique lors de la suppression de la RFQ #' + rfq_id + '.');
                    $('#rfq-delete-message-container').show();
                }
            });

            return false;
        });
    });
    </script>

</body>
</html>
<?php
} else {
    echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=" . $_SERVER['REQUEST_URI'] . "\">";
}
?>
