<?php

require_once 'Dase/DB/Autogen/Collection.php';

class Dase_DB_Collection extends Dase_DB_Autogen_Collection 
{
	public $item_count;
	public $admin_attribute_array;
	public $attribute_array;
	public $category_array;

	function xmlDump() {
		$writer = new XMLWriter();
		$writer->openMemory();
		$writer->setIndent(true);
		$writer->startDocument('1.0','UTF-8');
		$writer->startElement('archive');
		$writer->writeAttribute('name',$this->collection_name);
		$writer->writeAttribute('ascii_id',$this->ascii_id);
		$attribute = new Dase_DB_Attribute;
		$attribute->collection_id = $this->id;
		foreach($attribute->find() as $att) {
			$writer->startElement('attribute');
			$writer->writeAttribute('name',$att->attribute_name);
			$writer->writeAttribute('ascii_id',$att->ascii_id);
			$writer->endElement();
		}
		$item = new Dase_DB_Item;
		$item->collection_id = $this->id;
		//NOTE 2000 item limit!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		$item->setLimit(2000);
		foreach($item->find() as $it) {
			$writer->startElement('item');
			$writer->writeAttribute('serial_number',$it->serial_number);
			$it->getItemType();
			if (isset($it->item_type->name)) {
				$writer->writeAttribute('item_type',$it->item_type->name);
			}
			$value = new Dase_DB_Value;
			$value->item_id = $it->id;
			foreach($value->find() as $val) {
				$writer->startElement('metadata');
				$val->getAttribute();
				$writer->writeAttribute('attribute_ascii_id',$val->attribute->ascii_id);
				$writer->text($val->value_text);
				$writer->endElement();
			}
			$media_file = new Dase_DB_MediaFile;
			$media_file->item_id = $it->id;
			foreach($media_file->find() as $mf) {
				$writer->startElement('media_file');
				$writer->writeAttribute('filename',$mf->filename);
				$writer->writeAttribute('size',$mf->size);
				$writer->writeAttribute('mime_type',$mf->mime_type);
				$writer->writeAttribute('width',$mf->width);
				$writer->writeAttribute('height',$mf->height);
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
		$fields = array('ascii_id','collection_name','path_to_media_files','description','is_public','display_categories');
		foreach ($fields as $field) {
			$coll->setAttribute($field,$this->$field);	
		}
		$dom->formatOutput = true;
		return $dom->saveXML();
	}

	public static function get($ascii_id) {
		$c = new Dase_DB_Collection;
		$c->ascii_id = $ascii_id;
		return($c->find(1));
	}

	static function getAllAsXml() {
		$dom = new DOMDocument('1.0');
		$coll_res = $dom->appendChild($dom->createElement('collectionSet'));
		$collection = new Dase_DB_Collection;
		foreach ($collection->getAll() as $c) {
			$coll = $coll_res->appendChild($dom->createElement('collection'));
			$fields = array('ascii_id','collection_name','path_to_media_files','description','is_public','display_categories');
			foreach ($fields as $field) {
				$coll->setAttribute($field,$c->$field);	
			}
		}
		$dom->formatOutput = true;
		return $dom->saveXML();
	}

	function getAttributes() {
		$att = new Dase_DB_Attribute;
		$att->collection_id = $this->id;
		$att->orderBy('sort_order');
		$this->attribute_array = $att->find();
		return $this->attribute_array;
	}

	function getAdminAttributes() {
		$att = new Dase_DB_Attribute;
		$att->collection_id = 0;
		$att->orderBy('sort_order');
		$this->admin_attribute_array = $att->find();
	}

	function getCategories() {
		$cat = new Dase_DB_Category;
		$cat->collection_id = $this->id;
		$cat->orderBy('sort_order');
		$this->category_array = $cat->find();
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
			if (!$coll->find(1)) {
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
				if (!$att->find(1)) {
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
		if (!$coll->find(1)) {
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
				$a->find(1);
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
		foreach ($item->find() as $it) {
			$it->buildSearchIndex(0);
		}
		return true;
	}
}
