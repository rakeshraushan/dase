<?php
if (isset($params['md5_hash'])) {
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

//switch to the simple xml interface here
$div = simplexml_import_dom($feed->setSubtitle());
$h2 = $div->addChild('h2',htmlspecialchars($subtitle));
$h2->addAttribute('class','searchEcho');
$ul = $div->addChild('ul');
$ul->addAttribute('class','searchTallies');
foreach ($result['tallies'] as $coll => $tal) {
	if ($tal['name'] && $tal['total']) {
		$tally_elem = $ul->addChild('li',htmlspecialchars($tal['name'] . ': ' . $tal['total']));
		$tally_elem->addAttribute('class',$coll);
	}
}

foreach($item_ids as $search_index => $item_id) {
	$item = new Dase_DB_Item();
	$item->load($item_id);
	$item->collection || $item->getCollection();
	$item->item_type || $item->getItemType();
	$item->item_status || $item->getItemStatus();
	$entry = $feed->addEntry();
	$item->injectAtomEntryData($entry);
}

Dase::display($feed->asXml());
