<?php

require_once 'Dase/DBO/Autogen/Item.php';

class Dase_DBO_Item extends Dase_DBO_Autogen_Item 
{

	public $collection = null;
	public $item_type;
	public $media = array();
	public $values = array();

	public static function create($collection_ascii_id,$serial_number=null,$eid=null)
	{
		if (!$eid) {
			$eid = '_dase';
		}
		$c = Dase_DBO_Collection::get($collection_ascii_id);
		return $c->createNewItem($serial_number,$eid);
	}

	public static function get($collection_ascii_id,$serial_number)
	{
		if (!$collection_ascii_id || !$serial_number) {
			throw new Exception('missing information');
		}
		$c = Dase_DBO_Collection::get($collection_ascii_id);
		if (!$c) {
			return false;
		}
		$item = new Dase_DBO_Item;
		$item->collection_id = $c->id;
		$item->serial_number = $serial_number;
		return $item->findOne();
	}

	public function deleteSearchIndexes()
	{
		$prefix = Dase_Config::get('table_prefix');
		$db = Dase_DB::get();
		$sql = "
			DELETE
			FROM {$prefix}search_table 
			WHERE item_id = $this->id
			";
		$db->query($sql);
		$sql = "
			DELETE
			FROM {$prefix}admin_search_table 
			WHERE item_id = $this->id
			";
		$db->query($sql);
	}

	public function buildSearchIndex()
	{
		$prefix = Dase_Config::get('table_prefix');
		$db = Dase_DB::get();
		//todo: make sure item->id is an integer
		$sql = "
			DELETE
			FROM {$prefix}search_table 
			WHERE item_id = $this->id
			";
		$db->query($sql);
		$sql = "
			DELETE
			FROM {$prefix}admin_search_table 
			WHERE item_id = $this->id
			";
		$db->query($sql);
		//search table
		$composite_value_text = '';
		$db = Dase_DB::get();
		$sql = "
			SELECT value_text
			FROM {$prefix}value v
			WHERE v.item_id = $this->id
			AND v.value_text != ''
			AND v.attribute_id in (SELECT id FROM {$prefix}attribute a where a.in_basic_search = true)
			";
		$st = $db->prepare($sql);
		$st->execute();
		//todo: this should be a foreach
		while ($value_text = $st->fetchColumn()) {
			$composite_value_text .= $value_text . " ";
		}

		//todo: fix this to get the latest version of content only
		$content = $this->getContents();
		if ($content && $content->text) {
			$composite_value_text .= $content->text . " ";
		}
		$c = $this->getCollection();
		$search_table = new Dase_DBO_SearchTable;
		$search_table->value_text = $composite_value_text;
		$search_table->item_id = $this->id;
		$search_table->collection_id = $this->collection_id;
		$search_table->collection_ascii_id = $c->ascii_id;
		$search_table->updated = date(DATE_ATOM);
		if ($composite_value_text) {
			$search_table->insert();
		}

		//admin search table
		$composite_value_text = '';
		$sql = "
			SELECT value_text
			FROM {$prefix}value
			WHERE item_id = $this->id
			";
		$st = $db->prepare($sql);
		$st->execute();
		while ($value_text = $st->fetchColumn()) {
			$composite_value_text .= $value_text . " ";
		}
		$content = $this->getContents();
		if ($content && $content->text) {
			$composite_value_text .= $content->text . " ";
		}
		$search_table = new Dase_DBO_AdminSearchTable;
		$search_table->value_text = $composite_value_text;
		$search_table->item_id = $this->id;
		$search_table->collection_id = $this->collection_id;
		$search_table->collection_ascii_id = $c->ascii_id;
		$search_table->updated = date(DATE_ATOM);
		$search_table->insert();
		$this->updated = date(DATE_ATOM);
		$this->update();
		Dase_Log::debug("built indexes for " . $this->serial_number);
	}

