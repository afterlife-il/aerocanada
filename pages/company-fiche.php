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

  <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">
    <!-- ICI ton contenu de page (le panel ADDRESS TYPE, etc.) -->
    <div class="row"> 
  </div><!-- /page-wrapper|2 -->
</div><!-- /wrapper -->

    <!-- ton contenu -->
  </div>
</div>
         <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                   
                </div>
                <!-- /.col-lg-10 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            
                        </div>
                        <div class="panel-body">
								<?php
$id_company=$_GET['id'];

require('../classes/company.class.php');


//recuperation du nom de la compagnie
//Table tb_company :::: Fld_Company_ID      Company_Old_Id        Fld_Company_Name        Fld_Company_Rating_ID         delete   	companyrating 	aci_contact  logocompany status internet cage_code
	$sqlcomn="SELECT * FROM tb_company where Fld_Company_ID='".$id_company."'";
	
	$reqcomn = mysql2_query($sqlcomn);
	$datacn = mysqli_fetch_array($reqcomn);
	$companyname = strtoupper($datacn['Fld_Company_Name']);
//Fin recuperation du nom de la compagnie

//recuperation de la date de premier contact
	$sqlrdpc="SELECT Fld_Date_Of_First_Contact FROM tbl_Company_Details where Fld_Company_ID='".$id_company."'";
	
	$reqrdpc = mysql2_query($sqlrdpc);
	$datardpc = mysqli_fetch_array($reqrdpc);
	$FldDateOfFirstContact = $datardpc['Fld_Date_Of_First_Contact'];
//Fin recuperation de la date de premier contact
?>
			<div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                             <h1><?php echo $companyname;?></h1>
							 <?php if(!empty($FldDateOfFirstContact)) echo "First contact : ".$FldDateOfFirstContact;
							 echo "<br><a href='modif_company.php?Fld_Company_ID=".$id_company."' style='decoration:none;color:white;' title='Modification Company'>
						<i style='margin-left:10px;position: relative;top: 4px;font-size:23px;color:white;' class='fa  fa-pencil-square-o'></i>
					    </a>
						<a href='ajout_contact_company.php?Fld_Company_ID=".$id_company."' style='decoration:none;color:white;' title='Add Contact Company'><img src='images/add_contact_w.png' width='28'>
					    </a>
						<a href='archive_company.php?Fld_Company_ID=".$id_company."' onClick=\"return(confirm('Etes vous sur ?'));\" style='decoration:none;color:white;'  title='Archive Company'>
						<i style='margin-left:10px;position: relative;top: 4px;font-size:23px;color:white;' class='fa  fa-archive'></i>
					    </a>
						";
						if ($id_company=='1182') echo "<a href='http://aerocanada-industries.com/adminaero/pages/price_list.php?Fld_Company_ID=".$id_company."' style='decoration:none;color:white;'>
						<i style='margin-left:10px;position: relative;top: 4px;font-size:23px;color:white;' class='fa fa-list-ul'></i>
					    </a>";
						?>
                        </div>
                        <!-- .panel-heading -->
                        <div class="panel-body">
                            <div class="panel-group" id="accordion">

<?php
//enregistrement maakav sur company
$today=date("Y-m-d");
$heuretoday=date("g:i a");
$requete = mysql2_query("INSERT INTO `tbl_maakav_company` (`id_maakav_company`, `id_company`, `datecomplete`, `heurevisite`, `id_Employee`) VALUES (NULL, '".$id_company."', '".$today."', '".$heuretoday."', '".$_SESSION['id_utilisateur']."');");
//Fin enregistrement maakav sur company
?>

<!--*********************************************************************GENERAL INFORMATION ABOUT THE COMPANY***********************************************-->
								<div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">GENERAL INFORMATION ABOUT THE COMPANY</a>
                                        </h4>
                                    </div>
<div id="collapseOne" class="panel-collapse collapse <?php if (empty($_GET['actcompet'])){?>in<?php }?>">
                                        <div class="panel-body">
<?php
/************************************
Table tbl_Company_Details
*************************************
id_tbl_company_Details Fld_Linked_ID Fld_Company_ID Company_Old_Id Fld_Company_Type_ID Fld_Company_Country Fld_Company_City Fld_Company_State Fld_Company_Street Fld_Company_ZipCode Fld_Company_Fax Fld_Company_Phone Fld_Company_Email Fld_Company_Score Fld_Company_BAX_Contact Fld_Remark   Fld_VAT_Nbr   Fld_Date_Of_First_Contact   Fld_Company_Address_Type   UTC_timezone  title_address
*/
// getting total number records without any search
$sql="SELECT * FROM tbl_Company_Details where Fld_Company_ID=".$id_company;	

$req = mysql2_query($sql);
$nbrows = mysqli_num_rows($req);
//echo $nbrows;
if(0<$nbrows){
	
?>                  
<form id="formgeneralinfo" name="formgeneralinfo" method="post" action="modif_info_general_company.php">
<input type="hidden" name="Fld_Company_ID" value="<?php echo $id_company;?>">
                               
      
								<?php
		
					$data = mysqli_fetch_array($req);
					
				
					//recuperation nom employee
					$sqlemp="SELECT Employee_Name FROM tbl_Employee where Employee_ID=".$data['Fld_Company_BAX_Contact'];
					
					$reqemp = mysql2_query($sqlemp);
					$dataemp = mysqli_fetch_array($reqemp);
					//Fin recuperation nom employee

                    echo "<div class='row'>";
					echo "<div class='col-lg-4'>";
					echo "<div class='form-group'><label>Company Name</label><input class=\"form-control\" type='text' name='Fld_Company_Name' value='".$datacn['Fld_Company_Name']."'></div>";
					echo "<div class='form-group'><label>ACI 770 Contact</label>
					<select class=\"form-control\" name=\"Employee_ID\">";
					//recuperation des types de compagnie
					                        $sqlemp="SELECT distinct(Employee_Name),Employee_ID FROM tbl_Employee";
											
											$reqemp = mysql2_query($sqlemp);
											while($dataemp = mysqli_fetch_array($reqemp)){
												echo "<option value='".$dataemp ['Employee_ID']."'";
												if ($dataemp['Employee_ID']==$datacn['aci_contact']) echo "selected";
												echo ">".$dataemp ['Employee_Name']."</option>";
											}
					                        //Fin recuperation des type de compagnie
					echo "</select></div>";
					
					echo "</div>";
					echo "<div class='col-lg-4'>";
					echo "<div class='form-group'><label>Company Type</label>
					<select class=\"form-control\" name=\"Fld_Company_Type_ID\">";
											
											//recuperation des types de compagnie
					                        $sqlctt="SELECT distinct(Fld_Company_Type_Text),Fld_Company_Type_ID FROM tbl_Company_Type";	
					                        $reqctt = mysql2_query($sqlctt);
					                        while($datactt = mysqli_fetch_array($reqctt)){
												echo "<option value='".$datactt['Fld_Company_Type_ID']."'";
												if ($datactt['Fld_Company_Type_ID']==$data['Fld_Company_Type_ID']) echo " selected";
												echo ">".$datactt['Fld_Company_Type_Text']."</option>";
											}
					                        //Fin recuperation des type de compagnie
										
                                                
                    echo "</select></div>";
					echo "<div class='form-group'><label>CAGE CODE #</label><input class=\"form-control\" type='text' name='cage_code' value='".$datacn['cage_code']."'></div>";
					echo "</div>";
					
					
					echo "<div class='col-lg-4'>";
					echo "<div class='form-group'><label>Website</label><input class=\"form-control\" type='text' name='internet' value='".$datacn['internet']."'></div>";
					echo "<div class='form-group'><label>VAT Nbr</label><input class=\"form-control\" type='text' name='Fld_VAT_Nbr' value='".$data['Fld_VAT_Nbr']."'></div>";
					echo "</div>";

					echo "</div>";
					echo "<div class='row'>";
					echo "<div class='col-lg-8'><div class='form-group'>
					<textarea class=\"form-control\" name='Fld_Remark'>".$data['Fld_Remark']."</textarea>
					</div></div>";
					echo "<div class='col-lg-2'></div>";
					echo "<div class='col-lg-1'><button type='submit' class='btn btn-default'>Submit Button</button></div>";
					echo "<div class='col-lg-1'></div>";
					echo "</div>";
					
			?> 
                              
                            
						</form>
<?php		
}
else echo "Pas de reponse";
			?>
								</div>
                                    </div>
										</div>
