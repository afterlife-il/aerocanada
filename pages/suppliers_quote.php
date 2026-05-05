<?php
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

</head>

<body>
    <div id="wrapper">
  <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
    <?php include "top_menu.php"; ?>                       <!-- barre rouge -->
    <?php if(isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
  </nav>

 
   

        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
              <!--
  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
    <span class="sr-only">Toggle navigation</span>
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
  </button>
  -->
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
		if($_SESSION['leftmenu']=='open') include "left_menu.php";
	   ?>
            <!-- /.navbar-static-side -->
        </nav>
         <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">

            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">SUPPLIERS QUOTE</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <a href="https://aerocanada-industries.com/pages/add_suppliers_quote.php">  <img src="images/add.png" width="30"> ADD A SUPPLIERS QUOTE</a>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <table width="100%" class="table table-striped table-bordered table-hover" id="dataTables-example">
                      <thead>
                        <tr>
                          <th style="display:none">_id</th>      <!-- colonne cachée pour trier sur l'ID -->
                          <th>RFQ ID</th>
                          <th>PN</th>
                          <th>SN</th>
                          <th>SUPPLIER NAME</th>
                          <th>CONTACT NAME</th>
                          <th>QTY</th>
                          <th>CONDITION</th>
                          <th>PRICE</th>         <!-- <<<<<  AJOUT/CONFIRMER CETTE COLONNE -->
                          <th>$/€</th>
                          <th>LEAD TIME</th>
                          <th>RELEASE</th>
                          <th>TAG INFO</th>
                          <th>TAG DATE</th>
                          <th>Traced To</th>
                          <th>SALES REMARKS</th>
                          <th class="text-center all">ACTIONS</th>
                        </tr>
                      </thead>
                    </table>

							       <div style="display:none" id="bloccompany"><div id="divcompany" align="center"></div></div>   
                            <!-- /.table-responsive -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
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

    <script>
  // Confirmation sur la poubelle, déléguée au tableau
  $('#dataTables-example').on('click', 'a.js-del-sq', function(e){
    if (!confirm('Delete this supplier quote?')) {
      e.preventDefault();
    }
  });
</script>
    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!-- Page-Level Demo Scripts - Tables - Use for reference -->
<script>
$(function () {
  $('#dataTables-example').DataTable({
    responsive: true,
    processing: true,
    serverSide: true,
    deferRender: true,
    searchDelay: 300,
    ajax: 'sqdata.php',
    order: [[0, 'desc']],                 // tri par RFQ ID normalisé (côté serveur)
    pageLength: 25,
    columns: [
      { visible: false, searchable: false }, // 0 _id
      null, // 0 RFQ ID
      null, // 1 PN
      null, // 2 SN
      null, // 3 Supplier
      null, // 4 Contact
      null, // 5 Qty
      null, // 6 Condition
      null, // 7 PRICE
      null, // 8 $/€
      null, // 9 Lead time
      null, // 10 Release
      null, // 11 Tag info
      null, // 12 Tag date
      null, // 13 Traced to
      null, // 14 Sales remarks
      { orderable: false, searchable: false, className: 'text-center all', width: 70 } // 15 Actions
    ],
    language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json' }
  });

  // Confirmation poubelle
  $('#dataTables-example').on('click', 'a.js-del-sq', function(e){
    if (!confirm('Supprimer ce supplier quote ?')) e.preventDefault();
  });
});
</script>



</body>

</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=".$_SERVER['REQUEST_URI']."\">";
?>
