<?php

require_once 'Dase/DBO/Autogen/Collection.php';

class Dase_DBO_Collection extends Dase_DBO_Autogen_Collection
{
	public $item_count;

	public static function get($ascii_id)
	{
		$collection = new Dase_DBO_Collection;
		$collection->ascii_id = $ascii_id;
		if ($collection->findOne()) {
			return $collection;
		} else {
			return false;
		}
	}

	public function getBaseUrl() {
		return APP_ROOT . '/collection/' . $this->ascii_id;
	}

	function asJson() 
	{
		$coll_array = array();
		$coll_array['collection'] = array(
			'name' => $this->collection_name,
			'ascii_id' => $this->ascii_id,
			'path_to_media_file' => $this->path_to_media_files,
		);
		$coll_array['collection']['attributes'] = array();
		foreach ($this->getAttributes() as $att) {
			$att_array['name'] = $att->attribute_name;
			$att_array['ascii_id'] = $att->ascii_id;
			$coll_array['collection']['attributes'][] = $att_array;
		}
		return Dase_Json::get($coll_array,true);
	}

	public function expunge()
	{
		$items = new Dase_DBO_Item;
		$items->collection_id = $this->id;
		foreach ($items->find() as $item) {
			Dase_Log::info("item $this->ascii_id:$item->serial_number deleted");
			$item->expunge();
		}
		$item_types = new Dase_DBO_ItemType;
		$item_types->collection_id = $this->id;
		foreach ($item_types->find() as $type) {
			$type->expunge();
		}
		$atts = new Dase_DBO_Attribute;
		$atts->collection_id = $this->id;
		foreach ($atts->find() as $a) {
			Dase_Log::info("attribute $this->asci_id:$a->ascii_id deleted");
			$a->delete();
		}	
		$cms = new Dase_DBO_CollectionManager;
		$cms->collection_ascii_id = $this->ascii_id;
		foreach ($cms->find() as $cm) {
			$cm->delete();
		}
		$this->delete();
		Dase_Log::info("$this->ascii_id deleted");
	}

	function getBaseAtomFeed() 
	{
		$feed = new Dase_Atom_Feed;
		$feed->setTitle($this->collection_name);
		if ($this->description) {
			$feed->setSubtitle($this->description);
		}
		$feed->setUpdated($this->updated);
		$feed->addCategory($this->ascii_id,'http://daseproject.org/category/collection',$this->collection_name);
		$feed->addCategory($this->getItemCount(),"http://daseproject.org/category/collection/item_count");
		$feed->setId($this->getBaseUrl());
		$feed->addAuthor();
		$feed->addLink($this->getBaseUrl(),'alternate');
		//$feed->addLink($this->getBaseUrl().'/service','service','application/atomsvc+xml',null,'AtomPub Service Document');
		return $feed;
	}

	function getAttributesAtom() {
		$feed = $this->getBaseAtomFeed();
		$feed->setFeedType('attributes');
		foreach ($this->getAttributes() as $att) {
			$att->injectAtomEntryData($feed->addEntry());
		}
		return $feed;
	}

	function asAtom()
	{
		$feed = $this->getBaseAtomFeed();
		$feed->setFeedType('collection');
		$feed->addLink(APP_ROOT.'/atom/collection/'.$this->ascii_id,'self');
		return $feed->asXml();
	}

	function asAtomEntry()
	{
		$entry = new Dase_Atom_Entry();
		$entry->setId(APP_ROOT.'/collection/'.$this->ascii_id);
		$entry->setEntryType('collection');
		$entry->addLink(APP_ROOT.'/collection/'.$this->ascii_id.'.atom','self');
		$entry->addLink(APP_ROOT.'/collection/'.$this->ascii_id);
		return $entry->asXml();
	}

	function asAtomFull() 
	{
		$feed = $this->getBaseAtomFeed();
		$feed->addLink(APP_ROOT.'/atom/collection/'.$this->ascii_id.'/full','self');
		$cms = new Dase_DBO_CollectionManager;
		$cms->collection_ascii_id = $this->ascii_id;
		foreach ($cms->find() as $cm) {
			$cm->injectAtomEntryData($feed->addEntry());
		}
		foreach ($this->getAttributes() as $att) {
			$att->injectAtomEntryData($feed->addEntry(),$this);
		}
		foreach ($this->getItemTypes() as $type) {
			$type->injectAtomEntryData($feed->addEntry(),$this);
		}
		return $feed->asXml();
	}

