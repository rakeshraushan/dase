<?php

require_once 'Dase/DBO/Autogen/ItemTypeRelation.php';

class Dase_DBO_ItemTypeRelation extends Dase_DBO_Autogen_ItemTypeRelation 
{
	public $child;
	public $parent;

	public static function get($collection_ascii_id,$ascii_id)
	{
		$itr = new Dase_DBO_ItemTypeRelation;
		$itr->collection_ascii_id = $collection_ascii_id;
		list($child_ascii,$parent_ascii) = explode('_children_of_',str_replace('.atom','',$ascii_id));
		$itr->child_type_ascii_id = $child_ascii;
		$itr->parent_type_ascii_id = $parent_ascii;
		return $itr->findOne();
	}

	public static function getByItemSerialNumbers($collection_ascii_id,$parent_sernum,$child_sernum)
	{
		$p = Dase_DBO_Item::get($collection_ascii_id,$parent_sernum);
		$c = Dase_DBO_Item::get($collection_ascii_id,$child_sernum);
		if (!$c || !$p) { return false; }
		$ptype = $p->getItemType();
		$ctype = $c->getItemType();
		$itr = new Dase_DBO_ItemTypeRelation;
		$itr->collection_ascii_id = $collection_ascii_id;
		$itr->parent_type_ascii_id = $ptype->ascii_id;
		$itr->child_type_ascii_id = $ctype->ascii_id;
		return $itr->findOne();
	}

	public function getChildType() 
	{
		$this->child = Dase_DBO_ItemType::get($this->collection_ascii_id,$this->child_type_ascii_id);
		return $this->child;
	}

	public function getParentType() 
	{
		$this->parent = Dase_DBO_ItemType::get($this->collection_ascii_id,$this->parent_type_ascii_id);
		return $this->parent;
	}

	public function getRelativeUrl() 
	{
		return 'item_type/'.
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
		$app_root = Dase_Config::get('app_root');
		$coll = $this->collection_ascii_id;
		$base_url = $this->getRelativeUrl();
		$entry->setTitle('Item Type Relation: '.$this->title);
		$entry->setId($base_url);
		$entry->setSummary($this->description);
		$entry->addLink($base_url.'.atom','edit');
		$entry->addLink($app_root.'/'.$this->getChild()->getRelativeUrl($coll),'http://daseproject.org/relation/child_type');
		$entry->addLink($app_root.'/'.$this->getParent()->getRelativeUrl($coll),'http://daseproject.org/relation/parent_type');
		$entry->addCategory('item_type_relation','http://daseproject.org/category/entrytype','Item Type Relation');
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

	public function updateAtomCache()
	{
		//very expensive, may want to just delete atom cache for each
		$ir = new Dase_DBO_ItemRelation;
		$ir->item_type_relation_id = $this->id;
		$i = 0;
		foreach ($ir->find() as $item_rel) {
			$i++;
			$item_rel->saveParentAtom();
			$item_rel->saveChildAtom();
		}
		Dase_Log::debug('updated atom caches for '.$i.' items');
	}
}
