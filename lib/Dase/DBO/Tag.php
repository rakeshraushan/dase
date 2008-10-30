<?php

require_once 'Dase/DBO/Autogen/Tag.php';

class Dase_DBO_Tag extends Dase_DBO_Autogen_Tag 
{
	private $user;

	public static function getByUser($user)
	{
		$prefix = Dase_Config::get('table_prefix');
		$sql = "
			SELECT * 
			FROM {$prefix}tag 
			WHERE dase_user_id = ?
			";
		$tags = array();
		foreach (Dase_DBO::query($sql,array($user->id))->fetchAll() as $row) { 
			$row['count'] = $row['item_count'];
			$tags[] = $row;
		}
		return $tags;
	}

	public static function create($tag_name,$user)
	{
		$tag = new Dase_DBO_Tag;
		$tag->ascii_id = Dase_Util::dirify($tag_name);
		$tag->dase_user_id = $user->id;
		if ($tag->findOne()) {
			return false;
		} else {
			$user->expireDataCache();
			$tag->name = $tag_name;
			$tag->type = 'set';
			$tag->background = 'white';
			$tag->is_public = 0;
			$tag->eid = $user->eid;
			$tag->created = date(DATE_ATOM);
			$tag->insert();
			return $tag;
		}
	}

	public static function get($ascii_id,$eid)
	{
		$user = Dase_DBO_DaseUser::get($eid);
		$tag = new Dase_DBO_Tag;
		$tag->ascii_id = $ascii_id;
		$tag->dase_user_id = $user->id;
		$tag->findOne();
		if ($tag->id) {
			return $tag;
		} else {
			return false;
		}
	}

	/** be careful w/ this -- we do not archive before deleting */
	function expunge()
	{
		if (!$this->id) {
			throw new Exception("invalid");
		} 
		$tag_items = new Dase_DBO_TagItem;
		$tag_items->tag_id = $this->id;

		if ($tag_items->findCount() > 50) {
			throw new Exception("dangerous-looking tag deletion (more than 50 tag items)");
		} 
		foreach ($tag_items->find() as $doomed_tag_item) {
			$doomed_tag_item->delete();
		}
		$this->delete();
	}

	function updateItemCount()
	{
		$tag_items = new Dase_DBO_TagItem;
		$tag_items->tag_id = $this->id;
		$this->item_count = $tag_items->findCount();
		$this->updated = date(DATE_ATOM);
		//postgres boolean weirdness make this necessary
		if (!$this->is_public) {
			$this->is_public = 0;
		}
		$this->update();
	}

	function getTagItemIds()
	{
		$prefix = Dase_Config::get('table_prefix');
		$db = Dase_DB::get();
		$sql = "
			SELECT id 
			FROM {$prefix}tag_item 
			where tag_id = ?
			ORDER BY sort_order
			";
		$st = $db->prepare($sql);
		$st->execute(array($this->id));
		return $st->fetchAll(PDO::FETCH_COLUMN);
	}

	function getItemUniques()
	{
		$uniqs = array();
		foreach ($this->getTagItems() as $ti) {
			$uniqs[] = $ti->p_collection_ascii_id.'/'.$ti->p_serial_number;
		}
		return $uniqs;
	}

	function getUpdated()
	{
		$tag_item = new Dase_DBO_TagItem;
		$tag_item->tag_id = $this->id;
		$tag_item->orderBy('updated DESC');
		$tag_item->findOne();
		return $tag_item->updated;
	}

	function getTagItems()
	{
		$tag_item = new Dase_DBO_TagItem;
		$tag_item->tag_id = $this->id;
		$tag_item->orderBy('sort_order');
		return $tag_item->find();
	}

	function resortTagItems($dir='DESC')
	{
		$tag_item = new Dase_DBO_TagItem;
		$tag_item->tag_id = $this->id;
		$tag_item->orderBy('sort_order, updated '.$dir);
		$i = 0;
		foreach ($tag_item->find() as $ti) {
			$i++;
			$ti->sort_order = $i;
			$ti->updated = date(DATE_ATOM);
			$ti->update();
		}
	}

