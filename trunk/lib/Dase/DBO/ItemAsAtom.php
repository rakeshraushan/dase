<?php

require_once 'Dase/DBO/Autogen/ItemAsAtom.php';

class Dase_DBO_ItemAsAtom extends Dase_DBO_Autogen_ItemAsAtom 
{
	public static function getByItemId($id)
	{   
		$atom = new Dase_DBO_ItemAsAtom;
		$atom->item_id = $id;
		$atom->app_root = Dase_Config::get('app_root');
		return $atom->findOne();
	}
}
