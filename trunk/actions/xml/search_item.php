<?php

$search = Dase_Search::get($params);
$num = Dase::filterGet('num');
$max = Dase::filterGet('max');
$max = $max ? $max : MAX_ITEMS; 
if (!$num) {
	$num = 1;
}
$result = $search->getResult();
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
	$item_sx = $item->getXml(false);
	$item_sx->addChild('num',$num);
	$request_elem = $item_sx->addChild('request');
	$request_elem->addAttribute('url',$item_request_url . '?' . $query_string . '&num=' . $num);
	$search_link_elem = $item_sx->addChild('search_link');
	$search_link_elem->addAttribute('url',$request_url . '?' . $query_string . '&start=' . $start);
	if (isset($next)) {
		//$pattern = '/num=(\d+)/';
		//$request_url = preg_replace($pattern, 'num='.$next, $request_url);
		$next_elem = $item_sx->addChild('request-next');
		$next_elem->addAttribute('url',$item_request_url . '?' . $query_string . '&num=' . $next);
	}
	if (isset($previous)) {
		//$pattern = '/num=(\d+)/';
		//$request_url = preg_replace($pattern, 'num='.$previous, $request_url);
		$prev_elem = $item_sx->addChild('request-previous');
		$prev_elem->addAttribute('url',$item_request_url . '?' . $query_string . '&num=' . $previous);
	}

	$subtitle = 'Item ' . $num . ' of ' . $result['count'] . ' items for ' . $result['echo']; 
	$item_sx->addChild('subtitle',$subtitle);
	Dase::display($item_sx->asXml());
}
Dase::error(404);

