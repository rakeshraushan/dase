<?php

$search = Dase_Search::get($params);
$num = Dase::filterGet('num');
$max = Dase::filterGet('max');
$max = $max ? $max : MAX_ITEMS; 
if (!$num) {
	$num = 1;
}
$result = $search->getResult();
//this will change:
$request_url = str_replace('xml/','',$result['request_url']);
$item_request_url = str_replace('search','search_item',$request_url);
$query_string = $result['query_string'];
$count = $result['count'];
$previous = 0;
$next = 0;
if ($num < $count) {
	$next = $num + 1;
}
if ($num > 1) {
	$previous = $num - 1;
}

$start = (floor($num/$max) * $max) + 1;

$item_id = $result['item_ids'][$num-1];
$item = new Dase_DB_Item;
if ($item->load($item_id)) {
	$feed = new Dase_Atom_Feed();
	$item->injectAtomFeedData($feed);
	$item->injectAtomEntryData($feed->addEntry());
	$feed->addLink($item_request_url . '?' . $query_string . '&num=' . $num,'http://daseproject.org/relation/search-item-link');
	$feed->addLink($request_url . '?' . $query_string . '&start=' . $start,'http://daseproject.org/relation/search-link');
	if (isset($next)) {
		$feed->addLink($item_request_url . '?' . $query_string . '&num=' . $next,'next','application/xhtml+xml');
	}
	if (isset($previous)) {
		$feed->addLink($item_request_url . '?' . $query_string . '&num=' . $previous,'previous','application/xhtml+xml');
	}
	$subtitle = 'Item ' . $num . ' of ' . $result['count'] . ' items for ' . $result['echo']; 
	$feed->setSubtitle($subtitle);
	Dase::display($feed->asXml());
}
Dase::error(404);
