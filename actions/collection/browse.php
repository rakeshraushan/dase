<?php

if (isset($params['collection_ascii_id'])) {
	$coll = new Dase_DB_Collection;
	$tpl = Dase_Template::instance();
	$coll->ascii_id = $params['collection_ascii_id'];
	$coll->findOne();
	$coll->getItemCount();
	$tpl->assign('collection',$coll);
	$tpl->assign('content','collection');
	$tpl->display('page.tpl');
	exit;
}
