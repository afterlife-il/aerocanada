						
<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);



//exemple code https://coderexample.com/datatable-demo-server-side-in-phpmysql-and-ajax/
/* Database connection start */
include_once "conf.php";
include_once "page_titles.php";
/* Database connection end */


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

// 1. Requête initiale : total des données
$sql = "SELECT * FROM tb_company_contact WHERE status='Available'";
$query = mysqli_query($conn, $sql);
if (!$query) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Erreur SQL initiale : " . mysqli_error($conn)]);
    exit;
}
$totalData = mysqli_num_rows($query);
$totalFiltered = $totalData;

// 2. Requête avec filtre de recherche
$sql = "SELECT tb_company_contact.*, tb_company.Fld_Company_Name 
        FROM tb_company_contact, tb_company 
        WHERE tb_company_contact.Fld_Company_ID = tb_company.Fld_Company_ID 
        AND tb_company_contact.status = 'Available'";

if (!empty($requestData['search']['value'])) {
    $search = mysqli_real_escape_string($conn, $requestData['search']['value']);
    $sql .= " AND (( tb_company_contact.id_company_contact LIKE '%$search%' 
              OR tb_company_contact.Fld_Contact_Name LIKE '%$search%' 
              OR tb_company_contact.Fld_Contact_Email LIKE '%$search%' 
              OR tb_company_contact.Fld_Contact_Remark LIKE '%$search%' ) 
              OR tb_company.Fld_Company_Name LIKE '%$search%' )";
}

$query = mysqli_query($conn, $sql);
if (!$query) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Erreur SQL après recherche : " . mysqli_error($conn)]);
    exit;
}
$totalFiltered = mysqli_num_rows($query);

// 3. Ajout pagination
$sql .= " ORDER BY tb_company.Fld_Company_Name DESC 
          LIMIT " . intval($requestData['start']) . ", " . intval($requestData['length']);

$query = mysqli_query($conn, $sql);
if (!$query) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Erreur SQL finale (pagination) : " . mysqli_error($conn)]);
    exit;
}

$data = array();
while( $row=mysqli_fetch_array($query) ) {  // preparing an array
	$nestedData=array(); 

	$nestedData[] = htmlspecialchars($row["Fld_Company_Name"] ?? '');
	$nestedData[] = htmlspecialchars($row["Fld_Contact_Name"] ?? '');
	$nestedData[] = $row["Fld_Contact_Phone"];
	$nestedData[] = $row["Fld_Contact_Phone2"];
	$nestedData[] = $row["Fld_Company_Mobile"];
	$nestedData[] = htmlspecialchars($row["Fld_Contact_Email"] ?? '');
	$nestedData[] = $row["Fld_Contact_Division_ID"];
	$nestedData[] = htmlspecialchars($row["Fld_Contact_Remark"] ?? '');
	
								//recuperation nom employee
								$sqlemp="SELECT Employee_Name FROM tbl_Employee where Employee_ID=".$row['aci_contact'];
								$reqemp=mysqli_query($conn, $sqlemp);
								$dataemp = mysqli_fetch_array($reqemp);
								//Fin recuperation nom employee
					
			$nestedData[] = htmlspecialchars($dataemp['Employee_Name'] ?? '', ENT_QUOTES, 'UTF-8');
					
			$nestedData[] = $row["entry_date"];
					
	$data[] = $nestedData;
}

$json_data = array(
			"draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
			"recordsTotal"    => intval( $totalData ),  // total number of records
			"recordsFiltered" => intval( $totalFiltered ), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data"            => $data   // total data array
			);
header('Content-Type: application/json');
echo json_encode($json_data);  // send data as json format

?>
