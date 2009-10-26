<?php

require_once 'Dase/DBO/Autogen/Item.php';

class Dase_DBO_Item extends Dase_DBO_Autogen_Item 
{

	private $_collection = null;
	private $_content = null;
	private $_item_type = null;
	private $_media = array();
	private $_metadata = array();

	public static function get($db,$collection_ascii_id,$serial_number)
	{
		if (!$collection_ascii_id || !$serial_number) {
			throw new Exception('missing information');
		}
		$item = new Dase_DBO_Item($db);
		$item->p_collection_ascii_id = $collection_ascii_id;
		$item->serial_number = $serial_number;
		if ($item->findOne()) {
			return $item;
		} else {
			return false;
		}
	}

	public function saveAtomFile($path_to_media)
	{
		$subdir = Dase_Util::getSubdir($this->serial_number);
		$path = $path_to_media.'/'.$this->p_collection_ascii_id.'/atom/'.$subdir;
		if (!file_exists($path)) {
			mkdir($path);
		}
		$filename = $this->serial_number.'.atom';
		$app_root = '{APP_ROOT}';
		$entry = new Dase_Atom_Entry_Item;
		$entry = $this->injectAtomEntryData($entry,$app_root);
		if (file_put_contents($path.'/'.$filename,$entry->asXml($entry->root))) {
			return $path.'/'.$filename;
		}	
	}

	public static function getByUrl($db,$url)
	{
		//ignores everything but last two sections
		$url = str_replace('.atom','',$url);
		$sections = explode('/',trim($url,'/'));
		$sernum = array_pop($sections);
		$coll = array_pop($sections);
		//will return false if no such item
		return Dase_DBO_Item::get($db,$coll,$sernum);
	}

	public static function getByUnique($db,$unique)
	{
		$sections = explode('/',$unique);
		$sernum = array_pop($sections);
		$coll = array_pop($sections);
		//will return false if no such item
		return Dase_DBO_Item::get($db,$coll,$sernum);
	}

	public function deleteSearchIndex()
	{
		$engine = Dase_SearchEngine::get($this->db,$this->config);
		Dase_Log::debug(LOG_FILE,"deleted index for " . $this->serial_number);
		return $engine->deleteItemIndex($this);
	}

	public function buildSearchIndex($commit=true)
	{
		$engine = Dase_SearchEngine::get($this->db,$this->config);
		Dase_Log::debug(LOG_FILE,"built indexes for " . $this->serial_number);
		return $engine->buildItemIndex($this,$commit);
	}

	public function store()
	{
		$ds = Dase_DocStore::get($this->db,$this->config);
		Dase_Log::debug(LOG_FILE,"saved as document: " . $this->serial_number);
		return $ds->storeItem($this);
	}

	public function retrieveAtomDoc($app_root,$as_feed=false)
	{
		$ds = Dase_DocStore::get($this->db,$this->config);
		return $ds->getItem($this->getUnique(),$app_root,$as_feed);
	}

	public function retrieveJsonDoc($app_root)
	{
		$ds = Dase_DocStore::get($this->db,$this->config);
		return $ds->getItemJson($this->getUnique(),$app_root);
	}

	private function _getMetadata()
	{
		if (count($this->_metadata)) {
			return $this->_metadata;
		}
		$db = $this->db;
		$prefix = $this->db->table_prefix;
		$metadata = array();
		$bound_params = array();
		$sql = "
			SELECT a.ascii_id, a.id as att_id, a.attribute_name,
			v.value_text,a.collection_id, v.id, 
			a.in_basic_search,a.is_on_list_display, a.is_public,v.url,v.modifier,a.modifier_type,a.html_input_type
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE v.item_id = ?
			AND v.attribute_id = a.id
			ORDER BY a.sort_order,v.value_text
			";
		$st = Dase_DBO::query($db,$sql,array($this->id));
		while ($row = $st->fetch()) {
			$row['edit-id'] =  '/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'/metadata/'.$row['id'];
			$metadata[] = $row;
		}
		$this->_metadata = $metadata;
		return $metadata;
	}

	public function getMetadata($include_admin=false,$att_ascii_id='')
	{
		$metadata = array();
		foreach ($this->_getMetadata() as $meta) {
			if ($att_ascii_id && ($meta['ascii_id'] != $att_ascii_id)) {
				break;
			}
			if (0 == $meta['collection_id']) {
				if ($include_admin) {
					$metadata[] = $meta;
				}
			} else {
				$meta['edit-id'] = 
					'/item/'.$this->p_collection_ascii_id.'/'.
					$this->serial_number.'/metadata/'.$meta['id'];
				$metadata[] = $meta;
			}
		}
		return $metadata;
	}

