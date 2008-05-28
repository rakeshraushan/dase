<?php

class CollectionbuilderHandler
{

	public static function index($request)
	{
		//this is the best/easist way to implement redirect
		CollectionbuilderHandler::settings($request);
	}

	public static function settings($request)
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('user',Dase_User::get($request));
		$tpl->assign('collection',Dase_Collection::get($request));
		$request->renderResponse($tpl->fetch('collectionbuilder/settings.tpl'));
	}

	public static function attributes($request)
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('user',Dase_User::get($request));
		$c = Dase_Collection::get($request);
		$tpl->assign('collection',Dase_Collection::get($request));
		$tpl->assign('attributes',Dase_Collection::get($request)->getAttributes());
		$request->renderResponse($tpl->fetch('collectionbuilder/attributes.tpl'));
	}

	public static function managers($request)
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('user',Dase_User::get($request));
		$tpl->assign('collection',Dase_Collection::get($request));
		$tpl->assign('managers',Dase_Collection::get($request)->getManagers());
		$request->renderResponse($tpl->fetch('collectionbuilder/managers.tpl'));
	}

	public static function uploadForm($request)
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('user',Dase_User::get($request));
		$tpl->assign('collection',Dase_Collection::get($request));
		$request->renderResponse($tpl->fetch('collectionbuilder/upload_form.tpl'));
	}

	public static function checkAtom($request)
	{
		$tpl = new Dase_Template($request);
		$coll = Dase_Collection::get($request);
		$tpl->assign('collection',$coll);
		$entry = Dase_Atom_Entry_MemberItem::load($_FILES['atom']['tmp_name'],false);
		$tpl->assign('entry',$entry);
		$metadata = array();
		if ($entry->validate()) {
			foreach ($entry->metadata as $k => $v) {
				if (Dase_DBO_Attribute::get($coll->ascii_id,$k)) {
					$metadata[$k] = 'OK -- attributes exists';
				} else {
					$metadata[$k] = 'does NOT exist';
				}
			}
		} else {
			//see http://www.imc.org/atom-protocol/mail-archive/msg10901.html
			Dase::error(422);
		}
		$tpl->assign('metadata',$metadata);
		$request->renderResponse($tpl->fetch('collectionbuilder/check.tpl'));
	}

	public static function item_types($request)
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('user',Dase_User::get($request));
		$c = Dase_Collection::get($request);
		$tpl->assign('collection',Dase_Collection::get($request));
		$tpl->assign('item_types',Dase_Collection::get($request)->getItemTypes());
		$request->renderResponse($tpl->fetch('collectionbuilder/item_types.tpl'));
	}

	public static function dataAsJson($request)
	{
		$coll = Dase_Collection::get($request);
		if (isset($params['select'])) {
			//attributes, settings, types, managers are possible values
			$select = $params['select'];
		} else {
			$select = 'all';
		}	
		$cache = Dase_Cache::get($c->ascii_id . '_' . $select);
		//ttl can be long, but make sure that changes expire the cache!
		if (!$cache->isFresh(333)) {
			$data = $coll->getJsonData();
			$headers = array("Content-Type: application/json; charset=utf-8");
			$cache->setData($data,$headers);
		}
		$cache->display();
	}

	public static function setAttributeSortOrder($request)
	{
		$c = Dase_Collection::get($request);
		$cache = Dase_Cache::get($c->ascii_id . '_attributes');
		$cache->expire();
		$att_ascii_id = $params['attribute_ascii_id'];
		$new_so = file_get_contents('php://input');
		if (is_numeric($new_so)) {
			$new_so = intval($new_so);
			$c->changeAttributeSort($att_ascii_id,$new_so);
		}
		$request->renderResponse('success '.$att_ascii_id.' -> '.$new_so);
	}
}

