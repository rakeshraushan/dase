<?php

$u = Dase_User::get($params['eid']);
$u->expireDataCache();
$tag_item = new Dase_DB_TagItem;
$tag_item->load($params['tag_item_id']);
$tag = new Dase_DB_Tag;
$tag->load($tag_item->tag_id);
if ($tag->dase_user_id == $u->id) {
	$tag_item->delete();
	echo "tag item {$params['tag_item_id']} deleted!";
	exit;
} else {
	Dase::error(401);
}