	function asAtomArchive($limit=0) {
		//todo: this needs ot be paged
		$feed = $this->getBaseAtomFeed();
		$feed->addLink(APP_ROOT.'/atom/collection/'.$this->ascii_id.'/archive','self');
		$feed->setFeedType('archive');
		foreach ($this->getAttributes() as $att) {
			$att->injectAtomEntryData($feed->addEntry('attribute'));
		}
		foreach ($this->getAdminAttributes() as $att) {
			$att->injectAtomEntryData($feed->addEntry('attribute'));
		}
		$items = new Dase_DBO_Item;
		$items->collection_id = $this->id;
		if ($limit && is_numeric($limit)) {
			$items->setLimit($limit);
		}
		foreach ($items->find() as $item) {
			$item->injectAtomEntryData($feed->addEntry('item'));
		}
		//returned XML will be VERY large
		return $feed->asXml();
	}

	function asJsonArchive($limit=0) 
	{
		$coll_array['managers'] = array();
		$coll_array['attributes'] = array();
		$coll_array['items'] = array();
		foreach ($this->getItems() as $item) {
			$coll_array['items'][] = $item->asArray();
		}
		foreach ($this->getManagers() as $manager) {
			$coll_array['managers'][] = $manager;
		}
		foreach ($this->getAttributes() as $attribute) {
			$coll_array['attributes'][] = $attribute->asArray();
		}
		return Dase_Json::get($coll_array);
	}

	function asJsonCollection($page=1,$limit=50)
	{
		$offset = $limit * ($page-1);
		if ($offset < 0) {
			$offset = 0;
		}
		$coll_array = array();
		$coll_array['name'] = $this->collection_name;
		$coll_array['ascii_id'] = $this->ascii_id;
		$coll_array['item_count'] = $this->getItemCount();
		/*
		$db = Dase_DB::get();
		$sql = "
			SELECT serial_number
			FROM item 
			WHERE collection_id = ?
			ORDER BY serial_number
			LIMIT ? 
			OFFSET ?
			";
		$sth = $db->prepare($sql);
		$sth->execute(array($this->id,$limit,$offset));
		$item_array = array();
		 */
		//while ($sernum = $sth->fetchColumn()) {
		foreach ($this->getItems() as $item) {
			$item_array['href'] = 'https://dase.laits.utexas.edu/'.$this->ascii_id.'/'.$item->serial_number;
			$item_array['title'] = $item->getTitle();
			$coll_array['members'][] = $item_array;
		}
		$next = $page+1;
		$coll_array['next'] = $this->getBaseUrl().'.json?page='. $next;
		$json = new Services_JSON;
		return $json->encode($coll_array,true);
	}

	static function listAsJson($public_only = false)
	{
		$c = new Dase_DBO_Collection;
		$c->orderBy('collection_name');
		if ($public_only) {
			$c->is_public = 1;
		} 
		foreach ($c->find() as $coll) {
			foreach ($coll as $k => $v) {
				$coll_array[$k] = $v;
			}
			$coll_array['count'] = $coll->getItemCount();
			$result[] = $coll_array;
		}
		return Dase_Json::get($result);
	}

	static function listAsAtom($public_only = false)
	{
		$c = new Dase_DBO_Collection;
		$c->orderBy('collection_name');
		if ($public_only) {
			$c->is_public = 1;
		} 
		$cs = $c->find();
		$feed = new Dase_Atom_Feed;
		$feed->setTitle('DASe Collections');
		$feed->setId(APP_ROOT);
		$feed->setFeedType('collection_list');
		//todo:fix this to *not* simply be a time stamp
		$feed->setUpdated(date(DATE_ATOM));
		$feed->addAuthor('DASe (Digital Archive Services)','http://daseproject.org');
		$feed->addLink(APP_ROOT.'/atom','self');
		foreach ($cs as $coll) {
			$entry = $feed->addEntry();
			$entry->setTitle($coll->collection_name);
			$entry->setContent(str_replace('_collection','',$coll->ascii_id));
			$entry->setId(APP_ROOT . '/' . $coll->ascii_id . '/');
			$entry->setUpdated($coll->created);
			$entry->setEntryType('collection');
			$entry->addLink(APP_ROOT.'/atom/collection/'.$coll->ascii_id.'/','self');
			$entry->addLink($coll->getBaseUrl(),'alternate');
			if ($coll->is_public) {
				$pub = "public";
			} else {
				$pub = "private";
			}
			$entry->addCategory($pub,"http://daseproject.org/category/visibility");
		}
		return $feed->asXML();
	}