	/** this is for the slideshow sorter */
	function sort($sort_array)
	{
		if (!count($sort_array)) {
			return;
		}
		//reconstitute set w/ original order
		$orig_set = array();
		foreach ($this->getTagItems() as $ti) {
			$orig_set[] = clone $ti;
		}

		foreach ($sort_array as $old_position => $new_position) {
			$tag_item = $orig_set[$old_position-1];
			$tag_item->sort_order = $new_position;
			$tag_item->update();
			unset($orig_set[$old_position-1]); //remove from array
		}
		$sort_order = 0;
		foreach ($orig_set as $ti) {
			$sort_order++;
			//skip sort_orders that were used in a change
			while (in_array($sort_order,$sort_array)) {
				$sort_order++;
			}
			$ti->sort_order = $sort_order;
			$ti->update();
		}
	}

	function getType()
	{
		//for compat
		return $this->type;
	}

	function getUser()
	{
		$user = new Dase_DBO_DaseUser;
		$this->user = $user->load($this->dase_user_id);
		return $this->user;
	}

	function getLink() 
	{
		$this->user || $this->getUser(); 
		return APP_ROOT . '/tag/' . $this->user->eid . '/' . $this->ascii_id;
	}

	function addItem($item_unique,$updateCount=false)
	{
		$tag_item = new Dase_DBO_TagItem;
		$tag_item->tag_id = $this->id;
		list ($coll,$sernum) = explode('/',$item_unique);

		//todo: compat (but handy anyway)
		$item = Dase_DBO_Item::get($coll,$sernum);
		$tag_item->item_id = $item->id;

		$tag_item->p_collection_ascii_id = $coll;
		$tag_item->p_serial_number = $sernum;

		try {
			$tag_item->insert();
			//this is too expensive when many items are being added in one request
			if ($updateCount) {
				$this->updateItemCount();
			}
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	function removeItem($item_unique,$update_count=false)
	{
		$tag_item = new Dase_DBO_TagItem;
		$tag_item->tag_id = $this->id;
		list ($coll,$sernum) = explode('/',$item_unique);

		//todo: compat
		$item = Dase_DBO_Item::get($coll,$sernum);
		$tag_item->item_id = $item->id;

		$tag_item->p_collection_ascii_id = $coll;
		$tag_item->p_serial_number = $sernum;
		if ($tag_item->findOne()) {
			$tag_item->delete();
			//this is too expensive when many items are being removed in one request
			if ($update_count) {
				$this->updateItemCount();
			}
		}
	}

	function asJson()
	{
		$collection_lookup = Dase_DBO_Collection::getLookupArray();
		$json_tag;
		$eid = $this->getUser()->eid;
		$json_tag['uri'] = $this->getLink();
		if ($this->created) {
			$json_tag['updated'] = $this->created;
		} else {
			$json_tag['updated'] = date(DATE_ATOM);
		}
		$json_tag['name'] = $this->name;
		$json_tag['description'] = $this->description;
		$json_tag['background'] = $this->background;
		$json_tag['is_public'] = $this->is_public;
		$json_tag['type'] = $this->type;
		$json_tag['eid'] = $eid;
		foreach($this->getTagItems() as $tag_item) {
			$item = $tag_item->getItem();
			if (!$item) {
				Dase_Log::debug('tag_item missing item: '.$tag_item->id);
				continue;
			}
			$json_item = array();
			//$json_item['url'] = APP_ROOT.'/tag/'.$eid.'/'.$this->ascii_id.'/item/'.$tag_item->p_collection_ascii_id.'/'.$tag_item->p_serial_number; 
			$json_item['url'] = APP_ROOT.'/tag/'.$eid.'/'.$this->ascii_id.'/'.$tag_item->id; 
			$json_item['sort_order'] = $tag_item->sort_order;
			//make sure p_ values are always populated!
			$json_item['item_unique'] = $tag_item->p_collection_ascii_id.'/'.$tag_item->p_serial_number;
			$json_item['size'] = $tag_item->size;
			$json_item['updated'] = $tag_item->updated;
			$json_item['annotation'] = $tag_item->annotation;
			$json_item['title'] = $item->getTitle();
			$json_item['collection_name'] = $collection_lookup[$item->collection_id]['collection_name'];

			foreach ($item->getMedia() as $m) {
				$json_item['media'][$m->size] = APP_ROOT.'/media/'.$item->collection->ascii_id.'/'.$m->size.'/'.$m->filename;
			}
			$json_tag['items'][] = $json_item;
		}
		return Dase_Json::get($json_tag);
		//$js = new  Services_JSON;	
		//return $js->json_format($json_tag);	
	}

	function asAtom()
	{
		$this->user || $this->getUser(); 
		$feed = new Dase_Atom_Feed;
		$feed->setTitle($this->name);
		if ($this->description) {
			$feed->setSubtitle($this->description);
		}
		$feed->setId(APP_ROOT . '/user/'. $this->user->eid . '/tag/' . $this->ascii_id);
		$feed->setUpdated($this->getUpdated());
		$feed->addAuthor($this->user->eid);
		$feed->setFeedType('tag');
		$feed->addLink(APP_ROOT.'/user/'.$this->user->eid.'/tag/'.$this->ascii_id.'.atom','self');
		$feed->addLink(APP_ROOT.'/user/'.$this->user->eid.'/tag/'.$this->ascii_id);

		$feed->addCategory($this->type,"http://daseproject.org/category/tag/type",$this->type);
		if ($this->is_public) {
			$pub = "public";
		} else {
			$pub = "private";
		}
		$feed->addCategory($pub,"http://daseproject.org/category/tag/visibility");
		$feed->addCategory($this->background,"http://daseproject.org/category/tag/background");

		/*  TO DO categories: admin_coll_id, updated, created, master_item, etc */
		$setnum=0;
		foreach($this->getTagItems() as $tag_item) {
			$item = $tag_item->getItem();
			if ($item) {
				$entry = $feed->addEntry();
				$item->injectAtomEntryData($entry);
				$setnum++;
				$entry->addCategory($setnum,'http://daseproject.org/category/position');
				$entry->addLink(APP_ROOT . '/tag/' . $this->user->eid . '/' . $this->ascii_id . '/' . $tag_item->id,"http://daseproject.org/relation/search-item");
			}
		}
		return $feed->asXml();
	}

	function injectAtomEntryData(Dase_Atom_Entry $entry)
	{
		$this->user || $this->getUser(); 
		$entry->setTitle($this->name);
		if ($this->description) {
			$entry->setSubtitle($this->description);
		}
		$entry->setId(APP_ROOT . '/user/'. $this->user->eid . '/tag/' . $this->ascii_id);
		$entry->setUpdated($this->getUpdated());
		$entry->addAuthor($this->user->eid);
		$entry->addLink(APP_ROOT.'/tag/'.$this->user->eid.'/'.$this->ascii_id.'.atom','self');
		$entry->addLink(APP_ROOT.'/tag/'.$this->user->eid.'/'.$this->ascii_id);

		$entry->addCategory($this->type,"http://daseproject.org/category/tag/type",$this->type);
		if ($this->is_public) {
			$pub = "public";
		} else {
			$pub = "private";
		}
		$entry->addCategory($pub,"http://daseproject.org/category/tag/visibility");
		$entry->addCategory($this->background,"http://daseproject.org/category/tag/background");
		return $entry;
	}

	public function isBulkEditable($user)
	{
		$prefix = Dase_Config::get('table_prefix');
		$db = Dase_DB::get();
		$sql = "
			SELECT p_collection_ascii_id 
			FROM {$prefix}tag_item 
			where tag_id = ?
			GROUP BY p_collection_ascii_id
			";
		$st = $db->prepare($sql);
		$st->execute(array($this->id));
		$colls = $st->fetchAll();
		if (1 === count($colls) && $colls[0]['p_collection_ascii_id']) {
			$c = Dase_DBO_Collection::get($colls[0]['p_collection_ascii_id']);
			if ($c && $user->can('write',$c)) {
				return true;
			}
		}
		return  false;
	}
}
