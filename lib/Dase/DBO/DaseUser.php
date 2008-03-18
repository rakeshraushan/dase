<?php

require_once 'Dase/DBO/Autogen/DaseUser.php';

class Dase_DBO_DaseUser extends Dase_DBO_Autogen_DaseUser 
{

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

	public function getCart()
	{
		$item_id_array = array();
		$db = Dase_DB::get();
		$sql = "
			SELECT ti.id,ti.item_id,t.id
			FROM tag t, tag_item ti
			WHERE t.id = ti.tag_id
			AND t.dase_user_id = ?
			AND t.tag_type_id = ?
			";
		$sth = $db->prepare($sql);	
		$sth->execute(array($this->id,CART));
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
		$cache = new Dase_Cache("json/user/$this->eid/data");
		$cache->expire();
	}

	public function isSuperuser()
	{
		if (in_array($this->eid,Dase::getConf('superuser'))) {
			return true;
		}
		return false;
	}

	public function asSimpleXml()
	{
		$sx = simplexml_load_string("<user/>");
		foreach($this as $k => $v) {
			$sx->addChild($k,htmlspecialchars($v));
		}
		$superuser = 0;
		if ($this->isSuperuser()) {
			$superuser = 1;
		}
		$sx->addChild('superuser',$superuser);
		$sx->addchild('ppd',md5($this->eid . Dase::getConf('ppd_token')));
		return $sx;
	}
}