	public function getMetadata($att_ascii_id = '')
	{
		$prefix = Dase_Config::get('table_prefix');
		$metadata = array();
		$bound_params = array();
		$sql = "
			SELECT a.ascii_id, a.attribute_name,v.value_text,a.collection_id, v.id, a.is_on_list_display, a.is_public
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE v.item_id = ?
			AND v.attribute_id = a.id
			AND a.collection_id != 0
			";
		$bound_params[] = $this->id;
		if ($att_ascii_id) {
			$sql .= "
				AND a.ascii_id = ?
				";
			$bound_params[] = $att_ascii_id;
		}
		$sql .= "
			ORDER BY a.sort_order,v.value_text
			";
		$st = Dase_DBO::query($sql,$bound_params);
		while ($row = $st->fetch()) {
			$metadata[] = $row;
		}
	
		return $metadata;
	}

	public function getAdminMetadata($att_ascii_id = '')
	{
		$prefix = Dase_Config::get('table_prefix');
		$metadata = array();
		$bound_params = array();
		$sql = "
			SELECT a.ascii_id, a.attribute_name,v.value_text,a.collection_id, v.id, a.is_on_list_display, a.is_public
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE v.item_id = ?
			AND v.attribute_id = a.id
			AND a.collection_id = 0
			";
		$bound_params[] = $this->id;
		if ($att_ascii_id) {
			$sql .= "
				AND a.ascii_id = ?
				";
			$bound_params[] = $att_ascii_id;
		}
		$sql .= "
			ORDER BY a.sort_order,v.value_text
			";
		$st = Dase_DBO::query($sql,$bound_params);
		while ($row = $st->fetch()) {
			$metadata[] = $row;
		}
	
		return $metadata;
	}

	//used for edit metadata form
	public function getMetadataJson()
	{
		$prefix = Dase_Config::get('table_prefix');
		$metadata = array();
		$bound_params = array();
		$sql = "
			SELECT a.id as att_id,a.ascii_id,a.attribute_name,a.html_input_type,v.value_text,v.id as value_id, a.collection_id
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE v.item_id = ?
			AND v.attribute_id = a.id
			ORDER BY a.sort_order,v.value_text
			";
		$bound_params[] = $this->id;
		$st = Dase_DBO::query($sql,$bound_params);
		while ($row = $st->fetch()) {
			$set = array();
			$set['value_id'] = $row['value_id'];
			$set['collection_id'] = $row['collection_id'];
			$set['att_ascii_id'] = $row['ascii_id'];
			$set['attribute_name'] = $row['attribute_name'];
			$set['html_input_type'] = $row['html_input_type'];
			$set['value_text'] = $row['value_text'];
			if (in_array($row['html_input_type'],array('radio','checkbox','select','text_with_menu'))) {
				$att = new Dase_DBO_Attribute;
				$att->load($row['att_id']);
				$set['values'] = $att->getFormValues();
			}
			$metadata[] = $set;
		}
		return Dase_Json::get($metadata);
	}

	public function getValues()
	{
		$val = new Dase_DBO_Value;
		$val->item_id = $this->id;
		return $val->find();
	}

	public function getValue($att_ascii_id)
	{
		$prefix = Dase_Config::get('table_prefix');
		$sql = "
			SELECT v.value_text
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE v.item_id = ?
			AND v.attribute_id = a.id
			AND a.ascii_id = ?
			LIMIT 1
			";
		$res = Dase_DBO::query($sql,array($this->id,$att_ascii_id),true)->fetch();
		if ($res && $res->value_text) {
			return $res->value_text;
		} else {
			return false;
		}
	}

	public function getCollection()
	{
		//avoids another db lookup
		if ($this->collection) {
			return $this->collection;
		}
		$c = new Dase_DBO_Collection;
		$c->load($this->collection_id);
		if ($c) {
			$this->collection = $c;
			return $c;
		} else {
			return false;
		}
	}

	public function getItemType()
	{
		if ($this->item_type) {
			return $this->item_type;
		}
		$item_type = new Dase_DBO_ItemType;
		if ($this->item_type_id) {
			$item_type->load($this->item_type_id);
		} else {
			$item_type->label = 'default';
			$item_type->ascii_id = 'default';
		}
		$this->item_type = $item_type;
		return $this->item_type;
	}