	public function getAdminMetadata($att_ascii_id = '')
	{
		$metadata = array();
		foreach ($this->_getMetadata() as $meta) {
			if (0 == $meta['collection_id']) {
				$metadata[] = $meta;
			}
		}
		return $metadata;
	}

	//used for edit metadata form
	public function getMetadataJson($app_root)
	{
		//clean up to use standard names
		$metadata = array();
		foreach ($this->_getMetadata() as $meta) {
			$set = array();
			$set['value_id'] = $meta['id'];
			$set['url'] = $app_root.$meta['edit-id'];
			$set['collection_id'] = $meta['collection_id'];
			$set['att_ascii_id'] = $meta['ascii_id'];
			$set['attribute_name'] = $meta['attribute_name'];
			$set['html_input_type'] = $meta['html_input_type'];
			$set['value_text'] = $meta['value_text'];
			$set['metadata_link_url'] = $meta['url'];
			$set['modifier'] = $meta['modifier'];
			$set['modifier_type'] = $meta['modifier_type'];
			if (in_array($meta['html_input_type'],
				array('radio','checkbox','select','text_with_menu'))
			) {
				$att = new Dase_DBO_Attribute($this->db);
				$att->load($meta['att_id']);
				$set['values'] = $att->getFormValues();
			}
			$metadata[] = $set;
		}
		return Dase_Json::get($metadata);
	}

	public function getValues()
	{
		$val = new Dase_DBO_Value($this->db);
		$val->item_id = $this->id;
		return $val->find();
	}

	public function getValue($att_ascii_id)
	{
		$db = $this->db;
		//only returns first found
		$prefix = $this->db->table_prefix;
		$sql = "
			SELECT v.value_text
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE v.item_id = ?
			AND v.attribute_id = a.id
			AND a.ascii_id = ?
			LIMIT 1
			";
		$res = Dase_DBO::query($db,$sql,array($this->id,$att_ascii_id),true)->fetch();
		if ($res && $res->value_text) {
			return $res->value_text;
		} else {
			return false;
		}
	}

	public function getCollection()
	{
		//avoids another db lookup
		if ($this->_collection) {
			return $this->_collection;
		}
		$db = $this->db;
		$c = new Dase_DBO_Collection($db);
		$c->load($this->collection_id);
		if ($c) {
			$this->_collection = $c;
			return $c;
		} else {
			return false;
		}
	}

	public function getItemType()
	{
		if ($this->_item_type) {
			return $this->_item_type;
		}
		$db = $this->db;
		$item_type = new Dase_DBO_ItemType($db);
		if ($this->item_type_id) {
			$item_type->load($this->item_type_id);
		} else {
			$item_type->name = 'default';
			$item_type->ascii_id = 'default';
			$item_type->collection_id = $this->collection_id;
		}
		$this->_item_type = $item_type;
		return $item_type;
	}

	public function getMedia()
	{
		if (count($this->_media)) {
			return $this->_media;
		}
		$prefix = $this->db->table_prefix;
		$sql = "
			SELECT * FROM {$prefix}media_file
			WHERE item_id = ?
			ORDER BY file_size ASC 
			";
		$st = Dase_DBO::query($this->db,$sql,array($this->id));
		$last = null;
		while ($m = $st->fetch()) {
			$m['url'] = 
				'/media/'.$m['p_collection_ascii_id'].
				'/'.$m['size'].'/'.$m['filename'];
			$this->_media[$m['size']] = $m;
			$last = $m;
		}
		//last, biggest media 
		if ($last) {
			$this->_media['enclosure'] = $last;
		}
		return $this->_media;
	}

	public function getMediaUrl($size,$app_root,$token = '')
	{
		$med_array = $this->getMedia(); 
		if (isset($med_array[$size])) {
			$m = $med_array[$size];
			$url = $app_root.$m['url'];
			if ($token) {
				$expires = time() + (60*60); 
				$auth_token = md5($url.$expires.$token);
				$url = $url.'?auth_token='.$auth_token.'&'.'expires='.$expires;
			}
			return $url;
		}
	}

	function getMediaCount()
	{
		return count($this->getMedia());
	}

