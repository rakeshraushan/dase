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

	function getSerialNumber() 
	{
		if (!$this->serial_number) {
			//serial numbers are modelled the same way as 
			//ascii ids (last segment of id)
			$this->serial_number = $this->getAsciiId();
		}
		return $this->serial_number;
	}

	function getItemLink()
	{
		//shouldn't this just be rel="alternate" link?
		return $this->getLink('http://daseproject.org/relation/search-item');
	}

	function getMediaCollectionLink()
	{
		return $this->getLink('http://daseproject.org/relation/media-collection');
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

	function getViewitemLink()
	{
		$x = new DomXPath($this->dom);
		$x->registerNamespace('media',Dase_Atom::$ns['media']);
		$x->registerNamespace('atom',Dase_Atom::$ns['atom']);
		$elem =  $x->query("media:group/media:content/media:category[. = 'viewitem']",$this->root)->item(0)->parentNode;
		if ($elem) {
			return $elem->getAttribute('url');
		}
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
			$bytes = $el->getAttribute('fileSize');
			$kilobytes = round((int) $bytes/1000,2);
			$file['fileSize'] = $kilobytes;
			$file['label'] = $el->getElementsByTagName('category')->item(0)->nodeValue;
			$media_array[] = $file;
		}
		return $media_array;
	}

	function selectMedia($size) 
	{
		//todo: fix this!!
		$x = new DomXPath($this->dom);
		$x->registerNamespace('media',Dase_Atom::$ns['media']);
		$x->registerNamespace('atom',Dase_Atom::$ns['atom']);
		//return $x->query("media:group/media:content/media:category[. = '$size']")
		//	->item(0)->parentNode->getAttribute('url');
		$nodes = $x->query("media:group/media:content/media:category[. = '$size']");
		foreach ($nodes as $node) {
			return $node->parentNode->getAttribute('url');
		}
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

	function getUnique()
	{
		return $this->getCollectionAsciiId().'/'.$this->getSerialNumber();
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

	function insert($request) 
	{
		$eid = $request->getUser()->eid;
		$c = Dase_DBO_Collection::get($request->get('collection_ascii_id'));
		if (!$c) { return; }
		if ($request->has('serial_number')) {
			$item = Dase_DBO_Item::create($c->ascii_id,$request->get('serial_number'),$eid);
		} else {
			$item = Dase_DBO_Item::create($c->ascii_id,null,$eid);
		}
		foreach ($this->metadata as $att => $keyval) {
			//creates atribute if it doesn't exist!
			Dase_DBO_Attribute::findOrCreate($c->ascii_id,$att);
			foreach ($keyval['values'] as $v) {
				if (trim($v)) {
					$item->setValue($att,$v);
				}
			}
		}
		$content = new Dase_DBO_Content;
		$atom_content = $this->getContent();
		if ($atom_content) {
			$content->text = $atom_content;
			$content->type = $this->getContentType();
			$content->item_id = $item->id;
			$content->p_collection_ascii_id = $c->ascii_id;
			$content->p_serial_number = $item->serial_number;
			$content->updated = date(DATE_ATOM);
			$content->updated_by_eid = $eid;
			$content->insert();
		}
		$item->setValue('title',$this->getTitle());
		$item->setValue('description',$this->getSummary());

		//how do we authenticate to get the enclosure??
		/*
		$enc = $this->getEnclosure(); 
		if ($enc) {
			$upload_dir = Dase_Config::get('path_to_media').'/'.$c->ascii_id.'/uploaded_files';
			if (!file_exists($upload_dir)) {
				$request->renderError(401,'missing upload directory');
			}
			$ext = Dase_File::$types_map[$enc['mime_type']]['ext'];
			$new_file = $upload_dir.'/'.$item->serial_number.'.'.$ext;
			file_put_contents($new_file,file_get_contents($enc['href']));

			try {
				$file = Dase_File::newFile($new_file,$enc['mime_type']);
				$media_file = $file->addToCollection($item,false);
			} catch(Exception $e) {
				Dase_Log::debug('error',$e->getMessage());
				$request->renderError(500,'could not ingest enclosure file ('.$e->getMessage().')');
			}
		}
		 */
		$item->buildSearchIndex();
		return $item;
	}

	function update($request) 
	{
		$eid = $request->getUser()->eid;
		$sernum = $this->getSerialNumber();
		$c = Dase_DBO_Collection::get($request->get('collection_ascii_id'));
		if (!$c) { return; }
		$item = Dase_DBO_Item::get($c->ascii_id,$sernum);
		$item->updated = date(DATE_ATOM);
		$item->update();
		$metadata = $this->getMetadata();
		$item->deleteValues();
		foreach (array_keys($metadata) as $ascii_id) {
			foreach ($metadata[$ascii_id]['values'] as $val) {
				$item->setValue($ascii_id,$val);
			}
		}
		$item->buildSearchIndex();
		return $item;
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
