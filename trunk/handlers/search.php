<?php

class SearchHandler
{

	//MOST of this should be in Dase_Search!!

	public static function opensearch($request)
	{

		if ($request->has('md5_hash')) {
			$result = Dase_DBO_SearchCache::get($request->get('md5_hash'));
		} else {
			$result = Dase_Search::get($request)->getResult();
		}

		$start = $request->get('start');
		$start = $start ? $start : 1;
		$max = $request->get('max');
		$max = $max ? $max : MAX_ITEMS; 

		$request_path = $request->path.'.html';
		$query_string = $request->queryString;

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
		$feed->addLink(APP_ROOT.'/'.$request_path.'?'.$query_string,'self');
		$feed->setUpdated($result['timestamp']);
		$feed->setFeedType('search');
		if (isset($next)) {
			$feed->addLink(APP_ROOT.'/'.$request_path.'?'.$query_string.'&start='.$next,'next');
		}
		if (isset($previous)) {
			$feed->addLink(APP_ROOT.'/'.$request_path.'?'.$query_string.'&start='.$previous,'previous');
		}
		$feed->setId(APP_ROOT.'/search/'.$result['hash']);
		$feed->setOpensearchTotalResults($result['count']);
		$feed->setOpensearchStartIndex($start);
		$feed->setOpensearchItemsPerPage($max);
		//switch to the simple xml interface here
		$div = simplexml_import_dom($feed->setSubtitle());
		$search_echo = $div->addChild('div',htmlspecialchars($subtitle));
		$search_echo->addAttribute('class','searchEcho');
		$ul = $div->addChild('ul');
		$query_string_no_colls = preg_replace('/(\?|&|&amp;)c=\w+/i','',$query_string);
		foreach ($result['tallies'] as $coll => $tal) {
			if ($tal['name'] && $tal['total']) {
				$tally_elem = $ul->addChild('li');
				$tally_elem->addAttribute('class',$coll);
				$a = $tally_elem->addChild('a',htmlspecialchars($tal['name'] . ': ' . $tal['total']));
				//adds collection filter (collection_ascii_id trumps 'c')
				$a->addAttribute('href',APP_ROOT.'/'.$request_path.'?'.$query_string_no_colls.'&collection_ascii_id='.$coll);
				$feed->addLink(
					APP_ROOT.'/'.$request_path.'?'.$query_string_no_colls.'&collection_ascii_id='.$coll,
					'text/html',
					'',
					'related',$tal['name'].': '.$tal['total'].' items'
				);
			}
		}
		//this prevents a 'search_item' becoming 'search_item_item':
		$item_request_url = str_replace('search_item','search',$request_path);
		$item_request_url = str_replace('search','search_item',$item_request_url);
		$num = 0;
		foreach($item_ids as $item_id) {
			$num++;
			$setnum = $num + $start - 1;
			$item = new Dase_DBO_Item();
			$item->load($item_id);
			$item->collection || $item->getCollection();
			$item->item_type || $item->getItemType();
			$item->item_status || $item->getItemStatus();
			$entry = $feed->addEntry();
			$item->injectAtomEntryData($entry);
			$entry->addLink($item_request_url . '?' . $query_string . '&num=' . $setnum,'http://daseproject.org/relation/search-item');
		}
		$request->renderResponse($feed->asXml(),$request);
	}

	public static function itemAsAtom($request)
	{
		$search = Dase_Search::get($request);
		$num = $request->get('num');
		$max = $request->get('max');
		$max = $max ? $max : MAX_ITEMS; 
		if (!$num) {
			$num = 1;
		}
		$result = $search->getResult();
		//this will change:
		$request_path = str_replace('atom/','',$result['request_url']);
		//this prevents a 'search_item' becoming 'search_item_item':
		$item_request_url = str_replace('search_item','search',$request_path);
		$item_request_url = str_replace('search','search_item',$item_request_url);
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

		$start = Dase_Filter::filterGet('start');
		if (!$start) {
			$start = (floor($num/$max) * $max) + 1;
		}

		$item_id = $result['item_ids'][$num-1];
		$item = new Dase_DBO_Item;
		if ($item->load($item_id)) {
			$feed = new Dase_Atom_Feed();
			$feed->setFeedType('searchitem');
			$item->injectAtomFeedData($feed);
			$item->injectAtomEntryData($feed->addEntry());
			$feed->addCategory('browse',"http://daseproject.org/category/tag_type",'browse');
			$feed->addLink($item_request_url . '?' . $query_string . '&num=' . $num,'http://daseproject.org/relation/search-item-link');
			$feed->addLink($request_path . '?' . $query_string . '&start=' . $start,'http://daseproject.org/relation/feed-link');
			if (isset($next)) {
				$feed->addLink($item_request_url . '?' . $query_string . '&num=' . $next,'next','application/xhtml+xml');
			}
			if (isset($previous)) {
				$feed->addLink($item_request_url . '?' . $query_string . '&num=' . $previous,'previous','application/xhtml+xml');
			}
			$subtitle = 'Item ' . $num . ' of ' . $result['count'] . ' items for ' . $result['echo']; 
			$feed->setSubtitle($subtitle);
			$request->renderResponse($feed->asXml(),$request);
		}
		$request->renderError(404);
	}

	public static function item($request)
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('item',Dase_Atom_Feed::retrieve(APP_ROOT.'/'.$request->url.'&format=atom'));
		$request->renderResponse($tpl->fetch('item/transform.tpl'),$request);
	}

	public static function search($request)
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('items',Dase_Atom_Feed::retrieve(APP_ROOT.'/'.$request->url.'&format=atom'));
		$request->renderResponse($tpl->fetch('item_set/search.tpl'),$request);
	}

	public static function showSql($request)
	{
		$result = htmlspecialchars(Dase_Search::get($request)->getResult());
		print "<pre>{$result['sql']}</pre>";
		exit;
	}
}

