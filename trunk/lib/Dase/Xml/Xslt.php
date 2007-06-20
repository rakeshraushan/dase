<?php

class Dase_Xml_Xslt {  

	function __construct() {
	}

	static function transform($stylesheet,$source) {
		//load stylesheet
		$xsl = new DOMDocument;
		$stylesheet = XSLT_DIR . "/$stylesheet";
		$xsl->load($stylesheet);

		//load source xml
		$xml = new DOMDocument;
		$xml_doc = XML_DIR . "/$source";
		if (is_file($xml_doc)) {
			$xml->load($xml_doc);
		} else {
			$xml->loadXML($source);
		}

		//run xslt transform
		$xslt = new XSLTProcessor();
		$xslt->importStylesheet($xsl);
		return ($xslt->transformToXML($xml));
	}
}
