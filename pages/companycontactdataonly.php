<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
    echo json_encode(["draw"=>0,"recordsTotal"=>0,"recordsFiltered"=>0,"data"=>[]]);
    exit;
}

include_once "conf.php";


// storing  request (ie, get/post) global array to a variable  
$requestData= $_REQUEST;

$columns = array( 
// datatable column index  => database column name


	0 =>'id_company_contact', 
	1 =>'Fld_Linked_ID', 
	2=> 'Fld_Company_ID',
	3=> 'Company_Old_Id',
	4=> 'Fld_Contact_Name',
	5=> 'Fld_Contact_Phone',
	6=> 'Fld_Contact_Phone2',
	7=> 'Fld_Contact_Fax',
	8=> 'Fld_Company_Mobile',
	9=> 'Fld_Contact_Division_ID',
	10=> 'Fld_Contact_Email',
	11=> 'Fld_Contact_Title',
	12=> 'Fld_Contact_Remark',
	13=> 'status',
	14=> 'aci_contact',
	15=> 'entry_date'
);

// getting total number records without any search
//****tb_company_contact*****id_company_contact Fld_Linked_ID Fld_Company_ID Company_Old_Id Fld_Contact_Name Fld_Contact_Phone Fld_Contact_Phone2 Fld_Contact_Fax Fld_Company_Mobile Fld_Contact_Division_ID Fld_Contact_Email Fld_Contact_Title Fld_Contact_Remark status aci_contact entry_date

$sql = "SELECT * FROM tb_company_contact where status='Available'";
//echo $sql;
$query=mysqli_query($conn, $sql) or die("server_processing.php: get employees");
$totalData = mysqli_num_rows($query);
//echo $totalData;
$totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.

$sql ="SELECT * FROM tb_company_contact where tb_company_contact.status='Available'";

if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
	$sql.=" AND ( tb_company_contact.id_company_contact LIKE '%".$requestData['search']['value']."%' ";  

			//*******requete d'affichage de toutes les colonnes d'une table
			$sql.=" OR tb_company_contact.Fld_Contact_Name LIKE '%".$requestData['search']['value']."%' ";
			$sql.=" OR tb_company_contact.Fld_Contact_Email LIKE '%".$requestData['search']['value']."%' ";
			$sql.=" OR tb_company_contact.Fld_Contact_Remark LIKE '%".$requestData['search']['value']."%' ";
			//******requete d'affichage de toutes les colonnes d'une table
			$sql.=")";

}

$query=mysqli_query($conn, $sql) or die("server_processing.php: get employees");
$totalFiltered = mysqli_num_rows($query); // when there is a search parameter then we have to modify total number filtered rows as per search result. 
//$sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
$sql.=" ORDER BY tb_company_contact.Fld_Contact_Name Desc  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";


/* $requestData['order'][0]['column'] contains colmun index, $requestData['order'][0]['dir'] contains order such as asc/desc  */	
$query=mysqli_query($conn, $sql) or die("server_processing.php: get employees");

$data = array();
while( $row=mysqli_fetch_array($query) ) {  // preparing an array
	$nestedData=array(); 

	$nestedData[] = htmlspecialchars($row["Fld_Contact_Name"]);
	$nestedData[] = $row["Fld_Contact_Phone"];
	$nestedData[] = $row["Fld_Contact_Phone2"];
	$nestedData[] = $row["Fld_Contact_Fax"];
	$nestedData[] = $row["Fld_Company_Mobile"];
	
											//recuperation du nom de la division	
											 //*******tbl_Division*********Fld_Division_ID    Fld_Division_Text
											$sqldiv="SELECT * FROM tbl_Division where Fld_Division_ID=".(int)$row['Fld_Contact_Division_ID'];
											
											//echo $sqldiv;
											$reqemp = mysql2_query($sqldiv);
											$datadiv = mysqli_fetch_array($reqemp);
											
					                        //Fin recuperation des type de compagnie
	
	$nestedData[] = htmlspecialchars($datadiv['Fld_Division_Text']);
	
	$nestedData[] = htmlspecialchars($row["Fld_Contact_Email"]);
	$nestedData[] = htmlspecialchars($row["Fld_Contact_Title"]);
	$nestedData[] = htmlspecialchars($row["Fld_Contact_Remark"]);
					
	$data[] = $nestedData;
}

$json_data = array(
			"draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
			"recordsTotal"    => intval( $totalData ),  // total number of records
			"recordsFiltered" => intval( $totalFiltered ), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data"            => $data   // total data array
			);

echo json_encode($json_data);  // send data as json format

?>
