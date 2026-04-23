
<?php

include_once "conf.php";
include_once "page_titles.php";

//CREATE QUERY TO DB AND PUT RECEIVED DATA INTO ASSOCIATIVE ARRAY
if (isset($_REQUEST['query'])) {
    $query = $_REQUEST['query'];
    $sql = mysql2_query ("SELECT zip, city FROM zips WHERE city LIKE '%{$query}%' OR zip LIKE '%{$query}%'");
	$array = array();
    while ($row = mysqli_fetch_array($sql)) {
        $array[] = array (
            'label' => $row['city'].', '.$row['zip'],
            'value' => $row['city'],
        );
    }
    //RETURN JSON ARRAY
    echo json_encode ($array);
}

?>