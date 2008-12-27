<?php

require_once 'Dase/DBO/Autogen/ItemTypeRelation.php';

class Dase_DBO_ItemTypeRelation extends Dase_DBO_Autogen_ItemTypeRelation 
{
	public $child;
	public $parent;

	public function getChild() 
	{
		$this->child = Dase_DBO_ItemType::get($this->collection_ascii_id,$this->child_type_ascii_id);
		return $this->child;
	}

	public function getParent() 
	{
		$this->parent = Dase_DBO_ItemType::get($this->collection_ascii_id,$this->parent_type_ascii_id);
		return $this->parent;
	}

	public function getBaseUri() 
	{
		return 'item_type/'.
			$this->collection_ascii_id.'/'.
			$this->child_type_ascii_id.'/children_of/'.
			$this->parent_type_ascii_id;
	}

	public function getChildCount($parent_serial_number)
	{
		$ir = new Dase_DBO_ItemRelation;
		$ir->item_type_relation_id = $this->id;
		$ir->parent_serial_number = $parent_serial_number;
		return ($ir->findCount());
	}

}
