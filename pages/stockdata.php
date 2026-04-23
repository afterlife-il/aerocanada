<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
    echo json_encode(["draw"=>0,"recordsTotal"=>0,"recordsFiltered"=>0,"data"=>[]]);
    exit;
}

include_once "conf.php";
include_once "page_titles.php";


// storing  request (ie, get/post) global array to a variable  
$requestData= $_REQUEST;

$columns = array( 
// datatable column index  => database column name

 0=>'Fld_Stock_ID',
 1=>'Fld_Part_ID', 
 2=>'Fld_Part_SN', 
 3=>'Fld_Supplier_ID', 
 4=>'Fld_Entry_Date', 
 5=>'Fld_Part_Price', 
 6=>'Fld_Price_Currency_ID', 
 7=>'Fld_BAX_PO_Nbr', 
 8=>'Fld_Supplier_order_Date', 
 9=>'Fld_Supplier_Payment_Date', 
 10=>'Fld_Qty', 
 11=>'Fld_Condition_ID', 
 12=>'Fld_Release_ID', 
 13=>'Fld_Tag_Info_ID', 
 14=>'Fld_Tag_Date', 
 15=>'Fld_Traceability_ID', 
 16=>'Fld_Warehouse_Location', 
 17=>'Fld_Physical_Stock', 
 18=>'Fld_Owner_ID', 
 19=>'Fld_Stock_Location_ID', 
 20=>'Fld_Status_ID', 
 21=>'Fld_Status_Ind', 
 22=>'Fld_Status_Date', 
 23=>'Fld_Stock_Remark', 
 24=>'Fld_Shelf_Life_Limit', 
 25=>'Fld_Valeur_Comptable', 
 26=>'Fld_Valeur_Comptable_currency_Id', 
 27=>'Fld_Sales_Remark', 
 28=>'Fld_External_Location', 
 29=>'Fld_Sales_Remark_ID', 
 30=>'Fld_Warehouse_Location_ID', 
 31=>'Fld_OriginalUnit_Stock_ID', 
 32=>'Fld_Min_Qty', 
 33=>'Fld_Publish'
);

// getting total number records without any search
$sql = "SELECT * from tbl_Stock";
//echo $sql;
$query=mysqli_query($db_link, $sql) or die("server_processing.php: get employees");
$totalData = mysqli_num_rows($query);
$totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.
//Table tbl_Parts :::: Fld_Part_ID  Fld_Part_Nbr  Fld_Part_Desc  Fld_Part_MFG  Fld_Part_MFG_Old  Fld_AC_ID  Fld_Old_LP  Fld_Part_List_Price  Fld_Part_Price_Currency_ID  Fld_Part_LP_Date  Fld_Remark status
$sql = "SELECT tbl_Stock.*,tbl_Parts.* from tbl_Stock,tbl_Parts WHERE tbl_Stock.Fld_Part_ID=tbl_Parts.Fld_Part_ID";
if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
	$sql.=" AND (( tbl_Stock.Fld_Stock_ID LIKE '%".$requestData['search']['value']."%' ";  

			//*******requete d'affichage de toutes les colonnes d'une table
			$sql2 = "SHOW COLUMNS from tbl_Stock";
			$query2=mysqli_query($db_link, $sql2) or die("server_processing.php: get employees");
			while( $row2=mysqli_fetch_array($query2) ) {
			$sql.=" OR tbl_Stock.".$row2["Field"]." LIKE '%".$requestData['search']['value']."%' ";
														}
			//******requete d'affichage de toutes les colonnes d'une table
			$sql.=" )";
			$sql.="OR (tbl_Parts.Fld_Part_Nbr LIKE '%".$requestData['search']['value']."%' OR tbl_Parts.Fld_Part_Desc LIKE '%".$requestData['search']['value']."%')";
			$sql.=" )";
}
$query=mysqli_query($db_link, $sql) or die("server_processing.php: get employees");
$totalFiltered = mysqli_num_rows($query); // when there is a search parameter then we have to modify total number filtered rows as per search result. 
$sql.=" ORDER BY tbl_Stock.". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";

//echo $sql;
/* $requestData['order'][0]['column'] contains colmun index, $requestData['order'][0]['dir'] contains order such as asc/desc  */	
$query=mysqli_query($db_link, $sql) or die("server_processing.php: get employees");

