<?php

require_once 'Dase/DB/Autogen/DaseUser.php';

class Dase_DB_DaseUser extends Dase_DB_Autogen_DaseUser 
{
	public function getTags() {
		$tag_array;
		foreach (Dase_DB_Tag::getByUser($this) as $row) {
			if (CART == $row['tag_type_id']) {
				$tag_array[$this->eid]['cart'][$row['ascii_id']] = $row['name'];
			}
			if (USER_COLLECTION == $row['tag_type_id']) {
				$tag_array[$this->eid]['user_collection'][$row['ascii_id']] = $row['name'];
			}
			if (SLIDESHOW == $row['tag_type_id']) {
				$tag_array[$this->eid]['slideshow'][$row['ascii_id']] = $row['name'];
			}
		}
		$subs = new Dase_DB_Subscription;
		$subs->dase_user_id = $this->id;
		foreach($subs->findAll() as $row) {
			$tag = new Dase_DB_Tag;
			$tag->load($row['tag_id']);
			if ($tag->name && $tag->ascii_id) {
				$tag_array[$this->eid]['subscription'][$tag->ascii_id] = $tag->name;
			}
		}
		$j = new Dase_Json;
		return $j->encodeData($tag_array,10);
	}
}
