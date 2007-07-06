<?php

class Dase_ApiHandler 
{
	public static function index() {
		echo "howdy, api"; exit;
	}

	public static function xml() {
		//single collection request
		$params = func_get_args();
		if (isset($params[0])) {
			if (isset($_GET['token']) && 'secret' == $_GET['token']) {
				$tpl = new Dase_Xml_Template;
				$coll = new Dase_DB_Collection;
				$coll->ascii_id = $params[0];
				$coll->find(1);
				$tpl->setXml($coll->xmlDump());
				$tpl->display();
			}
		}
	}
}
