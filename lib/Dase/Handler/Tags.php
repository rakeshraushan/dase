<?php

class Dase_Handler_Tags extends Dase_Handler
{

	public $resource_map = array( 
		'/' => 'tags',
		'service' => 'service',
		'search' => 'search',
		//categories by scheme:
		'{seg1}' => 'categories',
		'{seg1}/{seg2}' => 'categories',
		'{seg1}/{seg2}/{seg3}' => 'categories',
		'{seg1}/{seg2}/{seg3}/{seg4}' => 'categories',
	);

	protected function setup($r)
	{
	}	

	private function _getUri($r)
	{
		if ($r->has('uri')) {
			return $r->get('uri');
		}
		$uri_parts = array();
		foreach (array(1,2,3,4) as $i)  {
			if ($r->has('seg'.(string) $i)) {
				$uri_parts[] = $r->get('seg'.(string) $i);
			}       
		}
		return join('/',$uri_parts);
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

	public function getCategoriesCats($r)
	{
		$scheme_uri = $this->_getUri($r);
		$r->renderResponse(Dase_DBO_Tag::getTagCategoriesByScheme($this->db,$r->app_root,$scheme_uri));
	}

	public function getTags($r)
	{
		$tpl = new Dase_Template($r);
		$courses = Dase_Atom_Categories::load(file_get_contents($r->app_root.'/sets/utexas/courses.cats'));
		$tpl->assign('courses',$courses);
		if ($r->has('category')) {
			$feed = Dase_Atom_Feed::retrieve($r->app_root.'/tags.atom?category='.$r->get('category'));
		} else {
			$feed = Dase_Atom_Feed::retrieve($r->app_root.'/tags.atom');
		}
		$tpl->assign('sets',$feed);
		$r->renderResponse($tpl->fetch('tags/list.tpl'));
	}

	public function getSearchAtom($r) 
	{
		$r->renderResponse(Dase_DBO_Tag::searchTagsAtom($this->db,$r->app_root,$r->get('q')));
	}

	public function getSearch($r) 
	{

		$tpl = new Dase_Template($r);
		$courses = Dase_Atom_Categories::load(file_get_contents($r->app_root.'/sets/utexas/courses.cats'));
		$tpl->assign('courses',$courses);
		$feed = Dase_Atom_Feed::retrieve($r->app_root.'/tags/search.atom?q='.$r->get('q'));
		$tpl->assign('sets',$feed);
		$tpl->assign('q',$r->get('q'));
		$r->renderResponse($tpl->fetch('tags/list.tpl'));
	}
}

