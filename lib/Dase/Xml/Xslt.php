<?php

class Dase_Xml_Xslt {  

	private $xsl;
	private $xml;
	private $xslt;

	function __construct($stylesheet,$source) {
		$this->xsl = new DOMDocument;
		$this->xsl->load($stylesheet);

		$this->xml = new DOMDocument;
		if (is_file($source)) {
			$this->xml->load($source);
		} else {
			$this->xml->loadXML($source);
		}
		$this->xslt = new XSLTProcessor();
		$this->xslt->importStylesheet($this->xsl);
	}

	function set($name,$value) {
		$this->xslt->setParameter( null,$name,$value );
	}

	function transform() {
		$this->set('timer',Dase_Timer::getElapsed());
		return ($this->xslt->transformToXML($this->xml));
	}
}
