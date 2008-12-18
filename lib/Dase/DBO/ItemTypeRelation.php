<?php

require_once 'Dase/DBO/Autogen/ItemTypeRelation.php';

class Dase_DBO_ItemTypeRelation extends Dase_DBO_Autogen_ItemTypeRelation 
{
	public $child;
	public $parent;

	public function getChild() 
	{
		$c = new Dase_DBO_ItemType;
		$this->child = $c->load($this->child_type_id);
		return $c;
	}

	public function getParent() 
	{
		$p = new Dase_DBO_ItemType;
		$this->parent = $p->load($this->parent_type_id);
		return $p;
	}

}
