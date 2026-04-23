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

 
        <div id="page-wrapper">
<div class="panel-heading" style="background:#be0831; color:#fff; font-weight:bold; text-transform:uppercase;">
      <?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?>
  COMPANY
</div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">

                        <!-- /.panel-heading -->
                        <div class="panel-body">
                           <table id="employee-grid"  class="table table-striped table-bordered table-hover" cellpadding="0" cellspacing="0" border="0" class="display" width="100%">
					<thead>
						<tr>
										<th>Logo Company</th>
                                        <th>ID Company</th>
                                        <th>COMPANY</th>
                                        <th>ACI 770 Contact</th>
										<th>RATING</th>
										<th></th>
										<th></th>
										

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

    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!-- Page-Level Demo Scripts - Tables - Use for reference -->
   <script language="JavaScript" type="text/javascript">
    $(document).ready(function() {
        $('#dataTables-example').DataTable({
            responsive: true
        });
    });

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

<!--Statut contact company-->

function statutcontact(id){
        if (id > 0) {
            //Execution du script PHP avec Ajax
            $('#mytable tr[id="row_' + id + '"] td').css({
                        'backgroundImage': 'none',
                        'backgroundColor': '#be0831',
						'color': '#ffffff',
                    });
            $.get('archiver_contact.php', { // lien de la page qui permet la suppression
                idsup:id //variable de type GET (on recuperera la variable avec $_GET['idsup'])
            }, function(data){
              
			  
            });
			document.getElementById("case"+ id).innerHTML=" <a href=javascript:desarchivercontact("+id+")>annuler</a>";

        }
    }

	function desarchivercontact(id){
        if (id > 0) {
            //Execution du script PHP avec Ajax
            $('#mytable tr[id="row_' + id + '"] td').css({
                        'backgroundImage': 'none',
                        'backgroundColor': '#ffffff',
						'color': '#333333',
                    });
            $.get('desarchivercontact.php', { // lien de la page qui permet la suppression
                idsup:id //variable de type GET (on recuperera la variable avec $_GET['idsup'])
            }, function(data){
              
			  
            });
			document.getElementById("case"+ id).innerHTML=" <a href=javascript:statutcontact("+id+")><i style=\"margin-left:10px;position: relative;top: 4px;font-size:23px;\" class=\"fa  fa-archive\"></i></a>";

        }
    }
function majtarea(id){
 
    var selection = document.getElementById("recupmessageremark").value;
    $.get('majremarkcontact.php', { // lien de la page qui permet la suppression
                id_company_contact:id,Fld_Contact_Remark:selection //variable de type GET (on recuperera la variable avec $_GET['idsup'])
            }, function(data){
              
			  
            });
/*	alert('select :'+selection);*/
 
				}  
    </script>
	
	<!--Gestion de la base serverside Roy--
		<script type="text/javascript" language="javascript" src="Contacts/js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="Contacts/js/jquery.dataTables.js"></script>-->
		<script type="text/javascript" language="javascript" >
			$(document).ready(function() {
				var dataTable = $('#employee-grid').DataTable( {
					"processing": true,
					"serverSide": true,
					"ajax":{
						url :"company22.php", // json datasource
						type: "post",  // method  , by default get
						error: function(){  // error handling
							$(".employee-grid-error").html("");
							$("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="7">No data found in the server</th></tr></tbody>');
							$("#employee-grid_processing").css("display","none");
							
						}
					}
				} );
			} );
		</script>
		<!--Fin Gestion de la base serverside Roy-->

</body>

</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php\">";
?>