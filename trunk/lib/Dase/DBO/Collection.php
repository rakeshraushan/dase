<?php

require_once 'Dase/DBO/Autogen/Collection.php';

class Dase_DBO_Collection extends Dase_DBO_Autogen_Collection
{
	public static function get($db,$ascii_id)
	{
		if (!$ascii_id) {
			throw new Exception('missing collection ascii id');
		}
		$collection = new Dase_DBO_Collection($db);
		$collection->ascii_id = $ascii_id;
		if ($collection->findOne()) {
			return $collection;
		} else {
			return false;
		}
	}

	public function getUrl($app_root) {
		return $app_root.'/collection/' . $this->ascii_id;
	}

	public function createAscii() {
		if (!$this->collection_name) {
			return false;
		}
		$ascii_id = trim(preg_replace('/(collection|archive)/i','',$this->collection_name));
		$ascii_id = preg_replace('/ /i',"_",$ascii_id);
		$ascii_id = strtolower(preg_replace('/(__|_$)/','',$ascii_id));
		return $ascii_id;
	}

	public function expunge($messages = false)
	{
		$items = new Dase_DBO_Item($this->db);
		$items->collection_id = $this->id;
		foreach ($items->find() as $item) {
			$this->log->info("item $this->ascii_id:$item->serial_number deleted");
			if ($messages) {
				print "item $this->ascii_id:$item->serial_number deleted\n";
			}
			$item->expunge();
		}
		$item_types = new Dase_DBO_ItemType($this->db);
		$item_types->collection_id = $this->id;
		foreach ($item_types->find() as $type) {
			$type->expunge();
		}
		$coll_cats = new Dase_DBO_CollectionCategory($this->db);
		$coll_cats->collection_id = $this->id;
		foreach ($coll_cats->find() as $cc) {
			$cc->delete();
		}
		$atts = new Dase_DBO_Attribute($this->db);
		$atts->collection_id = $this->id;
		foreach ($atts->find() as $a) {
			$a->delete();
		}	
		$itrs = new Dase_DBO_ItemTypeRelation($this->db);
		$itrs->collection_ascii_id = $this->ascii_id;
		foreach ($itrs->find() as $itr) {
			$itr->delete();
		}
		$cms = new Dase_DBO_CollectionManager($this->db);
		$cms->collection_ascii_id = $this->ascii_id;
		foreach ($cms->find() as $cm) {
			$cm->delete();
		}
		$this->delete();
		$this->log->info("$this->ascii_id deleted");
		if ($messages) {
			print "$this->ascii_id collection deleted\n";
		}
	}

	function getSerialNumbers()
	{
		$sernums = array();
		foreach ($this->getItems() as $item) {
			$sernums[] = $item->serial_number;
		}
		return $sernums;
	}

	function getBaseAtomFeed($app_root) 
	{
		$feed = new Dase_Atom_Feed;
		$feed->setTitle($this->collection_name);
		if ($this->description) {
			$feed->setSubtitle($this->description);
		}
		$feed->setUpdated($this->updated);
		$feed->addCategory($app_root,"http://daseproject.org/category/base_url");
		$feed->addCategory($this->item_count,"http://daseproject.org/category/item_count");
		//todo: is this too expensive??
		$comm = $this->getCommunity();
		if ($comm) {
			$feed->addCategory($comm->term,$comm->getScheme(),$comm->label);
		}
		$feed->setId($this->getUrl($app_root));
		$feed->addAuthor();
		$feed->addLink($this->getUrl($app_root),'alternate');
		$feed->addLink($this->getUrl($app_root).'/service','service','application/atomsvc+xml',null,'AtomPub Service Document');
		return $feed;
	}

	function getAttributesAtom($app_root) {
		$feed = $this->getBaseAtomFeed($app_root);
		$feed->setFeedType('attributes');
		foreach ($this->getAttributes() as $att) {
			$att->injectAtomEntryData($feed->addEntry(),$app_root);
		}
		return $feed;
	}

