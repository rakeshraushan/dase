<?php

require_once 'Dase/DBO/Autogen/Value.php';

class Dase_DBO_Value extends Dase_DBO_Autogen_Value 
{
	public static function getCount($collection_ascii_id='')
	{
		$db = Dase_DB::get();
		$sql = "
			SELECT count(*) 
			FROM value
			";
		if ($collection_ascii_id) {
			$sql .= "
				, item, collection
				WHERE value.item_id = item.id
				AND collection.id = item.collection_id
				AND collection.ascii_id = ?
				";
			$sth = $db->prepare($sql);
			$sth->execute(array($collection_ascii_id));
		} else {
			$sth = $db->prepare($sql);
			$sth->execute();
		}
		return $sth->fetchColumn();
	}
}
