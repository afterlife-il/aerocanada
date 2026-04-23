<?php
//add_rfq.php
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
	
			<!--CSS rating ajoute par roy-->
			<link href="rating.css" rel="stylesheet">
			<!--Fin CSS rating ajoute par roy-->
</head>

<body>
    <div id="wrapper">
  <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
    <?php include "top_menu.php"; ?>                       <!-- barre rouge -->
    <?php if(isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
</nav>
<?php include "after_nav.php"; ?>

		<?php 
		//****tbl_RFQ_2******ID  Fld_RFQ_ID  Fld_Supplier_ID  Fld_Qty  Fld_Condition_ID  Fld_Payment_Term_ID  Fld_Delivery  Fld_Price  Fld_Price_Max  Fld_Price_Min  Fld_Currency_ID  Fld_Traceability_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Release_ID  Fld_Part_ID  Fld_Remark  Fld_IsBeen_Chosen  Fld_Current_Date  Fld_Qty_Received  Fld_Part_SN  Fld_Supplier_Contact_ID  Fld_Date_RecevdEnd_REP
		?>
          <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">

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
						    ADD RFQ
						</div>

						<?php
						// RFQ ID généré une seule fois
						$rfq_id  = date("Y-m-d-His");
						$rfq_date = date("d/m/Y");
						?>

						<form method="post" name="Form1" id="Form1" role="form"><!-- valid_add_rfq -->

					  <input type="hidden" name="action" value="add_multi_pn_rfq">
					  <input type="hidden" name="Fld_Part_ID_hidden" id="Fld_Part_ID_hidden" value="">
					  <input type="hidden" name="Fld_Customer_ID" id="Fld_Customer_ID" value="">
					  <input type="hidden" name="Fld_Part_ID"     id="Fld_Part_ID"     value="">
					  <input type="hidden" name="part_id"           value="<?php echo (int)($_GET['part_id'] ?? 0); ?>">


                        <div class="panel-body">
                           <div class="row">
								<div class="col-lg-3">
										<div class="form-group">
                                           <label>RFQ ID</label>
									<input 
									    class="form-control" 
									    value="<?php echo htmlspecialchars($rfq_id); ?>" 
									    readonly
									    style="background:#eee; cursor:not-allowed;"
									>
									<input type="hidden" name="Fld_RFQ_ID" value="<?php echo htmlspecialchars($rfq_id); ?>">

									...

									<label>DATE</label>
									<input 
									    class="form-control" 
									    name="RFQ_DATE" 
									    value="<?php echo htmlspecialchars($rfq_date); ?>"
									>
                                    </div>
								</div>
								<div class="col-lg-6">
								</div>
							</div>
							<div class="row">
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>RFQ TYPE</label>
											<select class="form-control" name="Fld_RFQ_Type_ID">
											<?php
											//recuperation RFQ Type 
											// ** tbl_RFQ_Type ** Fld_RFQ_Type_ID  Fld_RFQ_Type_Text
					                        $sqlrfqt="SELECT * FROM tbl_RFQ_Type order by Fld_RFQ_Type_Text";
											
											$reqrfqt = mysql2_query($sqlrfqt);
											while($datarfqt = mysqli_fetch_array($reqrfqt)){
												echo "<option value='".$datarfqt['Fld_RFQ_Type_ID']."'>".$datarfqt['Fld_RFQ_Type_Text']."</option>";
											}
					                        //Fin recuperation RFQ Type
											?>
                                                
                                            </select>
                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>PRIORITY</label>
											<select class="form-control" name="Fld_Priority_ID">
											<?php
											//recuperation Priority
											// ** tbl_Priority ** Fld_Priority_ID  Fld_Priority_Text
					                        $sqlPriority="SELECT * FROM tbl_Priority";
											
											$reqPriority = mysql2_query($sqlPriority);
											while($dataPriority = mysqli_fetch_array($reqPriority)){
												echo "<option value='".$dataPriority['Fld_Priority_ID']."'>".$dataPriority['Fld_Priority_Text']."</option>";
											}
					                        //Fin recuperation Priority
											?>
                                                
                                            </select>
                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>SALES CONTACT</label>
											<select class="form-control" name="Employee_ID">
											<?php
											//recuperation des types de compagnie
					                        $sqlemp="SELECT distinct(Employee_Name),Employee_ID FROM tbl_Employee";
											
											$reqemp = mysql2_query($sqlemp);
											while($dataemp = mysqli_fetch_array($reqemp)){
												echo "<option value='".$dataemp['Employee_ID']."'";
												if ($dataemp['Employee_ID']==$_SESSION['id_utilisateur']) echo "selected";
												echo ">".$dataemp ['Employee_Name']."</option>";
											}
					                        //Fin recuperation des type de compagnie
											?>
                                                
                                            </select>
										</div>
								</div>
							</div>

						   <div class="row">
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>CUSTOMER'S NAME</label><a data-toggle="modal" data-target="#myModalCompany" style="cursor: pointer;"><i style='margin-left:10px;top: 4px;font-size:23px;' class='fa fa-plus-circle '></i></a><br>
											<input type="text" name="companyid" id="companyid" class="companyid" placeholder="Please Enter company" >
                                        </div>
								</div>
								<div class="col-lg-3" id='bloccontactname'>
										<div class="form-group" id='divcontactname'>
                                            <label>CONTACT NAME</label>
											<select class="form-control" name="id_company_contact">
											    <option>CHOOSE CONTACT</option>
											</select>
                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>TERMS</label>
											<select class="form-control" name="Fld_Payment_Term_ID">
											<?php
											//recuperation des TERMS
											// tbl_Payment****** Fld_Payment_Term_ID  Fld_Payment_Text
											
					                        $sqlptid="SELECT * FROM tbl_Payment";
											
											$reqptid = mysql2_query($sqlptid);
											while($dataptid = mysqli_fetch_array($reqptid)){
												echo "<option value='".$dataptid['Fld_Payment_Term_ID']."'>".$dataptid['Fld_Payment_Text']."</option>";
											}
					                        //Fin recuperation des TERMS
											?>
                                                
                                            </select>
                                        </div>
								</div>
								<div class="col-lg-3">
								</div>
							</div>
							 <div class="row">
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>PN</label><a data-toggle="modal" data-target="#myModal" style="cursor: pointer;"><i style='margin-left:10px;top: 4px;font-size:23px;' class='fa fa-plus-circle '></i></a><br>
											<input type="text" name="pnid" id="pnid" class="pnid" placeholder="Please Enter P/N" required>
                                        </div>
									</div>
									<div class="col-lg-3" id='blocdescription'>
										<div class="form-group" id='divdescription'>
                                            <label>DESCRIPTION</label>
											<input class="form-control" name="description" onclick="javascript:descfrompn();">
                                        </div>
                                    </div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>QTY</label> 
                                            <input class="form-control" name="Fld_Qty" id="Fld_Qty" value="1">
                                        </div>
                                    </div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>CONDITION</label>
                                            <select class="form-control" name="Fld_Condition_ID">
											<option></option>
											<?php
											//recuperation condition 
											// ** tbl_Condition ** Fld_Condition_ID  Fld_Condition_Text
					                        $sqlc="SELECT * FROM tbl_Condition order by Fld_Condition_Text";
											
											$reqc = mysql2_query($sqlc);
											while($datac = mysqli_fetch_array($reqc)){
												echo "<option value='".$datac['Fld_Condition_ID']."'>".$datac ['Fld_Condition_Text']."</option>";
											}
					                        //Fin recuperation condition 
											?>
                                                
                                            </select>
                                        </div>
                                    </div>
							</div>
						   <div class="row">
									<div class="col-lg-6">
										<div class="form-group">
                                            <label>REMARKS</label>
											<!--!!!!!!! Surtout ne pas mettre les informations de ramarque de la table stock par ce que ce sont des infos interne de la boite qui ne doivent pas arriver au client!!!!!!!-->
                                            <textarea class="form-control" rows="3" name="Fld_Remark_rfq" id="Fld_Remark_rfq"></textarea>
                                        </div>
                                    </div>
									<div class="col-lg-6">
                                    </div>
						   </div>
						   <div class="row">
						   <div class="col-lg-4">
										<div class="form-group" align="right">
										<INPUT type="button" value="OPEN RFQs" name=button1 onclick="return OnButton1();" class="btn btn-success">
										</div>
								</div>	
								<div class="col-lg-4">
										<div class="form-group" align="right">
										<INPUT type="button" value="ADD RFQ" name=button2 onclick="return OnButton2();" class="btn btn-success">
										</div>
								</div>
								<div class="col-lg-4">
										<div class="form-group" align="right">
										<INPUT type="button" value="ADD Multiples RFQS" name=button2 onclick="return OnButton3();" class="btn btn-success">
										</div>
								</div>	
								

						   </div>						   
                        </div>
                        <!-- /.panel-body -->
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

	<!--*************************************************************************************************************************************-->
	<!--POPUP ADD PN-->
	
	<script type="text/javascript">
    $('#myModal').on('hidden.bs.modal', function (e) {
        //get value from #myPopupInput and set the value to #myMainPageInput
    })
</script>
	
	
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel">ADD PN</h4>
            </div>
            <div class="modal-body">
                PN:
                <input type="text" class="form-control" id="myPopupInputpn" />
            </div>
			
			 <div class="modal-body">
                DESCRIPTION:
                <input type="text" class="form-control" id="myPopupInputdescription" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>
	<!--END POPUP ADD PN-->
	<!--*************************************************************************************************************************************-->
	
<!--*************************************************************************************************************************************-->
	<!--*************************************************************************************************************************************-->
	<!--POPUP ADD A COMPAGNIE-->
	
	<script type="text/javascript">
    $('#myModalCompany').on('hidden.bs.modal', function (e) {
        //get value from #myPopupInput and set the value to #myMainPageInput
    })
</script>
	
	
	<div class="modal fade" id="myModalCompany" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" STYLE="background-color: #A7142A;">
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
							<label for="myPopupInputcompanyname" class="col-form-label">CONTACT NAME:</label>
							<input type="text" class="form-control" id="myPopupInputcompanycontactname" />
						</div>
						<div class="col-md-4">
							<label for="myPopupInputdescription" class="col-form-label">E-MAIL:</label>
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
	<!--END POPUP ADD A COMPAGNIE-->
	<!--*************************************************************************************************************************************-->
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
	<script src="../js/typeahead.js"></script>

	<!-- <script type="text/javascript" src="../js/bootstrap-datetimepicker.js" charset="UTF-8"></script> -->
	<!-- <script type="text/javascript" src="../js/locales/bootstrap-datetimepicker.fr.js" charset="UTF-8"></script> -->

    <!-- Page-Level Demo Scripts - Tables - Use for reference -->
    <script type="text/javascript">

    $(document).ready(function() {
        $('#dataTables-example').DataTable({
            responsive: true
        });
    });
<!--*****************************************************************************************************-->
<!--*****************************************************************************************************-->	
<!--****************************************ADD PN*******************************************************-->
	$('#myModal').on('click', '.btn-primary', function(){
    var value = $('#myPopupInputpn').val();
    var value2 = $('#myPopupInputdescription').val();
	//enregistrer le nouveau pn dans la base 
		 $.ajax({ url: 'add_pn.php',
         data: {pn: value, desc:value2},
         type: 'get',
         success: function(output) {
                      // alert(output);
         }
});
         //FIN enregistrer le nouvea pn dans la base 
    $('#myModal').modal('hide');
});
<!--************************************END ADD PN*******************************************************-->
<!--*****************************************************************************************************-->
<!--*****************************************************************************************************-->	
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

    // ⇨ Quand tu quittes le champ company (Tab ou clic ailleurs) → charge les contacts
    $('input.companyid').on('blur', function () {
        majtarea('divcontactname');
    });

    // ⇨ Si tu fais "Enter" dans le champ company → charge aussi les contacts
    $('input.companyid').on('keypress', function(e){
        if (e.which === 13) {
            e.preventDefault();
            majtarea('divcontactname');
        }
    });

    // ⇨ Quand le PN perd le focus → charge la description
    $('#pnid').on('blur', function () {
        descfrompn('divdescription');
    });

    // ⇨ Si tu fais "Enter" dans le champ PN → charge aussi la description
    $('#pnid').on('keypress', function(e){
        if (e.which === 13) {
            e.preventDefault();
            descfrompn('divdescription');
        }
    });
});
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
                   
            xhr.open("POST", "contactnamefromcompany-rfq.php?id="+companyidval, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
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

<!--*****************************************************************************************************-->	
<!--****************************************ADD A COMPANY POPUP******************************************-->
	$('#myModalCompany').on('click', '.btn-primary', function(){
    var value = $('#myPopupInputcompanyname').val();
    var value2 = $('#myPopupInputcompanycontactname').val();  
    var value3 = $('#myPopupInputcompanycontactemail').val();
	//enregistrer le nouveau pn dans la base 
		 $.ajax({ url: 'add_company_from_popup.php',
         data: {companyname: value, contactname:value2, contactemail:value3},
         type: 'get',
         success: function(output) {
                      // alert(output);
         }
});
         //FIN enregistrer le nouvea pn dans la base 
    $('#myModalCompany').modal('hide');
});
<!--************************************END ADD A COMPANY POPOUP*****************************************-->
<!--*****************************************************************************************************-->
<!--*****************************************************************************************************-->

<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!-- ****************** DIFFERENTS ACTION SUR BOUTON SUBMIT************************-->
<!--*******************************************************************************-->
<!--*******************************************************************************-->
function OnButton1()
{
    document.Form1.action = "rfq-list.php"
    document.Form1.target = "_self";    // Open in a new window
    document.Form1.submit();             // Submit the page
    return true;
}

function OnButton2()
{
    document.Form1.action = "valid_add_rfq.php"
    document.Form1.target = "_self";    // Open in a new window
    document.Form1.submit();             // Submit the page
    return true;
}
function OnButton3()
{
    document.Form1.action = "valid_add_multi_pn_rfq.php"
    document.Form1.target = "_self";    // Open in a new window
    document.Form1.submit();             // Submit the page
    return true;
}
-->
<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!-- ****************** FIN DIFFERENTS ACTION SUR BOUTON SUBMIT********************-->
<!--*******************************************************************************-->
<!--*******************************************************************************-->
</script>


<script>
(function(){
  // Extrait un entier "...,123" ou "... - 123"
  function extractIdLabel(s){
    if(!s) return '';
    var m = String(s).match(/[,|\-]\s*(\d+)\s*$/);
    return m ? m[1] : '';
  }

  var form = document.forms['Form1'] || document.getElementById('Form1');
  if(!form) return;

  // visibles
  var compInput = document.getElementById('companyid');           // champ société (typeahead)
  var pnInput   = document.getElementById('pnid')                 // si tu en as un
                || document.getElementById('pn_rfq')              // sinon souvent c’est pn_rfq
                || (form.querySelector('input[name="pn_rfq"]') || null);

  // cachés
  var hidCust = document.getElementById('Fld_Customer_ID');
  var hidPart = document.getElementById('Fld_Part_ID');

  // au blur on remplit
  if (compInput && hidCust) compInput.addEventListener('blur', function(){
    var id = extractIdLabel(compInput.value);
    if (id) hidCust.value = id;
  });
  if (pnInput && hidPart) pnInput.addEventListener('blur', function(){
    var id = extractIdLabel(pnInput.value);
    if (id) hidPart.value = id;
  });

  // sécurité : juste avant l’envoi
  form.addEventListener('submit', function(){
    if (compInput && hidCust && !hidCust.value) {
      var id = extractIdLabel(compInput.value);
      if (id) hidCust.value = id;
    }
    if (pnInput && hidPart && !hidPart.value) {
      var id = extractIdLabel(pnInput.value);
      if (id) hidPart.value = id;
    }
  });
})();
</script>

<script>
$(function() {
  // company
  $('input.companyid').bind('typeahead:select', function(ev, suggestion) {
    // si suggestion = { id: 123, value: "ACME" }
    $('#Fld_Customer_ID').val(suggestion.id || suggestion.Fld_Company_ID || '');
     // dès qu'on choisit une société dans la liste → on rafraîchit la liste des contacts
    majtarea('divcontactname');
  });

  // PN
  $('input.pnid').bind('typeahead:select', function(ev, suggestion) {
    // suggestion = { id: 104113, value: "1712507C" } par ex.
    $('#Fld_Part_ID_hidden').val(suggestion.id || suggestion.Fld_Part_ID || '');
    // et on met à jour la description
    descfrompn('divdescription');
  });
});
</script>

</body>

</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=".$_SERVER['REQUEST_URI']."\">";
?>