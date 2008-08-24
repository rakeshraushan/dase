<?php

require_once 'Dase/DBO/Autogen/Item.php';

class Dase_DBO_Item extends Dase_DBO_Autogen_Item 
{

	public $collection = null;
	public $item_type;
	public $media = array();
	public $values = array();

	const STATUS_PUBLIC = 'public';
	const STATUS_DRAFT = 'draft';
	const STATUS_DELETE = 'delete';
	const STATUS_ARCHIVE = 'archive';

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
		$db = Dase_DB::get();
		$sql = "
			DELETE
			FROM search_table 
			WHERE item_id = $this->id
			";
		$db->query($sql);
		$sql = "
			DELETE
			FROM admin_search_table 
			WHERE item_id = $this->id
			";
		$db->query($sql);
	}

	public function buildSearchIndex()
	{
		$db = Dase_DB::get();
		//todo: make sure item->id is an integer
		$sql = "
			DELETE
			FROM search_table 
			WHERE item_id = $this->id
			";
		$db->query($sql);
		$sql = "
			DELETE
			FROM admin_search_table 
			WHERE item_id = $this->id
			";
		$db->query($sql);

		//search table
		$composite_value_text = '';
		$db = Dase_DB::get();
		$sql = "
			SELECT value_text
			FROM value
			WHERE item_id = $this->id
			AND value.attribute_id in (SELECT id FROM attribute where in_basic_search = true)
			";
		$st = $db->prepare($sql);
		$st->execute();
		while ($value_text = $st->fetchColumn()) {
			$composite_value_text .= $value_text . " ";
		}
		foreach ($this->getContents() as $c) {
			$composite_value_text .= $c->text . " ";
		}
		$this->collection || $this->getCollection();
		$search_table = new Dase_DBO_SearchTable;
		$search_table->value_text = $composite_value_text;
		$search_table->item_id = $this->id;
		$search_table->collection_id = $this->collection_id;
		$search_table->collection_ascii_id = $this->collection->ascii_id;
		$search_table->updated = date(DATE_ATOM);
		if ($composite_value_text) {
			$search_table->insert();
		}

		//admin search table
		$composite_value_text = '';
		$sql = "
			SELECT value_text
			FROM value
			WHERE item_id = $this->id
			";
		$st = $db->prepare($sql);
		$st->execute();
		while ($value_text = $st->fetchColumn()) {
			$composite_value_text .= $value_text . " ";
		}
		foreach ($this->getContents() as $c) {
			$composite_value_text .= $c->text . " ";
		}
		$search_table = new Dase_DBO_AdminSearchTable;
		$search_table->value_text = $composite_value_text;
		$search_table->item_id = $this->id;
		$search_table->collection_id = $this->collection_id;
		$search_table->collection_ascii_id = $this->collection->ascii_id;
		$search_table->updated = date(DATE_ATOM);
		$search_table->insert();
		$this->updated = date(DATE_ATOM);
		$this->update();
		Dase_Log::info("built indexes for " . $this->serial_number);
	}

	public function asMicroformat() 
	{
		$div = new SimpleXMLElement('<div/>');
		$div->addAttribute('class',$this->serial_number);
		$img = $div->addChild('img');
		$img->addAttribute('src',$this->getMediaUrl('thumbnail'));
		$img->addAttribute('class','thumbnail');
		$contents = $div->addChild('ul');
		$contents->addAttribute('class','notes');
		foreach ($this->getContents() as $cont) {
			$content_note = $contents->addChild('li',htmlspecialchars($cont->text));
			$content_note->addAttribute('class',$cont->updated_by_eid .' '.$cont->updated);
		}
		$keyvals = $div->addChild('dl');
		$keyvals->addAttribute('class','metadata');
		foreach ($this->getMetadata() as $row) {
			//php dom will escape text for me here....
			$attname = $keyvals->addChild('dt',$row['attribute_name']);
			$attname->addAttribute('class',$row['ascii_id']);
			$val = $keyvals->addChild('dd',htmlspecialchars($row['value_text']));
		}
		$media = $div->addChild('ul');

		foreach ($this->getMedia() as $med) {
			$media_file = $media->addChild('li');
			$media_file->addAttribute('class',$med->size);
			$media_link = $media_file->addChild('a',$med->filename);
			$media_link->addAttribute('href',$med->getLink());
			$media_link->addAttribute('type',$med->mime_type);
			$media_link->addAttribute('title',$med->size);
			//$media_content->setAttribute('width',$med->width);
			//$media_content->setAttribute('height',$med->height);
			//$media_content->setAttribute('fileSize',$med->file_size);
		}
		return $div->asXml();
	}

	public function getMetadata($att_ascii_id = '')
	{
		$metadata = array();
		$bound_params = array();
		$sql = "
			SELECT a.ascii_id, a.attribute_name,v.value_text,a.collection_id, v.id
			FROM attribute a, value v
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
		$metadata = array();
		$bound_params = array();
		$sql = "
			SELECT a.id as att_id,a.ascii_id,a.attribute_name,a.html_input_type,v.value_text
			FROM attribute a, value v
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
		$sql = "
			SELECT v.value_text
			FROM attribute a, value v
			WHERE v.item_id = ?
			AND v.attribute_id = a.id
			AND a.ascii_id = ?
			LIMIT 1
			";
		$res = Dase_DBO::query($sql,array($this->id,$att_ascii_id),true)->fetch();
		if ($res) {
			return $res->value_text;
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
		$this->collection = $c;
		return $c;
	}

	public function getItemType()
	{
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
		$this->collection || $this->getCollection();
		$m = new Dase_DBO_MediaFile;
		//$m->item_id = $this->id;
		$m->p_collection_ascii_id = $this->collection->ascii_id;
		$m->p_serial_number = $this->serial_number;
		$m->orderBy('width');
		return $m->find();
	}

	public function getEnclosure()
	{
		$this->collection || $this->getCollection();
		$m = new Dase_DBO_MediaFile;
		//$m->item_id = $this->id;
		$m->p_collection_ascii_id = $this->collection_ascii_id;
		$m->p_serial_number = $this->serial_number;
		$m->orderBy('file_size DESC');
		return $m->findOne();
	}

	public function getMediaUrl($size)
	{  //size really means type here
		$this->collection || $this->getCollection();
		$m = new Dase_DBO_MediaFile;
		//$m->item_id = $this->id;
		$m->p_collection_ascii_id = $this->collection_ascii_id;
		$m->p_serial_number = $this->serial_number;
		$m->size = $size;
		$m->findOne();
		$url = APP_ROOT . "/media/{$this->collection->ascii_id}/$size/$m->filename";
		return $url;
	}

	function getMediaCount()
	{
		$this->collection || $this->getCollection();
		$db = Dase_DB::get();
		$sql = "
			SELECT count(*) 
			FROM media_file
			WHERE item_id = $this->id
			";
		$sql = "
			SELECT count(*) 
			FROM media_file
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
		$this->collection || $this->getCollection();
		$att = new Dase_DBO_Attribute;
		$att->ascii_id = $att_ascii_id;
		//allows for admin metadata, att_ascii for which
		//always begins 'admin_'
		if (false === strpos($att_ascii_id,'admin_')) {
			$att->collection_id = $this->collection_id;
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
			return false;
		}
	}

	function deleteValues()
	{
		//should sanity check and archive values
		$v = new Dase_DBO_Value;
		$v->item_id = $this->id;
		foreach ($v->find() as $doomed) {
			$doomed->delete();
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
		$this->collection || $this->getCollection();
		$graveyard = Dase_Config::get('graveyard');
		$filename = $this->collection->ascii_id .'_'.$this->serial_number;
		file_put_contents($graveyard.'/'.$filename,$this->asAtom());
		
		$this->deleteMedia();
		$this->deleteValues();
		$this->deleteSearchIndexes();
		$this->deleteContent();
		$this->delete();
	}

	function deleteContent()
	{
		$co = new Dase_DBO_Content;
		$co->item_id = $this->id;
		foreach ($co->find() as $doomed) {
			$doomed->delete();
		}
	}

	function deleteMedia()
	{
		$mf = new Dase_DBO_MediaFile;
		$mf->item_id = $this->id;
		foreach ($mf->find() as $doomed) {
			$doomed->expunge();
		}
	}

	function getTitle()
	{
		$sql = "
			SELECT v.value_text 
			FROM attribute a, value v
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
		$sql = "
			SELECT v.value_text 
			FROM attribute a, value v
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
		$sql = "
			SELECT v.value_text 
			FROM attribute a, value v
			WHERE a.id = v.attribute_id
			AND a.ascii_id = 'rights'
			AND v.item_id = ? 
			";
		return Dase_DBO::query($sql,array($this->id))->fetchColumn();
	}

	function injectAtomEntryData(Dase_Atom_Entry $entry)
	{
		$d = Dase_Atom::$ns['d'];
		$this->collection || $this->getCollection();
		$this->item_type || $this->getItemType();
		//I think this can be simplified when DASe 1.0 is retired
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
		//for AtomPub -- is this correct??
		$entry->addLink(APP_ROOT.'/item/'.$this->collection->ascii_id.'/'.$this->serial_number.'.atom','edit' );
		$entry->addLink(APP_ROOT.'/item/'.$this->collection->ascii_id.'/'.$this->serial_number.'/media','http://daseproject.org/relation/media-collection' );
		$entry->setUpdated($updated);
		$entry->setPublished($created);
		$entry->setId($this->getBaseUrl());
		$entry->addCategory($this->collection->ascii_id,'http://daseproject.org/category/collection',$this->collection->collection_name);
		$entry->addCategory($this->item_type->ascii_id,'http://daseproject.org/category/item/type',$this->item_type->label);
		$entry->addCategory($this->serial_number,'http://daseproject.org/category/item/serial_number');
		$entry->addCategory('item','http://daseproject.org/category/entrytype');
		if ($this->status) {
			$entry->addCategory($this->status,'http://daseproject.org/category/item/status');
		} else {
			$entry->addCategory('public','http://daseproject.org/category/item/status');
		}
		$entry->addLink($this->getBaseUrl(),'alternate' );

		//switch to the simple xml interface here
		$div = simplexml_import_dom($entry->setContent());
		$img = $div->addChild('img');
		$img->addAttribute('src',$this->getMediaUrl('thumbnail'));
		$img->addAttribute('class','thumbnail');
		//$div->addChild('p',htmlspecialchars($this->getDescription()));
		$contents = $div->addChild('ul');
		foreach ($this->getContents() as $cont) {
			$content_note = $contents->addChild('li',htmlspecialchars($cont->text));
			$content_note->addAttribute('eid',$cont->updated_by_eid);
			$content_note->addAttribute('date',$cont->updated);
		}
		$keyvals = $div->addChild('dl');
		$keyvals->addAttribute('class','metadata');
		foreach ($this->getMetadata() as $row) {
			//php dom will escape text for me here....
			$attname = $keyvals->addChild('dt',$row['attribute_name']);
			$val = $keyvals->addChild('dd',htmlspecialchars($row['value_text']));
			//$val->addAttribute('class',$row['ascii_id']);
			$meta = $entry->addElement('d:'.$row['ascii_id'],$row['value_text'],$d);
			$meta->setAttribute('d:label',$row['attribute_name']);
		}

		//much of the following can go in Dase_Atom_Entry
		$media_group = $entry->addElement('media:group',null,Dase_Atom::$ns['media']);
		foreach ($this->getMedia() as $med) {
			if ($med->size == 'thumbnail') {
				//$media_thumbnail = $entry->addElement('media:thumbnail',null,Dase_Atom::$ns['media']);
				$media_thumbnail = $media_group->appendChild($entry->dom->createElementNS(Dase_Atom::$ns['media'],'thumbnail'));
				$media_thumbnail->setAttribute('url',$med->getLink());
				$media_thumbnail->setAttribute('width',$med->width);
				$media_thumbnail->setAttribute('height',$med->height);
			}
		   	if ($med->size == 'viewitem') {
				//$media_viewitem = $entry->addElement('media:content',null,Dase_Atom::$ns['media']);
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
				$media_content->setAttribute('width',$med->width);
				$media_content->setAttribute('height',$med->height);
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
		$this->collection || $this->getCollection();
		if (is_numeric($this->updated)) {
			$updated = date(DATE_ATOM,$this->updated);
		} else {
			$updated = $this->updated;
		}
		$feed->setUpdated($updated);
		$feed->setTitle($this->getTitle());
		$feed->setId($this->getBaseUrl());
		$feed->addLink(APP_ROOT.'/item/'.$this->collection->ascii_id.'/'.$this->serial_number.'.atom','self' );
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
		return $feed->asXml();
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
		$this->collection || $this->getCollection();
		$app = new Dase_Atom_Service;
		$workspace = $app->addWorkspace($this->collection->collection_name.' Item '.$this->serial_number.' Workspace');
		$media_coll = $workspace->addCollection(APP_ROOT.'/item/'.$this->collection->ascii_id.'/'.$this->serial_number.'/media',$this->collection->collection_name.' Item '.$this->serial_number.' Media'); 
		foreach(Dase_Config::get('media_types') as $type) {
			$media_coll->addAccept($type);
		}
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

	public function getContents()
	{
		$contents = new Dase_DBO_Content;
		$contents->item_id = $this->id;
		return $contents->find();
	}

	public function getContentsJson()
	{
		$content = '';
		foreach ($this->getContents() as $c_obj) {
			$c['id'] = $c_obj->id;
			$c['updated'] = $c_obj->updated;
			$c['eid'] = $c_obj->updated_by_eid;
			$c['text'] = $c_obj->text;
			$content[] = $c;
		}
		return Dase_Json::get($content);
	}

	public function addContent($text,$eid)
	{
		$this->collection || $this->getCollection();
		$note = new Dase_DBO_Content;
		$note->item_id = $this->id;
		//todo: security! filter input....
		$note->text = $text;
		$note->p_collection_ascii_id = $this->collection->ascii_id;
		$note->p_serial_number = $this->serial_number;
		$note->updated = date(DATE_ATOM);
		$note->updated_by_eid = $eid;
		$note->insert();
	}
}
