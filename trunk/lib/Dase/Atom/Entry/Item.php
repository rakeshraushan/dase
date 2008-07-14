<?php
class Dase_Atom_Entry_Item extends Dase_Atom_Entry
{
	protected $_collection;
	protected $_collectionAsciiId;
	protected $_status;

	function __construct($dom = null,$root = null)
	{
		parent::__construct($dom,$root);
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

	function getItemLink()
	{
		//shouldn't this just be rel="alternate" link?
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

	function insert($request) 
	{
		$eid = $request->getUser()->eid;
		$c = Dase_DBO_Collection::get($request->get('collection_ascii_id'));
		$item = Dase_DBO_Item::create($c->ascii_id,null,$eid);
		foreach ($this->metadata as $att => $keyval) {
			foreach ($keyval['values'] as $v) {
				$item->setValue($att,$v);
			}
		}
		$item->buildSearchIndex();
		return $item;
	}

	function replace($request) 
	{
		$item = Dase_DBO_Item::get($request->get('collection_ascii_id'),$request->get('serial_number'));
		if ($item) {
		$item->deleteValues();
		foreach ($this->metadata as $att => $keyval) {
			foreach ($keyval['values'] as $v) {
				$item->setValue($att,$v);
			}
		}
		$item->buildSearchIndex();
		return $item;
		} else {
			Dase::error(404);
		}
	}

	function setEdited($dateTime)
	{
		if ($this->edited_is_set) {
			throw new Dase_Atom_Exception('edited is already set');
		} else {
			$this->edited_is_set = true;
		}
		$edited = $this->addElement('app:edited',$dateTime,Dase_Atom::$ns['app']);
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
