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
                            ADD SUPPLIERS QUOTE
                        </div>
						<form id="formajoutsq" role="form" method="post" action="sendemailnewsletter.php" enctype="multipart/form-data">
						<?php $today = date("Y-m-d");?>
						<input type="hidden" name="Fld_Current_Date" value="<?php echo $today;?>">
						<input type="hidden" name="aci_contact" value="<?php echo $_SESSION['id_utilisateur'];?>">
                        <div class="panel-body">
                            <div class="row">
							
							<div class="col-lg-2" id='blocaddsq'>
								<div id='divaddsq'>
										<div class="form-group">
                                            <label>SENDING GROUP</label>
                                            <select class="form-control" name="id_groupe_newsletter" id="id_groupe_newsletter">
				<?php
									//** tbl_groupe_newsletter ** id_groupe_newsletter group_name
									$sqldiv="SELECT * FROM tbl_groupe_newsletter";
									
									$reqemp = mysql2_query($sqldiv);
									while($datadiv = mysqli_fetch_array($reqemp))
									{
										echo "<option value='".$datadiv["id_groupe_newsletter"]."'>".$datadiv["group_name"]."</option>";
									}
				?>
                </select>
                                        </div>
								</div>
							</div>
								
							</div>

							
						</div>
							          

                        <!-- /.panel-body -->
									
						
<a data-toggle="modal" data-target="#myModaladdCompany" style="cursor: pointer;"><i style='margin-left:10px;top: 4px;font-size:23px;' class='fa fa-plus-circle '></i></a>
						<div class="table-responsive" style="min-height:500px;height:500px;overflow:auto;">
							
                                <table class="table" table-striped table-bordered table-hover" id="mytable">
                                    <thead>
                                        <tr>
                                            <th width="200">PN</th>
                                            <th>DESCRIPTION</th>

                                        </tr>
                                    </thead>
                                    <tbody>
									<?php
									for ($i=1; $i<=$_SESSION['countpnsessionnews']; $i++) {
										$sql="SELECT * from tbl_Parts where Fld_Part_ID='".$_SESSION['pnusedsessionnews'.$i]."'";
										$req = mysql2_query($sql);
										$data = mysqli_fetch_array($req);
										
										echo "<tr><td>".$data['Fld_Part_Nbr']."</td><td>".$data['Fld_Part_Desc']."</td></tr>";
									}
								     ?>
                                    </tbody>
                                </table>
								<button type="submit" class="btn btn-default">Validate</button>
								</form>
                            </div>
                            <!-- /.table-responsive -->
						
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
	<!--POPUP ADD A CPN-->
	
	<script type="text/javascript">
    $('#myModaladdCompany').on('hidden.bs.modal', function (e) {
        //get value from #myPopupInput and set the value to #myMainPageInput
    })
</script>
	
	
	<div class="modal fade" id="myModaladdCompany" tabindex="-1" role="dialog" aria-labelledby="myModaladdCompanyLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModaladdCompanyLabel">ADD A COMPANY</h4>
            </div>
            <div class="modal-body">
                <label>PN</label><br>
                <input type="text" name="Fld_Part_ID" id="Fld_Part_ID" class="Fld_Part_ID" placeholder="CHOOSE A PN">
            </div>
			
			 <div class="modal-body">
				<label>QTY</label>
				<input class="form-control" name="Fld_Qty_RFQ" id="Fld_Qty_RFQ" value="1">
            </div>
			
			<div class="modal-body">
				<label>CONDITION</label>
                                            <select class="form-control" name="Fld_Condition_ID" id="Fld_Condition_ID">
											<option></option>
											<?php
											//recuperation condition 
											// ** tbl_Condition ** Fld_Condition_ID  Fld_Condition_Text
					                        $sqlc="SELECT * FROM tbl_Condition order by Fld_Condition_Text";
											
											$reqc = mysql2_query($sqlc);
											while($datac = mysqli_fetch_array($reqc)){
												echo "<option value='".$datac['Fld_Condition_ID']."'";
												if ($datac['Fld_Condition_ID']=='1') echo " selected";
												echo ">".$datac ['Fld_Condition_Text']."</option>";
											}
					                        //Fin recuperation condition 
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
	<!--END POPUP ADD A PN-->
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
	
	<script type="text/javascript" src="../js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
	<script type="text/javascript" src="../js/locales/bootstrap-datetimepicker.fr.js" charset="UTF-8"></script>

    <!-- Page-Level Demo Scripts - Tables - Use for reference -->
    <script type="text/javascript">
    $(document).ready(function() {
        $('#dataTables-example').DataTable({
            responsive: true
        });
    });
	
	<!--*****************************************************************************************************-->	
<!--****************************************ADD A PN POPUP TO NEWSLETTER*************************************-->
	$('#myModaladdCompany').on('click', '.btn-primary', function(){
    var value = $('#Fld_Part_ID').val();
    var value2 = $('#Fld_Qty_RFQ').val();  
    var value3 = $('#Fld_Condition_ID').val();  
	//enregistrer le nouveau pn dans la base 
		 $.ajax({ url: 'add_pn_session_newsletter.php',
         data: {Fld_Part_ID: value, Fld_Qty_RFQ:value2, Fld_Condition_ID:value3},
         type: 'get',
         success: function(output) {
                      // alert(output);
         }
});
         //FIN enregistrer le nouvea pn dans la base 
    $('#myModaladdCompany').modal('hide');
	window.location.reload(true);
});
<!--************************************END ADD A PN POPUP TO NEWSLETTER*********************************-->
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
        .Fld_Part_ID {
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

            $('input.Fld_Part_ID').typeahead({
                name: 'Fld_Part_Nbr',
				id: 'Fld_Part_ID',
                remote: 'list-pn-select.php?query=%QUERY'
            });
        })
<!--Fin Ajout pour autocompression Roy-->
<!--*****************************************************************************************************-->
<!--*****************************************************************************************************-->
<!--*****************************************************************************************************-->


</script>
</body>

</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=".$_SERVER['REQUEST_URI']."\">";
?>