	/** now, this does not auto-create */
	function setItemType($type_ascii_id='')
	{
		if (!$type_ascii_id || 'none' == $type_ascii_id || 'default' == $type_ascii_id) {
			$this->item_type_id = 0;
			$this->item_type_ascii_id = 'default';
			$this->item_type_name = 'default';
			$this->update();
			return true;
		}
		$type = new Dase_DBO_ItemType($this->db);
		$type->ascii_id = $type_ascii_id;
		$type->collection_id = $this->collection_id;
		if ($type->findOne()) {
			$this->item_type_id = $type->id;
			$this->item_type_ascii_id = $type->ascii_id;
			$this->item_type_name = $type->name;
			$this->update();
			$this->_item_type = $type;
			return true;
		} else {
			return false;
		}
	}

	function updateMetadata($value_id,$value_text,$eid,$modifier='',$index=true)
	{
		$v = new Dase_DBO_Value($this->db);
		$v->load($value_id);
		$att = $v->getAttribute();
		$rev = new Dase_DBO_ValueRevisionHistory($this->db);
		$rev->added_text = $value_text;
		if ($modifier) {
			// a bit of a hack. to delete modifier
			// you need to pass in '_delete'
			if ('_delete' == $modifier) {
				$rev->added_modifier = '';
				$rev->deleted_modifier = $v->modifier;
			} else {
				$rev->added_modifier = $modifier;
				$rev->deleted_modifier = $v->modifier;
			}
		}
		$rev->attribute_name = $att->attribute_name;
		$rev->collection_ascii_id = $this->p_collection_ascii_id;
		$rev->dase_user_eid = $eid;
		$rev->deleted_text = $v->value_text;
		$rev->item_serial_number = $this->serial_number;
		$rev->timestamp = date(DATE_ATOM);
		$rev->insert();
		$v->value_text = $value_text;
		$v->update();
		if ($index) {
			$this->buildSearchIndex();
		}
	}

	function removeMetadata($value_id,$eid,$index=true)
	{
		$v = new Dase_DBO_Value($this->db);
		$v->load($value_id);
		$att = $v->getAttribute();
		$rev = new Dase_DBO_ValueRevisionHistory($this->db);
		$rev->added_text = '';
		$rev->attribute_name = $att->attribute_name;
		$rev->collection_ascii_id = $this->p_collection_ascii_id;
		$rev->dase_user_eid = $eid;
		$rev->deleted_text = $v->value_text;
		$rev->deleted_modifier = $v->modifier;
		$rev->item_serial_number = $this->serial_number;
		$rev->timestamp = date(DATE_ATOM);
		$rev->insert();
		$v->delete();
		if ($index) {
			$this->buildSearchIndex();
		}
	}

	/** simple convenience method */
	function updateTitle($value_text,$eid,$index=true)
	{
		//todo: set value revision history as well (using eid)
		$att = Dase_DBO_Attribute::findOrCreate($this->db,$this->p_collection_ascii_id,'title');
		if ($att) {
			$v = new Dase_DBO_Value($this->db);
			$v->item_id = $this->id;
			$v->attribute_id = $att->id;
			if ($v->findOne()) {
				$v->value_text = trim($value_text);
				$v->update();
			} else {
				$v->value_text = trim($value_text);
				$v->insert();
			}
			if ($index) {
				$this->buildSearchIndex();
			}
		}
	}

	function setValue($att_ascii_id,$value_text,$url='',$modifier='',$index=false)
	{
		//todo: this needs work -- no need to 'new' an att
		//todo: set value revision history as well
		$att = new Dase_DBO_Attribute($this->db);
		$att->ascii_id = $att_ascii_id;
		//allows for admin metadata, att_ascii for which
		//always begins 'admin_'
		//NOTE: we now create att if it does not exist
		if (false === strpos($att_ascii_id,'admin_')) {
			$att = Dase_DBO_Attribute::findOrCreate($this->db,$this->p_collection_ascii_id,$att_ascii_id);
		} else {
			$att = Dase_DBO_Attribute::findOrCreateAdmin($this->db,$att_ascii_id);
		}
		if ($att) {
			if ('listbox' == $att->html_input_type) {
				//never includes url or modifier
				$pattern = '/[\n;]/';
				$prepared_string = preg_replace($pattern,'%',trim($value_text));
				$values_array = explode('%',$prepared_string);
				foreach ($values_array as $val_txt) {
					$v = new Dase_DBO_Value($this->db);
					$v->item_id = $this->id;
					$v->attribute_id = $att->id;
					$v->value_text = $val_txt;
					$v->insert();
				}
			} else {
				$v = new Dase_DBO_Value($this->db);
				$v->item_id = $this->id;
				$v->attribute_id = $att->id;
				$v->value_text = trim($value_text);
				$v->url = $url;
				$v->modifier = $modifier;
				$v->insert();
				if ($index) {
					$this->buildSearchIndex();
				}
				return $v;
			}
			if ($index) {
				$this->buildSearchIndex();
			}
		} else {
			//simply returns false if no such attribute
			Dase_Log::debug(LOG_FILE,'[WARNING] no such attribute '.$att_ascii_id);
			return false;
		}
	}

