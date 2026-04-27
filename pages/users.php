<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
if($_SESSION['conectroy']=="parfait"){
require('../classes/users.class.php');
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

 

          <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">

            <div class="row">
                <div class="col-lg-12">
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
			<?php 
			if($_SESSION['statut']=="SuperAdmin")
						{
			?>
            <div class="row">
                <div class="col-lg-12">
                   <div class="panel panel-default">
                        <div class="panel-heading">
                            USERS<a href="javascript:adduser()"><img src="images/add-user.png" width="50"> + Add a user</a>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">

                            <div class="table-responsive">
							<form action='validation_users.php' method="post">
                                <table class="table table-striped table-bordered table-hover" id="mytable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Employee Name</th>
                                            <th>PW</th>
                                            <th>Tel</th>
                                            <th>Mobile</th>
                                            <th>Email</th>
                                            <th>Skype</th>
                                            <th>Position</th>
                                            <th>Permission profile</th>
                                            <th>MDP GMAIL</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
									<?php
									$z=0;
									//tbl_Employee---- Employee_ID Employee_Name Fld_Contact_Id pw email statut  position  tel  mobile  skype numformat  pwgmaero
									$objet=new users();
									$donnee = $objet->affichage_users();
									foreach($donnee as $dataemp)
									{
									$z++;
									$varsuivante=$z+1;
								
                                       $pw_display = (substr($dataemp['pw'], 0, 4) === '$2y$' || substr($dataemp['pw'], 0, 4) === '$2a$') ? '••••••••' : $dataemp['pw'];
                                       echo "<tr class=\"odd gradeX\" id=\"row_".$dataemp["Employee_ID"]."\"><td>".$dataemp['Employee_ID']."</td><td>".$dataemp['Employee_Name']."</td><td>".$pw_display."</td><td>".$dataemp['tel']."</td><td>".$dataemp['mobile']."</td>
									   <td>".$dataemp['email']."</td><td>".$dataemp['skype']."</td><td>".$dataemp['position']."</td><td>".$dataemp['statut']."</td><td>".$dataemp['pwgmaero']."<input type=\"hidden\" name=\"nbusers\" id=\"nbusers\" value=\"".$z."\"></td>";
									   echo "<td><a href=\"javascript:modif_company(".$dataemp["Employee_ID"].")\"><i style=\"margin-left:10px;position: relative;top: 4px;font-size:23px;\" class=\"fa  fa-pencil-square-o\"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href='javascript:supp_user(".$dataemp["Employee_ID"].",".$z.")' onClick=\"return(confirm('Etes vous sur ?'));\"><img src='images/bin-blue-full-icon.png' border='0' width='27'></a></td>";
									   echo "</tr>";
									}?>       
                                    </tbody>
                                </table>
								</form>
                            </div>
                            <!-- /.table-responsive -->
							<div style="display:none" id="blocuser"><div id="divuser" align="center"></div></div>  
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
						<?php }?>
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
	<!--Ajout utilisateur-->
	//fonction qui ajoute une ligne en fin de tableau pour ajouter une adresse
	function adduser(){
    var cell, ligne;
 
    // on recupere l'identifiant (id) de la table qui sera modifiee
    var tableau = document.getElementById("mytable");
    // nombre de lignes dans la table (avant ajout de la ligne)
    var nbLignes = tableau.rows.length;
 
    ligne = tableau.insertRow(-1); // creation d'une ligne pour ajout en fin de table
                                   // le parametre est dans ce cas (-1)
    ligne.id='row_'+eval(nbLignes+1);
    // creation et insertion des cellules dans la nouvelle ligne creee
    cell = ligne.insertCell(0);
    //cell.innerHTML = "Ligne " + nbLignes + " Cellule 0";
    cell.innerHTML = eval(nbLignes+1);
	
	cell = ligne.insertCell(1);
    cell.innerHTML = "<input class=\"form-control\" name=\"Employee_Name\" id=\"Employee_Name\" placeholder=\"User Name\">";
	
	cell = ligne.insertCell(2);
    cell.innerHTML = "<input class=\"form-control\" name=\"pw\" id=\"pw\" placeholder=\"pw\">";
	
	cell = ligne.insertCell(3);
    cell.innerHTML = "<input class=\"form-control\" name=\"tel\" id=\"tel\" placeholder=\"tel\">";
	
	cell = ligne.insertCell(4);
    cell.innerHTML = "<input class=\"form-control\" name=\"mobile\" id=\"mobile\" placeholder=\"mobile\">";
	
	cell = ligne.insertCell(5);
    //cell.innerHTML = "Ligne " + nbLignes + " Cellule 3";
    cell.innerHTML = "<input class=\"form-control\" name=\"email\" id=\"email\" placeholder=\"E-mail\">";
	
	cell = ligne.insertCell(6);
    cell.innerHTML = "<input class=\"form-control\" name=\"skype\" id=\"skype\" placeholder=\"skype\">";
	
	cell = ligne.insertCell(7);
    cell.innerHTML = "<input class=\"form-control\" name=\"position\" id=\"position\" placeholder=\"position\">";
	
	cell = ligne.insertCell(8);
    //cell.innerHTML = "Ligne " + nbLignes + " Cellule 3";
    cell.innerHTML = "<select name=\"statut\" id=\"statut\" class=\"form-control\"><option value=''>-- Choose Permission profile --</option><option value='SuperAdmin'>SuperAdmin</option><option value='Admin'>Admin</option><option value='Salesman'>Salesman</option></select><input type='hidden' name='nbusers' value='"+nbLignes+"'>";
	
	cell = ligne.insertCell(9);
    cell.innerHTML = "<input class=\"form-control\" name=\"pwgmaero\" id=\"pwgmaero\" placeholder=\"Mot de passe gmail\">";
	
	cell = ligne.insertCell(10);
    cell.innerHTML = "<input type='submit' value='submit' class=\"form-control\">";
}
<!--Fin Ajout utilisateur-->

<!--Supp user-->
function supp_user(id,nbligne){
        if (id > 0) {
            //Execution du script PHP avec Ajax
            $('#mytable tr[id="row_' + id + '"] td').css({
                        'backgroundImage': 'none',
                        'backgroundColor': 'white',
                    });
                    $('#mytable tr[id="row_' + id + '"] td').animate({
                        'backgroundColor': '#ff8888',
                        'color': '#941010'
                    }, 1000);
            $.get('del_user.php', { // lien de la page qui permet la suppression
                idsup:id //variable de type GET (on recuperera la variable avec $_GET'idsup'])
            }, function(data){
                //si la requete s'est bien deroulee
             /*   if (data == '1') {*/
                    $('#mytable tr[id="row_' + id + '"] td').fadeTo("slow", 0, function(){
                        $(this).hide();
                    });
			
                /*} else{
                    alert('Probleme de connexion a la base de donnee');
                }*/
            });

			document.getElementById("Employee_Name"+nbligne).value = '0';
        }
    }
<!--Fin Supp user-->
<!--Modification company-->
function modif_company(id)
{
var bloc=document.getElementById('blocuser');
//if(bloc.style.display=='table-row') bloc.style.display='none';
//else
    {
bloc.style.display='table-row';

document.getElementById("divuser").innerHTML='<div id="divuser" align="center"><img src="../images/loader.gif" border="0"></div>';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "modif_user.php?id="+id, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { up_donnee_courrier(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
    }
}
function up_donnee_courrier(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divuser').innerHTML='<div id="'+id+'" align="center">';
         var resp;
        resp = xhr.responseText;
        document.getElementById('divuser').innerHTML+=resp;
    document.getElementById('divuser').innerHTML+='</div>';
	document.location.href="#blocuser";//je redirige le lien vers le haut de la banniere (l'ancre haut)
    }
}

<!--Fin Modification company-->
    </script>

</body>

</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=".$_SERVER['REQUEST_URI']."\">";
?>