<?php

class Dase_Handler_Search extends Dase_Handler
{

	public $resource_map = array(
		'/' => 'search',
		'refine' => 'search_refined',
		'{md5_hash}' => 'search_by_hash',
		'item' => 'search_item',
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

	public function getSearchItemAtom($request)
	{
		$request->checkCache();
		$search = new Dase_Search($request);
		$atom_feed = $search->getResult()->getItemAsAtomFeed($this->start,$this->max,$request->get('num'));
		$request->renderResponse($atom_feed);
	}

	public function getSearchItem($request)
	{
		$request->checkCache();
		$tpl = new Dase_Template($request);
		$tpl->assign('item',Dase_Atom_Feed::retrieve(APP_ROOT.'/'.$request->url.'&format=atom'));
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