<!--*************************************************END GENERAL INFORMATION ABOUT THE COMPANY*****************************-->

<!--************************************************************DETAILS COMPANY********************************************-->
							<div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">COMPANY ADDRESS</a>
                                        </h4>
                                    </div>
                                    <div id="collapseTwo" class="panel-collapse collapse">
									
									
                                        <div class="panel-body">
<?php
/*
Table tbl_Company_Details
*************************************
id_tbl_company_Details Fld_Linked_ID Fld_Company_ID Company_Old_Id Fld_Company_Type_ID Fld_Company_Country Fld_Company_City Fld_Company_State Fld_Company_Street Fld_Company_ZipCode Fld_Company_Fax Fld_Company_Phone Fld_Company_Email Fld_Company_Score Fld_Company_BAX_Contact Fld_Remark   Fld_VAT_Nbr   Fld_Date_Of_First_Contact   Fld_Company_Address_Type   UTC_timezone  title_address
*/
// getting total number records without any search
$sql="SELECT * FROM tbl_Company_Details where Fld_Company_ID=".$id_company;	

$req = mysql2_query($sql);
$nbrows = mysqli_num_rows($req);
//echo $nbrows;
if(0<$nbrows){
	
?>

<a href="javascript:addaddresscompany()"> + Add A ADDRESS</a>
<form id="formmodifcontact" name="formmodifcontact" method="post" action="gestion_address_company.php">
<input type="hidden" name="Fld_Company_ID" value="<?php echo $id_company;?>">
<div class="table-responsive">
  <table class="table table-striped table-bordered table-hover" id="tableaddressecompany">
                                <thead>
                                    <tr>
                                        <th>Address Type</th>
                                        <th>Address Title</th>
                                        <th>Street</th>
                                        <th>City</th>
                                        <th>Zip Code</th>
                                        <th>State</th>
                                        <th>Country</th>
                                        <th>PHONE</th>
                                        <th>E-MAIL</th>
										<th>VAT Nbr</th>
										<th></th>
                                        
                                        
                                    </tr>
                                </thead>
                                <tbody>
								<?php
					$r=0;
					while ($data = mysqli_fetch_array($req))
					{ 
					$r++;
					//recuperation nom employee
					$sqlemp="SELECT Employee_Name FROM tbl_Employee where Employee_ID=".$data['Fld_Company_BAX_Contact'];
					
					$reqemp = mysql2_query($sqlemp);
					$dataemp = mysqli_fetch_array($reqemp);
					//Fin recuperation nom employee
					
					//recuperation de l'heure utc de l'adresse de la compagnie	
					$triggerOn="";
					if(!empty($data['UTC_timezone'])){
					$triggerOn=date("Y-m-d h:iA");				
					$schedule_date = new DateTime($triggerOn, new DateTimeZone('UTC') );
					$schedule_date->setTimeZone(new DateTimeZone($data['UTC_timezone']));
					$triggerOn =  $schedule_date->format('H:iA d/m/Y');
					$localtimecompany =  $schedule_date->format('H:iA');
					}
					//Fin recuperation de l'heure utc de l'adresse de la compagnie							
					//verification heure ouverture je verifie si l'heure du pays de la compagnie est entre 8h du matin et 20h du soir
					$current_time = $localtimecompany;
					$sunrise = "8:00AM";
					$sunset = "20:00PM";
					$date1 = DateTime::createFromFormat('H:i a', $current_time);
					$date2 = DateTime::createFromFormat('H:i a', $sunrise);
					$date3 = DateTime::createFromFormat('H:i a', $sunset);
					if ($date1 > $date2 && $date1 < $date3)
					{
					$couleur_horaire="green";
					}
					else $couleur_horaire="red";
					//Fin verification heure ouverture


					
					echo "<tr>";
					echo "<td>";
											
											//recuperation address type
											//** tbl_Division ** Fld_Division_ID  Fld_Division_Text
					                        $sqltypec="SELECT Fld_Division_Text FROM tbl_Division where Fld_Division_ID='".$data['Fld_Company_Address_Type']."'";
					                        $reqtypec = mysql2_query($sqltypec);
					                        $datatypec= mysqli_fetch_array($reqtypec);
											//End recuperation address type
										   
                    echo $datatypec['Fld_Division_Text']."</td>";
					echo "<td>".$data['title_address']."</td>";
					echo "<td>".$data['Fld_Company_Street']."</td>";
					echo "<td>".$data['Fld_Company_City']."</td>";
					echo "<td>".$data['Fld_Company_ZipCode']."</td>";
					echo "<td>".$data['Fld_Company_State']."</td>";
					echo "<td>".$data['Fld_Company_Country']."</td>";
					
					echo "<td>".$data['Fld_Company_Phone']."</td>";
					echo "<td>".$data['Fld_Company_Email']."</td>";
					echo "<td>".$data['Fld_VAT_Nbr']."</td>";
					
					echo "<td><input type=\"hidden\" name=\"id_tbl_company_Details".$r."\" id=\"id_tbl_company_Details\" value='".$data['id_tbl_company_Details']."'><input type='hidden' name='Fld_Company_Type_ID".$r."' value='".$data['Fld_Company_Type_ID']."'><a href=\"javascript:modif_address_company(".$data['id_tbl_company_Details'].")\"><i style=\"margin-left:10px;position: relative;top: 4px;font-size:23px;\" class=\"fa  fa-pencil-square-o\"></i></a></td>";
					echo "</tr>";
					}
			?> 
                                </tbody>
                            </table>
							
							</div>
							</form>
					<div style="display:none" id="blocdetailscompany"><div id="divdetailscompany"></div></div>
<?php		
}
else echo "Pas de reponse";
			?>
			
										
								
								</div>
								
                                    </div>
                                </div>
<!--**************************************************************END DETAILS COMPANY****************************-->



<!--*****************************************************************CONTACT COMPANY--*********************-->								
								<div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree">CONTACT</a>
                                        </h4><a href="company.php?companyrating=all&details2=ok&Fld_Company_ID=<?php echo $id_company;?>" style='decoration:none;color:white;'>
						<i style='margin-left:10px;position: relative;top: 4px;font-size:23px;color:white;' class='fa  fa-space-shuttle'></i>
                                    </div>
                                    <div id="collapseThree" class="panel-collapse collapse">
                                        <div class="panel-body">
			<?php

