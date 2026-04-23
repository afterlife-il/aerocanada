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
    <?php include "top_menu.php"; ?>  <!-- barre rouge avec SON burger -->
    <?php if(isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
</nav>
<?php include "after_nav.php"; ?>

<div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">

            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">ADD STOCK</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            
                        </div>
						
						<form id="formajoutpart" role="form" method="post" action="valid_ajout_stock.php">
					<?php
			//recuperation des quantites et id produit 
			/*
			Table tbl_Stock :::: 
Fld_Stock_ID
Fld_Part_ID
Fld_Part_SN
Fld_Supplier_ID
Fld_Entry_Date
Fld_Part_Price
Fld_Price_Currency_ID
Fld_BAX_PO_Nbr
Fld_Supplier_order_Date
Fld_Supplier_Payment_Date
Fld_Qty
Fld_Condition_ID
Fld_Release_ID
Fld_Tag_Info_ID
Fld_Tag_Date
Fld_Traceability_ID
Fld_Warehouse_Location (ne pas afficher)
Fld_Physical_Stock (??)
Fld_Owner_ID
Fld_Stock_Location_ID (info de la table + possibilite d'ajout)
Fld_Status_ID 
Fld_Status_Ind (ne pas mettre)
Fld_Status_Date
Fld_Stock_Remark
Fld_Shelf_Life_Limit
Fld_Valeur_Comptable (a mettre un champ)
Fld_Valeur_Comptable_currency_Id (a mettre un champ)
Fld_Sales_Remark
Fld_External_Location (champ recherche company) + rejouter un champ texte commentaire sur la location
Fld_Sales_Remark_ID
Fld_Warehouse_Location_ID (ne pas afficher)
Fld_OriginalUnit_Stock_ID
Fld_Min_Qty
Fld_Publish
status
*/
?>			
						<div class="panel-body">
							<div class="row">
							
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>PN</label><br>
										<input type="text" name="Fld_Part_ID" id="Fld_Part_ID" class="Fld_Part_ID" placeholder="CHOOSE A PN" required>
                                        </div>
									</div>	
									<div class="col-lg-2">
										<div class="form-group" id='blocdescription'>
										<div id='divdescription'>
                                            <label>Part description</label>
                                            <input class="form-control" name="Fld_Part_Desc" onclick="javascript:descfrompn();">
                                        </div>
                                        </div>
									</div>	
									<div class="col-lg-1">		
										<div class="form-group">
                                            <label>Currency</label>
                                            <select class="form-control" name="Fld_Price_Currency_ID">
											<?php
											//recuperation du nom de la currency	
											// Fld_Currency_ID    Fld_Currency_Text
											$sqldiv="SELECT * FROM tbl_Currency";
											
											//echo $sqldiv;
											$reqemp = mysql2_query($sqldiv);
											while($datadiv = mysqli_fetch_array($reqemp))
											{
												echo "<option value='".$datadiv ['Fld_Currency_ID']."'>".$datadiv ['Fld_Currency_Text']."</option>";
											}
											?>
                                                
                                            </select>
                                        </div>
									</div>	
									<div class="col-lg-1">	
										<div class="form-group">
                                            <label>Price</label>
                                            <input class="form-control" name="Fld_Part_Price">
                                        </div>
									</div>	
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>Supplier order date</label>
                                            <input class="form-control" name="Fld_Supplier_order_Date">
                                        </div>
									</div>	
									<div class="col-lg-1">	
										<div class="form-group">
                                            <label>Condition</label>
                                            <select class="form-control" name="Fld_Condition_ID">
											<?php

											$sqldiv="SELECT distinct(Fld_Condition_Text),Fld_Condition_ID FROM tbl_Condition order by Fld_Condition_Text";
											
											//echo $sqldiv;
											$reqemp = mysql2_query($sqldiv);
											while($datadiv = mysqli_fetch_array($reqemp))
											{
												echo "<option value='".$datadiv ['Fld_Condition_ID']."'>".$datadiv ['Fld_Condition_Text']."</option>";
											}
											?>
                                                
                                            </select>
                                        </div>
									</div>	
								
							</div>	
							<div class="row">
									<div class="col-lg-2">	 
										<div class="form-group">
                                            <label>MFG/OIM</label><br>
											<input type="text" name="Fld_Part_MFG" id="Fld_Part_MFG" class="Fld_Part_MFG" placeholder="CHOOSE A COMPANY" >
                                        </div>
									</div>	
									<div class="col-lg-1">	
										<div class="form-group">
                                            <label>Aircraft</label>
                                            <select class="form-control" name="Fld_AC_ID">
											<?php
											// Fld_AC_ID  Fld_AC_Model  Fld_AC_Series  Fld_AC_Manufacturer  Fld_AC_Engine_Model  Fld_AC_Engine_Series
											$sqldiv="SELECT Distinct(Fld_AC_Model),Fld_AC_ID FROM tbl_Aircraft";
											
											//echo $sqldiv;
											$reqemp = mysql2_query($sqldiv);
											while($datadiv = mysqli_fetch_array($reqemp))
											{
												echo "<option value='".$datadiv ['Fld_AC_ID']."'>".$datadiv ['Fld_AC_Model']."</option>";
											}
											?>
                                                
                                            </select>
                                        </div>
									</div>	
								<div class="col-lg-1">
										<div class="form-group">
                                            <label>List Price</label>
                                            <input class="form-control" name="Fld_Part_List_Price">
										</div>
								</div>
								<div class="col-lg-1">
										<div class="form-group">
                                            <label>SN</label>
                                            <input class="form-control" name="Fld_Part_SN">
                                        </div>
								</div>	
								
								<div class="col-lg-1">		
										<div class="form-group">
                                            <label>Supplier payment date</label>
                                            <input class="form-control" name="Fld_Supplier_Payment_Date">
                                        </div>
								</div>	
								<div class="col-lg-1">		
										<div class="form-group">
                                            <label>Release</label>
                                            <select class="form-control" name="Fld_Release_ID">
											<?php
											$sqldiv="SELECT * FROM tbl_Release";
											
											//echo $sqldiv;
											$reqemp = mysql2_query($sqldiv);
											while($datadiv = mysqli_fetch_array($reqemp))
											{
												echo "<option value='".$datadiv ['Fld_Release_ID']."'>".$datadiv ['Fld_Release_Text']."</option>";
											}
											?>
                                            </select>
                                        </div>
								</div>	
								<div class="col-lg-1">		
                                </div>
                        </div>
						<div class="row">			
								<div class="col-lg-2">	
										<div class="form-group">
                                            <label>Suppliers</label>
											<!--Les suppliers sont aussi les compagnie par ce qu'une compagnie peut etre vendeuse et acheteuse-->
                                          <br>
										<input type="text" name="Fld_Supplier_ID" id="Fld_Supplier_ID" class="Fld_Supplier_ID" placeholder="CHOOSE A COMPANY" >
                                        </div>
								</div>	
								<div class="col-lg-1">			
										<div class="form-group">
                                            <label>ACI 770 PO#</label>
                                            <input class="form-control" name="Fld_BAX_PO_Nbr">
                                        </div>
								</div>	
								<div class="col-lg-1">			
										<div class="form-group">
                                            <label>Qty</label>
                                            <input class="form-control" name="Fld_Qty">
                                        </div>
								</div>	
								<div class="col-lg-1">			
										<div class="form-group">
                                            <label>Tag Info</label><br>
											<input type="text" name="Fld_Tag_Info_ID" id="Fld_Tag_Info_ID" class="Fld_Tag_Info_ID" placeholder="CHOOSE A COMPANY" >
                                        </div>
								</div>	
								<div class="col-lg-1">
										  <div class="form-group">
										    <label>Tag Date</label>
										    <input type="text" class="form-control datepicker" name="Fld_Tag_Date">
										  </div>
										</div>

								<div class="col-lg-1">			
										<div class="form-group">
                                            <label>Traceability</label><br>
											<input type="text" name="Fld_Traceability_ID" id="Fld_Traceability_ID" class="Fld_Traceability_ID" placeholder="CHOOSE A COMPANY" >
                                        </div>
								</div>
								<div class="col-lg-1">			
										<div class="form-group">
                                            <label>Owner</label><br>
											<input type="text" name="Fld_Owner_ID" id="Fld_Owner_ID" class="Fld_Owner_ID" placeholder="CHOOSE A COMPANY" >
                                        </div>
								</div>	
								<div class="col-lg-1">		
										<div class="form-group">
                                            <label>Stock Location</label>
                                            <select class="form-control" name="Fld_Stock_Location_ID">
											<?php
											// ** tbl_Stock_Location ** Fld_Stock_Location_ID  Fld_Stock_Location_Text
											$sqlstockl="SELECT * FROM tbl_Stock_Location";
											
											//echo $sqlstockl;
											$reqstockl = mysql2_query($sqlstockl);
											while($datastockl = mysqli_fetch_array($reqstockl))
											{
												echo "<option value='".$datastockl['Fld_Stock_Location_ID']."'>".$datastockl['Fld_Stock_Location_Text']."</option>";
											}
											?>
                                                
                                            </select>
                                        </div>
								</div>	
								
								<div class="col-lg-2">			
										<div class="form-group">
                                            <label>Remark</label>
											<textarea class="form-control" rows="3" name="Fld_Remark"></textarea>
                                        </div>
                                </div>
										
                                <!-- /.col-lg-5 (nested) -->
                            </div>
							<div class="row">
								<div class="col-lg-1">		
										<div class="form-group">
                                            <label>Status</label>
                                            <select class="form-control" name="Fld_Status_ID">
											<?php
											// ** tbl_Status ** Fld_Status_ID  Fld_Status_Text
											$sqlstatus="SELECT * FROM tbl_Status";
											
											//echo $sqlstatus;
											$reqstatus = mysql2_query($sqlstatus);
											while($datastatus = mysqli_fetch_array($reqstatus))
											{
												echo "<option value='".$datastatus['Fld_Status_ID']."'>".$datastatus['Fld_Status_Text']."</option>";
											}
											?>
                                                
                                            </select>
                                        </div>
								</div>
								<div class="col-lg-1">			
										<div class="form-group">
                                            <label>Status Date</label>
                                            <input class="form-control" name="Fld_Status_Date">
                                        </div>
								</div>	
                            </div>
							<div class="row">
								
								<div class="col-lg-1">			
										<div class="form-group">
                                            <label>Shelf Life Limit</label>
                                            <input class="form-control" name="Fld_Shelf_Life_Limit">
                                        </div>
								</div>	
								<div class="col-lg-1">			
										<div class="form-group">
                                            <label>Valeur Comptable</label>
                                            <input class="form-control" name="Fld_Valeur_Comptable">
                                        </div>
								</div>	
								<div class="col-lg-1">		
										<div class="form-group">
                                            <label>Currency</label>
                                            <select class="form-control" name="Fld_Valeur_Comptable_currency_Id">
											<?php
											//recuperation du nom de la currency	
											// Fld_Currency_ID    Fld_Currency_Text
											$sqlvccid="SELECT * FROM tbl_Currency";
											
											//echo $sqlvccid;
											$reqvccid = mysql2_query($sqlvccid);
											while($datavccid = mysqli_fetch_array($reqvccid))
											{
												echo "<option value='".$datavccid['Fld_Currency_ID']."'>".$datavccid['Fld_Currency_Text']."</option>";
											}
											?>
                                                
                                            </select>
                                        </div>
								</div>
								<div class="col-lg-2">			
										<div class="form-group">
                                            <label>Sales Remark</label>
											<textarea class="form-control" rows="3" name="Fld_Sales_Remark"></textarea>
                                        </div>
                                </div>
								<div class="col-lg-1">			
										<div class="form-group">
                                            <label>Min Qty</label>
                                            <input class="form-control" name="Fld_Min_Qty">
                                        </div>
								</div>								
									
								
                            </div>
	
							<?php $today = date("Y-m-d"); ?>
							<input type="hidden" name="Fld_Entry_Date" value="<?php echo $today;?>">
										
									<button type="submit" class="btn btn-default">Validate</button>
                            </div>

									
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
    <!-- 
    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
    <script src="../vendor/datatables-responsive/dataTables.responsive.js"></script> 
    -->

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

	<!-- Include Date Range Picker -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/css/bootstrap-datepicker3.css"/>

<script>
   
$.fn.datepicker.defaults.format = "yyyy-mm-dd";
$('.datepicker').datepicker({startDate: '-3d'});

var datepicker = $.fn.datepicker.noConflict(); // return $.fn.datepicker to previously assigned value
$.fn.bootstrapDP = datepicker;

</script>
<!--Ajout pour autocompression Roy-->
  
	<!--<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-2.1.4.min.js"></script> 
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>-->
    <script src="js/typeahead.js"></script>
    <style>
	<!--
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
        .Fld_Supplier_ID,.Fld_Tag_Info_ID {
            border: 2px solid #CCCCCC;
            border-radius: 8px 8px 8px 8px;
            font-size: 24px;
            height: 45px;
            line-height: 30px;
            outline: medium none;
            padding: 8px 12px;
            width: 400px;
        }
-->
       
		.tt-hint,
        .Fld_Supplier_ID,.Fld_Tag_Info_ID,.Fld_Part_MFG ,.Fld_Part_ID,.Fld_Traceability_ID,.Fld_Owner_ID{
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
            border: 1px solid rgba(0, 0, 0, 0.2);
            border-radius: 8px 8px 8px 8px;
            font-size: 18px;
            color: #111;
            background-color: #F1F1F1;
        }
    </style>
    <script>
        $(document).ready(function() {

            $('input.Fld_Supplier_ID').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
			$('input.Fld_Tag_Info_ID').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
			$('input.Fld_Traceability_ID').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
			$('input.Fld_Owner_ID').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
			$('input.Fld_Part_MFG').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
			$('input.Fld_Part_ID').typeahead({
                name: 'Fld_Part_Nbr',
				id: 'Fld_Part_ID',
                remote: 'list-pn-select.php?query=%QUERY'
            });
        })
<!--Fin Ajout pour autocompression Roy-->


<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!--Recuperation Description a partir du P/N-->
<!--*******************************************************************************--> 
<!--*******************************************************************************-->
	function descfrompn(id)
{
var blocdescription=document.getElementById('blocdescription');
var Fld_Part_ID=document.getElementById('Fld_Part_ID').value;

blocdescription.style.display='inline';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "descriptionfrompn.php?id="+Fld_Part_ID, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { up_descfrompn(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
    
}
function up_descfrompn(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divdescription').innerHTML='<div id="'+id+'" align="center">';
         var resp2;
        resp2 = xhr.responseText;
        document.getElementById('divdescription').innerHTML+=resp2;
    document.getElementById('divdescription').innerHTML+='</div>';
    }
}

<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!-- Fin Recuperation Description a partir du P/N-->
<!--*******************************************************************************-->
<!--*******************************************************************************-->
</script>
</body>

</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=".$_SERVER['REQUEST_URI']."\">";
?>