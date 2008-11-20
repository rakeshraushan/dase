<?php

require_once 'Dase/DBO/Autogen/DaseUser.php';

class Dase_DBO_DaseUser extends Dase_DBO_Autogen_DaseUser 
{
	public $is_superuser=0;
	public $ppd;
	public $http_password;

	public static function get($eid)
	{
		$user = new Dase_DBO_DaseUser;
		$user->eid = $eid;
		return $user->findOne();
	}

	public static function init($eid)
	{
		$user = new Dase_DBO_DaseUser;
		$user->eid = $eid;
		if (!$user->findOne()) {
			//todo: this should trigger (in calling function) a redirect to settings/register page
			return false;
		} else {
			$user->initDisplayPreferences();
			$user->initCart();
			return true;
		}
	}

	public static function findByNameSubstr($str)
	{
		$set = array();
		$users = new Dase_DBO_DaseUser;
		$like = Dase_DB::getCaseInsensitiveLikeOp();
		$users->addWhere('name','%'.$str.'%',$like);
		$users->orderBy('name');
		foreach ($users->find() as $u) {
			//so we can count easily
			$set[] = clone $u;
		}
		return $set;
	}

	public function initDisplayPreferences()
	{
		Dase_Cookie::set('max',$this->max_items);
		Dase_Cookie::set('display',$this->display);
	}

	/** create cart if none exists, also returns cart count */
	public function initCart()
	{
		$tag = new Dase_DBO_Tag;
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

	public function getHttpPassword()
	{
		$this->http_password = substr(md5(Dase_Config::get('token').$this->eid.'httpbasic'),0,12);
		return $this->http_password;
	}

	public static function listAsJson($limit=0)
	{
		$u = new Dase_DBO_DaseUser;
		if ($limit) {
			$u->setLimit($limit);
		}
		$user_array = array();
		foreach ($u->find() as $user) {
			$user_array[$user->eid] = $user->name;
		}
		return Dase_Json::get($user_array);
	}

	public function getTags($update_count = false)
	{
		$tag_array = array();
		foreach (Dase_DBO_Tag::getByUser($this) as $row) {
			if (!$row['item_count']) {
				$row['item_count'] = 0;
			}
			$tag_array[] = $row;
		}
		uasort($tag_array, array('Dase_Util','sortByTagName'));
		return $tag_array;
	}

	public function getCollections()
	{
		$cm = new Dase_DBO_CollectionManager;
		$cm->dase_user_eid = $this->eid;
		$special_colls = array();
		$user_colls = array();
		foreach ($cm->find() as $managed) {
			$special_colls[$managed->collection_ascii_id] = $managed->auth_level;
		}
		$coll = new Dase_DBO_Collection;
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
				$user_colls[] =  array(
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

	public function getData()
	{
		$user_data = array();
		//todo: is this is taking too long:
		$user_data[$this->eid]['cart_count'] = $this->initCart();
		$user_data[$this->eid]['tags'] = $this->getTags();
		$user_data[$this->eid]['htpasswd'] = $this->getHttpPassword();
		$user_data[$this->eid]['name'] = $this->name;
		$user_data[$this->eid]['collections'] = $this->getCollections();
		$user_data[$this->eid]['ppd'] = md5($this->eid . Dase_Config::get('ppd_token'));
		if ($this->isSuperuser()) {
			$user_data[$this->eid]['is_superuser'] = 1;
		}

		// per REST principles (i.e. "Roy says...")
		// the server need not ever know any of the following
		// and they shouldn't be stored in the DB (unless there 
		// is an expectation that it should be persisted).
		// this is all stuff that the client should be managing
		//
		$user_data[$this->eid]['current_collections'] = $this->current_collections ? $this->current_collections : '';
		$user_data[$this->eid]['backtrack'] = $this->backtrack;
		$user_data[$this->eid]['current_search_cache_id'] = $this->current_search_cache_id;
		$user_data[$this->eid]['display'] = $this->display;
		$user_data[$this->eid]['last_action'] = $this->last_action;
		$user_data[$this->eid]['last_item'] = $this->last_item;
		$user_data[$this->eid]['max_items'] = $this->max_items;
		$user_data[$this->eid]['controls'] = $this->cb;
		$user_data[$this->eid]['template_composite'] = $this->template_composite;
		return $user_data;
	}

	public function getDataJson()
	{
		return Dase_Json::get($this->getData());
	}

	public function getCartArray()
	{
		$prefix = Dase_Config::get('table_prefix');
		$item_array = array();
		$db = Dase_DB::get();
		$sql = "
			SELECT ti.id,t.id,ti.p_collection_ascii_id,ti.p_serial_number
			FROM {$prefix}tag t, {$prefix}tag_item ti
			WHERE t.id = ti.tag_id
			AND t.type = 'cart' 
			AND t.dase_user_id = ?
			";
		$sth = $db->prepare($sql);	
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

	function expireDataCache()
	{
		$cache = Dase_Cache::get($this->eid."_data");
		$cache->expire();
	}

	public function isSuperuser()
	{
		if (in_array($this->eid,array_keys(Dase_Config::get('superuser')))) {
			return true;
		}
		return false;
	}

	public function isManager()
	{
		$cm = new Dase_DBO_CollectionManager; 
		$cm->collection_ascii_id = $collection->ascii_id;
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
			Dase_Log::debug('attempting get to authorization for non-existing collection');
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
		$cm = new Dase_DBO_CollectionManager; 
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
		$prefix = Dase_Config::get('table_prefix');
		$tag_count = array();
		$db = Dase_DB::get();
		$sql = "
			SELECT tag.id, count(*) 
			FROM {$prefix}tag_item,{$prefix}tag 
			WHERE tag.id = tag_item.tag_id 
			AND dase_user_id = ? 
			GROUP BY tag.id
			";
		$sth = $db->prepare($sql);	
		$sth->execute(array($this->id));
		while (list($id,$count) = $sth->fetch()) {
			$tag_count[$id] = $count;
		}
		return $tag_count;
	}

	function getTagsAsAtom()
	{
		//todo: look at Dase_DBO_Tag::getByUser and maybe merge them
		//that one uses arrays, this, objects (so we get the 'inject...' method)
		$feed = new Dase_Atom_Feed;
		$feed->setTitle($this->eid.' sets');
		$feed->setId(APP_ROOT.'user/'.$this->eid.'/sets');
		$feed->setFeedType('sets');
		$feed->setUpdated(date(DATE_ATOM));
		$feed->addAuthor();
		$tags = new Dase_DBO_Tag;
		$tags->dase_user_id = $this->id;
		$tag_count_lookup = $this->getTagCountLookup();
		foreach ($tags->find() as $tag) {
			if ($tag->ascii_id) { //compat: make sure tag has ascii_id
				if (isset($tag_count_lookup[$tag->id])) {
					$count = $tag_count_lookup[$tag->id];
				} else {
					$count = 0;
				}
				$entry = $tag->injectAtomEntryData($feed->addEntry('set'));
				$entry->addCategory($count,"http://daseproject.org/category/tag/count");
			}
		}
		return $feed->asXml();
	}
}
