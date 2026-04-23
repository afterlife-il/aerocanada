<?php
session_start();
/* Database connection start */
include_once "conf.php";
include_once "page_titles.php";
/* Database connection end */

//CREATE QUERY TO DB AND PUT RECEIVED DATA INTO ASSOCIATIVE ARRAY
/* Table tbl_Parts ::::   Fld_Part_ID  Fld_Part_Nbr  Fld_Part_Desc  Fld_Part_MFG  Fld_Part_MFG_Old  Fld_AC_ID  Fld_Old_LP  Fld_Part_List_Price  Fld_Part_Price_Currency_ID  Fld_Part_LP_Date  Fld_Remark*/
if (isset($_REQUEST['query'])) {
    $query = $_REQUEST['query'];
    $sql = mysql2_query ("SELECT Fld_Part_Nbr, Fld_Part_ID FROM tbl_Parts WHERE Fld_Part_Nbr LIKE '%{$query}%' OR Fld_Part_ID LIKE '%{$query}%' ");
	$array = array();
    while ($row = mysqli_fetch_array($sql)) {
		$_SESSION['varchoixcomp']=$row['Fld_Company_ID'];
        $array[] = array (
            'value' => $row['Fld_Part_Nbr'].', '.$row['Fld_Part_ID'],
			'label' => $row['Fld_Part_ID'],
           
        );
    }
    //RETURN JSON ARRAY
    echo json_encode ($array);
}

?>