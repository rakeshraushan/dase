<?php
class Dase_Atom_Feed_Collection extends Dase_Atom_Feed 
{
	function __construct($dom = null)
	{
		parent::__construct($dom);
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

	function getDescription()
	{
		return $this->getSubtitle();
	}

	function getName()
	{
		return $this->getTitle();
	}

	function getAscii_id()
	{
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
			if ('http://daseproject.org/category/collection' == $el->getAttribute('scheme')) {
				return $el->getAttribute('term');
			}
		}
	}

	function getItem_count()
	{
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
			if ('http://daseproject.org/category/collection/item_count' == $el->getAttribute('scheme')) {
				return $el->getAttribute('term');
			}
		}
	}
}
