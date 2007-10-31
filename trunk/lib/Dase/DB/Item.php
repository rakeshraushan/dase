<?php

require_once 'Dase/DB/Autogen/Item.php';

class Dase_DB_Item extends Dase_DB_Autogen_Item 
{

	public $collection = null;
	public $values = array();
	public $admin = array();
	public $thumbnail = null;
	public $viewitem = null;

	public static function create($collection_ascii_id,$serial_number= null) {
		$c = Dase_DB_Collection::get($collection_ascii_id);
		return $c->createNewItem($serial_number);
	}

	public static function retrieve($collection_ascii_id,$serial_number) {
		$c = Dase_DB_Collection::get($collection_ascii_id);
		$item = new Dase_DB_Item;
		$item->collection_id = $c->id;
		$item->serial_number = $serial_number;
		return $item->findOne();
	}

	public function buildSearchIndex() {
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
		$search_table = new Dase_DB_SearchTable;
		$search_table->value_text = $composite_value_text;
		$search_table->item_id = $this->id;
		$search_table->collection_id = $this->collection_id;
		$search_table->last_update = time();
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
		$search_table = new Dase_DB_AdminSearchTable;
		$search_table->value_text = $composite_value_text;
		$search_table->item_id = $this->id;
		$search_table->collection_id = $this->collection_id;
		$search_table->last_update = time();
		$search_table->insert();
		$this->last_update = time();
		$this->update();
		return "built indexes for " . $this->serial_number . "\n";
	}

	public function getValues() {
		$val = new Dase_DB_Value;
		$val->item_id = $this->id;
		foreach ($val->findAll() as $row) {
			$v = new Dase_DB_Value($row);
			$v->getAttributeName();
			$this->values[] = $v;
		}
		//what about sorting?????
		return $this->values;
	}

	public function getAttVal($att_ascii_id) {
		//NOTE: repeat attributes will only get ONE value!!!!
		$values = array();
		$this->collection || $this->getCollection();
		$val = new Dase_DB_Value;
		$val->item_id = $this->id;
		$val->attribute_id = Dase_DB_Attribute::get($this->collection->ascii_id,$att_ascii_id)->id;
		$val->findOne();
		return $val->value_text;
	}

	public function getCollection() {
		$c = new Dase_DB_Collection;
		$c->load($this->collection_id);
		$this->collection = $c;
		return $c;
	}

	public function getThumbnail() {
		$this->collection || $this->getCollection();
		$m = new Dase_DB_MediaFile;
		$m->item_id = $this->id;
		$m->size = 'thumbnail';
		$this->thumbnail = $m->findOne();
		$this->thumbnail->url = APP_ROOT . "/media/{$this->collection->ascii_id}/thumbnail/$m->filename";
		return $this->thumbnail;
	}

	public function getViewitem() {
		$this->collection || $this->getCollection();
		$m = new Dase_DB_MediaFile;
		$m->item_id = $this->id;
		$m->size = 'viewitem';
		$this->viewitem = $m->findOne();
		$this->viewitem->url = APP_ROOT . "/media/{$this->collection->ascii_id}/viewitem/$m->filename";
		return $this->viewitem;
	}

	public function getMedia() {
		$this->collection || $this->getCollection();
		$m = new Dase_DB_MediaFile;
		$m->item_id = $this->id;
		$media = array();
		foreach ($m->findAll() as $row) {
			$mf = new Dase_DB_MediaFile($row);
			$media[] = $mf;
		}
		return $media;
	}

	public function getMediaUrl($size) {  //size really means type here
		$this->collection || $this->getCollection();
		$m = new Dase_DB_MediaFile;
		$m->item_id = $this->id;
		$m->size = $size;
		$this->media = $m->findOne();
		$url = APP_ROOT . "/media/{$this->collection->ascii_id}/$size/$m->filename";
		return $url;
	}

