<?php

$u = Dase_User::get($params['eid']);
$tag = new Dase_DB_Tag;
$tag->dase_user_id = $u->id;
$tag->tag_type_id = CART;
$tag->findOne();
$tag_item = new Dase_DB_TagItem;
$tag_item->item_id = Dase::filterPost('item_id');
$tag_item->tag_id = $tag->id;
if ($tag_item->insert()) {
	echo "added cart item $tag_item->id";
} else {
	echo "add to cart failed";
}
