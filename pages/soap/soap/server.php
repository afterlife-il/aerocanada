<?php 

function getTemp($zip) { 
  $temp = rand(40,80);
  return $temp; 
} 

ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache 
$server = new SoapServer("temperature.wsdl"); 
$server->addFunction("getTemp"); 
$server->handle(); 
?> 
