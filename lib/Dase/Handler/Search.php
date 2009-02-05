<?php

class Dase_Handler_Search extends Dase_Handler
{

	public $resource_map = array(
		'/' => 'search',
		'item' => 'search_item',
		'recent' => 'recent',
		'delete_recent' => 'delete_recent', //so we can use post as well
		'{md5_hash}' => 'search_cache',
	);

	protected function setup($r)
	{
		//setting $r allows app cache-ability
		//but...breaks intermediate caching (work on that)
		if (Dase_Cookie::get('max')) {
			$r->set('max',Dase_Cookie::get('max'));
			$r->setQueryStringParam('max',Dase_Cookie::get('max'));
		}

		if (Dase_Cookie::get('display')) {
			$r->set('display',Dase_Cookie::get('display'));
		}

		if ($r->has('max')) {
			$this->max = $r->get('max');
		} else {
			$this->max = Dase_Config::get('max_items');
		}
		if ($r->has('start')) {
			$this->start = $r->get('start');
		} else {
			$this->start = 1;
		}
	}

	/** this should be used sparingly, since it is a sledgehammer */
	public function deleteRecent($r)
	{
		if (!$r->getUser('http')) {
			$r->renderError(401,'cannot delete recent searches');
		}
		$count = Dase_DBO_SearchCache::deleteRecent();
		$r->renderResponse($count." cached searches deleted");
	}

	public function postToDeleteRecent($r)
	{
		$this->deleteRecent($r);
	}

	public function getSearchCacheAtom($r)
	{
		$r->checkCache();
		$search_cache = new Dase_DBO_SearchCache;
		$search_cache->search_md5 = $r->get('md5_hash');
		if ($search_cache->findOne()) {
			$cache = Dase_Cache::get($search_cache->query);
			$data = $cache->getData(60*30);
			if ($data) { //30 minutes
				$search_result = unserialize($data);
				$atom_feed = $search_result->getResultSetAsAtomFeed($this->start,$this->max);
				$r->renderResponse($atom_feed);
			}
		}
		$r->renderError(404);
	}

	public function getSearchByHash($r)
	{
	}

	public function getSearchAtom($r)
	{
		$r->checkCache();
		$search = new Dase_Search($r);
		$atom_feed = $search->getResult()->getResultSetAsAtomFeed($this->start,$this->max);
		$r->renderResponse($atom_feed);
	}

	public function getSearchJson($r)
	{
		$r->checkCache();
		$search = new Dase_Search($r);
		$json_feed = $search->getResult()->getResultSetAsJsonFeed($this->max);
		$r->renderResponse($json_feed);
	}

	public function getSearchUris($r)
	{
		$r->checkCache();
		$search = new Dase_Search($r);
		$sernums = $search->getResult()->getResultSetUris();
		$r->renderResponse(join("\n",$sernums));
	}

	public function getSearchItemAtom($r)
	{
		$r->checkCache();
		$search = new Dase_Search($r);
		$search_result = $search->getResult();
		$atom_feed = $search_result->getItemAsAtomFeed($this->start,$this->max,$r->get('num'));
		$r->renderResponse($atom_feed);
	}

	public function getSearchItem($r)
	{
		$r->checkCache();
		$tpl = new Dase_Template($r);
		$feed = Dase_Atom_Feed::retrieve(APP_ROOT.'/'.$r->url.'&format=atom');
		if (!$feed->getOpensearchTotal()) {
			$r->renderError(404,'no such item');
		}
		$tpl->assign('item',$feed);
		$r->renderResponse($tpl->fetch('item/display.tpl'));
	}

	public function getSearch($r)
	{
		$r->checkCache();
		$tpl = new Dase_Template($r);
		//default slidehow max of 100
		$json_url = APP_ROOT.'/'.$r->url.'&format=json&max=100';
		$tpl->assign('json_url',$json_url);
		$feed_url = APP_ROOT.'/'.$r->url.'&format=atom';
		$tpl->assign('feed_url',$feed_url);
		$feed = Dase_Atom_Feed::retrieve($feed_url);
		//single hit goes directly to item
		$count = $feed->getCount();
		if (1 == $count) {
			$tpl->assign('item',$feed);
			$url = str_replace('search?','search/item?',$r->url);
			$r->renderRedirect(APP_ROOT.'/'.$url);
		}
		$end = $this->start+$this->max-1;
		if ($end > $count) {
			$end = $count;
		}
		$tpl->assign('start',$this->start);
		$tpl->assign('end',$end);
		$tpl->assign('sort',$r->get('sort'));
		$tpl->assign('items',$feed);
		if ('list' == $r->get('display')) {
			$tpl->assign('display','list');
		} else {
			$tpl->assign('display','grid');
		}
		$r->renderResponse($tpl->fetch('item_set/search.tpl'));
	}
}

