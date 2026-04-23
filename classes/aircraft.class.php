<?php
class aircraft
{
	// Attributs
	//************** tbl_Aircraft ************ Fld_AC_ID  Fld_AC_Model  Fld_AC_Series  Fld_AC_Manufacturer  Fld_AC_Engine_Model  Fld_AC_Engine_Series
    public $Fld_AC_ID;
    public $Fld_AC_Model;
    public $Fld_AC_Series;
    public $Fld_AC_Manufacturer;
    public $Fld_AC_Engine_Model;
    public $Fld_AC_Engine_Series;
	
	public function add_aircraft()
	{
		 $requete = mysql2_query("INSERT INTO tbl_Aircraft (`Fld_AC_ID`,`Fld_AC_Model`, `Fld_AC_Series`, `Fld_AC_Manufacturer`, `Fld_AC_Engine_Model`, `Fld_AC_Engine_Series`)
		 VALUES ('','".$_POST['Fld_AC_Model']."','".$_POST['Fld_AC_Series']."','".$_POST['Fld_AC_Manufacturer']."','".$_POST['Fld_AC_Engine_Model']."','".$_POST['Fld_AC_Engine_Series']."');");
	}
	public function affichage_aircrafts()
	{
		$res=array();
		$req="SELECT * FROM tbl_Aircraft";
		$requete=mysql2_query($req);
		
		while($reponse=mysqli_fetch_array($requete)){
		$res[]=$reponse;
													}
	return $res;
	}
	/*
	
	public function verif_login($email,$pw)
	{
		 $resultlogin=array();
		 $requete=mysql2_query("SELECT * FROM tbl_Employee where email='".$email."' and pw='".$pw."'");
		 while($reponse=mysqli_fetch_array($requete)) 
		 {
		 $resultlogin[]=$reponse;
		 }
		 return $resultlogin;
		 
		 //$num_rows = mysqli_num_rows($requete);
		 //return $num_rows;

	}
	*/
	public function modif_aircraft()
	{
		 $sql="update tbl_Aircraft set Fld_AC_Model='".$_POST['Fld_AC_Model']."',Fld_AC_Series='".$_POST['Fld_AC_Series']."',Fld_AC_Manufacturer='".$_POST['Fld_AC_Manufacturer']."',Fld_AC_Engine_Model='".$_POST['Fld_AC_Engine_Model']."',Fld_AC_Engine_Series='".$_POST['Fld_AC_Engine_Series']."' where Fld_AC_ID='".$_POST['Fld_AC_ID']."'";
		$query=mysql2_query($sql);
	}
	public function del_aircraft($Fld_AC_ID)
	{
		 $result = mysql2_query("DELETE FROM tbl_Aircraft where Fld_AC_ID='".$Fld_AC_ID."'"); 
	}
	
}
?>