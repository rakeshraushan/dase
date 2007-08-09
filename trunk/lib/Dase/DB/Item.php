<?php

require_once 'Dase/DB/Autogen/Item.php';

class Dase_DB_Item extends Dase_DB_Autogen_Item 
{

	public $values;
	public $thumbnail;
	public $thumbnail_url;

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
			AND value.attribute_id in (SELECT id FROM attribute where in_basic_search = 1)
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

	public function getThumbnail() {
		$m = new Dase_DB_MediaFile;
		$m->item_id = $this->id;
		$m->size = 'thumbnail';
		$this->thumbnail = $m->findOne();
		$coll = new Dase_DB_Collection;
		$coll->load($this->collection_id);
		$this->thumbnail_url = APP_ROOT . "/media/$coll->ascii_id/thumbnail/$m->filename";
		return $this->thumbnail;
	}
}
