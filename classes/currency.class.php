<?php
class currency
{
	// Attributs
	//**tbl_Currency**  Fld_Currency_ID Fld_Currency_Text
    public $Fld_Currency_ID;
    public $Fld_Currency_Text;

	
	public function affichage_currency()
	{
		$res=array();
		$req="SELECT * FROM tbl_Currency";
		$requete=mysql2_query($req);
		
		while($reponse=mysqli_fetch_array($requete)){
		$res[]=$reponse;
													}
	return $res;
	}

	public function add_currency()
	{	
		 
		 $requete = mysql2_query("INSERT INTO tbl_Currency (`Fld_Currency_ID`,`Fld_Currency_Text`) VALUES ('','".$_POST['Fld_Currency_Text']."');");
	}
	public function modif_currency()
	{
		
		 $sql="update tbl_Currency set Fld_Currency_Text='".$_GET['Fld_Currency_Text']."' where Fld_Currency_ID='".$_GET['Fld_Currency_ID']."'";
		$query=mysql2_query($sql);
	}
	public function del_currency($Fld_Currency_ID)
	{
		 $result = mysql2_query("DELETE FROM tbl_Currency where Fld_Currency_ID='".$Fld_Currency_ID."'"); 
	}
}
?>