	public function getMedia($order_by='file_size')
	{
		Dase_Log::debug("getting media for " . $this->id);
		$c = $this->getCollection();
		$m = new Dase_DBO_MediaFile;
		$m->p_collection_ascii_id = $c->ascii_id;
		$m->p_serial_number = $this->serial_number;
		$m->orderBy($order_by);
		return $m->find();
	}

	public function getEnclosure()
	{
		$c = $this->getCollection();
		$m = new Dase_DBO_MediaFile;
		$m->p_collection_ascii_id = $c->ascii_id;
		$m->p_serial_number = $this->serial_number;
		$m->addWhere('file_size','null','is not');
		//todo: make sure file_size has values!
		$m->orderBy('file_size DESC');
		//$m->orderBy('width DESC');
		return $m->findOne();
	}

	public function getMediaUrl($size)
	{  //size really means type here
		$c = $this->getCollection();
		$m = new Dase_DBO_MediaFile;
		$m->p_collection_ascii_id = $c->ascii_id;
		$m->p_serial_number = $this->serial_number;
		$m->size = $size;
		if ($m->findOne()) {
			$url = APP_ROOT . "/media/{$c->ascii_id}/$size/$m->filename";
			return $url;
		} else {
			return false;
		}
	}

	function getMediaCount()
	{
		$prefix = Dase_Config::get('table_prefix');
		$this->collection || $this->getCollection();
		$db = Dase_DB::get();
		$sql = "
			SELECT count(*) 
			FROM {$prefix}media_file
			WHERE p_serial_number = ?
			AND p_collection_ascii_id = ?
			";
		return Dase_DBO::query($sql,array($this->serial_number,$this->collection->ascii_id),true)->fetchColumn();
	}

	function setItemType($type_ascii_id)
	{
		if (!$type_ascii_id || 'none' == $type_ascii_id) {
			$this->item_type_id = 0;
			$this->update();
			return true;
		}
		$type = new Dase_DBO_ItemType;
		$type->ascii_id = $type_ascii_id;
		$type->collection_id = $this->collection_id;
		if ($type->findOne()) {
			$this->item_type_id = $type->id;
			$this->update();
			return true;
		} else {
			return false;
		}
	}

	function updateMetadata($request,$value_id,$value_text)
	{
		$c = $this->getCollection();
		$v = new Dase_DBO_Value;
		$v->load($value_id);
		$att = $v->getAttribute();
		$rev = new Dase_DBO_ValueRevisionHistory;
		$rev->added_text = $value_text;
		$rev->attribute_name = $att->attribute_name;
		$rev->collection_ascii_id = $c->ascii_id;
		$rev->dase_user_eid = $request->getUser()->eid;
		$rev->deleted_text = $v->value_text;
		$rev->item_serial_number = $this->serial_number;
		$rev->timestamp = date(DATE_ATOM);
		$rev->insert();
		$v->value_text = $value_text;
		$v->update();
		$this->buildSearchIndex();
	}

	function removeMetadata($request,$value_id)
	{
		$c = $this->getCollection();
		$v = new Dase_DBO_Value;
		$v->load($value_id);
		$att = $v->getAttribute();
		$rev = new Dase_DBO_ValueRevisionHistory;
		$rev->added_text = '';
		$rev->attribute_name = $att->attribute_name;
		$rev->collection_ascii_id = $c->ascii_id;
		$rev->dase_user_eid = $request->getUser()->eid;
		$rev->deleted_text = $v->value_text;
		$rev->item_serial_number = $this->serial_number;
		$rev->timestamp = date(DATE_ATOM);
		$rev->insert();
		$v->delete();
		$this->buildSearchIndex();
	}

	function setValue($att_ascii_id,$value_text)
	{
		//todo: set value revision history as well
		$c = $this->getCollection();
		$att = new Dase_DBO_Attribute;
		$att->ascii_id = $att_ascii_id;
		//allows for admin metadata, att_ascii for which
		//always begins 'admin_'
		//NOTE: we now create att if it does not exist
		if (false === strpos($att_ascii_id,'admin_')) {
			$att = Dase_DBO_Attribute::findOrCreate($c->ascii_id,$att_ascii_id);
		} else {
			$att = Dase_DBO_Attribute::findOrCreateAdmin($att_ascii_id);
		}
		if ($att) {
			$v = new Dase_DBO_Value;
			$v->item_id = $this->id;
			$v->attribute_id = $att->id;
			$v->value_text = trim($value_text);
			return($v->insert());
		} else {
			//simply returns false if no such attribute
			Dase_Log::debug('[WARNING] no such attribute '.$att_ascii_id);
			return false;
		}
	}

