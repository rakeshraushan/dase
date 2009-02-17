<?php

require_once 'Dase/DBO/Autogen/ItemAsAtom.php';

class Dase_DBO_ItemAsAtom extends Dase_DBO_Autogen_ItemAsAtom 
{
	public static function getByItem($item)
	{   
		$atom = new Dase_DBO_ItemAsAtom($item->db);
		$atom->item_id = $item->id;
		return $atom->findOne();
	}
}
