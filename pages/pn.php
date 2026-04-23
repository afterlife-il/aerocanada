<!DOCTYPE html>
<?php
include_once "conf.php";
include_once "page_titles.php";
if (!empty($_POST['act']))
{
	if($_POST['act']=='stockrfq')
	{
		$message="Order reference: ".$_POST['Order_reference']."
		PN: ".$_POST['pn'];
		
		//Insertion dans la BDD
$requete = mysql2_query("INSERT INTO tb_rfq (`id_tb_rfq`,`RFQ_ID`,`pn`,`part_id`,`Priority_ID`,`Payment_Term_ID`,`observation`,`Type_id`,`Date`,`Customer_id`,`Contact_id`,`Customer_Detail_id`,`BAX_Employee_id`) VALUES ('','".$_POST['Order_reference']."','".$_POST['pn']."','','','','','','','','','','');");
		// send email
		mail("lamalol@gmail.com","RFQ ".$_POST['Order_reference'],$message);
	}
}
//echo $_SERVER['DOCUMENT_ROOT'];
?>
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

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">P/N</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Part Editor
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                           <div class="row">
							<div class="fa col-lg-12">
						   <?php
			//recuperation des quantites et id produit 
					$sql="SELECT * from ps_product,ps_product_lang where ps_product.reference='".$_GET['pn']."' and ps_product.id_product=ps_product_lang.id_product";
					//echo $sql;
					$req = mysql2_query($sql);
					$data = mysqli_fetch_array($req);
					 
					echo "P/N :".$_GET['pn']."<br>";
					echo "DESCRIPTION :".$data['name'];
					
			?>
						   </div>
						   </div>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
			
			<div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            RFQ
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                           <div class="row">
							<div class="fa col-lg-12">
						   <div style='display:none' id='blocrfq' class="panel-body">
						   <div id='divrfq' align='left' class="table-responsive">
						   </div>
						   <!-- /.table-responsive -->
										</div>
										<!-- /.panel-body -->
						   </div>
						   </div>
						   
						   <!--Suppliers RFQ-->
						   <div class="row">
							<div class="fa col-lg-12">
						   <div style='display:none' id='blocsuppliersrfq' class="panel-body">
						   <div id='divsuppliersrfq' align='left' class="table-responsive">
						   </div>
						   <!-- /.table-responsive -->
										</div>
										<!-- /.panel-body -->
						   </div>
						   </div>
						   <!--Fin Suppliers RFQ-->
						   <!--Affichage Suppliers-->
						    <div class="table-responsive" style="min-height:190px;height:190px;overflow:auto;">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
											<th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>							
<?php
 
					$sql="SELECT * from tb_rfq order by id_tb_rfq desc";
					//echo $sql;
					$req = mysql2_query($sql);
					while($data = mysqli_fetch_array($req))
					{
                                            echo "<tr><td>".$data['id_tb_rfq']."</td><td>".$data['RFQ_ID']."</td><td>".$data['pn']."</td><td>".$data['part_id']."</td><td>".$data['Priority_ID']."</td><td>".$data['Payment_Term_ID']."</td><td>".$data['observation']."</td><td>".$data['Type_id']."</td><td>".$data['Date']."</td><td>".$data['Customer_id']."</td><td>".$data['Contact_id']."</td><td>".$data['Customer_Detail_id']."</td><td>".$data['BAX_Employee_id']."</td></tr>";
					}