	function setValueLink($att_ascii_id,$value_text,$url,$modifier='',$index=true)
	{
		return $this->setValue($att_ascii_id,$value_text,$url,$modifier,$index);
	}


	/** deletes non-admin values including those with urls (metadata-links) */
	function deleteValues($index=false)
	{
		//should sanity check and archive values
		$admin_ids = Dase_DBO_Attribute::listAdminAttIds($this->db);
		$v = new Dase_DBO_Value($this->db);
		$v->item_id = $this->id;
		foreach ($v->find() as $doomed) {
			//do not delete admin att values
			if (!in_array($doomed->attribute_id,$admin_ids)) {
				$doomed->delete();
			}
		}
		if ($index) {
			$this->buildSearchIndex();
		}
	}

	function deleteAdminValues()
	{
		$a = new Dase_DBO_Attribute($this->db);
		$a->collection_id = 0;
		foreach ($a->find() as $aa) {
			$v = new Dase_DBO_Value($this->db);
			$v->item_id = $this->id;
			$v->attribute_id = $aa->id;
			foreach ($v->find() as $doomed) {
				$doomed->delete();
			}
		}
		return "deleted admin metadata for " . $this->serial_number . "\n";
	}

	function expunge($path_to_media='')
	{
		if ($path_to_media) {
			$filename = $path_to_media.'/'.$this->p_collection_ascii_id.'/deleted/'.$this->serial_number.'.atom';
			file_put_contents($filename,$this->asAtom('http://daseproject.org/deleted/'));
		}

		$this->deleteMedia($path_to_media);
		$this->deleteValues();
		$this->deleteAdminValues();
		$this->deleteSearchIndex();
		//$this->deleteContent();
		//$this->deleteComments();
		//$this->deleteTagItems();
		$this->delete();

		//$ds = Dase_DocStore::get($this->db,$this->config);
		//$ds->deleteItem($this);

		//$this->getCollection()->updateItemCount();
	}

	function deleteContent($index=false)
	{
		$co = new Dase_DBO_Content($this->db);
		$co->item_id = $this->id;
		foreach ($co->find() as $doomed) {
			$doomed->delete();
		}
		if ($index) {
			$this->buildSearchIndex();
		}
	}

	function deleteComments()
	{
		$co = new Dase_DBO_Comment($this->db);
		$co->item_id = $this->id;
		foreach ($co->find() as $doomed) {
			$doomed->delete();
		}
	}

	function deleteTagItems()
	{
		$tag_item = new Dase_DBO_TagItem($this->db);
		$tag_item->item_id = $this->id;
		$tags = array();
		foreach ($tag_item->find() as $doomed) {
			$tag = $doomed->getTag();
			$doomed->delete();
			$tag->updateItemCount();
		}
	}

	function deleteMedia($path_to_media='')
	{
		$mf = new Dase_DBO_MediaFile($this->db);
		$mf->item_id = $this->id;
		foreach ($mf->find() as $doomed) {
			if ($path_to_media) {
				$doomed->moveFileToDeleted($path_to_media);
			}
			$doomed->delete();
		}
	}

	function getTitle()
	{
		$db = $this->db;
		$prefix = $this->db->table_prefix;
		$sql = "
			SELECT v.value_text 
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE a.id = v.attribute_id
			AND a.ascii_id = 'title'
			AND v.item_id = ? 
			";
		$title = Dase_DBO::query($db,$sql,array($this->id))->fetchColumn();
		if (!$title) {
			$title = $this->serial_number;
		}
		return $title;
	}

	function getDescription()
	{
		$db = $this->db;
		$prefix = $this->db->table_prefix;
		$sql = "
			SELECT v.value_text 
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE a.id = v.attribute_id
			AND a.ascii_id = 'description'
			AND v.item_id = ? 
			";
		$description = Dase_DBO::query($db,$sql,array($this->id))->fetchColumn();
		if (!$description) {
			$description = $this->getTitle();
		}
		return $description;
	}

	function getRights()
	{
		$db = $this->db;
		$prefix = $db->table_prefix;
		$sql = "
			SELECT v.value_text 
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE a.id = v.attribute_id
			AND a.ascii_id = 'rights'
			AND v.item_id = ? 
			";
		$text = Dase_DBO::query($db,$sql,array($this->id))->fetchColumn();
		if (!$text) { $text = 'daseproject.org'; }
		return $text;
	}