$data = array();
while( $row=mysqli_fetch_array($query) ) {  // preparing an array
	$nestedData=array(); 
$nestedData[]=$row['Fld_Stock_ID']; 
$nestedData[]=$row['Fld_Part_Nbr'];
$nestedData[]=$row['Fld_Part_Desc'];
$nestedData[]=$row['Fld_Part_SN']; 
											
											//recuperation du nom de compagnie SUPPLERS ********************
											$sqltiids="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID=".$row['Fld_Supplier_ID'];
											$reqtiids = mysql2_query($sqltiids);
											$datatiids = mysqli_fetch_array($reqtiids);
											//Fin recuperation du nom de compagnie SUPPLERS ********************
											
$nestedData[]=$datatiids['Fld_Company_Name']; 
$nestedData[]=htmlspecialchars($row['Fld_Entry_Date']); 
											
											//formatage du prix
											// $nombre_format_francais = number_format($Fld_Price_recup, 2, ',', ' ');
											setlocale(LC_MONETARY, 'en_US.UTF-8');
											$nombre_format_francais = money_format('%.2n', $row['Fld_Part_Price']);
											//Fin formatage du prix		
$nestedData[]=$nombre_format_francais; 

											//recuperation des currency
											//tbl_Currency---- Fld_Currency_ID Fld_Currency_Text
					                        $sqlcid="SELECT Fld_Currency_Text FROM tbl_Currency where Fld_Currency_ID=".$row["Fld_Price_Currency_ID"];
											
											$reqcid = mysql2_query($sqlcid);
											$datacid = mysqli_fetch_array($reqcid);
					                        //End recuperation of the currency
											
$nestedData[]=$datacid['Fld_Currency_Text']; 
$nestedData[]=$row['Fld_BAX_PO_Nbr'];
$nestedData[]=htmlspecialchars($row['Fld_Supplier_order_Date']);
$nestedData[]=$row['Fld_Supplier_Payment_Date']; 
$nestedData[]=$row['Fld_Qty']; 
											
											//recuperation de conditions ********************
											//tbl_Condition****Fld_Condition_ID  Fld_Condition_Text
											$sqlct="SELECT Fld_Condition_Text FROM  tbl_Condition WHERE Fld_Condition_ID=".$row['Fld_Condition_ID'];
											$reqct = mysql2_query($sqlct);
											$datact = mysqli_fetch_array($reqct);
											//Fin recuperation de conditions ********************
											
$nestedData[]=$datact['Fld_Condition_Text']; 
											
											//recuperation release
											// ** tbl_Release ** Fld_Release_ID  Fld_Release_Text
					                        $sqlr="SELECT Fld_Release_Text from tbl_Release where Fld_Release_ID='".$row['Fld_Release_ID']."'";
											
											$reqr = mysql2_query($sqlr);
											$datar = mysqli_fetch_array($reqr);
					                        //Fin recuperation release 
											
$nestedData[]=$datar['Fld_Release_Text']; 
											//recuperation du nom de compagnie TAG INFO ********************
											$sqltiid="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID='".$row['Fld_Tag_Info_ID']."'";
											$reqtiid = mysql2_query($sqltiid);
											$datatiid = mysqli_fetch_array($reqtiid);
											//Fin recuperation du nom de compagnie TAG INFO ********************
$nestedData[]=$datatiid['Fld_Company_Name']; 
$nestedData[]=htmlspecialchars($row['Fld_Tag_Date']);
$nestedData[]=$row['Fld_Traceability_ID']; 
$nestedData[]=$row['Fld_Warehouse_Location']; 
$nestedData[]=$row['Fld_Physical_Stock']; 
$nestedData[]=$row['Fld_Owner_ID']; 
$nestedData[]=$row['Fld_Stock_Location_ID']; 
$nestedData[]=$row['Fld_Status_ID']; 
$nestedData[]=htmlspecialchars($row['Fld_Status_Ind']); 
$nestedData[]=htmlspecialchars($row['Fld_Status_Date']); 
$nestedData[]=htmlspecialchars($row['Fld_Stock_Remark']); 
$nestedData[]=htmlspecialchars($row['Fld_Shelf_Life_Limit']); 
$nestedData[]=$row['Fld_Valeur_Comptable']; 
$nestedData[]=$row['Fld_Valeur_Comptable_currency_Id']; 
$nestedData[]=htmlspecialchars($row['Fld_Sales_Remark']);
$nestedData[]=$row['Fld_External_Location'];
$nestedData[]=$row['Fld_Sales_Remark_ID'];
$nestedData[]=$row['Fld_Warehouse_Location_ID'];
$nestedData[]=$row['Fld_OriginalUnit_Stock_ID'];
$nestedData[]=$row['Fld_Min_Qty'];
$nestedData[]=$row['Fld_Publish'];


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


