<?php
class parts
{
	// Attributs
	//*****tbl_Parts*************  Fld_Part_ID  Fld_Part_Nbr  Fld_Part_Desc  Fld_Part_MFG  Fld_Part_MFG_Old  Fld_AC_ID  Fld_Old_LP  Fld_Part_List_Price  Fld_Part_Price_Currency_ID  Fld_Part_LP_Date  Fld_Remark status alt_pn Fld_Add_PN_Date aci_contact_entry ata_chapter   cage_code    essentiality_category_id    nha   moq   oem_lead_time  core_value  id_currency_core_value
	public $Fld_Part_ID;
	public $Fld_Part_Nbr;
	public $Fld_Part_Desc;
	public $Fld_Part_MFG;
	public $Fld_Part_MFG_Old;
	public $Fld_AC_ID;
	public $Fld_Old_LP;
	public $Fld_Part_List_Price;
	public $Fld_Part_Price_Currency_ID;
	public $Fld_Part_LP_Date;
	public $Fld_Remark;
	public $status;
	public $alt_pn;
	public $Fld_Add_PN_Date;
	public $aci_contact_entry;
	public $ata_chapter;
	public $cage_code;
	public $essentiality_category_id;
	public $nha;
	public $moq;
	public $oem_lead_time;
	public $core_value;
	public $id_currency_core_value;
	
	public function get_part($Fld_Part_ID)
	{
		$res=array();
		$req="SELECT * FROM tbl_Parts where Fld_Part_ID=".$Fld_Part_ID;
		$requete=mysql2_query($req);
		
		while($reponse=mysqli_fetch_array($requete)){
			$res[]=$reponse;
		}
		return $res;
	}
	
