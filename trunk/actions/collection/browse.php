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

	/* smarty:
	$c = new Dase_DB_Collection;
	$c->ascii_id = $params['collection_ascii_id'];
	$c->findOne();
	$c->getItemCount();
	$tpl = Dase_Template::instance();
	$tpl->assign('collection',$c);
	$tpl->assign('content','collection');
	$tpl->display('page.tpl');
	 */
}
