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
    <?php include "top_menu.php"; ?>  <!-- barre rouge avec SON burger -->
    <?php if(isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
</nav>
<?php include "after_nav.php"; ?>

  <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">
    <!-- ICI ton contenu de page (le panel ADDRESS TYPE, etc.) -->
    <div class="row"> 
  </div><!-- /page-wrapper|2 -->
</div><!-- /wrapper -->

    <!-- ton contenu -->
  </div>
</div>
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
                            ADD COMPANY
                        </div>
						<form id="formajoutcompany" role="form" method="post" action="valid_company.php" enctype="multipart/form-data">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-5">
                                        <div class="form-group">
                                            <label>Company Name</label>
                                            <input class="form-control" name="Fld_Company_Name">
                                        </div>
										<div class="form-group">
                                            <label>Website</label>
                                            <input class="form-control" name="internet">
                                        </div>
										<div class="form-group">
                                            <label>ACI 770 Contact</label>
                                            <select class="form-control" name="Employee_ID">
											<option>Select ACI 770 Contact</option>
											<?php
											//recuperation des types de compagnie
					                        $sqlemp="SELECT distinct(Employee_Name),Employee_ID FROM tbl_Employee";
											
											$reqemp = mysql2_query($sqlemp);
											while($dataemp = mysqli_fetch_array($reqemp)){
												echo "<option value='".$dataemp ['Employee_ID']."'";
												if ($dataemp['Employee_ID']==$_SESSION['id_utilisateur']) echo "selected";
												echo ">".$dataemp ['Employee_Name']."</option>";
											}
					                        //Fin recuperation des type de compagnie
											?>
                                                
                                            </select>
                                        </div>
										
										<div class="form-group">
                                            <label>Company Type</label>
                                            <select class="form-control" name="Fld_Company_Type_ID">
											<option></option>
											<?php
											//recuperation des types de compagnie
					                        $sqlctt="SELECT distinct(Fld_Company_Type_Text),Fld_Company_Type_ID FROM tbl_Company_Type order by Fld_Company_Type_Text";	
					                        $reqctt = mysql2_query($sqlctt);
					                        while($datactt = mysqli_fetch_array($reqctt)){
												echo "<option value='".$datactt['Fld_Company_Type_ID']."'>".$datactt['Fld_Company_Type_Text']."</option>";
											}
					                        //Fin recuperation des type de compagnie
											?>
                                                
                                            </select>
                                        </div>
										<div class="form-group">
                                            <label>VAT</label>
                                            <input class="form-control" name="Fld_VAT_Nbr">
                                        </div>
										<div class="form-group">
                                            <label>CAGE CODE #</label>
                                            <input class="form-control" name="cage_code">
                                        </div>
										<div class="form-group">
										<label>Rating the company</label>
										<ul class="notes-echelle">
										<li>
											<label for="note01" title="Note&nbsp;: 1 sur 5">1</label>
											<input type="radio" name="companyrating" id="note01" value="1" />
										</li>
										<li>
											<label for="note02" title="Note&nbsp;: 2 sur 5">2</label>
											<input type="radio" name="companyrating" id="note02" value="2" />
										</li>
										<li>
											<label for="note03" title="Note&nbsp;: 3 sur 5">3</label>
											<input type="radio" name="companyrating" id="note03" value="3" />
										</li>
										<li>
											<label for="note04" title="Note&nbsp;: 4 sur 5">4</label>
											<input type="radio" name="companyrating" id="note04" value="4" />
										</li>
										<li>
											<label for="note05" title="Note&nbsp;: 5 sur 5">5</label>
											<input type="radio" name="companyrating" id="note05" value="5" />
										</li>
										</ul> 
										</div>
										
										
								</div>	
								<div class="col-lg-5">
										 
										

										<div class="form-group">
                                            <label>CUSTOMER PAYMENT TERM</label>
                                            <select class="form-control" name="customer_payment_term_id">
											<?php
											//recuperation Payment_Term
											// ** tbl_Payment ** Fld_Payment_Term_ID  Fld_Payment_Text
					                        $sqlpt="SELECT * FROM tbl_Payment order by Fld_Payment_Text";
											
											$reqpt = mysql2_query($sqlpt);
											while($datapt = mysqli_fetch_array($reqpt)){
												echo "<option value='".$datapt['Fld_Payment_Term_ID']."'";
												if($datapt['Fld_Payment_Text']=='In advance') echo "selected";
												echo ">".$datapt['Fld_Payment_Text']."</option>";
											}
					                        //Fin recuperation Payment_Term
											?>
                                                
                                            </select>
											
                                        </div>
										   
										<div class="form-group">
                                            <label>CUSTOMER PAYMENT TERM AMOUNT</label>
                                            <input class="form-control" name="customer_payment_term_amount">
                                        </div>
										<div class="form-group">
                                            <label>CUSTOMER PAYMENT TERM CURRENCY $/€</label>
											 <select class="form-control" name="customer_payment_term_currencyid">
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
										<div class="form-group">
                                            <label>ACI PAYMENT TERM</label>
                                            <select class="form-control" name="aci_payment_term_id">
											<?php
											//recuperation Payment_Term
											// ** tbl_Payment ** Fld_Payment_Term_ID  Fld_Payment_Text
					                        $sqlpt="SELECT * FROM tbl_Payment order by Fld_Payment_Text";
											
											$reqpt = mysql2_query($sqlpt);
											while($datapt = mysqli_fetch_array($reqpt)){
												echo "<option value='".$datapt['Fld_Payment_Term_ID']."'";
												if($datapt['Fld_Payment_Text']=='In advance') echo "selected";
												echo ">".$datapt['Fld_Payment_Text']."</option>";
											}
					                        //Fin recuperation Payment_Term
											?>
                                                
                                            </select>
                                        </div>
										<div class="form-group">
                                            <label>ACI PAYMENT TERM AMOUNT</label>   
                                            <input class="form-control" name="aci_payment_term_amount">
                                        </div>
										<div class="form-group">
                                            <label>ACI PAYMENT TERM CURRENCY $/€</label>
											<select class="form-control" name="aci_payment_term_currencyid">
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
										<div class="form-group">
										<label>Logo du cabinet</label><br><br>
										<input type="file" name="logocompany" id="logocompany">
										
										</div>
								</div>
							</div>
							<!--Gestion des adresse company-->
						<div>
						<b><i><u>Address 1</u></i></b> <a href="javascript:ajoutaddcompany(2)">+Add address</a>
							<div class="row">
							
								<div class="col-lg-5">
								<div class="form-group">
                                            <label>Address Type</label>
                                            <select class="form-control" name="Fld_Company_Address_Type1">
											<option></option>
											<?php
											//recuperation des types de compagnie
											//** tbl_Division ** Fld_Division_ID  Fld_Division_Text
					                        $sqlctt="SELECT * FROM tbl_Division order by Fld_Division_Text";	
					                        $reqctt = mysql2_query($sqlctt);
					                        while($datactt = mysqli_fetch_array($reqctt)){
												echo "<option value='".$datactt['Fld_Division_ID']."'>".$datactt['Fld_Division_Text']."</option>";
											}
												
					                        //Fin recuperation des type de compagnie
											?>
                                                
                                            </select>
									</div>
									<div class="form-group">
                                            <label>Address Title</label>
                                            <input class="form-control" name="title_address1">
                                    </div>
									<div class="form-group">
                                            <label>Street</label>
                                            <input class="form-control" name="Fld_Company_Street1">
                                    </div>
									
									<div class="form-group">
                                            <label>City</label>
                                            <input class="form-control" name="Fld_Company_City1">
                                    </div>
									<div class="form-group">
                                            <label>Zip Code</label>
                                            <input class="form-control" name="Fld_Company_ZipCode1">
                                    </div>
									<div class="form-group">
                                            <label>State</label>
                                            <input class="form-control" name="Fld_Company_State1">
                                    </div>
									<div class="form-group">
                                            <label>Country</label>
                                            <input class="form-control" name="Fld_Company_Country1">
                                    </div>
									
									
								</div>
								<div class="col-lg-5">
									<div class="form-group">
                                            <label>E-mail</label>
                                            <input class="form-control" name="Fld_Company_Email1">
                                    </div>
									<div class="form-group">
                                            <label>Phone</label>
                                            <input class="form-control" name="Fld_Company_Phone1">
                                    </div>
									<div class="form-group">
                                            <label>Fax</label>
                                            <input class="form-control" name="Fld_Company_Fax1">
                                    </div>
									<div class="form-group">
                                            <label>Remark</label>
											<textarea class="form-control" rows="3" name="Fld_Remark1"></textarea>
                                    </div>
									<div class="form-group">
                                            <label>Timezone</label>
                                            <select class="form-control" name="UTC_timezone1">
											<?php
											//recuperation du fuseau horaire

											/**
											* Timezones list with GMT offset
											*
											* @return array
											* @link http://stackoverflow.com/a/9328760
											*/
											function tz_list() {
											$zones_array = array();
											$timestamp = time();
											foreach(timezone_identifiers_list() as $key => $zone) {
												date_default_timezone_set($zone);
												$zones_array[$key]['zone'] = $zone;
												$zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
											}
											return $zones_array;
											}
											
					                        //Fin recuperation du fuseau horaire
											?>
    											<option value="0">Please, select timezone</option>
    											<?php foreach(tz_list() as $t) { ?>
    											  <option value="<?php print $t['zone'] ?>">
    											    <?php print $t['diff_from_GMT'] . ' - ' . $t['zone'] ?>
    											  </option>
    											<?php } ?>
                                            </select><br>
											<b>Pour une info sur une zone horaire <a href="https://www.worldtimeserver.com/" target="_blank"><u>worldtimeserver.com</u></a></b>
										</div>
										
										<input type="hidden" name="Fld_Date_Of_First_Contact1" value="<?php echo date('d-M-y');?>" />
										
									
                                </div>
            
                                <!-- /.col-lg-5 (nested) -->
                            </div>
                            <!-- /.row (nested) -->
								<input type="hidden" name="nbaddcompany" value="1">
										<div style='display:none' id='bloc2'><div id='div2' align='left' style="background:#F8F8F8;padding:10px;"></div>
										</div>	
                        </div>
								<!--Fin Gestion des adresse company-->
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


		<!--Ajout Company-->
	function ajoutaddcompany(id)
{
var bloc=document.getElementById('bloc'+id);
if(bloc.style.display=='inline') bloc.style.display='none';
else
    {
bloc.style.display='inline';

document.getElementById("div"+id).innerHTML='<div id="div'+id+'" align="center"><img src="../images_design/Spin.gif" border="0"></div>';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "addcompanyadd.php?id="+id, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { up_donnee_company(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
    }
}
function up_donnee_company(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('div'+id).innerHTML='<div id="'+id+'" align="center">';
         var resp;
        resp = xhr.responseText;
        document.getElementById('div'+id).innerHTML+=resp;
    document.getElementById('div'+id).innerHTML+='</div>';
    }
}
<!--Fin Ajout Company-->
   <!--***********************************************************-->
	<!--***********************************************************-->
	<!--Rating-->
	// Lorsque le DOM est chargé on applique le Javascript $(document).ready(function() {
	// On ajoute la classe "js" à la liste pour mettre en place par la suite du code CSS uniquement dans le cas où le Javascript est activé
	$("ul.notes-echelle").addClass("js");
	// On passe chaque note à l'état grisé par défaut
	$("ul.notes-echelle li").addClass("note-off");
	// Au survol de chaque note à la souris
	$("ul.notes-echelle li").mouseover(function() {
		// On passe les notes supérieures à l'état inactif (par défaut)
		$(this).nextAll("li").addClass("note-off");
		// On passe les notes inférieures à l'état actif
		$(this).prevAll("li").removeClass("note-off");
		// On passe la note survolée à l'état actif (par défaut)
		$(this).removeClass("note-off");
	});
	// Lorsque l'on sort du sytème de notation à la souris
	$("ul.notes-echelle").mouseout(function() {
		// On passe toutes les notes à l'état inactif
		$(this).children("li").addClass("note-off");
		// On simule (trigger) un mouseover sur la note cochée s'il y a lieu
		$(this).find("li input:checked").parent("li").trigger("mouseover");
	});



$("ul.notes-echelle input")
	// Lorsque le focus est sur un bouton radio
	.focus(function() {
		// On passe les notes supérieures à l'état inactif (par défaut)
		$(this).parent("li").nextAll("li").addClass("note-off");
		// On passe les notes inférieures à l'état actif
		$(this).parent("li").prevAll("li").removeClass("note-off");
		// On passe la note du focus à l'état actif (par défaut)
		$(this).parent("li").removeClass("note-off");
	})
	// Lorsque l'on sort du sytème de notation au clavier
	.blur(function() {
		// Si il n'y a pas de case cochée
		if($(this).parents("ul.notes-echelle").find("li input:checked").length == 0) {
			// On passe toutes les notes à l'état inactif
			$(this).parents("ul.notes-echelle").find("li").addClass("note-off");
		}
	});
	
	
	$("ul.notes-echelle input")
	// Lorsque le focus est sur un bouton radio
	.focus(function() {
		// On supprime les classes de focus
		$(this).parents("ul.notes-echelle").find("li").removeClass("note-focus");
		// On applique la classe de focus sur l'item tabulé
		$(this).parent("li").addClass("note-focus");
		// [...] cf. code précédent
	})
	// Lorsque l'on sort du sytème de notation au clavier
	.blur(function() {
		// On supprime les classes de focus
		$(this).parents("ul.notes-echelle").find("li").removeClass("note-focus");
		// [...] cf. code précédent
	})
	// Lorsque la note est cochée
	.click(function() {
		// On supprime les classes de note cochée
		$(this).parents("ul.notes-echelle").find("li").removeClass("note-checked");
		// On applique la classe de note cochée sur l'item choisi
		$(this).parent("li").addClass("note-checked");
	});
	
// On simule un survol souris des boutons cochés par défaut
$("ul.notes-echelle input:checked").parent("li").trigger("mouseover");
// On simule un click souris des boutons cochés
$("ul.notes-echelle input:checked").trigger("click");

		<!--Fin Rating-->
		<!--***********************************************************-->
		<!--***********************************************************-->
		
    </script>

</body>

</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=".$_SERVER['REQUEST_URI']."\">";
?>