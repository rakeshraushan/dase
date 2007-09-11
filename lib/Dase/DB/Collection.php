<?php

require_once 'Dase/DB/Autogen/Collection.php';

class Dase_DB_Collection extends Dase_DB_Autogen_Collection 
{
	public $item_count;
	public $admin_attribute_array;
	public $attribute_array;
	public $item_type_array;

	function getAtom() {
		$writer = new XMLWriter();
		$writer->openMemory();
		$writer->setIndent(true);
		$writer->startDocument('1.0','UTF-8');
		$writer->startElement('feed');
		$writer->writeAttribute('xmlns','http://www.w3.org/2005/Atom');
		$ascii_id_array = explode('_',$this->ascii_id);
		$prefix = $ascii_id_array[0];
		$writer->writeAttribute("xmlns:$prefix", APP_HTTP_ROOT . "/{$this->ascii_id}/1.0");
		$writer->writeAttribute('xml:base', APP_HTTP_ROOT . "/{$this->ascii_id}/");
		$writer->startElement('title');
		$writer->text($this->collection_name);
		$writer->endElement();
		$writer->startElement('id');
		$writer->text(APP_HTTP_ROOT . "/{$this->ascii_id}");
		$writer->endElement();
		$writer->startElement('author');
		$writer->startElement('name');
		$writer->text('john smith');
		$writer->endElement();
		$writer->endElement();
		$writer->startElement('updated');
		$writer->text($this->updated());
		$writer->endElement();
		$item = new Dase_DB_Item;
		$item->collection_id = $this->id;
		$item->setLimit(10);
		foreach($item->findAll() as $it) {
			$writer->startElement('entry');
			$writer->writeAttribute('xml:base', APP_HTTP_ROOT . "/{$this->ascii_id}/");
			$writer->startElement('id');
			$writer->text(APP_ROOT . "/{$this->ascii_id}/{$it['serial_number']}");
			$writer->endElement();
			$writer->startElement('updated');
			$writer->text(date('c',$it['last_update']));
			$writer->endElement();
			$writer->writeAttribute('xml:base', APP_HTTP_ROOT . "/{$this->ascii_id}/");
			$item_type = Dase_DB_Object::getArray('item_type',$it['item_type_id']);
			if (isset($item_type['ascii_id'])) {
				$writer->startElement('category');
				$writer->writeAttribute('scheme',APP_HTTP_ROOT . "/{$this->ascii_id}/item_type");
				$writer->writeAttribute('term',$item_type['ascii_id']);
				$writer->writeAttribute('label',$item_type['name']);
				$writer->endElement();
			}
			$db = Dase_DB::get();
			$sql = "
				SELECT value_text,ascii_id,atom_element 
				FROM value, attribute
				WHERE attribute.id = value.attribute_id
				AND value.item_id = {$it['id']}
			";
			$st = $db->query($sql);
			foreach ($st->fetchAll() as $row) {
				if ($row['atom_element']) {
					$writer->startElement($row['atom_element']);
				} else {
					$writer->startElement($prefix . ':' . $row['ascii_id']);
				}
				$writer->text($row['value_text']);
				$writer->endElement();
			}
			$media_file = new Dase_DB_MediaFile;
			$media_file->item_id = $it['id'];
			foreach($media_file->findAll() as $mf) {
				if ('thumbnail' == $mf['size']) {
					$thumbnail_file = $mf['filename'];
					$thumbnail_type = $mf['mime_type'];
				}
				$writer->startElement('link');
				if (!$mf['file_size']) {
					$h = get_headers(APP_ROOT . "/{$this->ascii_id}/media/{$mf['size']}/{$mf['filename']}",1);
					$mf['file_size'] = $h['Content-Length'];
				}
				$writer->writeAttribute('length',$mf['file_size']);
				$writer->writeAttribute('type',$mf['mime_type']);
				$writer->writeAttribute('rel',APP_ROOT . "/media/{$mf['size']}");
				$writer->writeAttribute('href',"/media/{$mf['size']}/{$mf['filename']}");
				$writer->endElement();
			}
			$writer->startElement('content');
			$writer->writeAttribute('src', APP_ROOT . "/{$this->ascii_id}/media/thumbnail/$thumbnail_file");
			$writer->writeAttribute('type', $thumbnail_type);
			$writer->endElement();
			$writer->startElement('summary');
			$writer->text('thumbnail image');
			$writer->endElement();
			$writer->endElement();
		}
		$writer->endElement();
		$writer->endDocument();
		return $writer->flush(true);
	}

