<?php

class CollectionbuilderHandler
{

	public static function index($params)
	{
		//this is the best/easist way to implement redirect
		CollectionbuilderHandler::settings($params);
	}

	public static function settings($params)
	{
		$tpl = new Dase_Template();
		$tpl->assign('user',Dase_User::get($params));
		$tpl->assign('collection',Dase_Collection::get($params));
		Dase::display($tpl->fetch('collectionbuilder/settings.tpl'));
	}

	public static function attributes($params)
	{
		$tpl = new Dase_Template();
		$tpl->assign('user',Dase_User::get($params));
		$c = Dase_Collection::get($params);
		$tpl->assign('collection',Dase_Collection::get($params));
		$tpl->assign('attributes',Dase_Collection::get($params)->getAttributes());
		Dase::display($tpl->fetch('collectionbuilder/attributes.tpl'));
	}

	public static function managers($params)
	{
		$tpl = new Dase_Template();
		$tpl->assign('user',Dase_User::get($params));
		$tpl->assign('collection',Dase_Collection::get($params));
		$tpl->assign('managers',Dase_Collection::get($params)->getManagers());
		Dase::display($tpl->fetch('collectionbuilder/managers.tpl'));
	}

	public static function item_types($params)
	{
		$tpl = new Dase_Template();
		$tpl->assign('user',Dase_User::get($params));
		$c = Dase_Collection::get($params);
		$tpl->assign('collection',Dase_Collection::get($params));
		$tpl->assign('item_types',Dase_Collection::get($params)->getItemTypes());
		Dase::display($tpl->fetch('collectionbuilder/item_types.tpl'));
	}

	public static function dataAsJson($params)
	{
		$coll = Dase_Collection::get($params);
		if (isset($params['select'])) {
			//attributes, settings, types, managers are possible values
			$select = $params['select'];
		} else {
			$select = 'all';
		}	
		$cache = Dase_Cache::get($c->ascii_id . '_' . $select);
		//ttl can be long, but make sure that changes expire the cache!
		if (!$cache->isFresh(333)) {
			$data = $coll->getJsonData();
			$headers = array("Content-Type: application/json; charset=utf-8");
			$cache->setData($data,$headers);
		}
		$cache->display();
	}

	public static function setAttributeSortOrder($params)
	{
		$c = Dase_Collection::get($params);
		$cache = Dase_Cache::get($c->ascii_id . '_attributes');
		$cache->expire();
		$att_ascii_id = $params['attribute_ascii_id'];
		$new_so = file_get_contents('php://input');
		if (is_numeric($new_so)) {
			$new_so = intval($new_so);
			$c->changeAttributeSort($att_ascii_id,$new_so);
		}
		Dase::display('success '.$att_ascii_id.' -> '.$new_so);
	}
}

