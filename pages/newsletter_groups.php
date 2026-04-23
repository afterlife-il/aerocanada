<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

if ($_SESSION['conectroy'] == "parfait") {

    require('../classes/company.class.php');

    // (Optionnel mais utile pour debug)
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

    <!-- CONTENU DE LA PAGE -->
    <div id="page-wrapper">
        <div class="container-fluid">

            <!-- Titre de page standard -->
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Newsletter Groups</h1>
                </div>
            </div>

            <?php
            if ($_SESSION['statut'] == "SuperAdmin") {
            ?>
            <div class="row">
                <div class="col-lg-10">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            NEWSLETTER GROUPS
                            <a href="javascript:add_newsletter_group()"> + ADD A NEWSLETTER GROUP</a>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="table-responsive">
                                <form action='validation_newsletter_group.php' method="post">
                                    <table class="table table-striped table-bordered table-hover" id="mytable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>GROUP NAME</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $z = 0;

                                        // recuperation newsletter groups
                                        // tbl_groupe_newsletter : id_groupe_newsletter / group_name
                                        $sqldiv = "SELECT * FROM tbl_groupe_newsletter";

                                        $reqemp = mysql2_query($sqldiv);
                                        while ($datadiv = mysqli_fetch_array($reqemp)) {
                                            $z++;
                                            $varsuivante = $z + 1;

                                            echo "<tr class=\"odd gradeX\" id=\"row_" . $z . "\">";
                                            echo "<td><b>" . $z . "</b></td>";
                                            echo "<td>
                                                    <input type='text'
                                                           name='group_name'
                                                           value='" . $datadiv['group_name'] . "'
                                                           id='group_name" . $datadiv["id_groupe_newsletter"] . "'
                                                           onmouseleave='javascript:majtarea(" . $datadiv["id_groupe_newsletter"] . ")'>
                                                    <input type=\"hidden\" name=\"nbnewsgroups\" id=\"nbnewsgroups\" value=\"" . $z . "\">
                                                  </td>";
                                            echo "<td>
                                                    <a href='javascript:sup_newsletter_group(" . $datadiv["id_groupe_newsletter"] . "," . $z . ")'
                                                       onClick=\"return(confirm('Etes vous sur ?'));\">
                                                        <img src='images/bin-blue-full-icon.png' border='0' width='27'>
                                                    </a>
                                                  </td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </form>
                            </div>
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

<script type="text/javascript">
    $(document).ready(function() {
        // ton tableau a l'id "mytable", donc on initialise DataTables dessus
        $('#mytable').DataTable({
            responsive: true
        });
    });

    // *******************************
    // Add newsletter group
    // *******************************
    function add_newsletter_group() {
        var cell, ligne;

        var tableau = document.getElementById("mytable");
        var nbLignes = tableau.rows.length;

        ligne = tableau.insertRow(-1); // nouvelle ligne en fin de tableau
        ligne.id = 'row_' + eval(nbLignes + 1);

        // Colonne #
        cell = ligne.insertCell(0);
        cell.innerHTML = eval(nbLignes + 1);

        // Colonne GROUP NAME
        cell = ligne.insertCell(1);
        cell.innerHTML = "<input class=\"form-control\" name=\"group_name\" id=\"group_name\" placeholder=\"GROUP NAME\">" +
                         "<input type='hidden' name='nbnewsgroups' value='" + nbLignes + "'>";

        // Colonne SUBMIT
        cell = ligne.insertCell(2);
        cell.innerHTML = "<input type='submit' value='submit' class=\"form-control\">";
    }

    // *******************************
    // Suppression newsletter group
    // *******************************
    function sup_newsletter_group(id, nbligne) {
        if (id > 0) {
            // Mise en évidence visuelle
            $('#mytable tr[id="row_' + nbligne + '"] td').css({
                'backgroundImage': 'none',
                'backgroundColor': 'white'
            });
            $('#mytable tr[id="row_' + nbligne + '"] td').animate({
                'backgroundColor': '#ff8888',
                'color': '#941010'
            }, 1000);

            $.get('del_newsletter_group.php', {
                idsup: id
            }, function(data) {
                $('#mytable tr[id="row_' + nbligne + '"] td').fadeTo("slow", 0, function() {
                    $(this).hide();
                });
            });

            document.getElementById("group_name" + id).value = '0';
        }
    }

    // *******************************
    // MAJ group_name (onmouseleave)
    // *******************************
    function majtarea(id) {
        var selection = document.getElementById("group_name" + id).value;
        $.get('majnewsgroup.php', {
            id_groupe_newsletter: id,
            group_name: selection
        }, function(data) {
            // rien à faire en retour pour l'instant
        });
    }
</script>

</body>
</html>
<?php
} else {
    echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=" . $_SERVER['REQUEST_URI'] . "\">";
}
?>
