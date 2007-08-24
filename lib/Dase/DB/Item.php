<?php

require_once 'Dase/DB/Autogen/Item.php';

class Dase_DB_Item extends Dase_DB_Autogen_Item 
{

	public $collection = null;
	public $values = array();
	public $thumbnail = null;
	public $viewitem = null;

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
		$search_table->insert();
	}

	public function getValues() {
		$val = new Dase_DB_Value;
		$val->item_id = $this->id;
		foreach ($val->findAll() as $row) {
			$v = new Dase_DB_Value($row);
			$v->getAttributeName();
			$this->values[] = $v;
		}
		return $this->values;
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

	public function getXml() {
		$writer = new XMLWriter();
		$writer->openMemory();
		$writer->setIndent(true);
		$writer->startDocument('1.0','UTF-8');
		$writer->startElement('item');
		$writer->writeAttribute('serial_number',$this->serial_number);
		$type = new Dase_DB_ItemType;
		$type->load($this->item_type_id);
		$writer->writeAttribute('item_type',$type->ascii_id);
		$db = Dase_DB::get();
		$sql = "
			SELECT value_text,ascii_id 
			FROM value, attribute
			WHERE attribute.id = value.attribute_id
			AND value.item_id = $this->id
			";
		$st = $db->query($sql);
		foreach ($st->fetchAll() as $row) {
			$writer->startElement('metadata');
			$writer->writeAttribute('attribute_ascii_id',$row['ascii_id']);
			$writer->text($row['value_text']);
			$writer->endElement();
		}
		$media_file = new Dase_DB_MediaFile;
		$media_file->item_id = $this->id;
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
		$att->collection_id = $this->collection_id;
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
}
