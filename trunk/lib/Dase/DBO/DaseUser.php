<?php

require_once 'Dase/DBO/Autogen/DaseUser.php';

class Dase_DBO_DaseUser extends Dase_DBO_Autogen_DaseUser 
{
	private $superusers = array();
	public $http_password;
	public $is_superuser;
	public $is_service_user;
	public $ppd_token;
	public $token;

	public static function get($db,$eid) {
		$u = new Dase_DBO_DaseUser($db);
		return $u->retrieveByEid($eid);
	}

	/** this is case insensitive! */
	public function retrieveByEid($eid)
	{
		$prefix = $this->db->table_prefix;
		$dbh = $this->db->getDbh(); 
		$sql = "
			SELECT * FROM {$prefix}dase_user 
			WHERE lower(eid) = ?
			";	
		$sth = $dbh->prepare($sql);
		$sth->execute(array(strtolower($eid)));
		$row = $sth->fetch();
		if ($row) {
			foreach ($row as $key => $val) {
				$this->$key = $val;
			}
			return $this;
		} else {
			return false;
		}
	}

	public function getUrl($app_root)
	{
		return $app_root.'/user/'.$this->eid;
	}

	public static function findByNameSubstr($db,$str)
	{
		$set = array();
		$users = new Dase_DBO_DaseUser($db);
		$like = $db->getCaseInsensitiveLikeOp();
		$users->addWhere('name','%'.$str.'%',$like);
		$users->orderBy('name');
		foreach ($users->find() as $u) {
			//so we can count easily
			$set[] = clone $u;
		}
		return $set;
	}

	/** create cart if none exists, also returns cart count */
	public function initCart()
	{
		$tag = new Dase_DBO_Tag($this->db);
		$tag->dase_user_id = $this->id;
		$tag->type = 'cart';
		if (!$tag->findOne()) {
			$tag->eid = $this->eid;
			$tag->name = 'My Cart';
			$tag->ascii_id = 'cart';
			$tag->created = date(DATE_ATOM);
			$tag->insert();
		}
		if (!$tag->item_count) {
			$tag->item_count = 0;
		}
		return $tag->item_count;
	}

	public function getHttpPassword($token)
	{
		if (!$token) {
			throw new Dase_Exception('user auth is not set');
		}
		if (!$this->http_password) {
			$this->http_password = substr(md5($token.$this->eid.'httpbasic'),0,12);
		}
		return $this->http_password;
	}

	public static function listAsJson($db,$limit=0)
	{
		$u = new Dase_DBO_DaseUser($db);
		if ($limit) {
			$u->setLimit($limit);
		}
		$user_array = array();
		foreach ($u->find() as $user) {
			$user_array[$user->eid] = $user->name;
		}
		return Dase_Json::get($user_array);
	}

	public static function listAsAtom($db,$app_root,$limit=100)
	{
		
		$users = new Dase_DBO_DaseUser($db);
		if ($limit) {
			$users->setLimit($limit);
		}
		$feed = new Dase_Atom_Feed;
		$feed->setTitle('DASe Users');
		$feed->setId($app_root.'/users');
		$feed->setFeedType('user_list');
		//todo:fix this to *not* simply be a time stamp
		$feed->setUpdated(date(DATE_ATOM));
		$feed->addAuthor();
		$feed->addLink($app_root.'/users.atom','self');
		$users->orderBy('updated DESC');
		foreach ($users->find() as $user) {
			$entry = $feed->addEntry();
			$entry->setTitle($user->name);
			$entry->setId($user->getUrl($app_root));
			$entry->setUpdated($user->updated);
			$entry->setEntryType('user');
			$entry->setContent($user->eid);
			$entry->addLink($user->getUrl($app_root).'.atom','self');
		}
		return $feed->asXML($app_root);
	}

	public function asAtomEntry($app_root)
	{
		$entry = new Dase_Atom_Entry_User;
		$entry->setTitle($this->name);
		$entry->setId($this->getUrl($app_root));
		$entry->addAuthor();
		$entry->setUpdated($this->updated);
		$entry->setEntryType('user');
		$entry->setContent($this->eid);
		$entry->addLink($this->getUrl($app_root).'.atom','self');
		return $entry->asXML();
	}

	public function getTags($update_count = false)
	{
		$tag_array = array();
		foreach (Dase_DBO_Tag::getByUser($this) as $row) {
			if (!$row['item_count'] || 'null' == $row['item_count']) {
				$row['item_count'] = 0;
			}
			$tag_array[] = $row;
		}
		uasort($tag_array, array('Dase_Util','sortByTagName'));
		return $tag_array;
	}

