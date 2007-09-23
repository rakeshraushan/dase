<?php
class Dase_Xml_Template 
{
	public $xml;
	private $content_type_header = 'Content-Type: text/xml; charset=utf-8';

	public function setXml( $xml) {
		$this->xml = $xml;
	}

	public function display() {
		header($this->content_type_header);
		if ($this->xml) {
			    echo $this->xml;
		}
		exit;
	}

	public function setContentType($mime = '') {
		if ($mime) {
			$this->content_type_header = "Content-Type: $mime; charset=utf-8";
		}
	}
}
