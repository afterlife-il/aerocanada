<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

if($_SESSION['conectroy']=="parfait"){

    // === helper : détection de famille avion à partir du texte complet ===
    function capa_get_aircraft_family($aircraftStr) {
        $s = strtoupper(trim($aircraftStr));
        if ($s === '') return '';

        // on simplifie un peu
        $s = str_replace(array('-', '/', '\\'), ' ', $s);

        // ATR
        if (strpos($s, 'ATR') !== false) {
            return 'ATR';
        }

        // AIRBUS famille A3xx
        if (preg_match('/\bA3(00|10|18|19|20|21|30|40|80)\b/', $s) || strpos($s, 'AIRBUS') !== false) {
            return 'AIRBUS';
        }

        // BOEING B7xx
        if (preg_match('/\bB7(27|37|47|57|67|77|87)\b/', $s) || strpos($s, 'BOEING') !== false) {
            return 'BOEING';
        }

        // DASH 8 / Q400 / DH8
        if (strpos($s, 'DASH 8') !== false || strpos($s, 'Q400') !== false || strpos($s, 'DH8') !== false) {
            return 'DASH 8';
        }

        // EMBRAER / ERJ / E-JET
        if (strpos($s, 'EMBRAER') !== false || strpos($s, 'ERJ') !== false || strpos($s, 'E190') !== false || strpos($s, 'E170') !== false) {
            return 'EMBRAER';
        }

        // BOMBARDIER / CRJ / CL-215/415
        if (strpos($s, 'CRJ') !== false || strpos($s, 'CL-215') !== false || strpos($s, 'CL-415') !== false || strpos($s, 'BOMBARDIER') !== false) {
            return 'BOMBARDIER';
        }

        // FOKKER
        if (strpos($s, 'F27') !== false || strpos($s, 'F50') !== false || strpos($s, 'F100') !== false || strpos($s, 'FOKKER') !== false) {
            return 'FOKKER';
        }

        // Si on ne sait pas classifier, on renvoie OTHER
        return 'OTHER';
    }

    // ===========================
    // Préparation des listes pour les filtres CAPA LIST
    // ===========================
    $providers    = array();
    $aircrafts    = array();
    $atas         = array();
    $capabilities = array();

    if (isset($db_link) && $db_link) {

        // Liste des fournisseurs (providers / MRO) à partir de tbl_capa_list.id_company
        $sqlProviders = "
            SELECT DISTINCT c.Fld_Company_ID, c.Fld_Company_Name
            FROM tbl_capa_list cl
            LEFT JOIN tb_company c ON cl.id_company = c.Fld_Company_ID
            WHERE cl.id_company IS NOT NULL
              AND cl.id_company <> ''
            ORDER BY c.Fld_Company_Name ASC
        ";
        if ($resP = mysqli_query($db_link, $sqlProviders)) {
            while ($rowP = mysqli_fetch_assoc($resP)) {
                $providers[] = $rowP;
            }
        }

        // Liste des AIRCRAFT (valeur brute telle que stockée dans la table)
        $sqlAircraft = "
            SELECT DISTINCT aircraft
            FROM tbl_capa_list
            WHERE aircraft IS NOT NULL
              AND aircraft <> ''
            ORDER BY aircraft ASC
        ";
        if ($resA = mysqli_query($db_link, $sqlAircraft)) {
            while ($rowA = mysqli_fetch_assoc($resA)) {
                $aircrafts[] = $rowA['aircraft'];
            }
        }
        
        // Construire la liste des FAMILLES d'avion à partir des valeurs brutes
        $aircraftFamilies = array();
        foreach ($aircrafts as $ac) {
            $fam = capa_get_aircraft_family($ac);
            if ($fam === '') continue;
            $aircraftFamilies[$fam] = true; // tableau associatif pour éviter les doublons
        }
        // on récupère les clés triées (ATR, AIRBUS, BOEING, ...)
        $aircraftFamilies = array_keys($aircraftFamilies);
        sort($aircraftFamilies);

        // Liste des ATA
        $sqlAta = "
            SELECT DISTINCT ata
            FROM tbl_capa_list
            WHERE ata IS NOT NULL
              AND ata <> ''
            ORDER BY ata ASC
        ";
        if ($resAta = mysqli_query($db_link, $sqlAta)) {
            while ($rowAta = mysqli_fetch_assoc($resAta)) {
                $atas[] = $rowAta['ata'];
            }
        }

        // Liste des CAPABILITIES
        $sqlCap = "
            SELECT DISTINCT capability
            FROM tbl_capa_list
            WHERE capability IS NOT NULL
              AND capability <> ''
            ORDER BY capability ASC
        ";
        if ($resCap = mysqli_query($db_link, $sqlCap)) {
            while ($rowCap = mysqli_fetch_assoc($resCap)) {
                $capabilities[] = $rowCap['capability'];
            }
        }
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

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body>

<div id="wrapper">
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
        <?php include "top_menu.php"; ?>
        <?php if(isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
    </nav>

    <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">

        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">CAPA LIST</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <a data-toggle="modal"
                           data-target="#myModal"
                           data-test="my_id_value"
                           class="identifyingClass"
                           style="top: 4px;color:#fff;text-decoration: none;">
                            ADD A PN
                            <i style="margin-left:10px;top: 4px;font-size:23px;" class="fa fa-plus-circle"></i>
                        </a>
                    </div>

                    <div class="panel-body">

                        <!-- Filtres avancés CAPA LIST -->
                        <div class="row capa-filters" style="margin-bottom:15px;">
                            <div class="col-md-3 col-sm-6 filter-group">
                                <label for="filter_company">Provider / MRO</label>
                                <select id="filter_company" class="form-control input-sm">
                                    <option value="">All providers</option>
                                    <?php foreach ($providers as $prov): ?>
                                        <option value="<?php echo (int)$prov['Fld_Company_ID']; ?>">
                                            <?php echo htmlspecialchars($prov['Fld_Company_Name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-lg-3">
    <div class="form-group">
        <label>Aircraft</label>
        <select class="form-control" id="aircraft" name="aircraft">
            <option value="">All aircraft</option>
            <?php
            if (!empty($aircraftFamilies)) {
                foreach ($aircraftFamilies as $fam) {
                    echo '<option value="'.htmlspecialchars($fam).'">'.htmlspecialchars($fam).'</option>';
                }
            }
            ?>
        </select>
    </div>
</div>


                            <div class="col-md-2 col-sm-6 filter-group">
                                <label for="filter_ata">ATA</label>
                                <select id="filter_ata" class="form-control input-sm">
                                    <option value="">All ATA</option>
                                    <?php foreach ($atas as $ata): ?>
                                        <option value="<?php echo htmlspecialchars($ata); ?>">
                                            <?php echo htmlspecialchars($ata); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3 col-sm-6 filter-group">
                                <label for="filter_capability">Capability</label>
                                <select id="filter_capability" class="form-control input-sm">
                                    <option value="">All capabilities</option>
                                    <?php foreach ($capabilities as $cap): ?>
                                        <option value="<?php echo htmlspecialchars($cap); ?>">
                                            <?php echo htmlspecialchars($cap); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-1 col-sm-6 filter-group">
                                <label>&nbsp;</label>
                                <button id="reset_filters" class="btn btn-default btn-sm btn-block" type="button">
                                    Reset
                                </button>
                            </div>
                        </div>

                        <!-- Tableau CAPA LIST -->
                        <table width="100%" class="table table-striped table-bordered table-hover" id="dataTablescapalist">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>PN</th>
                                    <th>DESCRIPTION</th>
                                    <th>AIRCRAFT</th>
                                    <th>ATA</th>
                                    <th>CAPABILITY</th>
                                    <th>PMA</th>
                                    <th>DOA</th>
                                    <th>DER</th>
                                    <th>CODE OEM</th>
                                    <th>DESIGN OEM</th>
                                    <th>COMPANY</th>
                                    <th></th>
                                </tr>
                            </thead>
                        </table>

                    </div><!-- /.panel-body -->
                </div><!-- /.panel -->

            </div><!-- /.col-lg-12 -->
        </div><!-- /.row -->

    </div><!-- /#page-wrapper/page-wrapper2 -->

</div><!-- /#wrapper -->


<!-- POPUP ADD A PN TO CAPA LIST -->

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel">ADD A PN TO CAPA LIST</h4>
            </div>

            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="pnidcl" class="col-form-label">PN:</label><br>
                            <input type="text" name="pnidcl" id="pnidcl" class="pnidcl" placeholder="Please Enter P/N" required>
                        </div>
                        <div class="col-md-2">
                            &nbsp;
                        </div>
                        <div class="col-md-4">
                            <label for="descriptioncl" class="col-form-label">DESCRIPTION:</label>
                            <input class="form-control" name="descriptioncl" id="descriptioncl">
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="aircraftcl" class="col-form-label">AIRCRAFT:</label>
                            <input type="text" name="aircraftcl" id="aircraftcl" class="form-control">
                        </div>
                        <div class="col-md-2">
                            &nbsp;
                        </div>
                        <div class="col-md-4">
                            <label for="atacl" class="col-form-label">ATA:</label>
                            <input class="form-control" name="atacl" id="atacl">
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="code_oemcl" class="col-form-label">CODE OEM:</label>
                            <input type="text" name="code_oemcl" id="code_oemcl" class="form-control">
                        </div>
                        <div class="col-md-2">
                            &nbsp;
                        </div>
                        <div class="col-md-4">
                            <label for="design_oemcl" class="col-form-label">DESIGN OEM:</label>
                            <input class="form-control" name="design_oemcl" id="design_oemcl">
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="companyidcl" class="col-form-label">COMPANY NAME:</label><br>
                            <input type="text" name="companyidcl" id="companyidcl" class="companyidcl" placeholder="Please Enter company">
                        </div>
                        <div class="col-md-2">
                        </div>
                        <div class="col-md-4">
                            <label for="capabilitycl" class="col-form-label">CAPABILITY:</label>
                            <input type="text" name="capabilitycl" id="capabilitycl" class="form-control">
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

<script type="text/javascript">
$(document).ready(function() {

    var table = $('#dataTablescapalist').DataTable({
        "responsive": true,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "capalistdata.php",
            "type": "POST",
            "data": function (d) {
                d.filter_company    = $('#filter_company').val();
                d.filter_aircraft   = $('#filter_aircraft').val();
                d.filter_ata        = $('#filter_ata').val();
                d.filter_capability = $('#filter_capability').val();
            }
        }
    });

    // Filtres
    $('#filter_company, #filter_aircraft, #filter_ata, #filter_capability').on('change keyup', function() {
        table.draw();
    });

    $('#reset_filters').on('click', function(e) {
        e.preventDefault();
        $('#filter_company').val('');
        $('#filter_aircraft').val('');
        $('#filter_ata').val('');
        $('#filter_capability').val('');
        table.draw();
    });

    // Couleurs par prestataire (COMPANY) – uniquement visuel interne
    var providerColorMap = {};
    var providerColors = ['#003f5c','#2f4b7c','#665191','#a05195','#d45087','#f95d6a','#ff7c43','#ffa600'];

    function getProviderColor(name) {
        name = $.trim(name || '');
        if (!name) {
            return '';
        }
        if (providerColorMap[name]) {
            return providerColorMap[name];
        }
        var index = Object.keys(providerColorMap).length % providerColors.length;
        providerColorMap[name] = providerColors[index];
        return providerColorMap[name];
    }

    table.on('draw', function () {
        $('#dataTablescapalist tbody tr').each(function () {
            var $tdCompany = $(this).find('td').eq(11); // COMPANY
            var name = $.trim($tdCompany.text());
            var color = getProviderColor(name);
            if (color) {
                $tdCompany.css({
                    'background-color': color,
                    'color': '#fff',
                    'font-weight': 'bold'
                });
            }
        });
    });

    // Sauvegarde PN depuis la popup
    $('#myModal').on('click', '.btn-primary', function(){
        var value  = $('#pnidcl').val();
        var value2 = $('#descriptioncl').val();
        var value3 = $('#aircraftcl').val();
        var value4 = $('#atacl').val();
        var value5 = $('#capabilitycl').val();
        var value6 = $('#code_oemcl').val();
        var value7 = $('#design_oemcl').val();
        var value8 = $('#companyidcl').val();

        $.ajax({
            url: 'add_capalist_popup.php',
            type: 'get',
            data: {
                pnid: value,
                description: value2,
                aircraft: value3,
                ata: value4,
                capability: value5,
                code_oem: value6,
                design_oem: value7,
                companyid: value8
            },
            success: function(output) {
                // On pourrait afficher un message si besoin
            }
        });

        $('#myModal').modal('hide');
        document.location.reload();
    });

});
</script>

<!-- Autocomplétion Roy -->
<script src="js/typeahead.js"></script>
<style>
    .capa-filters label {
        font-size: 11px;
        font-weight: 600;
        margin-bottom: 2px;
    }
    .capa-filters .form-control.input-sm {
        height: 30px;
        padding: 3px 6px;
        font-size: 12px;
    }
    .capa-filters .filter-group {
        margin-bottom: 8px;
    }

    .tt-hint,
    .companyidcl,
    .pnidcl {
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
$(document).ready(function() {
    $('input.companyidcl').typeahead({
        name: 'Fld_Company_Name',
        id: 'Fld_Company_ID',
        remote: 'list-company.php?query=%QUERY'
    });

    $('input.pnidcl').typeahead({
        name: 'Fld_Part_Nbr',
        id: 'Fld_Part_ID',
        remote: 'list-pn-select.php?query=%QUERY'
    });
});
</script>

</body>
</html>
<?php
} else {
    echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=".$_SERVER['REQUEST_URI']."\">";
}
?>
