<?php

require_once 'Dase/DBO/Autogen/ItemType.php';

class Dase_DBO_ItemType extends Dase_DBO_Autogen_ItemType 
{
	public $attributes;

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

	function injectAtomEntryData(Dase_Atom_Entry $entry,$collection)
	{
		$entry->setTitle('Item Type '.$this->name);
		$entry->setId(APP_ROOT.'/item_type/'.$collection->ascii_id.'/'.$this->ascii_id);
		$entry->addCategory('item_type','http://daseproject.org/category/entrytype','Item Type');
		if (is_numeric($this->updated)) {
			$updated = date(DATE_ATOM,$this->updated);
		} else {
			$updated = $this->updated;
		}
		$entry->setUpdated($updated);
		$entry->addAuthor('ss');
		$div = simplexml_import_dom($entry->setContent());
		$dl = $div->addChild('dl');
		foreach ($this as $k => $v) {
			$dt = $dl->addChild('dt',$k);
			$dd = $dl->addChild('dd',$v);
			$dd->addAttribute('class',$k);
		}
		return $entry;
	}

	function getCollection() {
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
		$attributes = array();
		$att_it = new Dase_DBO_AttributeItemType;
		$att_it->item_type_id = $this->id;
		foreach($att_it->find() as $ait) {
			$att = new Dase_DBO_Attribute;
			$att->load($ait->attribute_id);
			$att->cardinality = $ait->cardinality; 
			$attributes[] = $att;
		}
		$this->attributes = $attributes;
		return $attributes;
	}

	function getItems() {
		$i = new Dase_DBO_Item;
		$i->item_type_id = $this->id;
		return $i->find();
	}

	function getItemsCount() {
		$i = new Dase_DBO_Item;
		$i->item_type_id = $this->id;
		return $i->findCount();
	}

	function getItemsAsFeed() 
	{
		$c = $this->getCollection();
		$feed = new Dase_Atom_Feed;
		$feed->setTitle($this->name);
		$feed->addAuthor();
		if ($this->description) {
			$feed->setSubtitle($this->description);
		}
		$feed->setId(APP_ROOT . '/collection/'. $c->ascii_id . '/item_type/' . $this->ascii_id);
		$feed->setUpdated(date(DATE_ATOM));
		//figure out public/private tag thing (and whether token is needed)
		$feed->addLink(APP_ROOT . '/atom/collection/' . $c->ascii_id . '/item_type/' . $this->ascii_id,'self');
		$feed->addCategory($c->ascii_id,"http://daseproject.org/category/collection",$c->name);

		foreach($this->getItems() as $item) {
			$entry = $feed->addEntry();
			$item->injectAtomEntryData($entry);
		}
		return $feed->asXml();
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
		$this->delete();
	}
}
