<?php
/*
 * Copyright 2008 The University of Texas at Austin
 *
 * This file is part of DASe.
 * 
 * DASe is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * DASe is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with DASe.  If not, see <http://www.gnu.org/licenses/>.
 */ 


class Dase_Xslt 
{  

	//convenince wrapper for PHP
	//XSLT class

	private $xsl;
	private $xml;
	private $xslt;
	private $params = array();

	function __set($property,$value)
	{
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

	private function init()
	{
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

	function addSourceNode($simple_xml)
	{
		$this->init();
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

	function set($name,$value)
	{
		if ($this->xslt) {
			$this->xslt->setParameter( null,$name,$value );
		} else {
			$this->params[$name] = $value;
		}
	}

	function transform()
	{
		$this->init();
		if (!$this->xslt->getParameter(null,'msg')) {
			$this->set('msg',Dase::filterGet('msg'));
		}
		$page_hook = Dase_Registry::get('handler').'_'.Dase_Registry::get('action');
		$this->set('page_hook',$page_hook);
		$this->set('app_root',APP_ROOT . '/');
		//$this->set('timer',Dase_Timer::getElapsed());
		$this->xslt->registerPHPFunctions();
		return $this->xslt->transformToXML($this->xml);
	}
}
