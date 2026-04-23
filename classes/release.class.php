<?php
class release
{
	// Attributs
	//**tbl_Release**    Fld_Release_ID  Fld_Release_Text
    public $Fld_Release_ID;
    public $Fld_Release_Text;

	
	public function affichage_release()
	{
		$res=array();
		$req="SELECT * FROM tbl_Release";
		$requete=mysql2_query($req);
		
		while($reponse=mysqli_fetch_array($requete)){
		$res[]=$reponse;
													}
	return $res;
	}

	public function add_release()
	{	
		 
		 $requete = mysql2_query("INSERT INTO tbl_Release (`Fld_Release_ID`,`Fld_Release_Text`) VALUES ('','".$_POST['Fld_Release_Text']."');");
	}
	public function modif_release()
	{
		
		 $sql="update tbl_Release set Fld_Release_Text='".$_GET['Fld_Release_Text']."' where Fld_Release_ID='".$_GET['Fld_Release_ID']."'";
		$query=mysql2_query($sql);
	}
	public function del_release($Fld_Release_ID)
	{
		 $result = mysql2_query("DELETE FROM tbl_Release where Fld_Release_ID='".$Fld_Release_ID."'"); 
	}
}
?>