	function xmlDump($limit = 100000) {
		$writer = new XMLWriter();
		$writer->openMemory();
		$writer->setIndent(true);
		$writer->startDocument('1.0','UTF-8');
		$writer->startElement('archive');
		$writer->writeAttribute('name',$this->collection_name);
		$writer->writeAttribute('ascii_id',$this->ascii_id);
		$attribute = new Dase_DB_Attribute;
		$attribute->collection_id = $this->id;
		foreach($attribute->findAll() as $att) {
			$writer->startElement('attribute');
			$writer->writeAttribute('name',$att['attribute_name']);
			$writer->writeAttribute('ascii_id',$att['ascii_id']);
			$writer->endElement();
		}
		$type = new Dase_DB_ItemType;
		$type->collection_id = $this->id;
		foreach($type->findAll() as $t) {
			$writer->startElement('item_type');
			$writer->writeAttribute('name',$t['name']);
			$writer->writeAttribute('ascii_id',$t['ascii_id']);
			$writer->writeAttribute('description',$t['description']);
			$it_obj = new Dase_DB_ItemType;
			$it_obj->load($t['id']);
			foreach ($it_obj->getAttributes() as $a) {
				$writer->startElement('attribute');
				$writer->writeAttribute('ascii_id',$a->ascii_id);
				$writer->writeAttribute('cardinality',$a->cardinality);
				$writer->endElement();
			}
			$writer->endElement();
		}
		$item = new Dase_DB_Item;
		$item->collection_id = $this->id;
		if ($limit) { //0 means no limit
			$item->setLimit($limit);
		}
		foreach($item->findAll() as $it) {
			$writer->startElement('item');
			$writer->writeAttribute('serial_number',$it['serial_number']);
			$item_type = Dase_DB_Object::getArray('item_type',$it['item_type_id']);
			if (isset($item_type['ascii_id'])) {
				$writer->writeAttribute('item_type',$item_type['ascii_id']);
			}
			//straight db for 
			//metadata, took 2:40 
			//for 18K records
			$db = Dase_DB::get();
			$sql = "
				SELECT value_text,ascii_id 
				FROM value, attribute
				WHERE attribute.id = value.attribute_id
				AND value.item_id = {$it['id']}
			";
			$st = $db->query($sql);
			foreach ($st->fetchAll() as $row) {
				$writer->startElement('metadata');
				$writer->writeAttribute('attribute_ascii_id',$row['ascii_id']);
				$writer->text($row['value_text']);
				$writer->endElement();
			}
			//db_object get too 
			//4:40 for 18K 
			//records
			/*
			$value = new Dase_DB_Value;
			$value->item_id = $it['id'];
			foreach($value->findAll() as $val) {
				$writer->startElement('metadata');
				$att = Dase_DB_Object::getArray('attribute',$val['attribute_id']);
				$writer->writeAttribute('attribute_ascii_id',$att['ascii_id']);
				$writer->text($val['value_text']);
				$writer->endElement();
			}
			 */
			$media_file = new Dase_DB_MediaFile;
			$media_file->item_id = $it['id'];
			foreach($media_file->findAll() as $mf) {
				$writer->startElement('media_file');
				$writer->writeAttribute('filename',$mf['filename']);
				$writer->writeAttribute('size',$mf['size']);
				$writer->writeAttribute('mime_type',$mf['mime_type']);
				$writer->writeAttribute('width',$mf['width']);
				$writer->writeAttribute('height',$mf['height']);
				$writer->endElement();
			}
			$writer->endElement();
		}
		$writer->endElement();
		$writer->endDocument();
		return $writer->flush(true);
	}