	public function getEnclosure()
	{
		$media = $this->getMedia();
		if (isset($media['enclosure'])) {
			return $media['enclosure'];
		}
	}


	function injectAtomEntryData(Dase_Atom_Entry $entry,$app_root,$authorize_links=false)
	{
		if (!$this->id) { return false; }

		/* namespaces */

		$d = Dase_Atom::$ns['d'];
		$thr = Dase_Atom::$ns['thr'];

		/* resources */

		$base_url = $app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number;

		/* standard atom stuff */

		$entry->setId($base_url);

		$entry->addAuthor($this->created_by_eid);
		//todo: I think this can be simplified when DASe 1.0 is retired
		if (is_numeric($this->updated)) {
			$entry->setUpdated(date(DATE_ATOM,$this->updated));
		} else {
			$entry->setUpdated($this->updated);
		}
		if (is_numeric($this->created)) {
			$entry->setPublished(date(DATE_ATOM,$this->created));
		} else {
			$entry->setPublished($this->created);
		}

		//atompub
		$entry->setEdited($entry->getUpdated());

		//alternate link
		$entry->addLink($app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number,'alternate');

		//link to item metadata json, used for editing metadata
		$entry->addLink($app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'/metadata.json','http://daseproject.org/relation/metadata','application/json');

		$entry->addLink(
			$app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'.atom',
			'edit','application/atom+xml');
		if ($authorize_links) {
			$entry->addLink(
				$app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'.atom',
				'http://daseproject.org/relation/cached','application/atom+xml');
		} else {
			$entry->addLink(
				$app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'/authorized.atom',
				'http://daseproject.org/relation/authorized','application/atom+xml');
		}
		$entry->addLink(
			$app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'/content',
			'http://daseproject.org/relation/edit-content');
		$entry->addLink(
			$app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'.json',
			'http://daseproject.org/relation/edit','application/json');
		$entry->addLink(
			$app_root.'/collection/'.$this->p_collection_ascii_id.'/service',
			'service','application/atomsvc+xml');
		$entry->addLink(
			$app_root.'/collection/'.$this->p_collection_ascii_id.'/attributes.json',
			'http://daseproject.org/relation/attributes',
			'application/json');

		/**** COMMENT LINK (threading extension) **********/

		$replies = $entry->addLink($app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'/comments','replies' );
		if ($this->comments_count) {
			//lookup
			$replies->setAttributeNS($thr,'thr:count',$this->comments_count);
			//lookup
			$replies->setAttributeNS($thr,'thr:updated',$this->comments_updated);
		}

		/* dase categories */

		$entry->setEntrytype('item');

		//allows us to replace all if/when necessary :(
		$entry->addCategory($app_root,"http://daseproject.org/category/base_url");

		$entry->addCategory($this->item_type_ascii_id,'http://daseproject.org/category/item_type',$this->item_type_name);
		$entry->addCategory($this->p_collection_ascii_id,'http://daseproject.org/category/collection',$this->collection_name);
		$entry->addCategory($this->id,'http://daseproject.org/category/item_id');
		$entry->addCategory($this->serial_number,'http://daseproject.org/category/serial_number');

		if ($this->status) {
			$entry->addCategory($this->status,'http://daseproject.org/category/status');
		} else {
			$entry->addCategory('public','http://daseproject.org/category/status');
		}

		/********* METADATA **********/

		$item_metadata = $this->getMetadata(true);
		foreach ($item_metadata as $row) {
			if ($row['url']) { //create metadata LINK
				$metadata_link = $entry->addLink(
					$row['url'],
					'http://daseproject.org/relation/metadata-link/'.
					$this->p_collection_ascii_id.'/'.$row['ascii_id'],
					'',
					'',
					$row['value_text']
				);
				$metadata_link->setAttributeNS($d,'d:attribute',$row['attribute_name']);
				$metadata_link->setAttributeNS($d,'d:edit-id',$app_root.$row['edit-id']);
				if ($row['modifier']) {
					$metadata_link->setAttributeNS($d,'d:mod',$row['modifier']);
					if ($row['modifier_type']) {
						$metadata_link->setAttributeNS($d,'d:modtype',$row['modifier_type']);
					}
				}
			} 
		}
		foreach ($item_metadata as $row) {
			if ($row['url']) { 
				//already made metadata links
			} else { //create metadata CATEGORY
				if (0 == $row['collection_id']) {
					$meta = $entry->addCategory(
						$row['ascii_id'],'http://daseproject.org/category/admin_metadata',
						$row['attribute_name'],$row['value_text']);
				} else {
					if ($row['is_public']) {
						$meta = $entry->addCategory(
							$row['ascii_id'],'http://daseproject.org/category/metadata',
							$row['attribute_name'],$row['value_text']);
						$meta->setAttributeNS($d,'d:edit-id',$app_root.$row['edit-id']);
					} else {
						$meta = $entry->addCategory(
							$row['ascii_id'],'http://daseproject.org/category/private_metadata',
							$row['attribute_name'],$row['value_text']);
						$meta->setAttributeNS($d,'d:edit-id',$app_root.$row['edit-id']);
					}
					if ('title' == $row['ascii_id'] || 'Title' == $row['attribute_name']) {
						$entry->setTitle($row['value_text']);
					}
					if ('rights' == $row['ascii_id']) {
						$entry->setRights($row['value_text']);
					}
					if ($row['modifier']) {
						$meta->setAttributeNS($d,'d:mod',$row['modifier']);
						if ($row['modifier_type']) {
							$meta->setAttributeNS($d,'d:modtype',$row['modifier_type']);
						}
					}
				}
			}
		}

		//this will only "take" if there is not already a title
		$entry->setTitle($this->serial_number);

		/********* CONTENT **********/

		$thumb_url = $this->getMediaUrl('thumbnail',$app_root);
		$viewitem_url = $this->getMediaUrl('viewitem',$app_root);

		if ($this->content_length) {
			$content = $this->getContents();
			if ($content && $content->text) {
				if (!$content->type) {
					$content->type = 'text';
				}
				if ('application/json' == $content->type) {
					$entry->setExternalContent($base_url.'/content','application/json');
				} else {
					$entry->setContent($content->text,$content->type);
				}
				/* put thumbnail in summary */
				if ($thumb_url) {
					$entry->setThumbnail($app_root.$thumb_url);	
				}
			} else {
			/** skip splash for now 
			$list = '';
			foreach ($item_metadata as $row) {
				$list .= "
					<dt>{$row['attribute_name']}</dt>
					<dd>{$row['value_text']}</dd>
					";
			}
			$splash = "
				<div id=\"splash\">
				<img src=\"{$app_root}$thumb_url\"/>
				<img src=\"{$app_root}$viewitem_url\"/>
				<dl>$list</dl>
				</div>
				";
			$entry->setContent($splash,'html');
			 */
			}
		}

		/*******  MEDIA  ***********/

		$item_media = $this->getMedia();
		$token = $this->config->getAuth('token');

		if (isset($item_media['enclosure'])) {
			$enc = $item_media['enclosure'];
			if ($authorize_links) {
				$entry->addLink($this->getMediaUrl('enclosure',$app_root,$token),'enclosure',$enc['mime_type'],$enc['file_size']);
			} else {
				$entry->addLink($this->getMediaUrl('enclosure',$app_root),'enclosure',$enc['mime_type'],$enc['file_size']);
			}
		}

		/* edit-media link */

		$entry->addLink($this->getEditMediaUrl($app_root),'edit-media');
		$media_url = $app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'/media';
		$entry->addLink($media_url,'http://daseproject.org/relation/add-media');

		/* media rss ext */

		foreach ($this->getMedia() as $size => $med) {
			if ('thumbnail' == $size) {
				$media_thumbnail = $entry->addElement('media:thumbnail','',Dase_Atom::$ns['media']);
				$media_thumbnail->setAttribute('url',$app_root.$med['url']);
				$media_thumbnail->setAttribute('width',$med['width']);
				$media_thumbnail->setAttribute('height',$med['height']);
			} else {
				if ($size != 'enclosure') {
					$media_content = $entry->addElement('media:content','',Dase_Atom::$ns['media']);
					if ($authorize_links) {
						$media_content->setAttribute('url',$this->getMediaUrl($size,$app_root,$token));
					} else {
						$media_content->setAttribute('url',$this->getMediaUrl($size,$app_root));
					}
					if ($med['width'] && $med['height']) {
						$media_content->setAttribute('width',$med['width']);
						$media_content->setAttribute('height',$med['height']);
					}
					$media_content->setAttribute('fileSize',$med['file_size']);
					$media_content->setAttribute('type',$med['mime_type']);
					$media_category = $media_content->appendChild($entry->dom->createElement('media:category'));
					$media_category->appendChild($entry->dom->createTextNode($size));
				}
			}
		}
		return $entry;
	}

