<?php
include_once 'objects/XmlConverter.php';
$path = 'statics/XML.zip';
$xmlconverter = new XmlConverter();
$res = $xmlconverter->convertXMLtoJsonPersistToDatabase($path);
if($res == true){
    echo "Data persisted successfully.";
}
?>