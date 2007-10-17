<?php

// performance note:
// without cache ~55 req/sec
// with cache ~ 195 req/sec
//
// as part of a request for coll list html page (xslt)
// without cache ~40 req/sec
// with cache ~80 req/sec

$cxml = ''; 
$cache = new Dase_FileCache('collections.xml');
if ($cache->get()) {
	$cxml = $cache->get();
} else {
	$cxml = Dase_DB_Collection::listPublicAsXml();
	$cache->set($cxml);
}
$tpl = new Dase_Xml_Template();
$tpl->setXml($cxml);
$tpl->display();
