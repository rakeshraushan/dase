<?php

class CollectionbuilderHandler extends Dase_Handler
{

	public $collection;
	public $resource_map = array(
		'{collection_ascii_id}' => 'collection',
		'{collection_ascii_id}/attributes' => 'attributes',
		'{collection_ascii_id}/attributes/tallies' => 'attribute_tallies',
		'{collection_ascii_id}/attributes/{filter}' => 'attributes',
		'{collection_ascii_id}/attributes/{filter}/tallies' => 'attribute_tallies',
		'{collection_ascii_id}/attribute/{att_ascii_id}/values' => 'attribute_values',
	);

	protected function setup($request)
	{
		if ($request->has('collection_ascii_id')) {
			$this->collection = Dase_DBO_Collection::get($request->get('collection_ascii_id'));
		}
	}


	public function index($request)
	{
		//this is the best/easist way to implement redirect
		CollectionbuilderHandler::settings($request);
	}

	public function settings($request)
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('user',Dase_User::get($request));
		$tpl->assign('collection',Dase_Collection::get($request));
		$request->renderResponse($tpl->fetch('collectionbuilder/settings.tpl'));
	}

	public function attributes($request)
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('user',Dase_User::get($request));
		$c = Dase_Collection::get($request);
		$tpl->assign('collection',Dase_Collection::get($request));
		$tpl->assign('attributes',Dase_Collection::get($request)->getAttributes());
		$request->renderResponse($tpl->fetch('collectionbuilder/attributes.tpl'));
	}

	public function managers($request)
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('user',Dase_User::get($request));
		$tpl->assign('collection',Dase_Collection::get($request));
		$tpl->assign('managers',Dase_Collection::get($request)->getManagers());
		$request->renderResponse($tpl->fetch('collectionbuilder/managers.tpl'));
	}

	public function uploadForm($request)
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('user',Dase_User::get($request));
		$tpl->assign('collection',Dase_Collection::get($request));
		$request->renderResponse($tpl->fetch('collectionbuilder/upload_form.tpl'));
	}

	public function checkAtom($request)
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

	public function item_types($request)
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('user',Dase_User::get($request));
		$c = Dase_Collection::get($request);
		$tpl->assign('collection',Dase_Collection::get($request));
		$tpl->assign('item_types',Dase_Collection::get($request)->getItemTypes());
		$request->renderResponse($tpl->fetch('collectionbuilder/item_types.tpl'));
	}

	public function dataAsJson($request)
	{
		$coll = Dase_Collection::get($request);
		if (isset($params['select'])) {
			//attributes, settings, types, managers are possible values
			$select = $params['select'];
		} else {
			$select = 'all';
		}	
		$cache = Dase_Cache::get($c->ascii_id . '_' . $select);
		$data = $cache->getData(333);
		if (!$data) {
			$data = $coll->getJsonData();
			$cache->setData($data);
		}
		$request->renderResponse($data);
	}

	public function setAttributeSortOrder($request)
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

