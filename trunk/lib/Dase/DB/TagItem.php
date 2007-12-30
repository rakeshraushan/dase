<?php

require_once 'Dase/DB/Autogen/TagItem.php';

class Dase_DB_TagItem extends Dase_DB_Autogen_TagItem 
{
	private $item;

	function getItem() {
		if ($this->item) {
			return $this->item;
		} else {
			$item = new Dase_DB_Item;
			$item->load($this->item_id);
			$item->getCollection();
			$this->item = $item;
			return $item;
		}
	}

}