/*Table tb_company_contact
*************************************
id_company_contact  Fld_Linked_ID  Fld_Company_ID  Company_Old_Id  Fld_Contact_Name  Fld_Contact_Phone  Fld_Contact_Phone#2  Fld_Contact_Fax  Fld_Company_Mobile  Fld_Contact_Division_ID  Fld_Contact_Email  Fld_Contact_Title  Fld_Contact_Remark
*/

$sql="SELECT * FROM tb_company_contact where Fld_Company_ID=".$id_company." and status='Available' ORDER BY Fld_Contact_Name";
?>
<a href="javascript:addcontactcompany()"> + Add A COMPANY CONTACT</a>
<form id="formmodifcontact" name="formmodifcontact" method="post" action="valid_modif_contact_company_multi.php">
<input type="hidden" name="Fld_Company_ID" value="<?php echo $id_company;?>">
<div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="tableaddcontactcompany">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Phone 2</th>
                                        <th>Fax</th>
                                        <th>Mobile</th>
                                        <th>Division</th>
                                        <th>E-mail</th>
                                        <th>Title</th>
                                        <th>Remark</th>
										<th></th>
                                        
                                        
                                    </tr>
                                </thead>
                                <tbody>
								<?php
					$x=0;
					$req = mysql2_query($sql);
					while ($data = mysqli_fetch_array($req))
					{ 
						$x++;
						
						//recuperation des informations utilisateurs / employee
						//**tbl_Employee**  Employee_ID   Employee_Name    Fld_Contact_Id  pw  email  statut  position  tel  mobile  skype  numformat
						$sqliue="SELECT numformat FROM tbl_Employee where Employee_ID='".$_SESSION['id_utilisateur']."'";
						
						//echo $sqldiv;
						$reqiue = mysql2_query($sqliue);
						$dataiue = mysqli_fetch_array($reqiue);
						//Fin recuperation des informations utilisateurs / employee
						//modifiaction du format du numero de tel pour appel automatique
						$tel = str_replace("+","00",$data['Fld_Contact_Phone']);
						$tel = preg_replace('/[^0-9]/', '', $tel); // supression sauf chiffres
						//Fin modifiaction du format du numero de tel pour appel automatique
						
					if ($data['status']!='Available') $statustab="style='background-color:#be0831;color:#00000;'";	
					
					echo "<tr id=\"row_".$data['id_company_contact']."\">";
					echo "<td ".$statustab.">".$data['Fld_Contact_Name']."</td>";
					echo "<td ".$statustab."><a href='#' onclick='callclient(".$tel.",".$dataiue['numformat'].");'><i style='margin-left:10px;position: relative;top: 4px;font-size:23px;' class='fa  fa-phone'></i></a> ".$data['Fld_Contact_Phone']." </td>";   
					echo "<td ".$statustab.">".$data['Fld_Contact_Phone2']."</td>";
					echo "<td ".$statustab.">".$data['Fld_Contact_Fax']."</td>";
					echo "<td ".$statustab.">".$data['Fld_Company_Mobile']."</td>";
					
					echo "<td ".$statustab.">";
										

											
											//recuperation du nom de la division	
											 //*******tbl_Division*********Fld_Division_ID    Fld_Division_Text
											$sqldiv="SELECT * FROM tbl_Division where Fld_Division_ID='".$data['Fld_Contact_Division_ID']."'";
											
											//echo $sqldiv;
											$reqemp = mysql2_query($sqldiv);
											$datadiv = mysqli_fetch_array($reqemp);
											
												echo $datadiv['Fld_Division_Text'];
											
					                        //Fin recuperation des type de compagnie
											
                                                
                                            echo "</td>";
					
					
					echo "<td ".$statustab.">".$data['Fld_Contact_Email']."</td>";
					echo "<td ".$statustab.">".$data['Fld_Contact_Title']."</td>";
					echo "<td ".$statustab."><textarea class=\"form-control\" name='Fld_Contact_Remark".$data['id_company_contact']."' style='WIDTH: 400px; height:50px;'  id='recupmessageremark".$data['id_company_contact']."' onmouseleave='javascript:majtarea(".$data['id_company_contact'].")'>".$data['Fld_Contact_Remark']."</textarea></td>";
					if ($data['status']=='Available') echo "<td id='case".$data['id_company_contact']."'><a href='javascript:statutcontact(".$data['id_company_contact'].")' onClick=\"return(confirm('Etes vous sur ?'));\"><i style='margin-left:10px;position: relative;top: 4px;font-size:23px;' class='fa  fa-archive'></i></a><a href=\"javascript:modif_contact_company(".$data['id_company_contact'].")\"><i style=\"margin-left:10px;position: relative;top: 4px;font-size:23px;\" class=\"fa  fa-pencil-square-o\"></i></a></td>";
					else echo "<td id='case".$data['id_company_contact']."'><a href='javascript:desarchivercontact(".$data['id_company_contact'].")'>Annuler</a><a href=\"javascript:modif_contact_company(".$data['id_company_contact'].")\"><i style=\"margin-left:10px;position: relative;top: 4px;font-size:23px;\" class=\"fa  fa-pencil-square-o\"></i></a></td>";
					echo "<input type=\"hidden\" name=\"id_company_contact".$x."\" value=\"".$data['id_company_contact']."\"><input type=\"hidden\" name=\"nbcontact\" id=\"nbcontact\" value=\"".$x."\">";
					echo "</tr>";
					
					}
			?>
                                    
                      
                                </tbody>
								 </form>
                            </table>
										</div>
					<div style="display:none" id="bloccontactcompany"><div id="divcontactcompany"></div></div>
										</div>
                                    </div>
                                </div>
<!--**************************************************************END CONTACT COMPANY****************************-->								

								<div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseFour">CONTACT ARCHIVED</a>
                                        </h4>
                                    </div>
                                    <div id="collapseFour" class="panel-collapse collapse">
                                        <div class="panel-body">
			<?php
//*****************************************************************CONTACT COMPANY
/*Table tb_company_contact
*************************************
id_company_contact  Fld_Linked_ID  Fld_Company_ID  Company_Old_Id  Fld_Contact_Name  Fld_Contact_Phone  Fld_Contact_Phone#2  Fld_Contact_Fax  Fld_Company_Mobile  Fld_Contact_Division_ID  Fld_Contact_Email  Fld_Contact_Title  Fld_Contact_Remark
*/
// getting total number records without any search

