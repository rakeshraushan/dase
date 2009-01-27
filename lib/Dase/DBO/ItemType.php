<?php

require_once 'Dase/DBO/Autogen/ItemType.php';

class Dase_DBO_ItemType extends Dase_DBO_Autogen_ItemType 
{
	public $attributes;
	public $collection;
	public $parents = array();
	public $children = array();

	public static function get($collection_ascii_id,$ascii_id)
	{
		if ($collection_ascii_id && $ascii_id) {
			$item_type = new Dase_DBO_ItemType;
			$item_type->ascii_id = $ascii_id;
			$item_type->collection_id = Dase_DBO_Collection::get($collection_ascii_id)->id;
			return($item_type->findOne());
		} else {
			throw new Exception('missing a method parameter value');
		}
	}

	public static function findOrCreate($collection_ascii_id,$ascii_id) 
	{
		$type = new Dase_DBO_ItemType;
		$type->collection_id = Dase_DBO_Collection::get($collection_ascii_id)->id;
		$type->ascii_id = $ascii_id;
		if (!$type->findOne()) {
			$type->name = ucwords(str_replace('_',' ',$ascii_id));
			$type->insert();
		}
		return $type;
	}

	public function getRelativeUrl($coll='')
	{
		if (!$coll) {
			$coll = $this->getCollection()->ascii_id;
		}
		return 'item_type/'.$coll.'/'.$this->ascii_id;
	}

	public function asAtomEntry()
	{
		$c = $this->getCollection();
		$entry = new Dase_Atom_Entry_ItemType;
		$entry = $this->injectAtomEntryData($entry,$c);
		return $entry->asXml();
	}

	function injectAtomEntryData(Dase_Atom_Entry $entry,$collection)
	{
		$app_root = Dase_Config::get('app_root');
		$base_url = $app_root.'/'.$this->getRelativeUrl($collection->ascii_id);
		$entry->setTitle($this->name);
		$entry->setId($base_url);
		$entry->setSummary($this->description);
		$entry->addLink($base_url.'.atom','edit');
		$entry->addLink($base_url.'/items.cats','http://daseproject.org/relation/item_type/items','application/atomcat+xml','',$this->name.' Items');
		$entry->addLink($base_url.'/attributes.cats','http://daseproject.org/relation/item_type/attributes','application/atomcat+xml','',$this->name.' Attributes');
		$entry->addLink($base_url.'/attributes.atom','http://daseproject.org/relation/item_type/attributes','application/atom+xml','',$this->name.' Attributes');
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

	function getCollection() {

		if ($this->collection) {
			return $this->collection;
		}
		$c = new Dase_DBO_Collection;
		$c->load($this->collection_id);
		$this->collection = $c;
		return $c;
	}

	function getAtts() {
		//for lazy load from smarty (since there is an 'attributes' member
		return $this->getAttributes();
	}

	function getAttributes()
	{
		//todo: fix this!!
		$attributes = array();
		$att_it = new Dase_DBO_AttributeItemType;
		$att_it->item_type_id = $this->id;
		foreach($att_it->find() as $ait) {
			$att = new Dase_DBO_Attribute;
			$att->load($ait->attribute_id);
			$attributes[] = $att;
		}
		$this->attributes = $attributes;
		return $attributes;
	}

	function getItemsCount() {
		$i = new Dase_DBO_Item;
		$i->item_type_id = $this->id;
		return $i->findCount();
	}

	function getAttributesFeed() 
	{
		$c = $this->getCollection();
		$feed = new Dase_Atom_Feed;
		$feed->setTitle($this->name.' Attributes');
		$feed->setId(APP_ROOT . '/item_type/'. $c->ascii_id . '/' . $this->ascii_id.'/attributes');
		$feed->setUpdated(date(DATE_ATOM));
		foreach($this->getAttributes() as $att) {
			$entry = $feed->addEntry('attribute');
			$att->injectAtomEntryData($entry);
		}
		return $feed->asXml();
	}

	function getAttributesJson() 
	{
		$atts = array();
		foreach ($this->getAttributes() as $att) {
			$a['ascii_id'] = $att->ascii_id;
			$a['attribute_name'] = $att->attribute_name;
			$a['href'] = $att->getRelativeUrl();
			$atts[] = $a;
		}
		return Dase_Json::get($atts);
	}

	function getParentRelations()
	{
		$rel = new Dase_DBO_ItemTypeRelation;
		$rel->child_type_ascii_id = $this->ascii_id;
		$rel->collection_ascii_id = $this->getCollection()->ascii_id;
		foreach ($rel->find() as $r) {
			$this->parents[] = clone $r;
		}
		return $this->parents;
	}

	function getChildRelations()
	{
		$rel = new Dase_DBO_ItemTypeRelation;
		$rel->parent_type_ascii_id = $this->ascii_id;
		$rel->collection_ascii_id = $this->getCollection()->ascii_id;
		foreach ($rel->find() as $r) {
			$this->children[] = clone $r;
		}
		return $this->children;
	}

	function expunge()
	{
		if (!$this->id || !$this->ascii_id) {
			throw new Exception('cannot delete unspecified type');
		}
		$ait = new Dase_DBO_AttributeItemType;
		$ait->item_type_id = $this->id;
		foreach ($ait->find() as $doomed) {
			Dase_Log::info('deleted attribute_item_type '.$doomed->id);
			$doomed->delete();
		}
		$itr = new Dase_DBO_ItemTypeRelation;
		$itr->parent_type_ascii_id = $this->id;
		foreach ($itr->find() as $doomed_rel) {
			Dase_Log::info('deleted item_type_relation '.$doomed->id);
			$doomed_rel->expunge();
		}
		$itr = new Dase_DBO_ItemTypeRelation;
		$itr->child_type_ascii_id = $this->id;
		foreach ($itr->find() as $doomed_rel) {
			Dase_Log::info('deleted item_type_relation '.$doomed->id);
			$doomed_rel->expunge();
		}
		$this->delete();
	}
	
	public function getAtompubServiceDoc() 
	{
		$c = $this->getCollection();
		$svc = new Dase_Atom_Service;	
		$ws = $svc->addWorkspace($this->name.' Item Type Workspace');
		$coll = $ws->addCollection(APP_ROOT.'/item_type/'.$c->ascii_id.'/'.$this->ascii_id.'.atom',$this->name.' Items');
		$coll->addAccept('application/atom+xml;type=entry');
		$coll->addCategorySet()->addCategory('item','http://daseproject.org/category/entrytype');
		$atts = $coll->addCategorySet('yes','http://daseproject.org/category/metadata');
		foreach ($this->getAttributes() as $att) {
			$atts->addCategory($att->ascii_id,'',$att->attribute_name);
		}
		return $svc->asXml();
	}
}
