<?php

require_once 'Dase/DBO/Autogen/Item.php';

class Dase_DBO_Item extends Dase_DBO_Autogen_Item 
{

	public $admin = array();
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

	public static function create($collection_ascii_id,$serial_number= null) {
		$c = Dase_DBO_Collection::get($collection_ascii_id);
		return $c->createNewItem($serial_number);
	}

	public static function get($collection_ascii_id,$serial_number) {
		$c = Dase_DBO_Collection::get($collection_ascii_id);
		if (!$c) {
			return false;
		}
		$item = new Dase_DBO_Item;
		$item->collection_id = $c->id;
		$item->serial_number = $serial_number;
		return $item->findOne();
	}

	public function buildSearchIndex() {
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

	public function getValues() {
		$val = new Dase_DBO_Value;
		$val->item_id = $this->id;
		return $val->find();
	}

	public function getMetadata() {
		//minimize memory consumption 
		//as compared to getValues()
		$metadata = array();
		$db = Dase_DB::get();
		$sql = "
			SELECT a.ascii_id, a.attribute_name,v.value_text, v.value_text_md5
			FROM attribute a, value v
			WHERE v.item_id = $this->id
			AND v.attribute_id = a.id
			";
		$st = $db->prepare($sql);
		$st->execute();
		while ($row = $st->fetch()) {
			$metadata[] = $row;
		}
		return $metadata;
	}

	public function getChildren() {

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

	public function getAttVal($att_ascii_id) {
		//NOTE: repeat attributes will only get ONE value!!!!
		$values = array();
		$this->collection || $this->getCollection();
		$val = new Dase_DBO_Value;
		$val->item_id = $this->id;
		$val->attribute_id = Dase_DBO_Attribute::get($this->collection->ascii_id,$att_ascii_id)->id;
		$val->findOne();
		return $val->value_text;
	}

	public function getCollection() {
		$c = new Dase_DBO_Collection;
		$c->load($this->collection_id);
		$this->collection = $c;
		$this->collection_ascii_id = $c->ascii_id;
		$this->collection_name = $c->collection_name;
		//$this->coll = substr($c->ascii_idi,0,-11);
		return $c;
	}

	public function getThumbnail() {
		$this->collection || $this->getCollection();
		$m = new Dase_DBO_MediaFile;
		$m->item_id = $this->id;
		$m->size = 'thumbnail';
		$this->thumbnail = $m->findOne();
		$this->thumbnail_url = APP_ROOT . "/media/{$this->collection->ascii_id}/thumbnail/$m->filename";
		return $this->thumbnail;
	}

	public function getViewitem() {
		$this->collection || $this->getCollection();
		$m = new Dase_DBO_MediaFile;
		$m->item_id = $this->id;
		$m->size = 'viewitem';
		$this->viewitem = $m->findOne();
		$this->viewitem_url = APP_ROOT . "/media/{$this->collection->ascii_id}/viewitem/$m->filename";
		return $this->viewitem;
	}

	public function getItemType() {
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

	public function getItemStatus() {
		$status = new Dase_DBO_ItemStatus;
		$status->item_id = $this->id;
		if ($status->findOne()) {
			$this->item_status = $status->status;
		}
		return $this->item_status;
	}

	public function getMedia() {
		$this->collection || $this->getCollection();
		$m = new Dase_DBO_MediaFile;
		$m->item_id = $this->id;
		$m->orderBy('width');
		return $m->find();
	}

	public function getMediaUrl($size) {  //size really means type here
		$this->collection || $this->getCollection();
		$m = new Dase_DBO_MediaFile;
		$m->item_id = $this->id;
		$m->size = $size;
		$this->media = $m->findOne();
		$url = APP_ROOT . "/media/{$this->collection->ascii_id}/$size/$m->filename";
		return $url;
	}

	function getMediaCount() {
		$db = Dase_DB::get();
		$sql = "
			SELECT count(*) 
			FROM media_file
			WHERE item_id = $this->id
			";
		return $db->query($sql)->fetchColumn();
	}

	function setType($type_ascii_id) {
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

	function setValue($att_ascii_id,$value_text) {
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
			$v->value_text_md5 = md5($value_text);
			return($v->insert());
		} else {
			return false;
		}
	}

	function deleteValues() {
		//should sanity check and archive values
		$v = new Dase_DBO_Value;
		$v->item_id = $this->id;
		foreach ($v->find() as $doomed) {
			$doomed->delete();
		}
		$st = new Dase_DBO_SearchTable;
		$st->item_id = $this->id;
		foreach ($st->find() as $doomed) {
			$doomed->delete();
		}
		$ast = new Dase_DBO_AdminSearchTable;
		$ast->item_id = $this->id;
		foreach ($ast->find() as $doomed) {
			$doomed->delete();
		}
	}

	function deleteAdminValues() {
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

	function expunge() {
		$this->deleteMedia();
		$this->deleteValues();
		$this->deleteAdminValues();
		$this->delete();
	}

	function deleteMedia() {
		$mf = new Dase_DBO_MediaFile;
		$mf->item_id = $this->id;
		foreach ($mf->find() as $doomed) {
			$doomed->delete();
		}
	}

	function getAdminMetadata($att_ascii_id = null) {
		//admin is ONLY set once in the life of
		//an item object.  user can specify which 
		//one will be returned, otherwise array is returned
		if (!count($this->admin)) {
			$db = Dase_DB::get();
			$sql = "
				SELECT a.ascii_id, v.value_text 
				FROM attribute a, value v
				WHERE a.id = v.attribute_id
				AND v.item_id = $this->id
				AND a.collection_id = 0
				";
			$st = $db->query($sql);
			while ($row = $st->fetch()) {
				$this->admin[$row['ascii_id']] = $row['value_text'];
			}	
		}
		if ($att_ascii_id) {
			if (isset($this->admin[$att_ascii_id])) {
				return $this->admin[$att_ascii_id];
			} else {
				return false;
			}
		}
		return $this->admin;
	}

	function getTitle() {
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

	function injectAtomEntryData(Dase_Atom_Entry $entry) {
		$this->collection || $this->getCollection();
		$this->item_type || $this->getItemType();
		if (is_numeric($this->updated)) {
			$updated = date(DATE_ATOM,$this->updated);
		} else {
			$updated = $this->updated;
		}
		$entry->setTitle($this->getTitle());
		$entry->setUpdated($updated);
		$entry->setId(APP_ROOT . '/' . $this->collection_ascii_id . '/' . $this->serial_number);
		$entry->addCategory($this->id,'http://daseproject.org/category/item/id');
		$entry->addCategory($this->collection_ascii_id,'http://daseproject.org/category/collection',$this->collection_name);
		if ($this->item_type) {
			$entry->addCategory($this->item_type_ascii,'http://daseproject.org/category/item_type',$this->item_type_label);
		}
		$entry->addLink(APP_ROOT.'/collection/'.$this->collection_ascii_id.'/'.$this->serial_number,'alternate' );
		//switch to the simple xml interface here
		$div = simplexml_import_dom($entry->setContent());
		$div->addAttribute('class',$this->collection_ascii_id);
		$this->thumbnail || $this->getThumbnail();
		$img = $div->addChild('img');
		$img->addAttribute('src',$this->thumbnail_url);
		$img->addAttribute('class','thumbnail');
		$this->viewitem || $this->getViewitem();
		$img = $div->addChild('img');
		$img->addAttribute('src',$this->viewitem_url);
		$img->addAttribute('class','viewitem');
		$div->addChild('p',htmlspecialchars($this->collection->collection_name))->addAttribute('class','collection_name');;
		$dl = $div->addChild('dl');
		$dl->addAttribute('class','metadata');
		foreach ($this->getMetadata() as $row) {
			//note: since this is used in archiving scripts
			//I use getMetadata() rather than getValues() to
			//conserve memory
			$dt = $dl->addChild('dt',htmlspecialchars($row['attribute_name']));
			$dt->addAttribute('class',$row['ascii_id']);
			$dd = $dl->addChild('dd',htmlspecialchars($row['value_text']));
			$dd->addAttribute('class',htmlspecialchars($row['value_text_md5']));
		}
		$d = 'http://daseproject.org/media/';
		//$media_ul = $div->addChild('ul');
		//$media_ul->addAttribute('class','media');
		foreach ($this->getMedia() as $med) {
			//$media_li = $media_ul->addChild('li');
			//$media_li->addAttribute('class',$med->size);
			//$a = $media_li->addChild('a', $med->size . " (" . $med->width ."x" .$med->height .")");
			//$a->addAttribute('href', APP_ROOT . "/media/" . $this->collection_ascii_id.'/'.$med->size.'/'.$med->filename);
			//$a->addAttribute('class',$med->mime_type);
			$link = $entry->addLink(
				APP_ROOT.'/media/'.$this->collection_ascii_id.'/'.$med->size.'/'.$med->filename,
				'http://daseproject.org/relation/media',
				$med->mime_type,$med->file_size
			);
			$link->setAttributeNS($d,'d:width',$med->width);
			$link->setAttributeNS($d,'d:height',$med->height);
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

	function injectAtomFeedData(Dase_Atom_Feed $feed) {
		$this->collection || $this->getCollection();
		if (is_numeric($this->updated)) {
			$updated = date(DATE_ATOM,$this->updated);
		} else {
			$updated = $this->updated;
		}
		$feed->setUpdated($updated);
		$feed->setTitle($this->getTitle());
		$feed->setId(APP_ROOT . '/' . $this->collection_ascii_id . '/' . $this->serial_number);
		$feed->setGenerator('DASe','http://daseproject.org','1.0');
		$feed->addAuthor('DASe (Digital Archive Services)','http://daseproject.org');
		return $feed;
	}

	function asAtom() {
		$feed = new Dase_Atom_Feed;
		$this->injectAtomFeedData($feed);
		$this->injectAtomEntryData($feed->addEntry());
		return $feed->asXml();
	}
}