	public function add_part()
	{
		$sql = mysql2_query("SELECT COUNT(*) AS total FROM tbl_Parts where Fld_Part_Nbr='".$_POST['Fld_Part_Nbr']."'");
		$row = mysqli_fetch_assoc($sql);
		// if($row['total']=='0') {
			if (!empty($_POST['Fld_Part_MFG'])) {
				$companyid = explode(",", $_POST['Fld_Part_MFG']);
				$companyidrecup=$companyid[0]; 
			}
			$req="INSERT INTO tbl_Parts (`Fld_Part_ID`,`Fld_Part_Nbr`, `Fld_Part_Desc`, `Fld_Part_MFG`, `Fld_Part_MFG_Old`, `Fld_AC_ID`, `Fld_Old_LP`, `Fld_Part_List_Price`, `Fld_Part_Price_Currency_ID`, `Fld_Part_LP_Date`, `Fld_Remark`, `status`, `alt_pn`, `Fld_Add_PN_Date`, `aci_contact_entry`, `ata_chapter`, `oem_lead_time`, `core_value`, `id_currency_core_value`)
			VALUES ('','".$_POST['Fld_Part_Nbr']."','".addslashes($_POST['Fld_Part_Desc'])."','".$companyidrecup."','".$_POST['Fld_Part_MFG_Old']."','".$_POST['Fld_AC_ID']."','".$_POST['Fld_Old_LP']."','".$_POST['Fld_Part_List_Price']."','".$_POST['Fld_Part_Price_Currency_ID']."','".$_POST['Fld_Part_LP_Date']."','".addslashes($_POST['Fld_Remark'])."','Available','".$_POST['alt_pn']."','".$_POST['Fld_Add_PN_Date']."','".$_POST['aci_contact_entry']."','".$_POST['ata_chapter']."','".$_POST['oem_lead_time']."','".$_POST['core_value']."','".$_POST['id_currency_core_value']."');";
				$requete = mysql2_query($req);
			// }
		}
		public function add_multi_parts()
		{
			for($i=1;$i<=$_POST['nbpnadd'];$i++)
			{
				if(!empty($_POST['Fld_Part_Nbr'.$i]))
				{
					$sql = mysql2_query("SELECT COUNT(*) AS total FROM tbl_Parts where Fld_Part_Nbr='".$_POST['Fld_Part_Nbr'.$i]."'");
					$row = mysqli_fetch_assoc($sql);
					// if($row['total']=='0')
					// {
						$req="INSERT INTO tbl_Parts (`Fld_Part_ID`,`Fld_Part_Nbr`, `Fld_Part_Desc`, `Fld_Part_MFG`, `Fld_Part_MFG_Old`, `Fld_AC_ID`, `Fld_Old_LP`, `Fld_Part_List_Price`, `Fld_Part_Price_Currency_ID`, `Fld_Part_LP_Date`, `Fld_Remark`, `status`, `alt_pn`, `Fld_Add_PN_Date`, `aci_contact_entry`, `ata_chapter`)
						VALUES ('','".$_POST['Fld_Part_Nbr'.$i]."','".addslashes($_POST['Fld_Part_Desc'.$i])."','','','','','','','".$_POST['Fld_Part_LP_Date']."','','Available','','".$_POST['Fld_Add_PN_Date']."','".$_POST['aci_contact_entry']."','');";
		 // echo $req;
							$requete = mysql2_query($req);
						}
					// }
				}
			}
			public function add_docs()
			{
									//********************************telechargement du document
									//******************************************************
				
									//je verifie si il existe deja une photo si oui je l'efface avant de charger la nouvelle
				if(!empty($_FILES["docsattachment"]["name"]))
				{
					$nom_docs_attachment=$_FILES["docsattachment"]["name"];
										$nom_docs_attachment=str_replace(' ', '_', $nom_docs_attachment); // on remplace les espaces par des _
										
										
										
									//Fin je verifie si il existe deja une photo si oui je l'efface avant de charger la nouvelle
										$fichelogo="";
										$target_dir = "../docsattachment/";
									// $target_file = $target_dir . basename($_FILES["docsattachment"]["name"]);
										$target_file = $target_dir . $nom_docs_attachment;
										$uploadOk = 1;
										$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
										
									// Check if image file is a actual image or fake image
									// if(isset($_POST["submit"])) {
										// $check = getimagesize($_FILES["docsattachment"]["tmp_name"]);
										// if($check !== false) {
											// echo "Le fichier est une image - " . $check["mime"] . ".";
											// $uploadOk = 1;
										// } else {
											// echo "Ce fichier n'est pas une image.";
											// $uploadOk = 0;
										// }
									// }
									// Check if file already exists
										if (file_exists($target_file)) {
											echo "Désolé, ce fichier existe déjà.";
											$uploadOk = 0;
										}
									// Check file size
										if ($_FILES["docsattachment"]["size"] > 10000000) {
											echo "Désolé, votre fichier est trop volumineux.";
											$uploadOk = 0;
										}
									// Allow certain file formats
										if($imageFileType == "exe" ) {
											echo "Désolé, les fichiers EXE sont pas autorisés.";
											$uploadOk = 0;
										}
									// Check if $uploadOk is set to 0 by an error
										if ($uploadOk == 0) {
											echo "Désolé, votre fichier n'a pas été téléchargé.";
									// if everything is ok, try to upload file
										} else {
											if (move_uploaded_file($_FILES["docsattachment"]["tmp_name"], $target_file)) {
												echo "Le fichier ". basename( $_FILES["docsattachment"]["name"]). " a été téléchargé.";
												$fichelogo="ok";
												
											} 
											else {
												echo "Désolé, il y a une erreur de chargement dans votre fichier.";
												$fichelogo="no";
											}
										}
										
									}
									
									//********************************Fin telechargement du document
									//**********************************************************
									// **tbl_docs_attachment_pn** id_docs_attachment_pn   name docs_name   pn    pn_id
									$req="INSERT INTO tbl_docs_attachment_pn (`id_docs_attachment_pn`,`name`,`docs_name`, `pn`, `pn_id`)
									VALUES ('','".$_POST['docs_name']."','".$nom_docs_attachment."','".$_POST['pn']."','".$_POST['pn_id']."');";
									// echo $req;
										$requete = mysql2_query($req);
										
									}
									public function del_doc_pn($id_docs_attachment_pn)
									{	
										$sqldapn="SELECT * FROM tbl_docs_attachment_pn where id_docs_attachment_pn='".$id_docs_attachment_pn."'";
										$reqdapn = mysql2_query($sqldapn);
										$datadapn = mysqli_fetch_array($reqdapn);
										$docs_name=$datadapn['docs_name'];
										$result = mysql2_query("DELETE FROM tbl_docs_attachment_pn where id_docs_attachment_pn='".$id_docs_attachment_pn."'"); 
										unlink ("../docsattachment/".$docs_name); 
									}
									public function modif_part()
									{
										$sql="update tbl_Parts set Fld_Part_Nbr='".$_POST['Fld_Part_Nbr']."',Fld_Part_Desc='".addslashes($_POST['Fld_Part_Desc'])."',Fld_Part_MFG='".$_POST['Fld_Part_MFG']."',Fld_AC_ID='".$_POST['Fld_AC_ID']."',Fld_Part_List_Price='".$_POST['Fld_Part_List_Price']."',Fld_Part_Price_Currency_ID='".$_POST['Fld_Part_Price_Currency_ID']."',Fld_Remark='".addslashes($_POST['Fld_Remark'])."',alt_pn='".$_POST['alt_pn']."' where Fld_Part_ID='".$_POST['Fld_Part_ID']."'";
										$query=mysql2_query($sql);
									}
									public function archive_part($Fld_Part_ID)
									{
										$sql="update tbl_Parts set status='archive' where Fld_Part_ID='".$Fld_Part_ID."'";
										$query=mysql2_query($sql);
									}
									
									public function del_part($Fld_AC_ID)
									{
										$result = mysql2_query("DELETE FROM tbl_Parts where Fld_Part_ID='".$Fld_AC_ID."'"); 
									}
									
								}
							?>