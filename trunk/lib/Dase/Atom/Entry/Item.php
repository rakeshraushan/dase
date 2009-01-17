<?php
class Dase_Atom_Entry_Item extends Dase_Atom_Entry
{
	protected $_collection;
	protected $_collectionAsciiId;

	function __construct($dom = null,$root = null)
	{
		parent::__construct($dom,$root,'item');
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

	function getAttributesLink()
	{
		return $this->getLink('http://daseproject.org/relation/attributes');
	}

	function getMetadataLink()
	{
		return $this->getLink('http://daseproject.org/relation/metadata');
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
		$elem =  $x->query("media:content/media:category[. = 'viewitem']",$this->root)->item(0)->parentNode;
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

	function getThumbnailBase64()
	{
		$elem = $this->root->getElementsByTagNameNS(Dase_Atom::$ns['media'],'thumbnail')->item(0);
		if ($elem) {
			return base64_encode(file_get_contents($elem->getAttribute('url')));
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

	public function getLabel($att) 
	{
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['d'],'*') as $dd) {
			if ($att == $dd->localName) {
				return $dd->getAttributeNS(Dase_Atom::$ns['d'],'label');
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
		$x = new DomXPath($this->dom);
		$x->registerNamespace('media',Dase_Atom::$ns['media']);
		$x->registerNamespace('atom',Dase_Atom::$ns['atom']);
		$elem =  $x->query("media:content/media:category[. = '$size']",$this->root)->item(0)->parentNode;
		if ($elem) {
			return $elem->getAttribute('url');
		}
	}

	function getStatus()
	{
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
			if ('http://daseproject.org/category/status' == $el->getAttribute('scheme')) {
				return $el->getAttribute('term');
			}
		}
	}

	function getItemType()
	{
		$item_type['label'] = '';
		$item_type['term'] = '';
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
			if ('http://daseproject.org/category/item_type' == $el->getAttribute('scheme')) {
				//note we get label here!
				$item_type['label'] =  $el->getAttribute('label');
				$item_type['term'] =  $el->getAttribute('term');
				break;
			}
		}
		return $item_type;
	}

	function getParentItemTypeLinks()
	{
		$parent_types = array();
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'link') as $el) {
			if ('http://daseproject.org/relation/parent_item_type' == $el->getAttribute('rel')) {
				$parent_types[$el->getAttribute('href')] = $el->getattribute('title');
			}
		}
		return $parent_types;
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

	function getPosition()
	{
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
			if ('http://daseproject.org/category/position' == $el->getAttribute('scheme')) {
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

	function replaceMetadata($metadata_array) {
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
			if ('http://daseproject.org/category/metadata' == $el->getAttribute('scheme')) {
				$doomed[] = $el;
			}
			if ('http://daseproject.org/category/private_metadata' == $el->getAttribute('scheme')) {
				$doomed[] = $el;
			}
		}
		foreach ($doomed as $goner) {
			$this->root->removeChild($goner);
		}
		foreach ($metadata_array as $k => $v) {
			$this->addCategory($k,'http://daseproject.org/category/metadata','',$v);
		}
	}

	function addMetadata($att_ascii_id,$value_text)
	{
		$this->addCategory($att_ascii_id,'http://daseproject.org/category/metadata','',$value_text);
	}

	function setItemType($type_ascii_id,$type_name='')
	{
		$this->addCategory($type_ascii_id,'http://daseproject.org/category/item_type',$type_name);
	}

	function getMetadata($include_private_metadata=false) 
	{
		$metadata = array();
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
			if ('http://daseproject.org/category/metadata' == $el->getAttribute('scheme')) {
				$att_ascii_id = $el->getAttribute('term');
				$metadata[$att_ascii_id]['attribute_name'] = $el->getAttribute('label');
				$metadata[$att_ascii_id]['values'][] = $el->nodeValue;
			}
			if ($include_private_metadata &&
				'http://daseproject.org/category/private_metadata' == $el->getAttribute('scheme')) {
					$att_ascii_id = $el->getAttribute('term');
					$metadata[$att_ascii_id]['attribute_name'] = $el->getAttribute('label');
					$metadata[$att_ascii_id]['values'][] = $el->nodeValue;
				}
		}
		return $metadata;
	}

	function getAdminMetadata() 
	{
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
			if ('http://daseproject.org/category/admin_metadata' == $el->getAttribute('scheme')) {
				$att_ascii_id = $el->getAttribute('term');
				$metadata[$att_ascii_id]['attribute_name'] = $el->getAttribute('label');
				$metadata[$att_ascii_id]['values'][] = $el->nodeValue;
			}
		}
		return $metadata;
	}

