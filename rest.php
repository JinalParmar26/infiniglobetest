<?php
// error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: X-Requested-With');
header('Content-Type: application/json');

$api = $_SERVER['REQUEST_METHOD'];

if ($api == 'POST') {

	include_once 'objects/XmlConverter.php';

	$path = $_POST['path'];

	$xmlconverter = new XmlConverter();
	$res = $xmlconverter->convertXMLtoJsonUploadViaAPI($path);
	if($res == true){
    	echo "Data persisted successfully.";
	}
}

?>