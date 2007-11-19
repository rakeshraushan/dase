<?php

if (isset($params['collection_ascii_id']) && isset($params['attribute_ascii_id'])) {
	$t = new Dase_Xslt(XSLT_PATH.'/xoxo/xml2xoxo.xsl',XSLT_PATH.'/xoxo/xoxo.xml');
	$t->set('src',APP_ROOT.'/xml/' . $params['collection_ascii_id'] . '/att/' . $params['attribute_ascii_id']);
	$tpl = new Dase_Html_Template();
	$tpl->setText($t->transform());
	$tpl->display();
}
