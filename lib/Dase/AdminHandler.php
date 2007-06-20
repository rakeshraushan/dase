<?php

class Dase_AdminHandler 
{
	public static function index() {
		include (DASE_PATH . '/inc/config.php'); 
		if (!in_array(Dase::getUser()->eid,$conf['superusers'])) {
			Dase::reload('error','No dice...you need to be a superuser to go there.');
		}
		$coll = new Dase_DB_Collection;
		$coll->orderBy('collection_name');
		$tpl = Dase_Template::instance();
		$tpl->assign('collections',$coll->getAll());
		$msg = Dase_Utils::filterGet('msg');
		if (!$msg) {
			$msg = 'hello superadmin user';
		}
		$tpl->assign('msg',$msg);
		$tpl->assign('content','admin');
		$tpl->display();
		exit;
	}

	public static function buildIndex() {
		include (DASE_PATH . '/inc/config.php'); 
		if (!in_array(Dase::getUser()->eid,$conf['superusers'])) {
			Dase::reload('error','No dice...you need to be a superuser to go there.');
		}
		$coll = new Dase_DB_Collection;
		$coll->ascii_id = Dase_Utils::filterGet('collection_ascii_id');
		$coll->find(1);
		$coll->buildSearchIndex();
		Dase::reload('admin',"rebuilt indexes for $coll->collection_name");
	}
}
