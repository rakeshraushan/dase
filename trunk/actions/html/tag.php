<?php

if (isset($params['eid']) && isset($params['ascii_id'])) {
	$t = new Dase_Xslt(XSLT_PATH.'/xoxo/xml2xoxo.xsl',XSLT_PATH.'/xoxo/xoxo.xml');
	$t->set('src',APP_ROOT.'/xml/user/' . $params['eid'] . '/tag/' . $params['ascii_id']);
	$tpl = new Dase_Html_Template();
	$tpl->setText($t->transform());
	$tpl->display();
}
if (isset($params['id'])) {
	$t = new Dase_Xslt(XSLT_PATH.'/xoxo/xml2xoxo.xsl',XSLT_PATH.'/xoxo/xoxo.xml');
	$t->set('src',APP_ROOT.'/xml/tag/' . $params['id']);
	$tpl = new Dase_Html_Template();
	$tpl->setText($t->transform());
	$tpl->display();
}
