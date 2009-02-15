<?php
class Dase_Atom_Entry_Item extends Dase_Atom_Entry
{
	protected $_collection;
	protected $_collectionAsciiId;
	protected $_metadata;

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
		} else {
			return 'www/images/laits_colors.jpg';
		}
	}

	function getChildfeedLinkUrlByTypeJson($item_type)
	{
		$type = $this->getItemType();
		$desc = $item_type.'/children_of/'.$type['term'];
		foreach ($this->getChildJsonFeedLinks() as $link) {
			if (strpos($link['href'],$desc)) {
				return $link['href'];
			}
		}
	}


	function getChildfeedLinkUrlByTypeAtom($item_type)
	{
		$type = $this->getItemType();
		$desc = $item_type.'/children_of/'.$type['term'];
		foreach ($this->getChildFeedLinks() as $link) {
			if (strpos($link['href'],$desc)) {
				return $link['href'];
			}
		}
	}

	function getThumbnailLink()
	{
		$elem = $this->root->getElementsByTagNameNS(Dase_Atom::$ns['media'],'thumbnail')->item(0);
		if ($elem) {
			return $elem->getAttribute('url');
		} else {
			return 'www/images/laits_colors.jpg';
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
		foreach ($this->getMetadata() as $k => $v) {
			if ($k == $att) {
				if ($return_first) {
					return $v['value'];
				} else {
					return $v['values']; //will be an array
				}
			}
		}
	}

	function getParentLinkNodesByItemType($item_type)
	{
		$links = array();
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'link') as $link) {
			if ($item_type == $link->getAttributeNS(Dase_Atom::$ns['d'],'item_type')) {
				$links[] = $link;
			}
		}
		return $links;
	}

	function getParentLinkTitleByItemType($item_type)
	{
		//only gets FIRST one (often OK)
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'link') as $link) {
			if ($item_type == $link->getAttributeNS(Dase_Atom::$ns['d'],'item_type')) {
				return $link->getAttribute('title');
			}
		}
		return false;
	}

	function getParentLinks() 
	{
		return $this->getLinksByRel('http://daseproject.org/relation/parent'); 
	}

	function getChildFeedLinks() 
	{
		$links = array();
		foreach ($this->getLinksByRel('http://daseproject.org/relation/childfeed') as $link) {
			if ('application/atom+xml' == $link['type']) {
				$links[] = $link;
			}
		}
		return $links;
	}

	function getChildJsonFeedLinks() 
	{
		$links = array();
		foreach ($this->getLinksByRel('http://daseproject.org/relation/childfeed') as $link) {
			if ('application/json' == $link['type']) {
				$links[] = $link;
			}
		}
		return $links;
	}

	public function getLabel($att) 
	{
		//look in three places
		foreach ($this->getCategoriesByScheme('http://daseproject.org/category/metadata') as $cat) {
			if ($att == $cat['term']) {
				return $cat['label'];
			}
		}
		foreach ($this->getCategoriesByScheme('http://daseproject.org/category/private_metadata') as $cat) {
			if ($att == $cat['term']) {
				return $cat['label'];
			}
		}
		foreach ($this->getCategoriesByScheme('http://daseproject.org/category/admin_metadata') as $cat) {
			if ($att == $cat['term']) {
				return $cat['label'];
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

	function setStatus($status='public')
	{
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
			if ('http://daseproject.org/category/status' == $el->getAttribute('scheme')) {
				$el->setAttribute('term',$status);
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
		//metadata array is same strucutre as getRawMetadata 
		// $m[att_ascii_id] = array of values
		$doomed = array();
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
			foreach ($v as $value) {
				$this->addCategory($k,'http://daseproject.org/category/metadata','',$value);
			}
		}
	}

	function addMetadata($att_ascii_id,$value_text)
	{
		if ($value_text && $att_ascii_id) {
			$this->addCategory($att_ascii_id,'http://daseproject.org/category/metadata','',$value_text);
		}
	}

	function setItemType($type_ascii_id,$type_name='')
	{
		$this->addCategory($type_ascii_id,'http://daseproject.org/category/item_type',$type_name);
	}

	function getMetadata($att_ascii_id = '',$include_private_metadata=false) 
	{
		if (count($this->_metadata)) {
			if ($att_ascii_id) {
				if (isset($this->_metadata[$att_ascii_id])) {
					return $this->_metadata[$att_ascii_id];
				} else {
					return false;
				}
			}
			return $this->_metadata;
		}
		$metadata = array();
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
			if ('http://daseproject.org/category/metadata' == $el->getAttribute('scheme')) {
				$v = array();
				$att_ascii_id = $el->getAttribute('term');
				$metadata[$att_ascii_id]['attribute_name'] = $el->getAttribute('label');
				$v['edit'] = $el->getAttributeNS(Dase_Atom::$ns['d'],'edit-id');
				$v['id'] = array_pop(explode('/',$v['edit'])); //provides the last segment, i.e. value id
				$v['text'] = $el->nodeValue;
				$metadata[$att_ascii_id]['values'][] = $v;
				//easy access to first value
				if (1 == count($metadata[$att_ascii_id]['values'])) {
					$metadata[$att_ascii_id]['text'] = $v['text'];
					$metadata[$att_ascii_id]['edit'] = $v['edit'];
					$metadata[$att_ascii_id]['id'] = $v['id'];
				}
			}
			if ($include_private_metadata &&
				'http://daseproject.org/category/private_metadata' == $el->getAttribute('scheme')) {
					$att_ascii_id = $el->getAttribute('term');
					$metadata[$att_ascii_id]['attribute_name'] = $el->getAttribute('label');
					$v['edit'] = $el->getAttributeNS(Dase_Atom::$ns['d'],'edit-id');
					$v['id'] = array_pop(explode('/',$v['edit'])); //provides the last segment, i.e. value id
					$v['text'] = $el->nodeValue;
					$metadata[$att_ascii_id]['values'][] = $v;
					//easy access to first value
					if (1 == count($metadata[$att_ascii_id]['values'])) {
						$metadata[$att_ascii_id]['text'] = $v['text'];
						$metadata[$att_ascii_id]['edit'] = $v['edit'];
						$metadata[$att_ascii_id]['id'] = $v['id'];
					}
				}
		}
		$this->_metadata = $metadata;
		if ($att_ascii_id) {
			if (isset($metadata[$att_ascii_id])) {
				return $metadata[$att_ascii_id];
			} else {
				return false;
			}
		}
		return $metadata;
	}

	function getValue($att_ascii_id)
	{
		$v = $this->getMetadata($att_ascii_id,true);
		if (isset($v['text'])) {
			return $v['text'];
		} else {
			return false;
		}
	}

	function getRawMetadata($att = '') 
	{
		$metadata = array();
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
			if ('http://daseproject.org/category/metadata' == $el->getAttribute('scheme')) {
				$att_ascii_id = $el->getAttribute('term');
				$metadata[$att_ascii_id][] = $el->nodeValue;
			}
			if ('http://daseproject.org/category/private_metadata' == $el->getAttribute('scheme')) {
					$att_ascii_id = $el->getAttribute('term');
					$metadata[$att_ascii_id][] = $el->nodeValue;
				}
		}
		if ($att) {
			if (isset($metadata[$att]) && count($metadata[$att])) {
				return $metadata[$att][0];
			} else {
				return false;
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
				$metadata[$att_ascii_id]['values'][] = array('text' => $el->nodeValue);
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
		$eid = $r->getUser('http')->eid;
		$author = $this->getAuthorName();
		//allows created_by_eid to be author, NOT necessarily user
		if (!$author) {
			$author = $eid;
		}
		$c = Dase_DBO_Collection::get($r->get('collection_ascii_id'));
		if (!$c) { return; }
		$sn = Dase_Util::makeSerialNumber($r->get('slug'));
		$item = $c->createNewItem($sn,$author);
		foreach ($this->getMetadata() as $att => $keyval) {
			//creates atribute if it doesn't exist!
			Dase_DBO_Attribute::findOrCreate($c->ascii_id,$att);
			foreach ($keyval['values'] as $v) {
				if (trim($v['text'])) {
					$item->setValue($att,$v['text']);
				}
			}
		}

		//item_type
		$item_type = $this->getItemType(); 
		if ($item_type['term']) {
			$item->setItemType($item_type['term']);
		}

		$sernum = $item->serial_number;
		$coll = $c->ascii_id;
		foreach ($this->getParentLinks() as $ln) {
			//make sure parent is a legitimate item
			$parent = Dase_DBO_Item::getByUrl($ln['href']);
			//make sure relationship is legit
			if ($parent) {
				$itr = Dase_DBO_ItemTypeRelation::getByItemSerialNumbers(
					$coll,$parent->serial_number,$sernum
				);
			}
			if ($itr) {
				$item_relation = new Dase_DBO_ItemRelation;
				$item_relation->collection_ascii_id = $coll;
				$item_relation->parent_serial_number = $parent->serial_number;
				$item_relation->child_serial_number = $sernum;
				$item_relation->item_type_relation_id = $itr->id;
				$item_relation->insert();
				$item_relation->saveParentAtom();
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
			$content->updated_by_eid = $author;
			$content->insert();
		}
		//$item->setValue('title',$this->getTitle());
		//$item->setValue('description',$this->getSummary());

		if ($fetch_enclosure) {
			$enc = $this->getEnclosure(); 
			if ($enc) {
				$upload_dir = $r->config->get('path_to_media').'/'.$c->ascii_id.'/uploaded_files';
				if (!file_exists($upload_dir)) {
					$r->renderError(401,'missing upload directory');
				}
				$ext = Dase_File::$types_map[$enc['type']]['ext'];
				$new_file = $upload_dir.'/'.$item->serial_number.'.'.$ext;
				file_put_contents($new_file,file_get_contents($enc['href']));

				try {
					$file = Dase_File::newFile($new_file,$enc['mime_type']);
					$media_file = $file->addToCollection($item,false);
				} catch(Exception $e) {
					Dase_Log::get()->debug('error',$e->getMessage());
					$r->renderError(500,'could not ingest enclosure file ('.$e->getMessage().')');
				}
			}
		} 
		//messy
		$item->expireCaches(
			$r->config->get('cache'),$r->config->get('base_dir').'/'.$r->config->get('cache_dir')
		);
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
		//only deletes collection (not admin) metadata
		//then replaces it
		$item->deleteValues();
		foreach ($this->getMetadata(null,true) as $att => $keyval) {
			foreach ($keyval['values'] as $v) {
				if (trim($v['text'])) {
					$item->setValue($att,$v['text']);
				}
			}
		}

		//3.5 content!
		if ($this->getContent()) {
			$item->setContent($this->getContent(),$eid,$this->getContentType());
		}

		//4. replace parent item relations

		/* sample parent item indicator
			<category term="http://quickdraw.laits.utexas.edu/dase1/item/test/000524615" 
			scheme="parent" 
			label="Proposal: Cool Art Website"/>
		 */

		Dase_DBO_ItemRelation::removeParents($c->ascii_id,$sernum); 

		$coll = $this->getCollectionAsciiId();
		foreach ($this->getParentLinks() as $ln) {
			//make sure parent is a legitimate item
			$parent = Dase_DBO_Item::getByUrl($ln['href']);
			//make sure relationship is legit
			if ($parent) {
				$itr = Dase_DBO_ItemTypeRelation::getByItemSerialNumbers(
					$coll,$parent->serial_number,$sernum
				);
			}
			if ($itr) {
				$item_relation = new Dase_DBO_ItemRelation;
				$item_relation->collection_ascii_id = $coll;
				$item_relation->parent_serial_number = $parent->serial_number;
				$item_relation->child_serial_number = $sernum;
				if (!$item_relation->findOne()) {
					$item_relation->item_type_relation_id = $itr->id;
					$item_relation->insert();
					//too expensive??  maybe simply expire atom cache??
					$item_relation->saveParentAtom();
				}
			} else {
				//nothin'	
			}
		}
		$item->buildSearchIndex();
		$item->saveAtom();
		return $item;
	}

	function __get($var) 
	{
		//allows smarty to invoke function as if getter
		$classname = get_class($this);
		$method = 'get'.ucfirst($var);
		if (method_exists($classname,$method)) {
			return $this->{$method}();
		} elseif ($this->getMetadata($var)) {
			return $this->getMetadata($var);
		} elseif ($this->getParentLinkTitleByItemType($var)) {
			return $this->getParentLinkTitleByItemType($var);
		} else {
			return parent::__get($var);
		}
	}
}
