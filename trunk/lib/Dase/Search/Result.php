<?php

class Dase_Search_Result
{
	public $tallies = array();
	public $item_ids = array();
	public $count;
	public $url;
	public $echo_array = array();

	public function __construct($item_ids,$tallies,$url,$search_array)
	{
		$this->item_ids = $item_ids;
		$this->tallies = $tallies;
		$this->url = $url;
		$this->search_array = $search_array;
		$this->count = count($item_ids);
	}

	private function _constructEcho() {
//work on this!!!!
	}

	public function getResultSetAsJsonFeed()
	{
		//todo: this needs lots of work!
		$json_tag;
		$json_tag['uri'] = 'ssssssss';
		$json_tag['updated'] = date(DATE_ATOM);
		$json_tag['name'] = 'search: '.$this->_constructEcho();
		$json_tag['is_public'] = 1;
		foreach($this->item_ids as $item_id) {
			$item = new Dase_DBO_Item();
			$item->load($item_id);
			/*
			$json_item = array();
			foreach ($item->getMedia() as $m) {
				$json_item['media'][$m->size] = APP_ROOT.'/media/'.$item->collection->ascii_id.'/'.$m->size.'/'.$m->filename;
			}
			$json_tag['items'][] = $json_item;
			 */
			$json_tag['items'][] = $item->asArray();
		}
		return Dase_Json::get($json_tag);	
	}

	public function getResultSetAsAtomFeed($start,$max)
	{
		$next = $start + $max;
		if ($next > $this->count) {
			unset($next);
		}
		$previous = $start - $max;
		if ($previous < 1) {
			unset($previous);
		}
		$item_ids = array_slice($this->item_ids,$start-1,$max);
		$end = $start + count($item_ids) - 1;

		$echo = $this->_constructEcho();
		if ($end > $start) {
			$search_title = 'results '.$start.'-'.$end.' of '.$this->count.' items for '.$echo; 
		} elseif ($end == $start) {
			$search_title = '1 result for '.$echo; 
		} else {
			$search_title = 'no results for '.$echo; 
		}

		$feed = new Dase_Atom_Feed();
		$feed->addAuthor();
		$feed->setTitle('DASe Search Result');
		$feed->addLink(APP_ROOT.'/'.$this->url.'&format=atom&start='.$start.'&max='.$max,'self');
		//$feed->addLink($this->url,'alternate','text/html','',$search_title);
		$feed->addLink($this->url,'alternate','text/html','','Search Result');
		$feed->setUpdated(date(DATE_ATOM));
		$feed->setFeedType('search');
		if (isset($next)) {
			$feed->addLink(APP_ROOT.'/'.$this->url.'&start='.$next.'&max='.$max,'next');
		}
		if (isset($previous)) {
			$feed->addLink(APP_ROOT.'/'.$this->url.'&start='.$previous.'&max='.$max,'previous');
		}
		Dase_Log::debug('url per search result '.$this->url);
		$feed->setId(APP_ROOT.'/search/'.md5($this->url));
		$feed->setOpensearchTotalResults($this->count);
		$feed->setOpensearchStartIndex($start);
		$feed->setOpensearchItemsPerPage($max);
		//switch to the simple xml interface here
		$div = simplexml_import_dom($feed->setSubtitle());
		$ul = $div->addChild('ul');
		$url_no_colls = preg_replace('/(\?|&|&amp;)c=\w+/i','',$this->url);
		$url_no_colls = preg_replace('/(\?|&|&amp;)collection_ascii_id=\w+/i','',$url_no_colls);
		foreach ($this->tallies as $coll => $tal) {
			if ($tal['name'] && $tal['total']) {
				$tally_elem = $ul->addChild('li');
				$tally_elem->addAttribute('class',$coll);
				$a = $tally_elem->addChild('a',htmlspecialchars($tal['name'] . ': ' . $tal['total']));
				//adds collection filter (collection_ascii_id trumps 'c')
				$a->addAttribute('href',APP_ROOT.'/'.$url_no_colls.'&collection_ascii_id='.$coll);
				$feed->addLink(
					APP_ROOT.'/'.$url_no_colls.'&collection_ascii_id='.$coll,
					'related',
					'text/html',
					'',$tal['name'].': '.$tal['total'].' items'
				);
			}
		}
		//this prevents a 'search/item' becoming 'search/item/item':
		$item_request_url = str_replace('search/item','search',$this->url);
		$item_request_url = str_replace('search','search/item',$item_request_url);
		$num = 0;
		foreach($item_ids as $item_id) {
			$num++;
			$setnum = $num + $start - 1;
			$item = new Dase_DBO_Item();
			$item->load($item_id);
			$item->collection || $item->getCollection();
			$item->item_type || $item->getItemType();
			$entry = $feed->addEntry();
			$item->injectAtomEntryData($entry);
			$entry->addCategory($setnum,'http://daseproject.org/category/number_in_set');
			$entry->addLink($item_request_url.'&num=' . $setnum,'http://daseproject.org/relation/search-item');
		}
		return $feed->asXml();
	}

	public function getItemAsAtomFeed($start,$max,$num)
	{
		//no need to send back a feed if count is 0
		if (!$this->count) {
			return false;
		}
		$num = $num ? $num : 1;

		$previous = 0;
		$next = 0;
		if ($num < $this->count) {
			$next = $num + 1;
		}
		if ($num > 1) {
			$previous = $num - 1;
		}

		$search_url = str_replace('search/item','search',$this->url);
		$echo = $this->_constructEcho();
		if (isset($this->item_ids[$num-1])) {
			$item_id = $this->item_ids[$num-1];
		} else {
			return;
		}
		$item = new Dase_DBO_Item;
		if ($item->load($item_id)) {
			$feed = new Dase_Atom_Feed();
			$feed->setFeedType('searchitem');
			$item->injectAtomFeedData($feed);
			$item->injectAtomEntryData($feed->addEntry());
			$feed->addCategory('browse',"http://daseproject.org/category/tag/type",'browse');
			$feed->addLink(APP_ROOT.'/'.$this->url.'&num='.$num,'http://daseproject.org/relation/search-item-link');
			$feed->addLink(APP_ROOT.'/'.$search_url.'&start='.$start,'http://daseproject.org/relation/feed-link');
			if ($next) {
				$feed->addLink(APP_ROOT.'/'.$this->url.'&num='.$next,'next','application/xhtml+xml');
			}
			if ($previous) {
				$feed->addLink(APP_ROOT.'/'.$this->url.'&num='.$previous,'previous','application/xhtml+xml');
			}
			$subtitle = 'Item ' . $num . ' of ' . $this->count . ' items for ' . $echo; 
			$feed->setSubtitle($subtitle);
			return $feed->asXml();
		}
	}
}

