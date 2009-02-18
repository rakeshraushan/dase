<?php

require_once 'Dase/DBO/Autogen/ItemRelation.php';

class Dase_DBO_ItemRelation extends Dase_DBO_Autogen_ItemRelation 
{
	public static function removeParents($db,$collection_ascii_id,$child_serial_number)
	{
		$ir = new Dase_DBO_ItemRelation($db);
		$ir->child_serial_number = $child_serial_number;
		$ir->collection_ascii_id = $collection_ascii_id;
		foreach ($ir->find() as $doomed) {
			$doomed->delete();
		}
	}

	public function getParentType($db)

	{
		$itr = new Dase_DBO_ItemTypeRelation($db);
		$itr->load($this->item_type_relation_id);
		$parent_type = $itr->getParentType();
		return $parent_type;
	}

	public function saveParentAtom($db)
	{
		return Dase_DBO_Item::get($db,$this->collection_ascii_id,$this->parent_serial_number)->saveAtom();
	}

	public function saveChildAtom($db)
	{
		return Dase_DBO_Item::get($db,$this->collection_ascii_id,$this->child_serial_number)->saveAtom();
	}

	public function getParent()
	{
		return Dase_DBO_Item::get($db,$this->collection_ascii_id,$this->parent_serial_number);
	}

	public function getChild()
	{
		return Dase_DBO_Item::get($db,$this->collection_ascii_id,$this->child_serial_number);
	}
}
