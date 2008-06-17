<?php

class AdminHandler extends Dase_Handler
{
	public $collection;
	public $resource_map = array(
		'{collection_ascii_id}' => 'settings',
		'{collection_ascii_id}/archive' => 'archive',
		'{collection_ascii_id}/attributes' => 'attributes',
		'{collection_ascii_id}/item_types' => 'item_types',
		'{collection_ascii_id}/managers' => 'managers',
		'{collection_ascii_id}/settings' => 'settings',
		'{collection_ascii_id}/upload' => 'upload',
		'{collection_ascii_id}/attributes/{filter}' => 'attributes',
	);

	protected function setup($request)
	{
		$this->user = $request->getUser();
		if (!$this->user->checkCollectionAuth($request->get('collection_ascii_id'),'admin')) {
			$request->renderError(401);
		}
		$this->collection = Dase_DBO_Collection::get($request->get('collection_ascii_id'));
		if (!$this->collection) {
			$request->renderError(404);
		}
	}

	public function getSettings($request)
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('user',$this->user);
		$tpl->assign('collection',$this->collection);
		$request->renderResponse($tpl->fetch('admin/settings.tpl'));
	}

	public function getAttributes($request)
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('user',$this->user);
		$tpl->assign('collection',$this->collection);
		$tpl->assign('attributes',$this->collection->getAttributes());
		$request->renderResponse($tpl->fetch('admin/attributes.tpl'));
	}

	public function getItemTypes($request)
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('user',$this->user);
		$tpl->assign('collection',$this->collection);
		$tpl->assign('item_types',$this->collection->getItemTypes());
		$request->renderResponse($tpl->fetch('admin/item_types.tpl'));
	}

	public function getManagers($request)
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('user',$this->user);
		$tpl->assign('collection',$this->collection);
		$tpl->assign('managers',$this->collection->getManagers());
		$request->renderResponse($tpl->fetch('admin/managers.tpl'));
	}

	public function getUpload($request)
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('user',$this->user);
		$tpl->assign('collection',$this->collection);
		$request->renderResponse($tpl->fetch('admin/upload_form.tpl'));
	}

	public function getArchive($request) 
	{
		$archive = CACHE_DIR.$this->collection->ascii_id.'_'.time();
		file_put_contents($archive,$this->collection->asAtomArchive());
		$request->serveFile($archive,'text/plain',true);
	}

	public function rebuildIndexes($request) 
	{
		$c = Dase_Collection::get($request->get('collection_ascii_id'));
		$c->buildSearchIndex();
		$request->renderRedirect('',"rebuilt indexes for $c->collection_name");
	}

}

