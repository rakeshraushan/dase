<?php

class SearchHandler extends Dase_Handler
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
			$this->max = MAX_ITEMS;
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
		$tpl->assign('items',Dase_Atom_Feed::retrieve(APP_ROOT.'/'.$request->url.'&format=atom'));
		$request->renderResponse($tpl->fetch('item_set/search.tpl'));
	}
}

