<?php

class Dase_Search_Result
{
	public $tallies = array();
	public $item_ids = array();
	public $count;
	public $url;
	public $echo_array = array();

	public function __construct($item_ids,$tallies,$url,$echo_array)
	{
		$this->item_ids = $item_ids;
		$this->tallies = $tallies;
		$this->url = $url;
		$this->echo_array = $echo_array;
		$this->count = count($item_ids);
	}

	private function _constructEcho() {
		$echo = $this->echo_array;
		//construct echo
		$echo_str = '';
		if ($echo['query']) {
			$echo_str .= " {$echo['query']} ";
		} 
		if ($echo['exact']) {
			$echo_arr = array();
			foreach ($echo['exact'] as $k => $v) {
				$v = array_unique($v);
				foreach( $v as $val) {
					$echo_arr[] = "$val in $k";
				}
			}
			if ($echo_str) {
				$echo_str .= " AND ";
			}
			$echo_str .= join(' AND ',$echo_arr);
		}
		if ($echo['sub']) {
			$echo_arr = array();
			foreach ($echo['sub'] as $k => $v) {
				$v = array_unique($v);
				foreach( $v as $val) {
					$echo_arr[] = "$val in $k";
				}
			}
			if ($echo_str) {
				$echo_str .= " AND ";
			}
			$echo_str .= join(' AND ',$echo_arr);
		}
		if ($echo['collection_ascii_id']) {
			$echo_str .= " in {$echo['collection_ascii_id']} ";
		}
		if ($echo['type']) {
			if ($echo_str) {
				$echo_str .= " WITH ";
			}
			$echo_str .= " item type {$echo['type']} ";
		}
		return $echo_str;
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
		$feed->addLink($this->url,'alternate','text/html','',$search_title);
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
		$search_echo = $div->addChild('div',htmlspecialchars($search_title));
		$search_echo->addAttribute('class','searchEcho');
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
		$item_id = $this->item_ids[$num-1];
		$item = new Dase_DBO_Item;
		if ($item->load($item_id)) {
			$feed = new Dase_Atom_Feed();
			$feed->setFeedType('searchitem');
			$item->injectAtomFeedData($feed);
			$item->injectAtomEntryData($feed->addEntry());
			$feed->addCategory('browse',"http://daseproject.org/category/tag_type",'browse');
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

