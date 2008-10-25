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
		Dase_Log::info("built indexes for " . $this->serial_number);
	}

	public function getMetadata($att_ascii_id = '')
	{
		$prefix = Dase_Config::get('table_prefix');
		$metadata = array();
		$bound_params = array();
		$sql = "
			SELECT a.ascii_id, a.attribute_name,v.value_text,a.collection_id, v.id
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE v.item_id = ?
			AND v.attribute_id = a.id
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

	public function getEditFormJson()
	{
		$prefix = Dase_Config::get('table_prefix');
		$metadata = array();
		$bound_params = array();
		$sql = "
			SELECT a.id as att_id,a.ascii_id,a.attribute_name,a.html_input_type,v.value_text
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE v.item_id = ?
			AND v.attribute_id = a.id
			ORDER BY a.sort_order,v.value_text
			";
		$bound_params[] = $this->id;
		$st = Dase_DBO::query($sql,$bound_params);
		while ($row = $st->fetch()) {
			$set = array();
			if (in_array($row['html_input_type'],array('radio','checkbox','select','text_with_menu'))) {
				$att = new Dase_DBO_Attribute;
				$att->load($row['att_id']);
				$set['values'] = $att->getFormValues();
			}
			$set['att_ascii_id'] = $row['ascii_id'];
			$set['attribute_name'] = $row['attribute_name'];
			$set['html_input_type'] = $row['html_input_type'];
			$set['value_text'] = $row['value_text'];
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

	public function getMedia()
	{
		Dase_Log::debug("getting media for " . $this->id);
		$c = $this->getCollection();
		$m = new Dase_DBO_MediaFile;
		$m->p_collection_ascii_id = $c->ascii_id;
		$m->p_serial_number = $this->serial_number;
		$m->orderBy('file_size');
		return $m->find();
	}

	public function getEnclosure()
	{
		$c = $this->getCollection();
		$m = new Dase_DBO_MediaFile;
		$m->p_collection_ascii_id = $c->ascii_id;
		$m->p_serial_number = $this->serial_number;
		$m->orderBy('file_size DESC');
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

	function setType($type_ascii_id)
	{
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

	function setValue($att_ascii_id,$value_text)
	{
		//todo: set value revision history as well
		$c = $this->getCollection();
		$att = new Dase_DBO_Attribute;
		$att->ascii_id = $att_ascii_id;
		//allows for admin metadata, att_ascii for which
		//always begins 'admin_'
		if (false === strpos($att_ascii_id,'admin_')) {
			$att->collection_id = $c->id;
		}
		if ($att->findOne()) {
			//does NOT overwrite (just adds k-v pair)
			//and does NOT create attribute if not found
			$v = new Dase_DBO_Value;
			$v->item_id = $this->id;
			$v->attribute_id = $att->id;
			$v->value_text = $value_text;
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
		$this->delete();
		$c->updateItemCount();
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
		return Dase_DBO::query($sql,array($this->id))->fetchColumn();
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
		//for AtomPub
		$entry->setEdited($updated);
		$entry->addLink(APP_ROOT.'/item/'.$this->collection->ascii_id.'/'.$this->serial_number,'alternate');
		$entry->addLink(APP_ROOT.'/item/'.$this->collection->ascii_id.'/'.$this->serial_number.'.atom','edit' );
		$entry->addLink(APP_ROOT.'/item/'.$this->collection->ascii_id.'/'.$this->serial_number.'/media','http://daseproject.org/relation/media-collection' );

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
		$entry->addCategory($this->item_type->ascii_id,'http://daseproject.org/category/item/type',$this->item_type->label);
		$entry->addCategory('item','http://daseproject.org/category/entrytype');
		if ($this->status) {
			$entry->addCategory($this->status,'http://daseproject.org/category/item/status');
		} else {
			$entry->addCategory('public','http://daseproject.org/category/item/status');
		}

		/************** content *******************/
		$content = $this->getContents();
		if ($content && $content->text) {
			$entry->setContent($content->text,$content->type);
		} else {
			//switch to the simple xml interface here
			$div = simplexml_import_dom($entry->setContent());
			$thumb_url = $this->getMediaUrl('thumbnail');
			if ($thumb_url) {
				$img = $div->addChild('img');
				$img->addAttribute('src',$thumb_url);
				$img->addAttribute('class','thumbnail');
			}
		}
		//	$keyvals = $div->addChild('dl');
		//	$keyvals->addAttribute('class','metadata');
		foreach ($this->getMetadata() as $row) {
			//php dom will escape text for me here (no, it won't!!)....
			//$attname = $keyvals->addChild('dt',htmlspecialchars($row['attribute_name']));
			//$val = $keyvals->addChild('dd',htmlspecialchars($row['value_text']));
			//$val->addAttribute('class',$row['ascii_id']);
			$meta = $entry->addElement('d:'.$row['ascii_id'],$row['value_text'],$d);
			$meta->setAttribute('d:label',$row['attribute_name']);
		}
		/************** end content *******************/

		//much of the following can go in Dase_Atom_Entry
		$media_group = $entry->addElement('media:group',null,Dase_Atom::$ns['media']);

		foreach ($this->getMedia() as $med) {
			if ($med->size == 'thumbnail') {
				$media_thumbnail = $media_group->appendChild($entry->dom->createElementNS(Dase_Atom::$ns['media'],'thumbnail'));
				$media_thumbnail->setAttribute('url',$med->getLink());
				$media_thumbnail->setAttribute('width',$med->width);
				$media_thumbnail->setAttribute('height',$med->height);
			}
		   	if ($med->size == 'viewitem') {
				$media_viewitem = $media_group->appendChild($entry->dom->createElementNS(Dase_Atom::$ns['media'],'content'));
				$media_viewitem->setAttribute('url',$med->getLink());
				$media_viewitem->setAttribute('width',$med->width);
				$media_viewitem->setAttribute('height',$med->height);
				$media_viewitem->setAttribute('fileSize',$med->file_size);
				$media_viewitem->setAttribute('type',$med->mime_type);
				$media_category = $media_viewitem->appendChild($entry->dom->createElement('media:category'));
				$media_category->appendChild($entry->dom->createTextNode($med->size));
			}
			if ($med->size != 'thumbnail' && $med->size != 'viewitem') {
				$media_content = $media_group->appendChild($entry->dom->createElementNS(Dase_Atom::$ns['media'],'content'));
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
		$enc = $this->getEnclosure();
		if ($enc) {
			$entry->addLink($this->getMediaUrl($enc->size),'enclosure',$enc->mime_type,$enc->file_size);
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
		foreach ($this->getMedia() as $m) {
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
		$media_coll = $workspace->addCollection(APP_ROOT.'/item/'.$c->ascii_id.'/'.$this->serial_number.'/media',$c->collection_name.' Item '.$this->serial_number.' Media'); 
		foreach(Dase_Config::get('media_types') as $type) {
			$media_coll->addAccept($type);
		}
		$comments_coll = $workspace->addCollection(APP_ROOT.'/item/'.$c->ascii_id.'/'.$this->serial_number.'/comments',$c->collection_name.' Item '.$this->serial_number.' Comments'); 
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
		foreach ($this->getContents() as $c) {
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

	public function setContent($text,$eid)
	{
		$c = $this->getCollection();
		$content = new Dase_DBO_Content;
		$content->item_id = $this->id;
		//todo: security! filter input....
		$content->text = $text;
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
