<?php

if (isset($params['collection_ascii_id'])) {
	$coll = Dase_DB_Collection::get($params['collection_ascii_id']);
	if ($coll) {
	$tpl = new Dase_Xml_Template;
	$tpl->setXml($coll->getAtom());
	$tpl->setContentType('application/atom+xml');
	$tpl->display();
	} else {
		Dase::error(404);
	}
}
exit;
