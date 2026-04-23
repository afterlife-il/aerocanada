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

       

        <div id="<?php if($_SESSION['leftmenu']=='open') echo "page-wrapper"; else echo "page-wrapper2";?>">
            <div class="row">
                <div class="col-lg-12" align="center">

                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            PARTS <!--<a href="http://aerocanada-industries.com/adminaero/pages/ajout_parts.php">  -->
							<a data-toggle="modal" data-target="#myModal" style="cursor: pointer;"><img src="images/add.png" width="30"> +ADD A PN</a> 
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <table width="100%" class="table table-striped table-bordered table-hover" id="dataTablespars">
                                <thead>
                                    <tr>
                                        <?php
//Table tbl_Parts :::: Fld_Part_ID  Fld_Part_Nbr  Fld_Part_Desc  Fld_Part_MFG  Fld_Part_MFG_Old  Fld_AC_ID  Fld_Old_LP  Fld_Part_List_Price  Fld_Part_Price_Currency_ID  Fld_Part_LP_Date  Fld_Remark status alt_pn Fld_Add_PN_Date aci_contact_entry
?>
										<th>P/N</th>
										<th>ALT PN</th>
										<th>DESC</th>
										<th>MFG/OEM</th>
										<th>A/C</th>
										<th>LP</th>
										<th>$/€</th>
										<th>LP DATE</th>
										<th>REMARK</th>
										<?php 
						if($_SESSION['statut']=="SuperAdmin")
						{
							?>
										<th></th>
						<?php }
						?>
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

	<!--*************************************************************************************************************************************-->
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
            <div class="modal-header" STYLE="background-color: #A7142A;">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color:#FFFFFF;font-weight: bold;">×</button>
                <h4 class="modal-title" id="myModalLabel" style="color:#FFFFFF;font-weight: bold;">ADD A PART</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
					<div class="row">
						<div class="col-md-4">
							<label for="Fld_Part_Nbr" class="col-form-label">PN:</label>
							<input type="text" class="form-control" id="Fld_Part_Nbr" />
						</div>
						<div class="col-md-4">
							<label for="Fld_Part_Desc" class="col-form-label">DESCRIPTION:</label>
							<input type="text" class="form-control" id="Fld_Part_Desc" />
						</div>
					</div>	
				</div>
            </div>
			
			 <div class="modal-body">
                <div class="container-fluid">
					<div class="row">
						<div class="col-md-8">
							<label for="alt_pn" class="col-form-label">ALT PN:</label>
							<textarea class="form-control" rows="3" name="alt_pn" id="alt_pn"></textarea>
						</div>
					</div>	
				</div>
            </div>
			
			<div class="modal-body">
                <div class="container-fluid">
					<div class="row">
						<div class="col-md-4">
							<label for="Fld_Part_MFG" class="col-form-label">MFG/OEM:</label>
							<input type="text" name="Fld_Part_MFG" id="Fld_Part_MFG" size="30" class="Fld_Part_MFG" placeholder="Please Enter company">
						</div>
						<div class="col-md-4">
							<label for="oem_lead_time" class="col-form-label">OEM LEAD TIME:</label>
							<input type="text" class="form-control" id="oem_lead_time" />
						</div>
					</div>	
				</div>
            </div>
			
			<div class="modal-body">
                <div class="container-fluid">
					<div class="row">
						<div class="col-md-4">
							<label for="Fld_Part_List_Price" class="col-form-label">LIST PRICE:</label>
							<input type="text" class="form-control" name="Fld_Part_List_Price" id="Fld_Part_List_Price" size="30">
						</div>
						<div class="col-md-4">
							<label for="Fld_Part_Price_Currency_ID" class="col-form-label">OEM LEAD TIME:</label>
							<select class="form-control" name="Fld_Part_Price_Currency_ID" ID="Fld_Part_Price_Currency_ID">
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
						<div class="col-md-4">
							<label for="Fld_Part_LP_Date" class="col-form-label">LP DATE:</label>
							<input type="text" class="form-control" name="Fld_Part_LP_Date" id="Fld_Part_LP_Date" size="30">
						</div>
					</div>	
				</div>
            </div>
			
			<div class="modal-body">
                <div class="container-fluid">
					<div class="row">
						<div class="col-md-4">
							<label for="Fld_AC_ID" class="col-form-label">AIRCRAFT:</label>
							<select class="form-control" name="Fld_AC_ID" ID="Fld_AC_ID">
											<option value=""></option>
											<?php
											// Fld_AC_ID  Fld_AC_Model  Fld_AC_Series  Fld_AC_Manufacturer  Fld_AC_Engine_Model  Fld_AC_Engine_Series
											$sqldiv="SELECT Distinct(Fld_AC_Model),Fld_AC_ID FROM tbl_Aircraft order by Fld_AC_Model";
											
											//echo $sqldiv;
											$reqemp = mysql2_query($sqldiv);
											while($datadiv = mysqli_fetch_array($reqemp))
											{
												echo "<option value='".$datadiv ['Fld_AC_ID']."'>".$datadiv ['Fld_AC_Model']."</option>";
											}
											?>
                                                
                                            </select>
						</div>
						<div class="col-md-4">
							<label for="ata_chapter" class="col-form-label">ATA CHAPTER:</label>
							<input type="text" class="form-control" id="ata_chapter" />
						</div>
					</div>	
				</div>
            </div>
			 <div class="modal-body">
                <div class="container-fluid">
					<div class="row">
						<div class="col-md-8">
							<label for="Fld_Remark" class="col-form-label">REMARK:</label>
							<textarea class="form-control" rows="3" name="Fld_Remark" id="Fld_Remark"></textarea>
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
	<!--END POPUP ADD PN-->
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

    <!-- Page-Level Demo Scripts - Tables - Use for reference -->
    <script>
	
	
	
    $(document).ready(function() {
        $('#dataTablespars').DataTable({
        "responsive": true,
		"processing": true,
        "serverSide": true,
        "ajax": "partsdata.php"
        });
		
	oTable = $('#dataTablespars').DataTable();   //pay attention to capital D, which is mandatory to retrieve "api" datatables' object, as @Lionel said
$('#myInputTextField').keyup(function(){
      oTable.search($(this).val()).draw() ;
})
	
    });
	
	<!--*****************************************************************************************************-->	                                               
