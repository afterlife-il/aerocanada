						
<?php

//exemple code https://coderexample.com/datatable-demo-server-side-in-phpmysql-and-ajax/
/* Database connection start */
$servername = "localhost";
$username = "aerocanada-indus";
$password = "0Ewb9o~9";
$dbname = "aerocanada";

$conn = mysqli_connect($servername, $username, $password, $dbname) or die("Connection failed: " . mysqli_connect_error());

/* Database connection end */


// storing  request (ie, get/post) global array to a variable  
$requestData= $_REQUEST;

$columns = array( 
// datatable column index  => database column name
	0 =>'id_tb_JPFleet', 
	1 => 'Country',
	2=> 'Airline',
	3=> 'registration',
	4=> 'type_of_aircraft',
	5=> 'cn_fn',
	6=> 'exreg',
	7=> 'mfd'
);

// getting total number records without any search
$sql = "SELECT * from tb_JPFleet";
$query=mysqli_query($conn, $sql) or die("server_processing.php: get employees");
$totalData = mysqli_num_rows($query);
$totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


$sql = "SELECT * ";
$sql.=" FROM tb_JPFleet WHERE 1=1";
if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
	$sql.=" AND ( Country LIKE '".$requestData['search']['value']."%' ";    
	$sql.=" OR employee_salary LIKE '".$requestData['search']['value']."%' ";

	$sql.=" OR Airline LIKE '".$requestData['search']['value']."%' )";
}
$query=mysqli_query($conn, $sql) or die("server_processing.php: get employees");
$totalFiltered = mysqli_num_rows($query); // when there is a search parameter then we have to modify total number filtered rows as per search result. 
//$sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
echo $sql;

/* $requestData['order'][0]['column'] contains colmun index, $requestData['order'][0]['dir'] contains order such as asc/desc  */	
$query=mysqli_query($conn, $sql) or die("server_processing.php: get employees");

$data = array();
while( $row=mysqli_fetch_array($query) ) {  // preparing an array
	$nestedData=array(); 

	$nestedData[] = $row["id_tb_JPFleet"];
	$nestedData[] = $row["Country"];
	$nestedData[] = $row["Airline"];
	$nestedData[] = $row["registration"];
	$nestedData[] = $row["type_of_aircraft"];
	$nestedData[] = $row["cn_fn"];
	$nestedData[] = $row["exreg"];
	$nestedData[] = $row["mfd"];

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
