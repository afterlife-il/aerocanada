
<?php

include_once "conf.php";
include_once "page_titles.php";

//CREATE QUERY TO DB AND PUT RECEIVED DATA INTO ASSOCIATIVE ARRAY
if (isset($_REQUEST['query'])) {
    $query = $_REQUEST['query'];
    $sql = mysql2_query ("SELECT Fld_Company_ID, Fld_Company_Name FROM tb_company WHERE Fld_Company_Name LIKE '%{$query}%'");
    //SELECT distinct(Fld_Company_Name),Fld_Company_ID FROM tb_company
	$array = array();
    while ($row = mysqli_fetch_array($sql)) {
        $array[] = array (
            'label' => $row['Fld_Company_Name'],
            'value' => $row['Fld_Company_ID'],
        );
    }
    //RETURN JSON ARRAY
    echo json_encode ($array);
}

?>