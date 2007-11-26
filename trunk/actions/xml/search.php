<?php
$search = Dase_Search::get($params);
$start = Dase::filterGet('start');
$start = $start ? $start : 1;
$max = Dase::filterGet('max');
$max = $max ? $max : MAX_ITEMS; 

$result = $search->getResult();
$request_url = $result['request_url'];
$query_string = $result['query_string'];

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
if ($end - $start > 0) {
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
$request_elem = $sx->addChild('request');
$request_elem->addAttribute('url',$request_url . '?' . $query_string);
if (isset($next)) {
	$next_elem = $sx->addChild('request-next');
	$next_elem->addAttribute('url',$request_url . '?' . $query_string . '&start=' . $next);
}
if (isset($previous)) {
	$previous_elem = $sx->addChild('request-previous');
	$previous_elem->addAttribute('url',$request_url . '?' . $query_string . '&start=' . $previous);
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
	$new_request_url = str_replace('search','search_item',$request_url);
	$search_item_link_elem = $item_sx->addChild('search_item_link');
	$search_item_link_elem->addAttribute('url',$new_request_url . '?' . $query_string . '&num=' . ($search_index + $start));
	$sx = Dase_Util::simplexml_append($sx,Dase_Util::simplexml_append(Dase_Util::simplexml_append($item_sx,$value->resultSetAsSimpleXml()),$media->resultSetAsSimpleXml()));
}

Dase::display($sx->asXml());
