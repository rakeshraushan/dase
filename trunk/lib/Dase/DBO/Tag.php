<?php

require_once 'Dase/DBO/Autogen/Tag.php';

class Dase_DBO_Tag extends Dase_DBO_Autogen_Tag 
{
	private $user;

	public static function getByUser($user)
	{
		$prefix = $user->db->table_prefix;
		$sql = "
			SELECT * 
			FROM {$prefix}tag 
			WHERE dase_user_id = ?
			";
		$tags = array();
		foreach (Dase_DBO::query($user->db,$sql,array($user->id))->fetchAll() as $row) { 
			$row['count'] = $row['item_count'];
			if ($row['ascii_id']) { //compat: skip tags w/o ascii_id
				$tags[] = $row;
			}
		}
		return $tags;
	}

	public static function listAsFeed($db,$category='',$app_root)
	{
		//public ONLY!!!!!!
		$feed = new Dase_Atom_Feed;
		$feed->setTitle('public sets');
		$feed->setId($app_root.'/sets');
		$feed->setFeedType('sets');
		$feed->setUpdated(date(DATE_ATOM));
		$feed->addAuthor();

		if ($category) {
			$parts = explode('}',$category);
			if (1 == count($parts)) {
				$term = $parts[0];
			} elseif (2 == count($parts)) {
				$scheme = urldecode(trim($parts[0],'{'));
				$scheme = str_replace('http://daseproject.org/category/','',$scheme);
				$term = $parts[1];
			} else {
				return $feed->asXml($app_root);
			}
			$prefix = $db->table_prefix;
			$sql = "
				SELECT tc.tag_id 
				FROM {$prefix}category_scheme cs,{$prefix}category c, {$prefix}tag_category tc 
				WHERE cs.uri = ?
				AND cs.id = c.scheme_id
				AND c.term = ?	
				AND tc.category_id = c.id
				";
			foreach (Dase_DBO::query($db,$sql,array($scheme,$term)) as $row) {
				$tag = new Dase_DBO_Tag($this->db);
				$tag->load($row['tag_id']);
				if ($tag->ascii_id) { //compat
					$entry = $tag->injectAtomEntryData($feed->addEntry('set'),$app_root);
					$entry->addCategory($tag->item_count,"http://daseproject.org/category/item_count");
				}
			}

		} else {
			$tags = new Dase_DBO_Tag($db);
			$tags->is_public = true;
			$tags->orderBy('updated DESC');
			foreach ($tags->find() as $tag) {
				$tag = clone $tag;
				if ($tag->ascii_id) { //compat: make sure tag has ascii_id
					$entry = $tag->injectAtomEntryData($feed->addEntry('set'),$app_root);
					$entry->addCategory($tag->item_count,"http://daseproject.org/category/item_count");
				}
			}
		}
		$feed->sortByTitle();
		return $feed->asXml($app_root);
	}

