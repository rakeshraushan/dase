<?php

if (isset($params['collection_ascii_id'])) {
	$ascii = $params['collection_ascii_id'];
	//xslt version:
	$t = new Dase_Xslt(XSLT_PATH.'collection/browse.xsl');
	$t->set('c_ascii_id',$ascii);
	$t->set('local-layout',XSLT_PATH.'collection/browse.xml');
	$t->set('collection',APP_ROOT.'/xml/' . $ascii);
	$tpl = new Dase_Html_Template();
	$tpl->setText($t->transform());
	$tpl->display();
}
