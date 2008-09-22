<?php

require_once 'Dase/DBO/Autogen/Value.php';

class Dase_DBO_Value extends Dase_DBO_Autogen_Value 
{
	public static function getCount($collection_ascii_id='')
	{
		$prefix = Dase_Config::get('table_prefix');
		$db = Dase_DB::get();
		$sql = "
			SELECT count(*) 
			FROM {$prefix}value v
			";
		if ($collection_ascii_id) {
			$sql .= "
				, {$prefix}item i, {$prefix}collection c
				WHERE v.item_id = i.id
				AND c.id = i.collection_id
				AND c.ascii_id = ?
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