	function getItemTypesAtom($app_root) {
		$feed = new Dase_Atom_Feed;
		$feed->setTitle($this->collection_name.' Item Types');
		$feed->setUpdated($this->updated);
		$comm = $this->getCommunity();
		if ($comm) {
			$feed->addCategory($comm->term,$comm->getScheme(),$comm->label);
		}
		$feed->setId($this->getUrl($app_root));
		$feed->addAuthor();
		$feed->addCategory($app_root,"http://daseproject.org/category/base_url");
		$feed->addLink($this->getUrl($app_root),'alternate');
		$feed->addLink($this->getUrl($app_root).'/service','service','application/atomsvc+xml',null,'AtomPub Service Document');
		$feed->setFeedType('item_types');
		foreach ($this->getItemTypes() as $it) {
			$it->injectAtomEntryData($feed->addEntry(),$app_root);
		}
		return $feed;
	}

	function asAtom($app_root,$limit = 5)
	{
		$feed = $this->getBaseAtomFeed($app_root);
		$feed->setFeedType('collection');
		$feed->addLink($app_root.'/collection/'.$this->ascii_id.'.atom','self');
		$feed->addCategory($app_root,"http://daseproject.org/category/base_url");
		$items = new Dase_DBO_Item($this->db);
		$items->collection_id = $this->id;
		if ($limit && is_numeric($limit)) {
			$items->setLimit($limit);
		}
		$items->orderBy('updated DESC');
		foreach ($items->find() as $item) {
			$feed->addItemEntry($item,$app_root);
		}
		return $feed->asXml();
	}

	function asAtomEntry($app_root)
	{
		$entry = new Dase_Atom_Entry();
		$entry->setId($app_root.'/collection/'.$this->ascii_id);
		$entry->setTitle($this->collection_name);
		$entry->setEntryType('collection');
		$entry->addLink($app_root.'/collection/'.$this->ascii_id.'.atom','self');
		$entry->addLink($app_root.'/collection/'.$this->ascii_id);
		if ($this->is_public) {
			$pub = "public";
		} else {
			$pub = "private";
		}
		$entry->addCategory($app_root.'',"http://daseproject.org/category/base_url");
		$entry->addCategory($pub,"http://daseproject.org/category/visibility");
		$entry->addCategory($this->item_count,"http://daseproject.org/category/item_count");
		return $entry->asXml();
	}

	static function dataAsJson($db)
	{
		$result = array();
		$colls = new Dase_DBO_Collection($db);
		foreach ($colls->find() as $c) {
			$result[$c->ascii_id]['visibility'] = $c->visibility;
			$result[$c->ascii_id]['path_to_media_files'] = Dase_Config::get('path_to_media').'/'.$c->ascii_id;
		}
		return Dase_Json::get($result);
	}

	static function listAsArray($db,$public_only = false)
	{
		$colls = new Dase_DBO_Collection($db);
		$colls->orderBy('collection_name');
		if ($public_only) {
			$colls->is_public = 1;
		} 
		foreach ($colls->find() as $c) {
			$result[] = clone($c);
		}
		return $result;
	}

	static function listAsJson($db,$public_only = false)
	{
		$colls = new Dase_DBO_Collection($db);
		$colls->orderBy('collection_name');
		if ($public_only) {
			$colls->is_public = 1;
		} 
		foreach ($colls->find() as $c) {
			foreach ($c as $k => $v) {
				$coll_array[$k] = $v;
			}
			$result[] = $coll_array;
		}
		return Dase_Json::get($result);
	}

