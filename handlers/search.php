<?php

class SearchHandler
{

	//MOST of this should be in Dase_Search!!

	public static function opensearch($params)
	{

		if (isset($params['md5_hash'])) {
			$result = Dase_DBO_SearchCache::get($params['md5_hash']);
		} else {
			$result = Dase_Search::get(Dase_Url::getRequestUrl(),Dase_Url::getQueryString())->getResult();
		}

		$start = Dase_Filter::filterGet('start');
		$start = $start ? $start : 1;
		$max = Dase_Filter::filterGet('max');
		$max = $max ? $max : MAX_ITEMS; 

		$request_url = $result['request_url'];
		$query_string = $result['query_string'];

		$request_url = str_replace('atom/','',$request_url);

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
		if (isset($next)) {
			$feed->addLink(APP_ROOT.'/'.$request_url.'?'.$query_string.'&start='.$next,'next');
		}
		if (isset($previous)) {
			$feed->addLink(APP_ROOT.'/'.$request_url.'?'.$query_string.'&start='.$previous,'previous');
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
		foreach ($result['tallies'] as $coll => $tal) {
			if ($tal['name'] && $tal['total']) {
				$tally_elem = $ul->addChild('li');
				$tally_elem->addAttribute('class',$coll);
				$a = $tally_elem->addChild('a',htmlspecialchars($tal['name'] . ': ' . $tal['total']));
				//adds collection filter (collection_ascii_id trumps 'c')
				$a->addAttribute('href',APP_ROOT.'/'.$request_url.'?'.$query_string.'&collection_ascii_id='.$coll);
			}
		}
		//this prevents a 'search_item' becoming 'search_item_item':
		$item_request_url = str_replace('search_item','search',$request_url);
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
		Dase::display($feed->asXml());
	}

	public static function itemAsAtom($params)
	{
		$search = Dase_Search::get(Dase_Url::getRequestUrl(),Dase_Url::getQueryString());
		$num = Dase_Filter::filterGet('num');
		$max = Dase_Filter::filterGet('max');
		$max = $max ? $max : MAX_ITEMS; 
		if (!$num) {
			$num = 1;
		}
		$result = $search->getResult();
		//this will change:
		$request_url = str_replace('atom/','',$result['request_url']);
		//this prevents a 'search_item' becoming 'search_item_item':
		$item_request_url = str_replace('search_item','search',$request_url);
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
			$item->injectAtomFeedData($feed);
			$item->injectAtomEntryData($feed->addEntry());
			$feed->addCategory('browse',"http://daseproject.org/category/tag_type",'browse');
			$feed->addLink($item_request_url . '?' . $query_string . '&num=' . $num,'http://daseproject.org/relation/search-item-link');
			$feed->addLink($request_url . '?' . $query_string . '&start=' . $start,'http://daseproject.org/relation/feed-link');
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
	}

	public static function item($params)
	{
		$request_url = Dase_Url::getRequestUrl();
		$query_string = Dase_Url::getQueryString();
		/*
		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'item/transform.xsl';
		$t->set('local-layout',XSLT_PATH.'item/source.xml');
		$t->set('src',APP_ROOT.'/atom/'. $request_url . '?' . $query_string);
		Dase::display($t->transform());
		 */
		$tpl = new Dase_Template();
		$tpl->atomDoc('item',DASE_URL.'/atom/'.$request_url.'?'.$query_string);
		Dase::display($tpl->fetch('item/transform.tpl'));
	}

	public static function search($params)
	{
		$request_url = Dase_Url::getRequestUrl();
		$query_string = Dase_Url::getQueryString();
		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'item_set/search.xsl';
		$t->set('local-layout',XSLT_PATH.'item_set/source.xml');
		$t->set('src',APP_ROOT.'/atom/'. $request_url . '?' . $query_string);
		Dase::display($t->transform());
	}

	public static function sql($params)
	{
		$result = htmlspecialchars(Dase_Search::get(Dase_Url::getRequestUrl(),Dase_Url::getQueryString())->getResult());
		print "<pre>{$result['sql']}</pre>";
		exit;
	}
}

