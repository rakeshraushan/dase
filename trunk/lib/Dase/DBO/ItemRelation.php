<?php

require_once 'Dase/DBO/Autogen/ItemRelation.php';

class Dase_DBO_ItemRelation extends Dase_DBO_Autogen_ItemRelation 
{
	public static function removeParents($collection_ascii_id,$child_serial_number)
	{
		$ir = new Dase_DBO_ItemRelation;
		$ir->child_serial_number = $child_serial_number;
		$ir->collection_ascii_id = $collection_ascii_id;
		foreach ($ir->find() as $doomed) {
			$doomed->delete();
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
