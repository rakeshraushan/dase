<?php

class Dase_Handler_Service extends Dase_Handler
{
	public $resource_map = array(
		'/' => 'service',
	);

	public function setup($request)
	{
	}

	public function getService($request)
	{
		$svc = new Dase_Atom_Service;	
		$meta_workspace = $svc->addWorkspace('DASe MetaUser Workspace');
		$meta_coll = $meta_workspace->addCollection(APP_ROOT.'/collections','DASe Collections');
		$meta_coll->addAccept('application/atom+xml;type=entry');
		$meta_cats = $meta_coll->addCategorySet();
		$meta_cats->addCategory('collection','http://daseproject/category/entrytype');

		$workspace = $svc->addWorkspace('DASe Collections Workspace');
		$colls = new Dase_DBO_Collection;
		foreach ($colls->find() as $c) {
			$coll_coll = $workspace->addCollection(APP_ROOT.'/'.$c->ascii_id,$c->collection_name.' Items');
			$coll_coll->addAccept('application/atom+xml;type=entry');
			$cats = $coll_coll->addCategorySet();
			$cats->addCategory('item','http://daseproject/category/entrytype');
		}
		$request->response_mime_type = 'application/atomsvc+xml';
		$request->renderResponse($svc->asXml());
	}
}

