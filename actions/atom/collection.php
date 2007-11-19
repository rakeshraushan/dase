<?php

if (isset($params['collection_ascii_id'])) {
	$t = new Dase_Xslt(XSLT_PATH.'/atom/collection.xsl',XSLT_PATH.'/atom/layout.xml');
	$t->set('src',APP_ROOT.'/xml/' . $params['collection_ascii_id']);
	$tpl = new Dase_Xml_Template();
	$tpl->setXml($t->transform());
	$tpl->setContentType('application/atom+xml');
	$tpl->display();
} else {
	Dase::error(404);
}

