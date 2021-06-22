<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
include_once 'objects/XmlConverter.php';

final class XmlConverterTest extends TestCase
{

	
	public function testconvertXMLtoJsonPersistToDatabase(){
		
		$xmlconverter = new XmlConverter();
		$result = $xmlconverter->convertXMLtoJsonPersistToDatabase('/home/jinal/Documents/XML.zip');
		$this->assertTrue($result == true);
	}

}