	function deleteValues()
	{
		//should sanity check and archive values
		$admin_ids = Dase_DBO_Attribute::listAdminAttIds();
		$v = new Dase_DBO_Value;
		$v->item_id = $this->id;
		foreach ($v->find() as $doomed) {
			//do not delete admin att values
			if (!in_array($doomed->attribute_id,$admin_ids)) {
				$doomed->delete();
			}
		}
	}

	function deleteAdminValues()
	{
		$a = new Dase_DBO_Attribute;
		$a->collection_id = 0;
		foreach ($a->find() as $aa) {
			$v = new Dase_DBO_Value;
			$v->item_id = $this->id;
			$v->attribute_id = $aa->id;
			foreach ($v->find() as $doomed) {
				$doomed->delete();
			}
		}
		return "deleted admin metadata for " . $this->serial_number . "\n";
	}

	function expunge()
	{
		$c = $this->getCollection();
		$filename = Dase_Config::get('path_to_media').'/'.$c->ascii_id.'/deleted/'.$this->serial_number.'.atom';
		file_put_contents($filename,$this->asAtom());
		
		$this->deleteMedia();
		$this->deleteValues();
		$this->deleteAdminValues();
		$this->deleteSearchIndexes();
		$this->deleteContent();
		$this->deleteComments();
		$this->deleteTagItems();
		$this->deleteItemRelations();
		$this->delete();
		$c->updateItemCount();
	}


	function deleteItemRelations()
	{
		$irs = new Dase_DBO_ItemRelation;
		$irs->parent_serial_number = $this->serial_number;
		foreach ($irs->find() as $ir) {
			$ir->delete();
		}
		$irs = new Dase_DBO_ItemRelation;
		$irs->child_serial_number = $this->serial_number;
		foreach ($irs->find() as $ir) {
			$ir->delete();
		}
	}

	function deleteContent()
	{
		$co = new Dase_DBO_Content;
		$co->item_id = $this->id;
		foreach ($co->find() as $doomed) {
			$doomed->delete();
		}
	}

	function deleteComments()
	{
		$co = new Dase_DBO_Comment;
		$co->item_id = $this->id;
		foreach ($co->find() as $doomed) {
			$doomed->delete();
		}
	}

	function deleteTagItems()
	{
		$tag_item = new Dase_DBO_TagItem;
		$tag_item->item_id = $this->id;
		$tags = array();
		foreach ($tag_item->find() as $doomed) {
			$tag = $doomed->getTag();
			$doomed->delete();
			$tag->updateItemCount();
		}
	}

	function deleteMedia()
	{
		$mf = new Dase_DBO_MediaFile;
		$mf->item_id = $this->id;
		foreach ($mf->find() as $doomed) {
			$doomed->delete();
		}
	}

	function getTitle()
	{
		$prefix = Dase_Config::get('table_prefix');
		$sql = "
			SELECT v.value_text 
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE a.id = v.attribute_id
			AND a.ascii_id = 'title'
			AND v.item_id = ? 
			";
		$title = Dase_DBO::query($sql,array($this->id))->fetchColumn();
		if (!$title) {
			$title = $this->serial_number;
		}
		return $title;
	}

	public function getParents()
	{
		$cats = array();
		$c = $this->getCollection();
		$relations = new Dase_DBO_ItemRelation;
		$relations->collection_ascii_id = $c->ascii_id;
		$relations->child_serial_number = $this->serial_number;
		foreach ($relations->find() as $prel) {
			$parent_type = $prel->getParentType();
			$cat['scheme'] = $parent_type->getBaseUrl($c->asciii_id);
			$cat['term'] = $prel->parent_serial_number;
			$parent_item = Dase_DBO_Item::get($prel->collection_ascii_id,$prel->parent_serial_number);
			$cat['label'] = $parent_type->name.': '.$parent_item->getTitle();
			$cat['item_url'] = $parent_item->getBaseUrl();
			$cats[] = $cat;
		}
		return $cats;
	}

