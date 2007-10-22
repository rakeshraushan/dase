<?php

$collections = array();

if (Dase::filterGet('smarty')) {
	//smarty version: 41 req/sec
	$coll = new Dase_DB_Collection;
	$tpl = Dase_Template::instance();
	$coll->orderBy('collection_name');
	$coll->is_public = 1;
	$collections = $coll->findAll();
	$tpl->assign('collections',$collections);
	$tpl->assign('content','collections');
	$tpl->display('page.tpl');
} else {
	//xslt version: 103 req/sec
	$t = new Dase_Xslt(XSLT_PATH.'collection/list.xsl');
	$t->set('local-layout',XSLT_PATH.'collection/list.xml');
	$t->set('collections',APP_ROOT.'/xml/collections');
	$tpl = new Dase_Html_Template();
	
	//xhtml output:
	$tpl->setText($t->transform());
	$tpl->display();

	//html output:
	//$t2 = new Dase_Xslt(XSLT_PATH.'xhtml2html.xsl',$t->transform());
	//$tpl = new Dase_Html_Template();
	//$tpl->setText($t2->transform());
	//$tpl->display();
	
}
