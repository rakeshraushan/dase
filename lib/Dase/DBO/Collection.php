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

	public function getLastSerialNumber($begins_with)
	{
		$item = new Dase_DBO_Item($this->db);
		$item->collection_id = $this->id;
		$item->orderBy('serial_number DESC');
		if (false !== $begins_with) {
			$item->addWhere('serial_number',$begins_with.'%','like');
		}
		if ($item->findOne()) {
			return $item->serial_number;
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

	/** called reduce since empty is reserved */
	public function reduce($messages = false)
	{
		$items = new Dase_DBO_Item($this->db);
		$items->collection_id = $this->id;
		foreach ($items->find() as $item) {
			Dase_Log::info(LOG_FILE,"item $this->ascii_id:$item->serial_number deleted");
			if ($messages) {
				print "item $this->ascii_id:$item->serial_number deleted\n";
			}
			$item->expunge();
		}
	}

	public function expunge($messages = false)
	{
		$items = new Dase_DBO_Item($this->db);
		$items->collection_id = $this->id;
		foreach ($items->find() as $item) {
			Dase_Log::info(LOG_FILE,"item $this->ascii_id:$item->serial_number deleted");
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

		$atts = new Dase_DBO_Attribute($this->db);
		$atts->collection_id = $this->id;
		foreach ($atts->find() as $a) {
			$a->delete();
		}	

		$cms = new Dase_DBO_CollectionManager($this->db);
		$cms->collection_ascii_id = $this->ascii_id;
		foreach ($cms->find() as $cm) {
			$cm->delete();
		}
		$this->delete();
		Dase_Log::info(LOG_FILE,"$this->ascii_id deleted");
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
			$att->injectAtomEntryData($feed->addEntry(),$this->ascii_id,$app_root);
		}
		return $feed;
	}

	function getItemTypesAtom($app_root) {
		$feed = new Dase_Atom_Feed;
		$feed->setTitle($this->collection_name.' Item Types');
		$feed->setUpdated($this->updated);
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

	function getItemsBySerialNumberRangeAsAtom($app_root,$start,$end)
	{
		$feed = $this->getBaseAtomFeed($app_root);
		$feed->setFeedType('collection');
		$feed->addLink($app_root.'/collection/'.$this->ascii_id.'/items/range/'.$start.'/'.$end.'.atom','self');
		$feed->addCategory($app_root,"http://daseproject.org/category/base_url");
		$items = new Dase_DBO_Item($this->db);
		$items->collection_id = $this->id;
		$items->addWhere('serial_number',$start,'>=');
		$items->addWhere('serial_number',$end,'<=');
		$items->setLimit(100);
		$items->orderBy('updated DESC');
		foreach ($items->find() as $item) {
			$feed->addItemEntry($item,$app_root);
		}
		return $feed->asXml();
	}

	function asAtomEntry($app_root)
	{
		$entry = new Dase_Atom_Entry();
		return $this->injectAtomEntryData($entry,$app_root)->asXml();
	}

	function injectAtomEntryData(Dase_Atom_Entry $entry,$app_root)
	{
		$url = $this->getUrl($this->ascii_id,$app_root);
		$entry->setTitle($this->collection_name);
		$entry->setId($url);
		$entry->setUpdated($this->updated);
		$entry->addAuthor();
		$entry->addLink($url.'.atom');
		$entry->addLink($url.'.atom','edit');
		$entry->addCategory('collection','http://daseproject.org/category/entrytype');
		if ($this->description) {
			$entry->setSummary($this->description);
		} else {
			$entry->setSummary(str_replace('_collection','',$this->ascii_id));
		}

		if ($this->admin_notes) {
			$entry->setContent($this->admin_notes);
		}

		foreach ($this->getAttributes() as $a) {
			$entry->addCategory($a->ascii_id,"http://daseproject.org/category/attribute",$a->attribute_name);
		}
		foreach ($this->getItemTypes() as $item_type) {
			$entry->addCategory($item_type->ascii_id,"http://daseproject.org/category/item_type",$item_type->name);
		}

		if ($this->is_public) {
			$pub = "public";
		} else {
			$pub = "private";
		}
		$entry->addCategory($pub,"http://daseproject.org/category/status");

		if (!$this->visibility) {
			$this->visibility = 'user';
		}
		$entry->addCategory($this->visibility,"http://daseproject.org/category/visibility");
		$entry->addCategory($app_root,"http://daseproject.org/category/base_url");
		$entry->addCategory($this->item_count,"http://daseproject.org/category/item_count");
		return $entry;
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
		$feed->setUpdated(Dase_DBO_Collection::getLastCreated($db));
		$feed->addAuthor('DASe (Digital Archive Services)','http://daseproject.org');
		$feed->addLink($app_root.'/collections.atom','self');
		$feed->addCategory($app_root,"http://daseproject.org/category/base_url");
		foreach ($cs as $coll) {
			$coll->injectAtomEntryData($feed->addEntry(),$app_root);
		}
		return $feed->asXml();
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

	/**  note: this returns an array of manager (stdClass) objects
	 */
	function getManagers()
	{
		$prefix = $this->db->table_prefix;
		$sql = "
			SELECT m.dase_user_eid,m.auth_level,m.expiration,m.created,m.created_by_eid,u.name 
			FROM {$prefix}collection_manager m,{$prefix}dase_user u 
			WHERE m.collection_ascii_id = ?
			AND m.dase_user_eid = u.eid
			ORDER BY m.dase_user_eid";
		return Dase_DBO::query($this->db,$sql,array($this->ascii_id),true);
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

	function getAdminAttributes()
	{
		$att = new Dase_DBO_Attribute($this->db);
		$att->collection_id = 0;
		$att->orderBy('sort_order');
		return $att->find();
	}

	/** this is NOT visibility, it is public/private */ 
	function updateVisibility($visibility)
	{
		if ('public' == $visibility) {
			$this->is_public = 1;
			$this->update();
		}
		if ('private' == $visibility) {
			$this->is_public = 0;
			$this->update();
		}
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

		//reverse chronological so we can get "recent" w/ limit param
		$item->orderBy('updated DESC');

		if ($limit && is_numeric($limit)) {
			$item->setLimit($limit);
		}
		//note: MUST clone items 
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

	public function buildSearchIndex()
	{
		$item = null;
		foreach ($this->getItems() as $item) {
			$item = clone($item);
			//don't commit
			$item->buildSearchIndex(0,false);
		}
		//now commit
		if ($item) {
			$item->buildSearchIndex(0,true);
		}
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
				Dase_Log::info(LOG_FILE,"duplicate serial number: ".$serial_number);
				throw new Dase_Exception('duplicate serial number!');
				return;
			}
			$item->status = 'public';
			$item->item_type_id = 0;
			$item->item_type_ascii_id = 'default';
			$item->item_type_name = 'default';
			$item->created = date(DATE_ATOM);
			$item->updated = date(DATE_ATOM);
			$item->created_by_eid = $eid;
			$item->p_collection_ascii_id = $this->ascii_id;
			$item->collection_name = $this->collection_name;
			$item->insert();
			$this->updateItemCount();
			return $item;
		} else {
			$item->status = 'public';
			$item->item_type_id = 0;
			$item->item_type_ascii_id = 'default';
			$item->item_type_name = 'default';
			$item->created = date(DATE_ATOM);
			$item->created_by_eid = $eid;
			$item->p_collection_ascii_id = $this->ascii_id;
			$item->collection_name = $this->collection_name;
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
		$media_repos = $app_root.'/media/'.$this->ascii_id.'.atom';
		$media_coll = $ws->addCollection($media_repos,$this->collection_name.' Media');
		foreach(Dase_Media::getAcceptedTypes() as $type) {
			//$media_coll->addAccept($type,true);
			$media_coll->addAccept($type);
		}
		//json items collection
		$ws->addCollection($app_root.'/collection/'.$this->ascii_id.'.atom',$this->collection_name.' JSON Items')
			->addAccept('application/json');
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

	public  function xmlDump() {

		$db = $this->db;

		$admin_atts = new Dase_DBO_Attribute($db);
		$admin_atts->collection_id = 0;
		foreach ($admin_atts->find() as $aa) {
			$aa = clone($aa);
			$attribute_lookup[$aa->id] = $aa;
		}

		$prefix = $db->table_prefix;
		$writer = new XMLWriter();
		$writer->openMemory();
		$writer->setIndent(true);
		$writer->startDocument('1.0','UTF-8');
		$writer->startElement('archive');
		$writer->writeAttribute('archived_date',date(DATE_ATOM));
		$writer->writeAttribute('name',$this->collection_name);
		$writer->writeAttribute('id',$this->ascii_id);
		$writer->writeAttribute('created',$this->created);
		$writer->writeAttribute('item_count',$this->item_count);
		$writer->writeAttribute('is_public',$this->is_public);
		$writer->writeAttribute('visibility',$this->visibility);
		if ($this->description) {
			$writer->startElement('description');
			$writer->text($this->description);
			$writer->endElement();
		}
		if ($this->admin_notes) {
			$writer->startElement('admin_notes');
			$writer->text($this->admin_notes);
			$writer->endElement();
		}
		$attribute = new Dase_DBO_Attribute($this->db);
		$attribute->collection_id = $this->id;
		foreach($attribute->find() as $att) {
			$att = clone($att);
			$attribute_lookup[$att->id] = $att;
			$writer->startElement('att');
			$writer->writeAttribute('id',$att->ascii_id);
			$writer->writeAttribute('name',$att->attribute_name);
			$writer->writeAttribute('sort_order',$att->sort_order);
			$writer->writeAttribute('in_basic_search',$att->in_basic_search);
			$writer->writeAttribute('is_on_list_display',$att->is_on_list_display);
			$writer->writeAttribute('is_repeatable',$att->is_repeatable);
			$writer->writeAttribute('is_required',$att->is_required);
			$writer->writeAttribute('is_public',$att->is_public);
			if ($att->usage_notes) {
				$writer->writeAttribute('usage_notes',$att->usage_notes);
			}
			if ($att->mapped_admin_att_id) {
				$mapped = $attribute_lookup[$att->mapped_admin_att_id];
				$writer->writeAttribute('mapped_admin_att',$mapped->ascii_id);
			}
			$writer->writeAttribute('updated',$att->updated);
			$writer->writeAttribute('html_input_type',$att->html_input_type);
			if ($att->modifier_type) {
				$writer->writeAttribute('modifier_type',$att->modifier_type);
			}
			if ($att->modifier_defined_list) {
				$writer->writeAttribute('modifier_defined_list',$att->modifier_defined_list);
			}
			foreach ($att->getDefinedValues() as $df) {
				$writer->startElement('val');
				$writer->text($df);
				$writer->endElement();
			}
			$writer->endElement();
		}
		$item_type = new Dase_DBO_ItemType($this->db);
		$item_type->collection_id = $this->id;
		foreach($item_type->find() as $itype) {
			$itype = clone($itype);
			$item_type_lookup[$itype->id] = $itype;
			$writer->startElement('item_type');
			$writer->writeAttribute('id',$itype->ascii_id);
			$writer->writeAttribute('name',$itype->name);
			foreach ($itype->getAttributes() as $att) {
				$att = clone($att);
				$writer->startElement('att');
				$writer->writeAttribute('id',$att->ascii_id);
				$writer->writeAttribute('name',$att->attribute_name);
				$writer->endElement();
			}
			$writer->endElement();
		}
		foreach($this->getManagers() as $manager) {
			$writer->startElement('manager');
			$writer->writeAttribute('eid',$manager->dase_user_eid);
			$writer->writeAttribute('auth_level',$manager->auth_level);
			$writer->writeAttribute('created',$manager->created);
			$writer->writeAttribute('expiration',$manager->expiration);
			$writer->writeAttribute('created_by',$manager->created_by_eid);
			$writer->text($manager->name);
			$writer->endElement();
		}
		$i=0;
		$sql = "
			SELECT *
			FROM {$prefix}item
			WHERE collection_id = $this->id	
			";
		foreach (Dase_DBO::query($db,$sql) as $item) {
			$writer->startElement('item');
			$writer->writeAttribute('sernum',$item['serial_number']);
			$writer->writeAttribute('type',$item['item_type_ascii_id']);
			$writer->writeAttribute('created',$item['created']);
			$writer->writeAttribute('updated',$item['updated']);
			$writer->writeAttribute('status',$item['status']);

			/** metadata **/

			$sql = "
				SELECT * 
				FROM {$prefix}value v
				WHERE v.item_id = {$item['id']} 
			";
			foreach (Dase_DBO::query($db,$sql) as $val) {
				//in case orphaned value
				if (isset($attribute_lookup[$val['attribute_id']])) {
					$att = $attribute_lookup[$val['attribute_id']];
					$writer->startElement('meta');
					$writer->writeAttribute('att',$att->ascii_id);
					if ($val['modifier']) {
						$writer->writeAttribute('mod',$val['modifier']);
					}
					if ($val['url']) {
						$writer->writeAttribute('url',$val['url']);
					}
					$writer->text($val['value_text']);
					$writer->endElement();
				} else {
					$writer->startElement('orphan');
					$writer->writeAttribute('attribute_id',$att->id);
					if ($val['modifier']) {
						$writer->writeAttribute('mod',$val['modifier']);
					}
					if ($val['url']) {
						$writer->writeAttribute('url',$val['url']);
					}
					$writer->text($val['value_text']);
					$writer->endElement();
				}
			}

			/** comments **/

			if ($item['comments_count']) {
				$sql = "
					SELECT * 
					FROM {$prefix}comment
					WHERE comment.item_id = {$item['id']} 
				";
				foreach (Dase_DBO::query($db,$sql) as $comment) {
					$writer->startElement('comment');
					$writer->writeAttribute('type',$comment['type']);
					$writer->writeAttribute('updated',$comment['updated']);
					$writer->writeAttribute('eid',$comment['updated_by_eid']);
					$writer->text($comment['text']);
					$writer->endElement();
				}
			}

			/** content **/

			if ($item['content_length']) {
				$sql = "
					SELECT * 
					FROM {$prefix}content
					WHERE content.item_id = {$item['id']} 
				";
				foreach (Dase_DBO::query($db,$sql) as $content) {
					$writer->startElement('content');
					$writer->writeAttribute('type',$content['type']);
					$writer->writeAttribute('updated',$content['updated']);
					$writer->writeAttribute('eid',$content['updated_by_eid']);
					$writer->text($content['text']);
					$writer->endElement();
				}
			}


			/** media **/

			$sql = "
				SELECT * 
				FROM {$prefix}media_file
				WHERE media_file.item_id = {$item['id']} 
			";
			foreach (Dase_DBO::query($db,$sql) as $mf) {
				$writer->startElement('media');
				$writer->writeAttribute('filename',$mf['filename']);
				$writer->writeAttribute('size',$mf['size']);
				$writer->writeAttribute('mime',$mf['mime_type']);
				$writer->writeAttribute('w',$mf['width']);
				$writer->writeAttribute('h',$mf['height']);
				$writer->writeAttribute('len',$mf['file_size']);
				$writer->endElement();
			}
			$writer->endElement();
			error_log(++$i.' of '.$this->item_count.' ('.$this->collection_name.')');
		}
		$writer->endDocument();
		return $writer->flush(true);
	}

}