	public static function create($db,$tag_name,$user)
	{
		$tag = new Dase_DBO_Tag($db);
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

	public static function get($db,$ascii_id,$eid)
	{
		if (!$ascii_id || !$eid) {
			return false;
		}
		$user = Dase_DBO_DaseUser::get($db,$eid);
		$tag = new Dase_DBO_Tag($db);
		$tag->ascii_id = $ascii_id;
		$tag->dase_user_id = $user->id;
		$tag->findOne();
		if ($tag->id) {
			return $tag;
		} else {
			return false;
		}
	}

	public function getUrl($app_root)
	{
		$u = $this->getUser();
		return $app_root.'/tag/'.$u->eid.'/'.$this->ascii_id;
	}

	/** be careful w/ this -- we do not archive before deleting */
	function expunge()
	{
		if (!$this->id) {
			throw new Exception("invalid");
		} 
		$tag_items = new Dase_DBO_TagItem($this->db);
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
		$tag_items = new Dase_DBO_TagItem($this->db);
		$tag_items->tag_id = $this->id;
		$this->item_count = $tag_items->findCount();
		$this->updated = date(DATE_ATOM);
		//postgres boolean weirdness make this necessary
		if (!$this->is_public) {
			$this->is_public = 0;
		}
		$this->update();
	}

	function setBackground($background)
	{
		$this->background = $background;
		if (!$this->is_public) {
			$this->is_public = 0;
		}
		return $this->update();
	}

	function getTagItemIds()
	{
		$prefix = $this->db->table_prefix;
		$dbh = $this->db->getDbh();
		$sql = "
			SELECT id 
			FROM {$prefix}tag_item 
			where tag_id = ?
			ORDER BY sort_order
			";
		$st = $dbh->prepare($sql);
		$st->execute(array($this->id));
		return $st->fetchAll(PDO::FETCH_COLUMN);
	}

	function getTagItems()
	{
		$tag_item = new Dase_DBO_TagItem($this->db);
		$tag_item->tag_id = $this->id;
		$tag_item->orderBy('sort_order');
		return $tag_item->find();
	}

	function resortTagItems($dir='DESC')
	{
		$tag_item = new Dase_DBO_TagItem($this->db);
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
		//avoids another db lookup
		if ($this->user) {
			return $this->user;
		}
		$user = new Dase_DBO_DaseUser($this->db);
		$this->user = $user->load($this->dase_user_id);
		return $this->user;
	}

	function addItem($item_unique,$updateCount=false)
	{
		$tag_item = new Dase_DBO_TagItem($this->db);
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
		$tag_item = new Dase_DBO_TagItem($this->db);
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

	function asJson($app_root)
	{

		$collection_lookup = Dase_DBO_Collection::getLookupArray();
		$json_tag;
		$eid = $this->getUser()->eid;
		$json_tag['uri'] = $this->getUrl($app_root);
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
				Dase_Log::get()->debug('tag_item missing item: '.$tag_item->id);
				continue;
			}
			$json_item = array();
			$json_item['url'] = $app_root.'/tag/'.$eid.'/'.$this->ascii_id.'/'.$tag_item->id; 
			$json_item['sort_order'] = $tag_item->sort_order;
			//make sure p_ values are always populated!
			$json_item['item_unique'] = $tag_item->p_collection_ascii_id.'/'.$tag_item->p_serial_number;
			$json_item['size'] = $tag_item->size;
			$json_item['updated'] = $tag_item->updated;
			$json_item['annotation'] = $tag_item->annotation;
			$json_item['title'] = $item->getTitle();
			$json_item['collection_name'] = $collection_lookup[$item->collection_id]['collection_name'];

			foreach ($item->getMedia() as $m) {
				$json_item['media'][$m->size] = $app_root.'/media/'.$item->collection->ascii_id.'/'.$m->size.'/'.$m->filename;
			}
			$json_tag['items'][] = $json_item;
		}
		return Dase_Json::get($json_tag);
	}

	function asAtom($app_root)
	{
		$this->user || $this->getUser(); 
		$feed = new Dase_Atom_Feed;
		$feed->setTitle($this->name);
		if ($this->description) {
			$feed->setSubtitle($this->description);
		}
		$feed->setId($app_root.'/tag/'. $this->user->eid . '/' . $this->ascii_id);
		$feed->setUpdated($this->updated);
		$feed->addAuthor($this->user->eid);
		$feed->setFeedType('tag');
		$feed->addLink($app_root.'/tag/'.$this->user->eid.'/'.$this->ascii_id.'.atom','self');
		$feed->addLink($app_root.'/tag/'.$this->user->eid.'/'.$this->ascii_id,'alternate');
		$feed->addLink($app_root.'/tag/'.$this->user->eid.'/'.$this->ascii_id.'/list','alternate','text/html','','list');
		$feed->addLink($app_root.'/tag/'.$this->user->eid.'/'.$this->ascii_id.'/grid','alternate','text/html','','grid');
		$feed->addLink($app_root.'/tag/'.$this->user->eid.'/'.$this->ascii_id.'/data','alternate','text/html','','data');

		$feed->addCategory($this->type,"http://daseproject.org/category/tag_type",$this->type);
		if ($this->is_public) {
			$pub = "public";
		} else {
			$pub = "private";
		}
		$feed->addCategory($pub,"http://daseproject.org/category/visibility");
		$feed->addCategory($this->background,"http://daseproject.org/category/background");

		/*  TO DO categories: admin_coll_id, updated, created, master_item, etc */
		$setnum=0;
		foreach($this->getTagItems() as $tag_item) {
			$item = $tag_item->getItem();
			if ($item) {
				$entry = $feed->addEntry();
				$item->injectAtomEntryData($entry,$app_root);
				$setnum++;
				$entry->addCategory($setnum,'http://daseproject.org/category/position');
				$entry->addLink($app_root.'/tag/' . $this->user->eid . '/' . $this->ascii_id . '/' . $tag_item->id,"http://daseproject.org/relation/search-item");
				if ($tag_item->annotation) {
					$entry->setSummary($tag_item->annotation);
				}
			}
		}
		return $feed->asXml($app_root);
	}

