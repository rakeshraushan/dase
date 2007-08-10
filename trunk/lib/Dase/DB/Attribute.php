<?php

require_once 'Dase/DB/Autogen/Attribute.php';

class Dase_DB_Attribute extends Dase_DB_Autogen_Attribute 
{
	public $display_values = array();
	public $cardinality;

	function getValueCount() {
		if (!$this->id) {
			throw new Exception('attribute not instantiated/loaded'); 
		}
		$db = Dase_DB::get();
		$st = $db->prepare('SELECT count(*) FROM value WHERE attribute_id = ?');
		$st->execute(array($this->id));	
		return $st->fetchColumn();
	}

	function getDisplayValues($limit = 10,$coll = null) {
		$admin_sql = '';
		if (!$this->id) {
			throw new Exception('attribute not instantiated/loaded'); 
		}
		$db = Dase_DB::get();
		//presence od collection_id says it is an admin att
		if ($coll) {
			$admin_sql = "AND item_id IN (SELECT id FROM item WHERE collection_id IN (SELECT id FROM collection WHERE ascii_id = '$coll'))";
		}
		$sql = "
			SELECT value_text, value_text_md5, count(value_text)
			FROM value
			WHERE attribute_id = ?
			$admin_sql
			GROUP BY value_text, value_text_md5
			ORDER BY value_text
			";
		$st = $db->prepare($sql);
		$st->execute(array($this->id));
		$display_values_array = array();
		while ($row = $st->fetch()) {
			$display_values_array[] = array(
				'value_text' => $row[0],
				'urlencoded_value_text' => urlencode($row[0]),
				'value_text_md5' => $row[1],
				'tally' => $row[2]
			);
		}
		$this->display_values = $display_values_array;
		return $display_values_array;
	}
}
