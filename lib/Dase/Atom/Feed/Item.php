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
		/*
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
		 */
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

	function getStatus()
	{
		if (!$this->_status) {
			foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
				if ('http://daseproject.org/category/item/status' == $el->getAttribute('scheme')) {
					$this->_status =  $el->getAttribute('term');
					break;
				}
			}
		}
		return $this->_status;
	}

	function getStatusLabel()
	{
		$labels['public'] = "Public";
		$labels['draft'] = "Draft (Admin View Only)";
		$labels['delete'] = "Marked for Deletion";
		$labels['archive'] = "In Deep Storage";

		if (!$this->_status) {
			foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
				if ('http://daseproject.org/category/item/status' == $el->getAttribute('scheme')) {
					$this->_status =  $el->getAttribute('term');
					break;
				}
			}
		}
		return $labels[$this->_status];
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

	/** combination of coll_ascii_id & serial_number */
	function getUnique()
	{
		return $this->getCollectionAsciiId().'/'.$this->getSerialNumber();
	}

	function selectMedia($size) 
	{
		$x = new DomXPath($this->dom);
		$x->registerNamespace('media',Dase_Atom::$ns['media']);
		$x->registerNamespace('atom',Dase_Atom::$ns['atom']);
		return $x->query("atom:entry/media:group/media:content/media:category[. = '$size']")
			->item(0)->parentNode->getAttribute('url');
	}


	function getViewitemLink()
	{
		$x = new DomXPath($this->dom);
		$x->registerNamespace('media',Dase_Atom::$ns['media']);
		$x->registerNamespace('atom',Dase_Atom::$ns['atom']);
		$elem =  $x->query("atom:entry/media:group/media:content/media:category[. = 'viewitem']")->item(0)->parentNode;
		if ($elem) {
			return $elem->getAttribute('url');
		}
	}

	function getThumbnailLink()
	{
		return $this->root->getElementsByTagNameNS(Dase_Atom::$ns['media'],'thumbnail')->item(0)->getAttribute('url');
	}

	function getItemId() 
	{
		if (!$this->item_id) {
			foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
				if ('http://daseproject.org/category/item/id' == $el->getAttribute('scheme')) {
					$this->item_id =  $el->getAttribute('term');
					break;
				}
			}
		}
		return $this->item_id;
	}

	function getSerialNumber() 
	{
		if (!$this->serial_number) {
			foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
				if ('http://daseproject.org/category/item/serial_number' == $el->getAttribute('scheme')) {
					$this->serial_number =  $el->getAttribute('term');
					break;
				}
			}
		}
		return $this->serial_number;
	}

	function getEntry()
	{
		if (!$this->entry_dom) {
			$this->entry_dom = $this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'entry')->item(0);
		}
		return new Dase_Atom_Entry_Item($this->dom,$this->entry_dom);
	}

	/** note this is different than the same method in feed:search */
	function getSearchEcho()
	{
		return $this->getAtomElementText('subtitle');
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