	function getItemsByAttVal($att_ascii_id,$value_text,$substr = false) {
		$writer = new XMLWriter();
		$writer->openMemory();
		$writer->setIndent(true);
		$writer->startDocument('1.0','UTF-8');
		$writer->startElement('items');
		$writer->writeAttribute('collection',$this->collection_name);
		$writer->writeAttribute($att_ascii_id,$value_text);

		$a = new Dase_DB_Attribute;
		$a->ascii_id = $att_ascii_id;
		$a->collection_id = $this->id;
		$a->findOne();

		$v = new Dase_DB_Value;
		$v->attribute_id = $a->id;
		if ($substr) {
			$v->addWhere('value_text',"%$value_text%",'like');
		} else {
			$v->value_text = $value_text;
		}
		foreach ($v->findAll() as $vrow) {
			$it = new Dase_DB_Item;
			$it->load($vrow['item_id']);
			$writer->startElement('item');
			$writer->writeAttribute('serial_number',$it->serial_number);
			$type = new Dase_DB_ItemType;
			$type->load($vrow['item_id']);
			if (isset($type->name)) {
				$writer->writeAttribute('item_type',$type->name);
			}
			$db = Dase_DB::get();
			$sql = "
				SELECT value_text,ascii_id 
				FROM value, attribute
				WHERE attribute.id = value.attribute_id
				AND value.item_id = $it->id
				";
			$st = $db->query($sql);
			foreach ($st->fetchAll() as $row) {
				$writer->startElement('metadata');
				$writer->writeAttribute('attribute_ascii_id',$row['ascii_id']);
				$writer->text($row['value_text']);
				$writer->endElement();
			}
			$media_file = new Dase_DB_MediaFile;
			$media_file->item_id = $it->id;
			foreach($media_file->findAll() as $mf) {
				$writer->startElement('media_file');
				$writer->writeAttribute('filename',$mf['filename']);
				$writer->writeAttribute('size',$mf['size']);
				$writer->writeAttribute('mime_type',$mf['mime_type']);
				$writer->writeAttribute('width',$mf['width']);
				$writer->writeAttribute('height',$mf['height']);
				$writer->endElement();
			}
			$writer->endElement();
		}
		$writer->endElement();
		$writer->endDocument();
		return $writer->flush(true);
	}

	function getItemsXmlByType($type_ascii_id) {
		$writer = new XMLWriter();
		$writer->openMemory();
		$writer->setIndent(true);
		$writer->startDocument('1.0','UTF-8');
		$writer->startElement('items');
		$writer->writeAttribute('collection',$this->collection_name);

		$type = new Dase_DB_ItemType;
		$type->ascii_id = $type_ascii_id;
		$type->collection_id = $this->id;
		$type->findOne();

		$item = new Dase_DB_Item;
		$item->item_type_id = $type->id;
		$item->status_id = 0;
		foreach($item->findAll() as $row) {

			$it = new Dase_DB_Item($row);
			$writer->startElement('item');
			$writer->writeAttribute('serial_number',$it->serial_number);
			$db = Dase_DB::get();
			$sql = "
				SELECT value_text,ascii_id 
				FROM value, attribute
				WHERE attribute.id = value.attribute_id
				AND value.item_id = $it->id
				";
			$st = $db->query($sql);
			foreach ($st->fetchAll() as $row) {
				$writer->startElement('metadata');
				$writer->writeAttribute('attribute_ascii_id',$row['ascii_id']);
				$writer->text($row['value_text']);
				$writer->endElement();
			}
			$media_file = new Dase_DB_MediaFile;
			$media_file->item_id = $it->id;
			foreach($media_file->findAll() as $mf) {
				$writer->startElement('media_file');
				$writer->writeAttribute('filename',$mf['filename']);
				$writer->writeAttribute('size',$mf['size']);
				$writer->writeAttribute('mime_type',$mf['mime_type']);
				$writer->writeAttribute('width',$mf['width']);
				$writer->writeAttribute('height',$mf['height']);
				$writer->endElement();
			}
			$writer->endElement();
		}
		$writer->endElement();
		$writer->endDocument();
		return $writer->flush(true);
	}

	function asXml() {
		$dom = new DOMDocument('1.0');
		$coll = $dom->appendChild($dom->createElement('collection'));
		$fields = array('ascii_id','collection_name','path_to_media_files','description','is_public');
		foreach ($fields as $field) {
			$coll->setAttribute($field,$this->$field);	
		}
		$dom->formatOutput = true;
		return $dom->saveXML();
	}

	public static function get($ascii_id) {
		$c = new Dase_DB_Collection;
		$c->ascii_id = $ascii_id;
		return($c->findOne());
	}

	static function getAllAsXml() {
		$db = Dase_DB::get();
		$sql = "
			SELECT collection.id, collection.ascii_id,count(item.id) as item_tally,
				collection.collection_name,collection.path_to_media_files, collection.description,
				collection.is_public	
				FROM	
				collection, item
				WHERE collection.id = item.collection_id
				AND item.status_id = 0
				GROUP BY collection.id, collection.ascii_id,collection.collection_name,
				collection.path_to_media_files,collection.description,
				collection.is_public
				ORDER BY collection.collection_name
				";
		$st = $db->prepare($sql);
		$st->execute();
		$dom = new DOMDocument('1.0');
		$root = $dom->appendChild($dom->createElement('collections'));
		foreach ($st->fetchAll() as $row) {
			$coll = $root->appendChild($dom->createElement('collection'));
			$fields = array('ascii_id','collection_name','item_tally','path_to_media_files','description','is_public');
			foreach ($fields as $field) {
				$coll->setAttribute($field,$row[$field]);	
			}
		}
		$dom->formatOutput = true;
		return $dom->saveXML();
/*
		$dom = new DOMDocument('1.0');
		$coll_res = $dom->appendChild($dom->createElement('collectionSet'));
		$collection = new Dase_DB_Collection;
		foreach ($collection->getAll() as $c) {
			$coll = $coll_res->appendChild($dom->createElement('collection'));
			$fields = array('ascii_id','collection_name','path_to_media_files','description','is_public');
			foreach ($fields as $field) {
				$coll->setAttribute($field,$c[$field]);	
			}
		}
		$dom->formatOutput = true;
		return $dom->saveXML();
 */
	}