	static function listAsAtom($db,$app_root,$public_only = false)
	{
		$c = new Dase_DBO_Collection($db);
		$c->orderBy('collection_name');
		if ($public_only) {
			$c->is_public = 1;
		} 
		$cs = $c->find();
		$feed = new Dase_Atom_Feed;
		$feed->setTitle('DASe Collections');
		$feed->setId($app_root);
		$feed->setFeedType('collection_list');
		//todo:fix this to *not* simply be a time stamp
		$feed->setUpdated(Dase_DBO_Collections::getLastCreated($db));
		$feed->addAuthor('DASe (Digital Archive Services)','http://daseproject.org');
		$feed->addLink($app_root.'/collections.atom','self');
		$feed->addCategory($app_root,"http://daseproject.org/category/base_url");
		foreach ($cs as $coll) {
			$entry = $feed->addEntry();
			$entry->setTitle($coll->collection_name);
			$entry->setContent(str_replace('_collection','',$coll->ascii_id));
			$entry->setId($coll->getUrl($app_root));
			$entry->setUpdated($coll->created);
			$entry->setEntryType('collection');
			$entry->addLink($coll->getUrl($app_root).'.atom','self');
			$entry->addLink($coll->getUrl($app_root),'alternate');
			if ($coll->is_public) {
				$pub = "public";
			} else {
				$pub = "private";
			}
			$entry->addCategory($coll->item_count,"http://daseproject.org/category/item_count");
			$entry->addCategory($pub,"http://daseproject.org/category/visibility");
		}
		return $feed->asXML();
	}

	static function getLastCreated($db)
	{
		$prefix = $db->table_prefix;
		$sql = "
			SELECT created
			FROM {$prefix}collection
			ORDER BY created DESC
			";
		//returns first non-null created
		foreach (Dase_DBO::query($db,$sql) as $row) {
			if ($row['created']) {
				return $row['created'];
			}
		}
	}

	static function getLookupArray($db)
	{
		$hash = array();
		$c = new Dase_DBO_Collection($db);
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
		$prefix = $this->db->table_prefix;
		$sql = "
			SELECT m.dase_user_eid,m.auth_level,m.expiration,m.created,u.name 
			FROM {$prefix}collection_manager m,{$prefix}dase_user u 
			WHERE m.collection_ascii_id = ?
			AND m.dase_user_eid = u.eid
			ORDER BY m.dase_user_eid";
		return Dase_DBO::query($sql,array($this->ascii_id),true);
	}

	function getAttributes($sort = 'sort_order')
	{
		$att = new Dase_DBO_Attribute($this->db);
		$att->collection_id = $this->id;
		$att->orderBy($sort);
		return $att->find();
	}

	public function resortAttributesByName() 
	{
		$new_sort_order = 0;
		foreach ($this->getAttributes('attribute_name') as $att) {
			$new_sort_order++;
			$att->sort_order = $new_sort_order;
			$att->fixBools();
			$att->update();
		}
	}

	function getAttributesSortedArray($sort = 'sort_order')
	{
		$att_array[] = 'First';
		foreach ($this->getAttributes($sort) as $att) {
			$att_array[] = "after ".$att->attribute_name;
		}
		return $att_array;
	}

	function changeAttributeSort($att_ascii_id,$new_so)
	{
		$prefix = $this->db->table_prefix;
		$att_ascii_id_array = array();
		$sql = "
			SELECT ascii_id 
			FROM {$prefix}attribute
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
			UPDATE {$prefix}attribute
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
		$att = new Dase_DBO_Attribute($this->db);
		$att->collection_id = 0;
		$att->orderBy('sort_order');
		return $att->find();
	}

	function updateItemCount()
	{
		$items = new Dase_DBO_Item($this->db);
		$items->collection_id = $this->id;
		$this->item_count = $items->findCount();
		$this->updated = date(DATE_ATOM);
		//postgres boolean weirdness make this necessary
		if (!$this->is_public) {
			$this->is_public = 0;
		}
		$this->update();
	}

	function getItems($limit='')
	{
		$item = new Dase_DBO_Item($this->db);
		$item->collection_id = $this->id;
		if ($limit && is_numeric($limit)) {
			$item->setLimit($limit);
		}
		return $item->find();
	}

