<?php

if (isset($params['collection_ascii_id'])) {
	$coll = new Dase_DB_Collection;
	$tpl = Dase_Template::instance();
	$coll->ascii_id = $params['collection_ascii_id'];
	$coll->findOne();
	$coll->getAdminAttributes();
	$coll->getAttributes();
	$coll->getItemCount();
	$tpl->assign('collection',$coll);
	$tpl->assign('content','collection');
	Dase_Plugins::act($coll->ascii_id,'before_display');
	$tpl->display('page.tpl');
	exit;
}
