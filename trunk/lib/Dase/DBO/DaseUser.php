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
			$user->initCart();
			return true;
		}
	}

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
	}

	public function getHttpPassword()
	{
		$this->http_password = substr(md5(Dase_Config::get('token').$this->eid.'httpbasic'),0,12);
		return $this->http_password;
	}

	public static function listAsJson($limit = 10)
	{
		$u = new Dase_DBO_DaseUser;
		$u->setLimit($limit);
		$user_array = array();
		foreach ($u->find() as $user) {
			foreach ($user as $k => $v) {
				$user_array[$user->eid][$k] = $v;
			}
		}
		return Dase_Json::get($user_array);
	}

	public function getTags()
	{
		$tag_array = array();
		foreach (Dase_DBO_Tag::getByUser($this) as $row) {
			$tag_array[] = $row;
		}
		$subs = new Dase_DBO_Subscription;
		$subs->dase_user_id = $this->id;
		foreach($subs->find() as $sub) {
			$tag = new Dase_DBO_Tag;
			$tag->load($sub->tag_id);
			if ($tag->name && $tag->ascii_id) {
				$sub_tag['id'] = $tag->id;
				//note that I am overloading the ascii_id place w/ the id
				//to ensure uniqueness
				$sub_tag['ascii_id'] = "a" . $sub->tag_id;
				$sub_tag['name'] = $tag->name;
				$sub_tag['type'] = 'subscription';
				$sub_tag['count'] = '';
			}
			$tag_array[] = $sub_tag;
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
					'auth_level' => $auth_level
				);
			}
		}
		return $user_colls;
	}

	public function getData()
	{
		$user_data = array();
		//this is taking too long:
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
		$user_data[$this->eid]['current_collections'] = $this->current_collections;
		$user_data[$this->eid]['backtrack'] = $this->backtrack;
		$user_data[$this->eid]['current_search_cache_id'] = $this->current_search_cache_id;
		$user_data[$this->eid]['display'] = $this->display;
		$user_data[$this->eid]['last_action'] = $this->last_action;
		$user_data[$this->eid]['last_item'] = $this->last_item;
		$user_data[$this->eid]['max_items'] = $this->max_items;
		$user_data[$this->eid]['template_composite'] = $this->template_composite;
		return Dase_Json::get($user_data);
	}

	public function getCartJson()
	{
		$item_array = array();
		$db = Dase_DB::get();
		$sql = "
			SELECT ti.id,t.id,ti.p_collection_ascii_id,ti.p_serial_number
			FROM tag t, tag_item ti
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
		return Dase_Json::get($item_array);
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

	function checkAttributeAuth($attribute,$auth_level)
	{
		return $this->checkCollectionAuth($attribute->getCollection(),$auth_level);
	}

	function checkItemAuth($item,$auth_level)
	{
		return $this->checkCollectionAuth($item->getCollection(),$auth_level);
	}

	function checkCollectionAuth($collection,$auth_level)
	{
		if (!$collection) {
			return false;
		}
		if ('read' == $auth_level && $collection->is_public) {
			return true;
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
		if ($tag->dase_user_id == $this->id) {
			return true;
		} else {
			return false;
		}	
	}

	function can($auth_level,$entity)
	{
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

}
