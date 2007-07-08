<?php

class Dase_CollectionHandler 
{
	public static function index() {
		$params = func_get_args();
		$coll = new Dase_DB_Collection;
		$tpl = Dase_Template::instance();
		//single collection request
		if (isset($params[0])) {
			if (isset($_GET['browse_attribute_id'])) {
				$browse_attribute_id = Dase_Utils::filterGet('browse_attribute_id');
				$att = new Dase_DB_Attribute;
				$att->load($browse_attribute_id);
				$att->getDisplayValues();
				$tpl->assign('attribute',$att);
			}
			$coll->ascii_id = $params[0];
			$coll->findOne();
			$coll->getAdminAttributes();
			$coll->getAttributes();
			$coll->getCategories();
			$coll->getItemCount();
			$tpl->assign('collection',$coll);
			$tpl->assign('content','collection');
			Dase_Plugins::act($coll->ascii_id,'before_display');
			$tpl->display('page.tpl');
			//multi or no collection request
		} else {
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
		}
	}

	public static function publicXml() {
		//single collection request
		$params = func_get_args();
		if (isset($params[0])) {
			if (isset($_GET['token']) && 'secret' == $_GET['token']) {
				$tpl = new Dase_Xml_Template;
				$coll = new Dase_DB_Collection;
				$coll->ascii_id = $params[0];
				$coll->findOne();
				$tpl->setXml($coll->xmlDump());
				$tpl->display();
			}
		}
	}
}
