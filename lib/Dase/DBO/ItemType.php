<?php

require_once 'Dase/DBO/Autogen/ItemType.php';

class Dase_DBO_ItemType extends Dase_DBO_Autogen_ItemType 
{
	public $attributes;

	function getAttributes()
	{
		$attributes = array();
		$att_it = new Dase_DBO_AttributeItemType;
		$att_it->item_type_id = $this->id;
		foreach($att_it->find() as $ait) {
			$att = new Dase_DBO_Attribute;
			$att->load($ait->attribute_id);
			$att->cardinality = $ait->cardinality; 
			$att->is_identifier = $ait->is_identifier; 
			$attributes[] = $att;
		}
		$this->attributes = $attributes;
		return $attributes;
	}
}
