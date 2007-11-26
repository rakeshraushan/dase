<?php
$result = Dase_DB_Search::getResultByHash($params['md5_hash']);

$request_url = $result['request_url'];
$query_string = $result['query_string'];

$start = Dase::filterGet('start');
$start = $start ? $start : 1;
$max = Dase::filterGet('max');
$max = $max ? $max : MAX_ITEMS; 

if ($start > $result['count']) {
	$start = 1;
}

$next = $start + $max;
if ($next > $result['count']) {
	unset($next);
}
$previous = $start - $max;
if ($previous < 1) {
	unset($previous);
}

$item_ids = array_slice($result['item_ids'],$start-1,$max);

$end = $start + count($item_ids) - 1;
if ($end - $start) {
	$subtitle = ' results ' . $start . '-' . $end . ' of ' . $result['count'] . ' items for ' . $result['echo']; 
} else {
	$subtitle = ' no results for ' . $result['echo']; 

}
//note that we are only creating xml for THIS slice
$sx = new SimpleXMLElement("<items/>");
//add children here to describe search
$sx->addChild('total',$result['count']);
$subtitle = $sx->addChild('subtitle',$subtitle);
$request_url = str_replace('xml/','',$request_url);
//htmlspecialchars necessary here since it's element text (I believe...)
$sx->addChild('request',$request_url . '?' . htmlspecialchars($query_string));
if (isset($next)) {
	$sx->addChild('request-next',$request_url . '?' . htmlspecialchars($query_string) . '&amp;start=' . $next);
}
if (isset($previous)) {
	$sx->addChild('request-previous',$request_url . '?' . htmlspecialchars($query_string) . '&amp;start=' . $previous);
}
$sx->addChild('start',$start);
$sx->addChild('max',$max);
$sx->addChild('updated',$result['timestamp']);
$sx->addChild('hash',$result['hash']);
$tallies = $sx->addChild('tallies');
foreach ($result['tallies'] as $coll => $tal) {
	if ($tal['name'] && $tal['total']) {
		$tally_elem = $tallies->addChild('tally');
		$tally_elem->addAttribute('collection_ascii_id',$coll);
		$tally_elem->addAttribute('collection_name',$tal['name']);
		$tally_elem->addAttribute('total',$tal['total']);
	}
}
foreach($item_ids as $search_index => $item_id) {
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
	//since we need to do things like adding index,
	//best to have all this here in action that as
	//a method on Item class
	$item_sx->addChild('search_index',$search_index + $start);
	$sx = Dase_Util::simplexml_append($sx,Dase_Util::simplexml_append(Dase_Util::simplexml_append($item_sx,$value->resultSetAsSimpleXml()),$media->resultSetAsSimpleXml()));
}

Dase::display($sx->asXml());
