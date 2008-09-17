<?php

require_once 'Dase/DBO/Autogen/Collection.php';

class Dase_DBO_Collection extends Dase_DBO_Autogen_Collection
{
	public $item_count;

	const COLLECTION_VISIBILITY_PUBLIC = 'public';
	const COLLECTION_VISIBILITY_USER = 'user';
	const COLLECTION_VISIBILITY_MANAGER = 'manager';

	public static function get($ascii_id)
	{
		if (!$ascii_id) {
			throw new Exception('missing collection ascii id');
		}
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

	function getSerialNumbers()
	{
		$sernums = array();
		foreach ($this->getItems() as $item) {
			$sernums[] = $item->serial_number;
		}
		return $sernums;
	}

	function getBaseAtomFeed() 
	{
		$feed = new Dase_Atom_Feed;
		$feed->setTitle($this->collection_name);
		if ($this->description) {
			$feed->setSubtitle($this->description);
		}
		$feed->setUpdated($this->updated);
		$feed->addCategory($this->getItemCount(),"http://daseproject.org/category/collection/item_count");
		$feed->setId($this->getBaseUrl());
		$feed->addAuthor();
		$feed->addLink($this->getBaseUrl(),'alternate');
		$feed->addLink($this->getBaseUrl().'/service','service','application/atomsvc+xml',null,'AtomPub Service Document');
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

	function asAtom($limit = 5)
	{
		$feed = $this->getBaseAtomFeed();
		$feed->setFeedType('collection');
		$feed->addLink(APP_ROOT.'/collection/'.$this->ascii_id.'.atom','self');
		$items = new Dase_DBO_Item;
		$items->collection_id = $this->id;
		if ($limit && is_numeric($limit)) {
			$items->setLimit($limit);
		}
		$items->orderBy('updated DESC');
		foreach ($items->find() as $item) {
			$item->injectAtomEntryData($feed->addEntry('item'));
		}
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
		//todo: this needs to be paged
		$feed = $this->getBaseAtomFeed();
		$feed->addLink(APP_ROOT.'/atom/collection/'.$this->ascii_id.'/archive','self');
		$feed->setFeedType('archive');
		foreach ($this->getAttributes() as $att) {
			$att->injectAtomEntryData($feed->addEntry('attribute'));
		}
		foreach ($this->getAdminAttributes() as $att) {
			$att->injectAtomEntryData($feed->addEntry('attribute'));
		}
		foreach ($this->getItemTypes() as $item_type) {
			$item_type->injectAtomEntryData($feed->addEntry('item_type'),$this);
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

	static function dataAsJson()
	{
		$result = array();
		$colls = new Dase_DBO_Collection;
		foreach ($colls->find() as $c) {
			$result[$c->ascii_id]['visibility'] = $c->visibility;
			$result[$c->ascii_id]['path_to_media_files'] = Dase_Config::get('path_to_media').'/'.$c->ascii_id;
		}
		return Dase_Json::get($result);
	}

	static function listAsJson($public_only = false)
	{
		$colls = new Dase_DBO_Collection;
		$colls->orderBy('collection_name');
		if ($public_only) {
			$colls->is_public = 1;
		} 
		foreach ($colls->find() as $c) {
			foreach ($c as $k => $v) {
				$coll_array[$k] = $v;
			}
			$coll_array['count'] = $c->getItemCount();
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
			$entry->setId(APP_ROOT . '/' . $coll->ascii_id);
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
		$sql = "
			SELECT created
			FROM collection
			ORDER BY created DESC
			";
		//returns first non-null created
		foreach (Dase_DBO::query($sql) as $row) {
			if ($row['created']) {
				return $row['created'];
			}
		}
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
		$sql = "
			SELECT m.dase_user_eid,m.auth_level,m.expiration,m.created,u.name 
			FROM collection_manager m,dase_user u 
			WHERE m.collection_ascii_id = ?
			AND m.dase_user_eid = u.eid
			ORDER BY m.dase_user_eid";
		return Dase_DBO::query($sql,array($this->ascii_id),true);
	}

	function getAttributes($sort = 'sort_order')
	{
		$att = new Dase_DBO_Attribute;
		$att->collection_id = $this->id;
		$att->orderBy($sort);
		return $att->find();
	}

	function getAttributesJson($sort = 'sort_order')
	{
		$att_array = array();
		$last = 0;
		$last_name = 'First';
	
		$att_array['ordered_atts']['_first'] = 'First';
		foreach ($this->getAttributes($sort) as $att) {
			$att_array['ordered_atts'][$att->ascii_id] = "after ".$att->attribute_name;
			foreach ($att as $k => $v) {
				$att_array['attributes'][$att->ascii_id][$k] = $v;
			}
			$att_array['attributes'][$att->ascii_id]['values'] = $att->getFormValues();
			$att_array['attributes'][$att->ascii_id]['count'] = count($att_array['attributes'][$att->ascii_id]['values']);
			$att_array['attributes'][$att->ascii_id]['collection_ascii_id'] = $this->ascii_id;
			$att_array['attributes'][$att->ascii_id]['last'] = $last;
			$att_array['attributes'][$att->ascii_id]['last_name'] = $last_name;
			$last = $att->ascii_id;
			$last_name = $att->attribute_name;
		}
		return Dase_Json::get($att_array);
	}

	function changeAttributeSort($att_ascii_id,$new_so)
	{
		$att_ascii_id_array = array();
		$sql = "
			SELECT ascii_id 
			FROM attribute
			WHERE collection_id = ?
			ORDER BY sort_order";
		$sth = Dase_DBO::query($sql,array($this->id))->fetch(); 
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
		$so = 1;
		foreach ($att_ascii_id_array as $ascii) {
			$now = date(DATE_ATOM);
			Dase_DBO::query($sql,array($so,$now,$ascii,$this->id));
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
		$sql = "
			SELECT count(item.id) as count
			FROM item
			where collection_id = ?
			";
		return Dase_DBO::query($sql,array($this->id))->fetchColumn();
	}

	function getItems()
	{
		$item = new Dase_DBO_Item;
		$item->collection_id = $this->id;
		return $item->find();
	}

	function getItemIdRange($start,$count)
	{
		$sql = "
			SELECT id 
			FROM item
			WHERE collection_id = ?
			ORDER BY updated DESC
			";
		$i = 0;
		$total = 0;
		$item_id_array = array();
		foreach (Dase_DBO::query($sql,array($this->id)) as $row) {
			$i++;
			if ($i >= $start && $count >= $total) {
				$total++;
				$item_id_array[] = $row['id'];
			}
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
				AND value_text != ''
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
			//$search_table->collection_ascii_id = $this->ascii_id;
			$search_table->updated = date(DATE_ATOM);
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
			$search_table->collection_ascii_id = $this->ascii_id;
			$search_table->updated = date(DATE_ATOM);
			$search_table->insert();
		}
		return true;
	}

	function createNewItem($serial_number=null,$eid=null)
	{
		if (!$eid) {
			$eid = '_dase';
		}
		$item = new Dase_DBO_Item;
		$item->collection_id = $this->id;
		if ($serial_number) {
			$item->serial_number = $serial_number;
			if ($item->findOne()) {
				throw new Dase_Exception('duplicate serial number!');
				return;
			}
			$item->status = 'public';
			$item->item_type_id = 0;
			$item->created = date(DATE_ATOM);
			$item->updated = date(DATE_ATOM);
			$item->created_by_eid = $eid;
			$item->insert();
			return $item;
		} else {
			$item->status = 'public';
			$item->item_type_id = 0;
			$item->created = date(DATE_ATOM);
			$item->created_by_eid = $eid;
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
		$ws = $svc->addWorkspace($this->collection_name.' Workspace');
		$ws->addCollection(APP_ROOT.'/collection/'.$this->ascii_id.'.atom',$this->collection_name.' Items')
			->addAccept('application/atom+xml;type=entry')
			->addCategorySet()
			->addCategory('item','http://daseproject.org/category/entrytype');
		$media_repos = APP_ROOT.'/media/'.$this->ascii_id.'.atom';
		$media_coll = $ws->addCollection($media_repos,$this->collection_name.' Media');
		foreach(Dase_Config::get('media_types') as $type) {
			$media_coll->addAccept($type);
		}
		$attributes_repos = APP_ROOT.'/collection/'.$this->ascii_id.'/attributes.atom';
		$ws->addCollection($attributes_repos,$this->collection_name.' Attributes')
			->addAccept('application/atom+xml;type=entry')
			->addCategorySet()
			->addCategory('attribute','http://daseproject.org/category/entrytype');
		return $svc->asXml();
	}
}
