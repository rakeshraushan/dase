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
		$c = Dase_Collection::get($params);
		if (isset($params['select'])) {
			//attributes, settings, types, managers are possible values
			$select = $params['select'];
		} else {
			$select = 'all';
		}	
		$cache = Dase_Cache::get($c->ascii_id . '_' . $select);
		$page = $cache->getData(); //if successful, sends headers
		if (!$page) {
			//todo: make long cache -- *and* make sure that changes kill it
			$cache->setTimeToLive(3);
			$page = $c->getData();
			$headers = array("Content-Type: application/json; charset=utf-8");
			$cache->setData($page,$headers);
			header($headers[0]);
		}
		echo $page;
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

