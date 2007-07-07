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

	public static function viewLog() {
		$params = func_get_args();
		include (DASE_PATH . '/inc/config.php'); 
		if (!in_array(Dase::getUser()->eid,$conf['superusers'])) {
			Dase::reload('error','No dice...you need to be a superuser to go there.');
		}
		if (isset($params[0])) {
			switch ($params[0]) {
			case 'standard':
				$file = 'standard.log';
				break;
			case 'error':
				$file = 'error.log';
				break;
			case 'sql':
				$file = 'sql.log';
				break;
			case 'remote':
				$file = 'remote.log';
				break;
			default:
				header("HTTP/1.0 404 Not Found");
				exit;
			}
			readfile(DASE_PATH . "/log/" . $file);
			exit;
		}
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