	function getItemIdRange($start,$count)
	{
		$prefix = $this->db->table_prefix;
		$sql = "
			SELECT id 
			FROM {$prefix}item
			WHERE collection_id = ?
			ORDER BY updated DESC
			";
		$i = 0;
		$total = 0;
		$item_id_array = array();
		foreach (Dase_DBO::query($this->db,$sql,array($this->id)) as $row) {
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
		$res = array();
		$types = new Dase_DBO_ItemType($this->db);
		$types->collection_id = $this->id;
		$types->orderBy('name');
		foreach ($types->find() as $t) {
			$res[] = clone $t;
		}
		return $res;
	}

	function getItemTypeRelations()
	{
		$res = array();
		$rels = new Dase_DBO_ItemTypeRelation($this->db);
		$rels->collection_ascii_id = $this->ascii_id;
		foreach ($rels->find() as $r) {
			$res[] = clone $r;
		}
		return $res;
	}

	public function buildSearchIndex()
	{
		$prefix = $this->db->table_prefix;
		$dbh = $db->getDbh();
		//todo: make sure this->id is an integer
		$db->query("DELETE FROM {$prefix}search_table WHERE collection_id = $this->id");
		$db->query("DELETE FROM {$prefix}admin_search_table WHERE collection_id = $this->id");
		$items = new Dase_DBO_Item($this->db);
		$items->collection_id = $this->id;
		foreach ($items->find() as $item) {
			//search table
			$composite_value_text = '';
			//NOTE: '= true' works for mysql AND postgres!
			$sql = "
				SELECT value_text
				FROM {$prefix}value v
				WHERE item_id = ?
				AND v.value_text != ''
				AND v.attribute_id in (SELECT id FROM {$prefix}attribute a where a.in_basic_search = true)
				";
			$st = $dbh->prepare($sql);
			$st->execute(array($item->id));
			while ($value_text = $st->fetchColumn()) {
				$composite_value_text .= $value_text . " ";
			}
			foreach ($item->getContents() as $c) {
				$composite_value_text .= $c->text . " ";
			}
			$search_table = new Dase_DBO_SearchTable($this->db);
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
				FROM {$prefix}value
				WHERE item_id = ?
				";
			$st = $dbh->prepare($sql);
			$st->execute(array($item->id));
			while ($value_text = $st->fetchColumn()) {
				$composite_value_text .= $value_text . " ";
			}
			$search_table = new Dase_DBO_AdminSearchTable($this->db);
			$search_table->value_text = $composite_value_text;
			$search_table->item_id = $item->id;
			$search_table->collection_id = $this->id;
			$search_table->collection_ascii_id = $this->ascii_id;
			$search_table->updated = date(DATE_ATOM);
			$search_table->insert();
		}
		return true;
	}

	function getSearchIndexArray()
	{
		$search_index_array = array();
		$prefix = $this->db->table_prefix;
		foreach ($this->getItems() as $item) {
			$composite_value_text = '';
			$sql = "
				SELECT value_text
				FROM {$prefix}value v
				WHERE v.item_id = $item->id
				AND v.value_text != ''
				AND v.attribute_id in (SELECT id FROM {$prefix}attribute a where collection_id != 0)
				";
			foreach (Dase_DBO::query($this->db,$sql) as $row) {
				$composite_value_text .= " ".$row['value_text'];
			}
			$search_index_array[$item->serial_number] = $composite_value_text;
		}
		return $search_index_array;
	}

	public function getItemsByAttAsAtom($attribute_ascii_id,$app_root)
	{
		$feed = $this->getBaseAtomFeed($app_root);
		$feed->setFeedType('items');
		$att = Dase_DBO_Attribute::get($this->db,$this->ascii_id,$attribute_ascii_id);
		$vals = new Dase_DBO_Value($this->db);
		$vals->attribute_id = $att->id;
		foreach ($vals->find() as $val) {
			$item = new Dase_DBO_Item($this->db);
			$item->load($val->item_id);
			//use cached ???
			$entry = $item->injectAtomEntryData($feed->addEntry(),$app_root);
			$entry->setSummary($item->getValue($attribute_ascii_id));
		}
		return $feed->asXML($app_root);
	}

	function createNewItem($serial_number=null,$eid=null)
	{
		if (!$eid) {
			$eid = '_dase';
		}
		$item = new Dase_DBO_Item($this->db);
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
			$item->p_collection_ascii_id = $this->ascii_id;
			$item->insert();
			$this->updateItemCount();
			return $item;
		} else {
			$item->status = 'public';
			$item->item_type_id = 0;
			$item->created = date(DATE_ATOM);
			$item->created_by_eid = $eid;
			$item->p_collection_ascii_id = $this->ascii_id;
			$item->insert();
			$item->serial_number = sprintf("%09d",$item->id);
			$item->updated = date(DATE_ATOM);
			$item->update();
			$this->updateItemCount();
			return $item;
		}
	}

