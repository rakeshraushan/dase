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

	public function getBaseUrl() 
	{
		return APP_ROOT.'/item_type/'.
			$this->collection_ascii_id.'/'.
			$this->child_type_ascii_id.'/children_of/'.
			$this->parent_type_ascii_id;
	}

	public function expunge()
	{
		$item_relations = new Dase_DBO_ItemRelation;
		$item_relations->item_type_relation_id = $this->id;
		foreach ($item_relations as $doomed) {
			$doomed->delete();
		}
	}

	public function asAtomEntry()
	{
		$entry = new Dase_Atom_Entry;
		$entry = $this->injectAtomEntryData($entry);
		return $entry->asXml();
	}

	function injectAtomEntryData(Dase_Atom_Entry $entry)
	{
		$base_url = $this->getBaseUrl();
		$entry->setTitle('Item Type Relation: '.$this->title);
		$entry->setId($base_url);
		$entry->setSummary($this->description);
		$entry->addLink($base_url.'.atom','edit');
		$entry->addLink($this->getChild()->getBaseUrl(),'http://daseproject.org/relation/child_type');
		$entry->addLink($this->getParent()->getBaseUrl(),'http://daseproject.org/relation/parent_type');
		$entry->addCategory('item_type','http://daseproject.org/category/entrytype','Item Type Relation');
		$entry->setUpdated(date(DATE_ATOM));
		$entry->addAuthor();
		return $entry;
	}


	public function getChildCount($parent_serial_number)
	{
		$ir = new Dase_DBO_ItemRelation;
		$ir->item_type_relation_id = $this->id;
		$ir->parent_serial_number = $parent_serial_number;
		return ($ir->findCount());
	}
}