	function getDescription()
	{
		$prefix = Dase_Config::get('table_prefix');
		$sql = "
			SELECT v.value_text 
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE a.id = v.attribute_id
			AND a.ascii_id = 'description'
			AND v.item_id = ? 
			";
		$description = Dase_DBO::query($sql,array($this->id))->fetchColumn();
		if (!$description) {
			$description = $this->getTitle();
		}
		return $description;
	}

	function getRights()
	{
		$prefix = Dase_Config::get('table_prefix');
		$sql = "
			SELECT v.value_text 
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE a.id = v.attribute_id
			AND a.ascii_id = 'rights'
			AND v.item_id = ? 
			";
		$text = Dase_DBO::query($sql,array($this->id))->fetchColumn();
		if (!$text) { $text = 'daseproject.org'; }
		return $text;
	}

	function injectAtomEntryData(Dase_Atom_Entry $entry)
	{
		if (!$this->id) { return false; }
		$d = Dase_Atom::$ns['d'];
		$thr = Dase_Atom::$ns['thr'];
		$c = $this->getCollection();
		$type = $this->getItemType();
		//todo: I think this can be simplified when DASe 1.0 is retired
		if (is_numeric($this->updated)) {
			$updated = date(DATE_ATOM,$this->updated);
		} else {
			$updated = $this->updated;
		}
		if (is_numeric($this->created)) {
			$created = date(DATE_ATOM,$this->created);
		} else {
			$created = $this->created;
		}
		$entry->setTitle($this->getTitle());
		$entry->setRights($this->getRights());
		$entry->addAuthor($this->created_by_eid);
		//for AtomPub
		$entry->setEdited($updated);
		$entry->addLink(APP_ROOT.'/item/'.$c->ascii_id.'/'.$this->serial_number,'alternate');
		if ('default' == $type->ascii_id) {
			$entry->addLink(APP_ROOT.'/item/'.$c->ascii_id.'/'.$this->serial_number.'.atom','edit' );
			$entry->addLink(APP_ROOT.'/collection/'.$c->ascii_id.'/attributes.json','http://daseproject.org/relation/attributes','application/json','',$c->collection_name.' Attributes' );
			//$entry->addLink(APP_ROOT.'/collection/'.$c->ascii_id.'/attributes.cats','http://daseproject.org/relation/attributes','application/atomcat+xml','',$c->collection_name.' Attributes' );
		} else {
			$entry->addLink($type->getBaseUrl().'/'.$this->serial_number.'.atom','edit' );
			$entry->addLink($type->getBaseUrl().'/service','service','application/atomsvc+xml','',$type->name.' Item Type Service Doc' );
			$entry->addLink($type->getBaseUrl().'/attributes.json','http://daseproject.org/relation/attributes','application/json','',$type->name.' Attributes' );
			//$entry->addLink($type->getBaseUrl().'/attributes.cats','http://daseproject.org/relation/attributes','application/atomcat+xml','',$type->name.' Attributes' );
		}

		$replies = $entry->addLink(APP_ROOT.'/item/'.$this->collection->ascii_id.'/'.$this->serial_number.'/comments','replies' );
		$thr_count = $this->getCommentsCount();
		if ($thr_count) {
			$replies->setAttributeNS($thr,'thr:count',$thr_count);
			$replies->setAttributeNS($thr,'thr:updated',$this->getCommentsUpdated());
		}
		$entry->setUpdated($updated);
		$entry->setPublished($created);
		$entry->setId($this->getBaseUrl());
		$entry->addCategory($this->collection->ascii_id,'http://daseproject.org/category/collection',$this->collection->collection_name);
		$entry->addCategory($this->item_type->ascii_id,'http://daseproject.org/category/item_type',$this->item_type->name);
		$entry->addCategory('item','http://daseproject.org/category/entrytype');
		//todo: per latest docs, this will become plain /category/status
		if ($this->status) {
			$entry->addCategory($this->status,'http://daseproject.org/category/item/status');
		} else {
			$entry->addCategory('public','http://daseproject.org/category/item/status');
		}

		foreach ($type->getChildRelations() as $rel) {
			$uri = APP_ROOT.'/'.$rel->getBaseUri();
			$link = $entry->addLink($uri."/".$this->serial_number.'.atom','related','','',$rel->title);
			$link->setAttributeNS(Dase_Atom::$ns['d'],'d:count',(string) $rel->getChildCount($this->serial_number));
		}

		foreach ($type->getParentRelations() as $rel) {
			$ptype = $rel->getParent();
			$entry->addCategory($ptype->ascii_id,'http://daseproject.org/category/parent_item_type',$ptype->name);
			$entry->addLink($ptype->getBaseUrl().'/items.cats','http://daseproject.org/relation/item_type_items','application/atomcat+xml','',$ptype->name.' Items');
		}

		foreach ($this->getParents() as $pcat) {
			$entry->addCategory($pcat['term'],$pcat['scheme'],$pcat['label']);
			$entry->addLink($pcat['item_url'],'related','','',$pcat['label']);
		}

		/************** content *******************/
		$content = $this->getContents();
		if ($content && $content->text) {
			if ('application/json' == $content->type) {
				$entry->setExternalContent(APP_ROOT.'/item/'.$this->collection->ascii_id.'/'.$this->serial_number.'/content','application/json');
			} else {
				$entry->setContent($content->text,$content->type);
			}
		} 
		$thumb_url = $this->getMediaUrl('thumbnail');
		if ($thumb_url) {
			$entry->setThumbnail($thumb_url);	
		}
		foreach ($this->getMetadata() as $row) {
			/*
			$meta = $entry->addElement('d:'.$row['ascii_id'],$row['value_text'],$d);
			$meta->setAttribute('d:label',$row['attribute_name']);
			if ($row['is_on_list_display']) {
				$meta->setAttribute('d:display','yes');
			} else {
				$meta->setAttribute('d:display','no');
			}
			if ($row['is_public']) {
				$meta->setAttribute('d:public','yes');
			} else {
				$meta->setAttribute('d:public','no');
			}
			 */

			//an experiment in using atom:category:

			$meta = $entry->addCategory($c->ascii_id.'.'.$row['ascii_id'],'http://daseproject.org/category/metadata',$row['attribute_name'],$row['value_text']);

			if ($row['is_on_list_display']) {
				$meta->setAttributeNS(Dase_Atom::$ns['d'],'d:display','yes');
			} else {
				$meta->setAttributeNS(Dase_Atom::$ns['d'],'d:display','no');
			}
			if ($row['is_public']) {
				$meta->setAttributeNS(Dase_Atom::$ns['d'],'d:public','yes');
			} else {
				$meta->setAttributeNS(Dase_Atom::$ns['d'],'d:public','no');
			}
		}

		foreach ($this->getAdminMetadata() as $row) {
			$meta = $entry->addCategory($row['ascii_id'],'http://daseproject.org/category/admin_metadata',$row['attribute_name'],$row['value_text']);
		}
		/************** end content *******************/

		$enc = $this->getEnclosure();
		if ($enc) {
			$entry->addLink($this->getMediaUrl($enc->size),'enclosure',$enc->mime_type,$enc->file_size);
		}
		$media_link = $entry->addLink(APP_ROOT.'/media/'.$this->collection->ascii_id.'/'.$this->serial_number,'edit-media');

		foreach ($this->getMedia() as $med) {
			if ('thumbnail' == $med->size) {
				$media_thumbnail = $entry->addElement('media:thumbnail','',Dase_Atom::$ns['media']);
				$media_thumbnail->setAttribute('url',$med->getLink());
				$media_thumbnail->setAttribute('width',$med->width);
				$media_thumbnail->setAttribute('height',$med->height);
			} else {
				$media_content = $entry->addElement('media:content','',Dase_Atom::$ns['media']);
				$media_content->setAttribute('url',$med->getLink());
				if ($med->width && $med->height) {
					$media_content->setAttribute('width',$med->width);
					$media_content->setAttribute('height',$med->height);
				}
				$media_content->setAttribute('fileSize',$med->file_size);
				$media_content->setAttribute('type',$med->mime_type);
				$media_category = $media_content->appendChild($entry->dom->createElement('media:category'));
				$media_category->appendChild($entry->dom->createTextNode($med->size));
			}
		}
		return $entry;
	}

