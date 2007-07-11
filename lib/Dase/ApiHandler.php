<?php

class Dase_ApiHandler 
{
	public static function index() {
		//single collection request
		$params = func_get_args();
		if (isset($params[0])) {
			$limit = Dase_Utils::filterGet('limit');
			if (!$limit) {
				//so you need to conciously try to crash firefox
				$limit = 1;
			}
			$tpl = new Dase_Xml_Template;
			$coll = new Dase_DB_Collection;
			$coll->ascii_id = $params[0];
			if ($coll->findOne()) {
				//check cache
				$cached_xml = Dase_DB_XmlCache::getXml('collection_xmldump',$coll->id,$limit);
				if ($cached_xml) {
					$tpl = new Dase_Xml_Template;
					$tpl->setXml($cached_xml);
					$tpl->display();
				}
				$xmldump = $coll->xmlDump($limit);
				$tpl->setXml($xmldump);
				Dase_DB_XmlCache::saveXml('collection_xmldump',$xmldump,$coll->id,$limit);
				$tpl->display();
			}
		} else {
			Dase_ApiHandler::collections();
		}
	}

	public static function collections() {
		//how to distinguish between 
		//public and non-public 
		//collections
		$tpl = new Dase_Xml_Template;
		$coll = new Dase_DB_Collection;
		//check cache
		$cached_xml = Dase_DB_XmlCache::getXml('collections_xml');
		if ($cached_xml) {
			$tpl = new Dase_Xml_Template;
			$tpl->setXml($cached_xml);
			$tpl->display();
		}
		$colls_xml = $coll->getAllAsXml();
		$tpl->setXml($colls_xml);
		Dase_DB_XmlCache::saveXml('collections_xml',$colls_xml);
		$tpl->display();
	}
}