	public function getCollections()
	{
		$cm = new Dase_DBO_CollectionManager($this->db);
		$cm->dase_user_eid = $this->eid;
		$special_colls = array();
		$user_colls = array();
		foreach ($cm->find() as $managed) {
			$special_colls[$managed->collection_ascii_id] = $managed->auth_level;
		}
		$coll = new Dase_DBO_Collection($this->db);
		$coll->orderBy('collection_name');
		foreach($coll->find() as $c) {
			if (!$c->item_count) {
				$c->item_count = 0;
			}
			if ((1 == $c->is_public) || (in_array($c->ascii_id,array_keys($special_colls)))) {
				if (isset($special_colls[$c->ascii_id])) {
					$auth_level = $special_colls[$c->ascii_id];
				} else {
					$auth_level = '';
				}
				$user_colls[$c->ascii_id] =  array(
					'id' => $c->id,
					'collection_name' => $c->collection_name,
					'ascii_id' => $c->ascii_id,
					'is_public' => $c->is_public,
					'item_count' => $c->item_count,
					'auth_level' => $auth_level
				);
			}
		}
		return $user_colls;
	}

	public function getData($auth_config)
	{
		if (!isset($auth_config['token']) || !isset($auth_config['ppd_token'])) {
			throw new Dase_Exception('missing auth config data');
		}
		if (!isset($auth_config['superusers'])) {
			$auth_config['superusers'] = array();
		}
		$user_data = array();
		//todo: is this is taking too long:
		$user_data[$this->eid]['cart_count'] = $this->initCart();
		$user_data[$this->eid]['tags'] = $this->getTags();
		$user_data[$this->eid]['htpasswd'] = $this->getHttpPassword($auth_config['token']);
		$user_data[$this->eid]['name'] = $this->name;
		$user_data[$this->eid]['collections'] = $this->getCollections();
		$user_data[$this->eid]['ppd'] = md5($this->eid.$auth_config['ppd_token']);
		if ($this->isSuperuser($auth_config['superusers'])) {
			$user_data[$this->eid]['is_superuser'] = 1;
		}

		// per REST principles (i.e. "Roy says...")
		// the server need not ever know any of the following
		// and they shouldn't be stored in the DB (unless there 
		// is an expectation that it should be persisted).
		// this is all stuff that the client should be managing
		//
		$user_data[$this->eid]['current_collections'] = $this->current_collections;
		$user_data[$this->eid]['backtrack'] = $this->backtrack;
		$user_data[$this->eid]['current_search_cache_id'] = $this->current_search_cache_id;
		$user_data[$this->eid]['display'] = $this->display;
		$user_data[$this->eid]['last_action'] = $this->last_action;
		$user_data[$this->eid]['last_item'] = $this->last_item;
		$user_data[$this->eid]['max_items'] = $this->max_items;
		$user_data[$this->eid]['controls'] = $this->cb;
		$user_data[$this->eid]['template_composite'] = $this->template_composite;
		$user_data[$this->eid]['token_date'] = date('Ymd',time());
		return $user_data;
	}

	public function getDataJson($auth_config)
	{
		return Dase_Json::get($this->getData($auth_config));
	}

	public function getCartArray()
	{
		$prefix = $this->db->table_prefix;
		$item_array = array();
		$dbh = $this->db->getDbh();
		$sql = "
			SELECT ti.id,t.id,ti.p_collection_ascii_id,ti.p_serial_number
			FROM {$prefix}tag t, {$prefix}tag_item ti
			WHERE t.id = ti.tag_id
			AND t.type = 'cart' 
			AND t.dase_user_id = ?
			";
		$sth = $dbh->prepare($sql);	
		$sth->execute(array($this->id));
		while (list($tag_item_id,$tag_id,$coll,$sernum) = $sth->fetch()) {
			$item_array[] = array(
				'tag_item_id' => $tag_item_id,
				'item_unique' => $coll.'/'.$sernum,
				'tag_id' => $tag_id
			);
		}
		return $item_array;
	}

	public function getCartJson()
	{
		return Dase_Json::get($this->getCartArray());
	}

	function expireDataCache($cache)
	{
		$cache->expire($this->eid."_data");
	}

	public function isSuperuser($superusers)
	{
		if (in_array($this->eid,array_keys($superusers))) {
			$this->is_superuser = true;
			return true;
		}
		return false;
	}

	/** if user is manager of ANY collection */
	public function isManager()
	{
		$cm = new Dase_DBO_CollectionManager($this->db); 
		$cm->dase_user_eid = $this->eid;
		$cm->addWhere('auth_level','none','!=');
		return $cm->findOne();
	}

	function checkAttributeAuth($attribute,$auth_level)
	{
		return $this->checkCollectionAuth($attribute->getCollection(),$auth_level);
	}

	function checkItemAuth($item,$auth_level)
	{
		if ($item->created_by_eid == $this->eid) {
			return true;
		} else {
			return $this->checkCollectionAuth($item->getCollection(),$auth_level);
		}
	}