	function injectAtomFeedData(Dase_Atom_Feed $feed,$app_root)
	{
		if (!$this->id) { return false; }
		$c = $this->getCollection();
		if (is_numeric($this->updated)) {
			$updated = date(DATE_ATOM,$this->updated);
		} else {
			$updated = $this->updated;
		}
		$feed->setUpdated($updated);
		$feed->setTitle($this->getTitle());
		$feed->setId('tag:daseproject.org,2008:'.Dase_Util::getUniqueName());
		$feed->addLink($app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'.atom','self' );
		$feed->addAuthor();
		return $feed;
	}

	function asAtom($app_root)
	{
		return $this->retrieveAtomDoc($app_root,true);
	}

	function asJson($app_root)
	{
		return $this->retrieveJsonDoc($app_root);
	}

	/** experimental */
	function asAtomJson($app_root)
	{
		$entry = new Dase_Atom_Entry;
		$this->injectAtomEntryData($entry,$app_root);
		return $entry->asJson();
	}


	function asAtomEntry($app_root="{APP_ROOT}",$authorize_links=false)
	{
		if ($authorize_links) {
			$entry = new Dase_Atom_Entry_Item;
			$entry = $this->injectAtomEntryData($entry,$app_root,true);
			return $entry->asXml();
		} else {
			return $this->retrieveAtomDoc($app_root);
		}
	}

