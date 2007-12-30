<?php
if ($params['md5_hash']) {
	$result = Dase_DB_Search::getResultByHash($params['md5_hash']);
} else {
	$result = Dase_Search::get($params)->getResult();
}

$start = Dase::filterGet('start');
$start = $start ? $start : 1;
$max = Dase::filterGet('max');
$max = $max ? $max : MAX_ITEMS; 

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
if ($end > $start) {
	$subtitle = ' results ' . $start . '-' . $end . ' of ' . $result['count'] . ' items for ' . $result['echo']; 
} elseif ($end == $start) {
	$subtitle = ' 1 result for ' . $result['echo']; 
} else {
	$subtitle = ' no results for ' . $result['echo']; 
}

$feed = new Dase_Atom_Feed();
$feed->addAuthor();
$feed->setTitle('DASe Search Result');
$feed->setSubtitle($subtitle);
$feed->addLink(APP_ROOT.'/'.$request_url.'?'.$query_string,'self');
$feed->setUpdated($result['timestamp']);
/*
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
 */
$feed->setId(APP_ROOT.'/search/'.$result['hash']);
$feed->setOpensearchTotalResults($result['count']);
$feed->setOpensearchStartIndex($start);
$feed->setOpensearchItemsPerPage($max);
//$tallies = $sx->addChild('tallies');
/*
foreach ($result['tallies'] as $coll => $tal) {
	if ($tal['name'] && $tal['total']) {
		$tally_elem = $tallies->addChild('tally');
		$tally_elem->addAttribute('collection_ascii_id',$coll);
		$tally_elem->addAttribute('collection_name',$tal['name']);
		$tally_elem->addAttribute('total',$tal['total']);
	}
}
 */
foreach($item_ids as $search_index => $item_id) {
	$item = new Dase_DB_Item();
	$item->load($item_id);
	$item->collection || $item->getCollection();
	$item->item_type || $item->getItemType();
	$item->item_status || $item->getItemStatus();
	$entry = $feed->addEntry();
	$item->injectAtomEntryData($entry);
	/*
	$item_sx->addChild('search_index',$search_index + $start);
	$new_request_url = str_replace('search','search_item',$request_url);
	$search_item_link_elem = $item_sx->addChild('search_item_link');
	$search_item_link_elem->addAttribute('url',$new_request_url . '?' . $query_string . '&num=' . ($search_index + $start));
	$sx = Dase_Util::simplexml_append($sx,Dase_Util::simplexml_append(Dase_Util::simplexml_append($item_sx,$value->resultSetAsSimpleXml()),$media->resultSetAsSimpleXml()));
	 */
}

Dase::display($feed->asXml());
