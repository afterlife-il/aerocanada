<?php  
  
$conn = new mysqli('localhost', 'root', '');  
mysqli_select_db($conn, 'EmployeeDB');  
  
$setSql = "SELECT `ur_Id`,`ur_username`,`ur_password` FROM `tbl_user`";  
$setRec = mysqli_query($conn, $setSql);  
  
$columnHeader = '';  
$columnHeader = "Sr NO" . "\t" . "User Name" . "\t" . "Password" . "\t";  
  
$setData = '';  
  
while ($rec = mysqli_fetch_row($setRec)) {  
    $rowData = '';  
    foreach ($rec as $value) {  
        $value = '"' . $value . '"' . "\t";  
        $rowData .= $value;  
    }  
    $setData .= trim($rowData) . "\n";  
}  
  
  
header("Content-type: application/octet-stream");  
header("Content-Disposition: attachment; filename=User_Detail_Reoprt.xls");  
header("Pragma: no-cache");  
header("Expires: 0");  
  
echo ucwords($columnHeader) . "\n" . $setData . "\n";  
  
?>  