$sql="SELECT * FROM tb_company_contact where Fld_Company_ID=".$id_company." and status='none'";
$req = mysql2_query($sql);
$nbrows = mysqli_num_rows($req);
//echo $nbrows;
if(0<$nbrows){
?>
<form id="formmodifcontact" name="formmodifcontact" method="post" action="valid_modif_contact_company_multi.php">
<input type="hidden" name="Fld_Company_ID" value="<?php echo $id_company;?>">
<div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Phone 2</th>
                                        <th>Fax</th>
                                        <th>Mobile</th>
                                        <th>Division</th>
                                        <th>E-mail</th>
                                        <th>Title</th>
                                        <th>Remark</th>
										<th></th>
                                        
                                        
                                    </tr>
                                </thead>
                                <tbody>
								<?php
					$x=0;
					$req = mysql2_query($sql);
					while ($data = mysqli_fetch_array($req))
					{ 
						$x++;
						
						//recuperation des informations utilisateurs / employee
						//**tbl_Employee**  Employee_ID   Employee_Name    Fld_Contact_Id  pw  email  statut  position  tel  mobile  skype  numformat
						$sqliue="SELECT numformat FROM tbl_Employee where Employee_ID='".$_SESSION['id_utilisateur']."'";
						
						//echo $sqldiv;
						$reqiue = mysql2_query($sqliue);
						$dataiue = mysqli_fetch_array($reqiue);
						//Fin recuperation des informations utilisateurs / employee
						//modifiaction du format du numero de tel pour appel automatique
						$tel = str_replace("+","00",$data['Fld_Contact_Phone']);
						$tel = preg_replace('/[^0-9]/', '', $tel); // supression sauf chiffres
						//Fin modifiaction du format du numero de tel pour appel automatique
						
					if ($data['status']!='Available') $statustab="style='background-color:#be0831;color:#00000;'";	
					
					echo "<tr id=\"row_".$data['id_company_contact']."\">";
					echo "<td ".$statustab."><input class=\"form-control\" type='text' name='Fld_Contact_Name".$x."' value='".$data['Fld_Contact_Name']."'></td>";
					echo "<td ".$statustab."><input class=\"form-control\" type='text' name='Fld_Contact_Phone".$x."' value='".$data['Fld_Contact_Phone']."'> <a href='#' onclick='callclient(".$tel.",".$dataiue['numformat'].");'><i style='margin-left:10px;position: relative;top: 4px;font-size:23px;' class='fa  fa-phone'></i></a></td>";   
					echo "<td ".$statustab."><input class=\"form-control\" type='text' name='Fld_Contact_Phone2".$x."' value='".$data['Fld_Contact_Phone2']."'></td>";
					echo "<td ".$statustab."><input class=\"form-control\" type='text' name='Fld_Contact_Fax".$x."' value='".$data['Fld_Contact_Fax']."'></td>";
					echo "<td ".$statustab."><input class=\"form-control\" type='text' name='Fld_Company_Mobile".$x."' value='".$data['Fld_Company_Mobile']."'></td>";
					
					echo "<td ".$statustab."><select class=\"form-control\" name=\"Fld_Contact_Division_ID".$x."\">";
										

											
											//recuperation du nom de la division	
											 //*******tbl_Division*********Fld_Division_ID    Fld_Division_Text
											$sqldiv="SELECT * FROM tbl_Division";
											
											//echo $sqldiv;
											$reqemp = mysql2_query($sqldiv);
											while($datadiv = mysqli_fetch_array($reqemp))
											{
												echo "<option value='".$datadiv ['Fld_Division_ID']."'";
												if ($data['Fld_Contact_Division_ID']==$datadiv['Fld_Division_ID']) echo " selected";
												echo ">".$datadiv ['Fld_Division_Text']."</option>";
											}
					                        //Fin recuperation des type de compagnie
											
                                                
                                            echo "</select></td>";
					
					
					echo "<td ".$statustab."><input class=\"form-control\" type='text' name='Fld_Contact_Email".$x."' value='".$data['Fld_Contact_Email']."'></td>";
					echo "<td ".$statustab."><input class=\"form-control\" type='text' name='Fld_Contact_Title".$x."' value='".$data['Fld_Contact_Title']."'></td>";
					echo "<td ".$statustab."><textarea class=\"form-control\" name='Fld_Contact_Remark".$data['id_company_contact']."' style='WIDTH: 400px; height:50px;'  id='recupmessageremark".$data['id_company_contact']."' onmouseleave='javascript:majtarea(".$data['id_company_contact'].")'>".$data['Fld_Contact_Remark']."</textarea></td>";
					if ($data['status']=='Available') echo "<td id='case".$data['id_company_contact']."'><input class=\"form-control\" type='submit' value='ok' name='valform".$x."'> <a href='javascript:statutcontact(".$data['id_company_contact'].")' onClick=\"return(confirm('Etes vous sur ?'));\"><i style='margin-left:10px;position: relative;top: 4px;font-size:23px;' class='fa  fa-archive'></i></a></td>";
					else echo "<td id='case".$data['id_company_contact']."'><input class=\"form-control\" type='submit' value='ok' name='valform".$x."'><a href='javascript:desarchivercontact(".$data['id_company_contact'].")'>Annuler</a></td>";
					echo "<input type=\"hidden\" name=\"id_company_contact".$x."\" value=\"".$data['id_company_contact']."\"><input type=\"hidden\" name=\"nbcontact\" id=\"nbcontact\" value=\"".$x."\">";
					echo "</tr>";
					
					}
			?>
                                    
                      </form>
                                </tbody>
                            </table>
<?php
}
else echo "There is no contact archived for the moment";
			?>
										</div>
										</div>
                                    </div>
                                </div>
<!--**************************************************************END CONTACT COMPANY*****************************-->								
<!--*****************************************************************COMPANY FLEET********************************-->								
								<div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseFive">FLEET</a>
                                        </h4>
                                    </div>
                                    <div id="collapseFive" class="panel-collapse collapse">
                                        <div class="panel-body">
			<?php


//**tbl_Fleet** id_Fleet  Fld_Link_Id  Fld_Company_ID  Company_Old_Id  Fld_Region  Fld_Engine  Fld_Unit  Fld_AC_ID 

$sqlcomp="SELECT * FROM tbl_Fleet where Fld_Company_ID=".$id_company;
$reqcomp = mysql2_query($sqlcomp);
?>
<a href="javascript:addaircraft()"> + Add A AIRCRAFT</a>
<form id="formmodifcontact" name="formmodifcontact" method="post" action="valid_modif_aircraft_fleet.php">
<input type="hidden" name="Fld_Company_ID" value="<?php echo $id_company;?>">
<div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTablefleet">
                                <thead>
                                    <tr>
                                        <th>REGION</th>
                                        <th>ENGINE</th>
                                        <th>UNIT</th>
                                        <th>AIRCRAFT</th>
                                        
                                        <th></th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
								<?php
					$z=0;
					while ($datacomp = mysqli_fetch_array($reqcomp))
					{ 
				$z++;

					echo "<tr id=\"row_".$z."\">";
					echo "<td><select class=\"form-control\" name='Fld_Region".$z."'>";
					//recuperation region
					//** tbl_Region **  Region_ID  Region_Texte
					$sqlreg="SELECT distinct(Region_Texte),Region_ID FROM tbl_Region order by Region_Texte";
					
					$reqreg = mysql2_query($sqlreg);
					while($datareg = mysqli_fetch_array($reqreg))
					{
						echo "<option value='".$datareg['Region_ID']."'";
						if ($datareg['Region_ID']==$datacomp['Fld_Region']) echo "selected";
						echo ">".$datareg['Region_Texte']."</option>";
					}
					//Fin recuperation region
					echo "</select></td>";
					echo "<td><input class=\"form-control\" type='text' name='Fld_Engine".$z."' value='".$datacomp['Fld_Engine']."'></td>";
					echo "<td><input class=\"form-control\" type='text' name='Fld_Unit".$z."' value='".$datacomp['Fld_Unit']."'></td>";
					echo "<td><select class=\"form-control\" name='Fld_AC_ID".$z."'>";
					//recuperation Aircraft
					// ** tbl_Aircraft ** Fld_AC_ID  Fld_AC_Model  Fld_AC_Series  Fld_AC_Manufacturer  Fld_AC_Engine_Model  Fld_AC_Engine_Series
					$sqlrairc="SELECT distinct(Fld_AC_Model),Fld_AC_ID FROM tbl_Aircraft order by Fld_AC_Model";
					
					$reqrairc = mysql2_query($sqlrairc);
					while($datarairc = mysqli_fetch_array($reqrairc))
					{
						echo "<option value='".$datarairc['Fld_AC_ID']."'";
						if ($datarairc['Fld_AC_ID']==$datacomp['Fld_AC_ID']) echo "selected";
						echo ">".$datarairc['Fld_AC_Model']."</option>";
					}
					//Fin recuperation Aircraft
					echo "</select></td>";
					echo "<input type=\"hidden\" name=\"id_Fleet".$z."\" value=\"".$datacomp['id_Fleet']."\"><input type=\"hidden\" name=\"nbaircraft\" id=\"nbaircraft\" value=\"".$z."\">";
					echo "<td><input class=\"form-control\" type='submit' value='ok' name='valform".$z."'></td>";
					echo "</tr>";
					
					}
			?>
                                </tbody>
                            </table>
							</form>
									
										</div>
										</div>
                                    </div>
                                </div>
