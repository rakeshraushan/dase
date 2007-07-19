<?php

require_once 'Dase/DB/Autogen/ItemType.php';

class Dase_DB_ItemType extends Dase_DB_Autogen_ItemType 
{
	public $attributes;

	function getAttributes() {
		$attributes = array();
		$att_it = new Dase_DB_AttributeItemType;
		$att_it->item_type_id = $this->id;
		foreach($att_it->findAll() as $res) {
			$att = new Dase_DB_Attribute;
			$att->load($res['attribute_id']);
			$attributes[] = $att;
		}
		$this->attributes = $attributes;
		return $attributes;
	}
}