?>					
                                        
                                      
                                    </tbody>
                                </table>
                            </div>
						   <!--Fin Affichage Suppliers-->
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
			
			<div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            SUPPLIERS QUOTES
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                           <div class="row">
							<div class="fa col-lg-12">
						   test
						   <a href="javascript:addsuppliersrfq(1)">->RFQ</a>
						   </div>
						   </div>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
			
			<div class="row">
                <div class="col-lg-12">
				
				<div class="panel panel-default">
                        <div class="panel-heading">
                            IN STOCK  <a href="javascript:addstockpn('<?=$_GET['pn'];?>')">->Stock</a>
                        </div>
						
                        <!-- /.panel-heading -->
                        <div class="panel-body">
						
							 <!--Div ajout produit en stock-->
						   <div class="row">
							<div class="fa col-lg-12">
						   <div style='display:none' id='blocajoutstockpn' class="panel-body">
						   <div id='divajoutstockpn' align='left' class="table-responsive">
						   </div>
								</div>
						   </div>
						   </div>

						   <!--Fin Div ajout produit en stock-->
						   
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
									
									
									
									
<?php
			//recuperation des quantites et id produit 
					$sql="SELECT * from tb_stock_part";
					//echo $sql;
					$req = mysql2_query($sql);
					while($data = mysqli_fetch_array($req))
					{
                                        
                                            echo "<tr><td>".$data['id_stock_part']."</td><td>".$data['pn']."</td><td>".$data['part_id']."</td><td>".$data['sn']."</td><td>".$data['condition_part']."</td><td>".$data['release_tag']."</td><td>".$data['release_tag2']."</td><td>".$data['trace']."</td><td>".$data['release_tag_date']."</td><td>".$data['date_manufacture']."</td><td>".$data['location']."</td><td>".$data['aci_po']."</td><td>".$data['moq']."</td><td><a href='javascript:addrfq(".$data['id_stock_part'].")'>->RFQ</a></td></tr>";
					}
?>					
                                        
                                      
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.table-responsive -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
				
				
				
                 
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
<script src="../URBA/bower_components/jquery/dist/jquery.min.js"></script><!--Roy-->
    <!-- Page-Level Demo Scripts - Tables - Use for reference -->
    <script>
    $(document).ready(function() {
        $('#dataTables-example').DataTable({
            responsive: true
        });
    });
	

									<!--Ajout rfq-->
	function addrfq(id)
{
var blocrfq=document.getElementById('blocrfq');
if(blocrfq.style.display=='inline') blocrfq.style.display='none';
else
    {
blocrfq.style.display='inline';

document.getElementById("divrfq").innerHTML='<div id="divrfq" align="center"><img src="../images_design/Spin.gif" border="0"></div>';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "ajoutrfq.php?id_stock_part="+id, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { up_donnee_colla(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
    }
}
function up_donnee_colla(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divrfq').innerHTML='<div id="'+id+'" align="center">';
         var resp2;
        resp2 = xhr.responseText;
        document.getElementById('divrfq').innerHTML+=resp2;
    document.getElementById('divrfq').innerHTML+='</div>';
    }
}
<!--Suppliers RFQ-->
	function addsuppliersrfq(id)
{
var blocsuppliersrfq=document.getElementById('blocsuppliersrfq');
if(blocsuppliersrfq.style.display=='inline') blocsuppliersrfq.style.display='none';
else
    {
blocsuppliersrfq.style.display='inline';

document.getElementById("divsuppliersrfq").innerHTML='<div id="divsuppliersrfq" align="center"><img src="../images_design/Spin.gif" border="0"></div>';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "ajoutsppliersrfq.php?id_suppliers_part="+id, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { up_donnee_suppliers(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
    }
}
function up_donnee_suppliers(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divsuppliersrfq').innerHTML='<div id="'+id+'" align="center">';
         var resp2;
        resp2 = xhr.responseText;
        document.getElementById('divsuppliersrfq').innerHTML+=resp2;
    document.getElementById('divsuppliersrfq').innerHTML+='</div>';
    }
}
<!--Fin Suppliers RFQ-->


<!--Ajout Stock PN-->
	function addstockpn(id)
{
var blocajoutstockpn=document.getElementById('blocajoutstockpn');
if(blocajoutstockpn.style.display=='inline') blocajoutstockpn.style.display='none';
else
    {
blocajoutstockpn.style.display='inline';

document.getElementById("divajoutstockpn").innerHTML='<div id="divajoutstockpn" align="center"><img src="../images_design/Spin.gif" border="0"></div>';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "ajout_pn_stock.php?pn="+id, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { up_donnee_ajoutstockph(xhr,id); };
            xhr.send("pn="+id);/*si je veux mettre la variable sous forme de post je la met la*/
    }
}
function up_donnee_ajoutstockph(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divajoutstockpn').innerHTML='<div id="'+id+'" align="center">';
         var resp2;
        resp2 = xhr.responseText;
        document.getElementById('divajoutstockpn').innerHTML+=resp2;
    document.getElementById('divajoutstockpn').innerHTML+='</div>';
    }
}
<!--Fin Ajout Stock PN-->
												<!--Fin Ajout RFQ-->
    </script>

</body>

</html>