<!--*****************************************************************END COMPANY FLEET*************************
************************************************************************************************************-->
<!--****************************************FORWARDER*******************************************************-->
									<div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseSix">FORWARDER</a>
                                        </h4>
                                    </div>
                                    <div id="collapseSix" class="panel-collapse collapse">
                                        <div class="panel-body">
			<?php
//**tbl_Forwarder**   Fld_Linked_ID  Company_Old_Id  Fld_Company_ID  Fld_Shipper_ID  Fld_Account_Nbr Fld_Remark Fld_Shipper_Contact_Name_Forw  Fld_Shipper_Contact_Phone_Forw
//*** tbl_Shipper ***  Fld_Shipper_ID  Fld_Shipper_Text  Fld_Shipper_Contact_Name  Fld_Shipper_Contact_Phone

$sqlforwarder="SELECT tbl_Forwarder.*,tbl_Shipper.* FROM tbl_Forwarder,tbl_Shipper where tbl_Forwarder.Fld_Shipper_ID=tbl_Shipper.Fld_Shipper_ID AND tbl_Forwarder.Fld_Company_ID='".$id_company."'";
// echo $sqlforwarder;
$reqforwarder = mysql2_query($sqlforwarder);
?>
<a href="javascript:addforwarder()"> + Add A FORWARDER</a>
<form id="formForwarder" name="formForwarder" method="post" action="valid_modif_forwarder.php">
<input type="hidden" name="Fld_Company_ID" value="<?php echo $id_company;?>">
<div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTableforw">
                                <thead>
                                    <tr>
										<th>SHIPPER</th>
										<th>SHIPPER CONTAC NAME</th>
                                        <th>SHIPPER CONTAC PHONE</th>
										<th>ACCOUNT #</th>
										<th>REMARK</th>
                                        
                                        <th></th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
								<?php
					$w=0;
					while ($dataforwarder = mysqli_fetch_array($reqforwarder))
					{ 
				$w++;

					echo "<tr id=\"row_".$w."\">";
					echo "<td><select class=\"form-control\" name='Fld_Shipper_ID".$w."'>";
									// ** tbl_Shipper **  Fld_Shipper_ID  Fld_Shipper_Text  Fld_Shipper_Contact_Name  Fld_Shipper_Contact_Phone
									
									
									$objet=new company();
									$donnee = $objet->affichage_shippers();
									
									foreach($donnee as $dataemp)
									{
										echo "<option value='".$dataemp["Fld_Shipper_ID"]."'";
										if((!empty($dataforwarder['Fld_Shipper_ID']))&&($dataemp["Fld_Shipper_ID"]==$dataforwarder['Fld_Shipper_ID'])) echo "selected";
										echo ">".$dataemp["Fld_Shipper_Text"]."<option>";
									}
					echo "</select></td>";
					echo "<td><input class=\"form-control\" type='text' name='Fld_Shipper_Contact_Name_Forw".$w."' value='".$dataforwarder['Fld_Shipper_Contact_Name_Forw']."'><br>OLD CONTACT NAME : ".$dataforwarder['Fld_Shipper_Contact_Name']."</td>";
					echo "<td><input class=\"form-control\" type='text' name='Fld_Shipper_Contact_Phone_Forw".$w."' value='".$dataforwarder['Fld_Shipper_Contact_Phone_Forw']."'><br>OLD CONTACT PHONE : ".$dataforwarder['Fld_Shipper_Contact_Phone']."</td>";
					echo "<td><input class=\"form-control\" type='text' name='Fld_Account_Nbr".$w."' value='".$dataforwarder['Fld_Account_Nbr']."'></td>";
					echo "<td><input class=\"form-control\" type='text' name='Fld_Remark".$w."' value='".$dataforwarder['Fld_Remark']."'></td>";
					echo "<input type=\"hidden\" name=\"Fld_Linked_ID".$w."\" value=\"".$dataforwarder['Fld_Linked_ID']."\"><input type=\"hidden\" name=\"nbforwarder\" id=\"nbforwarder\" value=\"".$w."\">";
					echo "<td><input class=\"form-control\" type='submit' value='ok' name='valform".$w."'></td>";
					echo "</tr>";
					
					}
			?>
                                </tbody>
                            </table>
							</form>							
										</div>
										</div>
                                    </div>
                                </div>
<!--****************************************END FORWARDER*******************************************************-->
<!--************************************************************************************************************-->
<!--*****************************************************************BANK ACCOUNT*******************************-->								
									<div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseSeven">BANK ACCOUNT</a>
                                        </h4>
                                    </div>
                                    <div id="collapseSeven" class="panel-collapse collapse">
                                        <div class="panel-body">
			<?php

//**tbl_Company_Bank_Account**   Fld_Linked_ID  Fld_Company_ID  Fld_Bank_Acct_Nbr  Fld_Bank_Name  Fld_Bank_Address  Fld_ABA_Routing_Nbr  Fld_Swift_Nbr  Fld_Reference  branch_nbr  bank_nbr  comments

