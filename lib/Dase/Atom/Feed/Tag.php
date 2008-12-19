<?php
class Dase_Atom_Feed_Tag extends Dase_Atom_Feed 
{
	function __construct($dom = null)
	{
		parent::__construct($dom);
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

	function getEid()
	{
		return $this->getXpathValue("atom:author/atom:name");
	}

	function getSelf()
	{
		return $this->getLink('self');
	}

	function getListLink()
	{
		return $this->getLink('alternate','list');
	}

	function getGridLink()
	{
		return $this->getLink('alternate','grid');
	}

	function getDataLink()
	{
		return $this->getLink('alternate','data');
	}

	/** beware: this just gets the first coll_ascii_id it comes to 
	 * */
	function getCollectionAsciiId()
	{
		if (!$this->_collectionAsciiId) {
			foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
				if ('http://daseproject.org/category/collection' == $el->getAttribute('scheme')) {
					$this->_collectionAsciiId = $el->getAttribute('term');
					break;
				}
			}
		}
		return $this->_collectionAsciiId;
	}

	function getTagType()
	{
		foreach ($this->dom->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
			if ('http://daseproject.org/category/tag/type' == $el->getAttribute('scheme')) {
				return $el->getAttribute('term');
			}
		}
	}

	function getBackground()
	{
		foreach ($this->dom->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
			if ('http://daseproject.org/category/tag/background' == $el->getAttribute('scheme')) {
				return $el->getAttribute('term');
			}
		}
	}

}
