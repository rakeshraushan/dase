<?php

class AdminHandler
{

	public static function index($params)
	{
		//this is the best/easist way to implement redirect
		AdminHandler::settings($params);
	}

	public static function settings($params)
	{
		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'admin/settings.xsl';
		$t->source = XSLT_PATH.'admin/layout.xml';
		$user = Dase_User::get($params);
		$t->addSourceNode($user->asSimpleXml());
		$c = Dase_Collection::get($params);
		$t->addSourceNode($c->asSimpleXml());
		Dase::display($t->transform());
	}

	public static function attributes($params)
	{
		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'admin/attributes.xsl';
		$t->source = XSLT_PATH.'admin/layout.xml';
		$user = Dase_User::get($params);
		$t->addSourceNode($user->asSimpleXml());
		$c = Dase_Collection::get($params);
		$t->addSourceNode($c->attributesAsSimpleXml());
		Dase::display($t->transform());
	}

	public static function managers($params)
	{
		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'admin/managers.xsl';
		$t->source = XSLT_PATH.'admin/layout.xml';
		$user = Dase_User::get($params);
		$t->addSourceNode($user->asSimpleXml());
		$c = Dase_Collection::get($params);
		$t->addSourceNode($c->managersAsSimpleXml());
		Dase::display($t->transform());
	}

	public static function item_types($params)
	{
		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'admin/item_types.xsl';
		$t->source = XSLT_PATH.'admin/layout.xml';
		$user = Dase_User::get($params);
		$t->addSourceNode($user->asSimpleXml());
		$c = Dase_Collection::get($params);
		$t->addSourceNode($c->itemTypesAsSimpleXml());
		Dase::display($t->transform());
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
		$cache = new Dase_Cache($c->ascii_id . '_' . $select);
		$page = $cache->get();
		if (!$page) {
			//long cache -- make sure that changes kill it
			$cache->setTimeToLive(3);
			$page = $c->getData($select);
			$cache->set($page);
		}
		Dase::display($page,false);
	}

	public static function setAttributeSortOrder($params)
	{
		$c = Dase_Collection::get($params);
		$cache = new Dase_Cache($c->ascii_id . '_attributes');
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

