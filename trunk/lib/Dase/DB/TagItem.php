<?php

require_once 'Dase/DB/Autogen/TagItem.php';

class Dase_DB_TagItem extends Dase_DB_Autogen_TagItem 
{
	function getItem() {
		$item = new Dase_DB_Item;
		$item->load($this->item_id);
		$item->getCollection();
		return $item;
	}
}
