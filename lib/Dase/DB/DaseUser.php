<?php

require_once 'Dase/DB/Autogen/DaseUser.php';

class Dase_DB_DaseUser extends Dase_DB_Autogen_DaseUser 
{
	public function getTags() {
		$tag_array;
		foreach (Dase_DB_Tag::getByUser($this) as $row) {
			if (CART == $row['tag_type_id']) {
				$tag_array['cart'][$row['ascii_id']] = $row['name'];
			}
			if (USER_COLLECTION == $row['tag_type_id']) {
				$tag_array['user_collection'][$row['ascii_id']] = $row['name'];
			}
			if (SLIDESHOW == $row['tag_type_id']) {
				$tag_array['slideshow'][$row['ascii_id']] = $row['name'];
			}
		}
		$subs = new Dase_DB_Subscription;
		$subs->dase_user_id = $this->id;
		foreach($subs->findAll() as $row) {
			$tag = new Dase_DB_Tag;
			$tag->load($row['tag_id']);
			if ($tag->name && $tag->ascii_id) {
				$tag_array['subscription'][$tag->ascii_id] = $tag->name;
			}
		}
		return $tag_array;
	}

	public function getCollections() {
		$cm = new Dase_DB_CollectionManager;
		$cm->dase_user_eid = $this->eid;
		$special_colls = array();
		$user_colls = array();
		foreach ($cm->findAll() as $row) {
			$special_colls[] = $row['collection_ascii_id'];
		}
		$coll = new Dase_DB_Collection;
		$coll->orderBy('collection_name');
		foreach($coll->getAll() as $row) {
			if ((1 == $row['is_public']) || (in_array($row['ascii_id'],$special_colls))) {
				$user_colls[] =  array(
					'id' => $row['id'],
					'collection_name' => $row['collection_name'],
					'ascii_id' => $row['ascii_id'],
					'is_public' => $row['is_public']
				);
			}
		}
		return $user_colls;
	}

	public function getData() {
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
		$j = new Dase_Json;
		return $j->encodeData($user_data);
	}
}