	public function asAtomEntryDom() {
		$this->collection || $this->getCollection();
		$dom = new DOMDocument;
		$frag = $dom->createDocumentFragment();
		$entry = $dom->createElement('entry');
		$id = $dom->createElement('id');
		$id->appendChild($dom->createTextNode(APP_ROOT . "/{$this->collection->ascii_id}/{$this->serial_number}"));
		$entry->appendChild($id);
		$content = $dom->createElement('content');
		$content->setAttribute('type','xhtml');
		$dl = $dom->createElement('dl');
		foreach ($this->getValues() as $v) {
			$dt = $dom->createElement('dt');
			$dt->setAttribute('class',$this->collection->ascii_id . '.' . $v->attribute_ascii_id);
			$dt->appendChild($dom->createTextNode($v->attribute_name));
			$dl->appendChild($dt);
			$dd = $dom->createElement('dd');
			$a = $dom->createElement('a');
			$a->setAttribute('href',"search?{$this->collection->ascii_id}:{$v->attribute_ascii_id}={$v->value_text_md5}");
			$a->appendChild($dom->createTextNode($v->value_text));
			$dd->appendChild($a);
			$dl->appendChild($dd);
		}
		$div = $dom->createElement('div');
		$div->setAttribute('xmlns',"http://www.w3.org/1999/xhtml");
		$div->appendChild($dl);
		$collection_name = $dom->createElement('p');
		$collection_name->setAttribute('class','collectionName');
		$collection_name->appendChild($dom->createTextNode($this->collection->collection_name));
		$content->appendChild($div);
		$entry->appendChild($content);
		$title = $dom->createElement('title');
		$title->appendChild($dom->createTextNode($this->getTitle()));
		$entry->appendChild($title);

		$updated = $dom->createElement('updated');
		$updated->appendChild($dom->createTextNode(date('c',$this->last_update)));
		$entry->appendChild($updated);

		$html_link = $dom->createElement('link');
		$html_link->setAttribute('rel','alternate');
		$html_link->setAttribute('type','text/html');
		$html_link->setAttribute('href',APP_ROOT . "/{$this->collection->ascii_id}/{$this->serial_number}");
		$entry->appendChild($html_link);

		$xml_link = $dom->createElement('link');
		$xml_link->setAttribute('rel','alternate');
		$xml_link->setAttribute('type','application/xml');
		$xml_link->setAttribute('href',APP_ROOT . "/xml/{$this->collection->ascii_id}/{$this->serial_number}");
		$entry->appendChild($xml_link);

		foreach ($this->getMedia() as $m) {
			$link = $dom->createElement('link');
			$link->setAttribute('rel',$m->size);
			$link->setAttribute('type',$m->mime_type);
			$link->setAttribute('href',APP_ROOT . "/media/{$this->collection->ascii_id}/{$m->size}/{$m->filename}");
			$entry->appendChild($link);
			if ('thumbnail' == $m->size) {
				$thumbnail = $dom->createElement('img');
				$thumbnail->setAttribute('src',APP_ROOT . "/media/{$this->collection->ascii_id}/thumbnail/{$m->filename}");
				$thumbnail->setAttribute('alt',$this->getTitle());
				$div->appendChild($thumbnail);
			}
			$div->appendChild($collection_name);
		}
		$frag->appendChild($entry);
		return $frag;
		//$dom->appendChild($frag);
		//return $dom->saveXML();
	}

	//maybe should be in an atom class???
	public static function getAtomFeed($item_array,$feed_title,$feed_id) {
		$dom = new DOMDocument;
		$feed = $dom->createElement('feed');
		$feed->setAttribute('xmlns','http://www.w3.org/2005/Atom');
		$author = $dom->createElement('author');
		$author->appendChild($dom->createElement('name'));
		$feed->appendChild($author);
		$updated = $dom->createElement('updated');
		$updated->appendChild($dom->createTextNode(date('c',time())));
		$feed->appendChild($updated);
		$self_link = $dom->createElement('link');
		$self_link->setAttribute('rel','self');
		$self_link->setAttribute('type','application/atom+xml');
		$self_link->setAttribute('href',APP_ROOT . "/atom/feed/$feed_id");
		$feed->appendChild($self_link);
		$title = $dom->createElement('title');
		$title->appendChild($dom->createTextNode($feed_title));
		$feed->appendChild($title);
		$id = $dom->createElement('id');
		$id->appendChild($dom->createTextNode(APP_ROOT . "/atom/feed/$feed_id"));
		$feed->appendChild($id);
		foreach ($item_array as $item) {
			$entry = $dom->importNode($item->asAtomEntryDom(),true);
			$feed->appendChild($entry);
		}
		$dom->appendChild($feed);
		$dom->formatOutput = true;
		return $dom->saveXml();
	}

