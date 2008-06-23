<?php
class Dase_Atom_Entry_Item extends Dase_Atom_Entry
{
	protected $_collection;
	protected $_collectionAsciiId;

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
		$elem = $this->root->getElementsByTagNameNS(Dase_Atom::$ns['media'],'thumbnail')->item(0);
		if ($elem) {
			return $elem->getAttribute('url');
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
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['media'],'content') as $el) {
			$file['href'] = $el->getAttribute('url');
			$file['type'] = $el->getAttribute('type');
			$file['width'] = $el->getAttribute('width');
			$file['height'] = $el->getAttribute('height');
			$file['label'] = $el->getElementsByTagName('category')->item(0)->nodeValue;
			$media_array[] = $file;
		}
		return $media_array;
	}

	function selectMedia($size) 
	{
		$x = new DomXPath($this->dom);
		$x->registerNamespace('media',Dase_Atom::$ns['media']);
		$x->registerNamespace('atom',Dase_Atom::$ns['atom']);
		return $x->query("media:group/media:content/media:category[. = '$size']")
			->item(0)->parentNode->getAttribute('url');
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
