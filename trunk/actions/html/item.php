<?php

if (isset($params['collection_ascii_id']) && isset($params['serial_number'])) {
	$t = new Dase_Xslt(XSLT_PATH.'/xoxo/xml2xoxo.xsl',XSLT_PATH.'/xoxo/xoxo.xml');
	$t->set('src',APP_ROOT.'/xml/' . $params['collection_ascii_id'] . '/' . $params['serial_number']);
	$tpl = new Dase_Html_Template();
	$tpl->setText($t->transform());
	$tpl->display();
}
