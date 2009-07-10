<?php
class Dase_Atom_Feed_Search extends Dase_Atom_Feed 
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

	function getSearchLink()
	{
		return $this->getLink('alternate');
	}

	function getSearchEcho()
	{
		return $this->getXpathValue("atom:subtitle/h:div/h:div[@class='searchEcho']");
	}

	function getListLink()
	{
		return $this->getLink('related','list');
	}

	function getGridLink()
	{
		return $this->getLink('related','grid');
	}

	/** for single collection searches only */
	function getCollection()
	{
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'link') as $el) {
			if ('http://daseproject.org/relation/collection' == $el->getAttribute('rel')) {
				$res['href'] = $el->getAttribute('href');
				$res['title'] = $el->getAttribute('title');
				$res['ascii_id'] = array_pop(explode('/',$res['href']));
				return $res;
			}
		}
	}

	function getCollectionFilters()
	{
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
			if ('http://daseproject.org/category/collection_filter' == $el->getAttribute('scheme')) {
				$colls[] = $el->getAttribute('term');
			}
		}
		return $colls;
	}

	/** for single collection searches only */
	function getAttributesLink()
	{
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'link') as $el) {
			if ('http://daseproject.org/relation/collection/attributes' == $el->getAttribute('rel')) {
				return $el->getAttribute('href');
			}
		}
	}

	function getSearchTallies()
	{
		$tallied = array();
		$single_coll = array();
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'link') as $el) {
			if ('http://daseproject.org/relation/single_collection_search' == $el->getAttribute('rel')) {
				$single_coll['href'] = $el->getAttribute('href');
				$single_coll['title'] = $el->getAttribute('title');
				$single_coll['count'] = $el->getAttributeNS(Dase_Atom::$ns['thr'],'count');
				$tallied[] = $single_coll;
			}
		}
		return $tallied;
	}

	function getCount()
	{
		return $this->getOpensearchTotal();
	}

	function getMax()
	{
		$x = new DomXPath($this->dom);
		foreach (Dase_Atom::$ns as $k => $v) {
			$x->registerNamespace($k,$v);
		}
		return $this->getXpathValue("opensearch:itemsPerPage");
	}

	function getStartIndex()
	{
		$x = new DomXPath($this->dom);
		foreach (Dase_Atom::$ns as $k => $v) {
			$x->registerNamespace($k,$v);
		}
		return $this->getXpathValue("opensearch:startIndex");
		return $this->getAtomElementText('startIndex','os');
	}

	/** convenience method to get first item */
	function getEntry()
	{
		if (!$this->entry_dom) {
			$this->entry_dom = $this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'entry')->item(0);
		}
		return new Dase_Atom_Entry_Item($this->dom,$this->entry_dom);
	}

}
