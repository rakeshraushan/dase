<?php

require_once 'Dase/DB/Autogen/Attribute.php';

class Dase_DB_Attribute extends Dase_DB_Autogen_Attribute 
{
	public $display_values = array();

	public static function get($id) {
		$db = Dase_DB::get();
		return $db->query("SELECT * FROM attribute WHERE id = $id")->fetch();
	}

	function getValueCount() {
		if (!$this->id) {
			throw new Exception('attribute not instantiated/loaded'); 
		}
		$db = Dase_DB::get();
		$st = $db->prepare('SELECT count(*) FROM value WHERE attribute_id = ?');
		$st->execute(array($this->id));	
		return $st->fetchColumn();
	}

	function getDisplayValues($limit = 10) {
		if (!$this->id) {
			throw new Exception('attribute not instantiated/loaded'); 
		}
		$db = Dase_DB::get();
		$sql = "
			SELECT value_text,count(value_text)
			FROM value
			WHERE attribute_id = ?
			GROUP BY value_text
			";
		$st = $db->prepare($sql);
		$st->execute(array($this->id));
		$display_values_array = array();
		while ($row = $st->fetch()) {
			$display_values_array[] = array(
				'value_text' => $row[0],
				'urlencoded_value_text' => urlencode($row[0]),
				'tally' => $row[1]
			);
		}
		$this->display_values = $display_values_array;
		return $display_values_array;
	}
}
