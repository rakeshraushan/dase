<?php
class Dase_Atom_Feed_Item extends Dase_Atom_Feed 
{

	protected $_collection;
	protected $_collectionAsciiId;
	protected $entry_dom = null;

	function __construct($dom = null)
	{
		parent::__construct($dom);
	}

	function getFeedLink()
	{
		return $this->getLink('http://daseproject.org/relation/feed-link');
	}

	function getPrevious()
	{
		return $this->getLink('previous');
	}

	function getNext()
	{
		return $this->getLink('next');
	}

	function getTagType()
	{
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
			if ('http://daseproject.org/category/tag_type' == $el->getAttribute('scheme')) {
				return $el->getAttribute('term');
			}
		}
	}

	function getMedia() {
		foreach ($this->root->getElementsByTagName('link') as $el) {
			if (strpos($el->getAttribute('rel'),'relation/media')) {
				$file['href'] = $el->getAttribute('href');
				$file['type'] = $el->getAttribute('type');
				$file['width'] = $el->getAttributeNS(Dase_Atom::$ns['d'],'width');
				$file['height'] = $el->getAttributeNS(Dase_Atom::$ns['d'],'height');
				$file['label'] = $el->getAttribute('title');
				$media_array[] = $file;
			}
		}
		return $media_array;
	}

	function getMetadata() {
		$metadata = array();
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['d'],'*') as $dd) {
			if ('admin_' != substr($dd->localName,0,6)) {
				$metadata[$dd->localName]['attribute_name'] = $dd->getAttributeNS(Dase_Atom::$ns['d'],'label');
				$metadata[$dd->localName]['values'][] = $dd->nodeValue;
			}
		}
		return $metadata;
	}

	function getAdminMetadata() {
		$metadata = array();
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['d'],'*') as $dd) {
			if ('admin_' == substr($dd->localName,0,6)) {
				$metadata[$dd->localName]['attribute_name'] = $dd->getAttributeNS(Dase_Atom::$ns['d'],'label');
				$metadata[$dd->localName]['values'][] = $dd->nodeValue;
			}
		}
		return $metadata;
	}

	function getDescription()
	{
		return $this->getAtomElementText('summary');
	}

	function getCollection()
	{
		if (!$this->_collection) {
			foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
				if ('http://daseproject.org/category/collection' == $el->getAttribute('scheme')) {
					$this->_collection =  $el->getAttribute('label');
					break;
				}
			}
		}
		return $this->_collection;
	}

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

	function getViewitemLink()
	{
		foreach ($this->root->getElementsByTagName('link') as $el) {
			if ('viewitem' == $el->getAttribute('title')) {
				return $el->getAttribute('href');
			}
		}
	}

	function getThumbnailLink()
	{
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['h'],'img') as $el) {
			if ('thumbnail' == $el->getAttribute('title')) {
				return $el->getAttribute('href');
			}
		}
	}

	function getItemId() 
	{
		return $this->getAtomElementText('item_id','d');
	}

	function getSerialNumber() 
	{
		return $this->getAtomElementText('serial_number','d');
	}

	function getEntry()
	{
		if (!$this->entry_dom) {
			$this->entry_dom = $this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'entry')->item(0);
		}
		return new Dase_Atom_Entry_Item($this->dom,$this->entry_dom);
	}

	function getEditLink() {
		return $this->getEntry()->getEditLink();
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
}
