<?php
$u = Dase_User::get($params['eid']);
$tag = new Dase_DB_Tag;
$tag->load($params['id']);
if ($tag->dase_user_id != $u->id) {
	Dase::error(401);
}
$sx = new SimpleXMLElement("<tag/>");
$sx->addChild('name',$tag->name);
$sx->addChild('description',$tag->description);
$sx->addChild('tag_type',$tag->tag_type_id);
$sx->addChild('is_public',$tag->is_public);
$sx->addChild('background',$tag->background);
$sx->addChild('admin_collection_id',$tag->admin_collection_id);
$sx->addChild('ascii_id',$tag->ascii_id);
$sx->addChild('master_item_id',$tag->master_item_id);
$sx->addChild('created',$tag->created);
foreach($tag->getItemIds() as $index => $item_id) {
	$item = new Dase_DB_Item();
	$item->load($item_id);
	$item->collection || $item->getCollection();
	$item->item_type || $item->getItemType();
	$item->item_status || $item->getItemStatus();
	//merge 3 sets of xml results
	$value = new Dase_DB_Value;
	$value->item_id = $item->id;
	$media = new Dase_DB_MediaFile;
	$media->item_id = $item->id;
	$item_sx = $item->asSimpleXml();
	$item_sx->addChild('index',$index);
	$new_request_url = str_replace('tag','tag_item',$request_url);
	$tag_item_link_elem = $item_sx->addChild('tag_item_link');
	$tag_item_link_elem->addAttribute('url',$new_request_url . '?' . $query_string . '&num=' . $index);
	$sx = Dase_Util::simplexml_append($sx,Dase_Util::simplexml_append(Dase_Util::simplexml_append($item_sx,$value->resultSetAsSimpleXml()),$media->resultSetAsSimpleXml()));
}

Dase::display($sx->asXml());