	/** todo this does NOT work */
	function mediaAsAtomFeed($app_root) 
	{
		$feed = new Dase_Atom_Feed;
		$this->injectAtomFeedData($feed,$app_root);
		foreach ($this->getMedia('updated DESC') as $m) {
			$entry = $feed->addEntry();
			$m->injectAtomEntryData($entry,$app_root);
		}
		return $feed->asXml();
	}	

	public function getUrl($app_root) 
	{
		return $app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number;
	}

	public function getUnique() 
	{
		if (!$this->p_collection_ascii_id) {
			$this->p_collection_ascii_id = $this->getCollection()->ascii_id;
			$this->update();
		}
		return $this->p_collection_ascii_id.'/'.$this->serial_number;
	}

	public function getEditMediaUrl($app_root='')
	{
		return $app_root.'/media/'.$this->p_collection_ascii_id.'/'.$this->serial_number;
	}

	public function getAtomPubServiceDoc($app_root) {
		$c = $this->getCollection();
		$app = new Dase_Atom_Service;
		$workspace = $app->addWorkspace($c->collection_name.' Item '.$this->serial_number.' Workspace');
		$media_coll = $workspace->addCollection($app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'/media.atom',$c->collection_name.' Item '.$this->serial_number.' Media'); 
		foreach(Dase_Media::getAcceptedTypes() as $type) {
			$media_coll->addAccept($type);
		}
		$comments_coll = $workspace->addCollection($app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'/comments.atom',$c->collection_name.' Item '.$this->serial_number.' Comments'); 
		$comments_coll->addAccept('text/plain');
		$comments_coll->addAccept('text/html');
		$comments_coll->addAccept('application/xhtml+xml');
		$metadata_coll = $workspace->addCollection($app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'/metadata.atom',$c->collection_name.' Item '.$this->serial_number.' Metadata'); 
		$metadata_coll->addAccept('application/x-www-form-urlencoded');
		$metadata_coll->addAccept('application/json');
		return $app->asXml();
	}

	public function statusAsJson()
	{
		$labels['public'] = "Public";
		$labels['draft'] = "Draft (Admin View Only)";
		$labels['delete'] = "Marked for Deletion";
		$labels['archive'] = "In Deep Storage";

		$status['term'] = $this->status;
		$status['label'] = $labels[$this->status];

		return Dase_Json::get($status);
	}

	public function getContents()
	{
		if ($this->content_length) {
			$db = $this->db;
			$contents = new Dase_DBO_Content($db);
			$contents->item_id = $this->id;
			$contents->orderBy('updated DESC');
			if ($contents->findOne()) {
				$this->_content = $contents;
			}
			return $contents;
		} else {
			return false;
		}
	}

	public function getContentRevisions()
	{
		$db = $this->db;
		$contents = new Dase_DBO_Content($db);
		$contents->item_id = $this->id;
		$contents->orderBy('updated DESC');
		return $contents->find();
	}

	public function getCommentsJson($app_root,$eid='')
	{
		$db = $this->db;
		$comments = new Dase_DBO_Comment($db);
		$comments->item_id = $this->id;
		$comments->updated_by_eid = $eid;
		$comments->orderBy('updated DESC');
		$com = array();
		foreach ($comments->find() as $c_obj) {
			$c['id'] = $c_obj->id;
			//$c['updated'] = $c_obj->updated;
			$c['updated'] = date('D M j, Y \a\t g:ia',strtotime($c_obj->updated));
			$c['eid'] = $c_obj->updated_by_eid;
			$c['text'] = $c_obj->text;
			$c['url'] = $this->getUrl($app_root).'/comments/'.$c_obj->id;
			$com[] = $c;
		}
		return Dase_Json::get($com);
	}