	public function getXml() {
		$writer = new XMLWriter();
		$writer->openMemory();
		$writer->setIndent(true);
		$writer->startDocument('1.0','UTF-8');
		$writer->startElement('item');
		$writer->writeAttribute('serial_number',$this->serial_number);
		$writer->writeAttribute('collection_ascii_id',$this->getCollection()->ascii_id);
		$type = new Dase_DB_ItemType;
		$type->load($this->item_type_id);
		$writer->writeAttribute('item_type',$type->ascii_id);
		$db = Dase_DB::get();
		$sql = "
			SELECT value.value_text,value.value_text_md5,attribute.ascii_id,attribute.attribute_name 
			FROM value, attribute
			WHERE attribute.id = value.attribute_id
			AND value.item_id = $this->id
			";
		$st = $db->query($sql);
		foreach ($st->fetchAll() as $row) {
			$writer->startElement('meta');
			$writer->startElement('att');
			$writer->writeAttribute('ascii_id',$row['ascii_id']);
			$writer->text($row['attribute_name']);
			$writer->endElement();
			$writer->startElement('val');
			$writer->writeAttribute('md5',$row['value_text_md5']);
			$writer->text($row['value_text']);
			$writer->endElement();
			$writer->endElement();
		}
		$media_file = new Dase_DB_MediaFile;
		$media_file->item_id = $this->id;
		foreach($media_file->findAll() as $mf) {
			$writer->startElement('media_file');
			$writer->writeAttribute('href',$this->getMediaUrl($mf['size']));
			$writer->writeAttribute('rel',$mf['size']);
			$writer->writeAttribute('type',$mf['mime_type']);
			$writer->writeAttribute('width',$mf['width']);
			$writer->writeAttribute('height',$mf['height']);
			$writer->endElement();
		}
		$writer->endElement();
		$writer->endDocument();
		return $writer->flush(true);
	}

	function getMediaCount() {
		$mf = new Dase_DB_MediaFile;
		$mf->item_id = $this->id;
		$i = 0;
		foreach ($mf->findAll() as $m) {
			$i++;
		}
		return $i;
	}

	function setType($type_ascii_id) {
		$type = new Dase_DB_ItemType;
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
		$att = new Dase_DB_Attribute;
		$att->ascii_id = $att_ascii_id;
		//allows for admin metadata, att_ascii for which
		//always begins 'admin_'
		if (false === strpos($att_ascii_id,'admin_')) {
			$att->collection_id = $this->collection_id;
		}
		if ($att->findOne()) {
			$v = new Dase_DB_Value;
			$v->item_id = $this->id;
			$v->attribute_id = $att->id;
			$v->value_text = $value_text;
			return($v->insert());
		} else {
			return false;
		}
	}

	function deleteValues() {
		//should snaity check and archive values
		$v = new Dase_DB_Value;
		$v->item_id = $this->id;
		foreach ($v->findAll() as $row) {
			$doomed = new Dase_DB_Value($row);
			$doomed->delete();
		}
		$st = new Dase_DB_SearchTable;
		$st->item_id = $this->id;
		foreach ($st->findAll() as $row) {
			$doomed = new Dase_DB_SearchTable($row);;
			$doomed->delete();
		}
		$ast = new Dase_DB_AdminSearchTable;
		$ast->item_id = $this->id;
		foreach ($ast->findAll() as $row) {
			$doomed = new Dase_DB_SearchTable($row);;
			$doomed->delete();
		}
	}

	function deleteAdminValues() {
		$a = new Dase_DB_Attribute;
		$a->collection_id = 0;
		foreach ($a->findAll() as $row) {
			$v = new Dase_DB_Value;
			$v->item_id = $this->id;
			$v->attribute_id = $row['id'];
			foreach ($v->findAll() as $row) {
				$doomed = new Dase_DB_Value($row);
				$doomed->delete();
			}
		}
		return "deleted admin metadata for " . $this->serial_number . "\n";
	}

	function deleteMedia() {
		$mf = new Dase_DB_MediaFile;
		$mf->item_id = $this->id;
		foreach ($mf->findAll() as $row) {
			$doomed = new Dase_DB_MediaFile($row);
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
}
