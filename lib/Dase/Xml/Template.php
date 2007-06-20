<?php
class Dase_Xml_Template 
{
	public $xml;

	public function setXml( $xml) {
		$this->xml = $xml;
	}

	public function display() {
		header('Content-Type: text/xml; charset=utf-8');
		if ($this->xml) {
			    echo $this->xml;
		}
		exit;
	}
}
