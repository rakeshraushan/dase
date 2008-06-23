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
		}
	}

	public function initCart()
	{
		$tag = new Dase_DBO_Tag;
		$tag->dase_user_id = $this->id;
		$tag->type = 'cart';
		if (!$tag->findOne()) {
			$tag->created = date(DATE_ATOM);
			$tag->insert();
		}
	}

	public function getHttpPassword()
	{
		$this->http_password = substr(md5(Dase::getConfig('token').$this->eid.'httpbasic'),0,8);
		return $this->http_password;
	}

	public function getTags()
	{
		$tag_array = array();
		foreach (Dase_DBO_Tag::getByUser($this) as $row) {
			if (CART == $row['tag_type_id']) {
				//$tag_array['cart'][$row['ascii_id']] = $row['name'] . ' (' . $row['count'] . ')';
				$tag_array['cart'][$row['ascii_id']] = $row['count'];
			}
			if (USER_COLLECTION == $row['tag_type_id']) {
				$tag_array['user_collection'][$row['ascii_id']] = $row['name'] . ' (' . $row['count'] . ')';
			}
			if (SLIDESHOW == $row['tag_type_id']) {
				$tag_array['slideshow'][$row['ascii_id']] = $row['name'] . ' (' . $row['count'] . ')';
			}
		}
		$subs = new Dase_DBO_Subscription;
		$subs->dase_user_id = $this->id;
		foreach($subs->find() as $sub) {
			$tag = new Dase_DBO_Tag;
			$tag->load($sub->tag_id);
			if ($tag->name && $tag->ascii_id) {
				//note that I am overloading the ascii_id place w/ the id
				$key = "a" . $sub->tag_id;
				$tag_array['subscription'][$key] = $tag->name;
			}
		}
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
		$item_id_array = array();
		$db = Dase_DB::get();
		$sql = "
			SELECT ti.id,ti.item_id,t.id
			FROM tag t, tag_item ti
			WHERE t.id = ti.tag_id
			AND t.type = 'cart' 
			AND t.dase_user_id = ?
			";
		$sth = $db->prepare($sql);	
		$sth->execute(array($this->id));
		while (list($tag_item_id,$item_id,$tag_id) = $sth->fetch()) {
			$item_id_array[] = array(
				'tag_item_id' => $tag_item_id,
				'item_id' => $item_id,
				'tag_id' => $tag_id
			);
		}
		return Dase_Json::get($item_id_array);
	}

	function expireDataCache()
	{
		$cache = Dase_Cache::get($this->eid."_data");
		$cache->expire();
	}

	public function isSuperuser()
	{
		if (in_array($this->eid,Dase::getConfig('superuser'))) {
			return true;
		}
		return false;
	}

	public function getSettings()
	{
		if ($this->isSuperuser()) {
			$this->is_superuser = 1;
		}
		$this->ppd = md5($this->eid . Dase::getConfig('ppd_token'));
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

	function can($auth_level,$entity_type,$entity)
	{
		switch ($entity_type) {
		case 'attribute':
			return $this->checkAttributeAuth($entity,$auth_level);
		case 'collection':
			return $this->checkCollectionAuth($entity,$auth_level);
		case 'item':
			return $this->checkItemAuth($entity,$auth_level);
		case 'tag':
			return $this->checkTagAuth($entity,$auth_level);
		default:
			return false;
		}
	}

}
