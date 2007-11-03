<?php

if (isset($params['collection_ascii_id']) && ($params['serial_number'])) {
	$coll = $params['collection_ascii_id'];
	$sernum = $params['serial_number'];
	$t = new Dase_Xslt(XSLT_PATH.'item/default.xsl');
	$t->set('local-layout',XSLT_PATH.'item/default.xml');
	$t->set('item',APP_ROOT.'/atom/'. $coll . '/' . $sernum);
	$tpl = new Dase_Html_Template();
	$tpl->setText($t->transform());
	$tpl->display();
}