	static function getLastCreated()
	{
		$db = Dase_DB::get();
		$sql = "
			SELECT created
			FROM collection
			ORDER BY created DESC
			";
		$st = $db->prepare($sql);
		$st->execute();
		return $st->fetchColumn();
	}

	static function getLookupArray()
	{
		$hash = array();
		$c = new Dase_DBO_Collection;
		foreach ($c->find() as $coll) {
			$iter = $coll->getIterator();
			foreach ($iter as $field => $value) {
				$coll_hash[$field] = $value;
			}
			$hash[$coll->id] = $coll_hash;
		}
		return $hash;
	}

	/**  note: this returns an array of arrays
		NOT an array of manager objects
	 */
	function getManagers()
	{
		$db = Dase_DB::get();
		$sql = "
			SELECT m.dase_user_eid,m.auth_level,m.expiration,m.created,u.name 
			FROM collection_manager m,dase_user u 
			WHERE m.collection_ascii_id = ?
			AND m.dase_user_eid = u.eid
			ORDER BY m.dase_user_eid";
		$sth = $db->prepare($sql);
		$sth->setFetchMode(PDO::FETCH_ASSOC);
		$sth->execute(array($this->ascii_id));
		return $sth;
	}

	function getAttributes($sort = 'sort_order')
	{
		$att = new Dase_DBO_Attribute;
		$att->collection_id = $this->id;
		$att->orderBy($sort);
		return $att->find();
	}

	function getAttributesData()
	{
		$att = new Dase_DBO_Attribute;
		$cols = Dase_DB::listColumns('attribute');
		$sql = "
			SELECT *
			FROM attribute
			WHERE collection_id = ?
			ORDER BY sort_order
			";
		$db = Dase_DB::get();
		$sth = $db->prepare($sql);
		$sth->setFetchMode(PDO::FETCH_ASSOC);
		$sth->execute(array($this->id));
		return $sth->fetchAll();
	}

	function changeAttributeSort($att_ascii_id,$new_so)
	{
		$att_ascii_id_array = array();
		$db = Dase_DB::get();
		$sql = "
			SELECT ascii_id 
			FROM attribute
			WHERE collection_id = ?
			ORDER BY sort_order";
		$sth = $db->prepare($sql);
		$sth->setFetchMode(PDO::FETCH_ASSOC);
		$sth->execute(array($this->id));
		while ($row = $sth->fetch()) {
			if ($att_ascii_id != $row['ascii_id']) {
				$att_ascii_id_array[] = $row['ascii_id'];
			}
		} 
		array_splice($att_ascii_id_array,$new_so-1,0,$att_ascii_id);
		$sql = "
			UPDATE attribute
			SET sort_order = ?,
			updated = ?
			WHERE ascii_id = ?
			AND collection_id = ?";
		$sth = $db->prepare($sql);
		$so = 1;
		foreach ($att_ascii_id_array as $ascii) {
			$now = date(DATE_ATOM);
			$sth->execute(array($so,$now,$ascii,$this->id));
			$so++;
		}
	}

	function getAdminAttributes()
	{
		$att = new Dase_DBO_Attribute;
		$att->collection_id = 0;
		$att->orderBy('sort_order');
		return $att->find();
	}

	function getItemCount()
	{
		$db = Dase_DB::get();
		$sql = "
			SELECT count(item.id) as count
			FROM item
			where collection_id = ?
			";
		$st = $db->prepare($sql);
		$st->execute(array($this->id));
		$this->item_count = $st->fetchColumn();
		return $this->item_count;
	}

	function getItems()
	{
		$item = new Dase_DBO_Item;
		$item->collection_id = $this->id;
		return $item->find();
	}

