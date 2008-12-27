<?php

require_once 'Dase/DBO/Autogen/ItemRelation.php';

class Dase_DBO_ItemRelation extends Dase_DBO_Autogen_ItemRelation 
{
	public function getParentType()
	{
		$itr = new Dase_DBO_ItemTypeRelation;
		$itr->load($this->item_type_relation_id);
		$parent_type = $itr->getParent();
		return $parent_type;
	}

}
