<?php

class Dase_Handler_Search extends Dase_Handler
{

	public $resource_map = array(
		'/' => 'search',
		'sernums' => 'search_serial_numbers',
		'refine' => 'search_refined',
		'item' => 'search_item',
		'{md5_hash}' => 'search_by_hash',
	);

	protected function setup($request)
	{
		if ($request->has('max')) {
			$this->max = $request->get('max');
		} else {
			$this->max = Dase_Config::get('max_items');
		}
		if ($request->has('start')) {
			$this->start = $request->get('start');
		} else {
			$this->start = 1;
		}
	}

	public function getSearchByHashAtom($request)
	{
		$request->checkCache();
		$search_cache = new Dase_DBO_SearchCache;
		$search_cache->search_md5 = $request->get('md5_hash');
		if ($search_cache->findOne()) {
			$cache = Dase_Cache::get($search_cache->query);
			$data = $cache->getData(60*30);
			if ($data) { //30 minutes
				$search_result = unserialize($data);
				$atom_feed = $search_result->getResultSetAsAtomFeed($this->start,$this->max);
				$request->renderResponse($atom_feed);
			}
		}
		$request->renderError(404);
	}

	public function getSearchByHash($request)
	{
	}

	public function getSearchAtom($request)
	{
		$request->checkCache();
		$search = new Dase_Search($request);
		$atom_feed = $search->getResult()->getResultSetAsAtomFeed($this->start,$this->max);
		$request->renderResponse($atom_feed);
	}

	public function getSearchJson($request)
	{
		$request->checkCache();
		$search = new Dase_Search($request);
		$json_feed = $search->getResult()->getResultSetAsJsonFeed();
		$request->renderResponse($json_feed);
	}

	public function getSearchSerialNumbers($request)
	{
		$request->checkCache();
		$search = new Dase_Search($request);
		$sernums = $search->getResult()->getResultSetSerialNumbers();
		$request->renderResponse(join('|',$sernums));
	}

	public function getSearchItemAtom($request)
	{
		$request->checkCache();
		$search = new Dase_Search($request);
		$search_result = $search->getResult();
		$atom_feed = $search_result->getItemAsAtomFeed($this->start,$this->max,$request->get('num'));
		if ($atom_feed) {
			$request->renderResponse($atom_feed);
		} else {
			$request->renderError(404,'no such item');
		}
	}

	public function getSearchItem($request)
	{
		$request->checkCache();
		$tpl = new Dase_Template($request);
		$feed = Dase_Atom_Feed::retrieve(APP_ROOT.'/'.$request->url.'&format=atom');
		if (!$feed) {
			$request->renderError(404,'no such item');
		}
		$tpl->assign('item',$feed);

		$request->renderResponse($tpl->fetch('item/transform.tpl'));
	}

	public function getSearch($request)
	{
		$request->checkCache();
		$tpl = new Dase_Template($request);
		$json_url = APP_ROOT.'/'.$request->url.'&format=json';
		$tpl->assign('json_url',$json_url);
		$feed_url = APP_ROOT.'/'.$request->url.'&format=atom';
		$tpl->assign('feed_url',$feed_url);
		$feed = Dase_Atom_Feed::retrieve($feed_url);

		$tpl->assign('items',$feed);
		//todo: reimplement single hit going directly to item??
		$request->renderResponse($tpl->fetch('item_set/search.tpl'));
	}
}

