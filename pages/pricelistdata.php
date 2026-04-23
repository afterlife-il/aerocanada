						
<?php

//exemple code https://coderexample.com/datatable-demo-server-side-in-phpmysql-and-ajax/
/* Database connection start */
$servername = "localhost";
$username = "aerocanada-indus";
$password = "0Ewb9o~9";
$dbname = "aerocanada";

$conn = mysqli_connect($servername, $username, $password, $dbname) or die("Connection failed: " . mysqli_connect_error());

/* Database connection end */
// ***tbl_price_list*** id  CMM_Reference  pn  Description  brand  model  Sales_Unit  Lead_Time_Days  MOQ  Unit_price  Currency  id_company

// storing  request (ie, get/post) global array to a variable  
$requestData= $_REQUEST;

$columns = array( 
// datatable column index  => database column name
	0 =>'id', 
	1 => 'CMM_Reference',
	2=> 'pn',
	3=> 'Description',
	4=> 'brand',
	5=> 'model',
	6=> 'Sales_Unit',
	7=> 'Lead_Time_Days',
	8=> 'MOQ',
	9=> 'Unit_price',
	10=> 'Currency',
	11=> 'id_company'

);

// getting total number records without any search
$sql = "SELECT * from tbl_price_list";
//echo $sql;
$query=mysqli_query($conn, $sql) or die("server_processing.php: get employees");
$totalData = mysqli_num_rows($query);
$totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.

$sql = "SELECT * ";
$sql.=" FROM tbl_price_list WHERE 1=1";
if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
	$sql.=" AND ( id LIKE '%".$requestData['search']['value']."%' ";  
	//$sql.=" OR Country LIKE '%".$requestData['search']['value']."%' ";
	//$sql.=" OR Airline LIKE '%".$requestData['search']['value']."%' )";
			//*******requete d'affichage de toutes les colonnes d'une table
			$sql2 = "SHOW COLUMNS from tbl_price_list";
			$query2=mysqli_query($conn, $sql2) or die("server_processing.php: get employees");
			while( $row2=mysqli_fetch_array($query2) ) {
			$sql.=" OR ".$row2["Field"]." LIKE '%".$requestData['search']['value']."%' ";
														}
			//******requete d'affichage de toutes les colonnes d'une table
	$sql.=" )";
}
$query=mysqli_query($conn, $sql) or die("server_processing.php: get employees");
$totalFiltered = mysqli_num_rows($query); // when there is a search parameter then we have to modify total number filtered rows as per search result. 
$sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";


/* $requestData['order'][0]['column'] contains colmun index, $requestData['order'][0]['dir'] contains order such as asc/desc  */	
$query=mysqli_query($conn, $sql) or die("server_processing.php: get employees");

$data = array();
while( $row=mysqli_fetch_array($query) ) {  // preparing an array
	$nestedData=array(); 
                      
	$nestedData[] = $row["id"];
	$nestedData[] = htmlspecialchars($row["CMM_Reference"]);
	$nestedData[] = htmlspecialchars($row["pn"]);
	$nestedData[] = htmlspecialchars($row["Description"]);
	$nestedData[] = htmlspecialchars($row["brand"]);
	$nestedData[] = htmlspecialchars($row["model"]);
	$nestedData[] = htmlspecialchars($row["Sales_Unit"]);
	$nestedData[] = htmlspecialchars($row["Lead_Time_Days"]);
	$nestedData[] = htmlspecialchars($row["MOQ"]);
	$nestedData[] = htmlspecialchars($row["Unit_price"]);
	$nestedData[] = htmlspecialchars($row["Currency"]);
	
											//Recuperation du nom de la company
											//tb_company****  Fld_Company_ID Company_Old_Id  Fld_Company_Name  Fld_Company_Rating_ID  delete  companyrating  aci_contact  logocompany  status  internet  cage_code
											include_once "conf.php";
include_once "page_titles.php";
					                        $sqlidcon="SELECT Fld_Company_Name FROM tb_company where Fld_Company_ID='".$row["id_company"]."'";
											
											$resultidcon = mysql2_query($sqlidcon);
											$dataidcon = mysqli_fetch_array($resultidcon);
					                        //Fin Recuperation du nom de la company
	
	$nestedData[] = htmlspecialchars($dataidcon["Fld_Company_Name"]);
	

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