	function getAttributes($sort = null) {
		$att = new Dase_DB_Attribute;
		$att->collection_id = $this->id;
		if ($sort) {
			$att->orderBy($sort);
		} else {
			$att->orderBy('sort_order');
		}
		$this->attribute_array = $att->findAll();
		return $this->attribute_array;
	}

	function getAdminAttributes() {
		$att = new Dase_DB_Attribute;
		$att->collection_id = 0;
		$att->orderBy('sort_order');
		$this->admin_attribute_array = $att->findAll();
	}

	function getItemCount() {
		$db = Dase_DB::get();
		$sql = "
			SELECT count(item.id) as count
			FROM item
			where collection_id = ?
			";
		$st = $db->prepare($sql);
		$st->execute(array($this->id));
		$this->item_count = $st->fetchColumn();
		return $this->item_count;
	}

	function getItems() {
		//beware -- this could fill exceed memory limitations!!!
		$db = Dase_DB::get();
		$sql = "
			SELECT *
			FROM item
			where collection_id = $this->id
			";
		$st = $db->query($sql);
		$st->setFetchMode(PDO::FETCH_ASSOC);
		while ($row = $st->fetch()) {
			$items[] = new Dase_DB_Item($row);
		}
		return $items;
	}

	function getItemTypes() {
		$type = new Dase_DB_ItemType;
		$type->collection_id = $this->id;
		$type->orderBy('name');
		$this->item_type_array = $type->findAll();
		return $this->item_type_array;
	}

	function getItemsByType($type_ascii_id) {
		$it = new Dase_DB_ItemType;
		$it->collection_id = $this->id;
		$it->ascii_id = $type_ascii_id;
		$it->findOne();
		$ite = new Dase_DB_Item;
		$ite->item_type_id = $it->id;
		foreach ($ite->findAll() as $row) {
			$item = new Dase_DB_ItemType($row);
			$item_array[] = $item;
		}
		return $item_array;
	}

	public static function insertCollection($xml) {
		$dom = new DOMDocument;
		$dom->loadXML($xml);
		$schema = DASE_PATH . 'schemas/collection.rng';
		if ($dom->relaxNGValidate($schema)) {
			$attribute_nodes = $dom->getElementsByTagname('collection');
			$coll_elem = $attribute_nodes->item(0);
			$coll = new Dase_DB_Collection;
			$coll->ascii_id = $coll_elem->getAttribute('ascii_id');
			if ($coll->find(1)) {
				return $coll->id;
			} 
			$coll->collection_name = $coll_elem->getAttribute('collection_name');
			if (!$coll->collection_name) {
				$coll->collection_name = $coll_elem->nodeValue;
			}
			$coll->path_to_media_files = $coll_elem->getAttribute('path_to_media_files');
			$coll->is_public = $coll_elem->getAttribute('is_public');
			return $coll->insert();
		} else {
			throw new Exception('not a valid collection');
		}
	}

	public static function insertAttributes($ascii_id,$xml) {
		require_once 'Dase/DB/Attribute.php';
		if ($ascii_id) { //allow ascii_id = 0 to be used for admin_atts
			$coll = new Dase_DB_Collection;
			$coll->ascii_id = $ascii_id;
			if (!$coll->findOne()) {
				throw new Exception('no such collection');
			}
		}
		$dom = new DOMDocument;
		$dom->loadXML($xml);
		$schema = DASE_PATH . '/schemas/attributes.rng';
		$count = 0;
		if ($dom->relaxNGValidate($schema)) {
			foreach ($dom->getElementsByTagname('attribute') as $att_node) {
				$att = new Dase_DB_Attribute;
				if (isset($coll->id)) {
					$att->collection_id  = $coll->id;
				}
				$att->ascii_id  = $att_node->getAttribute('ascii_id');
				if (strstr($att->ascii_id,'admin_')) {
					$att->collection_id = 0;
				}
				if (!$att->findOne()) {
					$att->sort_order = $att_node->getAttribute('sort_order');
					$att->attribute_name = $att_node->getAttribute('attribute_name');
					$att->is_public = $att_node->getAttribute('is_public');
					$att->insert();
					$count++;
				} 
			}
		} else {
			//throw new Exception('not a valid attribute set');
			return;
		}
		return $count;
	}	

