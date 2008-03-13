<?php

require_once 'Dase/DBO/Autogen/TagItem.php';

class Dase_DBO_TagItem extends Dase_DBO_Autogen_TagItem 
{
	function getItem() {
		$item = new Dase_DBO_Item;
		$item->load($this->item_id);
		$item->getCollection();
		return $item;
	}
}
