<?php
	$ZIP = $_GET['zipcode'];
  $client = new SoapClient("temperature.wsdl");
  $return = $client->getTemp($ZIP);
  echo("Temperature is: " . $return);
?> 