	function checkCollectionAuth($collection,$auth_level)
	{
		if (!$collection) {
			$this->log->debug('attempting get to authorization for non-existing collection');
			return false;
		}
		if ('read' == $auth_level) {
			if (
				$collection->is_public || 
				'user' == $collection->visibility || 
				'public' == $collection->visibility
			) {
			return true;
			}
		}
		if ('write' == $auth_level) {
			if (
				'user' == $collection->visibility || 
				'public' == $collection->visibility
			) {
			return true;
			}
		}
		$cm = new Dase_DBO_CollectionManager($this->db); 
		$cm->collection_ascii_id = $collection->ascii_id;
		//todo: need to account for case here!
		//needs to be case-insensitive
		$cm->dase_user_eid = $this->eid;
		$cm->findOne();
		if ($cm->auth_level) {
			if ('read' == $auth_level) {
				return true;
			} elseif ('write' == $auth_level && in_array($cm->auth_level,array('write','admin','manager','superuser'))) {
				return true;
			} elseif ('admin' == $auth_level && in_array($cm->auth_level,array('admin','manager','superuser'))) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}	
	}

	function checkTagAuth($tag,$auth_level)
	{
		if (!$tag) {
			return false;
		}
		if ('read' == $auth_level && $tag->is_public) {
			return true;
		} 
		if ('read' == $auth_level && $tag->dase_user_id == $this->id) {
			return true;
		} 
		if ('write' == $auth_level && $tag->dase_user_id == $this->id) {
			return true;
		} 
		//in the case of tag, admin means tag includes items from one collection only
		//and the user has write privileges for that collection
		if ('admin' == $auth_level && 
			$tag->dase_user_id == $this->id &&
			$tag->isBulkEditable($this)
		) {
			return true;
		} 
		return false;
	}

	function can($auth_level,$entity)
	{
		//possible auth_levels: read, write, admin (other...?)
		$class = get_class($entity);
		switch ($class) {
		case 'Dase_DBO_Attribute':
			return $this->checkAttributeAuth($entity,$auth_level);
		case 'Dase_DBO_Collection':
			return $this->checkCollectionAuth($entity,$auth_level);
		case 'Dase_DBO_Item':
			return $this->checkItemAuth($entity,$auth_level);
		case 'Dase_DBO_Tag':
			return $this->checkTagAuth($entity,$auth_level);
		default:
			return false;
		}
	}

	function getTagCountLookup()
	{
		$prefix = $this->db->table_prefix;
		$tag_count = array();
		$dbh = $this->db->getDbh();
		$sql = "
			SELECT tag.id, count(*) 
			FROM {$prefix}tag_item,{$prefix}tag 
			WHERE tag.id = tag_item.tag_id 
			AND dase_user_id = ? 
			GROUP BY tag.id
			";
		$sth = $dbh->prepare($sql);	
		$sth->execute(array($this->id));
		while (list($id,$count) = $sth->fetch()) {
			$tag_count[$id] = $count;
		}
		return $tag_count;
	}

	function getTagsAsAtom($app_root)
	{
		//todo: look at Dase_DBO_Tag::getByUser and maybe merge them
		//that one uses arrays, this, objects (so we get the 'inject...' method)
		$feed = new Dase_Atom_Feed;
		$feed->setTitle($this->eid.' sets');
		$feed->setId($app_root.'/user/'.$this->eid.'/sets');
		$feed->setFeedType('sets');
		$feed->setUpdated(date(DATE_ATOM));
		$feed->addAuthor();
		$tags = new Dase_DBO_Tag($this->db);
		$tags->dase_user_id = $this->id;
		$tags->orderBy('updated DESC');
		$tag_count_lookup = $this->getTagCountLookup();
		foreach ($tags->find() as $tag) {
			if ($tag->ascii_id) { //compat: make sure tag has ascii_id
				if (isset($tag_count_lookup[$tag->id])) {
					$count = $tag_count_lookup[$tag->id];
				} else {
					$count = 0;
				}
				$entry = $tag->injectAtomEntryData($feed->addEntry('set'),$this,$app_root);
				$entry->addCategory($count,"http://daseproject.org/category/item_count");
			}
		}
		return $feed->asXml();
	}

	public function getAtompubServiceDoc($app_root) 
	{
		$svc = new Dase_Atom_Service;	
		$ws = $svc->addWorkspace('User '.$this->eid.' Workspace');
		$coll = $ws->addCollection($this->getUrl($app_root).'/sets.atom','User '.$this->eid.' Sets');
		$coll->addAccept('application/atom+xml;type=entry');
		$coll->addCategorySet()->addCategory('set','http://daseproject.org/category/entrytype');
		return $svc->asXml();
	}

}
