<?php

//NOTE: if params[i'collection_ascii_id'] is set, get individual coll dump
// otherwise xml of collections

if (isset($params['collection_ascii_id'])) {
	$token = Dase::filterGet('token');
	if ('secret' == $token) {
		$limit = Dase::filterGet('limit');
		if (!$limit) {
			//so you need to conciously try to crash firefox
			$limit = 1;
		}
		$tpl = new Dase_Xml_Template;
		$coll = new Dase_DB_Collection;
		$coll->ascii_id = $params['collection_ascii_id'];
		if ($coll->findOne()) {
			//check cache
			$cached = Dase_DB_UtilCache::getText('collection_xmldump',$coll->ascii_id);
			if ($cached) {
				$tpl = new Dase_Xml_Template;
				$tpl->setXml($cached);
				$tpl->display();
			}
			$xmldump = $coll->xmlDump($limit);
			$tpl->setXml($xmldump);
			Dase_DB_UtilCache::saveText('collection_xmldump',$xmldump,$coll->id,$limit);
			$tpl->display();
			exit;
		}
	}
} else {
	//how to distinguish between 
	//public and non-public 
	//collections ??????????????????????
	$tpl = new Dase_Xml_Template;
	$coll = new Dase_DB_Collection;
	//check cache
	$cached = Dase_DB_UtilCache::getText('collections_xml');
	if ($cached) {
		$tpl = new Dase_Xml_Template;
		$tpl->setXml($cached);
		$tpl->display();
	}
	$colls_xml = $coll->listAllAsXml();
	$tpl->setXml($colls_xml);
	Dase_DB_UtilCache::saveText('collections_xml','',$colls_xml);
	$tpl->display();
	exit;
}
