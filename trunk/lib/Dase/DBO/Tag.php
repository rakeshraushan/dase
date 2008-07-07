<?php

require_once 'Dase/DBO/Autogen/Tag.php';

class Dase_DBO_Tag extends Dase_DBO_Autogen_Tag 
{
	private $user;

	const TYPE_CART = 'cart';
	const TYPE_SET = 'set';
	const TYPE_SLIDESHOW = 'slideshow';
	const TYPE_ADMIN = 'admin';

	public static function getByUser($user)
	{
		$db = Dase_DB::get();
		//union allows us to get tags that have no items
		$sql = "
			SELECT t.id,t.ascii_id,t.name,t.type,count(ti.id)
			FROM tag t , tag_item ti
			WHERE t.id = ti.tag_id
			AND t.dase_user_id = ?
			GROUP BY t.id,t.ascii_id,t.name,t.type
			UNION
			SELECT t.id,t.ascii_id,t.name,t.type,0
			FROM tag t 
			WHERE NOT EXISTS(SELECT * FROM tag_item ti WHERE ti.tag_id = t.id)
			AND t.dase_user_id = ?
			";
		$sth = $db->prepare($sql);
		$sth->setFetchMode(PDO::FETCH_ASSOC);
		$sth->execute(array($user->id,$user->id));
		return $sth;
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
			$tag->type = self::TYPE_SET;
			$tag->background = 'white';
			$tag->is_public = 0;
			$tag->eid = $user->eid;
			$tag->created = date(DATE_ATOM);
			//todo: for backward compat -- get rid of this in DASe 2:
			$tag->tag_type_id = 2;
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

	function getItemCount()
	{
		$db = Dase_DB::get();
		$sql = "
			SELECT count(*)
			FROM tag_item 
			where tag_id = ?
			";
		$st = $db->prepare($sql);
		$st->execute(array($this->id));
		return $st->fetchColumn();
	}

	function getTagItemIds()
	{
		$db = Dase_DB::get();
		$sql = "
			SELECT id 
			FROM tag_item 
			where tag_id = ?
			ORDER BY sort_order
			";
		$st = $db->prepare($sql);
		$st->execute(array($this->id));
		return $st->fetchAll(PDO::FETCH_COLUMN);
	}

	function getItemIds()
	{
		$db = Dase_DB::get();
		$sql = "
			SELECT item_id
			FROM tag_item 
			WHERE tag_id = ?
			ORDER BY sort_order
			";
		$st = $db->prepare($sql);
		$st->execute(array($this->id));
		return $st->fetchAll(PDO::FETCH_COLUMN);
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

	function getType()
	{
		$type = new Dase_DBO_TagType;
		return $type->load($this->tag_type_id);
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
		return APP_ROOT . '/user/' . $this->user->eid . '/tag/' . $this->ascii_id;
	}

	function addItem($item_id)
	{
		$tag_item = new Dase_DBO_TagItem;
		$tag_item->tag_id = $this->id;
		$tag_item->item_id = $item_id;
		//I think this should be in a try-catch
		return ($tag_item->insert());
	}

	function removeItem($item_id)
	{
		$tag_item = new Dase_DBO_TagItem;
		$tag_item->tag_id = $this->id;
		$tag_item->item_id = $item_id;
		if ($tag_item->findOne()) {
			return ($tag_item->delete());
		}
	}

	function asJson()
	{
		$json_tag;
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
		$json_tag['eid'] = $this->getUser()->eid;
		foreach($this->getTagItems() as $tag_item) {
			$item = $tag_item->getItem();
			$json_item = array();
			$json_item['url'] = APP_ROOT.'/tag/'.$this->ascii_id.'/item/'.$tag_item->p_collection_ascii_id.'/'.$tag_item->p_serial_number; 
			$json_item['sort_order'] = $tag_item->sort_order;
			$json_item['size'] = $tag_item->size;
			$json_item['updated'] = $tag_item->updated;
			$json_item['annotation'] = $tag_item->annotation;

			foreach ($item->getMedia() as $m) {
				$json_item['media'][$m->size] = APP_ROOT.'/media/'.$item->collection->ascii_id.'/'.$m->size.'/'.$m->filename;
			}
			$json_tag['items'][] = $json_item;
		}
		$js = new  Services_JSON;	
		return $js->json_format($json_tag);	
	}


	function asAtom()
	{
		$this->user || $this->getUser(); 
		$feed = new Dase_Atom_Feed;
		//$feed->setTitle($this->name.' ('.$this->getItemCount().' items)');
		$feed->setTitle($this->name);
		if ($this->description) {
			$feed->setSubtitle($this->description);
		}
		$feed->setId(APP_ROOT . '/user/'. $this->user->eid . '/tag/' . $this->ascii_id);
		$feed->setUpdated($this->getUpdated());
		$feed->addAuthor($this->user->eid);
		$feed->setFeedType('tag');
		$feed->addLink(APP_ROOT.'/tag/'.$this->id .'?format=atom','self');
		$feed->addLink(APP_ROOT.'/tag/'.$this->id,'http://daseproject.org/relation/tag');

		$feed->addCategory($this->ascii_id,"http://daseproject.org/category/tag",$this->name);
		$feed->addCategory($this->getType()->ascii_id,"http://daseproject.org/category/tag/type",$this->type);
		if ($this->is_public) {
			$pub = "public";
		} else {
			$pub = "private";
		}
		$feed->addCategory($pub,"http://daseproject.org/category/visibility");
		$feed->addCategory($this->background,"http://daseproject.org/category/tag/background");

		/*  TO DO categories: admin_coll_id, updated, created, master_item, etc */
		foreach($this->getTagItems() as $tag_item) {
			$entry = $feed->addEntry();
			$item = $tag_item->getItem();
			$item->injectAtomEntryData($entry);
			$entry->addLink(APP_ROOT . '/tag/' . $this->user->eid . '/' . $this->ascii_id . '/' . $tag_item->id,"http://daseproject.org/relation/search-item");
		}
		return $feed->asXml();
	}
}
