<?php

require_once 'Dase/DBO/Autogen/ItemAsAtom.php';

class Dase_DBO_ItemAsAtom extends Dase_DBO_Autogen_ItemAsAtom 
{
	public static function getByItem($item)
	{   
		$atom = new Dase_DBO_ItemAsAtom($item->db);
		$atom->item_id = $item->id;
		$atom->app_root = '{APP_ROOT}';
		return $atom->findOne();
	}

	public static function expire($db,$item_id) 
	{
		$atom = new Dase_DBO_ItemAsAtom($db);
		$atom->item_id = $item_id;
		if ($atom->findOne()) {
			$atom->delete();
		}
	}

	public function getConvertedXml($app_root)
	{
		return str_replace('{APP_ROOT}',$app_root,$this->xml);
	}
}
