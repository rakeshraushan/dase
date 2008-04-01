<?php

require_once 'Dase/DBO/Autogen/TagItem.php';

class Dase_DBO_TagItem extends Dase_DBO_Autogen_TagItem 
{
	function getItem()
	{
		$item = new Dase_DBO_Item;
		$item->load($this->item_id);
		$item->getCollection();
		return $item;
	}

	function getTag()
	{
		$tag = new Dase_DBO_Tag;
		$tag->load($this->tag_id);
		return $tag;
	}

	function asAtom()
	{
		$item = $this->getItem();
		$tag = $this->getTag();
		$tag_type = $tag->getType();
		$feed = new Dase_Atom_Feed;
		$item->injectAtomFeedData($feed);
		$feed->addCategory($tag_type->ascii_id,"http://daseproject.org/category/tag_type",$tag_type->name);
		$feed->addLink($tag->getLink(),"http://daseproject.org/relation/feed-link");

		$tag_item_id_array = $tag->getTagItemIds();
		$place = array_search($this->id,$tag_item_id_array) + 1;

		if (1 == $place) {
			$prev_id = array_pop($tag_item_id_array);
			array_push($tag_item_id_array,$prev_id); //because array_pop shortened array
		} else {
			$prev_id = $tag_item_id_array[$place-2];
		}

		if (isset($tag_item_id_array[$place])) {
			$next_id = $tag_item_id_array[$place];
		} else {
			$next_id = $tag_item_id_array[0];
		}

		$feed->addLink($tag->getLink().'/'.$prev_id,"previous");
		$feed->addLink($tag->getLink().'/'.$next_id,"next");
		//tag name goes in subtitle, so doesn't need to be in category
		$feed->setSubtitle($tag->name.' '.$place.' of '.count($tag_item_id_array));
		$entry = $item->injectAtomEntryData($feed->addEntry());
		//todo: atompub edit link.  for now (3/31/08) user must 'tag' an item
		//in order for it to be eidtable
		$edit_link = (str_replace(APP_ROOT,APP_ROOT.'/edit',$entry->getId()));
		$entry->addLink($edit_link,'edit');
		return $feed->asXml();
	}

}
