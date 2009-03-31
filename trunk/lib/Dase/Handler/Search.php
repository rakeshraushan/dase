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
		if ($r->getCookie('max')) {
			$r->set('max',$r->getCookie('max'));
			$r->setQueryStringParam('max',$r->getCookie('max'));
		}

		if ($r->getCookie('display')) {
			$r->set('display',$r->getCookie('display'));
		}

		if ($r->has('max')) {
			$this->max = $r->get('max');
		} else {
			$this->max = $r->retrieve('config')->getAppSettings('max_items');
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
		$count = Dase_DBO_SearchCache::deleteRecent($this->db,$r);
		$r->renderResponse($count." cached searches deleted");
	}

	public function postToDeleteRecent($r)
	{
		$this->deleteRecent($this->db,$r);
	}

	public function getSearchCacheAtom($r)
	{
		$r->checkCache();
		$search_cache = new Dase_DBO_SearchCache($this->db);
		$search_cache->search_md5 = $r->get('md5_hash');
		if ($search_cache->findOne()) {
			$cache = Dase_Cache::get($search_cache->query);
			$data = $cache->getData(60*30);
			if ($data) { //30 minutes
				$search_result = unserialize($data);
				$atom_feed = $search_result->getResultSetAsAtomFeed($r->app_root,$this->db,$this->start,$this->max);
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
		$atom_feed = $search->getResult()->getResultSetAsAtomFeed($r->app_root,$this->db,$this->start,$this->max);
		$r->renderResponse($atom_feed);
	}

	public function getSearchJson($r)
	{
		$r->checkCache();
		$search = new Dase_Search($r);
		$json_feed = $search->getResult()->getResultSetAsJsonFeed($r->app_root,$this->db,$this->max);
		$r->renderResponse($json_feed);
	}

	public function getSearchUris($r)
	{
		$r->checkCache();
		$search = new Dase_Search($r);
		$sernums = $search->getResult()->getResultSetUris($r->app_root,$this->db);
		$r->renderResponse(join("\n",$sernums));
	}

	public function getSearchCsv($r)
	{
		$r->checkCache();
		$search = new Dase_Search($r);
		$sernums = $search->getResult()->getResultSetCsv($this->db);
		$r->renderResponse(join(",",$sernums));
	}

	public function getSearchItemAtom($r)
	{
		$r->checkCache();
		$search = new Dase_Search($r);
		$search_result = $search->getResult();
		$atom_feed = $search_result->getItemAsAtomFeed($r->app_root,$this->db,$this->start,$this->max,$r->get('num'));
		$r->renderResponse($atom_feed);
	}

	public function getSearchItem($r)
	{
		$r->checkCache();
		$tpl = new Dase_Template($r);
		$feed = Dase_Atom_Feed::retrieve($r->app_root.'/'.$r->url.'&format=atom');
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
		$json_url = $r->app_root.'/'.$r->url.'&format=json&max=100';
		$tpl->assign('json_url',$json_url);
		$feed_url = $r->app_root.'/'.$r->url.'&format=atom';
		$tpl->assign('feed_url',$feed_url);
		$feed = Dase_Atom_Feed::retrieve($feed_url);
		//single hit goes directly to item
		$count = $feed->getCount();
		if (1 == $count) {
			$tpl->assign('item',$feed);
			$url = str_replace('search?','search/item?',$r->url);
			$r->renderRedirect($r->app_root.'/'.$url);
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