	function injectAtomFeedData(Dase_Atom_Feed $feed)
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
		$feed->setId($this->getBaseUrl());
		$feed->addLink(APP_ROOT.'/item/'.$c->ascii_id.'/'.$this->serial_number.'.atom','self' );
		$feed->setGenerator('DASe','http://daseproject.org','1.0');
		$feed->addAuthor('DASe (Digital Archive Services)','http://daseproject.org');
		return $feed;
	}

	function asAtom()
	{
		$feed = new Dase_Atom_Feed;
		$this->injectAtomFeedData($feed);
		$feed->setFeedType('item');
		//todo: this needs to be passed in?
		$feed->addCategory('browse',"http://daseproject.org/category/tag/type",'browse');
		$this->injectAtomEntryData($feed->addEntry());
		//add comments
		foreach ($this->getComments() as $comment) {
			$entry = $feed->addEntry('comment');
			$comment->injectAtomEntryData($entry);
		}
		return $feed->asXml();
	}

	function asAtomEntry()
	{
		$entry = new Dase_Atom_Entry;
		$this->injectAtomEntryData($entry);
		return $entry->asXml();
	}

	function mediaAsAtomFeed() 
	{
		$feed = new Dase_Atom_Feed;
		$this->injectAtomFeedData($feed);
		foreach ($this->getMedia('updated DESC') as $m) {
			$entry = $feed->addEntry();
			$m->injectAtomEntryData($entry);
		}
		return $feed->asXml();
	}	

	public function getBaseUrl() 
	{
		return APP_ROOT.'/item/'.$this->getCollection()->ascii_id.'/'.$this->serial_number;
	}

	public function getAtomPubServiceDoc() {
		$c = $this->getCollection();
		$app = new Dase_Atom_Service;
		$workspace = $app->addWorkspace($c->collection_name.' Item '.$this->serial_number.' Workspace');
		$media_coll = $workspace->addCollection(APP_ROOT.'/item/'.$c->ascii_id.'/'.$this->serial_number.'/media.atom',$c->collection_name.' Item '.$this->serial_number.' Media'); 
		foreach(Dase_Config::get('media_types') as $type) {
			$media_coll->addAccept($type);
		}
		$comments_coll = $workspace->addCollection(APP_ROOT.'/item/'.$c->ascii_id.'/'.$this->serial_number.'/comments.atom',$c->collection_name.' Item '.$this->serial_number.' Comments'); 
		$comments_coll->addAccept('text/plain');
		$comments_coll->addAccept('text/html');
		$comments_coll->addAccept('application/xhtml+xml');
		return $app->asXml();
	}

	public function asArray()
	{
		$j = array();
		$this->collection || $this->getCollection();
		$item_array['serial_number'] = $this->serial_number;
		$item_array['created'] = $this->created;
		$item_array['updated'] = $this->updated;
		$item_array['collection'] = $this->collection->ascii_id;
		//$item_array['collection']['ascii_id'] = $this->collection->ascii_id;
		//$item_array['collection']['name'] = $this->collection->collection_name;
		$item_array['metadata'] = array();
		foreach ($this->getMetadata() as $row) {
			//note: a simpler way would be to ALWAYS make value an array.
			//but this is a bit more concise (only an array if multiple) 
			if (isset($item_array['metadata'][$row['ascii_id']])) {
				if (is_array($item_array['metadata'][$row['ascii_id']])) {
					$item_array['metadata'][$row['ascii_id']][] = $row['value_text'];
				} else {
					$orig = $item_array['metadata'][$row['ascii_id']];
					$item_array['metadata'][$row['ascii_id']] = array();;
					$item_array['metadata'][$row['ascii_id']][] = $orig;
					$item_array['metadata'][$row['ascii_id']][] = $row['value_text'];
				}
			} else {
				$item_array['metadata'][$row['ascii_id']] = $row['value_text'];
			}
		}
		$item_array['media'] = array();
		foreach ($this->getMedia() as $m) {
			/*
			foreach ($m as $k => $v) {
				if (!in_array($k,array('p_collection_ascii_id','p_serial_number'))) {
					$media_file[$k] = $v;
				}
			}
			$item_array['media'][] = $media_file;
		 */
		$item_array['media'][$m->size] = 
			APP_ROOT.'/media/'.
			$this->collection->ascii_id.'/'.$m->size.'/'.$m->filename;
		}
		$c = $this->getContents();
		if ($c) {
			$content[$c->id]['updated'] = $c->updated;
			$content[$c->id]['eid'] = $c->updated_by_eid;
			$content[$c->id]['text'] = $c->text;
			$item_array['content'][] = $content;
		}

		return $item_array;
	}

	public function asJson()
	{
		return Dase_Json::get($this->asArray(),true);
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

	/** pass in true to get all versions */
	public function getContents($get_all=false)
	{
		$contents = new Dase_DBO_Content;
		$contents->item_id = $this->id;
		$contents->orderBy('updated DESC');
		if ($get_all) {
			return $contents->find();
		} else {
			return $contents->findOne();
		}
	}

	public function getCommentsCount()
	{
		$comments = new Dase_DBO_Comment;
		$comments->item_id = $this->id;
		return $comments->findCount();
	}

	public function getCommentsUpdated()
	{
		$comments = new Dase_DBO_Comment;
		$comments->item_id = $this->id;
		$comments->orderBy('updated DESC');
		$latest = $comments->findOne();
		return $latest->updated;
	}

	public function getComments($eid='')
	{
		$comments = new Dase_DBO_Comment;
		$comments->item_id = $this->id;
		if ($eid) {
			$comments->updated_by_eid = $eid;
		}
		return $comments->find();
	}

	public function getCommentsJson($eid='')
	{
		$comments = '';
		foreach ($this->getComments($eid) as $c_obj) {
			$c['id'] = $c_obj->id;
			$c['updated'] = $c_obj->updated;
			$c['updated'] = date('D M j, Y \a\t g:ia',strtotime($c_obj->updated));
			$c['eid'] = $c_obj->updated_by_eid;
			$c['text'] = $c_obj->text;
			$comments[] = $c;
		}
		return Dase_Json::get($comments);
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

	public function setContent($text,$eid,$type="text")
	{
		$c = $this->getCollection();
		$content = new Dase_DBO_Content;
		$content->item_id = $this->id;
		//todo: security! filter input....
		$content->text = $text;
		$content->type = $type;
		$content->p_collection_ascii_id = $c->ascii_id;
		$content->p_serial_number = $this->serial_number;
		$content->updated = date(DATE_ATOM);
		$content->updated_by_eid = $eid;
		return $content->insert();
	}

	public function addComment($text,$eid)
	{
		$c = $this->getCollection();
		$note = new Dase_DBO_Comment;
		$note->item_id = $this->id;
		//todo: security! filter input....
		$note->text = $text;
		$note->p_collection_ascii_id = $c->ascii_id;
		$note->p_serial_number = $this->serial_number;
		$note->updated = date(DATE_ATOM);
		$note->updated_by_eid = $eid;
		$note->insert();
	}

	public function getTags()
	{
		$tags = array();
		$tag_item = new Dase_DBO_TagItem;
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

	public static function sortIdArray($sort,$item_ids)
	{
		$test_att = new Dase_DBO_Attribute;
		$test_att->ascii_id = $sort;
		if (!$test_att->findOne()) {
			return $item_ids;
		}
		$prefix = Dase_Config::get('table_prefix');
		$db = Dase_DB::get();
		$sql = "
			SELECT v.value_text
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE v.item_id = ?
			AND v.attribute_id = a.id
			AND a.ascii_id = ?
			LIMIT 1
			";
		$sth = $db->prepare($sql);
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
	public function expireCaches()
	{
		// attributes json (includes tallies)
		$c = $this->getCollection();
		$cache_id = "get|collection/".$c->ascii_id."/attributes/public/tallies|json|cache_buster=stripped&format=json";
		Dase_Cache::get($cache_id)->expire();
	
	}
}
