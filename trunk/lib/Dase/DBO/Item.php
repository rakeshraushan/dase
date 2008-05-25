<?php

require_once 'Dase/DBO/Autogen/Item.php';

class Dase_DBO_Item extends Dase_DBO_Autogen_Item 
{

	public $collection_ascii_id = '';
	public $collection_name = '';
	public $collection = null;
	public $item_type;
	public $item_type_ascii = '';
	public $item_type_label = '';
	public $media;
	public $status = '';
	public $thumbnail;
	public $thumbnail_url = '';
	public $values = array();
	public $viewitem;
	public $viewitem_url = '';

	public static function create($collection_ascii_id,$serial_number= null)
	{
		$c = Dase_DBO_Collection::get($collection_ascii_id);
		return $c->createNewItem($serial_number);
	}

	public static function get($collection_ascii_id,$serial_number)
	{
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
		$search_table = new Dase_DBO_SearchTable;
		$search_table->value_text = $composite_value_text;
		$search_table->item_id = $this->id;
		$search_table->collection_id = $this->collection_id;
		$search_table->updated = date(DATE_ATOM);
		$search_table->insert();

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
		$search_table = new Dase_DBO_AdminSearchTable;
		$search_table->value_text = $composite_value_text;
		$search_table->item_id = $this->id;
		$search_table->collection_id = $this->collection_id;
		$search_table->updated = date(DATE_ATOM);
		$search_table->insert();
		$this->updated = date(DATE_ATOM);
		$this->update();
		return "built indexes for " . $this->serial_number . "\n";
	}

	public function getMetadata($att_ascii_id = '')
	{
		$metadata = array();
		$bound_params = array();
		$db = Dase_DB::get();
		$sql = "
			SELECT a.ascii_id, a.attribute_name,v.value_text,a.collection_id, v.id
			FROM attribute a, value v
			WHERE v.item_id = ?
			AND v.attribute_id = a.id
			ORDER BY a.sort_order,v.value_text
			";
		$bound_params[] = $this->id;
		if ($att_ascii_id) {
			$sql .= "
				AND a.ascii_id = ?
				";
			$bound_params[] = $att_ascii_id;
		}
		$st = $db->prepare($sql);
		$st->execute($bound_params);
		while ($row = $st->fetch()) {
			$metadata[] = $row;
		}
		return $metadata;
	}

	public function getChildren()
	{

		//WORK ON THIS!!!!!!!!
		$sql = "
			SELECT i.id 
			FROM attribute a, attribute_item_type ai, item i, item_type_relation r
			WHERE a.item_id = $this->id
			AND a.id = ai.attribute_id
			AND ai.is_identifier = 't'	
			AND ai.item_type_id = $this->item_type_id
			AND r.parent_item_type_id = $this->item_type_id
			AND i.item_type_id = r.item_type_id
			";


	}

	public function getAttVal($att_ascii_id)
	{
		//NOTE: repeat attributes will only get ONE value!!!!
		$values = array();
		$this->collection || $this->getCollection();
		$val = new Dase_DBO_Value;
		$val->item_id = $this->id;
		$val->attribute_id = Dase_DBO_Attribute::get($this->collection->ascii_id,$att_ascii_id)->id;
		$val->findOne();
		return $val->value_text;
	}

	public function getCollection()
	{
		$c = new Dase_DBO_Collection;
		$c->load($this->collection_id);
		$this->collection = $c;
		$this->collection_ascii_id = $c->ascii_id;
		$this->collection_name = $c->collection_name;
		//$this->coll = substr($c->ascii_idi,0,-11);
		return $c;
	}

	public function getThumbnail()
	{
		$this->collection || $this->getCollection();
		$m = new Dase_DBO_MediaFile;
		$m->item_id = $this->id;
		$m->size = 'thumbnail';
		$this->thumbnail = $m->findOne();
		if ($this->thumbnail) {
			$this->thumbnail_url = APP_ROOT . "/media/{$this->collection->ascii_id}/thumbnail/$m->filename";
			return $this->thumbnail;
		}
	}

	public function getViewitem()
	{
		$this->collection || $this->getCollection();
		$m = new Dase_DBO_MediaFile;
		$m->item_id = $this->id;
		$m->size = 'viewitem';
		$this->viewitem = $m->findOne();
		if ($this->viewitem) {
			$this->viewitem_url = APP_ROOT . "/media/{$this->collection->ascii_id}/viewitem/$m->filename";
			return $this->viewitem;
		}
	}

	public function getItemType()
	{
		$type = new Dase_DBO_ItemType;
		if ($this->item_type_id) {
			$type->load($this->item_type_id);
			$this->item_type = $type->findOne();
			if ($this->item_type) {
				$this->item_type_ascii = $type->ascii_id;
				$this->item_type_label = $type->name;
				return $this->item_type;
			} 
		}
		return false;
	}

