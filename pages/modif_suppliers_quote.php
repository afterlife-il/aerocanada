<?php
session_start();
include_once "conf.php";
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
	
			<!--CSS rating ajoute par roy-->
			<link href="rating.css" rel="stylesheet">
			<!--Fin CSS rating ajoute par roy-->
</head>

<body>
<?php
include_once "conf.php";
?>
    <div id="wrapper">
  <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
    <?php include "top_menu.php"; ?>                       <!-- barre rouge -->
    <?php if(isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
</nav>
<?php include "after_nav.php"; ?>

  <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">
    <!-- ton contenu -->
  </div>
</div>

        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
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
		include "left_menu.php";
	   ?>
            <!-- /.navbar-static-side -->
        </nav>
		<?php 
		//****tbl_RFQ_2******ID  Fld_RFQ_ID  Fld_Supplier_ID  Fld_Qty  Fld_Condition_ID  Fld_Payment_Term_ID  Fld_Delivery  Fld_Price  Fld_Price_Max  Fld_Price_Min  Fld_Currency_ID  Fld_Traceability_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Release_ID  Fld_Part_ID  Fld_Remark  Fld_IsBeen_Chosen  Fld_Current_Date  Fld_Qty_Received  Fld_Part_SN  Fld_Supplier_Contact_ID  Fld_Date_RecevdEnd_REP  lead_time  	aci_contact
		  
					$sql="SELECT * from tbl_RFQ_2 where ID='".$_GET['ID']."'";
					//echo $sqlrfq2;
					$req = mysql2_query($sql);
					$data = mysqli_fetch_array($req);
		?>
         <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-10">
                   
                </div>
                <!-- /.col-lg-10 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-10">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            MODIF SUPPLIERS QUOTE
                        </div>
						<form id="formajoutsq" role="form" method="post" action="valid_modif_sq.php" enctype="multipart/form-data">
						<input type="hidden" name="part_id" value="<?php if(!empty($_GET['part_id'])) echo $_GET['part_id'];?>">
						<input type="hidden" name="id_table_rfq2" value="<?php echo $data['ID'];?>">
                        <div class="panel-body">
                            <div class="row">
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>RFQ ID</label>
                                            <input class="form-control" name="Fld_RFQ_ID" value="<?php echo $data['Fld_RFQ_ID'];?>">
                                        </div>
								</div>
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>SUPPLIERS</label><br>
											<!--Les suppliers sont aussi les compagnie par ce qu'une compagnie peut etre vendeuse et acheteuse-->
											<?php 
											//recuperation du nom de compagnie pour Suppliers********************
											$sqlncs="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID=".$data['Fld_Supplier_ID'];
											$reqncs = mysql2_query($sqlncs);
											$datancs = mysqli_fetch_array($reqncs);
											//Fin recuperation du nom de compagnie pour Suppliers********************?>
											<input type="text" name="companyid" id="companyid" class="companyid" placeholder="<?php echo $datancs['Fld_Company_Name'];?>">
                                        </div>
								</div>
								<div class="col-lg-2" id='bloccontactname'>
										<div class="form-group" id='divcontactname'>
                                            <label>SUPPLIERS CONTACT ID</label>
											<?php 
											//recuperation du nom du contact suppliers ********************
											$sqlscid="SELECT Fld_Contact_Name FROM tb_company_contact WHERE id_company_contact=".$data['Fld_Supplier_Contact_ID'];
											$reqscid = mysql2_query($sqlscid);
											$datascid = mysqli_fetch_array($reqscid);
											//Fin recuperation du nom du contact suppliers ********************
											
											?>
											<select class="form-control" name="Fld_Supplier_Contact_ID"  onclick="javascript:majtarea();">
											<option value="<?php echo $data['Fld_Supplier_Contact_ID'];?>"><?php echo $datascid['Fld_Contact_Name'];?></option>
                                                
                                            </select>
                                        </div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>PN</label><br>
											<?php
											//Table tbl_Parts :::: Fld_Part_ID  Fld_Part_Nbr  Fld_Part_Desc  Fld_Part_MFG  Fld_Part_MFG_Old  Fld_AC_ID  Fld_Old_LP  Fld_Part_List_Price  Fld_Part_Price_Currency_ID  Fld_Part_LP_Date  Fld_Remark status
											//recuperation du PN ********************
											$sqlpn="SELECT Fld_Part_Nbr,Fld_Part_Desc FROM tbl_Parts WHERE Fld_Part_ID=".$data['Fld_Part_ID'];
											$reqpn = mysql2_query($sqlpn);
											$datapn = mysqli_fetch_array($reqpn);
											//Fin recuperation du PN ********************
											?>
											<input type="text" name="pnid" id="pnid" class="pnid" placeholder="<?php echo $datapn['Fld_Part_Nbr'];?>">
       
                                        </div>
								</div>
								<div class="col-lg-2" id='blocdescription'>
										<div class="form-group" id='divdescription'>
                                            <label>DESCRIPTION</label>
                                            <input class="form-control" name="description"  onclick="javascript:descfrompn();" placeholder="<?php echo $datapn['Fld_Part_Desc'];?>">
                                        </div>
								</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>PART SN</label>
											<input class="form-control" name="Fld_Part_SN" value="<?php echo $data['Fld_Part_SN'];?>">
                                        </div>
								</div>
								<div class="col-lg-1">
										<div class="form-group">
                                            <label>QTY</label>
                                            <input class="form-control" name="Fld_Qty" value="<?php echo $data['Fld_Qty'];?>">
                                        </div>
								</div>
								<div class="col-lg-2">
										<label>CONDITION</label>
                                            <select class="form-control" name="Fld_Condition_ID">
											<?php

											$sqldiv="SELECT distinct(Fld_Condition_Text),Fld_Condition_ID FROM tbl_Condition order by Fld_Condition_Text";
											
											//echo $sqldiv;
											$reqemp = mysql2_query($sqldiv);
											while($datadiv = mysqli_fetch_array($reqemp))
											{
												echo "<option value='".$datadiv ['Fld_Condition_ID']."'";
												if($datadiv ['Fld_Condition_ID']==$data['Fld_Condition_ID']) echo "selected";
												echo ">".$datadiv ['Fld_Condition_Text']."</option>";
											}
											?>
                                                
                                            </select>
								</div>	
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>RELEASE</label>
                                            <select class="form-control" name="Fld_Release_ID">
											<?php
											$sqldiv="SELECT * FROM tbl_Release order by Fld_Release_Text";
											
											//echo $sqldiv;
											$reqemp = mysql2_query($sqldiv);
											while($datadiv = mysqli_fetch_array($reqemp))
											{
												echo "<option value='".$datadiv['Fld_Release_ID']."'";
												if($datadiv['Fld_Release_ID']==$data['Fld_Release_ID']) echo "selected";
												echo ">".$datadiv ['Fld_Release_Text']."</option>";
											}
											?>
                                            </select>
                                        </div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>TAG INFO</label><br>
											<?php
											//recuperation du nom de compagnie TAG INFO ********************
											$sqltiid="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID=".$data['Fld_Tag_Info_ID'];
											$reqtiid = mysql2_query($sqltiid);
											$datatiid = mysqli_fetch_array($reqtiid);
											//Fin recuperation du nom de compagnie TAG INFO ********************
											?>
											<input type="text" name="companyidtaginfo" id="companyidtaginfo" class="companyidtaginfo" placeholder="<?php echo $datatiid['Fld_Company_Name'];?>" >
                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>TAG DATE (JJ/MM/AAAA)</label>
                                            <input class="form-control" name="Fld_Tag_Date" value="<?php echo $data['Fld_Tag_Date'];?>">
                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>TRACEABILITY</label><br>
											<!--Traceability sont les noms de compagnie-->
											<?php
											//recuperation du nom de compagnie TRACABILITY ********************
											$sqltrac="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID=".$data['Fld_Traceability_ID'];
											$reqtrac = mysql2_query($sqltrac);
											$datatrac = mysqli_fetch_array($reqtrac);
											//Fin recuperation du nom de compagnie TRACABILITY ********************
											?>
                                            <input type="text" name="companyidtreacability" id="companyidtreacability" class="companyidtreacability" placeholder="<?php echo $datatrac['Fld_Company_Name'];?>" >
                                        </div>
									</div>
							</div>

							<div class="row">
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>LEAD TIME</label>
											<input class="form-control" name="lead_time" value="<?php echo $data['lead_time'];?>">
                                    </div>
                                </div>
								<div class="col-lg-1">
										<div class="form-group">
                                            <label>DELIVERY </label><!--(number of days)-->
                                            <input class="form-control" name="Fld_Delivery" value="<?php echo $data['Fld_Delivery'];?>">
                                        </div>
								</div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>PRICE</label>
                                            <input class="form-control" name="Fld_Price" value="<?php echo $data['Fld_Price'];?>">
                                        </div>
									</div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>$/€</label>
                                            <select class="form-control" name="Fld_Currency_ID">
											<?php
											//recuperation du nom de la currency	
											// Fld_Currency_ID    Fld_Currency_Text
											$sqldiv="SELECT * FROM tbl_Currency";
											
											//echo $sqldiv;
											$reqemp = mysql2_query($sqldiv);
											while($datadiv = mysqli_fetch_array($reqemp))
											{
												echo "<option value='".$datadiv['Fld_Currency_ID']."'";
												if($datadiv['Fld_Currency_ID']==$data['Fld_Currency_ID']) echo "selected";
												echo ">".$datadiv['Fld_Currency_Text']."</option>";
											}
											?>
                                                
                                            </select>
                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>PAYMENT TERM</label>
                                            <select class="form-control" name="Fld_Payment_Term_ID">
											<?php
											//recuperation Payment_Term
											// ** tbl_Payment ** Fld_Payment_Term_ID  Fld_Payment_Text
					                        $sqlpt="SELECT * FROM tbl_Payment order by Fld_Payment_Text";
											
											$reqpt = mysql2_query($sqlpt);
											while($datapt = mysqli_fetch_array($reqpt)){
												echo "<option value='".$datapt['Fld_Payment_Term_ID']."'";
												if($datapt['Fld_Payment_Term_ID']==$data['Fld_Payment_Term_ID']) echo "selected";
												echo ">".$datapt['Fld_Payment_Text']."</option>";
											}
					                        //Fin recuperation Payment_Term
											?>
                                                
                                            </select>
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>REMARK</label>
                                            
											<textarea class="form-control" rows="3" name="Fld_Remark"><?php echo $data['Fld_Remark'];?></textarea>
                                        </div>
									</div>
							</div>
							<div class="row">		
									
									
									
									
							</div>
							<div class="row">
									
									
									
							</div>
							<div class="row">
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>QTY RECEIVED</label>
											<input class="form-control" name="Fld_Qty_Received" value="<?php echo $data['Fld_Qty_Received'];?>">
                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>DATE RECEVED END REP</label>
											<input class="form-control" name="Fld_Date_RecevdEnd_REP" value="<?php echo $data['Fld_Date_RecevdEnd_REP'];?>">
                                        </div>
								</div>
							</div>
							<div class="row">
								
							</div>
							          

                        <!-- /.panel-body -->
									<button type="submit" class="btn btn-default">Validate</button>
						</form>
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-10 -->
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
	
	<script type="text/javascript" src="../js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
	<script type="text/javascript" src="../js/locales/bootstrap-datetimepicker.fr.js" charset="UTF-8"></script>

    <!-- Page-Level Demo Scripts - Tables - Use for reference -->
    <script type="text/javascript">
    $(document).ready(function() {
        $('#dataTables-example').DataTable({
            responsive: true
        });
    });
    </script>
<!--*****************************************************************************************************-->
<!--*****************************************************************************************************-->
<!--*****************************************************************************************************-->
<!--Ajout pour autocompression Roy-->
 <!--<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>-->
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
            border: 1px solid rgba(0, 0, 0, 0.2);
            border-radius: 8px 8px 8px 8px;
            font-size: 18px;
            color: #111;
            background-color: #F1F1F1;
        }
    </style>
    <script>
        $(document).ready(function() {

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
        })
