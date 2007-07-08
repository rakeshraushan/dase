<?php

require_once 'Dase/DB/Autogen/ItemType.php';

class Dase_DB_ItemType extends Dase_DB_Autogen_ItemType 
{
	public static function get($id) {
		$db = Dase_DB::get();
		return $db->query("SELECT * FROM item_type WHERE id = $id")->fetch();
	}
}