$sqlbanka="SELECT * FROM tbl_Company_Bank_Account where Fld_Company_ID=".$id_company;
$reqbanka = mysql2_query($sqlbanka);
?>
<a href="javascript:addaba()"> + Add A BANK ACCOUNT</a>
<form id="formbankaccount" name="formbankaccount" method="post" action="valid_modif_bank_account.php">
<input type="hidden" name="Fld_Company_ID" value="<?php echo $id_company;?>">
<div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTableba">
                                <thead>
                                    <tr>
										<th>BANK NAME</th>
										<th>BANK ADDRESS</th>
                                        <th>ACCOUNT #</th>
										<th>BRANCH #</th>
										<th>BANK #</th>
                                        <th>SWIFT #</th>
                                        <th>ABA ROUTING #</th>
                                        <th>REFERENCE</th>
                                        <th>COMMENTS</th>
                                        
                                        <th></th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
								<?php
					$a=0;
					while ($banka = mysqli_fetch_array($reqbanka))
					{ 
				$a++;

					echo "<tr id=\"row_".$a."\">";
					echo "<td><input class=\"form-control\" type='text' name='Fld_Bank_Name".$a."' value='".$banka['Fld_Bank_Name']."'></td>";
					echo "<td><input class=\"form-control\" type='text' name='Fld_Bank_Address".$a."' value='".$banka['Fld_Bank_Address']."'></td>";
					echo "<td><input class=\"form-control\" type='text' name='Fld_Bank_Acct_Nbr".$a."' value='".$banka['Fld_Bank_Acct_Nbr']."'></td>";
					echo "<td><input class=\"form-control\" type='text' name='branch_nbr".$a."' value='".$banka['branch_nbr']."'></td>";
				
					echo "<td><input class=\"form-control\" type='text' name='bank_nbr".$a."' value='".$banka['bank_nbr']."'></td>";
					echo "<td><input class=\"form-control\" type='text' name='Fld_Swift_Nbr".$a."' value='".$banka['Fld_Swift_Nbr']."'></td>";
					
					echo "<td><input class=\"form-control\" type='text' name='Fld_ABA_Routing_Nbr".$a."' value='".$banka['Fld_ABA_Routing_Nbr']."'></td>";
					
					echo "<td><input class=\"form-control\" type='text' name='Fld_Reference".$a."' value='".$banka['Fld_Reference']."'></td>";
					echo "<td><input class=\"form-control\" type='text' name='comments".$a."' value='".$banka['comments']."'></td>";
					echo "<input type=\"hidden\" name=\"Fld_Linked_ID".$a."\" value=\"".$banka['Fld_Linked_ID']."\"><input type=\"hidden\" name=\"nbbankaccount\" id=\"nbbankaccount\" value=\"".$a."\">";
					echo "<td><input class=\"form-control\" type='submit' value='ok' name='valform".$a."'></td>";
					echo "</tr>";
					
					}
			?>
                                </tbody>
                            </table>
							</form>
									</div>
										</div>
                                    </div>
                                </div>
<!--*****************************************************************END BANK ACCOUNT****************************************-->	
<!--************************************************************************************************************-->
<!--******************************************************COMPETITOR******************************************-->								
									<div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseEight">COMPETITOR</a>
                                        </h4>
                                    </div>
                                    <div id="collapseEight" class="panel-collapse collapse <?php if ($_GET['actcompet']=='addcomp'){?>in<?php }?>"">
                                        <div class="panel-body">
										<div align="center">
										<form action="add_competitor.php" method="post">
										<input type="hidden" name="Fld_Company_ID" value="<?php echo $id_company;?>">
										<input type="text" name="companyid" id="companyid" class="companyid" placeholder="Please Enter company" ><input type="submit"></Form>
										</div>
										<br>
			<?php

//**tbl_Competitor**  Fld_Linked_ID Fld_Company_ID  Fld_Competitor_ID

$sqlcompetitor="SELECT tbl_Competitor.*,tb_company.Fld_Company_Name FROM tbl_Competitor,tb_company where tbl_Competitor.Fld_Competitor_ID=tb_company.Fld_Company_ID AND tbl_Competitor.Fld_Company_ID='".$id_company."' ORDER BY tb_company.Fld_Company_Name";
// echo $sqlcompetitor;
$reqcompetitor = mysql2_query($sqlcompetitor);
?>

<div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTableba">
                                <tbody>
								<?php
					$a=0;
					//i count how much result i got
					// $num_rows = mysqli_num_rows($reqcompetitor);
					
					while ($datacompetitor = mysqli_fetch_array($reqcompetitor))
					{ 
					$a++;
					//recuperation du nom de la compagnie
					//Table tb_company :::: Fld_Company_ID      Company_Old_Id        Fld_Company_Name        Fld_Company_Rating_ID         delete   	companyrating 	aci_contact  logocompany status internet cage_code
					// $sqlcomn="SELECT * FROM tb_company where Fld_Company_ID='".$datacompetitor['Fld_Competitor_ID']."'";
					// 
					// $reqcomn = mysql2_query($sqlcomn);
					// $datacn = mysqli_fetch_array($reqcomn);
					// $companynamecom = strtoupper($datacn['Fld_Company_Name']);
					//Fin recuperation du nom de la compagnie
					if ($a=='1')echo "<tr>";
					echo "<td width='20%'>".$datacompetitor['Fld_Company_Name']."</td>";
					echo "<td width='5%'><a href='del_competitor.php?Fld_Linked_ID=".$datacompetitor['Fld_Linked_ID']."&Fld_Company_ID=".$id_company."'  onClick=\"return(confirm('Are you sure ?'));\"><img src='images/bin-blue-full-icon.png' border='0' width='27'></a></td>";
					if ($a=='4')
					{
						echo "</tr>";
						$a=0;
					}
					}
						if($a<4) 
						{
						$b=(4-$a)*2;
						echo "<td colspan='".$b."'></td>";
						echo "</tr>";
						}
			?>
                                </tbody>
                            </table>
							
						
</div>
										</div>
                                    </div>
                                </div>
<!--*****************************************************************END COMPETITOR****************************************-->

								
								</div>
							</div>
							<!-- .panel-body -->
                    
						</div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
			
                        </div>
                        <!-- /.panel-body -->
						
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

	<!--************************************************************************************************************-->
	<!--************************************************************************************************************-->
		<?php
					//recuperation du select qui va etre ajoute dans le javascription en dessous de l'ajout d'une ligne de fleet
					$varselaircraft="<select class='form-control' name='Fld_AC_ID'><option></option>";
					//recuperation Aircraft
					// ** tbl_Aircraft ** Fld_AC_ID  Fld_AC_Model  Fld_AC_Series  Fld_AC_Manufacturer  Fld_AC_Engine_Model  Fld_AC_Engine_Series
					$sqlairc="SELECT distinct(Fld_AC_Model),Fld_AC_ID FROM tbl_Aircraft order by Fld_AC_Model";	
					$reqairc = mysql2_query($sqlairc);
					while ($dataairc = mysqli_fetch_array($reqairc))
					//Fin recuperation Aircraft
					{
						$varselaircraft.="<option value='".$dataairc['Fld_AC_ID']."'>".$dataairc['Fld_AC_Model']."</option>";
						
					}
					$varselaircraft.="</select>";
					//Fin recuperation du select qui va etre ajoute dans le javascription en dessous de l'ajout d'une ligne de fleet
					
					
					
					//*************************************************************************************************************
					//*************************************************************************************************************
					
					
					//*************************************************************************************************************
					//********************************Creation du select shipper pour la creation d'un nouveau forwarder***********
					
					$varshipperselect="<select class='form-control' name='Fld_Shipper_ID'>";
									// ** tbl_Shipper **  Fld_Shipper_ID  Fld_Shipper_Text  Fld_Shipper_Contact_Name  Fld_Shipper_Contact_Phone
									
									
									$req="SELECT * FROM tbl_Shipper order by Fld_Shipper_Text";
									$requete=mysql2_query($req);
									
									while($reponse=mysqli_fetch_array($requete))
									{
										$varshipperselect.="<option value='".$reponse["Fld_Shipper_ID"]."'>".$reponse["Fld_Shipper_Text"]."<option>";
									}
					$varshipperselect.="</select>";
					//********************************Fin Creation du select shipper pour la creation d'un nouveau forwarder
					//*************************************************************************************************************
					//*************************************************************************************************************
					//********************************Creation Address type
					$varaddresstypeselect="<select class='form-control' name='Fld_Company_Address_Type'>";
											//** tbl_Division ** Fld_Division_ID  Fld_Division_Text
					                        $sqltypec="SELECT * FROM tbl_Division";
					                        $reqtypec = mysql2_query($sqltypec);
					                        while ($datatypec= mysqli_fetch_array($reqtypec))
											{
											$varaddresstypeselect.="<option value='".$datatypec["Fld_Division_ID"]."'>".$datatypec["Fld_Division_Text"]."<option>";	
											}
					$varaddresstypeselect.="</select>";
					//********************************Fin Creation Address type
					//*************************************************************************************************************
					
					
	?>
	<!--************************************************************************************************************-->
	<!--************************************************************************************************************-->
	
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
	<!--Ajout pour autocompression Roy-->
 <!--<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">-->
    <script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <script src="js/typeahead.js"></script>
    <style>       
		.tt-hint,
        .companyid {
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
    <script language="JavaScript" type="text/javascript">
        $(document).ready(function() {

            $('input.companyid').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
        })
<!--Fin Ajout pour autocompression Roy-->
	
<!--*****************************************************************************************************-->
<!--*****************************************************************************************************-->
<!--*****************************************************************************************************-->
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
 
    var selection = document.getElementById("recupmessageremark"+id).value;
    $.get('majremarkcontact.php', { // lien de la page qui permet la suppression
                id_company_contact:id,Fld_Contact_Remark:selection //variable de type GET (on recuperera la variable avec $_GET['idsup'])
            }, function(data){
              
			  
            });
/*	alert('select :'+selection);*/
 
				}

