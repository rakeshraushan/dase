<?php
$coll = Dase_Collection::get('friesen_collection','xml');
$keywords = $coll->getAttVals('keyword');
asort($keywords);
$sx = new SimpleXMLElement($coll->getItemXmlBySerialNumber($params['serial_number']));
$items = array();
foreach ($sx->item as $item) {
	$kws = array();
	$it = array();
	$it['serial_number'] = (string) $item['serial_number'];
	list($thumbnail) = $item->xpath("media_file[@size='thumbnail']/./@filename");
	list($viewitem) = $item->xpath("media_file[@size='viewitem']/./@filename");
	if ($item->xpath("metadata[@attribute_ascii_id='caption']")) {
		list($caption) = $item->xpath("metadata[@attribute_ascii_id='caption']");
		$it['caption'] = (string) $caption;
	}
	if ($item->xpath("metadata[@attribute_ascii_id='title']")) {
		list($title) = $item->xpath("metadata[@attribute_ascii_id='title']");
		$it['title'] = (string) $title;
	}
	if ($item->xpath("metadata[@attribute_ascii_id='text']")) {
		list($text) = $item->xpath("metadata[@attribute_ascii_id='text']");
		$it['text'] = (string) $text;
	}
	foreach ($item->xpath("metadata[@attribute_ascii_id='keyword']") as $kw) {
		$kws[] = (string) $kw;
	}
	$it['thumbnail'] = (string) $thumbnail;
	$it['viewitem'] = (string) $viewitem;
	asort($kws);
	$it['keywords'] = $kws;
	$items[] = $it;
}
$tpl = Dase_Template::instance('friesen');
$tpl->assign('keywords',$keywords);

if (1 == count($items)) {
	$tpl->assign('item',$items[0]);
	$tpl->display('view_item.tpl');
	exit;
}

$tpl->assign('items',$items);
$tpl->display('view_search_items.tpl');
exit;