<!--****************************************ADD PN POPUP*************************************************-->
	$('#myModal').on('click', '.btn-primary', function(){
    var value = $('#Fld_Part_Nbr').val();
    var value2 = $('#Fld_Part_Desc').val();  
    var value3 = $('#alt_pn').val();
    var value4 = $('#Fld_Part_MFG').val();
    var value5 = $('#oem_lead_time').val();
    var value6 = $('#Fld_Part_List_Price').val();
    var value7 = $('#Fld_Part_Price_Currency_ID').val();
    var value8 = $('#Fld_Part_LP_Date').val();
    var value9 = $('#Fld_AC_ID').val();
    var value10 = $('#ata_chapter').val();
    var value11 = $('#Fld_Remark').val();
	//enregistrer le nouveau pn dans la base 
		 $.ajax({ url: 'add_pn_from_popup.php',
         data: {Fld_Part_Nbr: value, Fld_Part_Desc:value2, alt_pn:value3, Fld_Part_MFG:value4, oem_lead_time:value5, Fld_Part_List_Price:value6, Fld_Part_Price_Currency_ID:value7, Fld_Part_LP_Date:value8, Fld_AC_ID:value9, ata_chapter:value10, Fld_Remark:value11},
         type: 'get',
         success: function(output) {
                      // alert(output);
         }
});
         //FIN enregistrer le nouvea pn dans la base 
    $('#myModal').modal('hide');
});
<!--************************************END ADD PN POPOUP************************************************-->
<!--*****************************************************************************************************-->
<!--*****************************************************************************************************-->
	</script>
<!--Ajout pour autocompression Roy-->

    <script src="js/typeahead.js"></script>
    <style>
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
        .Fld_Part_MFG {
            border: 1px solid #CCCCCC;
            <!--border-radius: 8px 8px 8px 8px;-->
            font-size: 24px;
            <!--height: 45px;-->
            line-height: 30px;
            outline: medium none;
            padding: 8px 12px;
            width: 100%;
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

            $('input.Fld_Part_MFG').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'

            });

        })
    
<!--Fin Ajout pour autocompression Roy-->



<!--Details company-->	
function detailcompany(id)
{
var bloc=document.getElementById('bloccompany');
//if(bloc.style.display=='table-row') bloc.style.display='none';
//else
    {
bloc.style.display='table-row';

document.getElementById("divcompany").innerHTML='<div id="divcompany" align="center"><img src="../images/loader.gif" border="0"></div>';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "detailcompany.php?id="+id, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { up_donnee_courrier(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
    }
}
function up_donnee_courrier(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divcompany').innerHTML='<div id="'+id+'" align="center">';
         var resp;
        resp = xhr.responseText;
        document.getElementById('divcompany').innerHTML+=resp;
    document.getElementById('divcompany').innerHTML+='</div>';
	document.location.href="#bloccompany";//je redirige le lien vers le haut de la banniere (l'ancre haut)
    }
}


function fermeturedetailcompany()
{
var bloc=document.getElementById('bloccompany');
if(bloc.style.display=='table-row') bloc.style.display='none';
}
<!--Fin Details company-->


    </script>

</body>

</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=".$_SERVER['REQUEST_URI']."\">";
?>