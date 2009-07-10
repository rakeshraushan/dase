<?php

class Dase_Handler_Tags extends Dase_Handler
{

	public $resource_map = array( 
		'/' => 'tags',
		'service' => 'service',
		'search' => 'search',
	);

	protected function setup($r)
	{
	}	

	public function postToTags($r)
	{
		$tag_name = $r->get('tag_name');
		//todo: make this work w/ cookie OR http auth??
		$user = $r->getUser();
		$tag = Dase_DBO_Tag::create($this->db,$tag_name,$user);
		if ($tag) {
			//todo: should send a 201 w/ location header
			$user->expireDataCache($r->getCache());
			$r->renderResponse('Created "'.$tag_name.'"');
		} else {
			$r->renderError(409,'Please choose another name.');
		}
	}

	public function getService($r)
	{
		$svc = new Dase_Atom_Service;	
		$meta_workspace = $svc->addWorkspace('DASe Sets Workspace');
		$meta_coll = $meta_workspace->addCollection($r->app_root.'/tags','DASe Sets');
		$meta_coll->addAccept('application/atom+xml;type=entry');
		$r->response_mime_type = 'application/atomsvc+xml';
		$r->renderResponse($svc->asXml());
	}

	public function getTagsAtom($r)
	{
		if ($r->has('category')) {
			$r->renderResponse(Dase_DBO_Tag::listAsFeed($this->db,$r->app_root,$r->get('category')));
		} else {
			$r->renderResponse(Dase_DBO_Tag::listAsFeed($this->db,$r->app_root));
		}

	}

	public function getTags($r)
	{
		$tpl = new Dase_Template($r);
		$feed = Dase_Atom_Feed::retrieve($r->app_root.'/tags.atom');
		$tpl->assign('sets',$feed);
		$r->renderResponse($tpl->fetch('tags/list.tpl'));
	}

	public function getSearchAtom($r) 
	{
		$term = $r->get('category');
		$uri = $r->get('scheme');
		//need to write sql here
	}
}

