<?php

require_once 'Dase/DBO/Autogen/TagItem.php';

class Dase_DBO_TagItem extends Dase_DBO_Autogen_TagItem 
{
	function getItem()
	{
		$item = new Dase_DBO_Item;
		//todo: go with p_coll & p_sernum
		if ($item->load($this->item_id)) {
			$item->getCollection();
			return $item;
		} else {
			return false;
		}
	}

	function getTag()
	{
		$tag = new Dase_DBO_Tag;
		$tag->load($this->tag_id);
		return $tag;
	}

	function persist()
   	{
		$prefix = Dase_Config::get('table_prefix');
		$db = Dase_DB::get();
		$sql = "
			SELECT c.ascii_id as collection_ascii_id,i.serial_number
			FROM {$prefix}tag_item t, {$prefix}collection c, {$prefix}item i
			WHERE i.id = t.item_id
			AND i.collection_id = c.id
			AND t.id = ? 
			";
		$sth = $db->prepare($sql);
		$sth->execute(array($this->id));
		$row = $sth->fetch();
		$this->p_collection_ascii_id = $row['collection_ascii_id'];
		$this->p_serial_number = $row['serial_number'];
		$this->update();
		return $this;
	}

	function asAtom()
	{
		$item = $this->getItem();
		$tag = $this->getTag();
		$feed = new Dase_Atom_Feed;

		$c = $item->getCollection();
		if (is_numeric($item->updated)) {
			$updated = date(DATE_ATOM,$item->updated);
		} else {
			$updated = $item->updated;
		}
		$feed->setUpdated($updated);
		$feed->setTitle($item->getTitle());
		$feed->setId('{APP_ROOT}/tag/item/'.$tag->id.'/'.$this->id);
		$feed->setGenerator('DASe','http://daseproject.org','1.0');
		$feed->addAuthor('DASe (Digital Archive Services)','http://daseproject.org');

		//$feed->addCategory($tag->type,"http://daseproject.org/category/tag_type",$tag->type);
		$feed->addCategory('set',"http://daseproject.org/category/tag_type");
		$feed->addLink($tag->getLink(),"http://daseproject.org/relation/feed-link");
		$tag_item_id_array = $tag->getTagItemIds();
		$position = array_search($this->id,$tag_item_id_array) + 1;
		$feed->addCategory($position,"http://daseproject.org/category/position");

		if (1 == $position) {
			$prev_id = array_pop($tag_item_id_array);
			array_push($tag_item_id_array,$prev_id); //because array_pop shortened array
		} else {
			$prev_id = $tag_item_id_array[$position-2];
		}

		if (isset($tag_item_id_array[$position])) {
			$next_id = $tag_item_id_array[$position];
		} else {
			$next_id = $tag_item_id_array[0];
		}
		//overloading opensearch elements here 
		$feed->setOpensearchTotalResults($tag->item_count);
		$feed->setOpensearchQuery($tag->name);


		//$feed->addLink($tag->getLink().'/'.$prev_id,"previous");
		//$feed->addLink($tag->getLink().'/'.$next_id,"next");
		$feed->addLink('{APP_ROOT}/tag/item/'.$tag->id.'/'.$this->id.'.atom',"self");
		$feed->addLink('{APP_ROOT}/tag/item/'.$tag->id.'/'.$prev_id,"previous");
		$feed->addLink('{APP_ROOT}/tag/item/'.$tag->id.'/'.$next_id,"next");
		$feed->setFeedType('tagitem');
		//tag name goes in subtitle, so doesn't need to be in category
		$feed->setSubtitle($tag->name.' '.$position.' of '.count($tag_item_id_array));
		$entry = $item->injectAtomEntryData($feed->addEntry());
		$entry->setSummary($this->annotation);
		return $feed->asXml();
	}

}
