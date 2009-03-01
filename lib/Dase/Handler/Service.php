<?php

/**
 *
 * for general AtomPub service document
 *
 */

class Dase_Handler_Service extends Dase_Handler
{
	public $resource_map = array(
		'/' => 'service',
	);

	public function setup($r)
	{
	}

	public function getServiceTxt($r)
	{
		$this->getService($r);
	}

	public function getService($r)
	{
		$svc = new Dase_Atom_Service;	
		$meta_workspace = $svc->addWorkspace('DASe MetaCollections Workspace');
		$meta_coll = $meta_workspace->addCollection($r->app_root.'/collections','DASe Collections');
		$meta_coll->addAccept('application/atom+xml;type=entry');
		$scheme = new Dase_DBO_CategoryScheme($this->db);
		$scheme->applies_to = 'collection';
		foreach ($scheme->find() as $sch) {
			$meta_cats = $meta_coll->addCategorySet(
				$sch->fixed,$r->app_root.'/category/'.$sch->uri);
			$meta_cats->addCategory('default');
		}
		$meta_cats = $meta_coll->addCategorySet();
		$meta_cats->addCategory('collection','http://daseproject.com/category/entrytype');
		$users_coll = $meta_workspace->addCollection($r->app_root.'/users','DASe Users');
		$users_coll->addAccept('application/atom+xml;type=entry');
		$users_cats = $users_coll->addCategorySet();
		$users_cats->addCategory('user','http://daseproject.com/category/entrytype');
		$r->response_mime_type = 'application/atomsvc+xml';
		$r->renderResponse($svc->asXml());
	}
}

