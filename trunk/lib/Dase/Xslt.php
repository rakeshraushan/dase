<?php

class Dase_Xslt {  

	private $xsl;
	private $xml;
	private $xslt;

	function __construct($stylesheet, $source = '') {
		if (!$source) {
			$source = XSLT_PATH . 'site/layout.xml';
		}
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

	/* addSourceNode is one way of getting dynamic content into 
	 * the web page. Simply create a SimpleXml doc and pass it 
	 * as a parameter.
	 */

	function addSourceNode($simple_xml) {
		$domnode = dom_import_simplexml($simple_xml);
		//see http://wiki.flux-cms.org/display/BLOG/GetElementById+Pitfalls
		$anchor = $this->xml->getElementById('dynamic');
		$anchor->appendChild($new = $this->xml->importNode($domnode,true));
	}

	/* another way to get content into the web page is by setting
	 * a variable that holds the URL of an XML-formatted web service
	 * which the stylesheet can consume. Of course you can also 
	 * pass a simple string to be included in the web page using
	 * this function as well.
	 */

	function set($name,$value) {
		$this->xslt->setParameter( null,$name,$value );
	}

	function transform() {
		$this->set('msg',Dase::filterGet('msg'));
		$this->set('app_root',APP_ROOT . '/');
		//$this->set('timer',Dase_Timer::getElapsed());
		$this->xslt->registerPHPFunctions();
		return $this->xslt->transformToXML($this->xml);
	}
}
