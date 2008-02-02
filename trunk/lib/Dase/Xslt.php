<?php

class Dase_Xslt {  

	private $xsl;
	private $xml;
	private $xslt;
	private $params = array();

	function __set($property,$value) {
		if ('stylesheet' == $property) {
			$this->xsl = new DOMDocument;
			return $this->xsl->load($value);
		}

		if ('source' == $property) {
			$this->xml = new DOMDocument;
			if (is_file($value)) {
				$this->xml->load($value);
			} else {
				$this->xml->loadXML($value);
			}
		}
	}

	private function init() {
		if (!$this->xsl) {
			throw new Exception('no stylesheet set!');
		}
		if (!$this->xml && file_exists(XSLT_PATH . 'layout.xml')) {
			$this->source = XSLT_PATH . 'layout.xml';
		}
		if (!$this->xslt) {
			$this->xslt = new XSLTProcessor();
			$this->xslt->importStylesheet($this->xsl);
			foreach($this->params as $k => $v) {
				$this->set($k,$v);
			}
		}
	}

	/* addSourceNode is one way of getting dynamic content into 
	 * the web page. Simply create a SimpleXml doc and pass it 
	 * as a parameter.
	 */

	function addSourceNode($simple_xml) {
		if ($this->xml) {
			$domnode = dom_import_simplexml($simple_xml);
			//see http://wiki.flux-cms.org/display/BLOG/GetElementById+Pitfalls
			$anchor = $this->xml->getElementById('dynamic');
			$anchor->appendChild($new = $this->xml->importNode($domnode,true));
		}
	}

	/* another way to get content into the web page is by setting
	 * a variable that holds the URL of an XML-formatted web service
	 * which the stylesheet can consume. Of course you can also 
	 * pass a simple string to be included in the web page using
	 * this function as well.
	 */

	function set($name,$value) {
		if ($this->xslt) {
			$this->xslt->setParameter( null,$name,$value );
		} else {
			$this->params[$name] = $value;
		}
	}

	function transform() {
		$this->init();
		$d = Dase::instance();
		$this->set('msg',Dase::filterGet('msg'));
		$page_hook = $d->handler.'_'.$d->action;
		$this->set('page_hook',$page_hook);
		$this->set('app_root',APP_ROOT . '/');
		//$this->set('timer',Dase_Timer::getElapsed());
		$this->xslt->registerPHPFunctions();
		return $this->xslt->transformToXML($this->xml);
	}
}
