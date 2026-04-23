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
<?php include "after_nav.php"; ?>

 

       

         <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">

            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Stock</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <table width="100%" class="table table-striped table-bordered table-hover" id="dataTablesStock">
                                <thead>
                                    <tr>
					<th>Fld_Stock_externe_ID</th>
					<th>PN</th>
					<th>DESCRIPTION</th>
					<th>Fld_Part_SN</th> 
					<th>Fld_Supplier_ID</th> 
					<th>Fld_Entry_Date</th> 
					<th>Fld_Part_Price</th> 
					<th>Fld_Price_Currency_ID</th> 
					<th>Fld_BAX_PO_Nbr</th>
					<th>Fld_Supplier_order_Date</th>
					<th>Fld_Supplier_Payment_Date</th> 
					<th>Fld_Qty</th> 
					<th>Fld_Condition_ID</th> 
					<th>Fld_Release_ID</th> 
					<th>Fld_Tag_Info_ID</th> 
					<th>Fld_Tag_Date</th>
					<th>Fld_Traceability_ID</th> 
					<th>Fld_Warehouse_Location</th> 
					<th>Fld_Physical_Stock</th> 
					<th>Fld_Owner_ID</th> 
					<th>Fld_Stock_Location_ID</th> 
					<th>Fld_Status_ID</th> 
					<th>Fld_Status_Ind</th> 
					<th>Fld_Status_Date</th> 
					<th>Fld_Stock_Remark</th> 
					<th>Fld_Shelf_Life_Limit</th> 
					<th>Fld_Valeur_Comptable</th> 
					<th>Fld_Valeur_Comptable_currency_Id</th> 
					<th>Fld_Sales_Remark</th>
					<th>Fld_External_Location</th>
					<th>Fld_Sales_Remark_ID</th>
					<th>Fld_Warehouse_Location_ID</th>
					<th>Fld_OriginalUnit_Stock_ID</th>
					<th>Fld_Min_Qty</th>
					<th>Fld_Publish</th>
					<th>Company</th>
                                    </tr>
                                </thead>
                               
                            </table>
							       
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

    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!-- Page-Level Demo Scripts - Tables - Use for reference -->
   <script language="JavaScript" type="text/javascript">
    $(document).ready(function() {
        $('#dataTablesStock').DataTable({
         "responsive": true,
		"processing": true,
        "serverSide": true,
        "ajax": "stockexternaldata.php"

        });
    });
       
    </script>

</body>

</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=".$_SERVER['REQUEST_URI']."\">";
?>