	function getItemIdRange($start,$count)
	{
		$db = Dase_DB::get();
		$sql = "
			SELECT id 
			FROM item
			WHERE collection_id = ?
			ORDER BY updated DESC
			";
		$sth = $db->prepare($sql);
		$sth->setFetchMode(PDO::FETCH_COLUMN);
		$sth->execute(array($this->id));
		$rows = $sth->fetchAll();
		$item_id_array_array = array_slice($rows,$start-1,$count);
		foreach ($item_id_array_array as $item_id_result) {
			$item_id_array[] = $item_id_result[0];
		}
		return $item_id_array;
	}

	function getItemTypes()
	{
		$types = new Dase_DBO_ItemType;
		$types->collection_id = $this->id;
		$types->orderBy('name');
		return $types->find();
	}

	function getItemsByAttVal($att_ascii_id,$value_text,$substr = false)
	{
		$a = new Dase_DBO_Attribute;
		$a->ascii_id = $att_ascii_id;
		$a->collection_id = $this->id;
		$a->findOne();
		$v = new Dase_DBO_Value;
		$v->attribute_id = $a->id;
		if ($substr) {
			$v->addWhere('value_text',"%$value_text%",'like');
		} else {
			$v->value_text = $value_text;
		}
		$items = array();
		foreach ($v->find() as $val) {
			$it = new Dase_DBO_Item;
			$it->load($val->item_id);
			$items[] = $it;
		}
		return $items;
	}

	public function buildSearchIndex()
	{
		$db = Dase_DB::get();
		//todo: make sure this->id is an integer
		$db->query("DELETE FROM search_table WHERE collection_id = $this->id");
		$db->query("DELETE FROM admin_search_table WHERE collection_id = $this->id");
		$items = new Dase_DBO_Item;
		$items->collection_id = $this->id;
		foreach ($items->find() as $item) {
			//search table
			$composite_value_text = '';
			//NOTE: '= true' works for mysql AND postgres!
			$sql = "
				SELECT value_text
				FROM value
				WHERE item_id = ?
				AND value.attribute_id in (SELECT id FROM attribute where in_basic_search = true)
				";
			$st = $db->prepare($sql);
			$st->execute(array($item->id));
			while ($value_text = $st->fetchColumn()) {
				$composite_value_text .= $value_text . " ";
			}
			foreach ($item->getContents() as $c) {
				$composite_value_text .= $c->text . " ";
			}
			$search_table = new Dase_DBO_SearchTable;
			$search_table->value_text = $composite_value_text;
			$search_table->item_id = $item->id;
			$search_table->collection_id = $this->id;
			$search_table->insert();

			//admin search table
			$composite_value_text = '';
			$sql = "
				SELECT value_text
				FROM value
				WHERE item_id = ?
				";
			$st = $db->prepare($sql);
			$st->execute(array($item->id));
			while ($value_text = $st->fetchColumn()) {
				$composite_value_text .= $value_text . " ";
			}
			$search_table = new Dase_DBO_AdminSearchTable;
			$search_table->value_text = $composite_value_text;
			$search_table->item_id = $item->id;
			$search_table->collection_id = $this->id;
			$search_table->insert();
		}
		return true;
	}

	function createNewItem($serial_number = null)
	{
		$item = new Dase_DBO_Item;
		$item->collection_id = $this->id;
		if ($serial_number) {
			$item->serial_number = $serial_number;
			if ($item->findOne()) {
				throw new Exception('duplicate serial number!');
				return;
			}
			$item->status_id = 0;
			$item->item_type_id = 0;
			$item->created = date(DATE_ATOM);
			$item->updated = date(DATE_ATOM);
			$item->insert();
			return $item;
		} else {
			$item->status_id = 0;
			$item->item_type_id = 0;
			$item->created = date(DATE_ATOM);
			$item->insert();
			$item->serial_number = sprintf("%09d",$item->id);
			$item->updated = date(DATE_ATOM);
			$item->update();
			return $item;
		}
	}

	public function getAtompubServiceDoc() 
	{
		$svc = new Dase_Atom_Service;	
		$svc->addWorkspace($this->collection_name.' Workspace')
			->addCollection(APP_ROOT.'/edit/'.$this->ascii_id,$this->collection_name.' Items')
			->addAccept('application/atom+xml;type=entry');
		return $svc->asXml();
	}

	public static function getMediaSources()
	{
		$sources = array();
		$colls = new Dase_DBO_Collection;
		foreach ($colls->find() as $coll) {
			$sources[$coll->ascii_id] = $coll->path_to_media_files;
		}
		return $sources;
	}
}