<!--Add A COMPANY ADDRESS-->
	function addaddresscompany(){
    var cell, ligne;
 
    // on recupere l'identifiant (id) de la table qui sera modifiee
    var tableau = document.getElementById("tableaddressecompany");
    // nombre de lignes dans la table (avant ajout de la ligne)
    var nbLignes = tableau.rows.length;
 
    ligne = tableau.insertRow(-1); // creation d'une ligne pour ajout en fin de table
                                   // le parametre est dans ce cas (-1)
    ligne.id='row_'+eval(nbLignes+1);
    // creation et insertion des cellules dans la nouvelle ligne creee
	
	cell = ligne.insertCell(0);
    cell.innerHTML = "<?php echo $varaddresstypeselect;?>";
	
	cell = ligne.insertCell(1);
    cell.innerHTML = "<input class=\"form-control\" name=\"title_address\" id=\"title_address\" placeholder=\"Address Title\">";
	
	cell = ligne.insertCell(2);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Company_Street\" id=\"Fld_Company_Street\" placeholder=\"Street\">";
	
	cell = ligne.insertCell(3);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Company_City\" id=\"Fld_Company_City\" placeholder=\"City\">";
	
	cell = ligne.insertCell(4);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Company_ZipCode\" id=\"Fld_Company_ZipCode\" placeholder=\"Zip Code\">";
	
	cell = ligne.insertCell(5);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Company_State\" id=\"Fld_Company_State\" placeholder=\"State\">";
	
	cell = ligne.insertCell(6);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Company_Country\" id=\"Fld_Company_Country\" placeholder=\"Country\">";
	
	cell = ligne.insertCell(7);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Company_Phone\" id=\"Fld_Company_Phone\" placeholder=\"PHONE\">";
	
	cell = ligne.insertCell(8);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Company_Email\" id=\"Fld_Company_Email\" placeholder=\"E-MAIL\">";
	
	cell = ligne.insertCell(9);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Remark\" id=\"Fld_Remark\" placeholder=\"Remark\">";
	
	cell = ligne.insertCell(10);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_VAT_Nbr\" id=\"Fld_VAT_Nbr\" placeholder=\"VAT Nbr\"><input type='hidden' name='act' value='addaddresscompany'>";
	
	cell = ligne.insertCell(11);
    cell.innerHTML = "<input type='submit' value='submit' class=\"form-control\">";
}
	<!--End Add A COMPANY ADDRESS-->
				
				
	<!--Add A COMPANY CONTACT-->
	function addcontactcompany(){
    var cell, ligne;
 
    // on recupere l'identifiant (id) de la table qui sera modifiee
    var tableau = document.getElementById("tableaddcontactcompany");
    // nombre de lignes dans la table (avant ajout de la ligne)
    var nbLignes = tableau.rows.length;
 
    ligne = tableau.insertRow(-1); // creation d'une ligne pour ajout en fin de table
                                   // le parametre est dans ce cas (-1)
    ligne.id='row_'+eval(nbLignes+1);
    // creation et insertion des cellules dans la nouvelle ligne creee
	
	cell = ligne.insertCell(0);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Contact_Name\" id=\"Fld_Contact_Name\" placeholder=\"\">";
	
	cell = ligne.insertCell(1);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Contact_Phone\" id=\"Fld_Contact_Phone\" placeholder=\"\">";
	
	cell = ligne.insertCell(2);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Contact_Phone2\" id=\"Fld_Contact_Phone2\" placeholder=\"\">";
	
	cell = ligne.insertCell(3);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Contact_Fax\" id=\"Fld_Contact_Fax\" placeholder=\"\">";
	
	cell = ligne.insertCell(4);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Company_Mobile\" id=\"Fld_Company_Mobile\" placeholder=\"\">";
	
	cell = ligne.insertCell(5);
    cell.innerHTML = "<select class=\"form-control\" name=\"Fld_Contact_Division_ID\"><option value=\"1\" selected=\"\">Sales</option><option value=\"2\">Account</option><option value=\"3\">Logistics / Shipping1</option><option value=\"5\">Technical</option><option value=\"6\">Purchasing</option><option value=\"7\">AOG</option><option value=\"8\">Customer Service Administrator</option><option value=\"9\">Management</option><option value=\"10\">Quality</option><option value=\"11\">Sales Technical</option><option value=\"12\">Shipping2</option><option value=\"13\">***No Longer Valid***</option><option value=\"14\">DROP SHIPMENT</option></select>";
	
	cell = ligne.insertCell(6);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Contact_Email\" id=\"Fld_Contact_Email\" placeholder=\"\">";
	
	cell = ligne.insertCell(7);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Contact_Title\" id=\"Fld_Contact_Title\" placeholder=\"\">";
	
	cell = ligne.insertCell(8);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Contact_Remark\" id=\"Fld_Contact_Remark\" placeholder=\"\"><input type='hidden' name='nbcontact' value='"+nbLignes+"'><input type='hidden' name='act' value='addcontact'>";
	
	cell = ligne.insertCell(9);
    cell.innerHTML = "<input type='submit' value='submit' class=\"form-control\">";
}
	<!--End Add A COMPANY CONTACT-->

	<!--Add A AIRCRAFT-->
	function addaircraft(){
    var cell, ligne;
 
    // on recupere l'identifiant (id) de la table qui sera modifiee
    var tableau = document.getElementById("dataTablefleet");
    // nombre de lignes dans la table (avant ajout de la ligne)
    var nbLignes = tableau.rows.length;
 
    ligne = tableau.insertRow(-1); // creation d'une ligne pour ajout en fin de table
                                   // le parametre est dans ce cas (-1)
    ligne.id='row_'+eval(nbLignes+1);
    // creation et insertion des cellules dans la nouvelle ligne creee
	
	cell = ligne.insertCell(0);
    cell.innerHTML = "<select class=\"form-control\" name=\"Fld_Region\"><option>Choose Region</option><option value=\"1\" >Africa</option><option value=\"2\">Asia & Pacific</option><option value=\"3\">Canada</option><option value=\"4\">Europe</option><option value=\"5\">Latin America</option><option value=\"6\">Middle East</option><option value=\"7\">USA</option></select>";
	
	cell = ligne.insertCell(1);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Engine\" id=\"Fld_Engine\" placeholder=\"\">";
	
	cell = ligne.insertCell(2);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Unit\" id=\"Fld_Unit\" placeholder=\"\">";
	
	cell = ligne.insertCell(3);
	cell.innerHTML ="<?php echo $varselaircraft;?>";
	
	cell = ligne.insertCell(4);
    cell.innerHTML = "<input type='hidden' name='nbaircraft' value='"+nbLignes+"'><input type='hidden' name='act' value='addaircraft'><input type='submit' value='submit' class=\"form-control\">";
}
	<!--End Add A AIRCRAFT-->
	
