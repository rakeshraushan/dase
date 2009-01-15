<?php

class Dase_Atom_Entry_ItemTypeRelation extends Dase_Atom_Entry
{
	function __construct($dom=null,$root=null)
	{
		parent::__construct($dom,$root);
	}

	function __get($var) {
		//allows smarty to invoke function as if getter
		$classname = get_class($this);
		$method = 'get'.ucfirst($var);
		if (method_exists($classname,$method)) {
			return $this->{$method}();
		} else {
			return parent::__get($var);
		}
	}

	function getChildType()
	{
		return $this->getLink('http://daseproject.org/relation/child_type');
	}

	function getParentType()
	{
		return $this->getLink('http://daseproject.org/relation/parent_type');
	}

	function insert($r,$collection) 
	{
		$itr = new Dase_DBO_ItemTypeRelation;
		$itr->collection_ascii_id = $collection->ascii_id;
		$itr->child_type_ascii_id = array_pop(explode('/',$this->getChildType));
		$itr->parent_type_ascii_id = array_pop(explode('/',$this->getParentType));
		if (!$itr->findOne()) {
			$itr->title = $this->getTitle();
			$itr->insert();
			return $itr;
		} else {
			throw new Dase_Exception('item type relation exists');
		}
	}

}
