<?php


// require 'SOAP/Client.php';

 $tel = str_replace("+","00",$_GET["ligne_a_appeler"]);
 $tel = preg_replace('/[^0-9]/', '', $tel); // supression sauf chiffres 


$wsdl_url = 'https://www.ovh.com/soapi/soapi-re-1.17.wsdl';
//$wsdl_url = 'http://aerocanada-industries.com/adminaero/pages/soapi-re-1.17.wsdl';
$WSDL = new SOAP_WSDL($wsdl_url);
$soap = $WSDL->getProxy();
echo $tel;
//telephonyClick2CallDo
$result = $soap->telephonyClick2CallDo("yop061", "motdepasse",$_GET["tel_source"],$tel, $_GET["tel_source"]);

if(PEAR::isError($result)) {
// echo  $_GET["ligne_a_appeler"];
 echo $tel;
 echo "Error : ".$result->getCode()." ".$result->getMessage();
 
} else {
 echo "telephonyClick2CallDo successfull";
 echo $tel;
}

?>
