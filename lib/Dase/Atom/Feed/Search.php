<?php
class Dase_Atom_Feed_Search extends Dase_Atom_Feed 
{
	function __construct($xml = null)
	{
		parent::__construct($xml);
	}

	function __get($var) 
	{
		//allows smarty to invoke function as if getter
		$classname = get_class($this);
		$method = 'get'.ucfirst($var);
		if (method_exists($classname,$method)) {
			return $this->{$method}();
		} else {
			return parent::__get($var);
		}
	}

	function getPrevious()
	{
		return $this->getLink('previous');
	}

	function getNext()
	{
		return $this->getLink('next');
	}

	function getSelf()
	{
		return $this->getLink('self');
	}

	function getSearchEcho()
	{
		return $this->getXpathValue("atom:subtitle/h:div/h:div[@class='searchEcho']");
	}

	function getSearchTallies()
	{
		$x = new DomXPath($this->dom);
		foreach (Dase_Atom::$ns as $k => $v) {
			$x->registerNamespace($k,$v);
		}
		$dom = new DOMDocument('1.0','utf-8');
		//need to import AND append!
		$node = $dom->importNode($x->query("atom:subtitle/h:div/h:ul")->item(0),true);
		$dom->appendChild($node);
		return $dom->saveHTML();
	}

	function getStartIndex()
	{
		return $this->getAtomElementText('startIndex','os');
	}
}
