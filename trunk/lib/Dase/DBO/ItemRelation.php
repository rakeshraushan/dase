<?php

require_once 'Dase/DBO/Autogen/ItemRelation.php';

class Dase_DBO_ItemRelation extends Dase_DBO_Autogen_ItemRelation 
{
	public static function isValid($collection_ascii_id,$parent_serial_number,$child_serial_number)
	{
		$parent = Dase_DBO_Item::get($collection_ascii_id,$parent_serial_number);
		$child = Dase_DBO_Item::get($collection_ascii_id,$child_serial_number);
		$parent_type = $parent->getItemType();
		$child_type = $child->getItemType();
		$itr = new Dase_DBO_ItemTypeRelation;
		$itr->parent_type_ascii_id = $parent_type->ascii_id;
		$itr->child_type_ascii_id = $child_type->ascii_id;
		$itr->collection_ascii_id = $collection_ascii_id;
		return $itr->findCount();
	}

	/** deletes invalid item relations based on current item_types*/
	public static function cleanup($collection_ascii_id,$child_serial_number)
	{
		$item_relations = new Dase_DBO_ItemRelation;
		$item_relations->child_serial_number = $child_serial_number;
		$item_relations->collection_ascii_id = $collection_ascii_id;
		foreach ($item_relations->find() as $item_relation) {
			if (!Dase_DBO_ItemRelation::isValid($collection_ascii_id,$item_relation->parent_serial_number,$child_serial_number)) {
				$item_relation->delete();
			}
		}
	}


	public function getParentType()

	{
		$itr = new Dase_DBO_ItemTypeRelation;
		$itr->load($this->item_type_relation_id);
		$parent_type = $itr->getParent();
		return $parent_type;
	}

}
