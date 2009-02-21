<?php

require_once 'Dase/DBO/Autogen/ItemType.php';

class Dase_DBO_ItemType extends Dase_DBO_Autogen_ItemType 
{
	public $attributes;
	public $collection;
	public $parents = array();
	public $children = array();

	public static function get($db,$collection_ascii_id,$ascii_id)
	{
		if ($collection_ascii_id && $ascii_id) {
			$item_type = new Dase_DBO_ItemType($db);
			$item_type->ascii_id = $ascii_id;
			$item_type->collection_id = Dase_DBO_Collection::get($db,$collection_ascii_id)->id;
			return($item_type->findOne());
		} else {
			throw new Exception('missing a method parameter value');
		}
	}

	public static function findOrCreate($db,$collection_ascii_id,$ascii_id) 
	{
		$type = new Dase_DBO_ItemType($db);
		$type->collection_id = Dase_DBO_Collection::get($db,$collection_ascii_id)->id;
		$type->ascii_id = $ascii_id;
		if (!$type->findOne()) {
			$type->name = ucwords(str_replace('_',' ',$ascii_id));
			$type->insert();
		}
		return $type;
	}

	public function getUrl($collection_ascii_id,$app_root)
	{
		return $app_root.'/item_type/'.$collection_ascii_id.'/'.$this->ascii_id;
	}

	public function asAtomEntry($collection_ascii_id,$app_root)
	{
		$c = $this->getCollection();
		$entry = new Dase_Atom_Entry_ItemType($this->db);
		$entry = $this->injectAtomEntryData($entry,$collection_ascii_id,$app_root);
		return $entry->asXml();
	}

	function injectAtomEntryData(Dase_Atom_Entry $entry,$collection_ascii_id,$app_root)
	{
		$entry->setTitle($this->name);
		$entry->setId($base_url);
		$entry->setSummary($this->description);
		$entry->addLink($this->getUrl($collection_ascii_id,$app_root).'.atom','edit');
		$entry->addLink($this->getUrl($collection_ascii_id,$app_root).'/items.cats','http://daseproject.org/relation/item_type/items','application/atomcat+xml','',$this->name.' Items');
		$entry->addLink($this->getUrl($collection_ascii_id,$app_root).'/attributes.cats','http://daseproject.org/relation/item_type/attributes','application/atomcat+xml','',$this->name.' Attributes');
		$entry->addLink($this->getUrl($collection_ascii_id,$app_root).'/attributes.atom','http://daseproject.org/relation/item_type/attributes','application/atom+xml','',$this->name.' Attributes');
		$entry->addCategory('item_type','http://daseproject.org/category/entrytype','Item Type');
		if (is_numeric($this->updated)) {
			$updated = date(DATE_ATOM,$this->updated);
		} else {
			$updated = $this->updated;
		}
		$entry->setUpdated($updated);
		$entry->addAuthor();
		return $entry;
	}

	public function getCollection() {

		if ($this->collection) {
			return $this->collection;
		}
		$c = new Dase_DBO_Collection($this->db);
		$c->load($this->collection_id);
		$this->collection = $c;
		return $c;
	}

	public function getAtts() {
		//for lazy load from smarty (since there is an 'attributes' member
		return $this->getAttributes();
	}

	public function getAttributes()
	{
		//todo: fix this!!
		$attributes = array();
		$att_it = new Dase_DBO_AttributeItemType($this->db);
		$att_it->item_type_id = $this->id;
		foreach($att_it->find() as $ait) {
			$att = new Dase_DBO_Attribute($this->db);
			$att->load($ait->attribute_id);
			$attributes[] = $att;
		}
		$this->attributes = $attributes;
		return $attributes;
	}

	public function getItemsCount() {
		$i = new Dase_DBO_Item($this->db);
		$i->item_type_id = $this->id;
		return $i->findCount();
	}

	public function getAttributesFeed($collection_ascii_id,$app_root) 
	{
		$feed = new Dase_Atom_Feed;
		$feed->setTitle($this->name.' Attributes');
		$feed->setId($app_root.'/item_type/'. $collection_ascii_id . '/' . $this->ascii_id.'/attributes');
		$feed->setUpdated(date(DATE_ATOM));
		foreach($this->getAttributes() as $att) {
			$entry = $feed->addEntry('attribute');
			$att->injectAtomEntryData($entry,$collection_ascii_id,$app_root);
		}
		return $feed->asXml();
	}

	public function getAttributesJson($collection_ascii_id,$app_root) 
	{
		$atts = array();
		foreach ($this->getAttributes() as $att) {
			$a['ascii_id'] = $att->ascii_id;
			$a['attribute_name'] = $att->attribute_name;
			$a['href'] = $att->getUrl($collection_ascii_id,$app_root);
			$atts[] = $a;
		}
		return Dase_Json::get($atts);
	}

	public function getParentRelations()
	{
		$rel = new Dase_DBO_ItemTypeRelation($this->db);
		$rel->child_type_ascii_id = $this->ascii_id;
		$rel->collection_ascii_id = $this->getCollection()->ascii_id;
		foreach ($rel->find() as $r) {
			$this->parents[] = clone $r;
		}
		return $this->parents;
	}

	public function getChildRelations()
	{
		$rel = new Dase_DBO_ItemTypeRelation($this->db);
		$rel->parent_type_ascii_id = $this->ascii_id;
		$rel->collection_ascii_id = $this->getCollection()->ascii_id;
		foreach ($rel->find() as $r) {
			$this->children[] = clone $r;
		}
		return $this->children;
	}

	public function expunge()
	{
		if (!$this->id || !$this->ascii_id) {
			throw new Exception('cannot delete unspecified type');
		}
		$ait = new Dase_DBO_AttributeItemType($this->db);
		$ait->item_type_id = $this->id;
		foreach ($ait->find() as $doomed) {
			Dase_Log::get()->info('deleted attribute_item_type '.$doomed->id);
			$doomed->delete();
		}
		$itr = new Dase_DBO_ItemTypeRelation($this->db);
		$itr->parent_type_ascii_id = $this->id;
		foreach ($itr->find() as $doomed_rel) {
			Dase_Log::get()->info('deleted item_type_relation '.$doomed->id);
			$doomed_rel->expunge();
		}
		$itr = new Dase_DBO_ItemTypeRelation($this->db);
		$itr->child_type_ascii_id = $this->id;
		foreach ($itr->find() as $doomed_rel) {
			Dase_Log::get()->info('deleted item_type_relation '.$doomed->id);
			$doomed_rel->expunge();
		}
		$this->delete();
	}
	
	public function getAtompubServiceDoc($app_root) 
	{
		$c = $this->getCollection();
		$svc = new Dase_Atom_Service;	
		$ws = $svc->addWorkspace($this->name.' Item Type Workspace');
		$coll = $ws->addCollection($app_root.'/item_type/'.$c->ascii_id.'/'.$this->ascii_id.'.atom',$this->name.' Items');
		$coll->addAccept('application/atom+xml;type=entry');
		$coll->addCategorySet()->addCategory('item','http://daseproject.org/category/entrytype');
		$atts = $coll->addCategorySet('yes','http://daseproject.org/category/metadata');
		foreach ($this->getAttributes() as $att) {
			$atts->addCategory($att->ascii_id,'',$att->attribute_name);
		}
		return $svc->asXml();
	}
}
