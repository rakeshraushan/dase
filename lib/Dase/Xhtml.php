<?php
class Dase_Xhtml
{
	public $dom;
	public $root;

	public static $ns = array(
		'h' => 'http://www.w3.org/1999/xhtml',
	);

	function addElement($tagname,$text='',$ns='') 
	{
		if (!$ns) {
			$ns = Dase_Xhtml::$ns['h'];
		}
		$elem = $this->root->appendChild($this->dom->createElementNS($ns,$tagname));
		if ($text || '0' === (string) $text) { //so '0' works
			$elem->appendChild($this->dom->createTextNode($text));
		}
		return $elem;
	}

	function addChildElement($parent,$tagname,$text='',$ns='') 
	{
		if (!$ns) {
			$ns = Dase_Xhtml::$ns['h'];
		}
		$elem = $parent->appendChild($this->dom->createElementNS($ns,$tagname));
		if ($text) {
			$elem->appendChild($this->dom->createTextNode($text));
		}
		return $elem;
	}

	function asXml($node=null) 
	{
		//format output
		$this->dom->formatOutput = true;
		if ($node) {
			return $this->dom->saveXML($node);
		} else {
			return $this->dom->saveXML();
		}
	}
}