<!--Fin Ajout pour autocompression Roy-->
<!--*****************************************************************************************************-->
<!--*****************************************************************************************************-->
<!--*****************************************************************************************************-->

<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!--Ajout nom contact a partir du nom de la societe-->
<!--*******************************************************************************-->
<!--*******************************************************************************-->
	function majtarea(id)
{
var bloccontactname=document.getElementById('bloccontactname');
var companyidval=document.getElementById('companyid').value;

bloccontactname.style.display='inline';

//document.getElementById("divcontactname").innerHTML='<div id="divcontactname" align="center"><img src="../images_design/Spin.gif" border="0"></div>';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "contactnamefromcompany-sq.php?id="+companyidval, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { up_contact_name(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
    
}
function up_contact_name(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divcontactname').innerHTML='<div id="'+id+'" align="center">';
         var resp2;
        resp2 = xhr.responseText;
        document.getElementById('divcontactname').innerHTML+=resp2;
    document.getElementById('divcontactname').innerHTML+='</div>';
    }
}

<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!-- Fin Ajout nom contact a partir du nom de la societe-->
<!--*******************************************************************************-->
<!--*******************************************************************************-->

<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!--Recuperation Description a partir du P/N-->
<!--*******************************************************************************--> 
<!--*******************************************************************************-->
	function descfrompn(id)
{
var blocdescription=document.getElementById('blocdescription');
var pnid=document.getElementById('pnid').value;

blocdescription.style.display='inline';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "descriptionfrompn.php?id="+pnid, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
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