	public function getContentJson()
	{
		$c_obj = $this->getContents();
		$content = array();
		if ($c_obj) {
			$content['latest']['text'] = $c_obj->text;
			$content['latest']['date'] = $c_obj->updated;
		} else {
			$content['latest']['text'] = '';
			$content['latest']['date'] = ''; 
		}
		return Dase_Json::get($content);
	}

	public function setContent($text,$eid,$type="text",$index=true)
	{
		$content = new Dase_DBO_Content($this->db);
		$content->item_id = $this->id;
		//todo: security! filter input....
		$content->text = $text;
		$content->type = $type;
		$content->p_collection_ascii_id = $this->p_collection_ascii_id;
		$content->p_serial_number = $this->serial_number;
		$content->updated = date(DATE_ATOM);
		$content->updated_by_eid = $eid;
		$res = $content->insert();

		$this->content_length = strlen($content->text);
		$this->update();

		if ($index) {
			$this->buildSearchIndex();
		}
		return $res;
	}

	public function addComment($text,$eid)
	{
		$note = new Dase_DBO_Comment($this->db);
		$note->item_id = $this->id;
		//todo: security! filter input....
		$note->text = $text;
		$note->p_collection_ascii_id = $this->p_collection_ascii_id;
		$note->p_serial_number = $this->serial_number;
		$note->updated = date(DATE_ATOM);
		$note->updated_by_eid = $eid;
		$res = $note->insert();
		//denormalization
		$this->comments_count = $this->comments_count+1;
		$this->comments_updated = $note->updated;
		$this->update();
		$this->buildSearchIndex();
		return $res;
	}

	public function getTags()
	{
		$tags = array();
		$tag_item = new Dase_DBO_TagItem($this->db);
		$tag_item->item_id = $this->id;
		foreach ($tag_item->find() as $ti) {
			$tags[] = $ti->getTag();
		}
		if (count($tags)) {
			return $tags;
		} else {
			return false;
		}
	}

	public static function sortIdArrayByUpdated($db,$item_ids)
	{
		$sortable_array = array();
		$prefix = $db->table_prefix;
		$dbh = $db->getDbh();
		$sql = "
			SELECT updated 
			FROM {$prefix}item i
			WHERE i.id = ? 
			";
		$sth = $dbh->prepare($sql);
		foreach ($item_ids as $item_id) {
			$sth->execute(array($item_id));
			$updated = $sth->fetchColumn();
			$sortable_array[$item_id] = $updated;
		}
		if (is_array($sortable_array)) {
			arsort($sortable_array);
			return array_keys($sortable_array);
		}
	}

	public static function sortIdArray($db,$sort,$item_ids)
	{
		$sortable_array = array();
		$test_att = new Dase_DBO_Attribute($db);
		$test_att->ascii_id = $sort;
		if (!$test_att->findOne()) {
			return $item_ids;
		}
		$prefix = $db->table_prefix;
		$dbh = $db->getDbh();
		$sql = "
			SELECT v.value_text
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE v.item_id = ?
			AND v.attribute_id = a.id
			AND a.ascii_id = ?
			LIMIT 1
			";
		$sth = $dbh->prepare($sql);
		foreach ($item_ids as $item_id) {
			$sth->execute(array($item_id,$sort));
			$vt = $sth->fetchColumn();
			$value_text = $vt ? $vt : 99999999;
			$sortable_array[$item_id] = $value_text;
		}
		if (is_array($sortable_array)) {
			asort($sortable_array);
			return array_keys($sortable_array);
		}
	}

	/** expires any cache that might hold stale metadata */
	public function expireCaches($cache)
	{
		//more will (perhaps) go here
		//
		// attributes json (includes tallies)
		$cache_id = "get|collection/".$this->p_collection_ascii_id."/attributes/public/tallies|json|cache_buster=stripped&format=json";
		$cache->expire($cache_id);

	}

	public function mapConfiguredAdminAtts()
	{
		$c = $this->getCollection();
		foreach ($c->getAttributes() as $att) {
			if ($att->mapped_admin_att_id) {
				foreach ($this->getAdminMetadata() as $row) {
					if ($att->mapped_admin_att_id == $row['att_id']) {
						$this->setValue($att->ascii_id,$row['value_text']);
					}
				}
			}
		}
	}
}
