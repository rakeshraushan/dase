<?php

class Dase_ApiHandler 
{
	public static function index() {
		//single collection request
		$params = func_get_args();
		if (isset($params[0])) {
			$tpl = new Dase_Xml_Template;
			$coll = new Dase_DB_Collection;
			$coll->ascii_id = $params[0];
			if ($coll->findOne()) {
				$tpl->setXml($coll->xmlDump());
				$tpl->display();
			}
		} else {
			Dase_ApiHandler::collections();
		}
	}

	public static function collections() {
		$tpl = new Dase_Xml_Template;
		$coll = new Dase_DB_Collection;
		$tpl->setXml($coll->getAllAsXml());
		$tpl->display();
	}
}
