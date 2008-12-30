<?php

class Dase_Atom_Categories extends Dase_Atom 
{
	protected $_categories = array();
	public static $schemes = array(
		//work on this
		'http://daseproject.org/category/entrytype',
	);

	function __construct($dom = null)
	{
		if ($dom) {
			//reader object
			$this->root = $dom;
			$this->dom = $dom;
		}  else {
			//creator object
			$dom = new DOMDocument('1.0','utf-8');
			$this->root = $dom->appendChild($dom->createElementNS(Dase_Atom::$ns['app'],'app:categories'));
			$this->dom = $dom;
		}
	}

	function addCategory($term,$scheme='',$label='') 
	{
		$cat = $this->addElement('category','',Dase_Atom::$ns['atom']);
		$cat->setAttribute('term',$term);
		if ($scheme) {
			$cat->setAttribute('scheme',$scheme);
		} 
		if ($label) {
			$cat->setAttribute('label',$label);
		}
	}

	function setFixed($yes_or_no) 
	{
		$this->root->setAttribute('fixed',$yes_or_no);
	}

	function getFixed() 
	{
		$this->root->getAttribute('fixed');
	}

	function setScheme($scheme) 
	{
		$this->root->setAttribute('scheme',$scheme);
	}

	function getScheme() 
	{
		$this->root->getAttribute('scheme');
	}

	function getCategories() {
		$default_scheme = $this->getScheme();
		$categories = array();
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $cat) {
			$category['term'] = $cat->getAttributeNS(Dase_Atom::$ns['atom'],'term');
			$category['label'] = $cat->getAttributeNS(Dase_Atom::$ns['atom'],'label');
			$category['scheme'] = $cat->getAttributeNS(Dase_Atom::$ns['atom'],'scheme');
			if (!$category['scheme']) {
				$category['scheme'] = $default_scheme;
			}
			$categories[] = $category;
		}
		return $categories;
	}

	function __get($var) {
		//allows smarty to invoke function as if getter
		$classname = get_class($this);
		$method = 'get'.ucfirst($var);
		if (method_exists($classname,$method)) {
			return $this->{$method}();
		} else {
			return parent::__get($var);
		}
	}

	function asXml() 
	{
		//format output
		$this->dom->formatOutput = true;
		return $this->dom->saveXML();
	}

}
