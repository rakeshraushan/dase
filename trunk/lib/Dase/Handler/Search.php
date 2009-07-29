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
			$this->max = MAX_ITEMS;
		}
		if ($r->has('start')) {
			$this->start = $r->get('start');
		} else {
			$this->start = 0;
		}
		if ($r->has('num')) {
			$this->num = $r->get('num');
		} else {
			$this->num = 0;
		}
		if ($r->has('sort')) {
			$this->sort = $r->get('sort');
		} else {
			$this->sort = '';
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

	public function getSearchByHash($r)
	{
	}

	public function getSearchAtom($r)
	{
		$r->checkCache();
		$search = Dase_SearchEngine::get($this->db,$this->config);
		$search->prepareSearch($r,$this->start,$this->max,$this->num,$this->sort);
		$atom_feed = $search->getResultsAsAtom();
		$r->renderResponse($atom_feed);
	}

	public function getSearchJson($r)
	{
		$r->checkCache();
		$feed_url = $r->app_root.'/'.$r->url.'&format=atom';
		$feed = Dase_Atom_Feed::retrieve($feed_url);
		$r->renderResponse($feed->asJson());
	}

	public function getSearchUris($r)
	{
	}

	public function getSearchCsv($r)
	{
	}

	public function getSearchItemAtom($r)
	{
		$r->checkCache();
		$search = Dase_SearchEngine::get($this->db,$this->config);
		$this->max =1;
		$search->prepareSearch($r,$this->start,$this->max,$this->num,$this->sort);
		$atom_feed = $search->getResultsAsItemAtom();
		$r->renderResponse($atom_feed);
	}

	public function getSearchItem($r)
	{
		$r->checkCache();
		$tpl = new Dase_Template($r);
		$feed = Dase_Atom_Feed::retrieve($r->app_root.'/'.$r->url.'&format=atom');
		//todo: figure this out
		//if (!$feed->getOpensearchTotal()) {
		//	$r->renderError(404,'no such item');
		//}
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

		/*
		if (strlen($r->get('q')) > 252) {
			$params['msg'] = 'query is too long';
			$r->renderRedirect($r->app_root.'/collections',$params);
		}
		 */

		//single hit goes directly to item
		$count = $feed->getCount();
		if (1 == $count) {
			$tpl->assign('item',$feed);
			$url = str_replace('search?','search/item?',$r->url);
			$r->renderRedirect($r->app_root.'/'.$url.'&num=1');
		}
		if (0 == $count) {
			if ($r->has('collection_ascii_id')) {
				$params['msg'] = 'no items found';
				$params['failed_query'] = $feed->getQuery();
				$r->renderRedirect($r->app_root.'/collection/'.$r->get('collection_ascii_id'),$params);
			} else {
				$params['msg'] = 'no items found';
				$params['failed_query'] = $feed->getQuery();
				$r->renderRedirect($r->app_root.'/collections',$params);
			}
		}
		$end = $this->start+$this->max;
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

