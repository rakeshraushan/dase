<?php

if (isset($params['collection_ascii_id'])) {
	$ascii = $params['collection_ascii_id'];
	$cxml = ''; 
	$cache = new Dase_FileCache($ascii . '.xml');
	if ($cache->get()) {
		$cxml = $cache->get();
	} else {
		$c = Dase_Collection::get($params['collection_ascii_id'],'db');
		$cxml = $c->getXml();
		$cache->set($cxml);
	}
	$tpl = new Dase_Xml_Template();
	$tpl->setXml($cxml);
	$tpl->display();
}
