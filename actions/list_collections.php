<?php

Dase::checkUser();
$coll = new Dase_DB_Collection;
$tpl = Dase_Template::instance();
$coll->orderBy('collection_name');
$collections = $coll->getAll();
$current_collections = Dase::getCurrentCollections();
if (!$current_collections) {
	foreach ($collections as $coll) {
		$current_collections[] = $coll['id'];
	}
}
$tpl->assign('last_search',Dase_Session::get('last_search'));
$tpl->assign('current_collections',$current_collections);
$tpl->assign('collections',$collections);
$tpl->assign('content','collections');
Dase_Plugins::act('dase','before_display');
$tpl->display('page.tpl');
exit;