	function asAtomEntry($app_root,$serialize=true)
	{
		if ($serialize) {
			return $this->injectAtomEntryData(new Dase_Atom_Entry,null,$app_root)->asXml();
		} else {
			return $this->injectAtomEntryData(new Dase_Atom_Entry,null,$app_root);
		}

	}

	function injectAtomEntryData(Dase_Atom_Entry $entry,$user=null,$app_root)
	{
		if (!$user) {
			$user = $this->getUser();
		}
		$entry->setTitle($this->name);
		if ($this->description) {
			$entry->setSummary($this->description);
		}
		$entry->setId($app_root.'/user/'. $user->eid . '/tag/' . $this->ascii_id);
		$updated = $this->updated ? $this->updated : '2005-01-01T00:00:01-06:00';
		$entry->setUpdated($updated);
		$entry->addAuthor($user->eid);
		$entry->addLink($app_root.'/tag/'.$user->eid.'/'.$this->ascii_id.'.atom','self');
		$entry->addLink($app_root.'/tag/'.$user->eid.'/'.$this->ascii_id.'/entry.atom','edit' );
		$entry->addLink($app_root.'/tag/'.$user->eid.'/'.$this->ascii_id.'/entry.json','http://daseproject.org/relation/edit','application/json');
		$entry->addLink($app_root.'/tag/'.$user->eid.'/'.$this->ascii_id,'alternate');
		//todo: beware expense??
		foreach (Dase_DBO_Category::getAll($this) as $cat) {
			$scheme = $cat->getScheme();
			$entry->addCategory($cat->term,$scheme,$cat->label);
		}
		$entry->addCategory($app_root,"http://daseproject.org/category/base_url");
		$entry->addCategory("set","http://daseproject.org/category/entrytype");
		$entry->addCategory($this->type,"http://daseproject.org/category/tag_type",$this->type);
		if ($this->is_public) {
			$pub = "public";
		} else {
			$pub = "private";
		}
		$entry->addCategory($pub,"http://daseproject.org/category/visibility");
		$entry->addCategory($this->background,"http://daseproject.org/category/background");
		return $entry;
	}

	public function deleteCategories()
	{
		foreach (Dase_DBO_Category::getAll($this) as $cat) {
			Dase_DBO_Category::remove($this,$cat->scheme_id);
		}
	}

	public function isBulkEditable($user)
	{
		$prefix = $this->db->table_prefix;
		$dbh = $this->db->getDbh();
		$sql = "
			SELECT p_collection_ascii_id 
			FROM {$prefix}tag_item 
			where tag_id = ?
			GROUP BY p_collection_ascii_id
			";
		$st = $dbh->prepare($sql);
		$st->execute(array($this->id));
		$colls = $st->fetchAll();
		if (1 === count($colls) && $colls[0]['p_collection_ascii_id']) {
			$c = Dase_DBO_Collection::get($this->db,$colls[0]['p_collection_ascii_id']);
			if ($c && $user->can('write',$c)) {
				return true;
			}
		}
		return  false;
	}

	public function isSingleCollection()
	{
		$prefix = $this->db->table_prefix;
		$dbh = $this->db->getDbh();
		$sql = "
			SELECT p_collection_ascii_id 
			FROM {$prefix}tag_item 
			where tag_id = ?
			GROUP BY p_collection_ascii_id
			";
		$st = $dbh->prepare($sql);
		$st->execute(array($this->id));
		$colls = $st->fetchAll();
		if (1 === count($colls) && $colls[0]['p_collection_ascii_id']) {
			return true;
		}
		return  false;
	}
}
