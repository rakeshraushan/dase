<?php

if (isset($params['collection_ascii_id'])) {
	$coll = Dase_Collection::get($params['collection_ascii_id'],'xml');
	$tpl = new Dase_Xml_Template;
	$tpl->setXml($coll->getXml());
	$tpl->display();
}
