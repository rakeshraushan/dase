<?php

require_once 'Dase/DB/Autogen/Item.php';

class Dase_DB_Item extends Dase_DB_Autogen_Item 
{

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
}
