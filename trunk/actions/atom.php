<?php

if (isset($params['collection_ascii_id'])) {
	$coll = Dase_DB_Collection::get($params['collection_ascii_id']);
	$tpl = new Dase_Xml_Template;
	$tpl->setXml($coll->getAtom());
	$tpl->display('application/atom+xml');
}
exit;
