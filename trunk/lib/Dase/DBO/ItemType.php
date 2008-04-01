<?php

require_once 'Dase/DBO/Autogen/ItemType.php';

class Dase_DBO_ItemType extends Dase_DBO_Autogen_ItemType 
{
	public $attributes;

	public static function get($ascii_id) {
		if (!$ascii_id) {
			return false;
		}
		$item_type = new Dase_DBO_ItemType;
		$item_type->ascii_id = $ascii_id;
		return $item_type->findOne();
	}

	function getCollection() {
		$c = new Dase_DBO_Collection;
		$c->load($this->collection_id);
		return $c;
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
			$att->is_identifier = $ait->is_identifier; 
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
}
