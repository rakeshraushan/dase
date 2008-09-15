<?php

class Dase_Handler_Search extends Dase_Handler
{

	public $resource_map = array(
		'/' => 'search',
		'serial_numbers' => 'serial_numbers',
		'refine' => 'search_refined',
		'item' => 'search_item',
		'{md5_hash}' => 'search_by_hash',
	);

	protected function setup($r)
	{
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

	public function getSearchByHashAtom($r)
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

	public function getSerialNumbers($r)
	{
		$r->checkCache();
		$search = new Dase_Search($r);
		$sernums = $search->getResult()->getResultSetSerialNumbers();
		$r->renderResponse(join('|',$sernums));
	}

	public function getSearchItemAtom($r)
	{
		$r->checkCache();
		$search = new Dase_Search($r);
		$search_result = $search->getResult();
		$atom_feed = $search_result->getItemAsAtomFeed($this->start,$this->max,$r->get('num'));
		if ($atom_feed) {
			$r->renderResponse($atom_feed);
		} else {
			$r->renderError(404,'no such item');
		}
	}

	public function getSearchItem($r)
	{
		$r->checkCache();
		$tpl = new Dase_Template($r);
		$feed = Dase_Atom_Feed::retrieve(APP_ROOT.'/'.$r->url.'&format=atom');
		if (!$feed) {
			$r->renderError(404,'no such item');
		}
		$tpl->assign('item',$feed);

		$r->renderResponse($tpl->fetch('item/transform.tpl'));
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

		$tpl->assign('items',$feed);
		//todo: reimplement single hit going directly to item??
		$r->renderResponse($tpl->fetch('item_set/search.tpl'));
	}
}