	function replace($r) 
	{
		$item = Dase_DBO_Item::get($r->get('collection_ascii_id'),$r->get('serial_number'));
		if ($item) {
			$item->deleteValues();
			$item->deleteAdminValues();
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

	function insert($r,$fetch_enclosure=false) 
	{
		$eid = $r->getUser()->eid;
		$sernum = $this->getSerialNumber();
		$c = Dase_DBO_Collection::get($r->get('collection_ascii_id'));
		if (!$c) { return; }
		if ($r->has('serial_number')) {
			$item = Dase_DBO_Item::create($c->ascii_id,$r->get('serial_number'),$eid);
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

		//item_type
		$item_type = $this->getItemType(); 
		if ($item_type['term']) {
			$item->setItemType($item_type['term']);
		}

		foreach ($this->getCategoriesByScheme('http://daseproject.org/category/parent') as $cat) {
			//make sure parent is a legitimate item
			$coll = $this->getCollectionAsciiId();
			$parent = Dase_DBO_Item::getByUrl($cat['term']);
			//make sure relationship is legit
			$itr = Dase_DBO_ItemTypeRelation::getByItemSerialNumbers(
				$coll,$parent->serial_number,$sernum
			);
			if ($parent && $itr) {
				$item_relation = new Dase_DBO_ItemRelation;
				$item_relation->collection_ascii_id = $coll;
				$item_relation->parent_serial_number = $parent->serial_number;
				$item_relation->child_serial_number = $sernum;
				$item_relation->created = date(DATE_ATOM);
				$item_relation->created_by_eid = $r->getUser()->eid;
				$item_relation->item_type_relation_id = $itr->id;
				$item_relation->insert();
			} else {
				return false;
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
		//$item->setValue('title',$this->getTitle());
		//$item->setValue('description',$this->getSummary());

		if ($fetch_enclosure) {
			$enc = $this->getEnclosure(); 
			if ($enc) {
				$upload_dir = Dase_Config::get('path_to_media').'/'.$c->ascii_id.'/uploaded_files';
				if (!file_exists($upload_dir)) {
					$r->renderError(401,'missing upload directory');
				}
				$ext = Dase_File::$types_map[$enc['mime_type']]['ext'];
				$new_file = $upload_dir.'/'.$item->serial_number.'.'.$ext;
				file_put_contents($new_file,file_get_contents($enc['href']));

				try {
					$file = Dase_File::newFile($new_file,$enc['mime_type']);
					$media_file = $file->addToCollection($item,false);
				} catch(Exception $e) {
					Dase_Log::debug('error',$e->getMessage());
					$r->renderError(500,'could not ingest enclosure file ('.$e->getMessage().')');
				}
			}
		} 
		$item->expireCaches();
		$item->buildSearchIndex();
		return $item;
	}

	/** used w/ PUT request -- affects categories:
	 *  1. deletes and replaces status 
	 *  2. deletes and replaces item_type (beware messing up semantics of existing relations)
	 *  3. deletes and replaces all metadata & private metadata (NOT admin metadata)
	 *  4. delete and replace any item relations to a parent item
	 */
	function update($r) 
	{
		$eid = $r->getUser()->eid;
		$sernum = $this->getSerialNumber();
		$c = Dase_DBO_Collection::get($r->get('collection_ascii_id'));
		if (!$c) { return; }
		$item = Dase_DBO_Item::get($c->ascii_id,$sernum);
		$item->updated = date(DATE_ATOM);

		//1. status
		$status = $this->getStatus();
		if (($status != $item->status) && in_array($status,array('delete','draft','public','archive'))) {
			$item->status = $status;
		}

		$item->update();

		//2. item_type
		//note that this *updates* db about item_type!
		$item_type = $this->getItemType(); 
		$orig_type = $item->getItemType();
		if (!$item_type['term'] || 'default' == $item_type['term']) {
			$item->setItemType();
		} elseif ($orig_type->ascii_id != $item_type['term']) {
			$item->setItemType($item_type['term']);
		} else {
			//nothin'
		}

		//3. metadata
		$metadata = $this->getMetadata(true);
		//only deletes collection (not admin) metadata
		//then replaces it
		$item->deleteValues();
		foreach (array_keys($metadata) as $ascii_id) {
			foreach ($metadata[$ascii_id]['values'] as $val) {
				$item->setValue($ascii_id,$val);
			}
		}

		//4. replace parent item relations

		/* sample parent item indicator
			<category term="http://quickdraw.laits.utexas.edu/dase1/item/test/000524615" 
			scheme="parent" 
			label="Proposal: Cool Art Website"/>
		 */

		Dase_DBO_ItemRelation::removeParents($c->ascii_id,$sernum); 

		foreach ($this->getCategoriesByScheme('http://daseproject.org/category/parent') as $cat) {
			//make sure parent is a legitimate item
			$coll = $this->getCollectionAsciiId();
			$parent = Dase_DBO_Item::getByUrl($cat['term']);
			//make sure relationship is legit
			$itr = Dase_DBO_ItemTypeRelation::getByItemSerialNumbers(
				$coll,$parent->serial_number,$sernum
			);
			if ($parent && $itr) {
				$item_relation = new Dase_DBO_ItemRelation;
				$item_relation->collection_ascii_id = $coll;
				$item_relation->parent_serial_number = $parent->serial_number;
				$item_relation->child_serial_number = $sernum;
				if (!$item_relation->findOne()) {
					$item_relation->item_type_relation_id = $itr->id;
					$item_relation->insert();
				}
			} else {
				return false;
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