<!--Add A BANK ACCOUNT-->
	function addaba(){
    var cell, ligne;
 
    // on recupere l'identifiant (id) de la table qui sera modifiee
    var tableau = document.getElementById("dataTableba");
    // nombre de lignes dans la table (avant ajout de la ligne)
    var nbLignesba = tableau.rows.length;
 
    ligne = tableau.insertRow(-1); // creation d'une ligne pour ajout en fin de table
                                   // le parametre est dans ce cas (-1)
    ligne.id='row_'+eval(nbLignesba+1);
    // creation et insertion des cellules dans la nouvelle ligne creee
	
	cell = ligne.insertCell(0);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Bank_Name\" id=\"Fld_Bank_Name\" placeholder=\"BANK NAME\">";
	
	cell = ligne.insertCell(1);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Bank_Address\" id=\"Fld_Bank_Address\" placeholder=\"BANK ADDRESS\">";
	
	cell = ligne.insertCell(2);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Bank_Acct_Nbr\" id=\"Fld_Bank_Acct_Nbr\" placeholder=\"ACCOUNT #\">";
	
	cell = ligne.insertCell(3);
    cell.innerHTML = "<input class=\"form-control\" name=\"branch_nbr\" id=\"branch_nbr\" placeholder=\"BRANCH #\">";
	
	cell = ligne.insertCell(4);
    cell.innerHTML = "<input class=\"form-control\" name=\"bank_nbr\" id=\"bank_nbr\" placeholder=\"BANK #\">";
	
	cell = ligne.insertCell(5);
	cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Swift_Nbr\" id=\"Fld_Swift_Nbr\" placeholder=\"SWIFT #\">";
	
	cell = ligne.insertCell(6);
	cell.innerHTML = "<input class=\"form-control\" name=\"Fld_ABA_Routing_Nbr\" id=\"Fld_ABA_Routing_Nbr\" placeholder=\"ABA ROUTING #\">";
	
	cell = ligne.insertCell(7);
	cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Reference\" id=\"Fld_Reference\" placeholder=\"REFERENCE\">";
	
	cell = ligne.insertCell(8);
	cell.innerHTML = "<input class=\"form-control\" name=\"comments\" id=\"comments\" placeholder=\"COMMENTS\">";
	
	cell = ligne.insertCell(9);
    cell.innerHTML = "<input type='hidden' name='nbbankaccount' value='"+nbLignesba+"'><input type='hidden' name='act' value='addbankaccount'><input type='submit' value='submit' class=\"form-control\">";
}
<!--End Add A BANK ACCOUNT-->

<!--Add A FORWARDER-->
	function addforwarder(){
    var cell, ligne;
 
    // on recupere l'identifiant (id) de la table qui sera modifiee
    var tableau = document.getElementById("dataTableforw");
    // nombre de lignes dans la table (avant ajout de la ligne)
    var nbLignesfo = tableau.rows.length;
 
    ligne = tableau.insertRow(-1); // creation d'une ligne pour ajout en fin de table
                                   // le parametre est dans ce cas (-1)
    ligne.id='row_'+eval(nbLignesfo+1);
    // creation et insertion des cellules dans la nouvelle ligne creee
	
	cell = ligne.insertCell(0);
    cell.innerHTML = "<?php echo $varshipperselect;?>";
	
	cell = ligne.insertCell(1);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Shipper_Contact_Name_Forw\" id=\"Fld_Shipper_Contact_Name_Forw\" placeholder=\"CONTACT NAME\">";
	
	cell = ligne.insertCell(2);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Shipper_Contact_Phone_Forw\" id=\"Fld_Shipper_Contact_Phone_Forw\" placeholder=\"CONTACT PHONE\">";
	
	cell = ligne.insertCell(3);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Account_Nbr\" id=\"Fld_Account_Nbr\" placeholder=\"ACCOUNT #\">";
	
	cell = ligne.insertCell(4);
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_Remark\" id=\"Fld_Remark\" placeholder=\REMARK\">";
	
	cell = ligne.insertCell(5);
    cell.innerHTML = "<input type='hidden' name='nbforwarder' value='"+nbLignesfo+"'><input type='hidden' name='act' value='addforwarder'><input type='submit' value='submit' class=\"form-control\">";
}
<!--End Add A FORWARDER-->	
	
	<!--Modification address company-->
function modif_address_company(id)
{
var bloc=document.getElementById('blocdetailscompany');
//if(bloc.style.display=='table-row') bloc.style.display='none';
//else
    {
bloc.style.display='inline';

document.getElementById("divdetailscompany").innerHTML='<div id="divdetailscompany" align="center"><img src="../images/loader.gif" border="0"></div>';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "modif_address_company.php?id="+id, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { up_donnee_address_company(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
    }
}
function up_donnee_address_company(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divdetailscompany').innerHTML='<div id="'+id+'" align="center">';
         var resp;
        resp = xhr.responseText;
        document.getElementById('divdetailscompany').innerHTML+=resp;
    document.getElementById('divdetailscompany').innerHTML+='</div>';
	document.location.href="#blocdetailscompany";//je redirige le lien vers le haut de la banniere (l'ancre haut)
    }
}

<!--Fin Modification address company-->

<!--Modification contact company-->
function modif_contact_company(id)
{
var bloc=document.getElementById('bloccontactcompany');
//if(bloc.style.display=='table-row') bloc.style.display='none';
//else
    {
bloc.style.display='inline';

document.getElementById("divcontactcompany").innerHTML='<div id="divcontactcompany" align="center"><img src="../images/loader.gif" border="0"></div>';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "modif_contact_company.php?id="+id, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { up_donnee_contact_company(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
    }
}
function up_donnee_contact_company(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divcontactcompany').innerHTML='<div id="'+id+'" align="center">';
         var resp;
        resp = xhr.responseText;
        document.getElementById('divcontactcompany').innerHTML+=resp;
    document.getElementById('divcontactcompany').innerHTML+='</div>';
	document.location.href="#bloccontactcompany";//je redirige le lien vers le haut de la banniere (l'ancre haut)
    }
}

<!--End Modification contact company-->
<!--*****************************************************************************************************-->
<!--*****************************************************************************************************-->
<!--*****************************************************************************************************-->



</script>
</body>

</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php\">";
?>