	public static function insertItem($ascii_id,$xml) {
		require_once 'Dase/DB/Item.php';
		require_once 'Dase/DB/MediaFile.php';
		require_once 'Dase/DB/Value.php';
		$coll = new Dase_DB_Collection;
		$coll->ascii_id = $ascii_id;
		if (!$coll->findOne()) {
			throw new Exception('no such collection');
		}
		$dom = new DOMDocument;
		$dom->loadXML($xml);
		$schema = DASE_PATH . '/schemas/item.rng';
		if ($dom->relaxNGValidate($schema)) {
			$item_nodes = $dom->getElementsByTagname('item');
			$item_elem = $item_nodes->item(0);
			$item = new Dase_DB_Item;
			$item->collection_id  = $coll->id;
			$item->status_id  = 0;
			$item->serial_number  = $item_elem->getAttribute('serial_number');
			$item_id = $item->insert();
			foreach($item_elem->getElementsByTagname('value') as $val_node) {
				$val = new Dase_DB_Value;
				$a = new Dase_DB_Attribute;
				$a->ascii_id = $val_node->getAttribute('attribute_ascii_id');
				if (strstr($a->ascii_id,'admin_')) {
					$a->collection_id = 0;
				} else {
					$a->collection_id = $coll->id;
				}
				$a->findOne();
				$val->attribute_id = $a->id;
				$val->value_text = $val_node->nodeValue;
				$val->item_id = $item_id;
				$val->insert();
			}
			foreach($item_elem->getElementsByTagname('media_file') as $mf_node) {
				$mf = new Dase_DB_MediaFile;
				$mf->filename = $mf_node->getAttribute('filename');
				$mf->size = $mf_node->getAttribute('size');
				$mf->mime_type = $mf_node->getAttribute('mime_type');
				$mf->width = $mf_node->getAttribute('width');
				$mf->height = $mf_node->getAttribute('height');
				$mf->item_id = $item_id;
				$mf->insert();
			}
		} else {
			//throw new Exception('not a valid item');
			return;
		}
		return "inserted item id $item_id";
	}	

	public function buildSearchIndex() {
		$db = Dase_DB::get();
		$db->query("DELETE FROM search_table WHERE collection_id = $this->id");
		$db->query("DELETE FROM admin_search_table WHERE collection_id = $this->id");
		$item = new Dase_DB_Item;
		$item->collection_id = $this->id;
		foreach ($item->findAll() as $it) {
			//search table
			$composite_value_text = '';
			//NOTE: '= true' works for mysql AND postgres!
			$sql = "
				SELECT value_text
				FROM value
				WHERE item_id = ?
				AND value.attribute_id in (SELECT id FROM attribute where in_basic_search = true)
				";
			$st = $db->prepare($sql);
			$st->execute(array($it['id']));
			while ($value_text = $st->fetchColumn()) {
				$composite_value_text .= $value_text . " ";
			}
			$search_table = new Dase_DB_SearchTable;
			$search_table->value_text = $composite_value_text;
			$search_table->item_id = $it['id'];
			$search_table->collection_id = $this->id;
			$search_table->insert();

			//admin search table
			$composite_value_text = '';
			$sql = "
				SELECT value_text
				FROM value
				WHERE item_id = ?
				";
			$st = $db->prepare($sql);
			$st->execute(array($it['id']));
			while ($value_text = $st->fetchColumn()) {
				$composite_value_text .= $value_text . " ";
			}
			$search_table = new Dase_DB_AdminSearchTable;
			$search_table->value_text = $composite_value_text;
			$search_table->item_id = $it['id'];
			$search_table->collection_id = $this->id;
			$search_table->insert();
		}
		return true;
	}

	function createNewItem() {
		$item = new Dase_DB_Item;
		$item->collection_id = $this->id;
		$item->status_id = 0;
		$item->insert();
		$item->serial_number = sprintf("%09d",$item->id);
		$item->item_type_id = 0;
		$item->update();
		return $item;
	}

	function updated() {
		$item = new Dase_DB_Item;
		$item->collection_id = $this->id;
		$item->orderBy('last_update DESC');
		$item->setLimit(1);
		$item->findOne();
		return date('c',$item->last_update);
	}
}
