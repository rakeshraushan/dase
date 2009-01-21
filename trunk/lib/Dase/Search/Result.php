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

	public function getResultSetAsJsonFeed($max = 500)
	{
		//todo: this needs lots of work!
		$json_tag;
		$json_tag['uri'] = 'ssssssss';
		$json_tag['updated'] = date(DATE_ATOM);
		$json_tag['name'] = 'search: '.str_replace('&quot;','"',$this->_getQueryAsString());
		$json_tag['is_public'] = 1;
		$item_ids = array_slice($this->item_ids,0,$max);
		foreach($item_ids as $item_id) {
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

	public function getResultSetUris()
	{
		//inefficient, but ok for now
		$uris = array();
		foreach($this->item_ids as $item_id) {
			$item = new Dase_DBO_Item();
			$item->load($item_id);
			$uris[] = $item->getBaseUrl();
		}
		return $uris;	
	}

	private function _getQueryAsString()
	{
		$q = '';
		foreach ($this->search_array['find'] as $find) {
			if (false !== strpos(trim($find),' ')) {
				$find = '&quot;'.$find.'&quot;';
			}
			$q .= ' '.$find;
		}

		//note cannot 'omit' phrases!
		foreach ($this->search_array['omit'] as $omit) {
			$q .= ' -'.$omit;
		}
		foreach ($this->search_array['qualified'] as $att => $vals) {
			foreach($vals as $val) {
				$set = $att.':'.$val;
				if (false !== strpos(trim($set),' ')) {
					$set = '&quot;'.$set.'&quot;';
				}
				$q .= ' '.$set;
			}
		}

		foreach ($this->search_array['att'] as $coll => $att_arrays) {
			foreach ($att_arrays as $att => $vals) {
				if (isset($vals['value_text_substr'])) {
					foreach ($vals['value_text_substr'] as $v) {
						$q .= ' &quot;'.$att.':'.$v.'&quot;';
					}
				}
				if (isset($vals['value_text'])) {
					foreach ($vals['value_text'] as $v) {
						$q .= ' &quot;'.$att.':'.$v.'&quot;';
					}
				}
			}
		}
		if (count($this->search_array['colls']) == 1) {
			$q .= ' c:'.$this->search_array['colls'][0];
		}
		return trim($q);
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

		$feed = new Dase_Atom_Feed();
		$feed->addAuthor();
		$feed->setTitle('DASe Search Result');
		$feed->addLink(APP_ROOT.'/'.$this->url.'&format=atom&start='.$start.'&max='.$max,'self');
		//$feed->addLink($this->url,'alternate','text/html','',$search_title);
		$feed->addLink($this->url,'alternate','text/html','','Search Result');
		$feed->addLink($this->url.'&start='.$start.'&max='.$max.'&display=grid','related','text/html','','grid');
		$feed->addLink($this->url.'&start='.$start.'&max='.$max.'&display=list','related','text/html','','list');
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
		$feed->setOpensearchQuery($this->_getQueryAsString());
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
		//here is where we place a collection link *only* if there is one collection
		if (1 == count($this->tallies)) {
			$feed->addLink(APP_ROOT.'/collection/'.$coll,'http://daseproject.org/relation/collection','text/html',null,$this->tallies[$coll]['name']);
			$feed->addLink(APP_ROOT.'/collection/'.$coll.'/attributes.json','http://daseproject.org/relation/collection/attributes','application/json');
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
			//will check cache
			//$entry = $feed->addItemEntry($item);
			$entry->addCategory($setnum,'http://daseproject.org/category/position');
			$entry->addLink($item_request_url.'&num=' . $setnum,'http://daseproject.org/relation/search-item');
		}
		return $feed->asXml();
	}

	public function getItemAsAtomFeed($start,$max,$num)
	{
		if (!$this->count) {
			$feed = new Dase_Atom_Feed();
			$feed->addAuthor();
			$feed->setTitle('DASe Search Result');
			$feed->setFeedType('searchitem');
			$feed->addLink($this->url,'alternate','text/html','','Search Result');
			$feed->setUpdated(date(DATE_ATOM));
			$feed->setId(APP_ROOT.'/search/'.md5($this->url));
			$feed->setOpensearchTotalResults(0);
			$feed->setOpensearchQuery($this->_getQueryAsString());
			return $feed->asXml();
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
			//$item->injectAtomEntryData($feed->addEntry());
			//uses cache
			$feed->addItemEntry($item);
			$feed->addCategory('browse',"http://daseproject.org/category/tag/type",'browse');
			$feed->addLink(APP_ROOT.'/'.$this->url.'&num='.$num);
			$feed->addCategory($num,"http://daseproject.org/category/position");
			$feed->addLink(APP_ROOT.'/'.$search_url.'&start='.$start,'http://daseproject.org/relation/feed-link');
			if ($next) {
				$feed->addLink(APP_ROOT.'/'.$this->url.'&num='.$next,'next','application/xhtml+xml');
			}
			if ($previous) {
				$feed->addLink(APP_ROOT.'/'.$this->url.'&num='.$previous,'previous','application/xhtml+xml');
			}
			$feed->setOpensearchQuery($this->_getQueryAsString());
			$feed->setOpensearchTotalResults($this->count);
			return $feed->asXml();
		}
	}
}

