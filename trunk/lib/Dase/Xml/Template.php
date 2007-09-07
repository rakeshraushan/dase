<?php
class Dase_Xml_Template 
{
	public $xml;

	public function setXml( $xml) {
		$this->xml = $xml;
	}

	public function display($mime = '') {
		if ($mime) {
			header("Content-Type: $mime; charset=utf-8");
		} else {
			header('Content-Type: text/xml; charset=utf-8');
		}
		if ($this->xml) {
			    echo $this->xml;
		}
		exit;
	}
}
