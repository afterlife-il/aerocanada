<?php
class users
{
	// Attributs
	//**tbl_Employee**  Employee_ID   Employee_Name    Fld_Contact_Id  pw  email  statut  position  tel  mobile  skype numformat  pwgmaero
    public $Employee_ID;
    public $Employee_Name;
    public $Fld_Contact_Id;
    public $pw;
    public $email;
    public $statut;
    public $position;
    public $tel;
    public $mobile;
    public $skype;
    public $numformat;
    public $pwgmaero;
	
	public function affichage_users()
	{
		$res=array();
		$req="SELECT * FROM tbl_Employee";
		$requete=mysql2_query($req);
		
		while($reponse=mysqli_fetch_array($requete)){
		$res[]=$reponse;
													}
	return $res;
	}
	
	public function display_employee($Employee_ID)
	{
		$res=array();
		$req="SELECT * FROM tbl_Employee where Employee_ID='".$Employee_ID."'";
		$requete=mysql2_query($req);
		
		$reponse=mysqli_fetch_array($requete);
		$res[]=$reponse;
		return $res;
	}
	
public function verif_login($email, $pw)
{
    $resultlogin = array();

    // Normaliser l’email reçu (minuscule et sans espace)
    $email = strtolower(trim($email));
    $pw = trim($pw);

    // Requête SQL avec normalisation (si besoin tu peux aussi forcer en BDD)
    $dbconn = $GLOBALS['db_link'] ?? $GLOBALS['link'] ?? $GLOBALS['conn'];
    $query = "SELECT * FROM tbl_Employee
              WHERE LOWER(TRIM(email)) = '".mysqli_real_escape_string($dbconn, $email)."'";

    $result = mysql2_query($query);
    while ($tab = mysqli_fetch_array($result, MYSQLI_BOTH)) {
        $resultlogin[] = $tab;
    }
    return $resultlogin;
}

	public function add_user()
	{
		 $requete = mysql2_query("INSERT INTO tbl_Employee (`Employee_ID`,`Employee_Name`, `Fld_Contact_Id`, `pw`, `email`, `statut`, `position`, `tel`, `mobile`, `skype`, `pwgmaero`)
		 VALUES ('','".$_POST['Employee_Name']."','','".$_POST['pw']."','".$_POST['email']."','".$_POST['statut']."','".$_POST['position']."','".$_POST['tel']."','".$_POST['mobile']."','".$_POST['skype']."','".$_POST['pwgmaero']."');");
	}
	public function modif_user()
	{
		 $sql="update tbl_Employee set Employee_Name='".$_POST['Employee_Name']."',email='".$_POST['email']."',pw='".$_POST['pw']."',statut='".$_POST['statut']."',position='".$_POST['position']."',tel='".$_POST['tel']."',mobile='".$_POST['mobile']."',skype='".$_POST['skype']."',pwgmaero='".$_POST['pwgmaero']."' where Employee_ID='".$_POST['Employee_ID']."'";
		$query=mysql2_query($sql);
	}
	public function del_user($Employee_ID)
	{
		 $result = mysql2_query("DELETE FROM tbl_Employee where Employee_ID='".$Employee_ID."'"); 
	}
	
	/*************************Managing Site Connections**********************************/
	//****tbl_connection******   id_connection	Employee_ID	ip_address	connection_date  session_id
	public function connection_user($Employee_ID,$ip_address,$session_id)
	{
		 $today = date("Y-m-d H:i:s");
		 $requete = mysql2_query("INSERT INTO tbl_connection (`id_connection`,`Employee_ID`, `ip_address`, `connection_date`, `session_id`)
		 VALUES ('','".$Employee_ID."','".$ip_address."','".$today."','".$session_id."');");
	}
	/*************************Managing Site Connections End**********************************/
}
?>