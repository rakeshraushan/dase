<?php

class AdminHandler
{

	public static function index() {
		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'admin/index.xsl';
		$t->source = XSLT_PATH.'admin/layout.xml';
		$user = Dase::getUser();
		$t->addSourceNode($user->asSimpleXml());
		$c = Dase_Registry::get('collection');
		$t->addSourceNode($c->asSimpleXml());
		Dase::display($t->transform());
	}

	public static function dataAsJson() {
		$c = Dase_Registry::get('collection');
		$params = Dase_Registry::get('params');
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

	public static function setAttributeSortOrder() {
		$c = Dase_Registry::get('collection');
		if (!$c) {
			Dase::log('error',"no collection found");
			Dase_Error::report(400);
		}	
		$cache = new Dase_Cache($c->ascii_id . '_attributes');
		$cache->expire();
		$params = Dase_Registry::get('params');
		$att_ascii_id = $params['attribute_ascii_id'];
		$new_so = file_get_contents('php://input');
		if (is_numeric($new_so)) {
			$new_so = intval($new_so);
			$c->changeAttributeSort($att_ascii_id,$new_so);
		}
		Dase::display('success '.$att_ascii_id.' -> '.$new_so);
	}
}

