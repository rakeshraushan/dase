<?php
class Dase_Atom_Entry_Item extends Dase_Atom_Entry
{
	protected $collection;
	protected $collectionAsciiId;

	function __construct($dom = null,$root = null)
	{
		parent::__construct($dom,$root);
	}

	function getItemId() 
	{
		return $this->getAtomElementText('item_id','d');
	}

	function getSerialNumber() 
	{
		return $this->getAtomElementText('serial_number','d');
	}

	function getItemLink()
	{
		return $this->getLink('http://daseproject.org/relation/search-item');
	}

	function getEditLink()
	{
		return $this->getLink('edit');
	}

	//not yet used???
	function getDescription()
	{
		return $this->getAtomElementText('summary');
	}

	function getThumbnailLink()
	{
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'link') as $el) {
			if ('thumbnail' == $el->getAttribute('title')) {
				return $el->getAttribute('href');
			}
		}
	}

	public function select($att,$return_first = true) 
	{
		foreach ($this->metadata as $k => $v) {
			if ($k == $att) {
				if ($return_first) {
					return $v['values'][0];
				} else {
					return $v['values']; //will be an array
				}
			}
		}
	}

	function getMedia() {
		$media_array = array();
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

	function selectMedia($size) 
	{
		foreach ($this->media as $m) {
			if ($m['label'] == $size) {
				return $m['href'];
			}
		}
	}

	function getCollection()
	{
		if (!$this->collection) {
			foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
				if ('http://daseproject.org/category/collection' == $el->getAttribute('scheme')) {
					$this->collection =  $el->getAttribute('label');
					break;
				}
			}
		}
		return $this->collection;
	}

	function getNumberInSet()
	{
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
			if ('http://daseproject.org/category/number_in_set' == $el->getAttribute('scheme')) {
				return $el->getAttribute('term');
			}
		}
	}

	function getCollectionAsciiId()
	{
		if (!$this->collectionAscii_id) {
			foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
				if ('http://daseproject.org/category/collection' == $el->getAttribute('scheme')) {
					$this->collectionAsciiId = $el->getAttribute('term');
					break;
				}
			}
		}
		return $this->collectionAsciiId;
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
}