	public function getAtompubServiceDoc($app_root) 
	{
		$svc = new Dase_Atom_Service;	
		$ws = $svc->addWorkspace($this->collection_name.' Workspace');
		$coll = $ws->addCollection($app_root.'/collection/'.$this->ascii_id.'.atom',$this->collection_name.' Items');
		$coll->addAccept('application/atom+xml;type=entry');
		$coll->addCategorySet()->addCategory('item','http://daseproject.org/category/entrytype');
		$atts = $coll->addCategorySet('yes','http://daseproject.org/category/metadata');
		foreach ($this->getAttributes() as $att) {
			$atts->addCategory($att->ascii_id,'',$att->attribute_name);
		}
		$ws->addCollection($app_root.'/collection/'.$this->ascii_id.'.atom',$this->collection_name.' JSON Items')
			->addAccept('application/json');
		$media_repos = $app_root.'/media/'.$this->ascii_id.'.atom';
		$media_coll = $ws->addCollection($media_repos,$this->collection_name.' Media');
		foreach(Dase_Media::getAcceptedTypes() as $type) {
			//$media_coll->addAccept($type,true);
			$media_coll->addAccept($type);
		}
		$item_types_repos = $app_root.'/collection/'.$this->ascii_id.'/item_types.atom';
		$ws->addCollection($item_types_repos,$this->collection_name.' Item Types')
			->addAccept('application/atom+xml;type=entry')
			->addCategorySet()
			->addCategory('item_type','http://daseproject.org/category/entrytype');
		$attributes_repos = $app_root.'/collection/'.$this->ascii_id.'/attributes.atom';
		$atts_repos = $ws->addCollection($attributes_repos,$this->collection_name.' Attributes');
		$atts_repos->addAccept('application/atom+xml;type=entry')->addCategorySet()
			->addCategory('attribute','http://daseproject.org/category/entrytype','',true);
		$html_inp_types = $atts_repos->addAccept('application/atom+xml;type=entry')
			->addCategorySet('yes','http://daseproject.org/category/html_input_type');
		foreach (array('text','textarea','select','radio','checkbox','noedit','list') as $inp) {
			$html_inp_types->addCategory($inp);
		}
		return $svc->asXml();
	}

	public function getItemTypesAtompubServiceDoc($app_root) 
	{
		$svc = new Dase_Atom_Service;	
		$ws = $svc->addWorkspace($this->collection_name.' Item Types Workspace');
		$item_types_repos = $app_root.'/collection/'.$this->ascii_id.'/item_types.atom';
		$coll = $ws->addCollection($item_types_repos,$this->collection_name.' Item Types');
		$coll->addAccept('application/atom+xml;type=entry')
			->addCategorySet()
			->addCategory('item_type','http://daseproject.org/category/entrytype');
		$atts = $coll->addCategorySet('yes','http://daseproject.org/category/metadata');
		foreach ($this->getAttributes() as $att) {
			$atts->addCategory($att->ascii_id,'',$att->attribute_name);
		}
		$parent_types = $coll->addCategorySet('yes','http://daseproject.org/category/parent_item_type');
		$child_types = $coll->addCategorySet('yes','http://daseproject.org/category/child_item_type');
		foreach ($this->getItemTypes() as $it) {
			$parent_types->addCategory($it->ascii_id,'',$it->name);
			$child_types->addCategory($it->ascii_id,'',$it->name);
		}
		return $svc->asXml();
	}

	public function setCommunity($community_term,$community_label='')
	{
		Dase_DBO_Category::set($this->db,$this,'community',$community_term,$community_label);
	}

	public function getCommunity()
	{
		return Dase_DBO_Category::get($this->db,$this,'community');
	}
}