	public function getItemStatus()
	{
		$status = new Dase_DBO_ItemStatus;
		$status->item_id = $this->id;
		if ($status->findOne()) {
			$this->item_status = $status->status;
		}
		return $this->item_status;
	}

	public function getMedia()
	{
		$this->collection || $this->getCollection();
		$m = new Dase_DBO_MediaFile;
		$m->item_id = $this->id;
		$m->orderBy('width');
		return $m->find();
	}

	public function getMediaUrl($size)
	{  //size really means type here
		$this->collection || $this->getCollection();
		$m = new Dase_DBO_MediaFile;
		$m->item_id = $this->id;
		$m->size = $size;
		$this->media = $m->findOne();
		$url = APP_ROOT . "/media/{$this->collection->ascii_id}/$size/$m->filename";
		return $url;
	}

	function getMediaCount()
	{
		$db = Dase_DB::get();
		$sql = "
			SELECT count(*) 
			FROM media_file
			WHERE item_id = $this->id
			";
		return $db->query($sql)->fetchColumn();
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
		$att = new Dase_DBO_Attribute;
		$att->ascii_id = $att_ascii_id;
		//allows for admin metadata, att_ascii for which
		//always begins 'admin_'
		if (false === strpos($att_ascii_id,'admin_')) {
			$att->collection_id = $this->collection_id;
		}
		if ($att->findOne()) {
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
		$graveyard = Dase::getConfig('graveyard');
		$filename = $this->collection->ascii_id .'_'.$this->serial_number;
		file_put_contents($graveyard.'/'.$filename,$this->asAtom());
		
		$this->deleteMedia();
		$this->deleteValues();
		$this->deleteSearchIndexes();
		$this->delete();
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
		$db = Dase_DB::get();
		$sql = "
			SELECT v.value_text 
			FROM attribute a, value v
			WHERE a.id = v.attribute_id
			AND a.ascii_id = 'title'
			AND v.item_id = $this->id
			";
		$st = $db->query($sql);
		$title = $st->fetchColumn();
		if (!$title) {
			$title = $this->serial_number;
		}
		return $title;
	}

	function getDescription()
	{
		$db = Dase_DB::get();
		$sql = "
			SELECT v.value_text 
			FROM attribute a, value v
			WHERE a.id = v.attribute_id
			AND a.ascii_id = 'description'
			AND v.item_id = $this->id
			";
		$st = $db->query($sql);
		$description = $st->fetchColumn();
		if (!$description) {
			$description = $this->getTitle();
		}
		return $description;
	}

	function injectAppEntryData(Dase_Atom_Entry $entry)
	{
		$app = "http://www.w3.org/2007/app";
		$this->collection || $this->getCollection();
		$this->item_type || $this->getItemType();
		if (is_numeric($this->updated)) {
			$updated = date(DATE_ATOM,$this->updated);
		} else {
			$updated = $this->updated;
		}
		$entry->setTitle($this->getTitle());
		$entry->setUpdated($updated);
		$entry->setEdited($updated);
		$entry->setSummary('');
		$entry->setId($this->getBaseUrl());
		$entry->addCategory($this->collection_ascii_id,'http://daseproject.org/category/collection',$this->collection_name);
		if ($this->item_type) {
			$entry->addCategory($this->item_type_ascii,'http://daseproject.org/category/item_type',$this->item_type_label);
		}
		$entry->addLink($this->getBaseUrl(),'alternate' );
		$entry->addLink(APP_ROOT.'/edit/'.$this->collection->ascii_id.'/'.$this->serial_number,'edit' );
		return $entry;
	}

	function injectAtomEntryData(Dase_Atom_Entry $entry)
	{
		$d = "http://daseproject.org/ns/1.0";
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
		$entry->setUpdated($updated);
		$entry->setPublished($created);
		$entry->setId($this->getBaseUrl());
		$entry->addCategory($this->collection_ascii_id,'http://daseproject.org/category/collection',$this->collection_name);
		if (!$this->item_type) {
			$this->item_type_ascii = 'default';
			$this->item_type_label = 'default';
		}
		$entry->addCategory($this->item_type_ascii,'http://daseproject.org/category/item_type',$this->item_type_label);
		$entry->addLink($this->getBaseUrl(),'alternate' );
		//switch to the simple xml interface here
		$div = simplexml_import_dom($entry->setContent());
		$this->thumbnail || $this->getThumbnail();
		if ($this->thumbnail) {
			$img = $div->addChild('img');
			$img->addAttribute('src',$this->thumbnail_url);
			$img->addAttribute('class','thumbnail');
		}
		$div->addChild('p',htmlspecialchars($this->getDescription()));

		foreach ($this->getMetadata() as $row) {
			//php dom will escape text for me here....
			$meta = $entry->addElement('d:'.$row['ascii_id'],$row['value_text'],$d);
			$meta->setAttribute('d:label',$row['attribute_name']);
		}

		$entry->addElement('d:item_id',$this->id,$d)->setAttribute('d:label','Item ID');
		$entry->addElement('d:serial_number',$this->serial_number,$d)->setAttribute('d:label','Serial Number');
		$entry->addElement('d:item_type',$this->item_type_ascii,$d)->setAttribute('d:label','Item Type');

		foreach ($this->getMedia() as $med) {
			$link = $entry->addLink($med->getLink(),'http://daseproject.org/relation/media');
			$link->setAttribute('d:height',$med->height);
			$link->setAttribute('d:width',$med->width);
			$link->setAttribute('type',$med->mime_type);
			$link->setAttribute('length',$med->file_size);
			$link->setAttribute('title',$med->size);
		}
		if ($this->xhtml_content) {
			$content_sx = new SimpleXMLElement($this->xhtml_content);	
			//from http://us.php.net/manual/en/function.simplexml-element-addChild.php
			$node1 = dom_import_simplexml($div);
			$dom_sxe = dom_import_simplexml($content_sx);
			$node2 = $node1->ownerDocument->importNode($dom_sxe, true);
			$node1->appendChild($node2);
		} elseif ($this->text_content) {
			$text = $div->addChild('div',htmlspecialchars($content));
			$text->addAttribute('class','itemContent');
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
		$feed->addLink(APP_ROOT.'/atom/collection/'.$this->collection->ascii_id.'/'.$this->serial_number,'self' );
		$feed->setGenerator('DASe','http://daseproject.org','1.0');
		$feed->addAuthor('DASe (Digital Archive Services)','http://daseproject.org');
		return $feed;
	}

	function asAtom()
	{
		$feed = new Dase_Atom_Feed;
		$this->injectAtomFeedData($feed);
		$feed->setFeedType('item');
		$this->injectAtomEntryData($feed->addEntry());
		return $feed->asXml();
	}

	function asAppMember()
	{
		$d = "http://daseproject.org/ns/1.0";
		$member = new Dase_Atom_Entry_MemberItem;
		$member->setEdited($this->updated);
		$this->injectAtomEntryData($member);
		$member->addLink(APP_ROOT.'/edit/'.$this->collection->ascii_id.'/'.$this->serial_number,'edit');
		$link = $member->addLink(APP_ROOT.'/edit/'.$this->collection->ascii_id.'/'.$this->serial_number.'/media','http://daseproject.org/relation/media_collection');
		//$elem = $member->addElement('d:feedLink',null,'http://schemas.google.com/g/2005');
		//$elem->setAttribute('rel','media');
		//$elem->setAttribute('href',APP_ROOT.'/edit/'.$this->collection->ascii_id.'/'.$this->serial_number.'/media');
		return $member->asXml();
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
		$this->collection || $this->getCollection();
		return $this->collection->getBaseUrl() . '/' . $this->serial_number;
	}

	public function getAtompubServiceDoc() {
		$this->collection || $this->getCollection();
		$app = new Dase_Atom_Service;
		$workspace = $app->addWorkspace($this->collection->collection_name.' Item '.$this->serial_number.' Workspace');
		$media_coll = $workspace->addCollection(APP_ROOT.'/edit/'.$this->collection->ascii_id.'/'.$this->serial_number.'/media',$this->collection->collection_name.' Item '.$this->serial_number.' Media'); 
		$media_coll->addAccept('image/*');
		$media_coll->addAccept('audio/*');
		$media_coll->addAccept('video/*');
		return $app->asXml();
	}

	public function asJson()
	{
		$j = array();
		$this->collection || $this->getCollection();
		$item_array['serial_number'] = $this->serial_number;
		$item_array['created'] = $this->created;
		$item_array['updated'] = $this->updated;
		$item_array['collection']['ascii_id'] = $this->collection->ascii_id;
		$item_array['collection']['name'] = $this->collection->collection_name;
		$item_array['media'] = array();
		foreach ($this->getMedia() as $m) {
			foreach ($m as $k => $v) {
				$media_file[$k] = $v;
			}
			$item_array['media'][] = $media_file;
		}
		$item_array['metadata'] = array();
		foreach ($this->getMetadata() as $row) {
			$item_array['metadata'][$row['ascii_id']] = $row['value_text'];
		}
		$json = new Services_JSON;
		return $json->encode($item_array,true);
	}
}
