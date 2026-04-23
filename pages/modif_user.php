<?php

$Employee_ID=$_GET['id'];
include_once "conf.php";
/**************************************************************Details User****************************
	//**tbl_Employee**  Employee_ID   Employee_Name    Fld_Contact_Id  pw  email  statut  position  tel  mobile  skype numformat  pwgmaero
*/
// getting total number records without any search
$sql="SELECT * FROM tbl_Employee where Employee_ID=".$Employee_ID;	

$req = mysql2_query($sql);
$nbrows = mysqli_num_rows($req);
//echo $nbrows;
if(0<$nbrows){
?>
<form action="validation_modif_users.php" method="post">
 <table width="100%" class="table table-striped table-bordered table-hover" id="dataTables-example">
                                <thead>
                                    <tr>
                                        <th>Employee ID</th>
                                        <th>Employee Name</th>
                                        <th>Tel</th>
                                            <th>Mobile</th>
                                            <th>Email</th>
                                            <th>MDP Gmail</th>
                                            <th>Skype</th>
                                            <th>Position</th>
                                        <th>Password</th>
                                        <th>Permission profile</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
								
								<?php
		
					while ($data = mysqli_fetch_array($req))
					{ 
					
					echo "<tr>";
					echo "<td><input type=\"hidden\" name=\"Employee_ID\" value='".$data['Employee_ID']."'>".$data['Employee_ID']."</td>";
					echo "<td><input class=\"form-control\" name=\"Employee_Name\" id=\"Employee_Name\" placeholder=\"User Name\" value=\"".$data['Employee_Name']."\"></td>";
					echo "<td><input class=\"form-control\" name=\"tel\" id=\"tel\" placeholder=\"Tel\" value=\"".$data['tel']."\"></td>";
					echo "<td><input class=\"form-control\" name=\"mobile\" id=\"mobile\" placeholder=\"Mobile\" value=\"".$data['mobile']."\"></td>";
					echo "<td><input class=\"form-control\" name=\"email\" id=\"email\" placeholder=\"E-mail\" value=\"".$data['email']."\"></td>";
					echo "<td><input class=\"form-control\" name=\"pwgmaero\" id=\"pwgmaero\" placeholder=\"MDP Gmail\" value=\"".$data['pwgmaero']."\"></td>";
					echo "<td><input class=\"form-control\" name=\"skype\" id=\"skype\" placeholder=\"Skype\" value=\"".$data['skype']."\"></td>";
					echo "<td><input class=\"form-control\" name=\"position\" id=\"position\" placeholder=\"Position\" value=\"".$data['position']."\"></td>";
					echo "<td><input class=\"form-control\" name=\"pw\" id=\"pw\" placeholder=\"pw\" value=\"".$data['pw']."\"></td>";
					echo "<td><select class=\"form-control\" name=\"statut\" id=\"statut\"><option value=''>-- Choose --</option><option value='SuperAdmin'";
					if ($data['statut']=='SuperAdmin') echo "selected";
					echo ">SuperAdmin</option><option value='Admin'";
					if ($data['statut']=='Admin') echo "selected";
					echo ">Admin</option><option value='Salesman'";
					if ($data['statut']=='Salesman') echo "selected";
					echo ">Salesman</option></select></td>";
					echo "<td><input type='submit' class=\"form-control\" value='submit'></td>";
					echo "</tr>";
					}
			?>
								
                                </tbody>
                            </table>
							</form>
<?php		
}
else echo "Pas de reponse<br><a href='gestion_contact.php'><img src='images/add_contact.png'></a>";
			//**************************************************************Fin Details Company****************************
			
		